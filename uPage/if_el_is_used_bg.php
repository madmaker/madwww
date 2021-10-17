<?php
namespace uPage\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class if_el_is_used_bg {
    /**
     * @var int
     */
    private $el_id;
    private $el_type;
    /**
     * @var uFunc
     */
    private $uFunc;
    /**
     * @var uSes
     */
    private $uSes;
    private $uCore;
    private function check_data() {
        if(!isset($_POST["el_type"],$_POST["el_id"])) $this->uFunc->error(0,1);
        if(
            $_POST["el_type"]!=="art"&&
            $_POST["el_type"]!=="form"
        ) $this->uFunc->error(0,1);

        $this->el_type=$_POST["el_type"];
        $this->el_id=(int)$_POST["el_id"];
    }
    private function get_pages($el_id,$el_type,$site_id=site_id){
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            u235_pages.page_id,
            page_title
            FROM 
            u235_pages
            JOIN
            u235_rows u235r on u235_pages.page_id = u235r.page_id and u235_pages.site_id = u235r.site_id
            JOIN
            u235_cols u235c on u235r.row_id = u235c.row_id and u235r.site_id = u235c.site_id
            JOIN
            u235_cols_els u235ce on u235c.col_id = u235ce.col_id and u235c.site_id = u235ce.site_id
            WHERE
            el_type=:el_type AND
            el_id=:el_id AND
            u235_pages.site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':el_type', $el_type,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':el_id', $el_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}
        return 0;
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");//Если не нужна кнопка авторизоваться на странице

        $this->uFunc=new uFunc($this->uCore);

        $this->check_data();
        $pages=$this->get_pages($this->el_id,$this->el_type);
        echo json_encode(array(
            'status'=>"done",
            "pages"=>$pages
        ));
    }
}
new if_el_is_used_bg($this);
