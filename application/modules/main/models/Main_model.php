<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Main_model extends CI_Model {
    
    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Bangkok');
        $this->db_ev = $this->load->database("evcharger" , true);
    }

    public function getUserFromSchniderDatabase()
    {
        if(!empty($this->input->post("cardid"))){
            $cardid = $this->input->post("cardid");
            $sql = $this->db->query("SELECT
            email,
            card_no,
            customer_name,
            account_no
            FROM account WHERE card_no = ? AND status_id = ?
            " , array($cardid , 1));

            //check card data
            if($sql->num_rows() == 0){
                $output = array(
                    "msg" => "ไม่พบข้อมูลผู้ใช้งานในระบบ",
                    "status" => "Not Found Data",
                );
            }else{
                $output = array(
                    "msg" => "ดึงข้อมูลผู้ใช้งานสำเร็จ",
                    "status" => "Select Data Success",
                    "result" => $sql->row()
                );
            }

        }else{
            $output = array(
                "msg" => "ดึงข้อมูลผู้ใช้งานไม่สำเร็จ",
                "status" => "Select Data Not Success",
            );
        }
        echo json_encode($output);
    }

    public function getUserFromIntranetDatabase()
    {
        if(!empty($this->input->post("account_no") && !empty($this->input->post("email")))){
            
            $cardid = $this->input->post("cardid");
            $email = $this->input->post("email");
            $account_no = $this->input->post("account_no");

            $sql = $this->db_ev->query("SELECT
            card_no,
            customer_name,
            email,
            login_expire,
            status,
            account_no
            FROM account WHERE account_no = ? AND email = ?
            ",array($account_no , $email));

            // check data
            if($sql->num_rows() == 0){
                $output = array(
                    "msg" => "ไม่พบข้อมูลผู้ใช้งานในระบบ Intranet",
                    "status" => "Not Found Data",
                );
            }else{
                $output = array(
                    "msg" => "พบข้อมูลผู้ใช้งานในระบบ Intranet",
                    "status" => "Send To Login",
                    "user_result" => $sql->row()
                );
            }
        }else{
            $output = array(
                "msg" => "ไม่พบข้อมูล Cardid และ Email",
                "status" => "Select Data Not Success"
            );
        }
        echo json_encode($output);
    }
    
    public function saveCreatePassword()
    {
        if(!empty($this->input->post("card_no")) && !empty($this->input->post("email")) && !empty($this->input->post("password"))){
            $card_no = $this->input->post("card_no");
            $email = $this->input->post("email");
            $password = md5($this->input->post("password"));
            $customer_name = $this->input->post("customer_name");
            $verifycode = md5(uniqid(rand(), true));
            $account_no = $this->input->post("account_no");

            $array_save = array(
                "account_no" => $account_no,
                "card_no" => $card_no,
                "customer_name" => $customer_name,
                "email" => $email,
                "password" => $password,
                "status" => "wait activate",
                "verifycode" => $verifycode,
                "verifycode_timelimit" => strtotime('+1 hours')
            );
            $this->db_ev->insert("account" , $array_save);

            $this->load->model("email_model" , "email");
            $this->email->send_activateEmail($account_no , $verifycode , $email , $customer_name);

            $output = array(
                "msg" => "บันทึกข้อมูลการสร้างรหัสผ่านสำเร็จ กรุณายืนยันตัวตนบน Email เพื่อจบขั้นตอนการลงทะเบียน",
                "status" => "Insert Data Success",
                "verifycode" => $verifycode,
                "account_no" => $account_no
            );
        }else{
            $output = array(
                "msg" => "ข้อมูลไม่ถูกต้องกรุณาตรวจสอบข้อมูลใหม่อีกครั้ง",
                "status" => "Insert Data Not Success",
            );
        }
        echo json_encode($output);
    }

    public function updateUserActivate()
    {
        if(!empty($this->input->post("account_no")) && !empty($this->input->post("activatecode"))){
            $account_no = $this->input->post("account_no");
            $activatecode = $this->input->post("activatecode");
            //check activate time
            $accountData = $this->getuserdataByAccountNo($account_no);
            if($accountData->num_rows() != 0){
                $activatetime = (float)$accountData->row()->verifycode_timelimit;
                $nowtime = time();
                $getUserData = $this->getuserdataByAccountNo($account_no);

                //Check Activate already
                if($accountData->row()->status !== "activated"){
                    if($nowtime <= $activatetime){
                        $arupdate = array(
                            "status" => "activated",
                            "verifycode" => null,
                            "verifycode_timelimit" => null,
                            "datetime_update" => date("Y-m-d H:i:s"),
                            "login_expire" => strtotime("+15 day")
                        );
                        $this->db_ev->where("account_no" , $account_no);
                        $this->db_ev->where("verifycode" , $activatecode);
                        $this->db_ev->update("account" , $arupdate);
            
                        $output = array(
                            "msg" => "ยืนยันการเข้าใช้งานโปรแกรมสำเร็จ",
                            "status" => "Activate Success" ,
                            "userdata_result" => $getUserData->row(),
                            "now" => $nowtime ,
                            "timelimit" => $activatetime
                        );
                    }else if($nowtime >= $activatetime){
                        $output = array(
                            "msg" => "หมดเวลาสำหรับการยืนยันตัวตนครั้งนี้ กรุณารอสักครู่ ระบบจะทำการส่ง Email ยืนยันตัวตนฉบับใหม่ไปให้ท่าน",
                            "status" => "Lost Time Activate" ,
                            "userdata_result" => $getUserData->row(),
                            "now" => $nowtime ,
                            "timelimit" => $activatetime
                        );
                    }
                }else{
                    $output = array(
                        "msg" => "ท่านยืนยันตัวตนเรียบร้อยแล้ว",
                        "status" => "You Activate Already" ,
                        "userdata_result" => $getUserData->row(),
                        "now" => $nowtime ,
                        "timelimit" => $activatetime
                    );
                }
            }else{
                $output = array(
                    "msg" => "ไม่พบข้อมูล ยืนยันการเข้าใช้งานโปรแกรมไม่สำเร็จ",
                    "status" => "Activate Not Success"
                );
            }
            
        }else{
            $output = array(
                "msg" => "ยืนยันการเข้าใช้งานโปรแกรมไม่สำเร็จ",
                "status" => "Activate Not Success"
            );
        }

        echo json_encode($output);
    }

    private function getuserdataByAccountNo($account_no)
    {
        if(!empty($account_no)){
            $sql = $this->db_ev->query("SELECT
            account.autoid,
            account.account_no,
            account.card_no,
            account.customer_name,
            account.email,
            account.`status`,
            account.verifycode,
            account.login_expire,
            account.verifycode_timelimit,
            account.datetime_update
            FROM
            account
            WHERE account_no = ?
            " , array($account_no));

            return $sql;
        }
    }

    public function getCardData()
    {
        if(!empty($this->input->post("account_no")) && !empty($this->input->post("email"))){
            $account_no = $this->input->post("account_no");
            $email = $this->input->post("email");

            $sql = $this->db->query("SELECT
            account.account_no,
            account.customer_name,
            account.email,
            account.remark,
            account.card_no,
            card.status_id,
            card.amount,
            account.payment_type,
            mt_payment_type.`name`
            FROM
            account
            INNER JOIN card ON card.card_no = account.card_no
            INNER JOIN mt_payment_type ON mt_payment_type.type_id = account.payment_type
            WHERE
            account.status_id = 1 AND account.email = ? AND account.account_no = ?
            " , array($email , $account_no));

            if($sql->num_rows() == 0){
                $output = array(
                    "msg" => "ไม่พบข้อมูลในระบบ",
                    "status" => "Not Found Data",
                );
            }else{
                $output = array(
                    "msg" => "ดึงข้อมูลสำเร็จ",
                    "status" => "Select Data Success",
                    "result" => $sql->row()
                );
            }
        }else{
            $output = array(
                "msg" => "พบข้อผิดพลาด",
                "status" => "Error"
            );
        }
        echo json_encode($output);
    }

    public function getCardTransactionData()
    {
        // DB table to use
        $table = 'cardtransaction';

        // Table's primary key
        $primaryKey = 'id';

        $columns = array(
            array('db' => 'id', 'dt' => 0 ,
                'formatter' => function($d , $row){
                    $result = getCardDetailData($d)->row();
                    $output = "<div><span><b>วันที่ทำรายการ : </b>$result->meter_stop_datetime</span></div>";
                    $output .= "<div><span><b>จำนวนไฟที่ได้รับ : </b>$result->meteruse_kwh kWh</span></div>";
                    $output .= "<div><span><b>จำนวนเงิน : </b>$result->price บาท</span></div>";
                    return $output;
                }
            )
        );

        // SQL server connection information
        $sql_details = array(
            'user' => getDb()->db_username,
            'pass' => getDb()->db_password,
            'db'   => getDb()->db_databasename,
            'host' => getDb()->db_host
        );

        $card_no = $_POST['card_no'];

        $sqlSearchByCardNo = "card_no = '$card_no'";

        /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
        * If you just want to use the basic configuration for DataTables with PHP
        * server-side, there is no need to edit below this line.
        */
        require('server-side/scripts/ssp.class.php');

        echo json_encode(
            SSP::complex($_POST, $sql_details, $table, $primaryKey, $columns, null, $sqlSearchByCardNo)
        );
    }

    public function getUserInformation()
    {
        if(!empty($this->input->post("account_no")) && !empty($this->input->post("email"))){
            $account_no = $this->input->post("account_no");
            $email = $this->input->post("email");

            $sql = $this->db->query("SELECT
            account.account_no,
            account.citizen_id,
            account.account_date,
            account.customer_name,
            account.telephone_no,
            account.email,
            account.status_id,
            account.remark,
            account.create_date,
            account.card_no,
            account.address,
            account.member_no,
            account.line_id,
            account.payment_type,
            mt_payment_type.`name`
            FROM
            account
            INNER JOIN mt_payment_type ON mt_payment_type.type_id = account.payment_type
            WHERE account_no = ? AND email = ?
            ",array($account_no , $email));

            $output = array(
                "msg" => "ดึงข้อมูลผู้ใช้งานสำเร็จ",
                "status" => "Select Data Success",
                "result" => $sql->row()
            );

        }else{
            $output = array(
                "msg" => "ดึงข้อมูลไม่สำเร็จ",
                "status" => "Select Data Not Success"
            );
        }

        echo json_encode($output);

    }

    public function fetchdataCharger()
    {
        if(!empty($this->input->post("card_no"))){
            $card_no = $this->input->post("card_no");
            // $card_no = "7A0EE9F4";
            $sql = $this->db->query("SELECT
            machine_connector.machine_code,
            machine_connector.connector_id,
            machine_connector.charge_point_status,
            machine_connector.charge_point_error_code,
            machine_connector.create_datetime,
            machine_connector.update_datetime,
            machine_connector.machine_connector_status,
            machine_connector.`status`,
            machine_connector.status_datetime,
            machine_connector.card_no,
            -- machine_connector.start_time,
            DATE_FORMAT(machine_connector.start_time , '%d/%m/%Y %H:%i:%s') AS start_time,
            -- machine_connector.stop_time,
            DATE_FORMAT(machine_connector.stop_time , '%d/%m/%Y %H:%i:%s') AS stop_time,
            machine_connector.meter_value,
            machine_connector.etf,
            machine_status.status_name_thai
            FROM
            machine_connector
            INNER JOIN machine_status ON machine_status.machine_status_id = machine_connector.machine_connector_status
            WHERE connector_id = 1 AND card_no = ?
            ", array($card_no));

            $output = array(
                "msg" => "ดึงข้อมูลการชาร์จสำเร็จ",
                "status" => "Select Data Success",
                "result" => $sql->row()
            );
        }else{
            $output = array(
                "msg" => "ดึงข้อมูลการชาร์จไม่สำเร็จ",
                "status" => "Select Data Not Success",
            );
        }
        echo json_encode($output);
    }

    public function fetchdataCharger_admin()
    {
        if($this->input->post("action") == "fetchdataChargerAdmin"){

            $sql = $this->db->query("SELECT
            machine_connector.machine_code,
            machine_connector.connector_id,
            machine_connector.charge_point_status,
            machine_connector.charge_point_error_code,
            machine_connector.create_datetime,
            machine_connector.update_datetime,
            machine_connector.machine_connector_status,
            machine_connector.`status`,
            machine_connector.status_datetime,
            machine_connector.card_no,
            -- machine_connector.start_time,
            DATE_FORMAT(machine_connector.start_time , '%d/%m/%Y %H:%i:%s') AS start_time,
            -- machine_connector.stop_time,
            DATE_FORMAT(machine_connector.stop_time , '%d/%m/%Y %H:%i:%s') AS stop_time,
            machine_connector.meter_value,
            machine_connector.etf,
            machine_status.status_name_thai
            FROM
            machine_connector
            INNER JOIN machine_status ON machine_status.machine_status_id = machine_connector.machine_connector_status
            WHERE connector_id = 1
            ");

            $output = array(
                "msg" => "ดึงข้อมูลการชาร์จสำเร็จ",
                "status" => "Select Data Success",
                "result" => $sql->row()
            );
        }else{
            $output = array(
                "msg" => "ดึงข้อมูลการชาร์จไม่สำเร็จ",
                "status" => "Select Data Not Success",
            );
        }
        echo json_encode($output);
    }

    public function fetchRealtimeDataCharger()
    {
        if(!empty($this->input->post("card_no"))){
            $card_no = $this->input->post("card_no");
            $sql = $this->db->query("SELECT
            machine_connector_transaction.id,
            machine_connector_transaction.machine_code,
            machine_connector_transaction.connector_id,
            machine_connector_transaction.card_no,
            machine_connector_transaction.card_no_stop,
            FORMAT(machine_connector_transaction.price , 2) AS price,
            machine_connector_transaction_meter.id,
            machine_connector_transaction_meter.meter_value,
            machine_connector_transaction_meter.context,
            machine_connector_transaction_meter.format,
            machine_connector_transaction_meter.measurand,
            machine_connector_transaction_meter.location,
            machine_connector_transaction_meter.unit,
            machine_connector_transaction_meter.meter_datetime,
            machine_connector_transaction_meter.create_datetime
            FROM
            machine_connector_transaction
            INNER JOIN machine_connector_transaction_meter ON machine_connector_transaction_meter.machine_connector_transaction_id = machine_connector_transaction.id
            WHERE machine_connector_transaction.card_no = ? 
            ORDER BY machine_connector_transaction_meter.meter_datetime DESC LIMIT 9
            " , array($card_no));

            $output = array(
                "msg" => "ดึงข้อมูลขณะชาร์จแบบเรียลไทม์สำเร็จ",
                "status" => "Select Data Success",
                "result" => $sql->result()
            );
        }else{
            $output = array(
                "msg" => "ดึงข้อมูลขณะชาร์จไม่สำเร็จ",
                "status" => "Select Data Not Success",
            );
        }
        echo json_encode($output);
    }

    public function fetchRealtimeDataCharger_admin()
    {
        if(!empty($this->input->post("card_no"))){
            $card_no = $this->input->post("card_no");
            $sql = $this->db->query("SELECT
            machine_connector_transaction.id,
            machine_connector_transaction.machine_code,
            machine_connector_transaction.connector_id,
            machine_connector_transaction.card_no,
            machine_connector_transaction.card_no_stop,
            FORMAT(machine_connector_transaction.price , 2) AS price,
            machine_connector_transaction_meter.id,
            machine_connector_transaction_meter.meter_value,
            machine_connector_transaction_meter.context,
            machine_connector_transaction_meter.format,
            machine_connector_transaction_meter.measurand,
            machine_connector_transaction_meter.location,
            machine_connector_transaction_meter.unit,
            machine_connector_transaction_meter.meter_datetime,
            machine_connector_transaction_meter.create_datetime
            FROM
            machine_connector_transaction
            INNER JOIN machine_connector_transaction_meter ON machine_connector_transaction_meter.machine_connector_transaction_id = machine_connector_transaction.id
            WHERE machine_connector_transaction.card_no = ? 
            ORDER BY machine_connector_transaction_meter.meter_datetime DESC LIMIT 9
            " , array($card_no));

            $output = array(
                "msg" => "ดึงข้อมูลขณะชาร์จแบบเรียลไทม์สำเร็จ",
                "status" => "Select Data Success",
                "result" => $sql->result()
            );
        }else{
            $output = array(
                "msg" => "ดึงข้อมูลขณะชาร์จไม่สำเร็จ",
                "status" => "Select Data Not Success",
            );
        }
        echo json_encode($output);
    }

    public function resendEmail()
    {
        if(!empty($this->input->post("account_no"))){
            $account_no = $this->input->post("account_no");
            $accountdata = $this->getuserdataByAccountNo($account_no);
            if($accountdata->num_rows() != 0){
                $email = $accountdata->row()->email;
                $customer_name = $accountdata->row()->customer_name;
                $verifycode = md5(uniqid(rand(), true));

                $array_save = array(
                    "verifycode" => $verifycode,
                    "verifycode_timelimit" => strtotime('+1 hours')
                );
                $this->db_ev->where("account_no" , $account_no);
                $this->db_ev->update("account" , $array_save);
    
                $this->load->model("email_model" , "email");
                $this->email->send_activateEmail($account_no , $verifycode , $email , $customer_name);

                $output = array(
                    "msg" => "ส่ง Email ยืนยันตัวตนสำเร็จ",
                    "status" => "Update Data Success"
                );
            }else{
                $output = array(
                    "msg" => "ส่ง Email ยืนยันตัวตนไม่สำเร็จ",
                    "status" => "Update Data Not Success"
                );
            }
        }else{
            $output = array(
                "msg" => "ไม่พบข้อมูลผู้ใช้งาน",
                "status" => "Not Found Account Data"
            );
        }
        echo json_encode($output);
    }
    

}

/* End of file ModelName.php */





?>