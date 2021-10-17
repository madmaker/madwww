<?php
class uSubscr_run_mailing {
    private $uCore,$rec_id,$m_id;
    private function check_data() {
        if(!isset($_POST['rec_id'])) $this->uCore->error(1);
        $this->rec_id=$_POST['rec_id'];
        if(!uString::isDigits($this->rec_id)) $this->uCore->error(2);
    }
    private function write2db() {
        //get new m_id
        if(!$query=$this->uCore->query("uSubscr","SELECT
        `m_id`
        FROM
        `u235_mailing`
        WHERE
        `site_id`='".site_id."'
        ORDER BY
        `m_id` DESC
        LIMIT 1
        ")) $this->uCore->error(3);
        if(mysqli_num_rows($query)) {
            $qr=$query->fetch_object();
            $this->m_id=$qr->m_id+1;
        }
        else $this->m_id=1;

        //insert mailing 2 db
        if(!$this->uCore->query("uSubscr","INSERT INTO
        `u235_mailing` (
        `m_id`,
        `rec_id`,
        `timestamp`,
        `status`,
        `site_id`
        ) VALUES (
        '".$this->m_id."',
        '".$this->rec_id."',
        '".time()."',
        'preparing',
        '".site_id."'
        )
        ")) $this->uCore->error(4);

    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(23)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->write2db();

        echo "{'status' : 'done'}";
    }
}
$uSubscr=new uSubscr_run_mailing($this);
