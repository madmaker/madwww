<?
namespace uPeople\admin;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";

use PDO;
use PDOException;
use processors\uFunc;
use uString;

class users_list_admin {
    private $uFunc;
    private $uSes;
    private $uCore,$orderBy,$orderDir;
    public $qUsers,$users_num,$qUser_groups,$user_grId2Title,$isTrash,$inactive,$sort_by;

    private function clean_deleted_users() {
        $expiration_time=86400;//1 day

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPeople")->prepare("SELECT
            user_id,
            site_id
            FROM
            u235_people
            WHERE
            status='deleted' AND
            timestamp<:timestamp
            ");
            $timestamp=time()-$expiration_time;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':timestamp', $timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        while($user=$stm->fetch(PDO::FETCH_OBJ)) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm1=$this->uFunc->pdo("uPeople")->prepare("DELETE FROM
                u235_people_groups
                WHERE
                user_id=:user_id AND
                site_id=:site_id
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':user_id', $user->user_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':site_id', $user->site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
        }

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPeople")->prepare("DELETE FROM
            u235_people
            WHERE
            status='deleted' AND
            timestamp<:timestamp
            ");
            $timestamp=time()-$expiration_time;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':timestamp', $timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }

    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new \uSes($this->uCore);
        if(!$this->uSes->access(10)) die("forbidden");
        
        $this->uFunc=new uFunc($this->uCore);

        //popConfirm
        $this->uFunc->incJs('js/bootstrap_plugins/PopConfirm/jquery.popconfirm.min.js');
        $this->uFunc->incJs("/uPeople/js/users_list_admin.min.js");
        $this->uFunc->incCss("/uPeople/css/users_list_admin.min.css");

        $this->clean_deleted_users();
    }
}
$uPeople=new users_list_admin($this);

$this->uFunc->incCss(u_sroot.'uPeople/css/default.min.css');
$this->uFunc->incJs(u_sroot.'uPeople/js/users_list_admin.min.js');
ob_start();
?>

    <div class="form-horizontal">
        <div class="form-group">
            <div class="input-group">
                <input type="text" id="uPeople_users_filter" class="form-control" placeholder="Фильтр" onkeyup="uPeople_users_list_admin.users_filter()">
                <span class="input-group-btn">
                            <button class="btn btn-default" type="button" onclick="uPeople_users_list_admin.users_filter()"><span class="icon-search"></span></button>
                        </span>
            </div>
        </div>
    </div>
<div id="uPeople_users"><div class="well-lg bg-primary loading">Загрузка</div></div>

<?$this->page_content=ob_get_contents();
ob_end_clean();
include "templates/u235/template.php";