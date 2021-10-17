<?php
class uSubscr_cron_prepate_mailing {
    private $uCore,$secret,$start_time,$finish_time,$m_id,$rec_id,$last_user_id_prepared,$site_id;
    private function check_data() {
        if(!isset($_POST['secret'])) $this->uCore->error(1);
        if($_POST['secret']!=$this->secret) $this->uCore->error(2);

        if(!isset($_POST['site_id'])) $this->uCore->error(3);
        $this->site_id=$_POST['site_id'];
        if(!uString::isDigits($this->site_id)) $this->uCore->error(4);

        //check if uSubscr is installed for this site
        if(!$query=$this->uCore->query("common","SELECT
        `site_id`
        FROM
        `u235_sites_modules`
        WHERE
        `site_id`='".$this->site_id."' AND
        `mod_name`='uSubscr' AND
        `installed`='1'
        ")) $this->uCore->error(5);
        if(!mysqli_num_rows($query)) die('');//die('not installed');
    }
    private function get_mailing_for_preparing() {
        if(!$query=$this->uCore->query("uSubscr","SELECT
        `m_id`,
        `rec_id`,
        `last_user_id_prepared`
        FROM
        `u235_mailing`
        WHERE
        `status`='preparing' AND
        `site_id`='".$this->site_id."'
        ORDER BY
        `m_id` ASC
        LIMIT 1
        ")) $this->uCore->error(6);
        if(!mysqli_num_rows($query)) die('nothing to prepare');
        $qr=$query->fetch_object();
        $this->m_id=$qr->m_id;
        $this->rec_id=$qr->rec_id;
        $this->last_user_id_prepared=$qr->last_user_id_prepared;
    }
    private function update_last_user_id_prepared($user_id) {
        if(!$this->uCore->query("uSubscr","UPDATE
        `u235_mailing`
        SET
        `last_user_id_prepared`='".$user_id."'
        WHERE
        `m_id`='".$this->m_id."' AND
        `site_id`='".$this->site_id."'
        ")) $this->uCore->error(7);
        die('to be continued...');
    }
    private function run_mailing() {
        if(!$this->uCore->query("uSubscr","UPDATE
        `u235_mailing`
        SET
        `status`='running'
        WHERE
        `m_id`='".$this->m_id."' AND
        `site_id`='".$this->site_id."'
        ")) $this->uCore->error(8);
        die('started new mailing');
    }
    private function prepare_mailing() {
        //get users for mailing
        if(!$query=$this->uCore->query("uSubscr","SELECT DISTINCT
        `user_id`
        FROM
        `u235_records_groups`,
        `u235_users_groups`
        WHERE
        `u235_records_groups`.`rec_id`='".$this->rec_id."' AND
        `u235_records_groups`.`site_id`='".$this->site_id."' AND
        `u235_users_groups`.`gr_id`=`u235_records_groups`.`gr_id` AND
        `user_id`>'".$this->last_user_id_prepared."' AND
        `u235_users_groups`.`site_id`='".$this->site_id."'
        ORDER BY
        `user_id` ASC
        ")) $this->uCore->error(9);

        //insert users to mailing results
        if(mysqli_num_rows($query)) {
            while($user=$query->fetch_object()) {
                if(!$this->uCore->query("uSubscr","INSERT INTO
                `u235_mailing_results` (
                `m_id`,
                `user_id`,
                `timestamp`,
                `result`,
                `hash`,
                `site_id`
                ) VALUES (
                '".$this->m_id."',
                '".$user->user_id."',
                '".time()."',
                'not sent',
                '".uFunc::genHash()."',
                '".$this->site_id."'
                )
                ")) $this->uCore->error(10);

                if($this->finish_time<time()) {
                    $this->update_last_user_id_prepared($user->user_id);
                }
            }
            if($this->finish_time<time()) {
                $this->update_last_user_id_prepared($user->user_id);
            }
            else $this->run_mailing();
        }
        else $this->run_mailing();
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->start_time=time();
        $this->secret="NhX%wc!!&TmQxBxnXHnMM88maS";
        $this->finish_time=$this->start_time+23;

        $this->check_data();

        $this->get_mailing_for_preparing();
        $this->prepare_mailing();
    }
}
$uSubscr=new uSubscr_cron_prepate_mailing($this);
