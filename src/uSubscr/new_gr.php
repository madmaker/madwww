<?php
class uSubscr_new_gr{
    private $uCore,$gr_title,$gr_id;
    private function check() {
        if(!isset($_POST['gr_title'])) $this->uCore->error(1);
        $this->gr_title=$_POST['gr_title'];
    }
    private function get_new_gr_id() {
        if(!$query=$this->uCore->query("uSubscr","SELECT
        `gr_id`
        FROM
        `u235_groups`
        WHERE
        `site_id`='".site_id."'
        ORDER BY
        `gr_id` DESC
        LIMIT 1
        ")) $this->uCore->error(3);
        if(mysqli_num_rows($query)) {
            $qr=$query->fetch_object();
            $this->gr_id=$qr->gr_id+1;
        }
        else $this->gr_id=1;
    }
    private function write2db() {
        if(!$this->uCore->query("uSubscr","INSERT INTO
        `u235_groups` (
        `gr_id`,
        `gr_title`,
        `site_id`
        ) VALUES (
        '".$this->gr_id."',
        '".uString::text2sql($this->gr_title)."',
        '".site_id."'
        )
        ")) $this->uCore->error(6);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(23)) die("{'status' : 'forbidden'}");

        $this->check();
        $this->get_new_gr_id();

        $this->write2db();

        echo  "{
        'status' : 'done',
        'gr_id' : '".$this->gr_id."',
        'gr_title':'".rawurlencode($this->gr_title)."'
        }";
    }
}
$uSubscr=new uSubscr_new_gr($this);
