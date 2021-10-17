<?php
namespace uCat;
use PDO;
use PDOException;
use processors\uFunc;
use uCore;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uCat/classes/common.php";

class save_order_admin_comment {
    /**
     * @var uFunc
     */
    private $uFunc;
    /**
     * @var uSes
     */
    private $uSes;
    /**
     * @var string
     */
    private $admin_comment;
    /**
     * @var int
     */
    private $order_id;
    private $uCore;
    private function check_data() {
        if(!isset(
            $_POST["order_id"],
            $_POST["admin_comment"]
        )) {
            print json_encode(array(
                "status"=>"error",
                "msg"=>"wrong data 1583239783"
            ));
            exit;
        }

        $this->order_id=(int)$_POST["order_id"];
        $this->admin_comment=trim($_POST["admin_comment"]);
    }

    private function save_admin_comment($site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE 
            orders
            SET
            admin_comment=:admin_comment
            WHERE
            order_id=:order_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':admin_comment', $this->admin_comment,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':order_id', $this->order_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1583240493'/*.$e->getMessage()*/,1);}
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new uCore();
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");

        $this->uFunc=new uFunc($this->uCore);

        $this->check_data();
        $this->save_admin_comment();

        print json_encode(array(
            "status"=>"done"
        ));
    }
}
new save_order_admin_comment($this);
