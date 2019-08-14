<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller{

  public function __construct(){
		parent::__construct();
    $this->load->model('M_admin');
    $this->load->library('upload');
	}

  public function index(){
    if($this->session->userdata('status') == 'login' && $this->session->userdata('role') == 1){
      //$data['avatar'] = $this->M_admin->get_data_gambar('tb_upload_gambar_user',$this->session->userdata('name'));
      $data['stokBarangMasuk'] = $this->M_admin->getTotalTransaksi('masuk');
      $data['stokBarangKeluar'] = $this->M_admin->getTotalTransaksi('keluar');
      $data['dataUser'] = $this->M_admin->numrows('user');
	  $barang = $this->M_admin->select('barang');
	  $masukArray = array();
	  $keluarArray = array();
	  $i=0;
	  $masukArray[$i]=0;
	  $keluarArray[$i]=0;
	  foreach($barang as $b){
		$masuk = $this->M_admin->getJmlTransaksi($b->id_barang, 'masuk');
		if($masuk > 0){
			$masukArray[$i] = $masuk;
		}
		$keluar = $this->M_admin->getJmlTransaksi($b->id_barang, 'keluar');
		if($keluar> 0){
			$keluarArray[$i] = $keluar;
		}
		$i++;
	  }
	  //var_dump($keluarArray);
	  $data['masuk'] = $masukArray;
	  $data['keluar'] = $keluarArray;
	  $data['list_data'] = $this->M_admin->select('barang');
      $this->load->view('admin/index',$data);
    }else {
      $this->load->view('login/login');
    }
  }

  public function sigout(){
    session_destroy();
    redirect('login');
  }

  ####################################
              // Profile
  ####################################

  public function profile()
  {
    $data['token_generate'] = $this->token_generate();
    //$data['avatar'] = $this->M_admin->get_data_gambar('tb_upload_gambar_user',$this->session->userdata('name'));
    $this->session->set_userdata($data);
    $this->load->view('admin/profile',$data);
  }

  public function token_generate()
  {
    return $tokens = md5(uniqid(rand(), true));
  }

  private function hash_password($password)
  {
    return password_hash($password,PASSWORD_DEFAULT);
  }

  public function proses_new_password()
  {
    //$this->form_validation->set_rules('email','Email','required');
    $this->form_validation->set_rules('new_password','New Password','required');
    $this->form_validation->set_rules('confirm_new_password','Confirm New Password','required|matches[new_password]');

    if($this->form_validation->run() == TRUE)
    {
      if($this->session->userdata('token_generate') === $this->input->post('token'))
      {
        $username = $this->input->post('username');
        //$email = $this->input->post('email');
        $new_password = $this->input->post('new_password');

        $data = array(
            'email'    => $email,
            'password' => $this->hash_password($new_password)
        );

        $where = array(
            'id' =>$this->session->userdata('id')
        );

        $this->M_admin->update_password('user',$where,$data);

        $this->session->set_flashdata('msg_berhasil','Password Telah Diganti');
        redirect(base_url('admin/profile'));
      }
    }else {
      $this->load->view('admin/profile');
    }
  }



  ####################################
           // End Profile
  ####################################



  ####################################
              // Users
  ####################################
  public function users()
  {
    $data['list_users'] = $this->M_admin->kecuali('user',$this->session->userdata('name'));
    $data['token_generate'] = $this->token_generate();
    
    $this->session->set_userdata($data);
    $this->load->view('admin/users',$data);
  }

  public function form_user()
  {
    $data['list_satuan'] = $this->M_admin->select('barang');
    $data['token_generate'] = $this->token_generate();
   // $data['avatar'] = $this->M_admin->get_data_gambar('tb_upload_gambar_user',$this->session->userdata('name'));
    $this->session->set_userdata($data);
    $this->load->view('admin/form_users/form_insert',$data);
  }

  public function update_user()
  {
    $id = $this->uri->segment(3);
    $where = array('id' => $id);
    $data['token_generate'] = $this->token_generate();
    $data['list_data'] = $this->M_admin->get_data('user',$where);
    //$data['avatar'] = $this->M_admin->get_data_gambar('tb_upload_gambar_user',$this->session->userdata('name'));
    $this->session->set_userdata($data);
    $this->load->view('admin/form_users/form_update',$data);
  }

  public function proses_delete_user()
  {
    $id = $this->uri->segment(3);
    $where = array('id' => $id);
    $this->M_admin->delete('user',$where);
    $this->session->set_flashdata('msg_berhasil','User Behasil Di Delete');
    redirect(base_url('admin/users'));

  }

  public function proses_tambah_user()
  {
    $this->form_validation->set_rules('username','Username','required');
    $this->form_validation->set_rules('email','Email','required|valid_email');
    $this->form_validation->set_rules('password','Password','required');
    $this->form_validation->set_rules('confirm_password','Confirm password','required|matches[password]');

    if($this->form_validation->run() == TRUE)
    {
      if($this->session->userdata('token_generate') === $this->input->post('token'))
      {

        $username     = $this->input->post('username',TRUE);
        $email        = $this->input->post('email',TRUE);
        $password     = $this->input->post('password',TRUE);
        $role         = $this->input->post('role',TRUE);

        $data = array(
              'username'     => $username,
              'email'        => $email,
              'password'     => $this->hash_password($password),
              'role'         => $role,
        );
        $this->M_admin->insert('user',$data);

        $this->session->set_flashdata('msg_berhasil','User Berhasil Ditambahkan');
        redirect(base_url('admin/form_user'));
        }
      }else {
        //$data['avatar'] = $this->M_admin->get_data_gambar('tb_upload_gambar_user',$this->session->userdata('name'));
        $this->load->view('admin/form_users/form_insert',$data);
    }
  }

  public function proses_update_user()
  {
    $this->form_validation->set_rules('username','Username','required');
    $this->form_validation->set_rules('email','Email','required|valid_email');

    
    if($this->form_validation->run() == TRUE)
    {
      if($this->session->userdata('token_generate') === $this->input->post('token'))
      {
        $id           = $this->input->post('id',TRUE);        
        $username     = $this->input->post('username',TRUE);
        $email        = $this->input->post('email',TRUE);
        $role         = $this->input->post('role',TRUE);

        $where = array('id' => $id);
        $data = array(
              'username'     => $username,
              'email'        => $email,
              'role'         => $role,
        );
        $this->M_admin->update('user',$data,$where);
        $this->session->set_flashdata('msg_berhasil','Data User Berhasil Diupdate');
        redirect(base_url('admin/users'));
       }
    }else{
        $this->load->view('admin/form_users/form_update');
    }
  }


  ####################################
           // End Users
  ####################################



  ####################################
        // DATA BARANG
  ####################################

  public function form_barangmasuk()
  {
    //$data['list_satuan'] = $this->M_admin->select('barang');
	$data['list_gudang'] = $this->M_admin->getArray('gudang');
    //$data['avatar'] = $this->M_admin->get_data_gambar('tb_upload_gambar_user',$this->session->userdata('name'));
    $this->load->view('admin/form_barangmasuk/form_insert',$data);
  }

  public function tabel_barangmasuk()
  {
    $data['list_data'] = $this->M_admin->getTransaksiMasuk();
              
    $this->load->view('admin/tabel/tabel_barangmasuk',$data);
  }

  /*public function update_barang($id_transaksi)
  {
    $where = array('id_transaksi' => $id_transaksi);
    $data['data_barang_update'] = $this->M_admin->get_data('tb_barang_masuk',$where);
    $data['list_satuan'] = $this->M_admin->select('barang');
    //$data['avatar'] = $this->M_admin->get_data_gambar('tb_upload_gambar_user',$this->session->userdata('name'));
    $this->load->view('admin/form_barangmasuk/form_update',$data);
  }

  public function delete_barang($id_transaksi)
  {
    $where = array('id_transaksi' => $id_transaksi);
    $this->M_admin->delete('tb_barang_masuk',$where);
    redirect(base_url('admin/tabel_barangmasuk'));
  }*/



  public function proses_databarang_masuk_insert()
  {
    $this->form_validation->set_rules('id_transaksi','ID Transaksi','required');
	$this->form_validation->set_rules('lokasi','Lokasi','required');
    $this->form_validation->set_rules('kode_barang','Kode Barang','required|is_unique[barang.id_barang]',
	array(
        'is_unique'     => 'Kode Barang sudah terdaftar!')
	);
    $this->form_validation->set_rules('nama_barang','Nama Barang','required');
    $this->form_validation->set_rules('jumlah','Jumlah','required|is_natural');

    if($this->form_validation->run() == TRUE)
    {
      $id_transaksi = $this->input->post('id_transaksi',TRUE);
      $tanggal      = $this->input->post('tanggal',TRUE);
      $lokasi       = $this->input->post('lokasi',TRUE);
      $kode_barang  = $this->input->post('kode_barang',TRUE);
      $nama_barang  = $this->input->post('nama_barang',TRUE);
      $jumlah       = $this->input->post('jumlah',TRUE);
	  $merk       = $this->input->post('merk',TRUE);
	  $tipe       = $this->input->post('tipe',TRUE);
	  

      $data_barang = array(
            'id_barang'		=> $kode_barang,
            'nama_barang'	=> $nama_barang,
            'jumlah'		=> $jumlah,
			'merk'			=> $merk,
			'tipe'			=> $tipe
      );
	  $data_transaksi = array(
			'id_transaksi' 	=> $id_transaksi,
			'jenis_transaksi' => "masuk",
			'jumlah_barang'	=> $jumlah,
            'tanggal'       => $tanggal,
			'id_barang'		=> $kode_barang,
			'id_gudang'		=> $lokasi
		);
      $this->M_admin->insert('barang',$data_barang);
	  $this->M_admin->insert('transaksi',$data_transaksi);

      $this->session->set_flashdata('msg_berhasil','Data Barang Berhasil Ditambahkan');
      redirect(base_url('admin/form_barangmasuk'));
    }else {
      $data['list_satuan'] = $this->M_admin->select('barang');
	  $data['list_gudang'] = $this->M_admin->getArray('gudang');
      $this->load->view('admin/form_barangmasuk/form_insert',$data);
    }
  }

  /*public function proses_databarang_masuk_update()
  {
    $this->form_validation->set_rules('lokasi','Lokasi','required');
    $this->form_validation->set_rules('kode_barang','Kode Barang','required');
    $this->form_validation->set_rules('nama_barang','Nama Barang','required');
    $this->form_validation->set_rules('jumlah','Jumlah','required');

    if($this->form_validation->run() == TRUE)
    {
      $id_transaksi = $this->input->post('id_transaksi',TRUE);
      $tanggal      = $this->input->post('tanggal',TRUE);
      $lokasi       = $this->input->post('lokasi',TRUE);
      $kode_barang  = $this->input->post('kode_barang',TRUE);
      $nama_barang  = $this->input->post('nama_barang',TRUE);
      $satuan       = $this->input->post('satuan',TRUE);
      $jumlah       = $this->input->post('jumlah',TRUE);

      $where = array('id_transaksi' => $id_transaksi);
      $data = array(
            'id_transaksi' => $id_transaksi,
            'tanggal'      => $tanggal,
            'lokasi'       => $lokasi,
            'kode_barang'  => $kode_barang,
            'nama_barang'  => $nama_barang,
            'satuan'       => $satuan,
            'jumlah'       => $jumlah
      );
      $this->M_admin->update('tb_barang_masuk',$data,$where);
      $this->session->set_flashdata('msg_berhasil','Data Barang Berhasil Diupdate');
      redirect(base_url('admin/tabel_barangmasuk'));
    }else{
	
      $this->load->view('admin/form_barangmasuk/form_update');
    }
  }*/
  

  public function tabel_stokbarang()
  {
    $data['list_data'] = $this->M_admin->select('barang');
    
    $this->load->view('admin/tabel/tabel_stokbarang',$data);
  }

  public function update_barang()
  {
    $uri = $this->uri->segment(3);
    $where = array('id_barang' => $uri);
    $data['data_satuan'] = $this->M_admin->get_data('barang',$where);
    
    $this->load->view('admin/form_satuan/form_update',$data);
  }

  public function delete_barang()
  {
    $uri = $this->uri->segment(3);
    $where = array('id_barang' => $uri);
    $this->M_admin->delete('barang',$where);
    redirect(base_url('admin/tabel_stokbarang'));
  }

  public function tambah_stok()
  {
	$this->form_validation->set_rules('id_transaksi','ID Transaksi','required');
	$this->form_validation->set_rules('tanggal','Tanggal','required');
    $this->form_validation->set_rules('id_barang','Kode Barang','required');
    $this->form_validation->set_rules('nama_barang','Nama Barang','required');
    $this->form_validation->set_rules('jumlah','Jumlah','required');
    if($this->form_validation->run() ==  TRUE)
    {
      $id_transaksi = $this->input->post('id_transaksi',TRUE);
	  $tanggal 		= $this->input->post('tanggal',TRUE);
	  $id_barang   = $this->input->post('id_barang' ,TRUE);
      $nama_barang = $this->input->post('nama_barang' ,TRUE);
	  $jumlah		= $this->input->post('jumlah' ,TRUE);

      $where = array(
            'id_barang' => $id_barang
      );

      $data = array(
            'jumlah' => $jumlah            
      );
	  $data_transaksi = array(
			'id_transaksi' 	=> $id_transaksi,
			'jenis_transaksi' => "masuk",
			'jumlah_barang'	=> $jumlah,
            'tanggal'       => $tanggal,
			'id_barang'		=> $id_barang
		);
      $this->M_admin->addStock('barang',$jumlah,$id_barang);
	  //$this->M_admin->insert('transaksi',$data_transaksi);

      $this->session->set_flashdata('msg_berhasil','Stok Data Berhasil Di-update');
      redirect(base_url('admin/tabel_stokbarang'));
    }else {
		
		$uri = $this->uri->segment(3);
    $where = array('id_barang' => $uri);
    $data['data_satuan'] = $this->M_admin->get_data('barang',$where);
    
    $this->load->view('admin/form_satuan/form_update',$data);
    }
  }

  ####################################
            // END DATA BARANG
  ####################################


  ####################################
     // DATA MASUK KE DATA KELUAR
  ####################################

  public function barang_keluar()
  {
    $uri = $this->uri->segment(3);
    $where = array( 'id_barang' => $uri);
    $data['list_data'] = $this->M_admin->get_data('barang',$where);
	$data['list_gudang'] = $this->M_admin->getArray('gudang');
    //$data['list_satuan'] = $this->M_admin->select('barang');
    
    $this->load->view('admin/perpindahan_barang/form_update',$data);
  }

  public function proses_data_keluar()
  {
    $this->form_validation->set_rules('id_transaksi','ID Transaksi','required');
	$this->form_validation->set_rules('tanggal','Tanggal','required');
	$this->form_validation->set_rules('lokasi','Tujuan','required');
    $this->form_validation->set_rules('id_barang','Kode Barang','required');
    $this->form_validation->set_rules('nama_barang','Nama Barang','required');
    $this->form_validation->set_rules('jumlah','Jumlah','required');
	//var_dump($id_barang);
    if($this->form_validation->run() == TRUE)
    {
      $id_transaksi   = $this->input->post('id_transaksi',TRUE);
      $tanggal		  = $this->input->post('tanggal',TRUE);
      $lokasi         = $this->input->post('lokasi',TRUE);
      $id_barang    = $this->input->post('id_barang',TRUE);
      $nama_barang    = $this->input->post('nama_barang',TRUE);
      $jumlah         = $this->input->post('jumlah',TRUE);

      $where = array( 'id_transaksi' => $id_transaksi);
      $data_transaksi = array(
              'id_transaksi' => $id_transaksi,
              'tanggal' => $tanggal,
			  'jenis_transaksi' => "keluar",
              'id_gudang' => $lokasi,
              'id_barang' => $id_barang,
			  'jumlah_barang' => $jumlah
      );
	  $data_update = array(
			'jumlah' => $jumlah
	  );
        $this->M_admin->insert('transaksi',$data_transaksi);
		$this->M_admin->substrStock('barang', $jumlah, $id_barang);
        $this->session->set_flashdata('msg_berhasil_keluar','Data Berhasil Keluar');
        redirect(base_url('admin/tabel_barangmasuk'));
    }else {
      $this->load->view('perpindahan_barang/form_update/');
    }

  }
  ####################################
    // END DATA MASUK KE DATA KELUAR
  ####################################


  ####################################
        // DATA BARANG KELUAR
  ####################################

  public function tabel_barangkeluar()
  {
    $data['list_data'] = $this->M_admin->getTransaksiKeluar();
    
    $this->load->view('admin/tabel/tabel_barangkeluar',$data);
  }


}
?>
