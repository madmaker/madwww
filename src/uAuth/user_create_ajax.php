<?php
namespace uAuth\admin;

use PDO;
use PDOException;
use processors\uFunc;
use uAuth_avatar;
use uSes;
use uString;

require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';
require_once 'uAuth/inc/avatar.php';

class user_create_ajax {
    /**
     * @var uFunc
     */
    private $uFunc;
    private $uCore,$registered,$email,$passUnsafe,$firstname,$secondname,$lastname,$user_id,$regTime;

    public function text($str) {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->uCore->text(array('uAuth','user_create_ajax'),$str);
    }

    private function checkData() {
        if(!isset($_POST['email'],$_POST['lastname'],$_POST['secondname'],$_POST['firstname'])) {
            $this->uFunc->error(10);
        }

        $this->email=$_POST['email'];
        if(!uString::isEmail($this->email)) {
            die("{'status' : 'email'}");
        }

        $this->firstname=$_POST['firstname'];
        if(strlen($this->firstname)<2) {
            die("{'status' : 'firstname'}");
        }
        $this->firstname=uString::text2sql($this->firstname);

        $this->lastname=$_POST['lastname'];
        if(strlen($this->firstname)<2) {
            die("{'status' : 'lastname'}");
        }
        $this->lastname=uString::text2sql($this->lastname);

        $this->secondname=$_POST['secondname'];
        $this->secondname=uString::text2sql($this->secondname);

        $this->passUnsafe=uFunc::genPass();

        //Check for email to not be in use
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo('uAuth')->prepare('SELECT
            user_id,
            firstname,
            secondname,
            lastname
            FROM
            u235_users
            WHERE
            email=:email
            ');
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':email', $this->email,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedVariableInspection PhpUndefinedMethodInspection */
        if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
            $this->registered=true;
            $this->user_id=$qr->user_id;
            $this->firstname=uString::sql2text($qr->firstname,1);
            $this->secondname=uString::sql2text($qr->secondname,1);
            $this->lastname=uString::sql2text($qr->lastname,1);

            //check if user is already created on this site
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo('uAuth')->prepare('SELECT
                user_id
                FROM
                u235_usersinfo
                WHERE
                user_id=:user_id AND
                site_id=:site_id
                ');
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if($stm->fetch(PDO::FETCH_OBJ)) {
                    die("{'status' : 'user exists'}");
                }
            }
            catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}
        }
        else {
            $this->registered=false;
            //Get last user id
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo('uAuth')->prepare('SELECT
                user_id
                FROM
                u235_users
                ORDER BY
                user_id
                DESC LIMIT 1
                ');
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                    $this->user_id = (int)$qr->user_id + 1;
                }
                else {
                    $this->user_id = 1;
                }
            }
            catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}
        }
    }
    private function addUser() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo('uAuth')->prepare('DELETE FROM
            u235_usersinfo_groups
            WHERE
            user_id=:user_id AND
            site_id=:site_id
            ');
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}

        $this->regTime=time();
        $pass=uFunc::passCrypt($this->passUnsafe,$this->regTime,$this->email,$this->user_id,'');

        if(!$this->registered) {
            //Create User
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo('uAuth')->prepare("INSERT INTO u235_users (
                user_id,
                type,
                firstname,
                secondname,
                lastname,
                password,
                email,
                cellphone,
                status,
                regDate
                ) VALUES (
                :user_id,
                0,
                :firstname,
                :secondname,
                :lastname,
                :password,
                :email,
                '',
                'active',
                :regDate
                )");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':firstname', $this->firstname,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':secondname', $this->secondname,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':lastname', $this->lastname,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':password', $pass,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':email', $this->email,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':regDate', $this->regTime,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('80'/*.$e->getMessage()*/);}
        }

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo('uAuth')->prepare("INSERT INTO u235_usersinfo (
            site_id,
            user_id,
            status
            ) VALUES (
            :site_id,
            :user_id,
            'active'
            )");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('90'/*.$e->getMessage()*/);}
    }
    private function sendEmail() {
        $html='<p>'.$this->text('Account creation msg Part1'/*Здравствуйте, */).$this->firstname.'</p>
        <p>'.$this->text('Account creation msg Part2'/*На сайте */).site_name.$this->text('Account creation msg Part3'/* для Вас создана учетная запись.*/).'</p>';
        if(!$this->registered) {
            $html .= '<p>' . $this->text('Account creation msg Part4'/*Ваш пароль: */) . $this->passUnsafe . '</p>';
        }
        $html.='<p>'.$this->text('Account creation msg Part5'/*Для входа на сайт используйте эту ссылку: */).'<a href="'.u_sroot.'uAuth/enter">'.u_sroot.'uAuth/enter</a></p>';
        $title=$this->text('Account creation msg Subject'/*Добро пожаловать на сайт */).site_name;

        $this->uFunc->sendMail($html,$title,$this->email);
    }
    public function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $uSes=new uSes($this->uCore);
        $uAuth_avatar=new uAuth_avatar($this->uCore);

        if(!$uSes->access(30)) {
            die("{'status' : 'forbidden'}");
        }

        $this->checkData();
        $this->addUser();
        $this->sendEmail();

        echo "{
        'status' : 'success', 
        'user_id' : '".$this->user_id."', 
        'firstname':'".$this->firstname."', 
        'secondname':'".$this->secondname."', 
        'lastname':'".$this->lastname."', 
        'regDate':'".$this->regTime."',
        'avatar':'".$uAuth_avatar->get_avatar('admin_list',$this->user_id,0)."',
        'email':'".$this->email."'
        }";
    }
}
new user_create_ajax($this);
