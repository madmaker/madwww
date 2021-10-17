<?php
namespace uNavi\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
class eip_sitemap {
    public $uFunc;
    public $uSes;
    private $uCore;
    public $page_id,$page_mod,$page,$mod,$cur_page_id,$cur_page;

    public function text($str) {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->uCore->text(array('uNavi','eip_sitemap_bg'),$str);
    }

    private function check_data() {
        if(!isset($_POST['tree_head_page_id'],$_POST['mod'],$_POST['page_id'])) $this->uFunc->error(10);
        if(strpos($_POST['tree_head_page_id'],'s')===0) {
            $page_id=str_replace('s','',$_POST['tree_head_page_id']);
            if(!uString::isDigits($page_id)) $this->uFunc->error(20);
            $this->page_mod='page';
        }
        elseif(strpos($_POST['tree_head_page_id'],'p')===0) {
            $page_id=str_replace('p','',$_POST['tree_head_page_id']);
            if(!uString::isDigits($page_id)) $this->uFunc->error(30);
            $this->page_mod='uPage';
        }
        else {
            if(!uString::isDigits($_POST['tree_head_page_id'])) $this->uFunc->error(40);
            $this->page_mod='modular';
        }
        $this->page_id=$_POST['tree_head_page_id'];

        if(!uString::isDigits($_POST['page_id'])) $this->uFunc->error(45);
        $this->cur_page_id=$_POST['page_id'];

        $this->mod=$_POST['mod'];
        if($this->mod!='page'&&
            $this->mod!='uPage') $this->mod='modular';
    }
    private function get_page_data() {
        if(strpos($_POST['tree_head_page_id'],'s')===0) {
            $page_id=str_replace('s','',$_POST['tree_head_page_id']);
            if(!uString::isDigits($page_id)) $this->uFunc->error(50);

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("pages")->prepare("SELECT
                page_title,
                page_id,
                page_name,
                navi_parent_page_id
                FROM
                u235_pages_html
                WHERE
                site_id=:site_id AND
                page_id=:page_id");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(60);
                $qr->page_mod='page';
                return $qr;
            }
            catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}
        }
        elseif(strpos($_POST['tree_head_page_id'],'p')===0) {
            $page_id=str_replace('p','',$_POST['tree_head_page_id']);
            if(!uString::isDigits($page_id)) $this->uFunc->error(80);

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
                page_title,
                page_id,
                navi_parent_page_id
                FROM
                u235_pages
                WHERE
                site_id=:site_id AND
                page_id=:page_id");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(90);
                $qr->page_mod='uPage';
                return $qr;
            }
            catch(PDOException $e) {$this->uFunc->error('100'/*.$e->getMessage()*/);}
        }
        else {
            if(!uString::isDigits($_POST['tree_head_page_id'])) $this->uFunc->error(105);
            $page_id=$_POST['tree_head_page_id'];

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("pages")->prepare("SELECT
                page_title,
                page_mod,
                page_id,
                page_name,
                navi_parent_page_id
                FROM
                u235_pages_list
                WHERE
                page_id=:page_id
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if(!$res=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(110);
                return $res;
            }
            catch(PDOException $e) {$this->uFunc->error('120'/*.$e->getMessage()*/);}
        }
        return 0;
    }
    private function get_cur_page_data() {
        if($this->mod=='page') {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("pages")->prepare("SELECT
                page_title,
                page_id,
                page_name,
                navi_parent_page_id
                FROM
                u235_pages_html
                WHERE
                site_id=:site_id AND
                page_id=:page_id");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->cur_page_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                $qr=$stm->fetch(PDO::FETCH_OBJ);
                $qr->page_mod='page';
                return $qr;
            }
            catch(PDOException $e) {$this->uFunc->error('125'/*.$e->getMessage()*/);}
        }
        elseif($this->mod=='uPage') {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
                page_title,
                page_id,
                page_url,
                navi_parent_page_id
                FROM
                u235_pages
                WHERE
                site_id=:site_id AND
                page_id=:page_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->cur_page_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                $qr=$stm->fetch(PDO::FETCH_OBJ);
                $qr->page_mod='uPage';
                return $qr;
            }
            catch(PDOException $e) {$this->uFunc->error('130'/*.$e->getMessage()*/);}
        }
        else {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("pages")->prepare("SELECT
                page_title,
                page_mod,
                page_id,
                page_name,
                navi_parent_page_id
                FROM
                u235_pages_list
                WHERE
                page_id=:page_id
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->cur_page_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if(!$res=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(135);
                return $res;
            }
            catch(PDOException $e) {$this->uFunc->error('140'/*.$e->getMessage()*/);}
        }
        return 0;
    }
    public function check_if_children_exists($page) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("SELECT
            page_id
            FROM
            u235_pages_html
            WHERE
            site_id=:site_id AND
            page_category='' AND
            navi_parent_page_id=:navi_parent_page_id".
            ($this->mod=='page'?" AND page_id!=:page_id ":"").
            " LIMIT 1
            ");
            $site_id=site_id;
            if($this->mod=="page")/** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->cur_page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':navi_parent_page_id', $page,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($stm->fetch(PDO::FETCH_OBJ)) return true;
        }
        catch(PDOException $e) {$this->uFunc->error('150'/*.$e->getMessage()*/);}

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("SELECT
            page_id
            FROM
            u235_pages_list
            WHERE
            page_category='' AND
            navi_parent_page_id=:navi_parent_page_id".
            ($this->mod!='page'?" AND page_id!=:page_id ":'')."
            LIMIT 1
            ");
            if($this->mod!='page') /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->cur_page_id,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':navi_parent_page_id', $page,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($stm->fetch(PDO::FETCH_OBJ)) return true;
        }
        catch(PDOException $e) {$this->uFunc->error('160'/*.$e->getMessage()*/);}

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            page_id
            FROM
            u235_pages
            WHERE
            page_type='' AND
            navi_parent_page_id=:navi_parent_page_id".
            ($this->mod!='page'?" AND page_id!=:page_id ":'')."
            LIMIT 1
            ");
            if($this->mod!='page') /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->cur_page_id,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':navi_parent_page_id', $page,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($stm->fetch(PDO::FETCH_OBJ)) return true;
        }
        catch(PDOException $e) {$this->uFunc->error('165'/*.$e->getMessage()*/);}

        return false;
    }
    public function get_children() {
        //uPage-pages
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $uPage_query=$this->uFunc->pdo("uPage")->prepare("SELECT
            page_title,
            page_id,
            page_url
            FROM
            u235_pages
            WHERE
            site_id=:site_id AND
            (deleted=0 OR deleted IS NULL) AND
            page_type!='folder' AND
            navi_parent_page_id=:navi_parent_page_id"
            .($this->uCore->mod=='uPage'?" AND page_id!=:page_id":'')."
            ORDER BY
            page_title");
            $site_id=site_id;
            if($this->uCore->mod=='uPage') /** @noinspection PhpUndefinedMethodInspection */$uPage_query->bindParam(':page_id', $this->cur_page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$uPage_query->bindParam(':navi_parent_page_id', $this->page_id,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$uPage_query->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$uPage_query->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('170'/*.$e->getMessage()*/);}

//        //html-pages
//        try {
//            /** @noinspection PhpUndefinedMethodInspection */
//            $html_query=$this->uFunc->pdo("pages")->prepare("SELECT
//            page_title,
//            page_id,
//            page_name
//            FROM
//            u235_pages_html
//            WHERE
//            site_id=:site_id AND
//            page_category='' AND
//            navi_parent_page_id=:navi_parent_page_id"
//            .($this->mod=='page'?" AND page_id!=:page_id":'')."
//            ORDER BY
//            page_title");
//            $site_id=site_id;
//            if($this->mod=='page') /** @noinspection PhpUndefinedMethodInspection */$html_query->bindParam(':page_id', $this->cur_page_id,PDO::PARAM_INT);
//            /** @noinspection PhpUndefinedMethodInspection */$html_query->bindParam(':navi_parent_page_id', $this->page_id,PDO::PARAM_STR);
//            /** @noinspection PhpUndefinedMethodInspection */$html_query->bindParam(':site_id', $site_id,PDO::PARAM_INT);
//            /** @noinspection PhpUndefinedMethodInspection */$html_query->execute();
//        }
//        catch(PDOException $e) {$this->uFunc->error('180'/*.$e->getMessage()*/);}
//
//        //modular pages
//        try {
//            /** @noinspection PhpUndefinedMethodInspection */
//            $list_query=$this->uFunc->pdo("pages")->prepare("SELECT
//            page_mod,
//            page_title,
//            page_id,
//            page_name
//            FROM
//            u235_pages_list
//            WHERE
//            page_category='' AND
//            navi_parent_page_id=:navi_parent_page_id"
//            .($this->mod!='page'&&$this->mod!='uPage'?" AND page_id!=:page_id":'')."
//            ORDER BY
//            page_mod,
//            page_title");
//            if($this->mod!='page'&&$this->mod!='uPage') /** @noinspection PhpUndefinedMethodInspection */$list_query->bindParam(':page_id', $this->cur_page_id,PDO::PARAM_INT);
//            /** @noinspection PhpUndefinedMethodInspection */$list_query->bindParam(':navi_parent_page_id', $this->page_id,PDO::PARAM_STR);
//            /** @noinspection PhpUndefinedMethodInspection */$list_query->execute();
//        }
//        catch(PDOException $e) {$this->uFunc->error('190'/*.$e->getMessage()*/);}
        /** @noinspection PhpUndefinedVariableInspection */
        return /*array($html_query,$list_query,*/$uPage_query/*)*/;
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
        
        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->page=$this->get_page_data();
        $this->cur_page=$this->get_cur_page_data();
    }
}
$uNavi=new eip_sitemap($this);
ob_start();
?>

<?/*if($uNavi->page_id=='0') {*/?>
    <div id="uNavi_sitemap_div_<?=$uNavi->page_id?>">
        <div class="uNavi_sitemap_p">
            <a href="<?=u_sroot.$uNavi->page->page_mod.'/'.(isset($uNavi->page->page_name)?$uNavi->page->page_name:$uNavi->page->page_id)?>" target="_blank"><?=$uNavi->page->page_title?></a>
            <?if($uNavi->cur_page->navi_parent_page_id==$uNavi->page_id) {?>
                <button type="button" class="btn btn-success btn-xs" onclick="uNavi_eip.bc_attach('<?=$uNavi->page_id?>')" disabled id="uNavi_attach_btn_<?=$uNavi->page_id?>"><span class="glyphicon glyphicon-link"></span> <?=$uNavi->text("Attached here - disabled btn"/*Прикреплено сюда*/)?></button>
            <?} else {?>
                <button type="button" class="btn btn-link" onclick="uNavi_eip.bc_attach('<?=$uNavi->page_id?>')" id="uNavi_attach_btn_<?=$uNavi->page_id?>"><span class="glyphicon glyphicon-link"></span> <?=$uNavi->text("Attach here - btn"/*Прикрепить*/)?></button>
            <?}?>
        </div>
        <?/*}*/?>
        <div class="collapse panel-collapse collapse in" id="uNavi_sitemap_collapse_<?=$uNavi->page_id?>">
            <div class="well">
                <div class="form-horizontal">
                    <div class="form-group">
                        <div class="input-group input-group-sm">
                            <input id="uNavi_eip_bc_filter_<?=$uNavi->page_id?>" class="form-control input-sm" placeholder="<?=$uNavi->text("Filter inside - placeholder"/*Фильтр внутри*/)?> <?=htmlspecialchars($uNavi->page->page_title)?>" onkeyup="uNavi_eip.bc_filter('<?=$uNavi->page_id?>')">
                    <span class="input-group-btn">
                        <button class="btn btn-default btn-sm" type="button"><span class="glyphicon glyphicon-search" onclick="uNavi_eip.bc_filter('<?=$uNavi->page_id?>')"></span></button>
                    </span>
                        </div>
                    </div>
                </div>
                <?$query_ar=$uNavi->get_children();
                /*while($page=$query_ar[0]->fetch(PDO::FETCH_OBJ)) {?>
                    <div id="uNavi_sitemap_div_s<?=$page->page_id?>">
                        <p class="uNavi_sitemap_s uNavi_sitemap_p_<?=$uNavi->mod=='modular'?'p':'s'?><?=$uNavi->page_id?>">
                            <?if($uNavi->check_if_children_exists('s'.$page->page_id)) {?>
                                <button onclick="uNavi_eip.load_site_tree('s<?=$page->page_id?>')" class="btn btn-outline" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample"><span class="glyphicon glyphicon-plus collapse_btn"></span></button>
                            <?}?>
                            <a href="<?=u_sroot?>page/<?=$page->page_name?>" target="_blank"><?=$page->page_title;?> (text)</a>
                            <?if($uNavi->cur_page->navi_parent_page_id=='s'.$page->page_id) {?>
                                <button type="button" class="btn btn-success btn-xs" onclick="uNavi_eip.bc_attach('s<?=$page->page_id?>')" disabled id="uNavi_attach_btn_s<?=$page->page_id?>"><span class="glyphicon glyphicon-link"></span> <?=$uNavi->text("Attached here - disabled btn")?></button>
                            <?}
                            else {?>
                                <button type="button" class="btn btn-link" onclick="uNavi_eip.bc_attach('s<?=$page->page_id?>')" id="uNavi_attach_btn_s<?=$page->page_id?>"><span class="glyphicon glyphicon-link"></span> <?=$uNavi->text("Attach here - btn")?></button>
                            <?}?>
                        </p>
                    </div>
                <?}
                while($page=$query_ar[1]->fetch(PDO::FETCH_OBJ)) {?>
                    <div id="uNavi_sitemap_div_<?=$page->page_id?>">
                        <p class="uNavi_sitemap_p uNavi_sitemap_p_<?=$uNavi->mod=='modular'?'p':'s'?><?=$uNavi->page_id?>">
                            <?if($uNavi->check_if_children_exists($page->page_id)) {?>
                                <button onclick="uNavi_eip.load_site_tree('<?=$page->page_id?>')" class="btn btn-outline" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample"><span class="glyphicon glyphicon-plus collapse_btn"></span></button>
                            <?}?>
                            <a href="<?=u_sroot.$page->page_mod.'/'.$page->page_name?>" target="_blank"><?=$page->page_title;?></a>
                            <?if($uNavi->cur_page->navi_parent_page_id==$page->page_id) {?>
                                <button type="button" class="btn btn-success btn-xs" onclick="uNavi_eip.bc_attach(<?=$page->page_id?>)" disabled id="uNavi_attach_btn_<?=$page->page_id?>"><span class="glyphicon glyphicon-link"></span> <?=$uNavi->text("Attached here - disabled btn")?></button>
                            <?} else {?>
                                <button type="button" class="btn btn-link" onclick="uNavi_eip.bc_attach(<?=$page->page_id?>)" id="uNavi_attach_btn_<?=$page->page_id?>"><span class="glyphicon glyphicon-link"></span> <?=$uNavi->text("Attach here - btn")?></button>
                            <?}?>
                        </p>
                    </div>
                <?}*/
                while($page=$query_ar/*[2]*/->fetch(PDO::FETCH_OBJ)) {?>
                    <div id="uNavi_sitemap_div_p<?=$page->page_id?>">
                        <p class="uNavi_sitemap_p uNavi_sitemap_p_<?=$uNavi->mod=='modular'?'p':'s'?><?=$uNavi->page_id?>">
                            <?if($uNavi->check_if_children_exists("p".$page->page_id)) {?>
                                <button onclick="uNavi_eip.load_site_tree('p<?=$page->page_id?>')" class="btn btn-outline" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample"><span class="glyphicon glyphicon-plus collapse_btn"></span></button>
                            <?}?>
                            <a href="<?=u_sroot.'uPage/'.((trim($page->page_url)!="")?$page->page_url:$page->page_id)?>" target="_blank"><?=$page->page_title;?> (page)</a>
                            <?if($uNavi->cur_page->navi_parent_page_id=="p".$page->page_id) {?>
                                <button type="button" class="btn btn-success btn-xs" onclick="uNavi_eip.bc_attach(<?=$page->page_id?>)" disabled id="uNavi_attach_btn_<?=$page->page_id?>"><span class="glyphicon glyphicon-link"></span> <?=$uNavi->text("Attached here - disabled btn"/*Прикреплено сюда*/)?></button>
                            <?} else {?>
                                <button type="button" class="btn btn-link" onclick="uNavi_eip.bc_attach('p<?=$page->page_id?>')" id="uNavi_attach_btn_<?=$page->page_id?>"><span class="glyphicon glyphicon-link"></span> <?=$uNavi->text("Attach here - btn"/*Прикрепить*/)?></button>
                            <?}?>
                        </p>
                    </div>
                <?}?>
            </div>
        </div>
    </div>

<?$page_content=ob_get_contents();
ob_end_clean();
echo '{
"status":"done",
"html":"'.rawurlencode($page_content).'",
"tree_page_id":"'.$uNavi->page_id.'"
}';