<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

//include Rest Controller library
require APPPATH . '/libraries/REST_Controller.php';

class User extends REST_Controller {	
    
    private $auth; 

	public function __construct() {
		parent::__construct();

        $this->auth = new stdClass();

        $this->load->model(array('user_model' => 'user'));

        $headers = $this->input->request_headers();
        //echo '<pre>'; print_r($headers); die;
        if (!isset($headers['Id']) || !isset($headers['Token'])) {            
            $this->response([
                'success' => 0,
                'message' => 'Authentication data required.'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
        
        $this->auth->id    = $headers['Id'];
        $this->auth->token = $headers['Token'];

        $session = $this->common->getUserSession($this->auth->id, $this->auth->token);

        //echo $this->db->last_query(); die;
        if (!isset($session->token) || $session->token !== $this->auth->token) {
            $this->response([
                'success' => 0,
                'message' => 'Authentication failed.'
            ], REST_Controller::HTTP_UNAUTHORIZED);
        }

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

	public function index_get($user_id = 0) {		

        if ($user_id == 0)
            $user_id = $this->auth->id;

        $data = $this->common->getUserById($user_id);

        if ($data) {
            $this->response([
                'success'  => 1,
                'message' => 'data fetched successfully.',
                'data'    => $data
            ], REST_Controller::HTTP_OK);
        }

        $this->response([
            'success'  => 0,
            'message' => 'No data found.'
        ], REST_Controller::HTTP_BAD_REQUEST);
	}

    public function all_get() {

        $data = $this->user->getAllUser();

        if ($data) {
            $this->response([
                'success'  => 1,
                'message' => 'data fetched successfully.',
                'data'    => $data
            ], REST_Controller::HTTP_OK);
        }

        $this->response([
            'success'  => 0,
            'message' => 'No data found.'
        ], REST_Controller::HTTP_BAD_REQUEST);
    }

    public function logout_get($secret_log_id = 0) {
        
        $session = $this->common->getSessionInfo($secret_log_id);
        //echo '<pre>'; print_r($session); die;
        if ($session) {
            if ($session->user_id != $this->auth->id) {
                $this->response([
                    'success'  => 0,
                    'message' => 'Secret log does not belongs to you.'
                ], REST_Controller::HTTP_BAD_REQUEST);
            }

            $this->common->logoutUser($secret_log_id);
            $this->response([
                'success'  => 1,
                'message' => 'Logout Successful.'
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'success'  => 0,
                'message' => 'Secret log not found.'
            ], REST_Controller::HTTP_BAD_REQUEST);
        }
    }    

    public function edit_put() {
        $userData = array(
            'first_name' => trim($this->put('first_name')),
            'last_name'  => trim($this->put('last_name'))
        );

        $userdata = array('first_name', 'last_name');
        $validation = $this->paramValidation($userdata, $userData);
        if ($validation['success'] == 0)
            $this->response($this->r_data, REST_Controller::HTTP_BAD_REQUEST);        

        $this->r_data['success'] = 0;
        $this->r_data['message'] = 'Oops something went wrong';                    
        $code = REST_Controller::HTTP_BAD_REQUEST;

        $userUpd = $this->user->updateUserData($userData, $this->auth->id);

        if ($userUpd) {
            $this->r_data['success'] = 1;
            $this->r_data['message'] = 'Profile updated successfully';
            $this->r_data['data']    = $this->common->getUserById($this->auth->id);
            $code = REST_Controller::HTTP_OK;
        }

        $this->response($this->r_data, $code);
    }

    public function remove_delete($user_id = 0) {
        $this->r_data['message'] = 'Please enter user id first';
        if ($user_id == 0)
            $this->response($this->r_data, REST_Controller::HTTP_BAD_REQUEST);

        $del = $this->user->deleteUser($user_id);

        if ($del) {
            $this->r_data['success'] = 1;
            $this->r_data['message'] = 'User deleted successfully';
            $code = REST_Controller::HTTP_OK;
        } else {
            $this->r_data['message'] = 'Oops! something went wrong';
            $code = REST_Controller::HTTP_BAD_REQUEST;
        }
        
        $this->response($this->r_data, $code);
    }
    
}
