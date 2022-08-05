<?php

class Model_chat extends CI_Model
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
        $sql = "SELECT COUNT(*) AS cnt FROM users WHERE token = ?";

        $param = array($token);

        $query = $this->db->query($sql, $param);

        return $query->row_array();
    }

    function check_expired($now, $token)
    {
        $sql = "SELECT (CAST(? AS DATE) - CAST(last_login AS DATE)) AS expired FROM users WHERE token = ?";

        $param = array($now, $token);

        $query = $this->db->query($sql, $param);

        return $query->row_array();
    }

    function get_chat($sender, $receiver)
    {
        $sql = "SELECT * FROM chat WHERE sender = ? AND receiver = ?";

        $param = array($sender, $receiver);

        $query = $this->db->query($sql, $param);

        return $query->row_array();
    }

    function get_read_by($id_chat)
    {
        $sql = "SELECT * FROM chat WHERE id = ?";

        $param = array($id_chat);

        $query = $this->db->query($sql, $param);

        return $query->row_array();
    }

    function get_chat_detail($id_chat)
    {
        $sql = "SELECT * FROM chat_detail WHERE id_chat = ? ORDER BY created_date ASC";

        $param = array($id_chat);

        $query = $this->db->query($sql, $param);

        return $query->result_array();
    }

    function get_list_chat($receiver)
    {
        $sql = "SELECT
        cd.id_chat,
        cd.message,
        (CASE WHEN cd.status = '0' THEN 'Belum Dibaca' ELSE 'Sudah Dibaca' END) AS status_read,
        u.name
        FROM chat_detail AS cd
        JOIN chat AS c ON c.id = cd.id_chat
        JOIN users AS u ON u.phone = cd.created_by
        WHERE cd.read_by = ?
        ORDER BY c.created_date DESC";

        $param = array($receiver);

        $query = $this->db->query($sql, $param);

        return $query->result_array();
    }
}
