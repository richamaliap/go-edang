<?php

class M_admin extends CI_Model
{

  public function insert($tabel,$data)
  {
    $this->db->insert($tabel,$data);
  }

  public function select($tabel)
  {
    $query = $this->db->get($tabel);
    return $query->result();
  }

  public function cek_jumlah($tabel,$id_transaksi)
  {
    return  $this->db->select('*')
               ->from($tabel)
               ->where('id_transaksi',$id_transaksi)
               ->get();

  }

  public function get_data_array($tabel,$id_transaksi)
  {
    $query = $this->db->select()
                      ->from($tabel)
                      ->where($id_transaksi)
                      ->get();
    return $query->result_array();
  }

  public function get_data($tabel,$id_transaksi)
  {
    $query = $this->db->select()
                      ->from($tabel)
                      ->where($id_transaksi)
                      ->get();
    return $query->result();
  }

  public function update($tabel,$data,$where)
  {
    $this->db->where($where);
    $this->db->update($tabel,$data);
  }
  
  public function addStock($tabel,$jumlah,$id_barang){
	$this->db->set("jumlah","jumlah + $jumlah", false);
    $this->db->where('id_barang',$id_barang);
    $this->db->update($tabel);	
  }
   public function substrStock($tabel,$jumlah,$id_barang){
	$this->db->set("jumlah","jumlah - $jumlah", false);
    $this->db->where('id_barang',$id_barang);
    $this->db->update($tabel);	
	//$query = "UPDATE $tabel SET jumlah = (jumlah - $jumlah) where id_barang = $id_barang";
	//return $query;
  }
  
  public function delete($tabel,$where)
  {
    $this->db->where($where);
    $this->db->delete($tabel);
  }

  public function mengurangi($tabel,$id_transaksi,$jumlah)
  {
    $this->db->set("jumlah","jumlah - $jumlah");
    $this->db->where('id_transaksi',$id_transaksi);
    $this->db->update($tabel);
  }

  public function update_password($tabel,$where,$data)
  {
    $this->db->where($where);
    $this->db->update($tabel,$data);
  }

  public function get_data_gambar($tabel,$username)
  {
    $query = $this->db->select()
                      ->from($tabel)
                      ->where('username_user',$username)
                      ->get();
    return $query->result();
  }

  public function sum($tabel,$field)
  {
    $query = $this->db->select_sum($field)
                      ->from($tabel)
                      ->get();
    return $query->result();
  }

  public function numrows($tabel)
  {
    $query = $this->db->select()
                      ->from($tabel)
                      ->get();
    return $query->num_rows();
  }

  public function kecuali($tabel,$username)
  {
    $query = $this->db->select()
                      ->from($tabel)
                      ->where_not_in('username',$username)
                      ->get();

    return $query->result();
  }
  public function getIdGudang($lokasi){
	  $this->db->select('*');
	  $this->db->from('gudang');
	 
	  $this->db->where("lokasi = '". $lokasi."'");
	  $query = $this->db->get();
	  return $query->row_array();
  }
  public function getArray($tabel)
  {
    $query = $this->db->get($tabel);
    return $query->result_array();
  }
  public function getTotalTransaksi($jenis){
	  $this->db->select_sum('t.jumlah_barang');
	  $this->db->from('transaksi t');
	  $this->db->join('barang b', 't.id_barang = b.id_barang', 'left');
	  $this->db->join('gudang g', 't.id_gudang = g.id_gudang', 'left');
	  $this->db->where("jenis_transaksi = '". $jenis."'");
	  $query = $this->db->get();
	  return $query->result();
  }  
  public function getTransaksiMasuk(){
	  $this->db->select('*');
	  $this->db->from('transaksi t');
	  $this->db->join('barang b', 't.id_barang = b.id_barang', 'left');
	  $this->db->join('gudang g', 't.id_gudang = g.id_gudang', 'left');
	  $this->db->where('jenis_transaksi = "masuk"');
	  $query = $this->db->get();
	  return $query->result_array();
  }
  public function getTransaksiKeluar(){
	  $this->db->select('*');
	  $this->db->from('transaksi t');
	  $this->db->join('barang b', 't.id_barang = b.id_barang', 'left');
	  $this->db->join('gudang g', 't.id_gudang = g.id_gudang', 'left');
	  $this->db->where('jenis_transaksi = "keluar"');
	  $query = $this->db->get();
	  return $query->result_array();
  }
  public function getJmlTransaksi($id_barang,$jenis){
	  $this->db->distinct();
	  $this->db->select('SUM(t.jumlah_barang) as jml');
	  $this->db->from('transaksi t');
	  $this->db->join('barang b', 't.id_barang = b.id_barang', 'left');
	  $this->db->join('gudang g', 't.id_gudang = g.id_gudang', 'left');
	  $this->db->where("t.jenis_transaksi = '". $jenis."'");
	  $this->db->where("t.id_barang = '". $id_barang."'");
	  $query = $this->db->get();
	  return $query->row()->jml;
  }
  public function getAllTransaksi(){
	  $this->db->distinct();
	  $this->db->select('*');
	  $this->db->from('barang b');
	  $this->db->join('transaksi t', 'b.id_barang = t.id_barang', 'left');
	  $this->db->join('gudang g', 't.id_gudang = g.id_gudang', 'left');
	  $this->db->order_by('b.id_barang');
	  $query = $this->db->get();
	  return $query->result_array();
  }
  public function getInvoiceKeluar($id_transaksi){
	  $this->db->select('*');
	  $this->db->from('transaksi t');
	  $this->db->join('barang b', 't.id_barang = b.id_barang', 'left');
	  $this->db->join('gudang g', 't.id_gudang = g.id_gudang', 'left');
	  $this->db->where('jenis_transaksi = "keluar"');
	  $this->db->where('id_transaksi', $id_transaksi);
	  $query = $this->db->get();
	  return $query->result();
  }

}



 ?>
