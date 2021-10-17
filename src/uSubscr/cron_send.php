<?php
class uSubscr_cron_send {
    private $uCore,$q_users,$start,$lifetime,
        $m_id,$rec_id,$rec_title,$rec_html,
        $site_id,$site_name,$site_email,$site_domain,$u_sroot,
        $secret;
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
        if(!mysqli_num_rows($query)) /*die('');*/die('not installed');
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
            ")) $this->uCore->error(7);
        if(!mysqli_num_rows($query)) $this->uCore->error(9);
        $qr=$query->fetch_object();
        return uString::sql2text($qr->value,true);
    }
    private function get_mailings() {
        //send 1 mailing per time
        if(!$query=$this->uCore->query("uSubscr","SELECT DISTINCT
        `m_id`,
        `u235_records`.`rec_id`,
        `rec_title`,
        `rec_html`
        FROM
        `u235_mailing`,
        `u235_records`
        WHERE
        `u235_mailing`.`status`='running' AND
        `u235_records`.`rec_id`=`u235_mailing`.`rec_id` AND
        `u235_mailing`.`site_id`='".$this->site_id."' AND
        `u235_records`.`site_id`='".$this->site_id."'
        LIMIT 1
        ")) $this->uCore->error(10);
        if(!mysqli_num_rows($query)) return false;

        $mailing=$query->fetch_object();

        $this->m_id=$mailing->m_id;
        $this->rec_id=$mailing->rec_id;
        $this->rec_title=uString::sql2text($mailing->rec_title);
        $this->rec_html=uString::sql2text($mailing->rec_html,true);

        $this->site_email=$this->get_site_conf('site_email','content');
        $this->site_name=$this->get_site_conf('site_name','content');
        $this->site_domain=$this->get_site_conf('site_domain','content');
        $this->u_sroot='http://'.$this->site_domain.'/';


        //get users
        if(!$this->q_users=$this->uCore->query("uSubscr","SELECT DISTINCT
        `user_name`,
        `user_email`,
        `u235_mailing_results`.`hash`,
        `u235_mailing_results`.`user_id`
        FROM
        `u235_users`,
        `u235_mailing_results`
        WHERE
        `u235_mailing_results`.`m_id`='".$mailing->m_id."' AND
        `u235_mailing_results`.`result`='not sent' AND
        `u235_mailing_results`.`site_id`='".$this->site_id."' AND
        `u235_mailing_results`.`user_id`=`u235_users`.`user_id` AND
        `u235_users`.`site_id`='".$this->site_id."'
        ")) $this->uCore->error(11);

        if(!mysqli_num_rows($this->q_users)) {
            return 'nobody found to send';
        }

        echo '<p>start sending</p>';
        return true;
    }
    private function send($get_mailings) {
        echo '<p>check 1</p>';
        if(mysqli_num_rows($this->q_users)) {
            echo '<p>check 2</p>';
            while($user=$this->q_users->fetch_object()) {
                echo '<p>check 3</p>';
                if(($this->start+$this->lifetime)<time()) {
                    echo '<p>check 4</p>';
                    $this->update_mailing_status($get_mailings);
                    break;
                }
                echo '<p>check 5</p>';
                $html=str_replace('{user_name}',$user->user_name,$this->rec_html);
                $html='<p><small><a href="'.$this->u_sroot.$this->uCore->mod.'/page?m_id='.$this->m_id.'&user_id='.$user->user_id.'&hash='.$user->hash.'">Если письмо отображается некорректно, нажмите сюда</a></small></p>'.$html;
                $html.='<p><small>Вы получили это письмо, так как подписаны на новости сайта <a href="http://'.$this->site_domain.'">'.$this->site_name.'</a><br>
                <a href="'.$this->u_sroot.$this->uCore->mod.'/subscription_change/'.$user->user_id.'/'.$user->hash.'">Нажмите сюда, чтобы отписаться от новостей или изменить свою подписку</a>.<img src="'.$this->u_sroot.$this->uCore->mod.'/tracker/px.png?m_id='.$this->m_id.'&user_id='.$user->user_id.'&hash='.$user->hash.'"></small></p>';
                if(uString::isEmail($user->user_email)) {
                    echo '<p>check 6: '.$user->user_email.'</p>';
                    $this->uCore->uFunc->mail($html,$this->rec_title,$user->user_email,$this->site_name,$this->site_email,$this->u_sroot,$this->site_id);
                }
                else {
                    echo '<p>check 6 Skiped: '.$user->user_email.'</p>';
                    uFunc::journal($user->user_email,'uSubscr_cron_send_wrong_emails');
                }
                echo '<p>check 7</p>';
                $this->update_mailing_result($this->m_id,$user->user_id);
            }
        }
        echo '<p>check 8</p>';
        $this->update_mailing_status($get_mailings);
    }
    private function update_mailing_result($m_id,$user_id) {
        if(!$this->uCore->query("uSubscr","UPDATE
        `u235_mailing_results`
        SET
        `result`='sent',
        `timestamp`='".time()."'
        WHERE
        `m_id`='".$m_id."' AND
        `user_id`='".$user_id."' AND
        `site_id`='".$this->site_id."'
        ")) $this->uCore->error(12);
    }
    private function update_mailing_status($get_mailings) {
        if($get_mailings==='noone found to send') {
            $progress=100;
        }
        else {
            //get all running msgs
            if(!$query=$this->uCore->query("uSubscr","SELECT
            COUNT(user_id)
            FROM
            `u235_mailing_results`
            WHERE
            `result`='not sent' AND
            `m_id`='".$this->m_id."' AND
            `site_id`='".$this->site_id."'
            ")) $this->uCore->error(13);
            $qr=$query->fetch_assoc();
            $not_sent=$qr['COUNT(user_id)'];

            //get all sent and read msgs
            if(!$query=$this->uCore->query("uSubscr","SELECT
            COUNT(user_id)
            FROM
            `u235_mailing_results`
            WHERE
            (`result`='sent' OR `result`='read') AND
            `m_id`='".$this->m_id."' AND
            `site_id`='".$this->site_id."'
            ")) $this->uCore->error(14);
            $qr=$query->fetch_assoc();
            $sent=$qr['COUNT(user_id)'];

            if($sent+$not_sent==0) $progress=100;//For case if noone subscribed to this mailing
            else $progress=100*$sent/($sent+$not_sent);
        }
        $status='';
        if($progress==100) $status=",`status`='finished'";

        if(!$this->uCore->query("uSubscr","UPDATE
        `u235_mailing`
        SET
        `progress`='".$progress."'
        ".$status."
        WHERE
        `m_id`='".$this->m_id."' AND
        `site_id`='".$this->site_id."'
        ")) $this->uCore->error(15);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->start=time();
        $this->secret="NhX%wc!!&TmQxBxnXHnMM88maS";
        $this->lifetime=20;//20 second then pause

        $this->check_data();

        $get_mailings=$this->get_mailings();
        if($get_mailings) {
            $this->send($get_mailings);
            echo 'sent';
        }
        else echo 'nothing 2 send';
        //else echo '';
    }
}
$uSubscr=new uSubscr_cron_send($this);
