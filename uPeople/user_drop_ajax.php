<?
namespace uPeople\admin;

use PDO;
use PDOException;
use processors\uFunc;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";

class user_drop_ajax{
    private $user_id;
    private $uFunc;
    private $uSes;
    private $uCore;

    private function check_data() {
        if(!isset($_POST['user_id'])) $this->uFunc->error(10);
        $this->user_id=(int)$_POST["user_id"];
    }
    private function delete_user($user_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPeople")->prepare("DELETE  
            FROM 
            u235_people
            WHERE 
            user_id=:user_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}

        echo "done";
        exit;
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new \uSes($this->uCore);
        if(!$this->uSes->access(10)) die('forbidden');
        
        $this->uFunc=new uFunc($this->uCore);

        $this->check_data();
        $this->delete_user($this->user_id);
    }
}
new user_drop_ajax($this);