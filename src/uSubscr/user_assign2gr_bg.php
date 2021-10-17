<?php
class uSubscr_rec_assing2gr {
    private $uCore,$gr_id,$user_id,$assigned;
    private function check_data() {
        if(!isset($_POST['user_id'],$_POST['gr_id'])) $this->uCore->error(1);
        $this->user_id=$_POST['user_id'];
        $this->gr_id=$_POST['gr_id'];

        if(!uString::isDigits($this->user_id)) $this->uCore->error(2);
        if(!uString::isDigits($this->gr_id)) $this->uCore->error(3);
    }
    private function write_out() {
        if(!$query=$this->uCore->query("uSubscr","SELECT
        `user_id`
        FROM
        `u235_users_groups`
        WHERE
        `gr_id`='".$this->gr_id."' AND
        `user_id`='".$this->user_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(4);
        if(mysqli_num_rows($query)) {
            if(!$this->uCore->query("uSubscr","DELETE
            FROM
            `u235_users_groups`
            WHERE
            `gr_id`='".$this->gr_id."' AND
            `user_id`='".$this->user_id."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(4);
            $this->assigned=0;
        }
        else {
            if(!$this->uCore->query("uSubscr","INSERT INTO
            `u235_users_groups` (
            `user_id`,
            `gr_id`,
            `site_id`
            ) VALUES (
            '".$this->user_id."',
            '".$this->gr_id."',
            '".site_id."'
            )
            ")) $this->uCore->error(5);
            $this->assigned=1;
        }
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(23)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->write_out();
        echo "{
        'status' : 'done',
        'gr_id' : '".$this->gr_id."',
        'user_id' : '".$this->user_id."',
        'assigned':'".$this->assigned."'
        }";
    }
}
$uSubscr=new uSubscr_rec_assing2gr($this);
