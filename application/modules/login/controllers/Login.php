<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends MX_Controller {
    
    public function __construct()
    {
        parent::__construct();
        //Do your magic here
        $this->load->model("login_model" , "login");
    }
    

    public function index()
    {
        echo show_404();
    }

    public function saveLogin(){
        $this->login->saveLogin();
    }

    public function forgotpassword()
    {
        $this->login->forgotpassword();
    }

    public function create_newpassword()
    {
        $this->login->create_newpassword();
    }

}
/* End of file Controllername.php */
?>