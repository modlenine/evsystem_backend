<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends MX_Controller {
    
    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Bangkok');
        $this->load->model("main_model" , "main");
    }
    

    public function index()
    {
        echo show_404();
    }

    public function getUserFromSchniderDatabase()
    {
        $this->main->getUserFromSchniderDatabase();
    }

    public function getUserFromIntranetDatabase()
    {
        $this->main->getUserFromIntranetDatabase();
    }

    public function saveCreatePassword()
    {
        $this->main->saveCreatePassword();
    }

    public function test()
    {
        echo date("Y-m-d H:i:s" , "1725006598");
        echo "<br>";
        echo date("Y-m-d H:i:s" , time());
        echo "<br>";
        echo time();
        echo "<br>";
        echo strtotime("+1 min");
    }

    public function updateUserActivate()
    {
        $this->main->updateUserActivate();
    }

    public function getCardData()
    {
        $this->main->getCardData();
    }

    public function getCardTransactionData()
    {
        $this->main->getCardTransactionData();
    }

    public function getUserInformation()
    {
        $this->main->getUserInformation();
    }

    public function fetchdataCharger()
    {
        $this->main->fetchdataCharger();
    }

    public function fetchdataCharger_admin()
    {
        $this->main->fetchdataCharger_admin();
    }

    public function fetchRealtimeDataCharger()
    {
        $this->main->fetchRealtimeDataCharger();
    }

    public function fetchRealtimeDataCharger_admin()
    {
        $this->main->fetchRealtimeDataCharger_admin();
    }

    public function resendEmail()
    {
        $this->main->resendEmail();
    }

}

/* End of file Controllername.php */


?>