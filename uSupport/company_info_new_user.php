<?php
namespace uSupport;
use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class company_info_new_user {
    public $uFunc;
    public $uSes;
    private $uCore,
        $user_id, $com_id,$com_title,$admin,$user,
        $support_email,$support_email_from,$use_smtp,$smtp_settings;

    private function check_data() {
        if(!isset($_POST['type'],$_POST['user_id'],$_POST['com_id'])) $this->uFunc->error(10);
        if($_POST['type']=='admin') $this->admin=1;
        else $this->admin=0;
        $this->user_id=&$_POST['user_id'];
        if($this->user_id=='undefined') die('{"status":"done"}');
        $this->com_id=&$_POST['com_id'];
        if(!uString::isDigits($this->user_id)) $this->uFunc->error(20);
        if(!uString::isDigits($this->com_id)) $this->uFunc->error(30);

        // check if company exists
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
            com_title
            FROM
            u235_comps
            WHERE
            site_id=:site_id AND
            com_id=:com_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $this->com_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(40);
            $this->com_title=uString::sql2text($qr->com_title,1);
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}

        //check if user exists
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uAuth")->prepare("SELECT DISTINCT
            firstname,
            secondname,
            email
            FROM
            u235_users
            JOIN 
            u235_usersinfo
            ON
            u235_usersinfo.user_id=u235_users.user_id
            WHERE
            u235_users.user_id=:user_id AND
            u235_usersinfo.site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$this->user=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(60);
        }
        catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}
    }
    private function check_access() {
        //operator
        if($this->uSes->access(201)) return true;

        //check if client or admin of this company

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
            user_id
            FROM
            u235_com_users
            WHERE
            user_id=:user_id AND
            com_id=:com_id AND
            admin=1 AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $user_id=$this->uSes->get_val("user_id");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $this->com_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($stm->fetch(PDO::FETCH_OBJ)) return true;
        }
        catch(PDOException $e) {$this->uFunc->error('80'/*.$e->getMessage()*/);}
        return false;
    }
    private function addUser() {
        //check if user already added to this company
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
            user_id
            FROM
            u235_com_users
            WHERE
            user_id=:user_id AND
            com_id=:com_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $this->com_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('90'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedVariableInspection PhpUndefinedMethodInspection */
        if($stm->fetch(PDO::FETCH_OBJ)) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSup")->prepare("UPDATE
                u235_com_users
                SET
                admin=:admin
                WHERE
                user_id=:user_id AND
                com_id=:com_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $this->com_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':admin', $this->admin,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('100'/*.$e->getMessage()*/);}
        }
        else {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSup")->prepare("INSERT INTO
                u235_com_users (
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
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $this->com_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('110'/*.$e->getMessage()*/);}
        }

        $this->notify_user();
    }
    private function notify_user() {
        $html='<p>Здравствуйте, '.uString::sql2text($this->user->firstname).' '.uString::sql2text($this->user->secondname).'</p>
        <div class="msg_text">';
        if($this->admin) $html.='<p>Вам назначена роль <b>администратора</b> в компании '.$this->com_title.' в технической поддержке сайта <a href="'.u_sroot.'">'.site_name.'</a></p>';
        else $html.='<p>Вы добавлены в компанию '.$this->com_title.' в технической поддержке сайта <a href="'.u_sroot.'">'.site_name.'</a></p>';
        $html.='</div>
        <p><small>На всякий случай: <a href="'.u_sroot.'uSupport/requests">войти в техподдержку можно здесь</a>. Сбросить пароль можно в диалоге авторизации</small></p>';
        $title='Вы добавлены в компанию в технической поддержке сайта '.site_name;
        $this->uCore->uFunc->mail($html,$title,$this->user->email,$this->support_email_from,$this->support_email,u_sroot,site_id,'',$this->use_smtp,$this->smtp_settings);
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);

        $this->check_data();
        if(!$this->check_access()) die('{"status":"forbidden"}');

        $this->support_email=$this->uCore->uFunc->getConf("support_email","uSup");
        $this->support_email_from=$this->uCore->uFunc->getConf("support_email_fromname","uSup");
        $this->use_smtp=$this->uCore->uFunc->getConf('smtp_use_madwww_server','uSup')=='0';
        if($this->use_smtp) {
            //port
            //server_name
            //use_ssl
            //user_name
            //password
            $this->smtp_settings['server_name']=$this->uCore->uFunc->getConf('smtp_server_name','uSup');
            $this->smtp_settings['port']=$this->uCore->uFunc->getConf('smtp_port','uSup');
            $this->smtp_settings['user_name']=$this->uCore->uFunc->getConf('smtp_user_name','uSup');
            $this->smtp_settings['password']=$this->uCore->uFunc->getConf('smtp_password','uSup');
            $this->smtp_settings['use_ssl']=$this->uCore->uFunc->getConf('smtp_use_ssl','uSup')=='1';
        }
        else $this->smtp_settings[0]=0;

        $this->addUser();
        die('{"status":"done"}');
    }
}
new company_info_new_user($this);