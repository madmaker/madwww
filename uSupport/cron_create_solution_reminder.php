<?php
class cron_create_solution_reminder {
    private $uCore,$secret,
        $q_sites,
        $start_reminders_after,
        $reminder_days,$reminder_timestamp,
        $support_email,$support_email_from,$use_smtp,$smtp_settings,
        $start_time,$time_limit,$stop_before_limit,
        $site_id,$site_name,$site_domain,$u_sroot;

    private function check_data() {
        if(!isset($_POST['uSecret'])) $this->uCore->error(1);
        if($this->secret!=$_POST['uSecret']) $this->uCore->error(2);
    }

    private function get_sites() {
        if(!$this->q_sites=$this->uCore->query("common","SELECT DISTINCT
        `u235_sites`.`site_id`
        FROM
        `u235_sites`,
        `u235_sites_modules`
        WHERE
        `u235_sites`.`site_id`=`u235_sites_modules`.`site_id` AND
        `status`='active' AND
        `main`='1' AND
        `mod_name`='uSup' AND
        `installed`='1'
        ")) $this->uCore->error(3);
    }

    private function set_vars() {
        $this->time_limit=30;
        $this->stop_before_limit=10;

        $this->site_name=$this->uCore->uFunc->getConf('site_name','content',0,$this->site_id);
        $this->site_domain=$this->uCore->uFunc->getConf('site_domain','content',0,$this->site_id);
        $this->u_sroot='http://'.$this->site_domain.'/';

        $this->reminder_days=trim($this->uCore->uFunc->getConf("create_solution_reminder_days","uSup",0,$this->site_id));
        if(!uString::isDigits($this->reminder_days)) $this->reminder_days=2;
        if($this->reminder_days=='0') return false;

        $this->reminder_timestamp=time()-$this->reminder_days*24*60*60;

        $this->support_email=$this->uCore->uFunc->getConf("support_email","uSup",0,$this->site_id);
        $this->support_email_from=$this->uCore->uFunc->getConf("support_email_fromname","uSup",0,$this->site_id);

        $this->use_smtp=$this->uCore->uFunc->getConf('smtp_use_madwww_server','uSup',true,$this->site_id)=='0';
        if($this->use_smtp) {
            //port
            //server_name
            //use_ssl
            //user_name
            //password
            $this->smtp_settings['server_name']=$this->uCore->uFunc->getConf('smtp_server_name','uSup',true,$this->site_id);
            $this->smtp_settings['port']=$this->uCore->uFunc->getConf('smtp_port','uSup',true,$this->site_id);
            $this->smtp_settings['user_name']=$this->uCore->uFunc->getConf('smtp_user_name','uSup',true,$this->site_id);
            $this->smtp_settings['password']=$this->uCore->uFunc->getConf('smtp_password','uSup',true,$this->site_id);
            $this->smtp_settings['use_ssl']=$this->uCore->uFunc->getConf('smtp_use_ssl','uSup',true,$this->site_id)=='1';
        }
        else $this->smtp_settings[0]=0;
        return true;
    }

    private function get_requests() {
        if(!$query=$this->uCore->query("uSup","SELECT
        `tic_id`,
        `cons_id`,
        `tic_changed_timestamp`,
        `tic_notified_about_noaction_timestamp`,
        `tic_subject`
        FROM
        `u235_requests`
        WHERE
        `tic_changed_timestamp`<'".$this->reminder_timestamp."' AND
        `tic_changed_timestamp`>'".$this->start_reminders_after."' AND
        `uknowbase_solution_isset`='0' AND
        `uknowbase_no_solution_user_id`='0' AND
        (`tic_status`='req_closed' OR `tic_status`='case_closed') AND
        `site_id`='".$this->site_id."'
        ")) $this->uCore->error(4);
        return $query;
    }

    private function get_cons_info($req) {
        if($req->cons_id!='0') {//consultant is set
            //get consultant's name, email
            if(!$q_cons=$this->uCore->query("uAuth","SELECT DISTINCT
            `u235_users`.`user_id`,
            `firstname`,
            `secondname`,
            `lastname`,
            `email`
            FROM
            `u235_users`,
            `u235_usersinfo`
            WHERE
            `u235_users`.`user_id`='".$req->cons_id."' AND
            `u235_users`.`user_id`=`u235_usersinfo`.`user_id` AND
            `u235_usersinfo`.`status`='active' AND
            `u235_users`.`status`='active' AND
            `u235_usersinfo`.`site_id`='".$this->site_id."'
            ")) $this->uCore->error(5);
        }
        else {//send reminder to operator
            //get operator's name, email
            if(!$q_cons=$this->uCore->query("uAuth","SELECT DISTINCT
            `u235_users`.`user_id`,
            `firstname`,
            `secondname`,
            `lastname`,
            `email`
            FROM
            `u235_users`,
            `u235_usersinfo`,
            `u235_usersinfo_groups`
            WHERE
            `u235_users`.`user_id`=`u235_usersinfo`.`user_id` AND
            `u235_usersinfo_groups`.`user_id`=`u235_users`.`user_id` AND
            `u235_usersinfo`.`status`='active' AND
            `u235_users`.`status`='active' AND
            `u235_usersinfo_groups`.`group_id`='4' AND
            `u235_usersinfo`.`site_id`='".$this->site_id."' AND
            `u235_usersinfo_groups`.`site_id`='".$this->site_id."'
            ")) $this->uCore->error(6);
        }
        return $q_cons;
    }

    private function send_reminder_about_inaction($req,$q_cons) {
        $write_above_line=$this->uCore->uFunc->getConf("email_write_above_line_content","uSup",0,$this->site_id);
        $tic_subject=uString::sql2text($req->tic_subject);

        $title='[#req-'.$req->tic_id.'] Добавьте решение в базу знаний. '.$tic_subject;

        //notify consultant or operator
        while($cons=$q_cons->fetch_object()) {
            $html='<p>Здравствуйте, '.$cons->firstname.' '.$cons->secondname.'</p>
            <div class="msg_text">
                <p><strong>Срочно добавьте решение в базу знаний</strong> для запроса <a href="'.$this->u_sroot.$this->uCore->mod.'/request_show/'.$req->tic_id.'">#'.$req->tic_id.' '.$tic_subject.'</a> в технической поддержку сайта <a href="'.$this->u_sroot.'">'.$this->site_name.'</a>.</p>
            </div>
            <p><small>На всякий случай: <a href="'.$this->u_sroot.$this->uCore->mod.'/requests">войти в техподдержку можно здесь</a>. Сбросить пароль можно в диалоге авторизации</small></p>';

            $this->uCore->uFunc->mail($html,$title,$cons->email,$this->support_email_from,$this->support_email,$this->u_sroot,$this->site_id,'<p>'.$write_above_line.'</p>',$this->use_smtp,$this->smtp_settings);
        }
    }
    private function remind_about_inaction($req) {
        $q_cons=$this->get_cons_info($req);

        if(!$this->uCore->query("uSup","UPDATE
        `u235_requests`
        SET
        `tic_notified_about_noaction_timestamp`='".time()."'
        WHERE
        `tic_id`='".$req->tic_id."' AND
        `site_id`='".$this->site_id."'
        ")) $this->uCore->error(7);

        //send reminders
        $this->send_reminder_about_inaction($req,$q_cons);
    }

    private function run_throw_requests() {
        $q_requests=$this->get_requests();
        while($req=$q_requests->fetch_object()) {
            if(time()>=$this->start_time+$this->time_limit-$this->stop_before_limit) exit('time limit exceeded');
            if($req->tic_notified_about_noaction_timestamp<$this->reminder_timestamp) $this->remind_about_inaction($req);
        }
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->start_time=time();
        $this->secret='teSozwY1htiuAhcwlKqQr3CLMu';

        $this->start_reminders_after=1416217691;

        //$this->check_data();

        $this->get_sites();

        while($site=$this->q_sites->fetch_object()) {
            $this->site_id=$site->site_id;
            if($this->set_vars()) $this->run_throw_requests();
        }
    }
}
$uSup=new cron_create_solution_reminder ($this);
