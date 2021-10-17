<?php
namespace dataairbag;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class registerHost {
    /**
     * @var int
     */
    public $print_data;
    private $host_id;
    private $hash;
    /**
     * @var uSes
     */
    private $uSes;
    /**
     * @var uFunc
     */
    private $uFunc;
    private $uCore;
    private function check_data() {
        if(!isset($_GET["hash"])) {
            print '<div class="jumbotron">
                <h1 class="page-header">Регистрация компьютера. Ошибка 1</h1>
                <p>Такой страницы не существует. Попробуйте заново</p>
            </div>';
            return 0;
        }
        if(!$this->uSes->access(2)) {
            print '<div class="jumbotron">
            <h1 class="page-header">Вы не авторизованы</h1>
                <p>Пожалуйста, авторизуйтесь</p>
                <p><a href="javascript:void(0)" class="btn btn-primary btn-lg"  onclick="uAuth_form.open()">Авторизоваться</a></p>
            </div>';
            return 0;
        }

        $this->hash=$_GET["hash"];
        if(!uString::isHash($this->hash)) {
            print '<div class="jumbotron">
                <h1 class="page-header">Регистрация компьютера. Ошибка 2</h1>
                <p>Такой страницы не существует. Попробуйте заново</p>
            </div>';
            return 0;
        }

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("dataairbag")->prepare("SELECT
            host_id,
            status,
            user_id
            FROM 
            hosts 
            WHERE 
            hash=:hash
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':hash', $this->hash,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) {
            print '<div class="jumbotron">
                <h1 class="page-header">Регистрация компьютера. Ошибка 3</h1>
                <p>Такой страницы не существует. Попробуйте заново</p>
            </div>';
            return 0;
        }

        $qr->user_id=(int)$qr->user_id;

        if($qr->status!=="") {
            if($qr->user_id===$this->uSes->get_val("user_id")) {
                print '<div class="jumbotron">
                <h1 class="page-header">Регистрация компьютера</h1>
                <p>Этот компьютер уже зарегистрирован в вашем кабинете</p>
            </div>';
                return 0;
            }
            else {
                print '<div class="jumbotron">
                <h1 class="page-header">Регистрация компьютера. Ошибка 4</h1>
                <p>Такой страницы не существует. Попробуйте заново</p>
            </div>';
                return 0;
            }
        }

        $this->host_id=$qr->host_id;


        return 1;
    }


    private function register() {
        //get new ftpuser for this host
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("dataairbag")->prepare("SELECT 
            ftpuser_id,
            username,
            userpass
            FROM 
            ftpusers_pool 
            WHERE 
            host_id=0
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        if($ftp=$stm->fetch(PDO::FETCH_OBJ)) {

        }
        else $this->uFunc->error(0);

        //register host in account
        $user_id=$this->uSes->get_val("user_id");
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("dataairbag")->prepare("UPDATE 
            hosts
            SET
            user_id=:user_id,
            status='registered',
            ftpusername=:ftpusername,
            ftpuserpass=:ftpuserpass
            WHERE
            host_id=:host_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':host_id', $this->host_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':ftpusername', $ftp->username,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':ftpuserpass', $ftp->userpass,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}

        //get new ftpuser for this host
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("dataairbag")->prepare("UPDATE  
            ftpusers_pool 
            SET
            host_id=:host_id
            WHERE 
            ftpuser_id=:ftpuser_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':host_id', $this->host_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':ftpuser_id', $ftp->ftpuser_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uSes=new uSes($this->uCore);
        $this->uFunc=new uFunc($this->uCore);

        ob_start();

        $this->print_data=0;

        if($this->check_data()) {

            $this->register();
            $this->print_data=1;
        }
    }
}
$da=new registerHost($this);
if($da->print_data) {
    ?>

    <div class="jumbotron">
        <h1 class="page-header">Регистрация компьютера</h1>
        <p>Ваш компьютер успешно зарегистрирован</p>
        <h2>Что делать дальше?</h2>
        <ol>
            <li>Открыть этот компьютер в <a href="/dataairbag/dashboard">личном кабинете</a></li>
            <li>Выбрать папки и файлы, резервные копии которых нужно делать</li>
            <li>DataAirbag дальше будет все делать автоматически</li>
        </ol>
    </div>

    <?
}
$this->page_content=ob_get_contents();
ob_end_clean();

include 'templates/template.php';
