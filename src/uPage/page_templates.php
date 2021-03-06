<?php
namespace uPage\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class page_templates {
    public $language;
    private $uSes;
    public $uFunc;
    private $uCore;
    public function text($str) {
        return $this->uCore->text(array('uPage','page_templates'),$str);
    }

    public function get_custom_templates($site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            page_template_id,
            page_template_name,
            site_id,
            page_id
            FROM 
            pages_templates
            WHERE 
            site_id=:site_id AND
            (page_template_status=0 OR
            page_template_status=1)
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}
        return 0;
    }
    public function get_system_templates() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            page_template_id,
            page_template_name,
            site_id,
            page_id
            FROM 
            pages_templates
            WHERE
            language=:language AND
            page_template_status=2
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':language', $this->language,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
        return 0;
    }
    public function get_moderate_templates($site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            page_template_id,
            page_template_name,
            site_id,
            page_id,
            language
            FROM 
            pages_templates
            WHERE 
            page_template_status=1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
        return 0;
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($uCore)) $this->uCore=new \uCore();//?????????? ?????????????? ?????? IDE, ?????????? ???????????? ??????????
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");

        $this->uFunc=new uFunc($this->uCore);

        $this->language=$this->uFunc->getConf("site_lang","content",0,site_id);

        if(!isset($_POST["no_template"])) $_POST["no_template"]=0;
        if(!isset($_POST["show_use_btn"])) $_POST["show_use_btn"]=0;
        $_POST["no_template"]=(int)$_POST["no_template"];
        $_POST["show_use_btn"]=(int)$_POST["show_use_btn"];

        $this->uFunc->incCss("/uPage/css/templates.min.css");
        $this->uFunc->incJs("/uPage/js/page_templates.min.js");

        $this->uCore->uInt_js('uPage','page_templates');
    }
}
$uPage=new page_templates($this);

ob_start();?>
<div class="uPage_page_templates" id="uPage_page_templates_container">
    <h3><?=$uPage->text("Your templates header")?></h3>
    <div class="row">
    <?
    $templates_stm=$uPage->get_custom_templates();
    /** @noinspection PhpUndefinedMethodInspection */
    while($row_tmp=$templates_stm->fetch(PDO::FETCH_OBJ)) {?>
        <div class="col-md-3 template_item">
            <h4><?=$row_tmp->page_template_name?></h4>
            <div style="background-image: url('<?=u_sroot?>uPage/templates/page_templates/<?=$row_tmp->site_id?>/<?=$row_tmp->page_template_id?>/crop.jpg');" class="thumbnail template_img">
                <div class="template_controls">
                    <div class="col-md-12">
                    <a href="<?=u_sroot?>uPage/templates/page_templates/<?=$row_tmp->site_id?>/<?=$row_tmp->page_template_id?>/crop.jpg" target="_blank" class="fancybox btn btn-primary btn-outline"><?=$uPage->text("Scale in btn label")?></a>
                    </div>
                    <div class="col-md-12">
                    <a href="<?=u_sroot?>uPage/<?=$row_tmp->page_id?>" target="_blank" class="btn btn-primary btn-outline"><?=$uPage->text("View on page btn label")?></a>
                    </div>
                    <div class="col-md-12">
                    <button class="btn btn-danger btn-outline" onclick="uPage_page_templates.delete_tmp_init(<?=$row_tmp->page_template_id?>)"><?=$uPage->text("Delete btn label")?></button>
                    </div>
                    <?if($_POST["show_use_btn"]){?>
                    <div class="col-md-12">
                    <button class="btn btn-primary use_page_template" onclick="uPage_common.create_page_exec(<?=$row_tmp->page_template_id?>)"><?=$uPage->text("Use btn label")?></button>
                    </div>
                    <?}?>
                </div>
            </div>
        </div>
    <?}?>
    </div>

    <h3><?=$uPage->text("System templates header")?></h3>
    <div class="row">
    <?
    $templates_stm=$uPage->get_system_templates();
    /** @noinspection PhpUndefinedMethodInspection */
    while($row_tmp=$templates_stm->fetch(PDO::FETCH_OBJ)) {?>
        <div class="col-md-3 template_item">
            <h4><?=$row_tmp->page_template_name?></h4>
            <div style="background-image: url('<?=u_sroot?>uPage/templates/page_templates/<?=$row_tmp->site_id?>/<?=$row_tmp->page_template_id?>/crop.jpg');" class="thumbnail template_img">
                <div class="template_controls">
                    <div class="col-md-12">
                    <a href="<?=u_sroot?>uPage/templates/page_templates/8/<?=$row_tmp->page_template_id?>/crop.jpg" target="_blank" class="fancybox btn btn-primary btn-outline"><?=$uPage->text("Scale in btn label")?></a>
                    </div>
                    <div class="col-md-12">
                    <a href="<?=$uPage->uFunc->site_id2u_sroot(8)?>uPage/<?=$row_tmp->page_id?>" target="_blank" class="btn btn-primary btn-outline"><?=$uPage->text("View on page btn label")?></a>
                    </div>
                    <?if(site_id==8) {?><div class="col-md-12">
                    <button class="btn btn-danger btn-outline" onclick="uPage_page_templates.delete_tmp_init(<?=$row_tmp->page_template_id?>)"><?=$uPage->text("Delete btn label")?></button>
                    </div><?}
                    if($_POST["show_use_btn"]){?>
                    <div class="col-md-12">
                    <button class="btn btn-primary use_page_template" onclick="uPage_common.create_page_exec(<?=$row_tmp->page_template_id?>)"><?=$uPage->text("Use btn label")?></button>
                    </div>
                    <?}?>
                </div>
            </div>
        </div>
    <?}?>
    </div>

    <?if(site_id==8) {?>
    <h3><?=$uPage->text("Moderate templates header")?></h3>
    <div class="row">
    <?$templates_stm=$uPage->get_moderate_templates();

    /** @noinspection PhpUndefinedMethodInspection */
    while($row_tmp=$templates_stm->fetch(PDO::FETCH_OBJ)) {?>
        <div class="col-md-3 template_item">
            <h4><?=$row_tmp->page_template_name?></h4>
            <div style="background-image: url('<?=u_sroot?>uPage/templates/page_templates/<?=$row_tmp->site_id?>/<?=$row_tmp->page_template_id?>/crop.jpg');" class="thumbnail template_img">
                <div class="template_controls">
                    <div class="col-md-6">
                    <a href="<?=u_sroot?>uPage/templates/page_templates/<?=$row_tmp->site_id?>/<?=$row_tmp->page_template_id?>/crop.jpg" target="_blank" class="fancybox btn btn-primary btn-outline"><span class="icon-zoom-in"></span></a>
                    </div>
                    <div class="col-md-6">
                    <a href="<?=$uPage->uFunc->site_id2u_sroot($row_tmp->site_id)?>uPage/<?=$row_tmp->page_id?>" target="_blank" class="btn btn-primary btn-outline"><span class="icon-link-ext"></span></a>
                    </div>
                    <div class="col-md-12">
                    <button class="btn btn-danger btn-outline" onclick="uPage_page_templates.decline_page_template_init(<?=$row_tmp->page_template_id?>)"><?=$uPage->text("Reject in btn label")?></button>
                    </div>
                    <div class="col-md-12">
                        <select id="page_template_language_<?=$row_tmp->page_template_id?>" class="form-control">
                            <option value="en_US" <?=$row_tmp->language==="en_US"?"selected":""?>>English</option>
                            <option value="ru_RU" <?=$row_tmp->language==="ru_RU"?"selected":""?>>??????????????</option>
                        </select>
                        <div class="help-block"><?=$uPage->text("Select a template language input label")?></div>
                    </div>
                    <div class="col-md-12">
                        <button class="btn btn-success use_page_template" onclick="uPage_page_templates.apply_page_template_init(<?=$row_tmp->page_template_id?>)"><?=$uPage->text("Apply btn label")?></button>
                    </div>
                </div>
            </div>
        </div>
    <?}?>
    </div>
    <?}?>
</div>
<?$this->page_content=ob_get_contents();
ob_end_clean();

if(!$_POST["no_template"]) include "templates/template.php";
else print $this->page_content;