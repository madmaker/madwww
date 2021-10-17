<?php
require_once 'processors/classes/uFunc.php';

class uSubscr_subscribe_bg {
    private $uCore,$user_id,$user_name,$user_email,$user_exists,$hash;
    private function check_data() {
        if(!isset($_POST['user_name'],$_POST['user_email'])) $this->uFunc->error(10);

        if(isset($_POST['unsubscribe'])&&!isset($_POST['user_id'])) $this->uFunc->error(20);
        $this->user_name=uString::sanitize_text($_POST['user_name']);

        if(isset($_POST['user_id'])) {//existing user
            if(!isset($_POST['hash'])) $this->uFunc->error(30);
            $this->hash=$_POST['hash'];
            if(!uString::isHash($this->hash)) $this->uFunc->error(40);

            $this->user_id=$_POST['user_id'];
            if(!uString::isDigits($this->user_id)) $this->uFunc->error(50);

            //check hash
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSubscr")->prepare("SELECT
                user_id
                FROM
                u235_mailing_changes_appr
                WHERE
                user_id=:user_id AND
                hash=:hash AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':hash', $this->hash,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}

            /** @noinspection PhpUndefinedVariableInspection */
            /** @noinspection PhpUndefinedMethodInspection */
            if(!$stm->fetch(PDO::FETCH_OBJ)) {//check hash from mailing result
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm = $this->uFunc->pdo("uSubscr")->prepare("SELECT
                    user_id
                    FROM
                    u235_mailing_results
                    WHERE
                    user_id=:user_id AND
                    hash=:hash AND
                    site_id=:site_id
                    ");
                    $site_id = site_id;
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm->bindParam(':user_id', $this->user_id, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm->bindParam(':hash', $this->hash, PDO::PARAM_STR);
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm->execute();

                    /** @noinspection PhpUndefinedMethodInspection */
                    if (!$stm->fetch(PDO::FETCH_OBJ)) die("{'status' : 'error', 'msg' : 'page_expired'}");
                } catch (PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}
            }

            //get user's info
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSubscr")->prepare("SELECT
                user_email
                FROM
                u235_users
                WHERE
                user_id=:user_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                    $this->user_email=$qr->user_email;
                    $this->user_exists=true;
                    if(isset($_POST['unsubscribe'])) {
                        $this->unsubscribe_user();
                    }
                }
                else {
                    $this->update_user();
                }
            }
            catch(PDOException $e) {$this->uFunc->error('80'/*.$e->getMessage()*/);}
        }
        else {//new user
            $this->user_email=trim($_POST['user_email']);
            if(!uString::isEmail($this->user_email)) die("{'status' : 'error', 'msg' : 'user_name'}");

            $this->get_user_id();
        }


    }
    private function get_user_id() {
        $this->user_exists=false;
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSubscr")->prepare("SELECT
            user_id,
            user_name
            FROM
            u235_users
            WHERE
            user_email=:user_email AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_email', $this->user_email,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('90'/*.$e->getMessage()*/);}
        /** @noinspection PhpUndefinedMethodInspection */
        if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
            $this->user_id=$qr->user_id;
            $this->user_exists=true;
            $this->user_name=uString::sql2text($qr->user_name);
        }
        else {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSubscr")->prepare("SELECT
                user_id
                FROM
                u235_users
                WHERE
                site_id=:site_id
                ORDER BY
                user_id DESC
                LIMIT 1
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                    $this->user_id=$qr->user_id+1;
                }
                else $this->user_id=1;
            }
            catch(PDOException $e) {$this->uFunc->error('100'/*.$e->getMessage()*/);}

            $this->create_user();
        }
    }
    private function create_user() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSubscr")->prepare("INSERT INTO
            u235_users (
            user_id,
            user_name,
            user_email,
            timestamp,
            site_id
            ) VALUES (
            :user_id,
            :user_name,
            :user_email,
            :timestamp,
            :site_id
            )
            ");
            $user_name=uString::text2sql($this->user_name);
            $timestamp=time();
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_name', $user_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_email', $this->user_email,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':timestamp', $timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('110'/*.$e->getMessage()*/);}
    }
    private function update_user() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSubscr")->prepare("UPDATE
            u235_users
            SET
            user_name=:user_name,
            unsubscribed=0
            WHERE
            user_id=:user_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_name', $this->user_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('120'/*.$e->getMessage()*/);}
    }
    private function unsubscribe_user() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSubscr")->prepare("UPDATE
            u235_users
            SET
            unsubscribe=1
            WHERE
            user_id=:user_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('130'/*.$e->getMessage()*/);}
    }
    private function insert_into_users_groups($gr_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSubscr")->prepare("INSERT IGNORE INTO 
                        u235_users_groups
                        SET 
                        gr_id=:gr_id,
                        approved=:approved,
                        user_id=:user_id,
                        site_id=:site_id
                        ");
            if(!$this->user_exists) $approved=1;
            else {
                if (isset($_POST['user_id'])) $approved = 1;
                else $approved = 0;
            }
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':gr_id', $gr_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':approved', $approved,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('140'/*.$e->getMessage()*/);}
    }
    private function attach_groups() {
        //get groups from cols_els_id
        if(isset($_POST['cols_els_id'])) {
            if(uString::isDigits($_POST['cols_els_id'])) {
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
                    channels_used
                    FROM 
                    el_config_uSubscr_news_form 
                    WHERE 
                    cols_els_id=:cols_els_id AND 
                    site_id=:site_id
                    ");
                    $site_id=site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $_POST['cols_els_id'],PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                    /** @noinspection PhpUndefinedMethodInspection */
                    if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                        $channels_ar=explode(",",$qr->channels_used);
                        $channels_used_ar=array();
                        for($i=0;$i<count($channels_ar);$i++) {
                            if(uString::isDigits($channels_ar[$i]))
                                $channels_used_ar[$channels_ar[$i]]=1;
                        }
                    }
                }
                catch(PDOException $e) {$this->uFunc->error('150'/*.$e->getMessage()*/);}
            }
        }
        ///get all groups
        try {
             /** @noinspection PhpUndefinedMethodInspection */
             $stm=$this->uFunc->pdo("uSubscr")->prepare("SELECT
             gr_id
             FROM
             u235_groups
             WHERE
             site_id=:site_id
             ");
             $site_id=site_id;
             /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
             /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('160'/*.$e->getMessage()*/);}

        while($gr=$stm->fetch(PDO::FETCH_OBJ)) {
            if(isset($channels_used_ar)) {//Форма отправлена с формы подписки на uPage - там уже определены новостные каналы, на которые подписывается пользователь
                if(isset($channels_used_ar[$gr->gr_id])) {//На эту группу оформляем подписку
                    $this->insert_into_users_groups($gr->gr_id);
                }
            }
            else {//Форма отправлена со страницы, где пользователь должен был выбрать новости, на которые подписывается
                if (isset($_POST['gr_' . $gr->gr_id])) {
                    $this->insert_into_users_groups($gr->gr_id);

                }
            }
        }

        //record hash
        $this->hash=uFunc::genHash();
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSubscr")->prepare("REPLACE INTO
            u235_mailing_changes_appr (
            user_id,
            hash,
            timestamp,
            site_id
            ) VALUES (
            :user_id,
            :hash,
            :timestamp,
            :site_id
            )
            ");
            $timestamp=time();
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':hash', $this->hash,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':timestamp', $timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('170'/*.$e->getMessage()*/);}
    }
    private function notify_user() {
        if(!$this->user_exists) {
            $title='Вы подписаны на новости сайта '.site_name;
            $html='<p>Здравствуйте, '.$this->user_name.'</p>
            <p>Вы успешно подписаны на новости сайта <a href="'.u_sroot.'">'.site_name.'</a></p>
            <p><a href="'.u_sroot.$this->uCore->mod.'/subscription_change/'.$this->user_id.'/'.$this->hash.'">Нажмите сюда, чтобы отписаться от новостей или изменить свою подписку</a>.</p>
            ';
        }
        else {
            if(isset($_POST['unsubscribe'])) {
                $title='Отмена подписки на новости сайта '.site_name;
                $html='<p>Здравствуйте, '.$this->user_name.'</p>
                    <p>Ваша подписка на новости сайта <a href="'.u_sroot.'">'.site_name.'</a> отменена.</p>
                    <p><a href="'.u_sroot.$this->uCore->mod.'/subscription_change/'.$this->user_id.'/'.$this->hash.'">Нажмите сюда, чтобы изменить свою подписку</a>.</p>
                    ';
            }
            else {
                if(!isset($_POST['user_id'])) {
                    $title='Требуется подтверждение изменения подписки на новости сайта '.site_name;
                    $html='<p>Здравствуйте, '.$this->user_name.'</p>
                    <p>На сайте <a href="'.u_sroot.'">'.site_name.'</a> совершена попытка изменить вашу подписку на новости.</p>
                    <p><a href="'.u_sroot.$this->uCore->mod.'/subscription_change/'.$this->user_id.'/'.$this->hash.'">Нажмите сюда, чтобы подтвердить изменения, отписаться от новостей или изменить свою подписку</a>.</p>
                    <p>Если вы этого не делали, то просто удалите это письмо.</p>
                    ';
                }
                else {
                    $title='Обновление подписки на новости сайта '.site_name;
                    $html='<p>Здравствуйте, '.$this->user_name.'</p>
                    <p>Ваша подписка на новости сайта <a href="'.u_sroot.'">'.site_name.'</a> изменена.</p>
                    <p><a href="'.u_sroot.$this->uCore->mod.'/subscription_change/'.$this->user_id.'/'.$this->hash.'">Нажмите сюда, чтобы отписаться от новостей или изменить свою подписку</a>.</p>
                    ';
                }
            }
        }
        $this->uCore->uFunc->mail($html,$title,$this->user_email);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);

        $this->check_data();

        $this->attach_groups();

        $this->notify_user();

        echo "{'status' : 'done'}";
    }
}
$uSubscr=new uSubscr_subscribe_bg ($this);