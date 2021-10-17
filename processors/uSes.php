<?php
require_once 'processors/classes/uFunc.php';

class uSes {
    private $uFunc;
    private $uCore;
    public $var;

    /**public переменные напрямую у uSes не использовать. Вместо них юзать uCore->uSes->var или просто uCore->var
     * @param $uCore
     * @param bool $uFunc
     */
    public function __construct(&$uCore, &$uFunc=false) {
        $this->uCore=&$uCore;
        if($uFunc) {
            $this->uFunc =& $uFunc;
        }
        else {
            $this->uFunc = new \processors\uFunc($this->uCore);
        }
    }
    public function get_val($val) {
        //sesId - hash
        //ses_id - int
        //user_id
        //firstname
        //type
        //cookies_disclaimer_closed
        //uSup_user_settings
        //captcha_needed
        //u235_panel_visible

        if(!isset($this->uCore->uSes->var)) {
            $this->uCore->uSes->var = new stdClass();
        }

        if ($val === 'sesId') {
            return $_SESSION['SESSION']['sesId'];
        }

        if ($val === 'user_id') {
            if(!isset($_SESSION["USER"]->user_id)) {
                try {
                    $stm=$this->uFunc->pdo("uSes")->prepare("SELECT 
                    user_id 
                    FROM 
                    u235_list 
                    WHERE 
                    id=:ses_id AND 
                    site_id=:site_id
                    ");
                    $site_id=site_id;
                    $ses_id=$this->get_val('ses_id');
                    $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    $stm->bindParam(':ses_id', $ses_id,PDO::PARAM_STR);
                    $stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error("uSes 10");}
                /** @noinspection PhpUndefinedVariableInspection */
                if($res=$stm->fetch(PDO::FETCH_OBJ)) {
                    if(!isset($_SESSION["USER"])) {
                        $_SESSION["USER"] = new stdClass();
                    }
                    $_SESSION["USER"]->user_id=(int)$res->user_id;
                }
                else {
                    $_SESSION["USER"]->user_id = 0;
                }
            }
            return $_SESSION["USER"]->user_id;
        }

        if ($val === 'firstname') {
            if(!isset($_SESSION["USER"]->firstname)) {
                try {
                    $stm=$this->uFunc->pdo("uAuth")->prepare("SELECT 
                    firstname 
                    FROM 
                    u235_users
                    WHERE 
                    user_id=:user_id 
                    ");
                    $user_id=$this->get_val('user_id');
                    $stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
                    $stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error("uSes 10");}
                /** @noinspection PhpUndefinedVariableInspection */
                if($res=$stm->fetch(PDO::FETCH_OBJ)) {
                    if(!isset($_SESSION["USER"])) {
                        $_SESSION["USER"] = new stdClass();
                    }
                    $_SESSION["USER"]->firstname=(int)$res->firstname;
                }
                else {
                    $_SESSION["USER"]->firstname = "";
                }
            }
            return $_SESSION["USER"]->firstname;
        }

        if ($val === 'lastname') {
            if(!isset($_SESSION["USER"]->lastname)) {
                try {
                    $stm=$this->uFunc->pdo("uAuth")->prepare("SELECT 
                    lastname 
                    FROM 
                    u235_users
                    WHERE 
                    user_id=:user_id 
                    ");
                    $user_id=$this->get_val('user_id');
                    $stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
                    $stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error("uSes 10");}
                /** @noinspection PhpUndefinedVariableInspection */
                if($res=$stm->fetch(PDO::FETCH_OBJ)) {
                    if(!isset($_SESSION["USER"])) {
                        $_SESSION["USER"] = new stdClass();
                    }
                    $_SESSION["USER"]->lastname=$res->lastname;
                }
                else {
                    $_SESSION["USER"]->lastname = "";
                }
            }
            return $_SESSION["USER"]->lastname;
        }

        if ($val === 'uSup_user_settings') {
            if(!isset($_SESSION['uSupport']['users_settings'])) {
                $_SESSION["uSupport"]['users_settings'] = false;
            }

            if(!$_SESSION["uSupport"]['users_settings']) {
                try {

                    $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
                    show_opened,
                    show_answered,
                    show_done,
                    show_closed,
                    show_requests,
                    show_cases,
                    show_mine,
                    show_others,
                    show_assigned2me,
                    show_assigned2others,
                    show_unassigned,
                    show_internal,
                    show_escalated
                    FROM
                    u235_users_settings
                    WHERE
                    user_id=:user_id
                    ");
                    $user_id=$this->get_val('user_id');
                    $stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
                    $stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error("uSes 20");}
                /** @noinspection PhpUndefinedVariableInspection*/
                if($res=$stm->fetch(PDO::FETCH_ASSOC)) {
                    if(!isset($_SESSION["uSupport"])) {
                        $_SESSION["uSupport"] = [];
                    }
                    $_SESSION["uSupport"]['users_settings']=$res;
                }
                else {
                    $_SESSION["uSupport"]['users_settings'] = false;
                }
            }
            return $_SESSION["uSupport"]['users_settings'];
        }

        if ($val === 'ses_id') {
            if(!isset($_SESSION['SESSION']['ses_id'])) {
                try {

                    $stm=$this->uFunc->pdo("uSes")->prepare("SELECT 
                    id 
                    FROM 
                    u235_list 
                    WHERE 
                    sesId=:sesId AND
                    site_id=:site_id
                    ");
                    $site_id=site_id;
                    $sesId=$this->get_val('sesId');
                    $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    $stm->bindParam(':sesId', $sesId,PDO::PARAM_STR);
                    $stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error("uSes 30");}
                /** @noinspection PhpUndefinedVariableInspection*/
                if($res=$stm->fetch(PDO::FETCH_OBJ)) {
                    $_SESSION['SESSION']['ses_id'] = (int)$res->id;
                }
                else {
                    $this->makeDefault();
                    return (int)$_SESSION['SESSION']['sesId'];
                }

            }
            return (int)$_SESSION['SESSION']['ses_id'];
        }

        if ($val === 'type') {
            if(!isset($_SESSION['USER']->type)) {
                try {

                    $stm=$this->uFunc->pdo("uAuth")->prepare("SELECT type FROM u235_users WHERE user_id=:user_id");
                    $user_id=$this->get_val('user_id');
                    $stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
                    $stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error("uSes 40");}
                /** @noinspection PhpUndefinedVariableInspection*/
                if($res=$stm->fetch(PDO::FETCH_OBJ)) {
                    $_SESSION['USER']->type = (int)$res->type;
                }
                else {
                    $_SESSION['USER']->type = 0;
                }
            }
            return $_SESSION['USER']->type;
        }

        if ($val === 'cookies_disclaimer_closed') {
            if(!isset($_SESSION['SESSION']['cookies_disclaimer_closed'])) {
                try {

                    $stm=$this->uFunc->pdo("uSes")->prepare("SELECT 
                    cookies_disclaimer_closed 
                    FROM 
                    u235_list 
                    WHERE 
                    id=:ses_id AND
                    site_id=:site_id
                    ");
                    $ses_id=$this->get_val('ses_id');
                    $site_id=site_id;
                    $stm->bindParam(':ses_id', $ses_id,PDO::PARAM_INT);
                    $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    $stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error("uSes 50");}
                /** @noinspection PhpUndefinedVariableInspection*/
                if($res=$stm->fetch(PDO::FETCH_OBJ)) {
                    $_SESSION['SESSION']['cookies_disclaimer_closed'] = (int)$res->cookies_disclaimer_closed;
                }
                else {
                    $_SESSION['SESSION']['cookies_disclaimer_closed'] = 0;
                }
            }
            return $_SESSION['SESSION']['cookies_disclaimer_closed'];
        }

        if ($val === 'captcha_needed') {
            if(!isset($_SESSION["SESSION"]["captcha_needed"])) {
                $this->set_val("captcha_needed", 0);
            }
            return (int)$_SESSION["SESSION"]["captcha_needed"];
        }

        if($val === 'u235_panel_visible') {
            if(!isset($_SESSION["u235_panel_visible"])) {
                $this->set_val("u235_panel_visible", 1);
            }
            return (int)$_SESSION["u235_panel_visible"];
        }
        return $this->uFunc->error("uSes 60");
    }
    public function set_val($field,$val) {
        //user_id
        //sesId
        //cookies_disclaimer_closed
        //captcha_needed
        //u235_panel_visible

        if(!isset($this->uCore->uSes->var)) {
            $this->uCore->uSes->var = new stdClass();
        }

        //cookies_disclaimer_closed
        if($field === "cookies_disclaimer_closed") {
            $_SESSION['SESSION']['cookies_disclaimer_closed']=(int)$val;
            try {
                $stm = $this->uFunc->pdo("uSes")->prepare("UPDATE 
                u235_list 
                SET
                 cookies_disclaimer_closed=:cookies_disclaimer_closed
                 WHERE 
                 id=:ses_id AND
                 site_id=:site_id
                ");
                $site_id = site_id;
                $ses_id = $this->get_val("ses_id");
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                $stm->bindParam(':ses_id', $ses_id, PDO::PARAM_INT);
                $stm->bindParam(':cookies_disclaimer_closed', $val, PDO::PARAM_INT);
                $stm->execute();
            } catch (PDOException $e) {
                $this->uFunc->error('uSes 70'/*.$e->getMessage()*/);
            }
        }
        elseif($field === "captcha_needed") {
            $_SESSION["SESSION"]["captcha_needed"]=(int)$val;
        }
        elseif($field === "u235_panel_visible") {
            $_SESSION["u235_panel_visible"]=(int)$val;
        }
        elseif($field === 'user_id') {
            $_SESSION['SESSION']['user_id']=$_SESSION["USER"]->user_id=(int)$val;
            try {
                $stm=$this->uFunc->pdo("uSes")->prepare("UPDATE 
                u235_list 
                SET
                user_id=:user_id
                WHERE
                id=:ses_id AND 
                site_id=:site_id
                ");
                $site_id=site_id;
                $ses_id=$this->get_val('ses_id');
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->bindParam(':ses_id', $ses_id,PDO::PARAM_INT);
                $stm->bindParam(':user_id', $val,PDO::PARAM_INT);
                $stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error("uSes 80"/*.$e->getMessage()*/);}
        }
        elseif($field === 'sesId') {$_SESSION['SESSION']['sesId']=$val;}
        else {
            $this->uFunc->error("uSes 90");
        }
    }
    public function access ($access/*код доступа*/) {
        if (!(int)$access) {
            return true;
        }

        if(isset($_SESSION['acl'][$access])) {
            return true;
        }//0-доступно всем
        return false;
    }

     private function resetPhpSesbyDb() {
        session_regenerate_id(true);

        $_SESSION['SESSION']['lastupdate']=$_SESSION['SESSION']['time']=time();//Записываем время, когда последний раз обновляли сессию в БД
        $_SESSION['SESSION']['HTTP_USER_AGENT']=isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'';
        $_SESSION['SESSION']['HTTP_ACCEPT_CHARSET']=isset($_SERVER['HTTP_ACCEPT_CHARSET'])?$_SERVER['HTTP_ACCEPT_CHARSET']:'';
        $_SESSION['SESSION']['HTTP_ACCEPT_LANGUAGE']=isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])?$_SERVER['HTTP_ACCEPT_LANGUAGE']:'';
        $_SESSION['SESSION']['REMOTE_ADDR']=$_SERVER['REMOTE_ADDR'];
        setcookie("ses_id",$_SESSION['SESSION']['sesId'],(time() + 315360000),'/');//Создаем куки с sesId и userId
        setcookie("user_id",$_SESSION['SESSION']['user_id'],(time() + 315360000),'/');
        $_SESSION['SESSION']['captcha_needed']=false;
    }
    private function insert_ses2db($id) {
        try {
            $stm = $this->uFunc->pdo("uSes")->prepare("INSERT INTO
              u235_list (
              id,
              user_id,
              sesId,
              `time`,
              HTTP_USER_AGENT,
              HTTP_ACCEPT_CHARSET,
              HTTP_ACCEPT_LANGUAGE,
              REMOTE_ADDR,
              site_id
              ) VALUES (
              :id,
              :user_id,
              :sesId,
              :ses_time,
              :HTTP_USER_AGENT,
              :HTTP_ACCEPT_CHARSET,
              :HTTP_ACCEPT_LANGUAGE,
              :REMOTE_ADDR,
              :site_id
              )");
            $site_id = site_id;
            $user_id = $_SESSION['SESSION']['user_id'];
            $sesId = $_SESSION['SESSION']['sesId'];
            $time = time();
            $HTTP_USER_AGENT = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "";
            $HTTP_ACCEPT_CHARSET = isset($_SERVER['HTTP_ACCEPT_CHARSET']) ? $_SERVER['HTTP_ACCEPT_CHARSET'] : "";
            $HTTP_ACCEPT_LANGUAGE = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : "";
            $REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
            $stm->bindParam(':id', $id, PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            $stm->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stm->bindParam(':sesId', $sesId, PDO::PARAM_STR);
            $stm->bindParam(':ses_time', $time, PDO::PARAM_INT);
            $stm->bindParam(':HTTP_USER_AGENT', $HTTP_USER_AGENT, PDO::PARAM_STR);
            $stm->bindParam(':HTTP_ACCEPT_CHARSET', $HTTP_ACCEPT_CHARSET, PDO::PARAM_STR);
            $stm->bindParam(':HTTP_ACCEPT_LANGUAGE', $HTTP_ACCEPT_LANGUAGE, PDO::PARAM_STR);
            $stm->bindParam(':REMOTE_ADDR', $REMOTE_ADDR, PDO::PARAM_STR);
            $stm->execute();
        } catch (PDOException $e) {
            uFunc::journal($e->getMessage(), "uSes120");
            return 0;
        }
        return $id;
    }
	 private function makeDefault() {
         if(isset($_SESSION['SESSION']['sesId'])) {
             //Удаляем старую сессию из БД
             try {

                 $stm=$this->uFunc->pdo("uSes")->prepare("DELETE FROM
                 u235_list
                 WHERE
                 sesId=:sesId AND
                 site_id=:site_id
                 ");
                 $site_id=site_id;
                 $sesId=$_SESSION['SESSION']['sesId'];
                 $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                 $stm->bindParam(':sesId', $sesId,PDO::PARAM_STR);
                 $stm->execute();
             }
             catch(PDOException $e) {$this->uFunc->error('uSes 100'/*.$e->getMessage()*/);}
         }

         @session_regenerate_id(true);

         $_SESSION['SESSION']['captcha_needed']=false;
		 $_SESSION['SESSION']['user_id']=0;//По умолчанию id пользователя - 0.
		 $_SESSION['SESSION']['sesId']=md5(mt_rand(0,1000000)*time());//Генерим код сессии.
		 $_SESSION['SESSION']['lastupdate']=$_SESSION['SESSION']['time']=time();//Записываем время, когда последний раз обновляли сессию в БД
		 $_SESSION['SESSION']['HTTP_USER_AGENT']=isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'';
		 $_SESSION['SESSION']['HTTP_ACCEPT_CHARSET']=isset($_SERVER['HTTP_ACCEPT_CHARSET'])?$_SERVER['HTTP_ACCEPT_CHARSET']:'';
		 $_SESSION['SESSION']['HTTP_ACCEPT_LANGUAGE']=isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])?$_SERVER['HTTP_ACCEPT_LANGUAGE']:'';
		 $_SESSION['SESSION']['REMOTE_ADDR']=$_SERVER['REMOTE_ADDR'];
		 setcookie("ses_id",$_SESSION['SESSION']['sesId'],(time() + 315360000),'/');//Создаем куки с sesId и user_id
		 setcookie("user_id",$_SESSION['SESSION']['user_id'],(time() + 315360000),'/');
		 //Записываем новую сессию в БД
         try {

             $stm=$this->uFunc->pdo("uSes")->prepare("SELECT 
            id 
            FROM 
            u235_list 
            WHERE 
            site_id=:site_id
            ORDER BY
            id DESC 
            LIMIT 1
            ");
            $site_id=site_id;
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                $id = $qr->id + 1;
            }
            else {
                $id = 1;
            }
         }
         catch(PDOException $e) {$this->uFunc->error('uSes 110'/*.$e->getMessage()*/);}
         for($i=0;$i<10;$i++) {
             if($new_id=$this->insert_ses2db($id+$i)) {
                 break;
             }
         }
         if(!$new_id) {
             $this->uFunc->error("uSes 115");
         }

         $this->getUserACL();
	 }
	 public function userLogin() {
         unset($_SESSION['acl']);
         $user_id=$this->get_val('user_id');
         $sesId=$this->get_val('sesId');

		  $_SESSION['SESSION']['lastupdate']=$_SESSION['SESSION']['time']=time();//Записываем время, когда последний раз обновляли сессию в БД
		  $_SESSION['SESSION']['HTTP_USER_AGENT']=isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'';
		  $_SESSION['SESSION']['HTTP_ACCEPT_CHARSET']=isset($_SERVER['HTTP_ACCEPT_CHARSET'])?$_SERVER['HTTP_ACCEPT_CHARSET']:'';
		  $_SESSION['SESSION']['HTTP_ACCEPT_LANGUAGE']=isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])?$_SERVER['HTTP_ACCEPT_LANGUAGE']:'';
		  $_SESSION['SESSION']['REMOTE_ADDR']=$_SERVER['REMOTE_ADDR'];
		  setcookie('ses_id',$sesId,(time() + 315360000)/*(10 * 365 * 24 * 60 * 60)*/,'/');//Создаем куки с sesId и user_id
		  setcookie('user_id',$user_id,(time() + 315360000),'/');


         try {

             $stm=$this->uFunc->pdo('uSes')->prepare('UPDATE
              u235_list
              SET
              user_id=:user_id,
              time=:time
              WHERE
              sesId=:sesId AND
              REMOTE_ADDR=:REMOTE_ADDR AND
              site_id=:site_id
              ');
             $site_id=site_id;
             $time=time();
             $REMOTE_ADDR=$_SERVER['REMOTE_ADDR'];
             $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
             $stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
             $stm->bindParam(':time', $time,PDO::PARAM_INT);
             $stm->bindParam(':sesId', $sesId,PDO::PARAM_STR);
             $stm->bindParam(':REMOTE_ADDR', $REMOTE_ADDR,PDO::PARAM_STR);
             $stm->execute();
         }
         catch(PDOException $e) {$this->uFunc->error('uSes 130'/*.$e->getMessage()*/);}

         $this->getUserACL();
	 }
	 public function userLogout() {
         try {

             $stm=$this->uFunc->pdo("uSes")->prepare("UPDATE
             u235_list
             SET
             user_id=0
             WHERE
             sesId=:sesId AND 
             site_id=:site_id
             ");
             $site_id=site_id;
             $sesId=$_SESSION['SESSION']['sesId'];
             $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
             $stm->bindParam(':sesId', $sesId,PDO::PARAM_STR);
             $stm->execute();
         }
         catch(PDOException $e) {$this->uFunc->error('uSes 140'/*.$e->getMessage()*/);}

		  session_unset();//Удаляем все ранее записанные переменные сессии;
		  $this->makeDefault();
	 }
	 private function update() {
		 //Обновляем таймстамп кукисов
		 setcookie("ses_id",$_SESSION['SESSION']['sesId'],(time() + 315360000),'/');
		 setcookie("user_id",$_SESSION['SESSION']['user_id'],(time() + 315360000),'/');
		 //Обновляем таймстамп сессии в БД
         try {

             $stm=$this->uFunc->pdo("uSes")->prepare("UPDATE 
             u235_list 
             SET 
             time=".time()."
             WHERE 
             sesId=:sesId AND
             site_id=:site_id
             ");
             $site_id=site_id;
             $sesId=$_SESSION['SESSION']['sesId'];
             $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
             $stm->bindParam(':sesId', $sesId,PDO::PARAM_STR);
             $stm->execute();
         }
         catch(PDOException $e) {$this->uFunc->error('uSes 150'/*.$e->getMessage()*/);}

         //Обновляем таймстамп php-сессии
		 $_SESSION['SESSION']['lastupdate']=$_SESSION['SESSION']['time']=time();//Записываем время, когда последний раз обновляли сессию в БД
         $this->getUserACL();
	}
     public function getUserACL(){
        //Достаем массив прав доступа пользователя
        unset($_SESSION['acl']);
        if($this->get_val("user_id")) {//if we know user_id
            try {

                $stm=$this->uFunc->pdo("uAuth")->prepare("SELECT DISTINCT
                acl_id
                FROM
                u235_groups_acl
                JOIN
                u235_usersinfo_groups
                ON
                u235_usersinfo_groups.group_id=u235_groups_acl.user_group_id
                WHERE
                (
                u235_usersinfo_groups.user_id=:user_id AND
                u235_usersinfo_groups.site_id=:site_id
                )
                OR 
                u235_groups_acl.user_group_id=2
                ");
                $user_id=$this->get_val("user_id");
                $site_id=site_id;

                $stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();


                while($tmp=$stm->fetch(PDO::FETCH_ASSOC)) {
                    $_SESSION['acl'][$tmp['acl_id']] = 1;
                }
            }
            catch(PDOException $e) {$this->uFunc->error('uSes 160'/*.$e->getMessage()*/);}
        }

         if($this->get_val("user_id")) {
             $_SESSION['acl'][1]=1;
             $_SESSION['acl'][2]=1;

             if($this->get_val("type")) {
                $_SESSION['acl'][12]=1;
                $_SESSION['acl'][13]=1;
                $_SESSION['acl'][14]=1;
                $_SESSION['acl'][15]=1;
                $_SESSION['acl'][16]=1;
                $_SESSION['acl'][17]=1;
                $_SESSION['acl'][28]=1;
                $_SESSION['acl'][29]=1;
                $_SESSION['acl'][30]=1;
                $_SESSION['acl'][31]=1;
                $_SESSION['acl'][1901]=1;
             }
         }
         else {//if don't know user_id - get acl for unauthorised users
             $_SESSION['acl'][1]=1;
             $_SESSION['acl'][11]=1;
         }
    }
	 public function logInCheck () {
         $_SESSION['SESSION']['timezone_difference_isset_always']=(isset($_SESSION['SESSION']['timezone_difference'])?$_SESSION['SESSION']['timezone_difference_isset_always']=$_SESSION['SESSION']['timezone_difference']:0);

		 if(isset($_SESSION['SESSION']['sesId'],$_COOKIE["ses_id"],$_COOKIE['user_id'])) {//php-сессия содержит sesId, в кукис есть sesId и user_id - проверяем соответствие кукис данным php-сессии

			 if(//Проверяем данные кукис и браузера с php-сессиией
				 $_SESSION['SESSION']['sesId']					===$_COOKIE["ses_id"]&&
				 $_SESSION['SESSION']['user_id']					==$_COOKIE['user_id']&&
				 $_SESSION['SESSION']['time']						>(time()-ses_lifetime)&&
				 $_SESSION['SESSION']['HTTP_ACCEPT_CHARSET']	===(isset($_SERVER['HTTP_ACCEPT_CHARSET'])?$_SERVER['HTTP_ACCEPT_CHARSET']:'')&&
				 $_SESSION['SESSION']['REMOTE_ADDR']			===$_SERVER['REMOTE_ADDR']
			 ) {//Если данные php-сессии совпадают с sesId и user_id из кукис и браузером - всё ОК. Пользователь авторизован

				 $_SESSION['SESSION']['time']=time();
				 if(($_SESSION['SESSION']['lastupdate']+ses_updatetime)<time()) {//Если подошло время обновления сессии в БД, то обновляем ее
                         $this->update();
				 }
			 }
			 else {//sesIs и user_id или данные браузера (или сессия устарела) от пользователя не соответствуют php-сессии - убиваем эту сессию и назначаем новую
                     if (uString::isHash($_COOKIE["ses_id"])) {//Если в sesId некорректные данные
                         $sesId =& $_COOKIE["ses_id"];
                         try {

                             $stm=$this->uFunc->pdo("uSes")->prepare("DELETE FROM 
                              u235_list 
                              WHERE 
                              sesId=:sesId AND 
                              site_id=:site_id
                              ");
                             $site_id=site_id;
                             $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                             $stm->bindParam(':sesId', $sesId,PDO::PARAM_STR);
                             $stm->execute();
                         }
                         catch(PDOException $e) {$this->uFunc->error('uSes 170'/*.$e->getMessage()*/);}
                     }
                     $this->makeDefault();
                     return false;
			 }
		 }
		 elseif(!isset($_SESSION['sesId'])&&isset($_COOKIE['user_id'])) {//php-сессия НЕ содержит sesId, но в кукис есть sesId и user_id - проверяем сессию по базе. Возможно php-сессия просто уже умерла

			 if(!uString::isDigits($_COOKIE['user_id'])||!uString::isHash($_COOKIE['ses_id'])) {//Если в ses_id или user_id херня - назначаем данные сесии по умолчанию
				 $this->makeDefault();	return false;
			 }

             try {

                 //HTTP_USER_AGENT=:HTTP_USER_AGENT AND - выкинул из запроса
                 $stm=$this->uFunc->pdo("uSes")->prepare("SELECT
                 *
                 FROM 
                 u235_list
                 WHERE
                 user_id=:user_id AND
                 time>".(time()-ses_lifetime)." AND 
                 HTTP_ACCEPT_CHARSET=:HTTP_ACCEPT_CHARSET AND 
                 HTTP_ACCEPT_LANGUAGE=:HTTP_ACCEPT_LANGUAGE AND 
                 REMOTE_ADDR=:REMOTE_ADDR AND
                 site_id=:site_id
                 ");

                 $site_id=site_id;
                 $user_id=$_COOKIE['user_id'];

                 $HTTP_ACCEPT_CHARSET=isset($_SERVER['HTTP_ACCEPT_CHARSET'])?$_SERVER['HTTP_ACCEPT_CHARSET']:"";
                 $HTTP_ACCEPT_LANGUAGE=isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])?$_SERVER['HTTP_ACCEPT_LANGUAGE']:"";
                 $REMOTE_ADDR=$_SERVER['REMOTE_ADDR'];


                 $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                 $stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
                 $stm->bindParam(':HTTP_ACCEPT_CHARSET', $HTTP_ACCEPT_CHARSET,PDO::PARAM_STR);
                 $stm->bindParam(':HTTP_ACCEPT_LANGUAGE', $HTTP_ACCEPT_LANGUAGE,PDO::PARAM_STR);
                 $stm->bindParam(':REMOTE_ADDR', $REMOTE_ADDR,PDO::PARAM_STR);
                 $stm->execute();
             }
             catch(PDOException $e) {$this->uFunc->error('uSes 180'/*.$e->getMessage()*/);}

             /** @noinspection PhpUndefinedVariableInspection */
             while($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                 if(isset($_COOKIE["ses_id"])) {

                     if($qr->sesId===$_COOKIE["ses_id"]) {
                         $_SESSION['SESSION']['sesId']=$qr->sesId;
                         $_SESSION['SESSION']['user_id']=$qr->user_id;
                         $_SESSION['SESSION']['HTTP_USER_AGENT']=$qr->HTTP_USER_AGENT;
                         $_SESSION['SESSION']['HTTP_ACCEPT_CHARSET']=$qr->HTTP_ACCEPT_CHARSET;
                         $_SESSION['SESSION']['HTTP_ACCEPT_LANGUAGE']=$qr->HTTP_ACCEPT_LANGUAGE;
                         $_SESSION['SESSION']['REMOTE_ADDR']=$qr->REMOTE_ADDR;
                         @$_SESSION['SESSION']['timezone_difference_isset_always']=$_SESSION['SESSION']['timezone_difference']=$qr->timezone_difference_isset_always;
                         $this->resetPhpSesbyDb();
                         $this->getUserACL();

                         if(!isset($_SESSION['SESSION']['lastupdate'])) {
                             $_SESSION['SESSION']['lastupdate'] = 0;
                         }
                         if(($_SESSION['SESSION']['lastupdate']+ses_updatetime)<time()) {//Если подошло время обновления сессии в БД, то обновляем ее
                             $this->update(); return true;
                         }

                         return true;
                     }

                     if(uString::isHash($_COOKIE["ses_id"])) {//не правильный sesId - нужно удалить сессию
                         try {

                             $stm1=$this->uFunc->pdo("uSes")->prepare("DELETE FROM
                             u235_list
                             WHERE
                             sesId=:sesId AND
                             site_id=:site_id
                             ");
                             $site_id=site_id;
                             $stm1->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                             $stm1->bindParam(':sesId', $qr->sesId,PDO::PARAM_STR);
                             $stm1->execute();
                         }
                         catch(PDOException $e) {$this->uFunc->error('uSes 190'/*.$e->getMessage()*/);}
                     }
                 }
             }
             $this->makeDefault(); return false;

		 }
		 else {//Пользователь не передал данные сессии - создаем сессию по умолчанию
                 $this->makeDefault();
                 return false;
		 }
         return false;
	 }
}
