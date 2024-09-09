<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Email_model extends CI_Model {
    
    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set("Asia/Bangkok");
    }

    public function send_activateEmail($account_no , $verifycode , $email , $customer_name)
    {
        if(!empty($account_no) && !empty($verifycode) && !empty($email) && !empty($customer_name)){

            $subject = "ยืนยันการเข้าใช้งานโปรแกรม EV System";
            if($_SERVER['HTTP_HOST'] != "localhost"){
               $activateurl = 'https://intranet.saleecolour.com/evsystem/result_activate/'.$account_no.'/'.$verifycode;
            }else{
               $activateurl = 'http://localhost:8080/result_activate/'.$account_no.'/'.$verifycode;
            }
         
            $body = '
               <h2>กรุณาทำการยืนยันตัวตน เพื่อเข้าใช้งานโปรแกรม EV System</h2>
               <table>
               <tr>
                  <td>
                  <span>เรียนคุณ '.$customer_name.' </span><br>
                  <span>กรุณาคลิกที่ลิงค์ด้านล่างนี้ ภายใน 1 ชม. เพื่อเป็นการยืนยันตัวตนการเข้าใช้งานโปรแกรม EV System </span><br>
                  <span>'.$activateurl.'</span>
                  </td>
               </tr>
               </table>
               ';
         
            $to = "";
            $cc = "";
         
            //  Email Zone
            $to = array($email);
         
            sendemail($subject, $body, $to, $cc);
            //  Email Zone
        }
    }

    public function send_createNewPass($account_no , $email , $customer_name )
    {
        if(!empty($account_no) && !empty($email) && !empty($customer_name)){

            $subject = "ยืนยันการสร้างรหัสผ่านใหม่โปรแกรม EV System";
            if($_SERVER['HTTP_HOST'] != "localhost"){
               $createNewPasswordUrl = 'https://intranet.saleecolour.com/evsystem/create_newpassword/'.$account_no;
            }else{
               $createNewPasswordUrl = 'http://localhost:8080/create_newpassword/'.$account_no;
            }
         
            $body = '
               <h2>กรุณาคลิกที่ลิ้งค์ด้านล่างนี้เพื่อสร้างรหัสผ่านใหม่</h2>
               <table>
               <tr>
                  <td>
                  <span>เรียนคุณ '.$customer_name.' </span><br>
                  <span>กรุณาคลิกที่ลิงค์ด้านล่างนี้ เพื่อสร้างรหัสผ่านใหม่ </span><br>
                  <span>'.$createNewPasswordUrl.'</span>
                  </td>
               </tr>
               </table>
               ';
         
            $to = "";
            $cc = "";
         
            //  Email Zone
            $to = array($email);
         
            sendemail($subject, $body, $to, $cc);
            //  Email Zone
        }
    }
    
    

}

/* End of file Email_model.php */



?>