<?php
namespace uNavi\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';

class cats {
    private $uCore;

    public function text($str) {
        return $this->uCore->text(array('uNavi','cats'),$str);
    }

    public function get_cattypes() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare("SELECT
            type_title,
            type_id
            FROM
            u235_cattypes
            ORDER BY
            type_title ASC
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}
    }
    public function get_cats() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare("SELECT
              u235_cats.cat_id,
              cat_title,
              cat_type,
              cat_access,
              COUNT(u235_menu.id) AS menu_count
            FROM
              u235_cats
              LEFT JOIN
                u235_menu
                ON
                  u235_cats.cat_id=u235_menu.cat_id AND
                  u235_cats.site_id=u235_menu.site_id
            WHERE
              u235_cats.status IS NULL AND
              u235_cats.site_id=:site_id
            GROUP BY cat_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);

        $this->uCore->page['page_title']=$this->text("Page name"/*Меню*/);

        $this->uCore->uInt_js('uNavi','cats');

        if($this->uSes->access(7)) {
            $this->uFunc->incJs(u_sroot . "js/react/react.min.js");
            $this->uFunc->incJs(u_sroot . "js/react/react-dom.min.js");

            $this->uFunc->incJs(u_sroot."js/bootstrap_plugins/PopConfirm/jquery.popconfirm.js");

            $this->uFunc->incJs(u_sroot.'uNavi/js/cats.min.js');
            $this->uFunc->incCss(u_sroot . 'templates/u235/css/uNavi/uNavi.min.css');
        }
        else {
            if(isset($_POST['data_only'])) die('{"status":"forbidden"}');
        }
    }
}
$uNavi=new cats($this);
ob_start();
if($uNavi->uSes->access(7)) {
    if(isset($_POST['data_only'])) {
        $q_cats = $uNavi->get_cats();
        echo '{';
        for($i = 0;$cat = $q_cats->fetch(PDO::FETCH_OBJ);$i++) {
            echo '"cat_id_'.$i.'":"'.$cat->cat_id.'",';
            echo '"cat_title_'.$i.'":"'.rawurlencode($cat->cat_title).'",';
        }
        echo '"status":"done"}';
    }
    else {
        ?>
        <div id="uNavi_container"></div>

        <script type="text/javascript">
            if(typeof uNavi_cats==="undefined") {
                uNavi_cats={};
                uNavi_cats.cat_id=[];
                uNavi_cats.cat_title=[];
                uNavi_cats.cat_type=[];
                uNavi_cats.cat_access=[];
                uNavi_cats.cat_id2index=[];

                uNavi_cats.type_id=[];
                uNavi_cats.type_title=[];
                uNavi_cats.type_id2i=[];
            }
            <?//Categories
            $q_cats = $uNavi->get_cats();
            for($i = 0;$cat = $q_cats->fetch(PDO::FETCH_OBJ);$i++) { ?>
            i =<?=$i?>;
            uNavi_cats.cat_id[i] =<?=$cat->cat_id?>;
            uNavi_cats.cat_title[i] = "<?=rawurlencode($cat->cat_title)?>";
            uNavi_cats.cat_type[i] =<?=$cat->cat_type?>;
            uNavi_cats.cat_access[i] =<?=$cat->cat_access?>;
            uNavi_cats.cat_id2index[uNavi_cats.cat_id[i]] = i;
            <?}

            //Categories types
            $q_cats_types = $uNavi->get_cattypes();
            for($i = 0;$cat_type = $q_cats_types->fetch(PDO::FETCH_OBJ);$i++) { ?>
            i =<?=$i?>;
            uNavi_cats.type_id[i] =<?=$cat_type->type_id?>;
            uNavi_cats.type_title[i] = "<?=rawurlencode($cat_type->type_title)?>";
            uNavi_cats.type_id2i[uNavi_cats.type_id[i]] = i;
            <?}?>
        </script>
        <?
        $this->page_content = ob_get_contents();
        ob_end_clean();
        include 'templates/template.php';
    }

}
else {?>
    <div class="jumbotron">
        <h1 class="page-header"><?=$uNavi->text("Log in - header"/*Авторизация*/)?></h1>
        <p><?=$uNavi->text("Log in please"/*Пожалуйста, авторизуйтесь*/)?></p>
        <p><a href="javascript:void(0)" class="btn btn-primary btn-lg"  onclick="uAuth_form.open()"><?=$uNavi->text("Log in - btn txt"/*Авторизоваться*/)?></a></p>
    </div>
<?
    $this->page_content=ob_get_contents();
    ob_end_clean();
    include 'templates/template.php';
}?>
