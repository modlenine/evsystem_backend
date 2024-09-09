<?php
class getfn{
    public $ci;
    public function __construct(){
        $this->ci =& get_instance();
        date_default_timezone_set("Asia/Bangkok");
    }
    public function gci(){
        return $this->ci;
    }

}

function get()
{
    $obj = new getfn();
    return $obj->gci();
}

function getDb()
{
    get()->db_ev = get()->load->database("evcharger" , true);
    $sql = get()->db_ev->query("SELECT * FROM db WHERE db_autoid = '1' ");
    return $sql->row();
}

function getCardDetailData($id)
{
    if(!empty($id)){
        $sql = get()->db->query("SELECT
        * FROM cardtransaction WHERE id = ?
        " , array($id));

        return $sql;
    }
}




?>