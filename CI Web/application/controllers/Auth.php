<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
    }
    public function index()
    {
        if ($this->session->userdata('user_email')) {
            redirect('user');
        }
        $this->form_validation->set_rules('user_email', 'Email', 'trim|required|valid_email');
        $this->form_validation->set_rules('user_pwd', 'Password', 'trim|required');

        if ($this->form_validation->run() == false) {
            $data['title'] = 'Login';
            $this->load->view('templates/auth_header', $data);
            $this->load->view('templates/topbar-guest');
            $this->load->view('auth/login');
            $this->load->view('templates/auth_footer');
        } else {
            $this->_login();
        }
    }

    private function _login()
    {
        $user_email = $this->input->post('user_email');
        $user_pwd = $this->input->post('user_pwd');

        $user_db = $this->db->get_where('user_db', ['user_email' => $user_email])->row_array();

        if ($user_db) {
            if ($user_db['user_active'] == 1) {
                if (password_verify($user_pwd, $user_db['user_pwd'])) {
                    $data = [
                        'user_email' => $user_db['user_email'],
                        'role_id' => $user_db['role_id']
                    ];
                    $this->session->set_userdata($data);
                    if ($user_db['role_id'] == 1) {
                        redirect('admin');
                    } else {
                        redirect('user');
                    }
                } else {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
                    Wrong password! </div>');
                    redirect('auth');
                }
            } else {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
                Email is has not been activated!  </div>');
                redirect('auth');
            }
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
            Email is not registered! </div>');
            redirect('auth');
        }
    }

    public function registration()
    {

        if ($this->session->userdata('user_email')) {
            redirect('user');
        }
        $this->form_validation->set_rules('user_name', 'Name', 'required|trim');
        $this->form_validation->set_rules('user_email', 'Email', 'required|trim|valid_email|is_unique[user_db.user_email]', [
            'is_unique' => 'This email has already registered!'
        ]);
        $this->form_validation->set_rules('user_pwd', 'Password', 'required|trim|min_length[3]|matches[user_pwd2]', [
            'matches' => 'Password dont match!',
            'min_length' => 'Password too short!'
        ]);
        $this->form_validation->set_rules('user_pwd2', 'Password', 'required|trim|min_length[3]|matches[user_pwd]');

        if ($this->form_validation->run() == false) {
            $data['title'] = 'Registration';
            $this->load->view('templates/auth_header', $data);
            $this->load->view('templates/topbar-guest');
            $this->load->view('auth/registration');
            $this->load->view('templates/auth_footer');
        } else {
            $email = $this->input->post('user_email', true);
            $data = [
                'user_name' => htmlspecialchars($this->input->post('user_name', true)),
                'user_email' => htmlspecialchars($email),
                'user_pwd' => password_hash($this->input->post('user_pwd'), PASSWORD_DEFAULT),
                'user_regist' => time(),
                'user_img_profile' => 'default.jpg',
                'role_id' => 3,
                'user_active' => 0,

            ];

            // create token
            $token = base64_encode(random_bytes(32));
            $user_token = [
                'email' => $email,
                'token' => $token,
                'date_created' => time()
            ];

            $this->db->insert('user_db', $data);
            $this->db->insert('user_token', $user_token);

            $this->_sendEmail($token, 'verify');

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
            Data <strong>berhasil </strong> ditambahkan. </div>');
            redirect('auth');
        }
    }

    private function _sendEmail($token, $type)
    {
        $config = [
            'protokol' => 'smtp',
            'smtp_host' => 'ssl://smtp.googlemail.com',
            'smtp_user' => 'liestranslation@gmail.com',
            'smtp_pass' => 'zinonime97',
            'smtp_port' => 465,
            'mailtype' => 'html',
            'charset' => 'utf-8'
        ];

        $this->email->initialize($config);
        $this->load->library('email', $config);

        $this->email->set_newline("\r\n");

        $this->email->from('liestranslation@gmail.com', 'Lies Translation');
        $this->email->to($this->input->post('user_email'));

        if ($type == 'verify') {
            $this->email->subject('Verification Code');
            $this->email->message('Click this link to verify your account : <a href="' . base_url() . 'auth/verify?email=' . $this->input->post('user_email') . '&token=' . urlencode($token) . '">Active</a>');
        } elseif ($type == 'forgot') {
            $this->email->subject('Reset Password');
            $this->email->message('Click this link to reset your password : <a href="' . base_url() . 'auth/resetpassword?email=' . $this->input->post('user_email') . '&token=' . urlencode($token) . '">Reset Password</a>');
        }

        if ($this->email->send()) {
            return true;
        } else {
            echo $this->email->print_debugger();
            die;
        };
    }

    // Verifikasi Token untuk aktifkan email yang digunakan untuk registrasi
    public function verify()
    {
        $email = $this->input->get('email');
        $token = $this->input->get('token');

        $user = $this->db->get_where('user_db', ['user_email' => $email])->result_array();

        if ($user) {
            $user_token = $this->db->get_where('user_token', ['token' => $token])->row_array();

            if ($user_token) {
                if (time() - $user_token['date_created'] < (60 * 60 * 24)) {
                    $this->db->set('user_active', 1);
                    $this->db->where('user_email', $email);
                    $this->db->update('user_db');

                    $this->db->delete('user_token', ['email' => $email]);

                    $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
                    Congrats! ' . $email . ' has been activated! Please login. </div>');
                    redirect('auth');
                } else {

                    $this->db->delete('user_db', ['user_email' => $email]);
                    $this->db->delete('user_token', ['email' => $email]);

                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
                    Account activation failed! Token has expired. </div>');
                    redirect('auth');
                }
            } else {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
                Account activation failed! Wrong token. </div>');
                redirect('auth');
            }
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
            Account activation failed! Wrong email. </div>');
            redirect('auth');
        }
    }

    public function logout()
    {
        $this->session->unset_userdata('user_email');
        $this->session->unset_userdata('role_id');

        $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
        You have been logged out! </div>');
        redirect('auth');
    }



    public function blocked()
    {
        $data['title'] = 'Access Denied!!';
        $this->load->view('templates/header', $data);
        $this->load->view('auth/blocked', $data);
        $this->load->view('templates/footer');
    }


    public function forgotPassword()
    {
        $this->form_validation->set_rules('user_email', 'Email', 'required|trim|valid_email');

        if ($this->form_validation->run() == false) {
            $data['title'] = 'Forgot Password';
            $this->load->view('templates/header', $data);
            $this->load->view('auth/forgotPassword', $data);
            $this->load->view('templates/footer');
        } else {
            $email = $this->input->post('user_email');
            $user = $this->db->get_where('user_db', ['user_email' => $email, 'user_active' => 1])->row_array();

            if ($user) {
                $token = base64_encode(random_bytes(32));
                $user_token = [
                    'email' => $email,
                    'token' => $token,
                    'date_created' => time()
                ];

                $this->db->insert('user_token', $user_token);
                $this->_sendEmail($token, 'forgot');

                $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
                Please check your email to reset your password! </div>');
                redirect('auth/forgotPassword');
            } else {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
                Email is not registered or activated </div>');
                redirect('auth/forgotPassword');
            }
        }
    }

    public function resetpassword()
    {
        $email = $this->input->get('email');
        $token = $this->input->get('token');

        $user = $this->db->get_where('user_db', [
            'user_email' => $email
        ])->row_array();

        if ($user) {
            $user_token = $this->db->get_where('user_token', ['token' => $token])->row_array();

            if ($user_token) {
                if (time() - $user_token['date_created'] < (60 * 60 * 24)) {
                    $this->session->set_userdata('reset_email', $email);
                    $this->changePassword();
                } else {
                    $this->db->delete('user_token', ['email' => $email]);

                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
                    Account activation failed! Token has expired. </div>');
                    redirect('auth');
                }
            } else {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
                Reset password failed! Wrong token. </div>');
                redirect('auth');
            }
        } else {
            $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
            Reset password failed! Wrong email. </div>');
            redirect('auth');
        }
    }

    public function changePassword()
    {
        if (!$this->session->userdata('reset_email')) {
            redirect('auth');
        }

        $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[6]|matches[repeatpassword]');
        $this->form_validation->set_rules('repeatpassword', 'Repeat Password', 'trim|required|min_length[6]|matches[password]');

        if ($this->form_validation->run() == false) {
            $data['title'] = 'Reset Password';
            $this->load->view('templates/header', $data);
            $this->load->view('auth/reset-password', $data);
            $this->load->view('templates/footer');
        } else {
            $password = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
            $email = $this->session->userdata('reset_email');

            $this->db->set('user_pwd', $password);
            $this->db->where('user_email', $email);
            $this->db->update('user_db');

            $this->session->unset_userdata('reset_email');

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
            Password has been changed! Please login. </div>');
            redirect('auth');
        }
    }
}
