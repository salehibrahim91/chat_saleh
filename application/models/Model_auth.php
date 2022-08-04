<?php

class Model_auth extends CI_Model
{
	function check_username($username)
	{
		$sql = "SELECT * FROM client_member WHERE cif_no = ?";

		$param = array($username);

		$query = $this->db->query($sql, $param);

		return $query->row_array();
	}

	function check_account($username, $password)
	{
		$sql = "SELECT * FROM client_member WHERE cif_no = ? AND password = ?";

		$param = array($username, $password);

		$query = $this->db->query($sql, $param);

		return $query->row_array();
	}

	function insert($table, $data)
	{
		$this->db->trans_begin();
		$this->db->insert($table, $data);

		if ($this->db->trans_status() === TRUE) {
			$this->db->trans_commit();

			return TRUE;
		} else {
			$this->db->trans_rollback();

			return FALSE;
		}
	}

	function update($table, $data, $param)
	{
		$this->db->trans_begin();
		$this->db->update($table, $data, $param);

		if ($this->db->trans_status() === TRUE) {
			$this->db->trans_commit();

			return TRUE;
		} else {
			$this->db->trans_rollback();

			return FALSE;
		}
	}
}
