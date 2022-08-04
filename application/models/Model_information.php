<?php

class Model_information extends CI_Model
{

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

    function check_token($token)
    {
        $sql = "SELECT COUNT(*) AS cnt FROM client_member WHERE token = ?";

        $param = array($token);

        $query = $this->db->query($sql, $param);

        return $query->row_array();
    }

    function check_expired($now, $token)
    {
        $sql = "SELECT (?::DATE - last_login::DATE) AS expired FROM client_member WHERE token = ?";

        $param = array($now, $token);

        $query = $this->db->query($sql, $param);

        return $query->row_array();
    }

    function check_username($username)
    {
        $sql = "SELECT * FROM client_member WHERE cif_no = ?";

        $param = array($username);

        $query = $this->db->query($sql, $param);

        return $query->row_array();
    }
}
