<?php
namespace uAuth;
use PDO;
use PDOException;
use processors\uFunc;
use translator\translator;
use uSes;
use uString;

require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';
require_once 'translator/translator.php';
require_once 'uAuth/classes/common.php';

class login_bg {
    public $uFunc;
    public $uSes;
    public $uSup;
    /**
     * @var translator
     */
    private $translator;
    private $email;
    /**
     * @var string
     */
    private $login;
    /**
     * @var common
     */
    private $uAuth;
    /**
     * @var int
     */
    private $isPhone;
    /**
     * @var int
     */
    private $isEmail;
    private $user_passUnsafe,$user_id,$firstname;

    private function checkReCaptcha() {
        return 1;
//        if($this->uSes->get_val("captcha_needed")) {
//            if($_POST["recaptcha_response_field"]=="") return 0;
//
//            $response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . recaptcha_secret . "&response=" . $_POST['recaptcha_response_field'] . "&remoteip=" . $_SERVER["REMOTE_ADDR"]), true);
//            if($_SERVER['HTTP_HOST']!=$response['hostname']) return 0;
//
//            if($response['success']) return 1;
//            return 0;
//        }
//        else return 1;
    }
    private function checkData(){
        if(!isset($_POST['email'],$_POST['pass'])) {
            return false;
        }
        $this->login=trim($_POST['email']);
        $this->user_passUnsafe=$_POST['pass'];

        $use_MAD_SMS_to_send_SMS=(int)$this->uFunc->getConf('use MAD SMS to send SMS', 'content',0);

        if(uString::isEmail($this->login)) {
            $this->isEmail=1;
            $this->isPhone=0;
        }
        elseif($use_MAD_SMS_to_send_SMS&&uString::isPhone($this->login)) {
            $this->isPhone=1;
            $this->isEmail=0;
        }
        else {
            return false;
        }
        return true;
    }
    private function log_attempt() {
        try {
            $stm = $this->uFunc->pdo('uAuth')->prepare('INSERT INTO
            u235_users_login_attempts (
            user_id,
            timestamp,
            IP
            ) VALUES (
            :user_id,
            :timestamp,
            :IP
            )');
            $timestamp=time();
            $stm->bindParam(':IP', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
            $stm->bindParam(':timestamp', $timestamp, PDO::PARAM_INT);
            $stm->bindParam(':user_id', $this->user_id, PDO::PARAM_INT);
            $stm->execute();
        } catch (PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}
    }
    private function check_attempts_last_any_ip() {
        //Get number of attempts for last 30 minutes with any IP
        try {
            $stm=$this->uFunc->pdo('uAuth')->prepare('SELECT 
            COUNT(attempt_id) 
            FROM
            u235_users_login_attempts
            WHERE
            user_id=:user_id AND
            timestamp>:timestamp 
            ');
            $timestamp=time()-1800;//last 30 minutes
            $stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
            $stm->bindParam(':timestamp', $timestamp,PDO::PARAM_INT);
            $stm->execute();
            if($qr=$stm->fetch(PDO::FETCH_ASSOC)) {
                $count = (int)$qr['COUNT(attempt_id)'];
            }
            else {
                $count = 0;
            }
            if($count>1) {
                return 0;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
        return 1;
    }
    private function check_attempts_last_time() {
        //Get time of last attempt from any IP
        try {
            $stm=$this->uFunc->pdo('uAuth')->prepare('SELECT 
            timestamp
            FROM
            u235_users_login_attempts
            WHERE
            user_id=:user_id
            ORDER BY
            timestamp DESC 
            LIMIT 1;
            ');
            $stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
            $stm->execute();

            if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                $timestamp = (int)$qr->timestamp;
            }
            else {
                $timestamp = 0;
            }
            if($timestamp>time()-10/*last 10 seconds*/) {
                return 0;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}
        return 1;
    }
    private function check_attempts_count_for_IP() {
        //Get number of attempts for this IP for last hour
        try {
            $stm=$this->uFunc->pdo('uAuth')->prepare('SELECT 
            COUNT(attempt_id)
            FROM
            u235_users_login_attempts
            WHERE
            user_id=:user_id AND
            timestamp>:timestamp 
            ');
            $timestamp=time()-3600;//for last 1 hour
            $stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
            $stm->bindParam(':timestamp', $timestamp,PDO::PARAM_INT);
            $stm->execute();

            if($qr=$stm->fetch(PDO::FETCH_ASSOC)) {
                $count = (int)$qr['COUNT(attempt_id)'];
            }
            else {
                $count = 0;
            }
            if($count>20) {
                return 0;
            }//more than 20
        }
        catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}
        return 1;
    }
    private function check_attempts_count_for_user() {
        //Get number of attempts for this user
        try {
            $stm=$this->uFunc->pdo('uAuth')->prepare('SELECT 
            COUNT(attempt_id)
            FROM
            u235_users_login_attempts
            WHERE
            user_id=:user_id 
            ');
            $stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
            $stm->execute();

            if($qr=$stm->fetch(PDO::FETCH_ASSOC)) {
                return (int)$qr['COUNT(attempt_id)'];
            }
        }
        catch(PDOException $e) {$this->uFunc->error('90'/*.$e->getMessage()*/);}
        return 0;
    }
    private function check_attempts_IP() {
        //Get IP's of attempts
        try {
            $stm=$this->uFunc->pdo('uAuth')->prepare('SELECT DISTINCT
            IP
            FROM
            u235_users_login_attempts
            WHERE
            user_id=:user_id AND 
            timestamp>:timestamp
            LIMIT 2;
            ');
            $timestamp=time()-3600;//last 1 hour
            $stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
            $stm->bindParam(':timestamp', $timestamp,PDO::PARAM_INT);
            $stm->execute();

            for($i=0; $qr[$i]=$stm->fetch(PDO::FETCH_OBJ); $i++) {
                continue;
            }
            if(isset($qr[$i])) {
                unset($qr[$i]);
            }

            if($i>1) {
                return 0;
            }//more than 2 IP for this user for last hour
        }
        catch(PDOException $e) {$this->uFunc->error('100'/*.$e->getMessage()*/);}
        return 1;
    }
    private function check_attempts() {
        /**Проверять количество попыток за последние 30 минут без привязки к IP. Если больше 2 - Captcha
        Проверять время попыток. Если чаще 10 секунд - Captcha
        Проверять IP - если за последний час с разных IP - Captcha
        Проверять количество попыток с данного IP. Если больше 20 за последний час - валить too often.
        После успешной авторизации удалять все попытки.

        Если накопилось больше 50 попыток неудачных авторизаций, то писать письмо пользователю.*/

        //CHECK Attempts for captcha
        if(!$this->uSes->get_val('captcha_needed') && !$this->check_attempts_last_any_ip()) //Check attempts for last 30 minutes with any IP - if more 2 - captcha
        {
            $this->uSes->set_val('captcha_needed', 1);
        }
        if(!$this->uSes->get_val('captcha_needed') && !$this->check_attempts_last_time())//Get time of last attempt from any IP - if there were attempts for last 10 seconds - captcha
        {
            $this->uSes->set_val('captcha_needed', 1);
        }
        if(!$this->uSes->get_val('captcha_needed') && !$this->check_attempts_IP())//Get attempt IPs - if more than 2 - captcha
        {
            $this->uSes->set_val('captcha_needed', 1);
        }

        //Check for suspicious activity
        $attempts_count=$this->check_attempts_count_for_user();
        if($attempts_count>50) {
            $this->notify_about_suspicious_activity($attempts_count);
        }

        //Check attempts for too often
        if(!$this->check_attempts_count_for_IP()) {
            $this->log_attempt();
            $this->uSes->set_val('captcha_needed',1);
            print json_encode(array(
                'status' => 'error',
                'msg' => 'too often'
            ));
            exit;
        }

    }

    private function notify_about_suspicious_activity($attempts_count) {
        if($this->email==='') {
            return false;
        }//Only if user has email

        $attempts_count=(int)$attempts_count;
        if($attempts_count===10||$attempts_count===20||$attempts_count===30||$attempts_count===50||$attempts_count===100||$attempts_count===200||$attempts_count===300||$attempts_count===500||$attempts_count===1000) {
            $html='<p>'.$this->translator->txt('Hello - email text - part 1'/*Здравствуйте, */).$this->firstname.'</p>
            <p>'.$this->translator->txt('Account Break Alert - email text - part 1'/*Мы подозреваем, что на сайте */).site_name.$this->translator->txt('Account Break Alert - email text - part 2'/* кто-то пытается получить доступ к Вашей учетной записи.*/).'</p>
            <p>'.$this->translator->txt('Account Break Alert - email text - part 3'/*Было совершено много (*/).$attempts_count.$this->translator->txt('Account Break Alert - email text - part 4'/*) попыток входа с неверным паролем.*/).'</p>
            <p>'.$this->translator->txt('Account Break Alert - email text - part 5'/*Рекомендуем Вам сменить пароль к своей учетной записи на сложный (не менее 8 больших и маленьких букв, цифр и каких-либо символов. Это не должно быть какое-то слово или дата), чтобы предотвратить несанкционированный доступ.*/).'</p>
            <p>'.$this->translator->txt('Account Break Alert - email text - part 6'/*Для авторизации откройте страницу */).'<a href="'.u_sroot.'uAuth/enter">'.u_sroot.'uAuth/enter</a></p>
            <p>'.$this->translator->txt('Account Break Alert - email text - part 7'/*Если это Вы пытаетесь авторизоваться, но не помните пароль, сбросьте пароль: */);
            $title=$this->translator->txt('Account Break Alert - email subject'/*Попытка взлома вашей учетной записи на сайте */).site_name;

            $this->uFunc->sendMail($html,$title,$this->login);
        }
        return true;
    }
    private function getUserData() {
        //Check if user is registered in madplugin
        if(!$user=$this->uAuth->userLogin2info('type,status,user_id,firstname,email,regDate,password,cellphone',$this->login,($this->isEmail?'email':'cellphone'))) {
            return false;
        }

        $this->user_id=$user->user_id;
        $this->firstname=$user->firstname;
        $this->email=$user->email;


        //ATTEMPTS CONTROL
        $this->check_attempts();
        $this->log_attempt();

        if($user->status !== 'active') {
            if($user->status === 'banned') {
                print json_encode(array(
                    'status' => 'error',
                    'msg' => 'banned'
                ));
                exit;
            }


            print json_encode(array(
                'status' => 'error',
                'msg' => 'unknown user status '.$user->status
            ));
            exit;
        }

        /** @noinspection StaticInvocationViaThisInspection */
        $pass=$this->uFunc->passCrypt($this->user_passUnsafe,$user->regDate,$user->email,$user->user_id,$user->cellphone);

        //Try one-off password
        if($pass!==$user->password) {
            //may be there are one-off password?
            //delete one-off passwords older than 5 minutes

            try {
                $stm=$this->uFunc->pdo('uAuth')->prepare('SELECT
                pass
                FROM
                pass_one_off
                WHERE
                user_id=:user_id
                ');
                $stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
                $stm->execute();

                $one_off=$stm->fetch(PDO::FETCH_OBJ);
            }
            catch(PDOException $e) {$this->uFunc->error('1586319970'/*.$e->getMessage()*/,1);}

            /** @noinspection PhpUndefinedVariableInspection */
            if(!$one_off) {
                return false;
            }

            if($this->user_passUnsafe!==$one_off->pass) {
                return false;
            }
        }

        unset($user->password);
        $user->type=(int)$user->type;
        if($user->type) {
            $user->group = 13;
        }
        $_SESSION['USER']=$user;

        //Check if user is registered on current site
        if(!$usersinfo=$usersinfo=$this->uAuth->user_id2usersinfo($this->user_id,'status')) {//if there are no info about user on this site - then we just create new record for this user

            try {
                $stm=$this->uFunc->pdo('uAuth')->prepare('INSERT INTO 
                u235_usersinfo (
                user_id,
                site_id,
                status
                ) VALUES (
                :user_id,
                :site_id,
                :status
                )');
                $site_id=site_id;
                $stm->bindParam(':status', $user->status,PDO::PARAM_STR);
                $stm->bindParam(':user_id', $user->user_id,PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('160'/*.$e->getMessage()*/);}

            if($user->type) {
                try {
                    $stm = $this->uFunc->pdo('uAuth')->prepare('INSERT INTO 
                    u235_usersinfo_groups (
                    user_id,
                    group_id,
                    site_id
                    ) VALUES (
                    :user_id,
                    13,
                    :site_id
                    )');
                    $site_id = site_id;
                    $stm->bindParam(':user_id', $user->user_id, PDO::PARAM_INT);
                    $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                    $stm->execute();
                } catch (PDOException $e) {
                    $this->uFunc->error('1586322342'/*.$e->getMessage()*/,1);
                }
            }
        }
        else {
            $status=$usersinfo->status;

            if($status !== 'active'&&!$user->type) {
                if($status === 'banned') {
                    print json_encode(array(
                        'status' => 'error',
                        'msg' => 'banned'
                    ));
                    exit;
                }

                print json_encode(array(
                    'status' => 'error',
                    'msg' => 'unknown user status ' . $status
                ));
                exit;
            }
        }
        return true;
    }
    private function clean_attempts() {
        try {
            $stm=$this->uFunc->pdo('uAuth')->prepare('DELETE FROM
            u235_users_login_attempts
            WHERE
            user_id=:user_id
            ');
            $stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1586319666'/*.$e->getMessage()*/,1);}

        $this->uSes->set_val('captcha_needed',0);
    }
    public function __construct(&$uCore) {
        $this->uFunc=new uFunc($uCore);
        $this->uSes=new uSes($uCore);
        $this->translator=new translator(site_lang,'uAuth/login_bg.php');

        if($this->uSes->access(2)) {
            print json_encode(array(
                'status' => 'forbidden'
            ));
            exit;
        }

        if(!$this->checkReCaptcha()) {
            print json_encode(array(
                'status' => 'error',
                'msg' => 'wrong captcha'
            ));
            exit;
        }
        if(!$this->checkData()) {
            print json_encode(array(
                'status' => 'error',
                'msg' => 'no user'
            ));
            exit;
        }

        $this->uAuth=new common($uCore);

        if(!$this->getUserData()) {
            if($this->uSes->get_val('captcha_needed')) {
                print json_encode(array(
                    'status' => 'error',
                    'msg' => 'no user captcha'
                ));
                exit;
            }

            print json_encode(array(
                'status' => 'error',
                'msg' => 'no user'
            ));
            exit;
        }

        $this->clean_attempts();
        $this->uSes->set_val('user_id',$this->user_id);

        $this->uSes->userLogin();

        //uSupport
        if($this->uFunc->mod_installed('uSup')) {
            require_once 'uSupport/classes/common.php';
            //Attach user to company
            $uSup = new \uSupport\common($uCore);

            if ($comps = $uSup->is_email_belongs2company($this->login)) {
                for ($i = 0; $comps[$i]; $i++) {
                    $uSup->attach_user2company($this->user_id, $comps[$i]->com_id, 0);
                }
            }
        }

        //obooking
        if($this->uFunc->mod_installed('obooking')) {
            $userInfo=$this->uAuth->user_id2user_data($this->user_id,'email, cellphone');
            //attach user to manager, admin, client
            require_once 'obooking/classes/common.php';
            $obooking=new \obooking\common($uCore);
            $obooking->assignUserToOwnerAdminManagerClient($this->user_id,$userInfo->email,$userInfo->cellphone);
        }

        print json_encode(array(
            'status'=>'done'
        ));
        exit;
    }
}

new login_bg($this);
