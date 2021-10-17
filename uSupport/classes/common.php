<?php
namespace uSupport;
require_once "processors/classes/uFunc.php";
require_once "uAuth/classes/common.php";

use PDO;
use PDOException;
use processors\uFunc;
use uString;

class common {
    public $uFunc;
    public $uAuth;
    private $uCore;

    public function is_email_belongs2company($email,$site_id=site_id) {
        //get email domain
        if(!uString::isEmail($email)) return array();

        $at_pos=strpos ($email, '@');
        $domain=substr($email,$at_pos+1);


        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT 
            com_id
            FROM 
            u235_com_email_domains 
            WHERE 
            domain=:domain AND 
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':domain', $domain,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error("uSup common 10");}

        /** @noinspection PhpUndefinedVariableInspection, PhpUndefinedMethodInspection PhpStatementHasEmptyBodyInspection */
        for($i=0; $comp[$i]=$stm->fetch(PDO::FETCH_OBJ); $i++) {};
        return $comp;
    }
    public function attach_user2company($user_id,$com_id,$notify=1,$site_id=site_id) {
        //attach user to company
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("INSERT IGNORE INTO 
              u235_com_users(
              user_id, 
              com_id, 
              admin, 
              site_id
              ) VALUES (
              :user_id, 
              :com_id, 
              0, 
              :site_id
              )");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $com_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uSup common 30'/*.$e->getMessage()*/);}


        if($notify) {
            $site_name=$this->uFunc->getConf('site_name','content',true,$site_id);
            $site_domain=$this->uFunc->getConf('site_domain','content',true,$site_id);
            $u_sroot='http://'.$site_domain.'/';

            $use_smtp=(int)$this->uFunc->getConf('smtp_use_madwww_server','uSup',true,$site_id)=='0';
            if($use_smtp) {
                //port
                //server_name
                //use_ssl
                //user_name
                //password
                $smtp_settings['server_name']=$this->uFunc->getConf('smtp_server_name','uSup',true,$site_id);
                if($smtp_settings['server_name']=='') return false;
                $smtp_settings['port']=$this->uFunc->getConf('smtp_port','uSup',true,$site_id);
                $smtp_settings['user_name']=$this->uFunc->getConf('smtp_user_name','uSup',true,$site_id);
                if($smtp_settings['user_name']=='') return false;
                $smtp_settings['password']=$this->uFunc->getConf('smtp_password','uSup',true,$site_id);
                if($smtp_settings['password']=='') return false;
                $smtp_settings['use_ssl']=$this->uFunc->getConf('smtp_use_ssl','uSup',true,$site_id)=='1';
            }
            else $smtp_settings[0]=0;

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSup")->prepare("SELECT 
                com_title 
                FROM 
                u235_comps 
                WHERE 
                com_id=:com_id AND 
                site_id=:site_id
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $com_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error("uSup common 40");}

            /** @noinspection PhpUndefinedMethodInspection PhpUndefinedVariableInspection */
            $qr=$stm->fetch(PDO::FETCH_OBJ);
            if(!$qr) $this->uFunc->error("uSup common 50");

            $com_title= uString::sql2text($qr->com_title,1);

            $html='<p>Здравствуйте,</p>
            <div class="msg_text">
                <p>Вы добавлены в компанию '.$com_title.' в технической поддержке сайта <a href="'.$u_sroot.'">'.$site_name.'</a></p>
            </div>
            <p><small>На всякий случай: <a href="'.$u_sroot.'uSupport/requests">войти в техподдержку можно здесь</a>. Сбросить пароль можно в диалоге авторизации</small></p>';

            $title='Вы добавлены в компанию в технической поддержке сайта '.$site_name;
            $user_data=$this->uAuth->user_id2user_data($user_id,"email");
            if($user_data) {
                $this->uFunc->mail($html, $title, $user_data->email, $this->uFunc->getConf("support_email_fromname", "uSup", true, $site_id), $this->uFunc->getConf("support_email", "uSup", true, $site_id), $u_sroot, $site_id, '', $use_smtp, $smtp_settings);
            }
        }
        return 0;
    }
    public function user_id2comps($user_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
            com_id,
            default_com
            FROM
            u235_com_users
            WHERE
            user_id=:user_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpStatementHasEmptyBodyInspection */
            /** @noinspection PhpUndefinedMethodInspection */
            for($i=0; $comps[$i]=$stm->fetch(PDO::FETCH_OBJ); $i++) {};
            return $comps;
        }
        catch(PDOException $e) {$this->uFunc->error('uSup common 60'/*.$e->getMessage()*/);}
        return array();
    }
    public function user_id2default_com_id($user_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT 
            com_id
            FROM
            u235_com_users
            WHERE
            default_com=1 AND
            user_id=:user_id AND
            site_id=:site_id 
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($com=$stm->fetch(PDO::FETCH_OBJ)) {//user has default com
                return $com->com_id;
            }
            else {//user has no default com
                $user_comps=$this->user_id2comps($user_id,$site_id);
                if(count($user_comps)>1) {
                    $this->set_com_as_default($user_id,$user_comps[0]->com_id,$site_id);
                    return $user_comps[0]->com_id;
                }
                else return 0;//user has no comps at all
            }
        }
        catch(PDOException $e) {$this->uFunc->error('uSup common 70'/*.$e->getMessage()*/);}
        return 0;
    }
    public function set_com_as_default($user_id,$com_id,$site_id=site_id) {
        //reset all comps default to 0;
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("UPDATE 
            u235_com_users
            SET
            default_com=0
            WHERE
            user_id=:user_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uSup common 80'/*.$e->getMessage()*/);}

        //set needed com as default
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("UPDATE 
            u235_com_users
            SET
            default_com=1
            WHERE
            user_id=:user_id AND
            com_id=:com_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $com_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uSup common 90'/*.$e->getMessage()*/);}
    }
    public function define_requests_shown_tip($uSup_settings) {
        $cnt="Отображены ";
                if(
                    ($uSup_settings['show_requests']=='1'&&$uSup_settings['show_cases']=='1')||
                    ($uSup_settings['show_requests']!='1'&&$uSup_settings['show_cases']!='1')
                ) $cnt.="<b>Запросы и Кейсы</b> ";
                elseif($uSup_settings['show_requests']=='1') $cnt.="<b>Запросы</b> ";
                elseif($uSup_settings['show_cases']=='1') $cnt.="<b>Кейсы</b> ";

                if(
                    ($uSup_settings['show_opened']=='1'&&$uSup_settings['show_answered']=='1'&&$uSup_settings['show_done']=='1'&&$uSup_settings['show_closed']=='1')
                    ||
                    ($uSup_settings['show_opened']!='1'&&$uSup_settings['show_answered']!='1'&&$uSup_settings['show_done']!='1'&&$uSup_settings['show_closed']!='1')
                ) $cnt.=" <b>с любым статусом</b> ";

                elseif($uSup_settings['show_opened']=='1'&&$uSup_settings['show_answered']=='1'&&$uSup_settings['show_done']=='1'&&$uSup_settings['show_closed']!='1') $cnt.='со статусом <b>"Открыт", "Отвечен" или "Выполнен"</b> ';
                elseif($uSup_settings['show_opened']=='1'&&$uSup_settings['show_answered']=='1'&&$uSup_settings['show_done']!='1'&&$uSup_settings['show_closed']=='1') $cnt.='со статусом <b>"Открыт", "Отвечен" или "Закрыт"</b> ';
                elseif($uSup_settings['show_opened']=='1'&&$uSup_settings['show_answered']!='1'&&$uSup_settings['show_done']=='1'&&$uSup_settings['show_closed']=='1') $cnt.='со статусом <b>"Открыт", "Выполнен" или "Закрыт"</b> ';
                elseif($uSup_settings['show_opened']!='1'&&$uSup_settings['show_answered']=='1'&&$uSup_settings['show_done']=='1'&&$uSup_settings['show_closed']=='1') $cnt.='со статусом <b>"Отвечен", "Выполнен" или "Закрыт"</b> ';

                elseif($uSup_settings['show_opened']=='1'&&$uSup_settings['show_answered']=='1'&&$uSup_settings['show_done']!='1'&&$uSup_settings['show_closed']!='1') $cnt.='со статусом <b>"Открыт" или "Отвечен"</b> ';
                elseif($uSup_settings['show_opened']=='1'&&$uSup_settings['show_answered']!='1'&&$uSup_settings['show_done']=='1'&&$uSup_settings['show_closed']!='1') $cnt.='со статусом <b>"Открыт" или "Выполнен"</b> ';
                elseif($uSup_settings['show_opened']=='1'&&$uSup_settings['show_answered']!='1'&&$uSup_settings['show_done']!='1'&&$uSup_settings['show_closed']=='1') $cnt.='со статусом <b>"Открыт" или "Закрыт"</b> ';

                elseif($uSup_settings['show_opened']!='1'&&$uSup_settings['show_answered']=='1'&&$uSup_settings['show_done']!='1'&&$uSup_settings['show_closed']=='1') $cnt.='со статусом <b>"Отвечен" или "Закрыт"</b> ';
                elseif($uSup_settings['show_opened']!='1'&&$uSup_settings['show_answered']=='1'&&$uSup_settings['show_done']=='1'&&$uSup_settings['show_closed']!='1') $cnt.='со статусом <b>"Отвечен" или "Выполнен"</b> ';

                elseif($uSup_settings['show_opened']!='1'&&$uSup_settings['show_answered']!='1'&&$uSup_settings['show_done']=='1'&&$uSup_settings['show_closed']=='1') $cnt.='со статусом <b>"Выполнен" или "Закрыт"</b> ';

                elseif($uSup_settings['show_opened']=='1'&&$uSup_settings['show_answered']!='1'&&$uSup_settings['show_done']!='1'&&$uSup_settings['show_closed']!='1') $cnt.='Только со статусом <b>"Открыт"</b> ';
                elseif($uSup_settings['show_opened']!='1'&&$uSup_settings['show_answered']=='1'&&$uSup_settings['show_done']!='1'&&$uSup_settings['show_closed']!='1') $cnt.='Только со статусом <b>Отвечен"</b> ';
                elseif($uSup_settings['show_opened']!='1'&&$uSup_settings['show_answered']!='1'&&$uSup_settings['show_done']=='1'&&$uSup_settings['show_closed']!='1') $cnt.='Только со статусом <b>"Выполнен"</b> ';
                elseif($uSup_settings['show_opened']!='1'&&$uSup_settings['show_answered']!='1'&&$uSup_settings['show_done']!='1'&&$uSup_settings['show_closed']=='1') $cnt.='Только со статусом <b>"Закрыт"</b> ';

                if(
                    ($uSup_settings['show_assigned2me']=='1'&&$uSup_settings['show_unassigned']=='1'&&$uSup_settings['show_assigned2others']=='1')||
                    ($uSup_settings['show_assigned2me']!='1'&&$uSup_settings['show_unassigned']!='1'&&$uSup_settings['show_assigned2others']!='1')
                ) $cnt.="назначенные кому угодно или неназначенные никому";

                elseif($uSup_settings['show_assigned2me']=='1'&&$uSup_settings['show_unassigned']=='1'&&$uSup_settings['show_assigned2others']!='1') $cnt.="назначенные вам или неназначенные никому";
                elseif($uSup_settings['show_assigned2me']=='1'&&$uSup_settings['show_unassigned']!='1'&&$uSup_settings['show_assigned2others']=='1') $cnt.="назначенные вам или другим консультантам";
                elseif($uSup_settings['show_assigned2me']!='1'&&$uSup_settings['show_unassigned']=='1'&&$uSup_settings['show_assigned2others']=='1') $cnt.="назначенные другим консультантам или неназначенные никому";

                elseif($uSup_settings['show_assigned2me']=='1'&&$uSup_settings['show_unassigned']!='1'&&$uSup_settings['show_assigned2others']!='1') $cnt.="назначенные только вам";
                elseif($uSup_settings['show_assigned2me']!='1'&&$uSup_settings['show_unassigned']=='1'&&$uSup_settings['show_assigned2others']!='1') $cnt.="только неназначенные никому";
                elseif($uSup_settings['show_assigned2me']!='1'&&$uSup_settings['show_unassigned']!='1'&&$uSup_settings['show_assigned2others']=='1') $cnt.="назначенные только другим консультантам";


                return $cnt;
    }

    public function com_id2com_info($com_id,$q_info="com_title",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT 
            ".$q_info."
            FROM 
            u235_comps
            WHERE 
            com_id=:com_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $com_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $qr=$stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('uSup common 100'/*.$e->getMessage()*/);}
        return 0;
    }
    public function req_id2info($req_id,$q_info="tic_subject",$site_id=site_id) {
        //get ticket's user_id
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
            ".$q_info."
            FROM
            u235_requests
            WHERE
            tic_id=:tic_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tic_id', $req_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('uSup common 110'/*.$e->getMessage()*/);}
        return 0;
    }

    public function get_operators($q_data="u235_users.user_id",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uAuth")->prepare("SELECT DISTINCT
            ".$q_data."
            FROM 
            u235_users
            JOIN
            u235_usersinfo
            ON
            u235_users.user_id=u235_usersinfo.user_id
            JOIN
            u235_usersinfo_groups
            ON
            u235_usersinfo_groups.user_id=u235_usersinfo.user_id AND
            u235_usersinfo_groups.site_id=u235_usersinfo.site_id
            WHERE
            u235_usersinfo.site_id=:site_id AND
            group_id=4
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uSup common 120'/*.$e->getMessage()*/);}
        return 0;
    }
    public function get_com_admins($q_data="user_id",$com_id=0,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT 
            ".$q_data."
            FROM 
            u235_com_users
            WHERE
            admin=1 AND
            site_id=:site_id AND
            com_id=:com_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $com_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uSup common 130'/*.$e->getMessage()*/);}
        return 0;
    }
    public function get_com_admins_to_notify_about_requests($q_data="user_id",$com_id=0,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT 
            ".$q_data."
            FROM 
            u235_com_users
            WHERE
            admin=1 AND
            notify_about_new_requests=1 AND
            site_id=:site_id AND
            com_id=:com_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $com_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uSup common 140'.$e->getMessage());}
        return 0;
    }
    public function get_com_users($q_data="user_id",$com_id=0,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT 
            ".$q_data."
            FROM 
            u235_com_users
            WHERE
            site_id=:site_id AND
            com_id=:com_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $com_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uSup common 150'/*.$e->getMessage()*/);}
        return 0;
    }

    public function msg_id2hash($msg_id,$site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT 
            hash 
            FROM 
            u235_file_access_hashes 
            WHERE 
            msg_id=:msg_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':msg_id', $msg_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uSup common 160'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedMethodInspection */
        if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) return "";

        return $qr->hash;
    }
    public function get_new_req_id($site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
            tic_id
            FROM
            u235_requests
            WHERE
            site_id=:site_id
            ORDER BY
            tic_id DESC
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr->tic_id+1;
            else return 1;
        }
        catch(PDOException $e) {$this->uFunc->error('uSup common 170'/*.$e->getMessage()*/);}

        return 1;
    }

    public function make_msg_hash($msg_id,$site_id=site_id) {
        //delete old hashes
        $hash_lifetime=1296000;//15 days

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("DELETE
            FROM
            u235_file_access_hashes
            WHERE
            timestamp<:timestamp
            ");
            $timestamp=time()-$hash_lifetime;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':timestamp', $timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uSup common 180'/*.$e->getMessage()*/);}

        $msg_hash=$this->uFunc->genHash();

        //add new hash
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("INSERT INTO
            u235_file_access_hashes (
            msg_id,
            hash,
            timestamp,
            site_id
            ) VALUES (
            :msg_id,
            :hash,
            :timestamp,
            :site_id
            )
            ");
            $timestamp=time();
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':timestamp', $timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':msg_id', $msg_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':hash', $msg_hash,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uSup common 190'/*.$e->getMessage()*/);}

        return $msg_hash;
    }

    public function get_msg_files_html($msg_id,$site_id=site_id) {
        $site_domain=$this->uFunc->getConf('site_domain','content',true,$site_id);
        $u_sroot='http://'.$site_domain.'/';

        //get msg hash
        $msg_hash=$this->msg_id2hash($msg_id,$site_id);

        //Достаём список файлов, прикреплённых к сообщению
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
            file_id,
            filename,
            file_size,
            file_mime,
            timestamp
            FROM
            u235_msgs_files
            WHERE
            msg_id=:msg_id AND
            site_id=:site_id
            ORDER BY
            file_id ASC
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':msg_id', $msg_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uSup common 200'/*.$e->getMessage()*/);}

        /** @noinspection PhpStatementHasEmptyBodyInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        for($i=0; $files_ar[$i]=$stm->fetch(PDO::FETCH_OBJ); $i++);

        $filesHtml='<p><strong>Прикрепленные файлы:</strong></p>
            <div class="files">';
            for($i=0;$files=$files_ar[$i];$i++) {
                if(!strpos('_'.$files->file_mime,'image')) {
                    $filename=uString::sql2text($files->filename);
                    $filesHtml.='<a href="'.$u_sroot.'uSupport/file/'.$files->file_id.'" target="_blank">'.$filename.'</a><br>';
                }
            }
            $filesHtml.='</div>
            <div class="images">';
            for($i=0;$files=$files_ar[$i];$i++) {
                if(strpos('_'.$files->file_mime,'image')) {
                    $filename=uString::sql2text($files->filename);
                    $filesHtml.='<a href="'.$u_sroot.'uSupport/file/'.$files->file_id.'/img.jpg?'.$files->timestamp.'" title="'.$filename.'"><img src="'.$u_sroot.'uSupport/file/'.$files->file_id.'/sm/'.$msg_id.'/'.$msg_hash.'/'.$files->timestamp.'.jpg" alt="'.$filename.'" class="img-thumbnail" />
                    </a> ';
                }
            }
            $filesHtml.='</div>';

            if($files_ar[0]) return $filesHtml;
            else return "";
    }

    public function request_id2last_msg_info($request_id,$select="msg_id",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT 
            ".$select." 
            FROM 
            u235_msgs 
            WHERE 
            tic_id=:tic_id AND
            site_id=:site_id
            ORDER BY
            msg_timestamp DESC
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tic_id', $request_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uSup common 205'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedMethodInspection */
        return $stm->fetch(PDO::FETCH_OBJ);
    }

    public function escalate_request($escalated=1,$request_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("UPDATE
            u235_requests
            SET
            escalated=:escalated
            WHERE 
            tic_id=:tic_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':escalated', $escalated,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tic_id', $request_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uSup common 210'/*.$e->getMessage()*/);}
    }

    public function new_request_cons_notification ($recipient_firstname,$recipient_secondname,$recipient_email,$request_id,$site_id) {
        //"Write above this line" text
        $write_above_line=$this->uFunc->getConf("email_write_above_line_content","uSup",true,$site_id);

        //u_sroot
        $site_domain=$this->uFunc->getConf('site_domain','content',true,$site_id);
        $u_sroot='http://'.$site_domain.'/';

        //Request info
        if(!$req_info=$this->req_id2info($request_id,"tic_subject,user_id,company_id")) return 0;
        $request_subject=$req_info->tic_subject;
        $request_company_id=$req_info->company_id;
        $author_user_id=(int)$req_info->user_id;

        //Author info
        if(!$author_info=$this->uAuth->user_id2user_data($author_user_id,"firstname,secondname,lastname,status")) return 0;
        $author_firstname=uString::sql2text($author_info->firstname,1);
        $author_secondname=uString::sql2text($author_info->secondname,1);
        $author_lastname=uString::sql2text($author_info->lastname,1);

//        if(!$author_usersinfo=$this->uAuth->user_id2usersinfo($author_user_id,"status")) return 0;
//        if($author_info->status=="active"&&$author_usersinfo->status=="active") $author_account_is_activated=1;
//        else $author_account_is_activated=0;

//        if(!$author_account_is_activated) return 0;

        //Email config
        $email_from_email=$this->uFunc->getConf("support_email","uSup",true,$site_id);
        $email_from_text=$this->uFunc->getConf("support_email_fromname","uSup",true,$site_id);

        $email_title='[#req-'.$request_id.'] '.$request_subject;

        //SMTP config
        $use_smtp=$this->uFunc->getConf('smtp_use_madwww_server','uSup',true,$site_id)=='0';
        if($use_smtp) {
            //port
            //server_name
            //use_ssl
            //user_name
            //password
            $smtp_settings['server_name']=$this->uFunc->getConf('smtp_server_name','uSup',true,$site_id);
            if($smtp_settings['server_name']=='') return 0;
            $smtp_settings['port']=$this->uFunc->getConf('smtp_port','uSup',true,$site_id);
            $smtp_settings['user_name']=$this->uFunc->getConf('smtp_user_name','uSup',true,$site_id);
            if($smtp_settings['user_name']=='') return 0;
            $smtp_settings['password']=$this->uFunc->getConf('smtp_password','uSup',true,$site_id);
            if($smtp_settings['password']=='') return 0;
            $smtp_settings['use_ssl']=$this->uFunc->getConf('smtp_use_ssl','uSup',true,$site_id)=='1';
        }
        else $smtp_settings[0]=0;

        //Message info
        if(!$msg_info=$this->request_id2last_msg_info($request_id,"msg_id,msg_text",$site_id)) return 0;
        $msg_id=$msg_info->msg_id;
        $msg_text=uString::sql2text($msg_info->msg_text,1);

        //Request's Message Files
        $request_files_html=$this->get_msg_files_html($msg_id,$site_id);

        //Company's info
        if($company_info=$this->com_id2com_info($request_company_id,"com_title",$site_id)) {
            $com_title=uString::sql2text($company_info->com_title,1);
        }
        else $com_title="Без компании";

        //Notification
        $html='<p>Здравствуйте, '.$recipient_firstname.' '.$recipient_secondname.'</p>
            <div class="msg_text">
                <p>Новый запрос в технической поддержке</a>.</p>
                <p><strong>Автор:</strong> '.$author_firstname.' '.$author_secondname.' '.$author_lastname.'.</p>
                <p><strong>Компания:</strong> '.$com_title.'.</p>
                <h4><a href="'.$u_sroot.'uSupport/request_show/'.$request_id.'">#'.$request_id.' '.$request_subject.'</a></h4>
                <div>'.nl2br($msg_text).
            '<p>&nbsp;</p>'.
            $request_files_html.
            '</div>
            </div>
            <p><small>При дальнейшем общении по этому запросу оставляйте в теме письма <i>[#req-'.$request_id.']</i>.</small></p>
            <p><small>Вы можете <a href="'.$u_sroot.'uSupport/request_show/'.$request_id.'">писать в запрос на сайте</a> или отвечать на email.</small></p>
            <p><small>На всякий случай: <a href="'.$u_sroot.'uSupport/requests">войти в техподдержку можно здесь</a>. Сбросить пароль можно в диалоге авторизации здесь: <a href="'.$u_sroot.'uAuth/enter">'.$u_sroot.'uAuth/enter</a></small></p>';

        $this->uFunc->mail($html,$email_title,$recipient_email,$email_from_text,$email_from_email,$u_sroot,$site_id,'<p>'.$write_above_line.'</p>',$use_smtp,$smtp_settings);
        return 1;
    }

    public function new_msg_cons_notification($request_id,$recipient_email,$recipient_firstname,$site_id) {
        //u_sroot
        $site_name=$this->uFunc->getConf('site_name','content',true,$site_id);
        $site_domain=$this->uFunc->getConf('site_domain','content',true,$site_id);
        $u_sroot='http://'.$site_domain.'/';

        //request info
        if(!$req_info=$this->req_id2info($request_id,"tic_subject,user_id,company_id",$site_id)) return 0;
        $request_title=uString::sql2text($req_info->tic_subject,1);
        $author_user_id=(int)$req_info->user_id;
        $company_id=(int)$req_info->company_id;

        //company_info
        if($company_info=$this->com_id2com_info($company_id,"com_title",$site_id)) {
            $company_name = uString::sql2text($company_info->com_title, 1);
        }
        else $company_name="Без компании";

        //Author's info
        if(!$author_info=$this->uAuth->user_id2user_data($author_user_id,"firstname,secondname,lastname")) return 0;
        $author_firstname=uString::sql2text($author_info->firstname,1);
        $author_secondname=uString::sql2text($author_info->secondname,1);
        $author_lastname=uString::sql2text($author_info->lastname,1);

        //Msg info
        if(!$msg_info=$this->request_id2last_msg_info($request_id,"msg_id,msg_text",$site_id)) return 0;
        $msg_id=(int)$msg_info->msg_id;
        $msg_text=uString::sql2text($msg_info->msg_text,1);
        $msg_files=$this->get_msg_files_html($msg_id,$site_id);

        //email config
        $email_title='[#req-'.$request_id.'] '.$request_title.'. Новое сообщение.';

        $email_from_email=$this->uFunc->getConf("support_email","uSup",true,$site_id);
        $email_from_text=$this->uFunc->getConf("support_email_fromname","uSup",true,$site_id);

        //SMTP config
        $use_smtp=$this->uFunc->getConf('smtp_use_madwww_server','uSup',true,$site_id)=='0';
        if($use_smtp) {
            //port
            //server_name
            //use_ssl
            //user_name
            //password
            $smtp_settings['server_name']=$this->uFunc->getConf('smtp_server_name','uSup',true,$site_id);
            if($smtp_settings['server_name']=='') return 0;
            $smtp_settings['port']=$this->uFunc->getConf('smtp_port','uSup',true,$site_id);
            $smtp_settings['user_name']=$this->uFunc->getConf('smtp_user_name','uSup',true,$site_id);
            if($smtp_settings['user_name']=='') return 0;
            $smtp_settings['password']=$this->uFunc->getConf('smtp_password','uSup',true,$site_id);
            if($smtp_settings['password']=='') return 0;
            $smtp_settings['use_ssl']=$this->uFunc->getConf('smtp_use_ssl','uSup',true,$site_id)=='1';
        }
        else $smtp_settings[0]=0;

        //"Write above this line" text
        $write_above_line=$this->uFunc->getConf("email_write_above_line_content","uSup",true,$site_id);

        $email_text='<p>Здравствуйте, '.$recipient_firstname.'</p>'.
        '<div class="msg_text">
            <p>Новое сообщение в запросе <a href="'.$u_sroot.'uSupport/request_show/'.$request_id.'">#'.$request_id.' - '.$request_title.'</a>.</p>
            <p><strong>Автор</strong>: '.$author_firstname.' '.$author_secondname.' '.$author_lastname.'.</p>
            <p><strong>Компания:</strong> '.$company_name.'.</p>
            <div>'.nl2br($msg_text).
            '<p>&nbsp;</p>'.
            $msg_files.
            '</div>
        </div>
        <p><small>При дальнейшем общении по этому запросу вставляйте в тему письма <i>[#req-'.$request_id.']</i>.</small></p>
        <p><small>Вы можете писать в запрос на сайте <a href="'.$u_sroot.'uSupport/request_show/'.$request_id.'">'.$site_name.'</a> или отвечаяя на email.</small></p>
        <p><small>На всякий случай: <a href="'.$u_sroot.'uSupport/requests">войти в техподдержку можно здесь</a>. Сбросить пароль можно в диалоге авторизации</small></p>';

        $this->uFunc->mail($email_text,$email_title,$recipient_email,$email_from_text,$email_from_email,$u_sroot,$site_id,'<p>'.$write_above_line.'</p>',$use_smtp,$smtp_settings);

        return 1;
    }
    public function new_msg_author_notification(
        $request_id,
        $site_id
    ) {
        //u_sroot
        $site_name=$this->uFunc->getConf('site_name','content',true,$site_id);
        $site_domain=$this->uFunc->getConf('site_domain','content',true,$site_id);
        $u_sroot='http://'.$site_domain.'/';

        //request info
        if(!$req_info=$this->req_id2info($request_id,"tic_subject,user_id",$site_id)) return 0;
        $request_title=uString::sql2text($req_info->tic_subject,1);
        $author_user_id=(int)$req_info->user_id;

        //Author's info
        if(!$author_info=$this->uAuth->user_id2user_data($author_user_id,"firstname,email")) return 0;
        $author_firstname=uString::sql2text($author_info->firstname,1);
        $author_email=$author_info->email;

        //Msg info
        if(!$msg_info=$this->request_id2last_msg_info($request_id,"msg_id,msg_text",$site_id)) return 0;
        $msg_id=(int)$msg_info->msg_id;
        $msg_text=uString::sql2text($msg_info->msg_text,1);
        $msg_files=$this->get_msg_files_html($msg_id,$site_id);

        //email config
        $email_title='[#req-'.$request_id.'] '.$request_title.'. Новое сообщение.';

        $email_from_email=$this->uFunc->getConf("support_email","uSup",true,$site_id);
        $email_from_text=$this->uFunc->getConf("support_email_fromname","uSup",true,$site_id);

        //SMTP config
        $use_smtp=$this->uFunc->getConf('smtp_use_madwww_server','uSup',true,$site_id)=='0';
        if($use_smtp) {
            //port
            //server_name
            //use_ssl
            //user_name
            //password
            $smtp_settings['server_name']=$this->uFunc->getConf('smtp_server_name','uSup',true,$site_id);
            if($smtp_settings['server_name']=='') return 0;
            $smtp_settings['port']=$this->uFunc->getConf('smtp_port','uSup',true,$site_id);
            $smtp_settings['user_name']=$this->uFunc->getConf('smtp_user_name','uSup',true,$site_id);
            if($smtp_settings['user_name']=='') return 0;
            $smtp_settings['password']=$this->uFunc->getConf('smtp_password','uSup',true,$site_id);
            if($smtp_settings['password']=='') return 0;
            $smtp_settings['use_ssl']=$this->uFunc->getConf('smtp_use_ssl','uSup',true,$site_id)=='1';
        }
        else $smtp_settings[0]=0;

        //"Write above this line" text
        $write_above_line=$this->uFunc->getConf("email_write_above_line_content","uSup",true,$site_id);

        $email_text='<p>Здравствуйте, '.$author_firstname.'</p>'.
        '<div class="msg_text">
            <p>Новый ответ в запросе <a href="'.$u_sroot.'uSupport/request_show/'.$request_id.'">#'.$request_id.' - '.$request_title.'</a>.</p>
            <div>'.nl2br($msg_text).
            '<p>&nbsp;</p>'.
            $msg_files.
            '</div>
        </div>
        <p><small>При дальнейшем общении по этому запросу, оставляйте в теме письма <i>[#req-'.$request_id.']</i>.</small></p>
        <p><small>Вы можете писать в запрос на сайте <a href="'.$u_sroot.'uSupport/request_show/'.$request_id.'">'.$site_name.'</a> или отвечаяя на email.</small></p>
        <p><small>На всякий случай: <a href="'.$u_sroot.'uSupport/requests">войти в техподдержку можно здесь</a>. Сбросить пароль можно в диалоге авторизации</small></p>';

        $this->uFunc->mail($email_text,$email_title,$author_email,$email_from_text,$email_from_email,$u_sroot,$site_id,'<p>'.$write_above_line.'</p>',$use_smtp,$smtp_settings);

        return 1;
    }

    public function new_account_is_created_notification(
        $user_name,
        $author_email,
        $account_confirmation_hash,
        $account_password,
        $site_id
        ) {

        $site_name=$this->uFunc->getConf('site_name','content',true,$site_id);
        $site_domain=$this->uFunc->getConf('site_domain','content',true,$site_id);
        $u_sroot='http://'.$site_domain.'/';

        $email_from_email=$this->uFunc->getConf("support_email","uSup",true,$site_id);
        $email_from_text=$this->uFunc->getConf("support_email_fromname","uSup",true,$site_id);

        $use_smtp=$this->uFunc->getConf('smtp_use_madwww_server','uSup',true,$site_id)=='0';
        if($use_smtp) {
            //port
            //server_name
            //use_ssl
            //user_name
            //password
            $smtp_settings['server_name']=$this->uFunc->getConf('smtp_server_name','uSup',true,$site_id);
            if($smtp_settings['server_name']=='') return 0;
            $smtp_settings['port']=$this->uFunc->getConf('smtp_port','uSup',true,$site_id);
            $smtp_settings['user_name']=$this->uFunc->getConf('smtp_user_name','uSup',true,$site_id);
            if($smtp_settings['user_name']=='') return 0;
            $smtp_settings['password']=$this->uFunc->getConf('smtp_password','uSup',true,$site_id);
            if($smtp_settings['password']=='') return 0;
            $smtp_settings['use_ssl']=$this->uFunc->getConf('smtp_use_ssl','uSup',true,$site_id)=='1';
        }
        else $smtp_settings[0]=0;

        $terms_page_id=(int)$this->uFunc->getConf("privacy_terms_text_id","content",1);
        if($terms_page_id) {
            $txt_obj=$this->uFunc->getStatic_data_by_id($terms_page_id,"page_name");
            if($txt_obj) {
                $terms_link = '<a target="_blank" href="' . $u_sroot . 'page/' . $txt_obj->page_name . '">';
                $terms_link_closer = "</a>";
            }
        }

        $html='<p>Здравствуйте, '.$user_name.'</p>
            <div class="msg_text">
                <p>Для Вас создана учетная запись в системе технической поддержки на сайте <a href="'.$u_sroot.'">'.$site_name.'</a></p>
                <p>&nbsp;</p>
                <h4>Ваши данные:</h4>
                <p><strong>Email:</strong> '.$author_email.'</p>
                <p><strong>Пароль:</strong> '.$account_password.'</p>
                </div>
                <p><small>Имя и email взяты автоматически из вашего письма. Если они не соответствуют вашим данным, пожалуйста, измените их в своем профиле.</small></p>
                <p>Если Вы не регистрировались - просто удалите это письмо.</p>
                <p>Продолжая использовать свою учетную запись на сайте <a href="'.$u_sroot.'">'.$site_name.'</a>, вы даете '.$terms_link.'согласие на обработку своих персональных данных.'.$terms_link_closer.'</p>';

        $title='Для Вас создана учетная запись на сайте '.$site_name;
        $this->uFunc->mail($html,$title,$author_email,$email_from_text,$email_from_email,$u_sroot,$site_id,'',$use_smtp,$smtp_settings);

        return 1;
    }

    public function reject_forbidden_user_request_notification(
        $author_email,
        $site_id
    ) {
        $site_name=$this->uFunc->getConf('site_name','content',true,$site_id);
        $site_domain=$this->uFunc->getConf('site_domain','content',true,$site_id);
        $u_sroot='http://'.$site_domain.'/';

        $email_from_email=$this->uFunc->getConf("support_email","uSup",true,$site_id);
        $email_from_text=$this->uFunc->getConf("support_email_fromname","uSup",true,$site_id);

        $use_smtp=$this->uFunc->getConf('smtp_use_madwww_server','uSup',true,$site_id)=='0';
        if($use_smtp) {
            //port
            //server_name
            //use_ssl
            //user_name
            //password
            $smtp_settings['server_name']=$this->uFunc->getConf('smtp_server_name','uSup',true,$site_id);
            if($smtp_settings['server_name']=='') return 0;
            $smtp_settings['port']=$this->uFunc->getConf('smtp_port','uSup',true,$site_id);
            $smtp_settings['user_name']=$this->uFunc->getConf('smtp_user_name','uSup',true,$site_id);
            if($smtp_settings['user_name']=='') return 0;
            $smtp_settings['password']=$this->uFunc->getConf('smtp_password','uSup',true,$site_id);
            if($smtp_settings['password']=='') return 0;
            $smtp_settings['use_ssl']=$this->uFunc->getConf('smtp_use_ssl','uSup',true,$site_id)=='1';
        }
        else $smtp_settings[0]=0;

        $html='<p>Здравствуйте,</p>
        <div class="msg_text">
            <p>К сожалению мы не можем обработать Ваше письмо, отправленное на '.$email_from_email.', так как Вы ('.$author_email.') не зарегистрированы в нашей системе технической поддержки.</p>
            <p>Если мы обслуживаем Вас или Вашу компанию то, пожалуйста, попросите своего системного администратора или наших специалистов зарегистрировать Вас в системе техподдержки и добавить в Вашу компанию.</p>
            <p>Нашу контактную информацию Вы можете найти на нашем сайте <a href="'.$u_sroot.'">'.$site_name.'</a></p>
        </div>';
        $title='Мы не можем обработать ваш запрос';
        $this->uFunc->mail($html,$title,$author_email,$email_from_text,$email_from_email,$u_sroot,$site_id,'',$use_smtp,$smtp_settings);

        return 1;
    }

    private function write_change_com_hash($com_id,$req_id,$user_id,$site_id=site_id) {
        $hash=$this->uFunc->genHash();

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("INSERT INTO
            u235_requests_change_com_links (
            tic_id,
            com_id,
            user_id,
            hash,
            timestamp,
            site_id
            ) VALUES (
            :tic_id,
            :com_id,
            :user_id,
            :hash,
            :timestamp,
            :site_id
            )
            ");
            $timestamp=time();
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tic_id', $req_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $com_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':timestamp', $timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':hash', $hash,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uSup common 215'/*.$e->getMessage()*/);}

        return $hash;
    }

    public function request_is_received_notification($request_id,$allow_change_company_id=1,$site_id=site_id) {
        //"Write above this line" text
        $write_above_line=$this->uFunc->getConf("email_write_above_line_content","uSup",true,$site_id);

        //u_sroot
        $site_domain=$this->uFunc->getConf('site_domain','content',true,$site_id);
        $u_sroot='http://'.$site_domain.'/';

        //Request info
        if(!$req_info=$this->req_id2info($request_id,"tic_subject,user_id,confirmation_hash,company_id")) return 0;
        $request_subject=$req_info->tic_subject;
        $request_confirmation_hash=$req_info->confirmation_hash;
        $request_company_id=$req_info->company_id;
        $author_user_id=(int)$req_info->user_id;

        //Author info
        if(!$author_info=$this->uAuth->user_id2user_data($author_user_id,"firstname,secondname,status,email")) return 0;
        $author_firstname=uString::sql2text($author_info->firstname,1);
        $author_secondname=uString::sql2text($author_info->secondname,1);
        $author_user_email=$author_info->email;

//        if(!$author_usersinfo=$this->uAuth->user_id2usersinfo($author_user_id,"status")) return 0;

//        if($author_info->status==$author_usersinfo->status&&$author_info->status=="active") $author_account_is_activated=1;
//        else $author_account_is_activated=0;

        $q_author_companies=$this->user_id2comps($author_user_id,$site_id);
        if(count($q_author_companies)>2) $author_is_member_of_several_companies=1;
        else $author_is_member_of_several_companies=0;

        //Email config
        $email_from_email=$this->uFunc->getConf("support_email","uSup",true,$site_id);
        $email_from_text=$this->uFunc->getConf("support_email_fromname","uSup",true,$site_id);

        $email_title='[#req-'.$request_id.'] '.$request_subject;

        //SMTP config
        $use_smtp=$this->uFunc->getConf('smtp_use_madwww_server','uSup',true,$site_id)=='0';
        if($use_smtp) {
            //port
            //server_name
            //use_ssl
            //user_name
            //password
            $smtp_settings['server_name']=$this->uFunc->getConf('smtp_server_name','uSup',true,$site_id);
            if($smtp_settings['server_name']=='') return 0;
            $smtp_settings['port']=$this->uFunc->getConf('smtp_port','uSup',true,$site_id);
            $smtp_settings['user_name']=$this->uFunc->getConf('smtp_user_name','uSup',true,$site_id);
            if($smtp_settings['user_name']=='') return 0;
            $smtp_settings['password']=$this->uFunc->getConf('smtp_password','uSup',true,$site_id);
            if($smtp_settings['password']=='') return 0;
            $smtp_settings['use_ssl']=$this->uFunc->getConf('smtp_use_ssl','uSup',true,$site_id)=='1';
        }
        else $smtp_settings[0]=0;

        //Message info
        if(!$msg_info=$this->request_id2last_msg_info($request_id,"msg_id,msg_text",$site_id)) return 0;
        $msg_id=$msg_info->msg_id;
        $msg_text=uString::sql2text($msg_info->msg_text,1);

        //Request's Message Files
        $request_files_html=$this->get_msg_files_html($msg_id,$site_id);

        //Company info
        if($company_info=$this->com_id2com_info($request_company_id,"com_title",$site_id)) {
            $com_title=uString::sql2text($company_info->com_title,1);
        }
        else $com_title="Без компании";


        //notify user
        $html='<p>Здравствуйте, '.$author_firstname.' '.$author_secondname.'</p>';

        $html.='<div class="msg_text">';

        if($author_is_member_of_several_companies&&$allow_change_company_id) {
            $html.='
            <p><strong>Компания:</strong> '.$com_title.'</p>
            <p>Чтобы запрос был прикреплен к другой Вашей компании, нажмите на одну из ссылок ниже:</p>';
            /** @noinspection PhpUndefinedMethodInspection */
            for($i=0;$com=$q_author_companies[$i];$i++) {
                if($request_company_id!==(int)$com->com_id) {
                    $com_info=$this->com_id2com_info($com->com_id,"com_title");
                    $com_title=uString::sql2text($com_info->com_title,1);
                    $hash=$this->write_change_com_hash($com->com_id,$request_id,$author_user_id);
                    $html.='<p>'.$com_title.':<br>
                    <a href="'.$u_sroot.'uSupport/request_change_com_id_bg/'.$author_user_id.'/'.$request_id.'/'.$com->com_id.'/'.$hash.'/now">Прикрепить к этой к компании</a> &nbsp; <a href="'.$u_sroot.'uSupport/request_change_com_id_bg/'.$author_user_id.'/'.$request_id.'/'.$com->com_id.'/'.$hash.'/default">Сделать эту компанию по умолчанию</a></p>';
                }
            }
            $html.='<p><small>Ссылки для смены компании запроса действительны в течение 2-х часов.</small></p>';
            $html.='<p>&nbsp;</p>';
        }
        $html.='
            <h4><a href="'.$u_sroot.'uSupport/request_show/'.$request_id.'">#'.$request_id.' '.$request_subject.'</h4>
            <div>'.nl2br($msg_text).
            '<p>&nbsp;</p>'.
            $request_files_html.
            '</div>
        </div>
        <p><small>При дальнейшем общении по этому запросу вставляйте в тему письма <i>[#req-'.$request_id.']</i>.</small></p>
        <p><small>Вы можете <a href="'.$u_sroot.'uSupport/request_show/'.$request_id.'">писать в запрос на сайте</a> или отвечать на email.</small></p>
        <p><small>На всякий случай: <a href="'.$u_sroot.'uSupport/requests">войти в техподдержку можно здесь</a>. Сбросить пароль можно в диалоге авторизации</small></p>';

        $this->uFunc->mail($html,$email_title,$author_user_email,$email_from_text,$email_from_email,$u_sroot,$site_id,'<p>'.$write_above_line.'</p>',$use_smtp,$smtp_settings);

        return 1;
    }

    public function msg_is_received_notification($request_id,$site_id) {
        //u_sroot
        $site_name=$this->uFunc->getConf('site_name','content',true,$site_id);
        $site_domain=$this->uFunc->getConf('site_domain','content',true,$site_id);
        $u_sroot='http://'.$site_domain.'/';

        //"Write above this line" text
        $write_above_line=$this->uFunc->getConf("email_write_above_line_content","uSup",true,$site_id);

        //Request info
        if(!$req_info=$this->req_id2info($request_id,"tic_subject,user_id",$site_id)) return 0;
        $request_title=uString::sql2text($req_info->tic_subject,1);
        $author_id=(int)$req_info->user_id;

        //Author info
        if(!$author_info=$this->uAuth->user_id2user_data($author_id,"firstname,secondname,email")) return 0;
        $author_firstname=uString::sql2text($author_info->firstname,1);
        $author_secondname=uString::sql2text($author_info->secondname,1);
        $author_email=$author_info->email;

        //Msg info
        if(!$msg_info=$this->request_id2last_msg_info($request_id,"msg_text,msg_id",$site_id)) return 0;
        $msg_text=uString::sql2text($msg_info->msg_text,1);
        $msg_id=(int)$msg_info->msg_id;

        $msg_files=$this->get_msg_files_html($msg_id,$site_id);

        //Email config
        $email_title='[#req-'.$request_id.'] '.$request_title.'. Новое сообщение.';

        $email_from_email=$this->uFunc->getConf("support_email","uSup",true,$site_id);
        $email_from_text=$this->uFunc->getConf("support_email_fromname","uSup",true,$site_id);

        //SMTP config
        $use_smtp=$this->uFunc->getConf('smtp_use_madwww_server','uSup',true,$site_id)=='0';
        if($use_smtp) {
            //port
            //server_name
            //use_ssl
            //user_name
            //password
            $smtp_settings['server_name']=$this->uFunc->getConf('smtp_server_name','uSup',true,$site_id);
            if($smtp_settings['server_name']=='') return 0;
            $smtp_settings['port']=$this->uFunc->getConf('smtp_port','uSup',true,$site_id);
            $smtp_settings['user_name']=$this->uFunc->getConf('smtp_user_name','uSup',true,$site_id);
            if($smtp_settings['user_name']=='') return 0;
            $smtp_settings['password']=$this->uFunc->getConf('smtp_password','uSup',true,$site_id);
            if($smtp_settings['password']=='') return 0;
            $smtp_settings['use_ssl']=$this->uFunc->getConf('smtp_use_ssl','uSup',true,$site_id)=='1';
        }
        else $smtp_settings[0]=0;


        $email_html='<p>Здравствуйте, '.$author_firstname.' '.$author_secondname.'</p>'.
            '<p>Мы приняли Ваше сообщение в техническую поддержку.</p>
        <div class="msg_text">
            <p><strong>Запрос:</strong> <a href="'.$u_sroot.'uSupport/request_show/'.$request_id.'">#'.$request_id.' '.$request_title.'</a>.</p>
            <div>'.nl2br($msg_text).
            '<p>&nbsp;</p>'.
            $msg_files.
            '</div>
        </div>
        <p><small>При дальнейшем общении по этому запросу оставляйте в теме письма <i>[#req-'.$request_id.']</i>.</small></p>
        <p><small>Вы можете писать в запрос на сайте <a href="'.$u_sroot.'uSupport/request_show/'.$request_id.'">'.$site_name.'</a> или отвечать на email.</small></p>
        <p><small>На всякий случай: <a href="'.$u_sroot.'uSupport/requests">войти в техподдержку можно здесь</a>. Сбросить пароль можно в диалоге авторизации</small></p>';

        $this->uFunc->mail($email_html,$email_title,$author_email,$email_from_text,$email_from_email,$u_sroot,$site_id,'<p>'.$write_above_line.'</p>',$use_smtp,$smtp_settings);

        return 1;
    }

    public function request_is_closed_notification($request_id,$site_id=site_id) {
        //u_sroot
        $site_domain=$this->uFunc->getConf('site_domain','content',true,$site_id);
        $u_sroot='http://'.$site_domain.'/';

        //"Write above this line" text
        $write_above_line=$this->uFunc->getConf("email_write_above_line_content","uSup",true,$site_id);

        //Request info
        if(!$req_info=$this->req_id2info($request_id,"tic_subject,company_id,user_id,tic_feedback_info,escalated,cons_id",$site_id)) return 0;
        $request_title=uString::sql2text($req_info->tic_subject,1);
        $author_id=(int)$req_info->user_id;
        $tic_feedback_info=$req_info->tic_feedback_info;
        $escalated=(int)$req_info->escalated;
        $cons_id=(int)$req_info->cons_id;
        $com_id=(int)$req_info->company_id;

        if($com_id) {
            $com_info=$this->com_id2com_info($com_id,"com_title");
            $com_title=uString::sql2text($com_info->com_title,1);
        }
        else $com_title="Без компании";

        //Author info
        if(!$author_info=$this->uAuth->user_id2user_data($author_id,"firstname,secondname,lastname,email")) return 0;
        $author_firstname=uString::sql2text($author_info->firstname,1);
        $author_secondname=uString::sql2text($author_info->secondname,1);
        $author_lastname=uString::sql2text($author_info->lastname,1);
        $author_email=$author_info->email;

        //Email config
        $email_from_email=$this->uFunc->getConf("support_email","uSup",true,$site_id);
        $email_from_text=$this->uFunc->getConf("support_email_fromname","uSup",true,$site_id);

        //SMTP config
        $use_smtp=$this->uFunc->getConf('smtp_use_madwww_server','uSup',true,$site_id)=='0';
        if($use_smtp) {
            //port
            //server_name
            //use_ssl
            //user_name
            //password
            $smtp_settings['server_name']=$this->uFunc->getConf('smtp_server_name','uSup',true,$site_id);
            if($smtp_settings['server_name']=='') return 0;
            $smtp_settings['port']=$this->uFunc->getConf('smtp_port','uSup',true,$site_id);
            $smtp_settings['user_name']=$this->uFunc->getConf('smtp_user_name','uSup',true,$site_id);
            if($smtp_settings['user_name']=='') return 0;
            $smtp_settings['password']=$this->uFunc->getConf('smtp_password','uSup',true,$site_id);
            if($smtp_settings['password']=='') return 0;
            $smtp_settings['use_ssl']=$this->uFunc->getConf('smtp_use_ssl','uSup',true,$site_id)=='1';
        }
        else $smtp_settings[0]=0;

        //define email's body
        $email_title='Запрос закрыт. #'.$request_id.' '.$request_title;
        $email2author_text='<p>Здравствуйте, '.uString::sql2text($author_firstname.' '.$author_secondname,1).'</p>
        <div class="msg_text">
            <p>Запрос "<a href="'.$u_sroot.'uSupport/request_show/'.$request_id.'">#'.$request_id.' '.$request_title.'</a> закрыт.</p>
            <p>Пожалуйста, <a href="'.$u_sroot.'uSupport/request_feedback/'.$tic_feedback_info.'">оставьте отзыв о качестве технической поддержки</a> по этому запросу.</p>
        </div>
        <p><small>На всякий случай: <a href="'.$u_sroot.'uSupport/requests">войти в техподдержку можно здесь</a>. Сбросить пароль можно в диалоге авторизации</small></p>';

        //Notify author
        $this->uFunc->mail($email2author_text,$email_title,$author_email,$email_from_text,$email_from_email,$u_sroot,$site_id,'<p>'.$write_above_line.'</p>',$use_smtp,$smtp_settings);


        $cons_email=array();
        if($cons_id) {//consultant is set
            //Consultant info
            if(!$cons_info=$this->uAuth->user_id2user_data($author_id,"firstname,email")) return 0;
            $cons_firstname[0]=uString::sql2text($cons_info->firstname,1);
            $cons_email[0]=$cons_info->email;
        }
        else {
            if($escalated) {//find in operators
                $q_operators=$this->get_operators("firstname,email",$site_id);

                for($i=0;$cons=$q_operators->fetch(PDO::FETCH_OBJ);$i++) {
                    $cons_email[$i]=$cons->email;
                    $cons_firstname[$i]=uString::sql2text($cons->firstname,1);
                }
            }
            else {//find in company admins
                $q_admins=$this->get_com_admins_to_notify_about_requests("user_id",$com_id,$site_id);

                $admins_ids=" (1=0 ";
                while($oper=$q_admins->fetch(PDO::FETCH_OBJ)) {
                    $admins_ids.=" OR u235_users.user_id=".(int)$oper->user_id." ";
                }
                $admins_ids.=" ) ";
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("uAuth")->prepare("SELECT DISTINCT 
                    firstname,
                    email
                    FROM 
                    u235_users 
                    JOIN 
                    u235_usersinfo
                    ON
                    u235_users.user_id=u235_usersinfo.user_id AND
                    u235_users.status=u235_usersinfo.status
                    WHERE 
                    site_id=:site_id AND
                    u235_users.status='active' AND
                    ".$admins_ids
                    );
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('uSup common 220'/*.$e->getMessage()*/);}

                for($i=0;$cons=$stm->fetch(PDO::FETCH_OBJ);$i++) {
                    $cons_email[$i]=$cons->email;
                    $cons_firstname[$i]=uString::sql2text($cons->firstname,1);
                }
            }
        }

        for($i=0;$i<count($cons_email);$i++) {
            $email2consultant_text = '<p>Здравствуйте, ' . $cons_firstname[$i] . '</p>
        <div class="msg_text">
            <p>Запрос "<a href="' . $u_sroot . 'uSupport/request_show/' . $request_id . '">#' . $request_id . ' ' . $req_info->tic_subject . '</a> закрыт.</p>
            <p>Клиент: ' . $author_firstname . ' ' . $author_lastname . ' </p>
            <p>Компания: ' . $com_title . '</p>
        </div>
        <p><small>На всякий случай: <a href="' . $u_sroot . 'uSupport/requests">войти в техподдержку можно здесь</a>. Сбросить пароль можно в диалоге авторизации</small></p>';

            //Notify operators
            $this->uFunc->mail($email2consultant_text,$email_title,$cons_email[$i],$email_from_text,$email_from_email,$u_sroot,$site_id,'<p>'.$write_above_line.'</p>',$use_smtp,$smtp_settings);
        }
    }

    public function msg_inaction_reminder($request_id,$recipient_firstname,$recipient_email,$site_id=site_id) {
        if($request_info=$this->req_id2info($request_id,"tic_subject,user_id",$site_id)) return 0;
        $request_title=uString::sql2text($request_info->tic_subject,1);
        $author_id=(int)$request_info->user_id;

        //author info
        if(!$author_info=$this->uAuth->user_id2user_data($author_id,"firstname,secondname,lastname")) return 0;
        $author_firstname=uString::sql2text($author_info->firstname,1);
        $author_secondname=uString::sql2text($author_info->secondname,1);
        $author_lastname=uString::sql2text($author_info->lastname,1);

        $site_name=$this->uFunc->getConf('site_name','content',true,$site_id);
        $site_domain=$this->uFunc->getConf('site_domain','content',true,$site_id);
        $u_sroot='http://'.$site_domain.'/';

        //email config
        $email_from_email=$this->uFunc->getConf("support_email","uSup",true,$site_id);
        $email_from_text=$this->uFunc->getConf("support_email_fromname","uSup",true,$site_id);

        //SMTP config
        $use_smtp=$this->uFunc->getConf('smtp_use_madwww_server','uSup',true,$site_id)=='0';
        if($use_smtp) {
            //port
            //server_name
            //use_ssl
            //user_name
            //password
            $smtp_settings['server_name']=$this->uFunc->getConf('smtp_server_name','uSup',true,$site_id);
            if($smtp_settings['server_name']=='') return 0;
            $smtp_settings['port']=$this->uFunc->getConf('smtp_port','uSup',true,$site_id);
            $smtp_settings['user_name']=$this->uFunc->getConf('smtp_user_name','uSup',true,$site_id);
            if($smtp_settings['user_name']=='') return 0;
            $smtp_settings['password']=$this->uFunc->getConf('smtp_password','uSup',true,$site_id);
            if($smtp_settings['password']=='') return 0;
            $smtp_settings['use_ssl']=$this->uFunc->getConf('smtp_use_ssl','uSup',true,$site_id)=='1';
        }
        else $smtp_settings[0]=0;

        //"Write above this line" text
        $write_above_line=$this->uFunc->getConf("email_write_above_line_content","uSup",true,$site_id);

        $email_title='[#req-'.$request_id.'] Бездействие в запросе. '.$request_title;

        $html='<p>Здравствуйте, '.$recipient_firstname.'</p>
        <div class="msg_text">
        <p><strong>Вы давно не отвечали на запрос</strong> в технической поддержке <a href="'.$u_sroot.'">'.$site_name.'</a>.</p>
        <h3><a href="'.$u_sroot.'uSupport/request_show/'.$request_id.'">#'.$request_id.' '.$request_title.'</a></h3>
        <p>Автор: <a href="'.$u_sroot.'uAuth/profile/'.$author_id.'">'.$author_firstname.' '.$author_secondname.' '.$author_lastname.'</a></p>
        </div>
        <p><small>При дальнейшем общении по этому запросу оставьте в теме письма <i>[#req-'.$request_id.']</i>.<small></p>
        <p><small>Вы можете <a href="'.$u_sroot.'uSupport/request_show/'.$request_id.'">писать в запрос на сайте</a> или отвечать на email.</small></p>
        <p><small>На всякий случай: <a href="'.$u_sroot.'uSupport/requests">войти в техподдержку можно здесь</a>. Сбросить пароль можно в <a href="'.$u_sroot.'uAuth/enter">диалоге авторизации</a></small></p>';

        $this->uFunc->mail($html,$email_title,$recipient_email,$email_from_text,$email_from_email,$u_sroot,$site_id,'<p>'.$write_above_line.'</p>',$use_smtp,$smtp_settings);

        return 1;
    }


    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uAuth=new \uAuth\common($this->uCore);
    }
}
