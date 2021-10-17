<?php
class uSup_company_create {
    private $uCore,$com_title;
    private function check_data() {
        if(!isset($_POST['com_title'])) $this->uCore->error(1);
        $this->com_title=trim($_POST['com_title']);
        if(!strlen($this->com_title)) die("{'status' : 'error', 'msg' : 'title_empty'}");
    }
    private function create_com() {
        //get com_id for new company
        if(!$query=$this->uCore->query('uSup',"SELECT
        `com_id`
        FROM
        `u235_comps`
        WHERE
        `site_id`='".site_id."'
        ORDER BY
        `com_id` DESC
        LIMIT 1
        ")) $this->uCore->error(2);
        if(mysqli_num_rows($query)>0) {
            $qr=$query->fetch_object();
            $com_id=$qr->com_id+1;
        }
        else $com_id=1;

        //Create new company id db
        if(!$this->uCore->query('uSup',"INSERT INTO
        `u235_comps` (
        `com_id`,
        `com_title`,
        `site_id`
        ) VALUES (
        '".$com_id."',
        '".uString::sql2text($this->com_title)."',
        '".site_id."'
        )")) $this->uCore->error(3);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(8)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->create_com();
        echo "{'status' : 'done'}";
    }
}
$uSup=new uSup_company_create($this);
