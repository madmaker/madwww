<?php
class uSubscr_new_record{
    private $uCore,$rec_title,$rec_id;
    private function check() {
        if(!isset($_POST['rec_title'])) $this->uCore->error(1);
        $this->rec_title=$_POST['rec_title'];
    }
    private function get_new_rec_id() {
        if(!$query=$this->uCore->query("uSubscr","SELECT
        `rec_id`
        FROM
        `u235_records`
        WHERE
        `site_id`='".site_id."'
        ORDER BY
        `rec_id` DESC
        LIMIT 1
        ")) $this->uCore->error(3);
        if(mysqli_num_rows($query)) {
            $qr=$query->fetch_object();
            $this->rec_id=$qr->rec_id+1;
        }
        else $this->rec_id=1;
    }
    private function write2db() {
        if(!$this->uCore->query("uSubscr","INSERT INTO
        `u235_records` (
        `rec_id`,
        `rec_title`,
        `site_id`
        ) VALUES (
        '".$this->rec_id."',
        '".uString::text2sql($this->rec_title)."',
        '".site_id."'
        )
        ")) $this->uCore->error(6);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(23)) die("{'status' : 'forbidden'}");

        $this->check();
        $this->get_new_rec_id();

        $this->write2db();

        echo  "{
        'status' : 'done',
        'rec_id' : '".$this->rec_id."',
        'rec_title':'".rawurlencode($this->rec_title)."'
        }";
    }
}
$newClass=new uSubscr_new_record($this);
