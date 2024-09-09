<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Login_model extends CI_Model {
    
    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set("Asia/Bangkok");
        $this->db_ev = $this->load->database("evcharger" , true);
    }

    public function saveLogin()
    {
        //code
        if(!empty($this->input->post("usernameInput")) && !empty($this->input->post("passwordInput"))){
            $username = $this->input->post("usernameInput");
            $password = md5($this->input->post("passwordInput"));

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
            account.datetime_update,
            account.user_permission
            FROM account WHERE email = ? AND password = ?
            " , array($username , $password));

            if($sql->num_rows() == 0){
                $output = array(
                    "msg" => "ไม่พบข้อมูลในระบบ",
                    "status" => "Not Found Data"
                );
            }else{
                //check login Expire
                $arupdate = array(
                    "login_expire" => strtotime("+15 day"),
                    "last_login" => date("Y-m-d H:i:s")
                );
                $this->db_ev->where("email" , $username);
                $this->db_ev->update("account" , $arupdate);

                $output = array(
                    "msg" => "ลงชื่อเข้าใช้สำเร็จ",
                    "status" => "Login Success",
                    "user_result" => $sql->row()
                );
            }
        }else{
            $output = array(
                "msg" => "กรุณาตรวจสอบ Username & Password" ,
                "status" => "Please Check Data"
            );
        }
        echo json_encode($output);
    }

    public function forgotpassword()
    {
        if(!empty($this->input->post("emailInput"))){
            $emailInput = $this->input->post("emailInput");
            $sql = $this->db_ev->query("SELECT
            email,
            account_no ,
            customer_name
            FROM account WHERE email = ?
            ",array($emailInput));

            $this->load->model("main/email_model" , "email");
            if($sql->num_rows() != 0){
                //send Email for create new password
                $resultEmail = $this->email->send_createNewPass($sql->row()->account_no , $sql->row()->email , $sql->row()->customer_name );

                $output = array(
                    "msg" => "ระบบได้ส่ง Email สำหรับสร้างรหัสผ่านใหม่แล้ว",
                    "status" => "Send Email Already",
                    "resultEmail" => $resultEmail
                );
            }else{
                $output = array(
                    "msg" => "ไม่พบข้อมูล Email บนระบบ",
                    "status" => "Not Found Email"
                );
            }
        }else{
            $output = array(
                "msg" => "กรุณากรอก Email ของท่าน",
                "status" => "Please Fill Email"
            );
        }

        echo json_encode($output);
    }

    public function create_newpassword()
    {
        if(!empty($this->input->post("newpassword")) && !empty($this->input->post("account_no"))){
            $newpassword = $this->input->post("newpassword");
            $account_no = $this->input->post("account_no");

            $ar_save = array(
                "password" => md5($newpassword)
            );
            $this->db_ev->where("account_no" , $account_no);
            $this->db_ev->update("account" , $ar_save);

            $output = array(
                "msg" => "สร้างรหัสผ่านใหม่สำเร็จ",
                "status" => "Update Data Success"
            );
        }else{
            $output = array(
                "msg" => "สร้างรหัสผ่านใหม่ไม่สำเร็จ",
                "status" => "Update Data Not Success"
            );
        }
        echo json_encode($output);
    }
    
    

}

/* End of file ModelName.php */





?>