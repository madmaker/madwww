<?php
/** @noinspection SpellCheckingInspection */
require_once 'inc/server_settings.php';
/** https://www.unixtimestamp.com */
$makeNumber=1589233229;

define ('v_timestamp',$makeNumber);//Дата релиза (нужно для js-файлов)
//https://www.unixtimestamp.com
define('ses_lifetime',172800);//Время жизни куки и сессии в секундах (48 часов - 172800 секунд)
define('ses_updatetime',1800);//Время в секундах, спустя которое нужно обновлять сессию в БД, если пользователь все еще активен. (30 минут - 1800
define('mp_admin_gr_id',13);
//define("urlbox_api_key","DYkpJDOhH4wDZ9Vn");
define('dadata_token', '8d514b14be632461dbc8141b69b27ef092e0230d');

//Recaptcha
define('recaptcha_key', '6LfExisUAAAAAL2BYWaR-zTJqa1oYJeEXTjkgxb9');
define('recaptcha_secret', '6LfExisUAAAAAAVNViKXJi7_2Da2ZG_TSQfePqDs');

define('ses_hack_lifetime',3600);//1 час - время жизни хак-сессий. Хак-сессия обновляется после каждого использования
class uConf {
    public $sql,
        $mp_access_ar;
    private function defineVars() {
        $this->mp_access_ar=array(27,28,29);
    }
    private function defineSql() {
        $this->sql['host']=db_host;
        $this->sql['user']=db_user;
        $this->sql['pass']=db_pass;
    }
    public function define_site_root() {
        if(!isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        }
        if(!defined('u_protocol')) {
            define('u_protocol', $_SERVER['HTTP_X_FORWARDED_PROTO']);
        }

        if(isset($_SERVER['SERVER_NAME'])) {
            if(!defined('u_sroot')) {
                define('u_sroot', u_protocol . '://' . $_SERVER['SERVER_NAME'] . '/');
            }
        }
        else if(!defined('u_sroot')) {
            define('u_sroot', '');
        }
    }
    public function __construct() {

        $this->defineSql();
        $this->defineVars();
    }
}
