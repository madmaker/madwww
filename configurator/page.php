<?php
namespace configurator;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once 'uDrive/classes/common.php';
require_once "configurator/classes/common.php";

class page {
    public $pr_name;
    public $pr_price;
    public $pages_ar;
    public $cur_page_info;
    public $cur_page_id;
    public $conf;
    public $page_was_visited;
    /**
     * @var int
     */
    public $currency;
    private $configurator;
    public $pr_id;
    private $uDrive;
    private $uFunc;
    public $uSes;
    private $uCore;

    private function check_data($site_id=site_id) {
        if(!isset($this->uCore->url_prop[1])) {
            header('Location: '.u_sroot.'products');
            exit;
        }
        $this->pr_id=(int)$this->uCore->url_prop[1];

        if(!$pr_info=$this->configurator->get_pr_info($this->pr_id,"pr_name,pr_price",$site_id)) {
            header('Location: '.u_sroot.'products');
            exit;
        }
        $this->pr_name=$pr_info->pr_name;
        $this->pr_price=(int)$pr_info->pr_price;

        if(!isset($this->uCore->url_prop[2])) {
            $this->cur_page_id=(int)$this->configurator->get_first_page_id_of_product($this->pr_id,$site_id);
            $this->configurator->set_ses_pr_id($this->pr_id,$this->pr_price);
        }
        else $this->cur_page_id=(int)$this->uCore->url_prop[2];


        if($this->cur_page_info=$this->configurator->get_page_info($this->cur_page_id,"page_name,page_text,page_pos,uDrive_folder_id,must_choose_option",$site_id)) {
            $this->cur_page_info->uDrive_folder_id=$this->define_page_uDrive_folder_id($this->cur_page_id,$this->cur_page_info->page_name,$this->cur_page_info->uDrive_folder_id);
        }
    }

    public function get_sects($page_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("SELECT 
            sect_id,
            sect_name,
            sect_text,
            sect_style,
            sect_selection_type,
            sect_pos
            FROM 
            sections 
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ORDER BY
            sect_pos ASC
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}
        return 0;
    }

    public function get_opts($sect_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("SELECT 
            opt_id,
            opt_name,
            opt_price,
            opt_price_type,
            opt_img_timestamp,
            opt_pos,
            opt_style,
            opt_text,
            opt_replacements,
            opt_incompatibles,
            opt_removables,
            opt_joinables,
            opt_required,
            opt_is_default
            FROM 
            options 
            WHERE
            sect_id=:sect_id AND
            site_id=:site_id
            ORDER BY
            opt_pos ASC
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sect_id', $sect_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
        return 0;
    }

    public function define_page_uDrive_folder_id($page_id,$page_name,$cur_folder_id=0,$site_id=site_id) {
        if(!(int)$cur_folder_id) {
            if(!isset($this->uDrive)) {
                require_once "uDrive/classes/common.php";
                $this->uDrive=new \uDrive\common($this->uCore);
            }
            $uDrive_folder_id = $this->uDrive->get_module_folder_id("configurator_page");
            $page_name=trim($page_name);
            $cur_folder_id=$this->uDrive->create_folder($page_name,$uDrive_folder_id);

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("configurator")->prepare("UPDATE 
                pages
                SET
                uDrive_folder_id=:folder_id
                WHERE
                page_id=:page_id AND
                site_id=:site_id");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':folder_id', $cur_folder_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
        }
        return $cur_folder_id;
    }

    function __construct (&$uCore,$site_id=site_id) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uSes=new uSes($this->uCore);
        $this->uFunc=new uFunc($this->uCore);

        if($this->uSes->access(7)) {
            $this->uFunc->incJs("configurator/js/page_admin.js");
            $this->uDrive=new \uDrive\common($this->uCore);
        }
        $this->uFunc->incJs("configurator/js/page.js");

        $this->uFunc->incCss("configurator/css/page.min.css");
        $this->uFunc->incCss("css/breadcrumbs-and-multistep-indicator/css/style.css");

        $this->configurator=new common($this->uCore);

        $this->check_data($site_id);

        $this->uCore->page['page_width']=1;
        if(isset($this->cur_page_info->page_name)) $this->uCore->page["page_title"]=$this->cur_page_info->page_name." - ".$this->pr_name." - Конфигуратор";

        $this->conf=&$_SESSION["configurator"];
//        unset($this->conf["conf_id"]);
        if(!isset($this->conf["pr_id"])) $this->conf["pr_id"]=$this->pr_id;
        if(!isset($this->conf["opts_price"])) $this->conf["opts_price"]=0;
        if(!isset($this->conf["base_price"])) $this->conf["base_price"]=0;
        if(isset($this->conf["page_was_visited"][$this->cur_page_id])) $this->page_was_visited=1;
        else {
            $this->conf["page_was_visited"][$this->cur_page_id]=1;
            $this->page_was_visited=0;
        };

        $pages_stm=$this->configurator->get_pages_of_product($this->pr_id,"page_id,page_name,page_pos",$site_id);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->pages_ar=$pages_stm->fetchAll(PDO::FETCH_OBJ);

        $this->currency=(int)$this->uFunc->getConf("currency","configurator");
    }
}
$configurator=new page($this);
ob_start();
if($configurator->uSes->access(7)) {
    include_once "configurator/dialogs/page.php";
}
?>
<script type="text/javascript">
    if(typeof configurator==="undefined") configurator={};
    if(typeof configurator.page_admin==="undefined") configurator.page_admin={};
    if (typeof configurator.page === "undefined") configurator.page = {};
    configurator.page_admin.pr_id=<?=$configurator->pr_id?>;
    configurator.page.page_was_visited=<?=$configurator->page_was_visited?>;
    <?if($configurator->cur_page_info){?>
    configurator.page.must_choose_option =<?=(int)$configurator->cur_page_info->must_choose_option?>;
    configurator.page_admin.cur_page_id =<?=$configurator->cur_page_id?>;
    configurator.page_admin.uDrive_folder_id =<?=$configurator->cur_page_info->uDrive_folder_id?>;
    configurator.page_admin.page_pos =<?=$configurator->cur_page_info->page_pos?>;
    <?}?>
</script>
    <div class="configurator page">
        <div class="pr_info">
            <div class="container">
                <div class="row">
                    <div class="col-md-9"><h2><a href="<?=u_sroot?>configurator/products"><span class="icon-angle-double-left"></span></a> <?=$configurator->pr_name?></h2></div>
                    <div class="col-md-3">от <span id="total_price"><?=number_format($configurator->conf["base_price"]+$configurator->conf["opts_price"],0,"."," ")?></span> <?php
                        if($configurator->currency===0) {?><span class="icon-rouble"></span><?}
                        elseif($configurator->currency===1) {?><span class="icon-euro"></span><?}
                        elseif($configurator->currency===2) {?>$<?}
                        ?></div>
                </div>
            </div>
        </div>
        <div class="wizard">
        <nav class="container">
        <ol class="cd-breadcrumb triangle pages_nav cont">
        <?
        $cur_page_array_index=0;
        $pages_count=count($configurator->pages_ar);
        for($i=0; $i<$pages_count; $i++) {
            $page=$configurator->pages_ar[$i];
            $page->page_id=(int)$page->page_id;
            if($page->page_id===$configurator->cur_page_id) $cur_page_array_index=$i;
            ?>
            <li class="page <?=$page->page_id===$configurator->cur_page_id?'current':''?>" id="page_<?=$page->page_id?>" data-page_pos="<?=$page->page_pos?>">
                <a href="<?=u_sroot?>configurator/page/<?=$configurator->pr_id?>/<?=$page->page_id?>"><span class="page_name" id="page_name_<?=$page->page_id?>"><?=$page->page_name?></span></a>
            </li>
        <?}?>
            <li class="page">
                <a href="<?=u_sroot?>configurator/result"><span class="page_name">Итог</span></a>
            </li>
        </ol>
        </nav>
        </div>

            <?if($pages_count) {
                if($configurator->uSes->access(7)){?>
                    <div id="uDrive_my_drive_uploader_init"></div>
                <?}
            }
            else {?>
        <div class="container">
                <div class="jumbotron">
                    <h2>Ничего не найдено</h2>
                    <?if($configurator->uSes->access(7)){?>
                        <p>Вы можете создать страницу прямо сейчас.</p>
                        <p><button class="btn btn-primary" onclick="configurator.page_admin.new_page_init()"><span class="icon-plus"></span> Создать страницу</button></p>
                    <?}?>
                    <p><a href="<?=u_sroot?>configurator/products">Посмотреть все продукты</a></p>
                </div>
        </div>
            <?}

            if($configurator->cur_page_info) {
                include_once 'uDrive/inc/my_drive_manager.php';?>
        <div class="container">
            <div class="page_text" id="page_text"><?=$configurator->cur_page_info->page_text?></div>
        </div>

                <?$sects_stm=$configurator->get_sects($configurator->cur_page_id);
                /** @noinspection PhpUndefinedMethodInspection */
                while($sect=$sects_stm->fetch(PDO::FETCH_OBJ)) {
                    $opts_stm = $configurator->get_opts($sect->sect_id);
                    /** @noinspection PhpUndefinedMethodInspection */
                    $opts_ar = $opts_stm->fetchAll(PDO::FETCH_OBJ);
                    $opts_ar_count = count($opts_ar);
                    if ($opts_ar_count) {
                        ?>
                        <div class="sect_header">
                            <div class="container">
                                <table
                                        class="table sect"
                                        id="sect_<?= $sect->sect_id ?>"
                                        data-sect_pos="<?= $sect->sect_pos ?>">
                                    <tr>
                                        <td class="sect_name"
                                            id="sect_name_<?= $sect->sect_id ?>"><?= $sect->sect_name ?></td>
                                    </tr>
                                </table>

                                <?
                                if ($configurator->uSes->access(7)) { ?>
                                    <div class="u235_eip control_buttons">
                                    <button class="btn btn-sm btn-danger"
                                            onclick="configurator.page_admin.delete_sect_prompt(<?= $sect->sect_id ?>)">
                                        <span class="icon-cancel"></span> Удалить раздел
                                    </button>
                                    <button class="btn btn-sm btn-default"
                                            onclick="configurator.page_admin.edit_sect_pos_init(<?= $sect->sect_id ?>)">
                                        <span class="icon-sort-alt-up"></span> Поменять разделы местами
                                    </button>
                                    <button class="btn btn-sm btn-primary"
                                            onclick="configurator.page_admin.new_opt_init(<?= $sect->sect_id ?>)"><span
                                                class="icon-plus"></span> Добавить опцию
                                    </button>
                                    </div>
                                <?
                                } ?>
                            </div>
                        </div>
                        <div class="container">
                            <div class="sect_text_container">
                                <div class="sect_text"
                                     id="sect_text_<?= $sect->sect_id ?>"><?= $sect->sect_text ?></div>
                            </div>
                            <table class="table options" id="sect_options_<?= $sect->sect_id ?>">
                                <tbody>
                                <?
                                for ($opt_i=0;$opt_i<$opts_ar_count;$opt_i++) {
                                    $opt = $opts_ar[$opt_i];
                                    $opt->opt_price_type = (int)$opt->opt_price_type;
                                    $opt->opt_id = (int)$opt->opt_id;
                                    $opt->opt_style = (int)$opt->opt_style;
                                    $opt->opt_is_default = (int)$opt->opt_is_default;
                                    ?>
                                    <tr
                                            class="
                                    opt <?
                                            if ($opt->opt_is_default) echo " opt_is_default ";
                                            if (isset($_SESSION["configurator"]["option_id2key"][$opt->opt_id])) {
                                                $key = $_SESSION["configurator"]["option_id2key"][$opt->opt_id];
                                                if (isset($_SESSION["configurator"]["options"][$key])) {
                                                    if ($_SESSION["configurator"]["options"][$key]["opt_id"] == $opt->opt_id) echo " selected ";
                                                }
                                            }
                                            if ($opt->opt_replacements !== "") {
                                                echo "has_replacements";
                                                $replacements_ar = explode(" ", $opt->opt_replacements);
                                                $replacements_ar_count = count($replacements_ar);
                                                for ($rep_i = 0; $rep_i < $replacements_ar_count; $rep_i++) {
                                                    echo " ro_" . $replacements_ar[$rep_i] . " ";
                                                }
                                            }
                                            ?>"
                                            data-opt_id="<?= $opt->opt_id ?>"
                                            id="opt_<?= $opt->opt_id ?>"
                                        <?
                                        if ($configurator->uSes->access(7)) { ?>
                                            data-opt_pos="<?= $opt->opt_pos ?>"
                                            data-opt_style="<?= $opt->opt_style ?>"
                                            data-opt_price_type="<?= $opt->opt_price_type ?>"
                                            data-opt_price="<?= $opt->opt_price ?>"
                                            data-opt_is_default="<?= $opt->opt_is_default ?>"
                                            data-opt_replacements="<?= $opt->opt_replacements ?>"
                                            data-opt_incompatibles="<?= $opt->opt_incompatibles ?>"
                                            data-opt_removables="<?= $opt->opt_removables ?>"
                                            data-opt_joinables="<?= $opt->opt_joinables ?>"
                                            data-opt_required="<?= $opt->opt_required ?>"
                                        <?
                                        } ?>
                                    >
                                        <td class="opt_checkbox_container">
                                            <span class="checkbox"
                                                  onclick="configurator.page.switch_option(<?= $opt->opt_id ?>)"></span>
                                            <?
                                            if ($configurator->uSes->access(7)) { ?><span class="u235_eip">
                                                #<?= $opt->opt_id ?></span><?
                                            } ?>
                                        </td>
                                        <td class="opt_name_container" <?
                                        if ($opt->opt_style === 1) { ?>colspan="2"<?
                                        } ?>>
                                            <span class="opt_name"
                                                  id="opt_name_<?= $opt->opt_id ?>"><?= $opt->opt_name ?></span>
                                        </td>
                                        <?
                                        if ($opt->opt_style === 0) { ?>
                                            <td>
                                                <div class="opt_text"
                                                     id="opt_text_<?= $opt->opt_id ?>"><?= $opt->opt_text ?></div>
                                            </td>
                                        <?
                                        } ?>
                                        <td class="opt_price_container" id="opt_price_container_<?= $opt->opt_id ?>">
                                    <span class="opt_price" id="opt_price_<?= $opt->opt_id ?>"><?
                                        if($opt->opt_price>(int)$opt->opt_price) $price_formated = number_format($opt->opt_price, 2, ".", " ");
                                        else $price_formated = number_format($opt->opt_price, 0, ".", " ");

                                        if ($opt->opt_price_type === 0) echo "Стандартное оборудование";
                                        elseif ($opt->opt_price_type === 1) echo "Без изменения цены";
                                        elseif ($opt->opt_price_type === 2) echo "Данные о цене отсутствуют";
                                        elseif ($opt->opt_price_type === 3) echo $price_formated;
                                        elseif ($opt->opt_price_type === 4) echo "от " . $price_formated;
                                        ?></span><span class="<?php
                                            if ($opt->opt_price_type === 0 || $opt->opt_price_type === 1 || $opt->opt_price_type === 2) print ' hidden ';

                                            if($configurator->currency===0) {?>icon-rouble"><?}
                                            elseif($configurator->currency===1) {?>icon-euro"><?}
                                            elseif($configurator->currency===2) {?>">$<?}
                                            ?></span>
                                        </td>
                                    </tr>
                                    <?
                                    if ($opt->opt_style === 1) { ?>
                                        <tr id="opt_<?= $opt->opt_id ?>_opt_text_tr" class="opt_text_tr">
                                            <td></td>
                                            <td colspan="3">
                                                <div class="opt_text"
                                                     id="opt_text_<?= $opt->opt_id ?>"><?= $opt->opt_text ?></div>
                                            </td>
                                        </tr>
                                    <?
                                    } ?>
                                    <tr id="opt_<?= $opt->opt_id ?>_opt_btns_tr" class="u235_eip opt_btns_tr">
                                        <td colspan="4"><?
                                            if ($configurator->uSes->access(7)) { ?><span
                                                    class="u235_eip control_buttons">
                                                <button class="btn btn-sm btn-danger"
                                                        onclick="configurator.page_admin.delete_opt_prompt(<?= $opt->opt_id ?>)"><span
                                                            class="icon-cancel"></span> Удалить опцию</button>
                                        <button class="btn btn-sm btn-default"
                                                onclick="configurator.page_admin.edit_opt_style_init(<?= $opt->opt_id ?>)"
                                                title="Стиль отображения"><span class="icon-th-large"></span> Стиль отображения</button>
                                        <button class="btn btn-sm btn-default"
                                                onclick="configurator.page_admin.edit_opt_pos_init(<?= $opt->opt_id ?>)"><span
                                                    class="icon-sort-alt-up"></span> Поменять опции местами</button>
                                        <button class="set_opt_is_default_btn btn btn-sm <?= $opt->opt_is_default ? 'btn-primary' : 'btn-default' ?>"
                                                onclick="configurator.page_admin.set_opt_is_default(<?= $opt->opt_id ?>)"><span
                                                    class="icon-down-open"></span> Опция по умолчанию</button>
                                        <button class="btn btn-sm btn-default"
                                                onclick="configurator.page_admin.edit_opt_relations_init(<?= $opt->opt_id ?>)"><span
                                                    class="icon-cogs"></span> Взаимодействие опций</button>
                                        <button class="btn btn-sm btn-default"
                                                onclick="configurator.page_admin.edit_opt_price_type_init(<?= $opt->opt_id ?>)"><span
                                                    class="icon-table"></span> Тип цены</button>
                                                </span>
                                            <?
                                            } ?></td>
                                    </tr>
                                <?
                                } ?>
                                </tbody>
                            </table>
                        </div>
                    <?
                    }
                }
            }?>

            <div class="container next_btn">
                <div class="pull-right">
                    <h4>Стоимость конфигурации:  <span id="total_price_bottom" style="color: inherit"><?=number_format($configurator->conf["base_price"]+$configurator->conf["opts_price"],0,"."," ")?></span> <?php
                        if($configurator->currency===0) {?><span class="icon-rouble" style="color: inherit"></span><?}
                        elseif($configurator->currency===1) {?><span class="icon-euro" style="color: inherit"></span><?}
                        elseif($configurator->currency===2) {?>$<?}
                        ?></h4>
                    <div>&nbsp;</div>
            <?if(isset($configurator->pages_ar[$cur_page_array_index+1])) {?>
                <a id="continue_btn" class="btn btn-primary disabled pull-right" href="configurator/page/<?=$configurator->pr_id?>/<?=$configurator->pages_ar[$cur_page_array_index+1]->page_id?>">Далее: <?=$configurator->pages_ar[$cur_page_array_index+1]->page_name?></a>
            <?}
            else {?>
                <a href="<?=u_sroot?>configurator/result" id="continue_btn" class="btn btn-primary disabled pull-right">Далее: Итог</a>
            <?}?>
                </div>
            </div>
    </div>

    <?$this->page_content=ob_get_contents();
ob_end_clean();

include 'templates/template.php';
