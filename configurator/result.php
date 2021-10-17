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

class result{
    public $pr_name;
    public $pr_price;
    public $pages_ar;
    public $cur_page_info;
    public $cur_page_id;
    public $conf;
    public $page_was_visited;
    public $config_found;
    public $pr_text;
    public $conf_id;
    public $configurator;
    public $pr_id;
    /**
     * @var int
     */
    public $currency;
    private $uDrive;
    public $uFunc;
    public $uSes;
    public $uCore;

    private function check_data($site_id=site_id) {
        if(isset($this->uCore->url_prop[1])) {
            $this->conf_id=(int)$this->uCore->url_prop[1];
            if(!$conf_data=$this->configurator->conf_id2data($this->conf_id,"
            pr_id,
            ses_id,
            name,
            email,
            phone,
            vat_number,
            kpp,
            company_name,
            company_address
            ",$site_id)) return 0;
            $_SESSION["configurator"]=[];
            $this->conf=&$_SESSION["configurator"];
            $this->conf["options"]=[];
            $this->conf["option_id2key"]=[];
            $this->conf["pr_id"]=(int)$conf_data->pr_id;
            $this->conf["phone"]=$conf_data->phone;
            $this->conf["name"]=$conf_data->name;
            $this->conf["email"]=$conf_data->email;
            $this->conf["vat_number"]=$conf_data->vat_number;
            $this->conf["kpp"]=$conf_data->kpp;
            $this->conf["company_name"]=$conf_data->company_name;
            $this->conf["company_address"]=$conf_data->company_address;

            if($conf_options=$this->configurator->conf_id2options($this->conf_id,$site_id)) {
                /** @noinspection PhpUndefinedMethodInspection */
                while($opt=$conf_options->fetch(PDO::FETCH_OBJ)) {
                    $key=count($this->conf["options"]);
                    $this->conf["option_id2key"][$opt->opt_id]=$key;
                    $this->conf["options"][$key]["opt_id"]=(int)$opt->opt_id;
                    $opt_info=$this->configurator->get_opt_info($opt->opt_id,"
                    opt_id,
                    opt_name,
                    opt_price,
                    opt_price_type,
                    opt_img_timestamp,
                    opt_text,
                    opt_replacements
                    ",$site_id);
                    $this->conf["options"][$key]["opt_info"]=$opt_info;

                }
            }
            $ses_id=$this->uSes->get_val("ses_id");
            $conf_ses_id=(int)$conf_data->ses_id;
            if($ses_id!==$conf_ses_id) {
                unset($this->conf_id);
            }
        }

        if(!isset($_SESSION["configurator"])) {
            return 0;
        }
        $this->conf=&$_SESSION["configurator"];
        if(!isset($this->conf["pr_id"])) {
            return 0;
        }

        $this->pr_id=(int)$this->conf["pr_id"];

        if(!$pr_info=$this->configurator->get_pr_info($this->pr_id,"pr_name,pr_price,pr_text",$site_id)) {
            return 0;
        }
        $this->pr_name=$pr_info->pr_name;
        $this->pr_text=$pr_info->pr_text;
        $this->pr_price=(float)$pr_info->pr_price;

        if(!isset($this->conf_id)) {
            $selected_opts_ar=[];
            if(isset($this->conf["options"])) {
                foreach ($this->conf["options"] as $key => $value) {
                    $selected_opts_ar[] = (int)$this->conf["options"][$key]["opt_id"];
                }
            }
            $this->conf_id=$this->configurator->save_new_conf($this->pr_id,$selected_opts_ar,$site_id);
            $this->conf["phone"]="";
            $this->conf["name"]="";
            $this->conf["email"]="";
            $this->conf["vat_number"]="";
            $this->conf["kpp"]="";
            $this->conf["company_name"]="";
            $this->conf["company_address"]="";
        }

        $this->configurator->recalculate_options($this->pr_id,$site_id);



        return 1;
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

        //Подсказки ИНН
        $this->uFunc->incCSS("/js/dadata_suggestions/suggestions.min.css");
        $this->uFunc->incJs("/js/dadata_suggestions/jquery.suggestions.js");

        $this->uFunc->incCss("configurator/css/page.min.css");
        $this->uFunc->incCss("css/breadcrumbs-and-multistep-indicator/css/style.css");

        $this->configurator=new common($this->uCore);

        $this->config_found=$this->check_data($site_id);

        $this->currency=(int)$this->uFunc->getConf("currency","configurator");

        $this->uCore->page['page_width']=1;
        $this->uCore->page['page_title']=$this->pr_name.' от '.$this->pr_price;

        if ($this->currency === 0) $this->uCore->page['page_title'].=' р.';
        elseif ($this->currency === 1) $this->uCore->page['page_title'].=' Eur';
        elseif ($this->currency === 2) $this->uCore->page['page_title'].=' USD';

        $pages_stm=$this->configurator->get_pages_of_product($this->pr_id,"page_id,page_name,page_text",$site_id);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->pages_ar=$pages_stm->fetchAll(PDO::FETCH_OBJ);

        $this->uFunc->incJs("/configurator/js/result.min.js");
        $this->uFunc->incCss("/configurator/css/result.min.css");
    }
}
$configurator=new result($this);
$this->page_content="";

if($configurator->config_found) {
    $this->page_content.='
    <!--suppress BadExpressionStatementJS -->
<script type="text/javascript">
        if(typeof configurator==="undefined") configurator={};
        if(typeof configurator.result==="undefined") configurator.result={};
        
        configurator.result.conf_data=JSON.parse(\''.json_encode(array(
            'conf_id'=>$configurator->conf_id,
            'name'=>$configurator->conf['name'],
            'email'=>$configurator->conf['email'],
            'phone'=>$configurator->conf['phone']
        )).'\');
    </script>
    
    <div class="configurator page result">
        <div class="pr_info">
            <div class="container">
                <div class="row">
                    <div class="col-md-9"><h2><a href="'.u_sroot.'configurator/products"><span class="icon-angle-double-left"></span></a>'.$configurator->pr_name.'</h2></div>
                    <div class="col-md-3"><span id="total_price">'.number_format($configurator->conf["base_price"]+$configurator->conf["opts_price"],0,"."," ").'</span> ';
                        if($configurator->currency===0) $this->page_content.='<span class="icon-rouble"></span>';
                        elseif($configurator->currency===1) $this->page_content.='<span class="icon-euro"></span>';
                        elseif($configurator->currency===2) $this->page_content.='$';
    $this->page_content.='</div>
                </div>
            </div>
        </div>
        <div class="wizard">
            <nav class="container">
                <ol class="cd-breadcrumb triangle pages_nav cont">';

                    $pages_count=count($configurator->pages_ar);
                    for($i=0; $i<$pages_count; $i++) {
                        $page=$configurator->pages_ar[$i];
                        $page->page_id=(int)$page->page_id;

                        $this->page_content.='<li class="page">
                            <a href="'.u_sroot.'configurator/page/'.$configurator->pr_id.'/'.$page->page_id.'"><span class="page_name">'.$page->page_name.'</span></a>
                        </li>';
                    }
                    $this->page_content.='<li class="page current">
                        <a href="'.u_sroot.'configurator/result"><span class="page_name">Итог</span></a>
                    </li>
                </ol>
            </nav>
        </div>

        <div class="container">
            <div>'.$configurator->pr_text.'</div>
        </div>';

        for($i=0; $i<$pages_count; $i++) {
            $sects_found=0;
            $cur_page_info=$configurator->pages_ar[$i];
            $conf_page_content='<div class="container">
                <h3 class="page_name" >'.$cur_page_info->page_name.'</h3>
            </div>';

            $sects_stm=$configurator->configurator->get_sects_of_page($cur_page_info->page_id,"sect_id,
            sect_name,
            sect_text,
            sect_style,
            sect_selection_type,
            sect_pos");
            /** @noinspection PhpUndefinedMethodInspection */
            while($sect=$sects_stm->fetch(PDO::FETCH_OBJ)) {
                $sect_content='
                <div class="sect_header">
                    <div class="container">
                        <table
                                class="table sect"
                                id="sect_'.$sect->sect_id.'"
                                data-sect_pos="'.$sect->sect_pos.'">
                            <tr>
                                <td class="sect_name" id="sect_name_'.$sect->sect_id.'">'.$sect->sect_name.'</td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="container">
                    <div class="sect_text_container">
                        <div class="sect_text"  id="sect_text_'.$sect->sect_id.'">'.$sect->sect_text.'</div>
                    </div>
                    <table class="table options" id="sect_options_'.$sect->sect_id.'">
                        <tbody>';

                        $opts_found=0;
                        $opts_stm=$configurator->configurator->get_opts_of_sect($sect->sect_id,"opt_id,
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
                        opt_is_default");
                        /** @noinspection PhpUndefinedMethodInspection */
                        while($opt=$opts_stm->fetch(PDO::FETCH_OBJ)) {
                            $opt->opt_id=(int)$opt->opt_id;
                            if(isset($configurator->conf["option_id2key"][$opt->opt_id])) {
                                $opts_found=1;
                                $opt->opt_price_type=(int)$opt->opt_price_type;
                                $opt->opt_is_default=(int)$opt->opt_is_default;
                                $opt->opt_style=(int)$opt->opt_style;
                                $sect_content.='
                                <tr class="opt selected">
                                    <td class="opt_checkbox_container"><span class="checkbox"></span></td>
                                    <td class="opt_name_container" ';if($opt->opt_style===1) $sect_content.='colspan="2"';$sect_content.='><span class="opt_name" >'.$opt->opt_name.'</span></td>';
                                    if($opt->opt_style===0) $sect_content.='<td><div class="opt_text">'.$opt->opt_text.'</div></td>';
                                    $sect_content.='<td class="opt_price_container" >
                                    <span class="opt_price" >';
                                        $price_formated=number_format($opt->opt_price,0,"."," ");
                                        if($opt->opt_price_type===0) $sect_content.="Стандартное оборудование";
                                        elseif($opt->opt_price_type===1) $sect_content.= "Без изменения цены";
                                        elseif($opt->opt_price_type===2) $sect_content.="Данные о цене отсутствуют";
                                        elseif($opt->opt_price_type===3) $sect_content.=$price_formated;
                                        elseif($opt->opt_price_type===4) $sect_content.="от ".$price_formated;
                                    $sect_content.='</span> <span class="';
                                if($opt->opt_price_type===0||$opt->opt_price_type===1||$opt->opt_price_type===2) $sect_content.=' hidden ';

                        if($configurator->currency===0) $sect_content.='icon-rouble">';
                        elseif($configurator->currency===1) $sect_content.='icon-euro">';
                        elseif($configurator->currency===2) $sect_content.='">$';

                                    $sect_content.='</span>
                                    </td>
                                </tr>';
                                if($opt->opt_style===1) {
                                    $sect_content.='<tr>
                                    <td></td>
                                    <td colspan="3"><div class="opt_text">'.$opt->opt_text.'</div></td>
                                </tr>';
                                }
                            }
                        }
                $sect_content.='</tbody>
                    </table>
                </div>';
                        if($opts_found) {
                            $sects_found=1;
                            $conf_page_content.=$sect_content;
                        }
            }
            if($sects_found) $this->page_content.=$conf_page_content;
        }

    $terms_link=$terms_link_closer="";

    $terms_page_id=(int)$configurator->uFunc->getConf("privacy_terms_text_id","content");
    if($terms_page_id) {
        $txt_obj=$configurator->uFunc->getStatic_data_by_id($terms_page_id,"page_name");
        if($txt_obj) {
            $terms_link = '<a target="_blank" href="' . u_sroot . 'page/' . $txt_obj->page_name . '">';
            $terms_link_closer = "</a>";
        }
    }

    $submit_btn_txt="Получить конфигурацию";

    $this->page_content.='<div class="container next_btn">
<h3 class="pull-right">Стоимость конфигурации:  <span style="color: inherit">'.number_format($configurator->conf["base_price"]+$configurator->conf["opts_price"],0,"."," ").'</span> ';
    if($configurator->currency===0) $this->page_content.='<span class="icon-rouble" style="color: inherit"></span>';
    elseif($configurator->currency===1) $this->page_content.='<span class="icon-euro" style="color: inherit"></span>';
    elseif($configurator->currency===2) $this->page_content.='$';
    $this->page_content.='</h3>
        </div>
    </div>
    <div class="container">
        <div class="" id="configurator_result_get_configuration_container">
            <div class="highlight container-fluid">
                <h3 class=" col-md-12">Получить Конфигурацию, КП, Счет</h3>
                <div class="form-group col-md-12">
                    <label>Ваше имя</label>
                    <input type="text" class="form-control" id="configurator_result_name">
                </div>
                <div class="form-group col-md-12">
                    <label>Email</label>
                    <input type="text" class="form-control" id="configurator_result_email">
                </div>
                <div class="form-group col-md-12" id="configurator_result_phone_form_group">
                    <label>Телефон</label>
                    <input type="text" class="form-control" id="configurator_result_phone">
                    <span class="hidden help-block" id="configurator_result_phone_help_block"></span>
                </div>
                <div class=" col-md-12">
                <button id="configurator_result_get_configuration_btn" class="btn btn-primary btn-lg" onclick="configurator.result.get_config()">'.$submit_btn_txt.'</button>
                <p>&nbsp;</p>
                <p class="text-muted">'.$terms_link.$configurator->uCore->text(array('uForms','form_builder'),"privacy policy agreement notice pt1").$submit_btn_txt.$configurator->uCore->text(array('uForms','form_builder'),"privacy policy agreement notice pt2").$terms_link_closer.'</p>
                </div>
                <div class="hidden col-md-12" id="configurator_result_get_configuration_ready"></div>
            </div>
        </div>
        <div class=" hidden" id="configurator_result_get_bill_container">
            <div class="highlight container-fluid">
                <h3 class=" col-md-12">Ваши реквизиты</h3>
                <div class="form-group col-md-6" id="configurator_result_vat_number_form_group">
                    <label>ИНН</label>
                    <input type="text" class="form-control" id="configurator_result_vat_number">
                    <span class="hidden help-block" id="configurator_result_vat_number_help_block"></span>
                </div>
                <div class="form-group col-md-6">
                    <label>КПП</label>
                    <input type="text" class="form-control" id="configurator_result_kpp">
                    <span class="hidden help-block" id="configurator_result_kpp_help_block"></span>
                </div>
                <div class="form-group col-md-12" id="configurator_result_company_name_form_group">
                    <label>Название организации</label>
                    <input type="text" class="form-control" id="configurator_result_company_name">
                </div>
                <div class="form-group col-md-12" id="configurator_result_company_address_form_group">
                    <label>Юридический адрес</label>
                    <input type="text" class="form-control" id="configurator_result_company_address">
                    <span class="hidden help-block" id="configurator_result_company_address_help_block"></span>
                </div>
                <div class=" col-md-12"><button class="btn btn-primary btn-lg" id="configurator_result_get_bill_btn" onclick="configurator.result.get_bill()">Получить счет</button></div>
                <div class="hidden col-md-12" id="configurator_result_get_bill_ready"></div>
            </div>
        </div>
    </div>';
}
else {
    $this->page_content.='<div class="container">
        <div class="jumbotron">
            <h2>Ничего не найдено</h2>
            <p><a href="'.u_sroot.'configurator/products">Посмотреть все продукты</a></p>
        </div>
    </div>';
}

include 'templates/template.php';
