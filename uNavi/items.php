<?php
namespace uNavi\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;
use uString;

require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';
require_once 'processors/uMenu.php';

class items {
    private $uCore;
    public $cat_id;

    public function text($str) {
        return $this->uCore->text(array('uNavi','items'),$str);
    }

    private function check_data() {
        if(!isset($this->uCore->url_prop[1])) {
            header("Location :".u_sroot."uNavi/cats");
            exit;
        }
        $this->cat_id=$this->uCore->url_prop[1];
        if(!uString::isDigits($this->cat_id)) {
            header("Location :".u_sroot."uNavi/cats");
            exit;
        }
    }
    public function get_cat_title() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare("SELECT
            cat_title
            FROM 
            u235_cats 
            WHERE
            cat_id=:cat_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $this->cat_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$cat=$stm->fetch(PDO::FETCH_OBJ)) {
                header("Location :".u_sroot."uNavi/cats");
                exit;
            }
            return $cat->cat_title;
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        $this->uMenu=new \uMenu($this->uCore);

        $this->uCore->page['page_title']=$this->text("Page name"/*Пункты меню*/);

        if($this->uSes->access(7)) {
            $this->check_data();
        }
    }
}
$uNavi=new items($this);
ob_start();
if($uNavi->uSes->access(7)) {?>
        <?=$uNavi->uMenu->insert_menu($uNavi->cat_id)?>

    <?$this->page_content=ob_get_contents();
    ob_end_clean();
    include 'templates/template.php';
}
else {?>
    <div class="jumbotron">
        <h1 class="page-header"><?=$uNavi->text("Log in - header")?></h1>
        <p><?=$uNavi->text("Log in please")?></p>
        <p><a href="javascript:void(0)" class="btn btn-primary btn-lg"  onclick="uAuth_form.open()"><?=$uNavi->text("Log in - btn txt")?></a></p>
    </div>
    <?
    $this->page_content=ob_get_contents();
    ob_end_clean();
    include 'templates/template.php';
}
ob_start();?>

<? $this->page_panel=ob_get_contents();
ob_end_clean();
