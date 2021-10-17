<?php
class uSup_request_inaction_close_cron {
    private $uCore,$secret,
        $inaction_days,$reminder_days,$reminder_timestamp,$reminder_days_left,$close_time_left,
        $write_above_line,$support_email,$support_email_from,$use_smtp,$smtp_settings,
        $tic_feedback_hash,
        $status2Text_ar,
        $start_time,$time_limit,$stop_before_limit,
        $site_id,$site_name,$site_domain,$u_sroot;

    private function check_data() {
        if(!isset($_POST['uSecret'])) $this->uCore->error(10);
        if($this->secret!=$_POST['uSecret']) $this->uCore->error(20);

        if(!isset($_POST['site_id'])) $this->uCore->error(30);
        $this->site_id=$_POST['site_id'];
        if(!uString::isDigits($this->site_id)) $this->uCore->error(40);

        //check if uSupport is installed for this site
        if(!$query=$this->uCore->query("common","SELECT
        `site_id`
        FROM
        `u235_sites_modules`
        WHERE
        `site_id`='".$this->site_id."' AND
        `mod_name`='uSup' AND
        `installed`='1'
        ")) $this->uCore->error(50);
        if(!mysqli_num_rows($query)) die('not installed for this site');
    }
    private function set_vars() {
        $this->time_limit=30;
        $this->stop_before_limit=10;

        $this->site_name=$this->get_site_conf('site_name','content');
        $this->site_domain=$this->get_site_conf('site_domain','content');
        $this->u_sroot='http://'.$this->site_domain.'/';

        $this->reminder_days=trim($this->get_site_conf("req_close_reminder_days","uSup"));
        $this->inaction_days=trim($this->get_site_conf("request_inaction_close_days","uSup"));
        if(!uString::isDigits($this->reminder_days)) $this->reminder_days=2;
        if(!uString::isDigits($this->inaction_days)) $this->inaction_days=$this->reminder_days+2;
        if($this->inaction_days<=$this->reminder_days) $this->inaction_days=$this->reminder_days+2;

        $this->reminder_timestamp=time()-$this->reminder_days*24*60*60;

        $this->reminder_days_left=$this->inaction_days-$this->reminder_days;
        $this->close_time_left=time()-($this->inaction_days-$this->reminder_days)*24*60*60;

        $this->write_above_line=$this->get_site_conf("email_write_above_line_content","uSup");
        $this->support_email=$this->get_site_conf("support_email","uSup");
        $this->support_email_from=$this->get_site_conf("support_email_fromname","uSup");

        $this->status2Text_ar['req_open']='Запрос открыт';
        $this->status2Text_ar['req_answered']='Есть ответ на запрос';
        $this->status2Text_ar['req_processing']='Запрос рассматривается';
        $this->status2Text_ar['req_closed']='Запрос закрыт';

        $this->status2Text_ar['case_open']='Кейс открыт';
        $this->status2Text_ar['case_answered']='Есть ответ на кейс';
        $this->status2Text_ar['case_processing']='Кейс в работе';
        $this->status2Text_ar['case_closed']='Кейс закрыт';
        $this->status2Text_ar['case_done']='Кейс выполнен';

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
    }

    private function get_site_conf($field,$mod) {
        if(!$query=$this->uCore->query("pages","SELECT
        `value`
        FROM
        `u235_conf`
        WHERE
        `field`='".$field."' AND
        `mod`='".$mod."' AND
        `site_id`='".$this->site_id."'
        ")) $this->uCore->error(60);
        if(!mysqli_num_rows($query)) $this->uCore->error(70);
        $qr=$query->fetch_object();
        return $qr->value;
    }

    private function delete_tmp_requests() {
        //get all tmp_requests
        if(!$query=$this->uCore->query("uSup","SELECT
        `tic_id`,
        `site_id`
        FROM
        `u235_requests`
        WHERE
        `tic_opened_timestamp`<'".(time()-86400)."' AND
        `tic_status`='new'
        ")) $this->uCore->error(80);
        //delete all tmp requests from db
        if(!$this->uCore->query("uSup","DELETE FROM
        `u235_requests`
        WHERE
        `tic_opened_timestamp`<'".(time()-86400)."' AND
        `tic_status`='new'
        ")) $this->uCore->error(90);
        while($req=$query->fetch_object()) {//delete every tmp request's folder and msgs_files record
            if(!$this->uCore->query("uSup","DELETE FROM
            `u235_msgs_files`
            WHERE
            `tic_id`='".$req->tic_id."' AND
            `site_id`='".$req->site_id."'"));
            @uFunc::rmdir("uSupport/msgs_files/".$req->site_id.'/'.$req->tic_id);
        }

        //get all tmp messages
        if(!$query=$this->uCore->query("uSup","SELECT
        `msg_id`,
        `tic_id`,
        `site_id`
        FROM
        `u235_msgs`
        WHERE
        `msg_timestamp`<'".(time()-86400)."' AND
        `msg_status`='0'
        ")) $this->uCore->error(100);
        //delete all tmp msgs from db
        if(!$this->uCore->query("uSup","DELETE FROM
        `u235_msgs`
        WHERE
        `msg_timestamp`<'".(time()-86400)."' AND
        `msg_status`='0'
        ")) $this->uCore->error(110);
        while($msg=$query->fetch_object()) {//delete every tmp msg's file and msgs_files record
            if(!$query1=$this->uCore->query("uSup","SELECT
            `file_id`
            FROM
            `u235_msgs_files`
            WHERE
            `msg_id`='".$msg->msg_id."' AND
            `tic_id`='".$msg->tic_id."' AND
            `site_id`='".$msg->site_id."'
            ")) $this->uCore->error(120);
            if(mysqli_num_rows($query1)) {
                $msg_file=$query1->fetch_object();
                if(!$this->uCore->query("uSup","DELETE FROM
                `u235_msgs_files`
                WHERE
                `msg_id`='".$msg->msg_id."' AND
                `tic_id`='".$msg->tic_id."' AND
                `site_id`='".$msg->site_id."'
                "));
                @uFunc::rmdir("uSupport/msgs_files/".$msg->site_id.'/'.$msg->tic_id.'/'.$msg_file->file_id);
            }
        }
    }

    private function make_days_word($days_count) {
        $last_digit2word=array('дней','день','дня','дня','дня','дней','дней','дней','дней','дней');

        return $last_digit2word[(int)substr($days_count,-1,1)];
    }

    private function get_old_requests() {
        if(!$query=$this->uCore->query("uSup","SELECT
        `tic_id`,
        `user_id`,
        `cons_id`,
        `tic_changed_timestamp`,
        `tic_notified_about_autoclosing_timestamp`,
        `tic_subject`,
        `tic_status`
        FROM
        `u235_requests`
        WHERE
        `tic_changed_timestamp`<'".$this->reminder_timestamp."' AND
        (`tic_status`='req_answered' OR
        `tic_status`='case_done') AND
        `site_id`='".$this->site_id."'
        ")) $this->uCore->error(130);
        return $query;
    }

    private function get_autors_info($req) {
        //get author's name, email
        if(!$q_author=$this->uCore->query("uAuth","SELECT DISTINCT
        `u235_users`.`user_id`,
        `firstname`,
        `secondname`,
        `lastname`,
        `email`
        FROM
        `u235_users`,
        `u235_usersinfo`
        WHERE
        `u235_users`.`user_id`='".$req->user_id."' AND
        `u235_users`.`user_id`=`u235_usersinfo`.`user_id` AND
        `u235_usersinfo`.`status`='active' AND
        `u235_users`.`status`='active' AND
        `u235_usersinfo`.`site_id`='".$this->site_id."'
        ")) $this->uCore->error(140);
        if(!mysqli_num_rows($q_author)) return false;
        return $q_author->fetch_object();
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
            ")) $this->uCore->error(150);
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
            ")) $this->uCore->error(160);
        }
        return $q_cons;
    }

    private function send_notificatopn_about_closing($req,$author,$q_cons) {
        $tic_subject=uString::sql2text($req->tic_subject);

        $title='Запрос закрыт. #'.$req->tic_id.' '.$tic_subject;

        //notify author
        $html='<p>Здравствуйте, '.$author->firstname.' '.$author->secondname.'</p>
        <div class="msg_text">
            <p>Запрос <a href="'.$this->u_sroot.$this->uCore->mod.'/request_show/'.$req->tic_id.'">#'.$req->tic_id.' '.$tic_subject.'</a>  <strong>закрыт автоматически</strong> так как от Вас не поступило новой информации.</p>
            <p><big>Пожалуйста, <a href="'.$this->u_sroot.$this->uCore->mod.'/request_feedback/'.$this->tic_feedback_hash.'"><b>оставьте отзыв</b> о качестве технической поддержки</a> по этому запросу.</big></p>
        </div>
        <p><small>На всякий случай: <a href="'.$this->u_sroot.$this->uCore->mod.'/requests">войти в техподдержку можно здесь</a>. Сбросить пароль можно в диалоге авторизации</small></p>';

        $this->uCore->uFunc->mail($html ,$title,$author->email,$this->support_email_from,$this->support_email,$this->u_sroot,$this->site_id,'',$this->use_smtp,$this->smtp_settings);


        //notify consultant or operator
        while($cons=$q_cons->fetch_object()) {
            $html='<p>Здравствуйте, '.$cons->firstname.' '.$cons->secondname.'</p>
            <div class="msg_text">
                <p>Запрос <a href="'.$this->u_sroot.$this->uCore->mod.'/request_show/'.$req->tic_id.'">#'.$req->tic_id.' '.$tic_subject.'</a>  от пользователя <a href="'.$this->u_sroot.'uAuth/profile/'.$author->user_id.'">'.$author->firstname.' '.$author->lastname.'</a> закрыт автоматически.</p>
            </div>
            <p><small>На всякий случай: <a href="'.$this->u_sroot.$this->uCore->mod.'/requests">войти в техподдержку можно здесь</a>. Сбросить пароль можно в диалоге авторизации</small></p>';

            $this->uCore->uFunc->mail($html ,$title,$cons->email,$this->support_email_from,$this->support_email,$this->u_sroot,$this->site_id,'',$this->use_smtp,$this->smtp_settings);
        }
    }
    private function send_reminder_about_inaction($req,$author,$q_cons) {
        $tic_subject=uString::sql2text($req->tic_subject);

        $title='[#req-'.$req->tic_id.'] Автоматическое закрытие запроса. '.$tic_subject;

        //notify author
        $html='<p>Здравствуйте, '.$author->firstname.' '.$author->secondname.'</p>
        <div class="msg_text">
            <p><strong>Ваш запрос</strong> <a href="'.$this->u_sroot.$this->uCore->mod.'/request_show/'.$req->tic_id.'">#'.$req->tic_id.' '.$tic_subject.'</a> в техническую поддержку сайта <a href="'.$this->u_sroot.'">'.$this->site_name.'</a><strong> будет автоматически закрыт</strong> через '.$this->reminder_days_left.' '.$this->make_days_word($this->reminder_days_left).', если от Вас не поступит новой информации.</p>
        </div>
        <p><small>При дальнейшем общении по этому запросу включайте в тему письма <i>[#req-'.$req->tic_id.']</i>.</small></p>
        <p><small>Вы можете <a href="'.$this->u_sroot.$this->uCore->mod.'/request_show/'.$req->tic_id.'">писать в запрос на сайте</a> или отвечать на email.</p>
        <p><small>На всякий случай: <a href="'.$this->u_sroot.$this->uCore->mod.'/requests">войти в техподдержку можно здесь</a>. Сбросить пароль можно в диалоге авторизации</small></p>';
        $this->uCore->uFunc->mail($html ,$title,$author->email,$this->support_email_from,$this->support_email,$this->u_sroot,$this->site_id,'<p>'.$this->write_above_line.'</p>',$this->use_smtp,$this->smtp_settings);

        //notify consultant or operator
        while($cons=$q_cons->fetch_object()) {
            $html='<p>Здравствуйте, '.$cons->firstname.' '.$cons->secondname.'</p>
            <div class="msg_text">
                <p><strong>Запрос</strong> <a href="'.$this->u_sroot.$this->uCore->mod.'/request_show/'.$req->tic_id.'">#'.$req->tic_id.' '.$tic_subject.'</a>  со статусом "'.$this->status2Text_ar[$req->tic_status].'" от пользователя <a href="'.$this->u_sroot.'uAuth/profile/'.$author->user_id.'">'.$author->firstname.' '.$author->lastname.'</a> в техническую поддержку сайта <a href="'.$this->u_sroot.'">'.$this->site_name.'</a> <strong>будет автоматически закрыт</strong> через '.$this->reminder_days_left.' '.$this->make_days_word($this->reminder_days_left).', если от автора не поступит новой информации.</p>
            </div>
            <p><small>При дальнейшем общении по этому запросу включайте в тему письма <i>[#req-'.$req->tic_id.']</i>.</small></p>
            <p><small>Вы можете <a href="'.$this->u_sroot.$this->uCore->mod.'/request_show/'.$req->tic_id.'">писать в запрос на сайте</a> или отвечать на email.</p>
            <p><small>На всякий случай: <a href="'.$this->u_sroot.$this->uCore->mod.'/requests">войти в техподдержку можно здесь</a>. Сбросить пароль можно в диалоге авторизации</small></p>';
            $this->uCore->uFunc->mail($html ,$title,$cons->email,$this->support_email_from,$this->support_email,$this->u_sroot,$this->site_id,'<p>'.$this->write_above_line.'</p>',$this->use_smtp,$this->smtp_settings);
        }
    }

    private function close_req($req){
        $author=$this->get_autors_info($req);
        $q_cons=$this->get_cons_info($req);

        //close_req
        if(strpos('s'.$req->tic_status,'case_')) $status='case_closed';
        else $status='req_closed';
        $this->tic_feedback_hash=uFunc::genHash();

        if(!$this->uCore->query("uSup","UPDATE
        `u235_requests`
        SET
        `tic_changed_timestamp`='".time()."',
        `tic_feedback_info`='".$this->tic_feedback_hash."',
        `tic_status`='".$status."'
        WHERE
        `tic_id`='".$req->tic_id."' AND
        `site_id`='".$this->site_id."'
        ")) $this->uCore->error(170);

        //send notifications
        $this->send_notificatopn_about_closing($req,$author,$q_cons);
    }
    private function remind_about_inaction($req) {
        $author=$this->get_autors_info($req);
        $q_cons=$this->get_cons_info($req);

        if(!$this->uCore->query("uSup","UPDATE
        `u235_requests`
        SET
        `tic_notified_about_autoclosing_timestamp`='".time()."'
        WHERE
        `tic_id`='".$req->tic_id."' AND
        `site_id`='".$this->site_id."'
        ")) $this->uCore->error(180);

        //send reminders
        $this->send_reminder_about_inaction($req,$author,$q_cons);
    }

    private function run_throw_requests() {
        $q_requests=$this->get_old_requests();
        while($req=$q_requests->fetch_object()) {
            if(time()>=$this->start_time+$this->time_limit-$this->stop_before_limit) exit('time limit exceeded');
            if($req->tic_notified_about_autoclosing_timestamp=='0') $this->remind_about_inaction($req);
            elseif($req->tic_notified_about_autoclosing_timestamp<$this->close_time_left) $this->close_req($req);
        }
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->secret='yrcl8Pbb6LvtQymJ$LVKIP6Q';
        $this->start_time=time();

        $this->check_data();
        $this->set_vars();

        $this->delete_tmp_requests();

        $this->run_throw_requests();
    }
}
$uSup=new uSup_request_inaction_close_cron($this);?>
done
