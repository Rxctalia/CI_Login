<?php
defined('BASEPATH') or exit('No direct script access allowed');

class user extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        is_logged_in();
    }

    public function index()
    {
        $data['title'] = 'My Profile';
        $data['user'] = $this->db->get_where('user_db', ['user_email' =>
        $this->session->userdata('user_email')])->row_array();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('user/index', $data);
        $this->load->view('templates/footer');
    }

    public function editProfile()
    {
        $data['title'] = 'Edit Profile';
        $data['user'] = $this->db->get_where('user_db', ['user_email' =>
        $this->session->userdata('user_email')])->row_array();

        $this->form_validation->set_rules('name', 'Full Name', 'required|trim');

        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/editProfile', $data);
            $this->load->view('templates/footer');
        } else {
            $name = $this->input->post('name');
            $email = $this->input->post('email');

            // check if there's image want to upload
            $upload_image = $_FILES['image']['name'];
            // configuration file upload
            if ($upload_image) {
                $config['upload_path'] = './assets/img/profile/';
                $config['allowed_types'] = 'gif|jpg|png';
                $config['max_size']     = '2048';
                // $config['max_width'] = '1024';
                // $config['max_height'] = '768';

                $this->load->library('upload', $config);
                if ($this->upload->do_upload('image')) {
                    $old_image = $data['user_db']['user_img_profile'];
                    // check if current image is default.jpg
                    if ($old_image != 'default.jpg') {
                        unlink(FCPATH . 'assets/img/profile/' . $old_image);
                    }
                    $new_image = $this->upload->data('file_name');
                    $this->db->set('user_img_profile', $new_image);
                    // error upload file / image 
                } else {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">' .
                        $this->upload->display_errors() . '</div>');
                    redirect('user');
                }
            }


            $this->db->set('user_name', $name);
            $this->db->where('user_email', $email);
            $this->db->update('user_db');

            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
            Your profile has been updated! </div>');
            redirect('user');
        }
    }

    public function confSecurity()
    {
        $data['title'] = 'Change Password';
        $data['user'] = $this->db->get_where('user_db', ['user_email' =>
        $this->session->userdata('user_email')])->row_array();

        $this->form_validation->set_rules('currentPwd', 'Current Password', 'required|trim');
        $this->form_validation->set_rules('newPwd', 'New Password', 'required|trim|min_length[6]|matches[repeatPwd]');
        $this->form_validation->set_rules('repeatPwd', 'Confirm New Password', 'required|trim|min_length[6]|matches[newPwd]');


        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('user/confSecurity', $data);
            $this->load->view('templates/footer');
        } else {
            $currentPwd = $this->input->post('currentPwd');
            $newPwd = $this->input->post('newPwd');
            // wrong password
            if (!password_verify($currentPwd, $data['user']['user_pwd'])) {
                $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
                Wrong Current Password! Please Try Again. </div>');
                redirect('user/confSecurity');
            } else {
                // current password == new password 
                if ($currentPwd == $newPwd) {
                    $this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
                    The new password cannot be same as the current password! </div>');
                    redirect('user/confSecurity');
                } else {
                    // password ok
                    $pwd_hash = password_hash($newPwd, PASSWORD_DEFAULT);
                    $this->db->set('user_pwd', $pwd_hash);
                    $this->db->where('user_email', $this->session->userdata('user_email'));
                    $this->db->update('user_db');

                    $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
                    Successfully! Password Changed. </div>');
                    redirect('user/confSecurity');
                }
            }
        }
    }
}
