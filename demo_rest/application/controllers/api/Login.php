<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

//include Rest Controller library
require APPPATH . '/libraries/REST_Controller.php';

class Login extends REST_Controller {
	
	function __construct() {
		parent::__construct();

        $this->load->model(array('user_model' => 'user'));
	}

	public function index_post() {		

		$email    = $this->post('email');
		$password = $this->post('password');

		$login = $this->user->getUserLogin($email, md5($password));

        //echo $this->db->last_query();
        //echo '<pre>'; print_r($login); die;
        if ($login) {

        	// delete all user sessions
        	//$this->common->deleteUserSession($login->user_id);

            if ($this->post('lattitude') && $this->post('longitude')) {
                $userData['lattitude'] = $this->post('lattitude');
                $userData['longitude'] = $this->post('longitude');
                $this->user->updateUserData($userData, $login->user_id);
            }

            // Add new user session
            $this->r_data['token'] = $this->common->getSecureKey();
            $user_session = array(
                'user_id' 	 => $login->user_id,
                'token'      => $this->r_data['token'],
                'start_date' => DATETIME
            );
            $this->r_data['success'] = 1;
            $this->r_data['message'] = 'Login Successful.';
            $this->r_data['secret_log_id'] = $this->common->insertUserSession($user_session);
            $this->r_data['data'] = $this->common->getUserById($login->user_id);

            $this->response($this->r_data, REST_Controller::HTTP_OK);
        } else {
        	$this->response([
                'success'  => 0,
                'message' => 'Invalid credentials'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }        
	}
}

