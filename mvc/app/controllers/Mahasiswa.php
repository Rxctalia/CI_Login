<?php

class Mahasiswa extends Controller
{
    public function index()
    {
        $data['title'] = 'Data Mahasiswa';
        $data['mhs'] = $this->model('mahasiswa_Model')->getAllMahasiswa();
        $this->view('templates/header', $data);
        $this->view('mahasiswa/index', $data);
        $this->view('templates/footer');
    }

    public function detail($id)
    {
        $data['title'] = 'Detail Mahasiswa';
        $data['mhs'] = $this->model('mahasiswa_Model')->getMahasiswaById($id);
        $this->view('templates/header', $data);
        $this->view('mahasiswa/detail', $data);
        $this->view('templates/footer');
    }

    public function tambah()
    {
        if ($this->model('mahasiswa_Model')->tambahDataMahasiswa($_POST) > 0) {
            Flasher::setFlash(' berhasil ', ' ditambahkan. ', 'success');
            header('Location:' . BASEURL . '/mahasiswa');
            exit;
        } else {
            Flasher::setFlash(' gagal ', ' ditambahkan. ', 'danger');
            header('Location:' . BASEURL . '/mahasiswa');
            exit;
        }
    }

    public function edit()
    {
        if ($this->model('mahasiswa_Model')->editDataMahasiswa($_POST) > 0) {
            Flasher::setFlash(' berhasil', ' diubah. ', 'success');
            header('Location:' . BASEURL . '/mahasiswa');
            exit;
        } else {
            Flasher::setFlash(' gagal ', ' diubah. ', 'danger');
            header('Location:' . BASEURL . '/mahasiswa');
            exit;
        }
    }

    public function hapus($id)
    {
        if ($this->model('mahasiswa_Model')->hapusDataMahasiswa($id) > 0) {
            Flasher::setFlash(' berhasil', ' dihapus. ', 'success');
            header('Location:' . BASEURL . '/mahasiswa');
            exit;
        } else {
            Flasher::setFlash(' gagal ', ' dihapus. ', 'danger');
            header('Location:' . BASEURL . '/mahasiswa');
            exit;
        }
    }

    public function getEdit()
    {
        echo json_encode($this->model('mahasiswa_Model')->getMahasiswaById($_POST['id']));
    }

    public function search()
    {
        $data['title'] = 'Data Mahasiswa';
        $data['mhs'] = $this->model('mahasiswa_Model')->searchDataMahasiswa();
        $this->view('templates/header', $data);
        $this->view('mahasiswa/index', $data);
        $this->view('templates/footer');
    }
}
