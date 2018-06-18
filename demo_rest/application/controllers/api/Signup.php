<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

//include Rest Controller library
require APPPATH . '/libraries/REST_Controller.php';

class Signup extends REST_Controller {
	
	function __construct() {
		parent::__construct();

        $this->load->model(array('user_model' => 'user'));

        $this->r_data['success'] = 0;
        $this->r_data['message'] = '';  
	}

    function paramValidation($paramarray, $data) {
        $NovalueParam = array();
        foreach ($paramarray as $val) {
            if ($data[$val] == '') {
                $NovalueParam[] = $val;
            }
        }
        if (is_array($NovalueParam) && count($NovalueParam) > 0) {
            $this->r_data['message'] = 'Sorry, that is not valid input. You missed ' . implode(', ', $NovalueParam) . ' parameters';
        } else {
            $this->r_data['success'] = 1;
        }
        return $this->r_data;
    }

	public function index_post() {
        //echo '<pre>'; print_r($_POST); die;
        $userData = array();
        $userData['first_name'] = $this->post('first_name');
        $userData['last_name']  = $this->post('last_name');
        $userData['email']      = $this->post('email');
        $userData['password']   = $this->post('password');
        $userData['is_social']  = $this->post('is_social') ? $this->post('is_social') : 0;

        $key = array('first_name', 'last_name', 'email', 'password');
        $validation = $this->paramValidation($key, $userData);
        if ($validation['success'] == 0)
            $this->response($this->r_data, REST_Controller::HTTP_BAD_REQUEST);

        $table = $this->common->getUserTable();
        
        if ($userData['is_social'] == 0) {
            // check email exist...
            $checkEmail = $this->common->checkExist('email', $userData['email'], $table);
            if (!empty($checkEmail)) {
                $this->response([
                    'success' => 0,
                    'message' => 'Email ID already exist'
                ], REST_Controller::HTTP_BAD_REQUEST);
            }
        }    

        if ($userData['is_social'] == 1) {
            $socialData = $this->common->select_row($this->common->getSocialUsersTable(), ['oauth_uid' => $this->post('oauth_uid')]);
            if (!empty($socialData)) {
                // Add user session
                $this->r_data['token'] = $this->common->getSecureKey();
                $user_session = array(
                    'user_id'    => $socialData->user_id,
                    'token'      => $this->r_data['token'],
                    'start_date' => DATETIME
                );
                $this->r_data['success'] = 1;
                $this->r_data['message'] = 'Login Successful.';
                $this->r_data['secret_log_id'] = $this->common->insertUserSession($user_session);

                $getData = $this->common->getUserById($socialData->user_id);
                $this->r_data['data'] = $getData;

                $this->response($this->r_data, REST_Controller::HTTP_OK);
            }
        }            

        if (isset($_FILES['picture']) && $_FILES['picture']['error'] == 0) {

            $target_dir = PROFILE_PIC;

            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $target_file = $target_dir . basename($_FILES["picture"]["name"]);
            $extension   = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

            $getStr = $this->common->generatePassword(10);
            $image_name  = 'PROFILE-'.time().'-'.$getStr.'.'.$extension;
            $target_dir .= $image_name;

            $this->r_data['image'] = FALSE;

            if (move_uploaded_file($_FILES["picture"]["tmp_name"], $target_dir)) {
                $this->r_data['image']  = TRUE;
                $userData['profilepic'] = $image_name;
            }
        }

        $userData['password'] = md5($this->post('password'));
		$login = $this->user->addUser($userData);
        
        if ($login) {

            if (isset($_POST['is_social']) && $_POST['is_social'] == 1) {
                $socialData['user_id']        = $login;
                $socialData['oauth_provider'] = $this->post('oauth_provider');
                $socialData['oauth_uid']      = $this->post('oauth_uid');

                $this->common->insert($this->common->getSocialUsersTable(), $socialData);
            }

            // Add user session
            $this->r_data['token'] = $this->common->getSecureKey();
            
            $user_session = array(
                'user_id' 	 => $login,
                'token'      => $this->r_data['token'],
                'start_date' => DATETIME
            );

            $this->r_data['success'] = 1;
            $this->r_data['message'] = 'Login Successful.';
            $this->r_data['secret_log_id'] = $this->common->insertUserSession($user_session);
            $this->r_data['data'] = $this->common->getUserById($login);

            $this->response($this->r_data, REST_Controller::HTTP_OK);
        } else {
        	$this->response([
                'success'  => 0,
                'message' => 'Oops! something went wrong'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }        
	}
}