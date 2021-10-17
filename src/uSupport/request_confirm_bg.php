<?php
namespace uSupport;
use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once 'uAuth/classes/common.php';
require_once "uSupport/classes/common.php";

class uSup_request_confirm {
    public $uAuth;
    public $uFunc;
    public $uSup;
    private $uCore,$tic_id,$confirmation_hash;
    public $txt;
    private function check_data() {
        if(!isset($this->uCore->url_prop[1],$this->uCore->url_prop[2])) return false;
        $this->tic_id=$this->uCore->url_prop[1];
        $this->confirmation_hash=$this->uCore->url_prop[2];
        if(!uString::isDigits($this->tic_id)) return false;
        if(!uString::isHash($this->confirmation_hash)) return false;
        return true;
    }
    private function confirm_req() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
            user_id,
            tic_subject,
            tic_confirmed,
            confirmation_hash,
            company_id
            FROM
            u235_requests
            WHERE
            tic_id=:tic_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tic_id', $this->tic_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$tic=$stm->fetch(PDO::FETCH_OBJ)) {
                $this->txt='<p class="bg-danger">К сожалению информация по такому запросу не найдена. Проверьте адрес страницы в письме, из которого ее открыли.</p>';
                return false;
            }

        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}


        /** @noinspection PhpUndefinedVariableInspection */
        if(!(int)$tic->tic_confirmed) {
            if($tic->confirmation_hash==$this->confirmation_hash) {
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("uAuth")->prepare("SELECT
                    email
                    FROM
                    u235_users
                    WHERE
                    user_id=:user_id
                    ");
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $tic->user_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                    /** @noinspection PhpUndefinedMethodInspection */
                    if(!$user=$stm->fetch(PDO::FETCH_OBJ)) {
                        $this->txt='<p class="bg-danger">К сожалению информация по такому запросу не найдена. Проверьте адрес страницы в письме, из которого ее открыли.</p>';
                        return false;
                    }
                }
                catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}


                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("uSup")->prepare("UPDATE
                    u235_requests
                    SET
                    tic_confirmed=1,
                    confirmation_hash=''
                    WHERE
                    tic_id=:tic_id AND
                    site_id=:site_id
                    ");
                    $site_id=site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tic_id', $this->tic_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}


                $this->txt='<p class="text-success">Спасибо, запрос успешно подтвержден. Мы его уже рассматриваем.</p>';
//                <p>Чтобы каждый раз не подтверждать запросы, <a href="'.$link.'">активируйте свою учетную запись</a></p>';


                //NOTIFY admins that request is confirmed
                if($com_info=$this->uSup->com_id2com_info($tic->company_id,"two_level")) {
                    $two_level=(int)$com_info->two_level;
                }
                else $two_level=0;

                if($two_level) {//notify admins
                    $q_com_admins_list=$this->uSup->get_com_admins_to_notify_about_requests("user_id",$tic->company_id);
                    $q_admin_ids=" (1=0";
                    while($admin=$q_com_admins_list->fetch(PDO::FETCH_OBJ)) {
                        $q_admin_ids.=" OR user_id=".$admin->user_id." ";
                    }
                    $q_admin_ids.=" )";

                    try {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $stm=$this->uFunc->pdo("uAuth")->prepare("SELECT 
                        firstname,
                        email
                        FROM 
                        u235_users 
                        WHERE 
                        ".$q_admin_ids."
                        ");
                        /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                    }
                    catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
                    $q_recipients=$stm;
                }
                else {//notify operators
                    $q_recipients=$this->uSup->get_operators("firstname,email");
                }
                while($oper=$q_recipients->fetch(PDO::FETCH_OBJ)) {
                    $this->uSup->new_msg_cons_notification($this->tic_id,$oper->email,$oper->firstname,site_id);
                }

                return true;
            }
            else {
                $this->txt='<p class="bg-danger">К сожалению информация по такому запросу не найдена. Проверьте адрес страницы в письме, из которого ее открыли.</p>';
                return false;
            }
        }
        else {
            $this->txt='<p class="text-success">Этот запрос уже подтвержден! Мы его рассматриваем.</p>';
            return false;
        }
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uAuth=new \uAuth\common($this->uCore);
        $this->uFunc=new uFunc($this->uCore);
        $this->uSup=new common($this->uCore);

        if($this->check_data()) {
            $this->confirm_req();
        }
        else $this->txt='<p class="bg-danger">К сожалению информация по такому запросу не найдена. Проверьте адрес страницы в письме, из которого ее открыли.</p>';
    }
}
$uSup=new uSup_request_confirm($this);
ob_start();?>

<div class="jumbotron">
    <h1 class="page-header">Подтверждение запроса в техподдержку</h1>
    <?=$uSup->txt?>
</div>

<?$this->page_content=ob_get_contents();
ob_end_clean();

include "templates/template.php";
