<?php
namespace uConf;
use PDO;
use PDOException;
use processors\uFunc;
use translator\translator;
use uCore;
use uSes;
use uString;

require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';
require_once 'translator/translator.php';

class settings_admin {
    public $uFunc;
    public $uSes;
    /**
     * @var translator
     */
    public $translator;
    private $uCore,$mod;
    public $q_settings,$q_tabs;

    private function go_home() {
        header('Location: '.u_sroot);
        exit;
    }

    private function get_tabs() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->q_tabs=$this->uFunc->pdo('pages')->prepare('SELECT tab_id, tab_name FROM u235_conf_tabs WHERE conf_mod=:conf_mod');

            /** @noinspection PhpUndefinedMethodInspection */$this->q_tabs->bindParam(':conf_mod', $this->mod,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$this->q_tabs->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}
    }
    private function get_settings() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->q_settings=$this->uFunc->pdo('pages')->prepare('SELECT
            field_id,
            value,
            field,
            field_type,
            min_length,
            max_length,
            tab_id
            FROM
            u235_conf
            WHERE
            `mod`=:mod AND
            site_id=:site_id
            ORDER BY
            pos');
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$this->q_settings->bindParam(':mod', $this->mod,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$this->q_settings->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$this->q_settings->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
    }
    public function __construct(&$uCore) {
        /** @noinspection UnusedConstructorDependenciesInspection */
        $this->uCore =&$uCore;
        if(!isset($this->uCore)) {
            /** @noinspection UnusedConstructorDependenciesInspection */
            $this->uCore = new uCore();
        }

        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        $this->translator=new translator(site_lang,'uConf/settings_admin.php');

        $this->uCore->uInt_js('uConf','settings_admin');

        $this->uCore->page['page_title']=$this->translator->txt('Page name'/*Настройки сайта*/);

        if($_GET['mod'] === 'uSup') {
            $this->mod=$_GET['mod'];
            $this->uCore->page['page_title']=$this->translator->txt('uSup settings - page title'/*Настройки системы технической поддержки*/);
            if(!$this->uSes->access(200)) {
                $this->go_home();
            }
        }
        elseif($_GET['mod'] === 'content') {
            $this->mod=$_GET['mod'];
            $this->uCore->page['page_title']=$this->translator->txt('Content settings - pg title'/*Настройки контента*/);
            if(!$this->uSes->access(100)) {
                $this->go_home();
            }
        }
        elseif($_GET['mod'] === 'configurator') {
            $this->mod=$_GET['mod'];
            $this->uCore->page['page_title']=$this->translator->txt('Configurator settings - pg title'/*Настройки конфигуратора*/);
            if(!$this->uSes->access(7)) {
                $this->go_home();
            }
        }
        elseif($_GET['mod'] === 'uCat') {
            $this->mod=$_GET['mod'];
            $this->uCore->page['page_title']=$this->translator->txt('uCat settings'/*Настройки каталога*/);
            if(!$this->uSes->access(25)) {
                $this->go_home();
            }
        }
        elseif($_GET['mod'] === 'uViblog') {
            $this->mod=$_GET['mod'];
            $this->uCore->page['page_title']=$this->translator->txt('uViblog settings'/*Настройки Видеоленты*/);
            if(!$this->uSes->access(4)) {
                $this->go_home();
            }
        }
        elseif($_GET['mod'] === 'uPeople') {
            $this->mod=$_GET['mod'];
            $this->uCore->page['page_title']=$this->translator->txt('uPeople settings - pg title'/*Настройки модуля "Наши люди"*/);
            if(!$this->uSes->access(10)) {
                $this->go_home();
            }
        }
        elseif($_GET['mod'] === 'uKnowbase') {
            $this->mod=$_GET['mod'];
            $this->uCore->page['page_title']=$this->translator->txt('uKnowbase settings - pg title'/*Настройки Базы знаний*/);
            if(!$this->uSes->access(200)) {
                $this->go_home();
            }
        }
        elseif($_GET['mod'] === 'uEvents') {
            $this->mod=$_GET['mod'];
            $this->uCore->page['page_title']=$this->translator->txt('uEvents settings'/*Настройки Событий*/);
            if(!$this->uSes->access(300)) {
                $this->go_home();
            }
        }
        else {
            $this->go_home();
        }

        $this->get_settings();
        $this->get_tabs();
    }
}

$uConf = new settings_admin($this);
$this->uFunc->incJs(u_sroot.'js/phpjs/functions/strings/str_replace.min.js',0);
$this->uFunc->incJs(u_sroot.'uConf/js/settings_admin.min.js',0);
$this->uFunc->incJs(u_sroot.'js/phpjs/functions/strings/nl2br.min.js',0);
$this->uFunc->incJs(u_sroot.'js/phpjs/functions/strings/htmlspecialchars.min.js',0);
$this->uFunc->incJs(u_sroot.'js/phpjs/functions/strings/htmlspecialchars_decode.min.js',0);
ob_start();?>

    <!--suppress HtmlFormInputWithoutLabel -->
    <h1><?=$this->page['page_title']?></h1>

    <div role="tabpanel">

        <!-- Nav tabs -->
        <ul class="nav nav-tabs" role="tablist">
            <?$first=true;
            /** @noinspection PhpUndefinedMethodInspection */
            while($tab=$uConf->q_tabs->fetch(PDO::FETCH_OBJ)) {?>
                <li role="presentation" class="<?=$first?'active':''?>"><a href="#uConf_settings_<?=$tab->tab_id?>" aria-controls="uConf_settings_<?=$tab->tab_id?>" role="tab" data-toggle="tab"><?=$uConf->translator->txt($tab->tab_name)?></a></li>
            <?$first=false;
            }?>
        </ul>

        <!-- Tab panes -->
        <div class="tab-content">
            <?php
            $first=true;
            /** @noinspection PhpUndefinedMethodInspection */
            $uConf->q_tabs->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            while($tab=$uConf->q_tabs->fetch(PDO::FETCH_OBJ)) {?>
            <div role="tabpanel" class="tab-pane <?=$first?'active':''?>" id="uConf_settings_<?=$tab->tab_id?>">
                <table class="table table-striped table-condensed table-hover">
                    <?/** @noinspection PhpUndefinedMethodInspection */
                    while($set=$uConf->q_settings->fetch(PDO::FETCH_OBJ)) {
                        if($set->tab_id==$tab->tab_id) {?>
                            <tr onclick="uConf_settings_admin.edit_val(<?=$set->field_id?>,<?=$set->field_type?>,<?=$set->min_length?>,<?=$set->max_length?>)">
                                <td id="uConf_settings_label_<?=$set->field_id?>">
                                    <?if($set->field_type=='13') {?><h3><?=$uConf->translator->txt($set->field)?></h3><?}
                                    else { echo $uConf->translator->txt($set->field);}?>
                                </td>
                                <td id="uConf_settings_val_<?=$set->field_id?>"><?php
                                    if($set->field_type=='1') {
                                        echo htmlspecialchars(uString::sql2text($set->value, 1));
                                    }
                                    elseif($set->field_type=='2'||$set->field_type=='3'||$set->field_type=='8'||$set->field_type=='10'||$set->field_type=='11') {
                                        echo $set->value;
                                    }
                                    elseif($set->field_type=='4') {?>
                                        <input type="checkbox" id="uConf_settings_editor_field_val_<?=$set->field_id?>" <?=$set->value==='1'?'checked':''?>>
                                        <script type="text/javascript">
                                            $(document).ready(function() {
                                                $("#uConf_settings_editor_field_val_<?=$set->field_id?>").bootstrapSwitch({
                                                    size:'small',
                                                onText:'I',
                                                offText:'0'
                                                }).on('switchChange.bootstrapSwitch', function() {
                                                    uConf_settings_admin.save_field(<?=$set->field_id?>,<?=$set->field_type?>)
                                                });
                                            });
                                        </script>
                                    <?}
                                    elseif($set->field_type=='5') {
                                        echo '*********';
                                    }
                                    elseif($set->field_type=='6') {
                                        $val=explode(' ',trim(uString::sql2text($set->value)));
                                        for($i=0, $iMax = count($val); $i< $iMax; $i++) {
                                            echo $val[$i].'<br>';
                                        }
                                    }
                                    elseif($set->field_type=='7') {
                                        $val=explode(',',trim(uString::sql2text($set->value,1)));
                                        for($i=0, $iMax = count($val); $i< $iMax; $i++) {
                                            echo $val[$i].'<br>';
                                        }
                                    }
                                    elseif($set->field_type=='9') echo nl2br(htmlspecialchars(uString::sql2text($set->value,1)));
                                    elseif($set->field_type=='12') {//selectbox
                                        //get values
                                        try {
                                            /** @noinspection PhpUndefinedMethodInspection */
                                            $stm=$uConf->uFunc->pdo('pages')->prepare('SELECT
                                            option_label,option_val,option_id
                                            FROM
                                            u235_conf_selectbox
                                            WHERE
                                            field_id=:field_id
                                            ORDER BY
                                            option_id
                                            ');
                                            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_id', $set->field_id,PDO::PARAM_INT);
                                            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                                        }
                                        catch(PDOException $e) {$uConf->uFunc->error('30'/*.$e->getMessage()*/);}
?>
                                        <select class="form-control" id="uConf_settings_editor_field_val_<?=$set->field_id?>" onchange="uConf_settings_admin.save_field(<?=$set->field_id?>,<?=$set->field_type?>)">
                                            <?php /** @noinspection PhpUndefinedVariableInspection, PhpUndefinedMethodInspection */
                                        while($option=$stm->fetch(PDO::FETCH_OBJ)) {?>
                                            <option value="<?=$option->option_id?>" <?=$set->value==$option->option_val?'selected':''?>><?=$option->option_label?></option>
                                        <?}?>
                                        </select>
                                    <?}
                                    elseif($set->field_type=='14') {
                                        echo htmlspecialchars($set->value);
                                    }
                                    ?></td>
                            </tr>
                        <?}
                    }
                    /** @noinspection PhpUndefinedMethodInspection */
                    $uConf->q_settings->execute();?>
                </table>
            </div>
            <?$first=false;
            }?>
        </div>
    </div>
    <?php
include_once 'inc/settings_admin_dialogs.php';
$this->page_content = ob_get_clean();

include 'templates/u235/template.php';
