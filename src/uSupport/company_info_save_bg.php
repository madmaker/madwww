<?php
namespace uSupport;
use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class company_info_save {
    public $uFunc;
    public $uSes;
    private $uCore,$com_id;
    private function check_data() {
        if(!isset($_POST['com_id'])) $this->uFunc->error(10);
        $this->com_id=$_POST['com_id'];
        if(!uString::isDigits($this->com_id)) $this->uFunc->error(20);
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
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
        return false;
    }
    private function save_com_title() {
        $com_title=$_POST['com_title'];
        if(!strlen($com_title)) die('{
        "status":"error",
        "msg":"title is empty"
        }');

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("UPDATE
            u235_comps
            SET
            com_title=:com_title
            WHERE
            com_id=:com_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $com_title=uString::text2sql($com_title);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_title', $com_title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $this->com_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}

        exit('{
        "status":"done",
        "com_title":"'.rawurlencode($com_title).'"
        }');
    }
    private function two_level_switch() {
        $two_level=(int)$_POST['two_level'];
        $two_level=$two_level?1:0;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("UPDATE
            u235_comps
            SET
            two_level=:two_level
            WHERE
            com_id=:com_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':two_level', $two_level,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $this->com_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}

        exit('{
        "status":"done",
        "two_level":"'.$two_level.'"
        }');
    }
    private function notify_about_new_requests() {
        if(!isset($_POST['user_id'],$_POST['notify_about_new_requests'])) $this->uFunc->error(60);
        $user_id=(int)$_POST['user_id'];
        $notify_about_new_requests=(int)$_POST['notify_about_new_requests']?1:0;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("UPDATE
            u235_com_users
            SET
            notify_about_new_requests=:notify_about_new_requests
            WHERE
            com_id=:com_id AND
            user_id=:user_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':notify_about_new_requests', $notify_about_new_requests,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $this->com_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}

        exit('{
        "status":"done",
        "notify_about_new_requests":"'.$notify_about_new_requests.'",
        "user_id":"'.$user_id.'"
        }');
    }
    private function del_com_avatar() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("UPDATE
            u235_comps
            SET
            logo_timestamp=0
            WHERE
            com_id=:com_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $this->com_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('80'/*.$e->getMessage()*/);}

        @uFunc::rmdir('uSupport/com_avatars/'.site_id.'/'.$this->com_id);

        include_once 'inc/com_avatar.php';
        $avatar=new \uSup_com_avatar($this->uCore);

        exit('{
        "status":"done",
        "url":"'.rawurlencode($avatar->get_avatar('com_page',$this->com_id,time())).'"
        }');
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
        
        $this->check_data();
        if(!$this->check_access()) die('{"status":"forbidden"}');

        if(isset($_POST['com_title'])) $this->save_com_title();
        if(isset($_POST['del_com_avatar'])) $this->del_com_avatar();
        if(isset($_POST['two_level'])) $this->two_level_switch();
        if(isset($_POST['notify_about_new_requests'])) $this->notify_about_new_requests();
        else die('{"status":"forbidden"}');
    }
}
new company_info_save($this);