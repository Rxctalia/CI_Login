<?php
defined('BASEPATH') or exit('No direct script access allowed');

class translator extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        is_logged_in();
    }

    public function index()
    {
        $data['title'] = 'My Project';
        $data['user_db'] = $this->db->get_where('user_db', ['user_email' =>
        $this->session->userdata('user_email')])->row_array();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('templates/topbar', $data);
        $this->load->view('translator/index', $data);
        $this->load->view('templates/footer');
    }
}
