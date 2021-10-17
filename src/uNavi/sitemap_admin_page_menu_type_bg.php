<?php
namespace uNavi\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";

class sitemap_admin_page_menu_type_bg {
    private $uCore;
    private $type;

    private function check_data() {
        if(!isset($_POST['page_id'],$_POST['type'])) $this->uFunc->error(10);
        $this->page_id=&$_POST['page_id'];
        $this->type=&$_POST['type'];
        if(!uString::isDigits(str_replace('s','',$this->page_id))) $this->uFunc->error(20);
        if(!uString::isDigits($this->type)) $this->uFunc->error(30);
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);

        $this->check_data();
        
        if(strpos($this->page_id, 's')===0) {

            $this->page_id=str_replace('s','',$this->page_id);

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("pages")->prepare("UPDATE 
                u235_pages_html 
                SET 
                navi_personal_menu=:navi_personal_menu 
                WHERE 
                page_id=:page_id AND 
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':navi_personal_menu', $this->type,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
        }
        else die('done');//pages_list can't be edited from here

        echo 'done';
    }
}
/*$newClass=*/new sitemap_admin_page_menu_type_bg($this);