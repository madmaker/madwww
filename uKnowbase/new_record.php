<?php
require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";

class uKnowbase_new_record{
    public $uFunc;
    public $uSes;
    private $uCore,$rec_title,$is_section,$rec_indent,$rec_position,$rec_id;
    private function check() {
        if(!isset(
        $_POST['rec_title'],
        $_POST['is_section'],
        $_POST['rec_indent'],
        $_POST['rec_position']
        )) $this->uCore->error(10);

        $this->rec_title=trim($_POST['rec_title']);
        if(!strlen($this->rec_title)) die('{"status":"error","msg":"title is empty"}');

        $this->rec_indent=$_POST['rec_indent'];
        if(!uString::isDigits($this->rec_indent)) $this->uCore->error(20);
        $this->rec_position=$_POST['rec_position'];
        if(!uString::isDigits($this->rec_position)) $this->uCore->error(30);
        //get position of rec after what we must be
        if($this->rec_position!='0') {
            if(!$query=$this->uCore->query("uKnowbase","SELECT
            `rec_position`
            FROM
            `u235_records`
            WHERE
            `rec_id`='".$this->rec_position."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(40);
            if(!mysqli_num_rows($query)) $this->uCore->error(50);
            $rec=$query->fetch_object();
            $this->rec_position=$rec->rec_position+1;
        }

        if($_POST['is_section']=='1') $this->is_section=1;
        else $this->is_section=0;
    }
    private function get_new_rec_id() {
        if(!$query=$this->uCore->query("uKnowbase","SELECT
        `rec_id`
        FROM
        `u235_records`
        WHERE
        `site_id`='".site_id."'
        ORDER BY
        `rec_id` DESC
        LIMIT 1
        ")) $this->uCore->error(60);
        if(mysqli_num_rows($query)) {
            $qr=$query->fetch_object();
            $this->rec_id=$qr->rec_id+1;
        }
        else $this->rec_id=1;
    }
    private function tunePositions() {
        //check if any record have position=0
        if(!$query=$this->uCore->query("uKnowbase","SELECT
        `rec_position`
        FROM
        `u235_records`
        WHERE
        `rec_position`='".$this->rec_position."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(70);
        if(mysqli_num_rows($query)) {
            //we must move all positions down
            if(!$this->uCore->query("uKnowbase","UPDATE
            `u235_records`
            SET
            `rec_position`=`rec_position`+1
            WHERE
            `rec_position`>=".$this->rec_position." AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(80);
            return true;
        }
        return false;
    }
    private function write2db() {
        $auto_publish=$this->uCore->uFunc->getConf("auto_publish","uKnowbase");
        if($auto_publish=='1') $status='active';
        else $status='new';
        if(!$this->uCore->query("uKnowbase","INSERT INTO
        `u235_records` (
        `rec_id`,
        `rec_title`,
        `is_section`,
        `rec_indent`,
        `rec_position`,
        `rec_status`,
        `user_id`,
        `site_id`
        ) VALUES (
        '".$this->rec_id."',
        '".uString::text2sql($this->rec_title)."',
        '".$this->is_section."',
        '".$this->rec_indent."',
        '".$this->rec_position."',
        '".$status."',
        '".$this->uSes->get_val("user_id")."',
        '".site_id."'
        )
        ")) $this->uCore->error(90);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);

        if(!$this->uCore->access(33)) die("{'status' : 'forbidden'}");

        $this->check();
        $this->get_new_rec_id();
        $this->tunePositions();
        $this->write2db();
        $this->uFunc->set_flag_update_sitemap(1, site_id);
        echo  "{
        'status' : 'done',
        'rec_id':'".$this->rec_id."'
        }";
    }
}
$newClass=new uKnowbase_new_record($this);
