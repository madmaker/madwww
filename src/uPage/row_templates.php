<?php
namespace uPage\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class row_templates {
    public $language;
    private $uSes;
    public $uFunc;
    private $uCore;
    public function text($str) {
        return $this->uCore->text(array('uPage','row_templates'),$str);
    }

    public function get_custom_templates($site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            row_template_id,
            row_template_name,
            site_id,
            page_id
            FROM 
            rows_templates
            WHERE 
            site_id=:site_id AND
            (row_template_status=0 OR row_template_status=1)
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
            row_template_id,
            row_template_name,
            site_id,
            page_id
            FROM 
            rows_templates
            WHERE
            language=:language AND
            row_template_status=2
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
            row_template_id,
            row_template_name,
            site_id,
            page_id,
            language
            FROM 
            rows_templates
            WHERE 
            row_template_status=1
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
        if(!isset($uCore)) $this->uCore=new \uCore();//Чиста обманка для IDE, чтобы видела класс
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");

        $this->uFunc=new uFunc($this->uCore);

        $this->language=$this->uFunc->getConf("site_lang","content",0,site_id);

        if(!isset($_POST["no_template"])) $_POST["no_template"]=0;
        if(!isset($_POST["show_use_btn"])) $_POST["show_use_btn"]=0;
        $_POST["no_template"]=(int)$_POST["no_template"];
        $_POST["show_use_btn"]=(int)$_POST["show_use_btn"];

        $this->uFunc->incCss("/uPage/css/templates.min.css");
        $this->uFunc->incJs("/uPage/js/row_templates.min.js");

        $this->uCore->uInt_js('uPage','row_templates');
    }
}
$uPage=new row_templates($this);

ob_start();?>
<div class="uPage_row_templates" id="uPage_row_templates_container">
    <h3><?=$uPage->text("Your templates header")?></h3>
    <div class="row">
    <?
    $templates_stm=$uPage->get_custom_templates();
    /** @noinspection PhpUndefinedMethodInspection */
    while($row_tmp=$templates_stm->fetch(PDO::FETCH_OBJ)) {?>
        <div class="col-md-4 template_item">
            <h4><?=$row_tmp->row_template_name?></h4>
            <div style="background-image: url('<?=u_sroot?>uPage/templates/row_templates/<?=$row_tmp->site_id?>/<?=$row_tmp->row_template_id?>/crop.jpg');" class="thumbnail template_img">
                <div class="template_controls">
                    <div class="col-md-4">
                    <a href="<?=u_sroot?>uPage/templates/row_templates/<?=$row_tmp->site_id?>/<?=$row_tmp->row_template_id?>/crop.jpg" target="_blank" class="fancybox btn btn-primary btn-outline" title="<?=$uPage->text("Scale in btn label")?>"><span class="icon-zoom-in"></span></a>
                    </div>
                    <div class="col-md-4">
                        <a href="<?=u_sroot?>uPage/<?=$row_tmp->page_id?>" target="_blank" class="btn btn-primary btn-outline" title="<?=$uPage->text("View on page btn label")?>"><span class="icon-link-ext"></span></a>
                    </div>
                    <div class="col-md-4">
                    <button class="btn btn-danger btn-outline" onclick="uPage_row_templates.delete_tmp_init(<?=$row_tmp->row_template_id?>)" title="<?=$uPage->text("Delete btn label")?>"><span class="icon-cancel"></span></button>
                    </div>
                    <?if($_POST["show_use_btn"]){?>
                    <div class="col-md-12">
                    <button class="btn btn-primary use_row_template" onclick="uPage_setup_uPage.insert_row_save(<?=$row_tmp->row_template_id?>)"><?=$uPage->text("Use btn label")?></button>
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
        <div class="col-md-4 template_item">
            <h4><?=$row_tmp->row_template_name?></h4>
            <div style="background-image: url('<?=u_sroot?>uPage/templates/row_templates/<?=$row_tmp->site_id?>/<?=$row_tmp->row_template_id?>/crop.jpg');" class="thumbnail template_img">
                <div class="template_controls">
                    <div class="col-md-<?=site_id==8?4:6?>">
                    <a href="<?=u_sroot?>uPage/templates/row_templates/8/<?=$row_tmp->row_template_id?>/crop.jpg" target="_blank" class="fancybox btn btn-primary btn-outline" title="<?=$uPage->text("Scale in btn label")?>"><span class="icon-zoom-in"></span></a>
                    </div>
                    <div class="col-md-<?=site_id==8?4:6?>">
                    <a href="<?=$uPage->uFunc->site_id2u_sroot(8)?>uPage/<?=$row_tmp->page_id?>" target="_blank" class="btn btn-primary btn-outline" title="<?=$uPage->text("View on page btn label")?>"><span class="icon-link-ext"></span></a>
                    </div>
                    <?if(site_id==8) {?><div class="col-md-4">
                    <button class="btn btn-danger btn-outline" onclick="uPage_row_templates.delete_tmp_init(<?=$row_tmp->row_template_id?>)" title="<?=$uPage->text("Delete btn label")?>"><span class="icon-cancel"></span></button>
                    </div><?}
                    if($_POST["show_use_btn"]){?>
                    <div class="col-md-12">
                    <button class="btn btn-primary use_row_template" onclick="uPage_setup_uPage.insert_row_save(<?=$row_tmp->row_template_id?>)"><?=$uPage->text("Use btn label")?></button>
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
        <div class="col-md-4 template_item">
            <h4><?=$row_tmp->row_template_name?></h4>
            <div style="background-image: url('<?=u_sroot?>uPage/templates/row_templates/<?=$row_tmp->site_id?>/<?=$row_tmp->row_template_id?>/crop.jpg');" class="thumbnail template_img">
                <div class="template_controls">
                    <div class="col-md-4">
                    <a href="<?=u_sroot?>uPage/templates/row_templates/<?=$row_tmp->site_id?>/<?=$row_tmp->row_template_id?>/crop.jpg" target="_blank" class="fancybox btn btn-primary btn-outline" title="<?=$uPage->text("Scale in btn label")?>"><span class="icon-zoom-in"></span></a>
                    </div>
                    <div class="col-md-4">
                    <a href="<?=$uPage->uFunc->site_id2u_sroot($row_tmp->site_id)?>uPage/<?=$row_tmp->page_id?>" target="_blank" class="btn btn-primary btn-outline" title="<?=$uPage->text("View on page btn label")?>"><span class="icon-link-ext"></span></a>
                    </div>
                    <div class="col-md-4">
                    <button class="btn btn-danger btn-outline" onclick="uPage_row_templates.decline_row_template_init(<?=$row_tmp->row_template_id?>)" title="<?=$uPage->text("Reject in btn label")?>"><span class="icon-cancel"></span></button>
                    </div>
                    <div class="col-md-6">
                        <select id="row_template_language_<?=$row_tmp->row_template_id?>" class="form-control">
                            <option value="en_US" <?=$row_tmp->language==="en_US"?"selected":""?>>English</option>
                            <option value="ru_RU" <?=$row_tmp->language==="ru_RU"?"selected":""?>>Русский</option>
                        </select>
                        <div class="help-block"><?=$uPage->text("Select a template language input label")?></div>
                    </div>
                    <div class="col-md-6">
                        <button class="btn btn-success use_row_template" onclick="uPage_row_templates.apply_row_template_init(<?=$row_tmp->row_template_id?>)"><?=$uPage->text("Apply btn label")?></button>
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