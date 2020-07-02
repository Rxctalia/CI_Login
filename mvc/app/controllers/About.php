<?php

class About extends Controller
{
    public function index($nama = 'Ludfi Azimada', $pekerjaan = 'Full Stack Developer', $umur = '23')
    {
        $data['nama'] = $nama;
        $data['pekerjaan'] = $pekerjaan;
        $data['umur'] = $umur;

        $data['title'] = 'About Me';

        $this->view('templates/header', $data);
        $this->view('about/index', $data);
        $this->view('templates/footer');
    }
    public function page()
    {
        $this->view('about/page');
    }
}
