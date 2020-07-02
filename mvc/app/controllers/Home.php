<?php

class Home extends Controller
{
    public function index()
    {

        $data['title'] = 'Welcome to  my website';
        $data['nama'] = $this->model('user_Model')->getUser();

        $this->view('templates/header', $data);
        $this->view('home/index', $data);
        $this->view('templates/footer');
    }
}
