<?php
defined('BASEPATH') or exit('No direct script access allowed');

class menu extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        is_logged_in();
    }

    public function index()
    {
        $data['title'] = 'Menu Management';
        $data['user'] = $this->db->get_where('user_db', ['user_email' =>
        $this->session->userdata('user_email')])->row_array();

        $data['menu_name'] = $this->db->get('user_menu')->result_array();

        $this->form_validation->set_rules('menu_name', 'Menu', 'required');

        if ($this->form_validation->run() ==  false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('menu/index', $data);
            $this->load->view('templates/footer');
        } else {
            $this->db->insert('user_menu', ['menu_name' => $this->input->post('menu_name')]);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
            New Menu Added! </div>');
            redirect('menu');
        }
    }

    public function submenu()
    {
        $data['title'] = 'Submenu Management';
        $data['user'] = $this->db->get_where('user_db', ['user_email' =>
        $this->session->userdata('user_email')])->row_array();

        $this->load->model('Menu_model', 'menu');

        $data['subMenu'] = $this->menu->get_subMenu();
        $data['user_menu'] = $this->db->get('user_menu')->result_array();

        $this->form_validation->set_rules('submenu_title', 'Title', 'required');
        $this->form_validation->set_rules('submenu_url', 'URL', 'required');
        $this->form_validation->set_rules('icon', 'Icon', 'required');
        $this->form_validation->set_rules('menu_id', 'Menu', 'required');

        // Add New Data
        if ($this->form_validation->run() == false) {
            $this->load->view('templates/header', $data);
            $this->load->view('templates/sidebar', $data);
            $this->load->view('templates/topbar', $data);
            $this->load->view('menu/submenu', $data);
            $this->load->view('templates/footer');
        } else {
            $data = [
                'submenu_title' => $this->input->post('submenu_title'),
                'menu_id' => $this->input->post('menu_id'),
                'submenu_url' => $this->input->post('submenu_url'),
                'icon' => $this->input->post('icon'),
                'is_active' => $this->input->post('is_active'),

            ];
            $this->db->insert('user_submenu', $data);
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
            New Submenu Added! </div>');
            redirect('menu/submenu');
        }
    }

    public function role()
    {
        $data['title'] = 'Role Management';
        $data['user'] = $this->db->get_where('user_db', ['user_email' =>
        $this->session->userdata('user_email')])->row_array();

        $data['role'] = $this->db->get('role_db')->result_array();

        // $this->form_validation->set_rules();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('menu/role', $data);
        $this->load->view('templates/footer');
    }
}
