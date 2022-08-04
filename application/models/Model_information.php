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
}
