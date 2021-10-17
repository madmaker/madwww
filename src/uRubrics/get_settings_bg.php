<?php
namespace uRubrics\admin;
use processors\uFunc;
use translator\translator;
use uRubrics\common;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uRubrics/classes/common.php";

class get_settings_bg {
    public $conf;
    /**
     * @var translator
     */
    public $translator;
    private $uRubrics;
    private $rubric_id;
    private $uFunc;
    private $uSes;
    private $uCore;
    private function check_data() {
        if(!isset($_POST['rubric_id'])) $this->uFunc->error(10,1);
        $this->rubric_id=(int)$_POST["rubric_id"];

        return $this->uRubrics->rubric_id2data($this->rubric_id,"
        rubric_name,
        pages_limit,
        pages_limit_on_news_list,
        display_style
        ");
    }

    public function text($str) {
        return $this->uCore->text(array('uRubrics','get_settings_bg'),$str);
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(7)) die("forbidden");
        $this->uFunc=new uFunc($this->uCore);
        $this->uRubrics=new common($this->uCore);

        require_once "translator/translator.php";
        $this->translator=new translator(site_lang,"uRubrics/get_settings_bg.php");

        if(!$this->conf=$this->check_data()) $this->uFunc->error(20,1);
        $this->conf->display_style=(int)$this->conf->display_style;
        $this->conf->pages_limit_on_news_list=(int)$this->conf->pages_limit_on_news_list;
        $this->conf->pages_limit=(int)$this->conf->pages_limit;
    }
}
$uRubrics=new get_settings_bg($this);?>
<div class="container-fluid">
    <div class="row">
        <div class="form-group col-md-12">
            <label for="uRubrics_settings_rubric_name"><?=$uRubrics->text('News name - input label')?></label>
            <input id="uRubrics_settings_rubric_name" class="form-control" type="text" value="<?=htmlspecialchars(\uString::text2sql($uRubrics->conf->rubric_name),1)?>">
        </div>
        <div class="form-group col-md-12">
            <label for="uRubrics_settings_pages_limit"><?=$uRubrics->text('News number on news page - input label')?></label>
            <select id="uRubrics_settings_pages_limit" class="form-control">
                    <option value="0" <?=$uRubrics->conf->pages_limit===0?"selected":""?>><?=$uRubrics->translator->txt("Show all")?></option>
                <?for($i=1;$i<=50;$i++) {?>
                    <option value="<?=$i?>" <?=$uRubrics->conf->pages_limit===$i?"selected":""?>><?=$i?></option>
                <?}?>
            </select>
        </div>
        <div class="form-group col-md-12">
            <label for="uRubrics_settings_pages_limit_on_news_list"><?=$uRubrics->text('News number on page with all news - input label')?></label>
            <select id="uRubrics_settings_pages_limit_on_news_list" class="form-control">
                <?for($i=1;$i<=20;$i++) {?>
                    <option value="<?=$i?>" <?=$uRubrics->conf->pages_limit_on_news_list===$i?"selected":""?>><?=$i?></option>
                <?}?>
            </select>
        </div>
        <div class="form-group col-md-12">
            <label for="uRubrics_settings_display_style"><?=$uRubrics->text('Display style of news of this type - input label')?></label>
            <select id="uRubrics_settings_display_style" class="form-control">
                <option <?=$uRubrics->conf->display_style===0?"selected":""?> value="0"><?=$uRubrics->text('News display style - list 1 col')?></option>
                <option <?=$uRubrics->conf->display_style===1?"selected":""?> value="1"><?=$uRubrics->text('News display style - list 2 col')?></option>
                <option <?=$uRubrics->conf->display_style===2?"selected":""?> value="2"><?=$uRubrics->text('News display style - list 3 col')?></option>
                <option <?=$uRubrics->conf->display_style===3?"selected":""?> value="3"><?=$uRubrics->text('News display style - list 4 col')?></option>
                <option <?=$uRubrics->conf->display_style===4?"selected":""?> value="4"><?=$uRubrics->text('News display style - tiles')?></option>
            </select>
        </div>
    </div>
</div>
