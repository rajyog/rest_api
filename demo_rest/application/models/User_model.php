<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

    /*
     * get login user
     */
    public function getUserLogin($emailId, $Password) {
        $this->db->where('email', $emailId);
        $this->db->where('password', $Password);
        $this->db->where('status', 1);
        $this->db->where('is_deleted', 0);
        $data = $this->db->get($this->common->getUserTable())->row();

        if ($data) {
            $data->image_path = base_url('assets/images/default.png');
            if ($data->profilepic != '')
                $data->image_path = base_url() . PROFILE_PIC . $data->profilepic;
        }
        return $data;
    }

    /**
     * Add user
     */
    public function addUser($data) {
        $this->db->insert($this->common->getUserTable(), $data);
        return $this->db->insert_id();
    }

    /**
     * Update User data by id
     */
    public function updateUserData($data, $id) {
        $this->db->where('user_id', $id);
        $data['updated_at'] = DATETIME;
        return $this->db->update($this->common->getUserTable(), $data);
    }

    /**
     * delete user
     */
    public function deleteUser($id) {
        $this->db->where('user_id', $id);

        $data['is_deleted'] = 1;
        $data['deleted_at'] = DATETIME;
        return $this->db->update($this->common->getUserTable(), $data);
    }    

    /**
     * get All user data
     */
    public function getAllUser($order_by = '') {

        if ($order_by != '')
            $this->db->order_by('user_id', $order_by);

        //$this->db->where('is_deleted', 0);
        $data = $this->db->get($this->common->getUserTable())->result();

        foreach ($data as $key => $value) {
            $data[$key] = $value;
            $data[$key]->image_path = base_url('assets/images/default.png');
            if ($value->profilepic)
                $data[$key]->image_path = base_url() . PROFILE_PIC . $value->profilepic;
        }
        return $data;
    }
    

}
