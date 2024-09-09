<?php
class email_function{
    public $ci;
    function __construct()
    {
        $this->ci =&get_instance();
        date_default_timezone_set("Asia/Bangkok");
    }
    
    public function email_ci()
    {
        return $this->ci;
    }
}

function email(){
    $obj = new email_function();
    return $obj->email_ci();
}

function getEmailUser()
{
    email()->db_ev = email()->load->database("evcharger" , true);
    $query = email()->db_ev->query("SELECT * FROM email_information WHERE email_id = 1");
    return $query->row();
}

function sendemail($subject , $body , $to , $cc)
{
    require("PHPMailer_5.2.0/class.phpmailer.php");
    require("PHPMailer_5.2.0/class.smtp.php");

    $mail = new PHPMailer();

    try {
        // Server settings
        $mail->CharSet      = "utf-8";  // ในส่วนนี้ ถ้าระบบเราใช้ tis-620 หรือ windows-874 สามารถแก้ไขเปลี่ยนได้
        $mail->SMTPDebug    = 0;                                       // Enable verbose debug output
        $mail->isSMTP();                                            // Set mailer to use SMTP
        $mail->Host         = 'mail.saleecolour.net';                     // Specify main and backup SMTP servers
        $mail->SMTPAuth     = true;                                   // Enable SMTP authentication
        $mail->Username     = getEmailUser()->email_user;               // SMTP username
        $mail->Password     = getEmailUser()->email_password;          // SMTP password
        $mail->From         = getEmailUser()->email_user;
        $mail->FromName     = "EV Charger System";
        $mail->Port         = 587;                                    // TCP port to connect to

        if(!empty($to)){
            foreach($to as $email){
                $mail->AddAddress($email);
            }
        }

        if(!empty($cc)){
            foreach($cc as $email){
                $mail->AddCC($email);
            }
        }

        // $mail->AddAddress("chainarong039@gmail.com");
        $mail->AddBCC("chainarong_k@saleecolour.com");

        $mail->WordWrap = 50;                          // set word wrap to 50 characters
        $mail->IsHTML(true);                           // set email format to HTML
        $mail->Subject = $subject;
        $mail->Body = '
        <style>
            @import url("https://fonts.googleapis.com/css2?family=Sarabun&display=swap");
    
            h3{
                font-family: Tahoma, sans-serif;
                font-size:14px;
            }
    
            table {
                font-family: Tahoma, sans-serif;
                font-size:14px;
                border-collapse: collapse;
                width: 90%;
              }
              
              td, th {
                border: 1px solid #ccc;
                text-align: left;
                padding: 8px;
              }
              
              tr:nth-child(even) {
                background-color: #F5F5F5;
              }
    
              .bghead{
                  text-align:center;
                  background-color:#D3D3D3;
              }
            </style>
        '.$body;
        // if($_SERVER['HTTP_HOST'] != "localhost"){
        //     $mail->send();
        // }
        $mail->send();
        return 'Message has been sent';
    } catch (Exception $e) {
        return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}




?>