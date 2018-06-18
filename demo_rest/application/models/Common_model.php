<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Common_model extends CI_Model {    
    
    private $users        = 'user_master';
    private $user_session = 'user_session';        
    private $social_users = 'social_users';    

    public function getUserTable() {
        return $this->users;
    }

    public function getUserSessionTable() {
        return $this->user_session;
    }

    public function getSocialUsersTable() {
        return $this->social_users;
    } 

    // fetching result from table
    public function select_result($table_name,$where = [], $limit='', $offset='') {
        return $this->db->get_where($table_name,$where, $limit='', $offset='')->result();
    }

    // fetching row from table
    public function select_row($table_name,$where) {
        return $this->db->get_where($table_name,$where)->row();
    }

    /**
     * Insert into table
     */
    public function insert($table, $data) {
        $this->db->insert($table, $data);
        return $this->db->insert_id();
    }

    /**
     * Update 
     */
    public function update($field, $field_value, $table, $data) {
        $this->db->where($field, $field_value);

        $data['updated_at'] = DATETIME;
        return $this->db->update($table, $data);
    }

    /**
     * Delete
     */
    public function delete($field, $field_value, $table) {
        $this->db->where($field, $field_value);
        return $this->db->delete($table);
    }

    /**
     * check if existing
     */
    public function checkExist($field, $value, $table) {
        $this->db->where($field, $value);
        return $this->db->get($table)->row();
    }
    
    /**
     * return user by id
     */
    public function getUserById($id) {
        $this->db->select('*');
        $this->db->where('user_id', $id);
        $data = $this->db->get($this->users)->row();

        if ($data) {            
            $data->image_path = base_url('assets/images/default.png');
            if ($data->profilepic != '')
                $data->image_path = base_url() . PROFILE_PIC . $data->profilepic;
        }
        return $data;
    }

    /**
     * Get secure key Token
     */
    public function getSecureKey() {
        $string = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $stamp = time();
        $secure_key = $pre = $post = '';
        for ($p = 0; $p <= 10; $p++) {
            $pre .= substr($string, rand(0, strlen($string) - 1), 1);
        }

        for ($i = 0; $i < strlen($stamp); $i++) {
            $key = substr($string, substr($stamp, $i, 1), 1);
            $secure_key .= (rand(0, 1) == 0 ? $key : (rand(0, 1) == 1 ? strtoupper($key) : rand(0, 9)));
        }

        for ($p = 0; $p <= 10; $p++) {
            $post .= substr($string, rand(0, strlen($string) - 1), 1);
        }
        return $pre . '-' . $secure_key . $post;
    }

    /**
     * Add user session data
     */
    public function insertUserSession($data) {
        $this->db->insert($this->user_session, $data);
        return $this->db->insert_id();
    }

    /**
     * Get user session data
     */
    public function getUserSession($user_id, $token) {
        $this->db->where('is_active', 1);
        $this->db->where('user_id', $user_id);
        $this->db->where('token', $token);
        return $this->db->get($this->user_session)->row();
    }

    /**
     * Get current user session data
     */
    public function getSessionInfo($secret_log_id) {
        $this->db->where('is_active', 1);
        $this->db->where('session_id', $secret_log_id);
        return $this->db->get($this->user_session)->row();
    }

    /**
     * Logout session data
     */
    public function logoutUser($secret_log_id) {
        $data = array('is_active' => 0, 'end_date' => DATETIME);
        $this->db->where('session_id', $secret_log_id);
        return $this->db->update($this->user_session, $data);
    }

    /**
     * Delete users session data
     */
    public function deleteUserSession($user_id) {
        $this->db->where('user_id', $user_id);
        return $this->db->delete($this->user_session);
    }

    /**
     * Generate random alphanumeric string
     */
    public function generatePassword($length = 7) {
        $post = '';
        $string = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($p = 0; $p <= $length; $p++) {
            $post .= substr($string, rand(0, strlen($string) - 1), 1);
        }
        return $post;
    } 
    
}
