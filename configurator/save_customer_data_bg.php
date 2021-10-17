<?php
namespace configurator;
use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";
require_once "configurator/classes/common.php";
require_once "uPage/inc/common.php";

class save_customer_data_bg {
    /**
     * @var string
     */
    private $addInfo;
    /**
     * @var string
     */
    private $phone;
    /**
     * @var string
     */
    private $email;
    /**
     * @var int
     */
    private $currency;
    private $company_address;
    private $company_name;
    private $vat_number;
    private $kpp;
    private $uPage;
    private $pr_text;
    private $conf;
    private $pr_name;
    private $pr_id;
    private $timestamp;
    private $conf_hash;
    private $configurator;
    private $conf_id;
    private $uFunc;
    private $uCore;

    private function check_data($site_id=site_id) {
        if(!isset($_POST["conf_id"])) $this->uFunc->error(10,1);
        $this->conf_id=(int)$_POST["conf_id"];

        if(!$conf_data=$this->configurator->conf_id2data($this->conf_id,"
        conf_id,
        timestamp,
        pr_id,
        kpp,
        vat_number,
        company_name,
        company_address
        ",$site_id)) $this->uFunc->error(20,1);
        $this->timestamp=(int)$conf_data->timestamp;
        $this->pr_id=(int)$conf_data->pr_id;
        $this->kpp=$conf_data->kpp;
        $this->vat_number=$conf_data->vat_number;
        $this->company_name=$conf_data->company_name;
        $this->company_address=$conf_data->company_address;

        if(!$pr_data=$this->configurator->get_pr_info($this->pr_id,"pr_name,pr_text",$site_id)) $this->uFunc->error(30,1);
        $this->pr_name=$pr_data->pr_name;
        $this->pr_text=$pr_data->pr_text;

        $this->conf=&$_SESSION["configurator"];
    }

    private function create_pdf($site_id=site_id) {
        $conf_dir="configurator/configurations_pdf/".$site_id."/".$this->conf_id;
        $dir=$conf_dir."/".$this->conf_hash."/".$this->timestamp;
        $file=$dir."/conf_".$this->conf_id.".pdf";

        $pages_stm=$this->configurator->get_pages_of_product($this->pr_id,"page_id,page_name,page_text",$site_id);
        /** @noinspection PhpUndefinedMethodInspection */
        $pages_ar=$pages_stm->fetchAll(PDO::FETCH_OBJ);
        $pages_count=count($pages_ar);

        if(!$company_bank_details=$this->uFunc->get_company_bank_details($site_id)) return false;

        //ООО НАУЧНО-ПРОИЗВОДСТВЕННОЕ ОБЪЕДИНЕНИЕ "СТАРТ-ПРОЛЕТАРСКИЙ ТРУД"
        //г Санкт-Петербург, Московский р-н, ул Цветочная, д 25 литер е

        $page_content='    
        <div class="configurator page result">
            <div class="pr_info">
                <div class="container">
                    <div><h1>'.$this->pr_name.'</h1></div>
                    <div><span>Прямая ссылка.: <a href="'.u_sroot.'configurator/result/'.$this->conf_id.'">'.u_sroot.'configurator/result/'.$this->conf_id.'</a></span></div>
                    
                    <div class="company_info">
                    <h4>'.$company_bank_details->company_name.'</h4>
                    <p>'.$company_bank_details->company_address.'</p>
                    <p>Email: '.$this->email.'</p>
                    <p>Телефон: '.$this->phone.'</p>
                    </div>
                </div>
            </div>
    
            <div class="container">
                <div>'.$this->pr_text.'</div>
            </div>';

                for($i=0; $i<$pages_count; $i++) {
                    $sects_found=0;
                    $cur_page_info=$pages_ar[$i];
                    $conf_page_content='<div class="container">
                    <h4 class="page_name" >'.$cur_page_info->page_name.'</h4>
                </div>';

                    $sects_stm=$this->configurator->get_sects_of_page($cur_page_info->page_id,"sect_id,
                sect_name,
                sect_text,
                sect_style,
                sect_selection_type,
                sect_pos");
                    /** @noinspection PhpUndefinedMethodInspection */
                    while($sect=$sects_stm->fetch(PDO::FETCH_OBJ)) {
                        $sect_content='
                    <div>
                        <div class="container">
                            <table class="table">
                                <tr>
                                    <td class="sect_name"><h5>'.$sect->sect_name.'</h5></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="container">
                        <div class="sect_text_container">
                            <div class="sect_text">'.$sect->sect_text.'</div>
                        </div>
                        <table class="table">';

                        $opts_found=0;
                        $opts_stm=$this->configurator->get_opts_of_sect($sect->sect_id,"opt_id,
                            opt_name,
                            opt_price,
                            opt_price_type,
                            opt_img_timestamp,
                            opt_pos,
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
                            if(isset($this->conf["option_id2key"][$opt->opt_id])) {
                                $opts_found=1;
                                $opt->opt_price_type=(int)$opt->opt_price_type;
                                $opt->opt_is_default=(int)$opt->opt_is_default;
                                $sect_content.='
                                    <tr class="opt selected">
                                        <td class="opt_name_container"><span class="opt_name" ><b>'.$opt->opt_name.'</b></span></td>
                                        <td class="opt_price_container">
                                        <span class="opt_price">';
                                $price_formated=number_format($opt->opt_price,0,"."," ");
                                if($opt->opt_price_type===0) $sect_content.="Стандартное оборудование";
                                elseif($opt->opt_price_type===1) $sect_content.= "Без изменения цены";
                                elseif($opt->opt_price_type===2) $sect_content.="Данные о цене отсутствуют";
                                elseif($opt->opt_price_type===3) $sect_content.=$price_formated;
                                elseif($opt->opt_price_type===4) $sect_content.="от ".$price_formated;
                                $sect_content.='</span> ';
                                if($opt->opt_price_type===3||$opt->opt_price_type===4) {
                                    if($this->currency===0) $sect_content.='р.';
                                    elseif($this->currency===1) $sect_content.='&euro;';
                                    elseif($this->currency===2) $sect_content.='$';
                                }
                                $sect_content.='
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><div class="opt_text">'.$opt->opt_text.'</div></td>
                                    </tr>';
                            }
                        }
                        $sect_content.='</table>
                    </div>';
                        if($opts_found) {
                            $sects_found=1;
                            $conf_page_content.=$sect_content;
                        }
                    }
                    if($sects_found) $page_content.=$conf_page_content;
                }
        $page_content.='
    <div><h3 style="text-align: right">Стоимость: '.number_format($this->conf["base_price"]+$this->conf["opts_price"],0,"."," ").' ';
        if($this->currency===0) $page_content.='р.';
        elseif($this->currency===1) $page_content.='&euro;';
        elseif($this->currency===2) $page_content.='$';
        $page_content.='</h3></div>  
    <div>'.$this->addInfo.'</div>

        </div>';

        ob_start();

        echo $page_content;

        $html=ob_get_contents();
        ob_end_clean();

        $this->uFunc->rmdir($conf_dir);
        if(!file_exists($dir)) mkdir($dir,0755,true);
        if(!$this->uFunc->create_empty_index($dir)) $this->uFunc->error(40,1);

        require_once "lib/MPDF/mpdf.php";
        $mpdf=new \mPDF('utf-8'/*, 'A4-L'*/);
        $stylesheet = file_get_contents('lib/MPDF/mpdf.css');


        //DIN Pro normal
        if(site_id==2||site_id==4||site_id==6||site_id==13||site_id==18||site_id==35||site_id==36||site_id==37||site_id==39||site_id==40||site_id==41||site_id==42||site_id==43||site_id==44||site_id==45||site_id==46||site_id==47||site_id==48||site_id==49||site_id==50||site_id==51||site_id==52||site_id==53||site_id==54||site_id==55||site_id==56||site_id==58) $stylesheet.= file_get_contents('fonts/DINPro/DIN Pro normal.min.css');
        //DIN PRO LIGHT
        if(site_id==4||site_id==13||site_id==35||site_id==36||site_id==37||site_id==40||site_id==41||site_id==42||site_id==43||site_id==44||site_id==46||site_id==47) $stylesheet.= file_get_contents('fonts/DINPro/DIN Pro Light.min.css');
        //DIN PRO Cond
        if(site_id==35||site_id==36||site_id==37||site_id==40||site_id==41||site_id==42||site_id==43||site_id==44||site_id==46) $stylesheet.= file_get_contents('fonts/DINPro/DIN Pro Cond.min.css');
        //DIN PRO Black
        if(site_id==13||site_id==35||site_id==36||site_id==37||site_id==40||site_id==41||site_id==42||site_id==43||site_id==44||site_id==46) $stylesheet.= file_get_contents('fonts/DINPro/DIN Pro Black.min.css');
        //DIN PRO MEDIUM
        if(site_id==2||site_id==6||site_id==13||site_id==18||site_id==35||site_id==36||site_id==37||site_id==40||site_id==41||site_id==42||site_id==43||site_id==44||site_id==46||site_id==47||site_id==51||site_id==52||site_id==53||site_id==54||site_id==55||site_id==56||site_id==58) $stylesheet.= file_get_contents('fonts/DINPro/DIN Pro Medium.min.css');
        //DIN PRO BOLD
        if(site_id==13||site_id==35||site_id==36||site_id==37||site_id==39||site_id==40||site_id==41||site_id==42||site_id==43||site_id==44||site_id==46||site_id==51||site_id==52||site_id==53||site_id==54||site_id==55||site_id==56) $stylesheet.= file_get_contents('fonts/DINPro/DIN Pro Bold.min.css');
        //ARIMO
        if(site_id==3||site_id==8) $stylesheet.= file_get_contents('fonts/Arimo/stylesheet.min.css');
        //FUTURA LIGHT
        if(site_id==6||site_id==18) $stylesheet.= file_get_contents('fonts/Futura/Futura-Light-Normal.min.css');
        //BookmanOldStyle
        if(site_id==54) $stylesheet.= file_get_contents('fonts/BookmanOldStyle/stylesheet.min.css');
        //Century Gothic
        if(site_id==12) $stylesheet.= file_get_contents('fonts/Century Gothic/stylesheet.min.css');
        //Rupster Script
        if(site_id==12) $stylesheet.= file_get_contents('fonts/Rupster Script/stylesheet.min.css');
        //Rupster Script
        if(site_id==17||site_id==31) $stylesheet.= file_get_contents('fonts/DIN/stylesheet.min.css');
        //ChinaCyr
        if(site_id==24) $stylesheet.= file_get_contents('fonts/ChinaCyr/stylesheet.min.css');
        //Raleway-Thin
        if(site_id==33||site_id==34) $stylesheet.= file_get_contents('fonts/Raleway/Raleway-Thin.min.css');
        //Raleway-Light
        if(site_id==33||site_id==34) $stylesheet.= file_get_contents('fonts/Raleway/Raleway-Light.min.css');
        //Raleway-Bold
        if(site_id==33||site_id==34) $stylesheet.= file_get_contents('fonts/Raleway/Raleway-Bold.min.css');
        //Raleway-Regular
        if(site_id==33||site_id==34||site_id==55) $stylesheet.= file_get_contents('fonts/Raleway/Raleway-Regular.min.css');
        //Raleway-Medium
        if(site_id==33||site_id==34||site_id==55) $stylesheet.= file_get_contents('fonts/Raleway/Raleway-Medium.min.css');
        //Raleway-Black
        if(site_id==33||site_id==34) $stylesheet.= file_get_contents('fonts/Raleway/Raleway-Black.min.css');
        //GillSans
        if(site_id==36||site_id==46) $stylesheet.= file_get_contents('fonts/GillSans/stylesheet.min.css');
        //LifeIsStrangeRU
        if(site_id==44) $stylesheet.= file_get_contents('fonts/LifeIsStrangeRU/stylesheet.min.css');

        //Bootstrap
        $stylesheet.= file_get_contents('js/bootstrap/css/bootstrap.min.css');

        $stylesheet.= file_get_contents(staticcontent_url.'css/lib/u235/common.min.css');
        $stylesheet.= file_get_contents('configurator/css/page.min.css');
        $stylesheet.= file_get_contents('configurator/css/result.min.css');

        $stylesheet .= file_get_contents($this->uPage->get_site_css_file());


        @$mpdf->WriteHTML($stylesheet,1);
        @$mpdf->WriteHTML($html,2);
        @$mpdf->Output($file,"F");
    }
    private function create_bill($site_id=site_id) {
        $items_ar=array(
            array($this->pr_name." - Конфигурация ".$this->conf_id,1,"",$this->conf["base_price"]+$this->conf["opts_price"])
        );

        $customer_info=array(
            $this->company_name,/*Наименование компании*/
            $this->vat_number,/*ИНН*/
            ((int)$this->kpp?$this->kpp:""),/*КПП. Пустая строка, если нет*/
            $this->company_address/*Юридический адрес. 633010, Новосибирская обл, г Бердск, ул Ленина, д 94, оф 3*/
        );

        if($this->currency===0) $currency="RUR";
        elseif($this->currency===1) $currency="EUR";
        elseif($this->currency===2) $currency="USD";

        return $bill_number=$this->uFunc->create_bill($items_ar,$customer_info,"",$currency,$site_id);
    }

    function save_customer_data($site_id=site_id) {
        if(!isset($_POST["phone"])) {
            die(json_encode(array(
                "status"=>"error",
                "msg"=>"phone is wrong"
            )));
        }
        $phone=trim($_POST["phone"]);
        if(!\uString::isPhone($phone)) {
            die(json_encode(array(
                "status"=>"error",
                "msg"=>"phone is wrong"
            )));
        }

        $email=$name="";
        if(isset($_POST["email"])) $email=trim($_POST["email"]);
        if(isset($_POST["name"])) $name=trim($_POST["name"]);

        $this->conf_hash=$this->uFunc->genHash();

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("UPDATE 
            configurations
            SET  
            name=:name, 
            email=:email, 
            phone=:phone,
            conf_hash=:conf_hash
            WHERE
            conf_id=:conf_id AND 
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':name', $name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':email', $email,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':phone', $phone,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':conf_hash', $this->conf_hash,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':conf_id', $this->conf_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/,1);}

        $this->create_pdf($site_id);

        //Notify Manager

        $msg="Посетитель собрал конфигурацию *".$this->pr_name."*.\n\n*Контактные данные:*\nИмя - ".$name."\nEmail - ".$email."\nТелефон - ".$phone." .\n\nПосмотреть конфигурацию можно по ссылке:\n".u_sroot.'configurator/result/'.$this->conf_id;
        $html='<h3>Посетитель собрал конфигурацию '.$this->pr_name.'</h3>
        <h4>Контактные данные:</h4>
        <p><b>Имя</b> - '.$name.'</p>
        <p><b>Email</b> - '.$email.'</p>
        <p><b>Телефон</b> - '.$phone.'</p>
        <p>&nbsp;</p>
        <p>Посмотреть конфигурацию можно по ссылке:<br>
        <a href="'.u_sroot.'configurator/result/'.$this->conf_id.'">'.u_sroot.'configurator/result/'.$this->conf_id.'</a></p>';
        $this->uFunc->slack($msg);

        $notification_email=$this->uFunc->getConf("invoice_emails","content",1);
        $emails_ar=explode(',',$notification_email);
        foreach ($emails_ar as $iValue) {
            $manager_email = $iValue;
            if (uString::isEmail($manager_email)) {
                $this->uFunc->sendMail($html, "Собрана новая конфигурация", $manager_email);
            }
        }


        //Notify Customer


        $html='<h3>'.$name.', Ваша конфигурация '.$this->pr_name.' готова</h3>
        <p>Посмотреть конфигурацию можно на сайте:<br>
        <a href="'.u_sroot.'configurator/result/'.$this->conf_id.'">'.u_sroot.'configurator/result/'.$this->conf_id.'</a></p>
        <p>Или скачайте ее в PDF по ссылке:<br>
        <a href="'.u_sroot.'configurator/configurations_pdf/'.site_id.'/'.$this->conf_id.'/'.$this->conf_hash.'/'.$this->timestamp.'/conf_'.$this->conf_id.'.pdf">'.u_sroot.'configurator/configurations_pdf/'.site_id.'/'.$this->conf_id.'/'.$this->conf_hash.'/'.$this->timestamp.'/conf_'.$this->conf_id.'.pdf</a></p>';

        if (uString::isEmail($email)) $this->uFunc->sendMail($html, "Ваша конфигурация готова", $email);

        echo json_encode(array(
            "status"=>"done",
            "conf_hash"=>$this->conf_hash,
            "timestamp"=>$this->timestamp
        ));
    }
    function save_company_data($site_id=site_id) {
//        print $this->vat_number;
        if(!isset($_POST["vat_number"])) {
            die(json_encode(array(
                "status"=>"error",
                "msg"=>"vat number is wrong"
            )));
        }
        $this->vat_number=trim($_POST["vat_number"]);
        $vat_number_length=strlen($this->vat_number);
        if(($vat_number_length!==10&&$vat_number_length!==12)||!\uString::isDigits($this->vat_number)) {
            die(json_encode(array(
                "status"=>"error",
                "msg"=>"vat number is wrong"
            )));
        }


        if(!isset($_POST["company_name"])) {
            die(json_encode(array(
                "status"=>"error",
                "msg"=>"company name is wrong"
            )));
        }
        $this->company_name=trim($_POST["company_name"]);
        $company_name_length=strlen($this->company_name);
        if($company_name_length<4) {
            die(json_encode(array(
                "status"=>"error",
                "msg"=>"company name is wrong"
            )));
        }

        if(!isset($_POST["company_address"])) {
            die(json_encode(array(
                "status"=>"error",
                "msg"=>"company address is wrong"
            )));
        }
        $this->company_address=trim($_POST["company_address"]);
        $company_address_length=strlen($this->company_address);
        if($company_address_length<4) {
            die(json_encode(array(
                "status"=>"error",
                "msg"=>"company address is wrong"
            )));
        }

        $this->kpp="";
        if(isset($_POST["kpp"])) $this->kpp=trim($_POST["kpp"]);

        $bill_number=$this->create_bill($site_id);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("UPDATE 
            configurations
            SET  
            vat_number=:vat_number,
            kpp=:kpp,
            company_name=:company_name,
            company_address=:company_address,
            bill_number=:bill_number
            WHERE
            conf_id=:conf_id AND 
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':vat_number', $this->vat_number,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':kpp', $this->kpp,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':company_name', $this->company_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':company_address', $this->company_address,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':bill_number', $bill_number,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':conf_id', $this->conf_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('60'.$e->getMessage(),1);}



        $bill_file_path=u_sroot.$this->uFunc->bill_number2file_path($bill_number);


        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("SELECT
            name, 
            email, 
            phone,
            conf_hash,
            timestamp
            FROM 
            configurations
            WHERE
            conf_id=:conf_id AND 
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':conf_id', $this->conf_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/,1);}

        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        if($qr=$stm->fetch(PDO::FETCH_OBJ)) {

            $name=$qr->name;
            $email=$qr->email;
            $phone=$qr->phone;
            $conf_hash=$qr->conf_hash;
            $timestamp=$qr->timestamp;

            //Notify Manager

            $msg = "Посетитель запросил счет на *" . $this->pr_name . "*.\n\n*Контактные данные:*\nИмя - " . $name . "\nEmail - " . $email . "\nТелефон - " . $phone . " .\n\nПосмотреть конфигурацию можно по ссылке:\n" . u_sroot . 'configurator/result/' . $this->conf_id;
            $html = '<h3>Посетитель запросил счет на ' . $this->pr_name . '</h3>
        <h4>Контактные данные:</h4>
        <p><b>Имя</b> - ' . $name . '</p>
        <p><b>Email</b> - ' . $email . '</p>
        <p><b>Телефон</b> - ' . $phone . '</p>
        <p>&nbsp;</p>
        <p>Посмотреть счет можно по ссылке:<br>
        <a href="' . $bill_file_path . '">' . $bill_file_path . '</a></p>

        <p>Посмотреть саму конфигурацию можно по ссылке:<br>
        <a href="' . u_sroot . 'configurator/result/' . $this->conf_id . '">' . u_sroot . 'configurator/result/' . $this->conf_id . '</a></p>';
            $this->uFunc->slack($msg);

            $notification_email = $this->uFunc->getConf("invoice_emails", "content", 1);
            $emails_ar = explode(',', $notification_email);
            foreach ($emails_ar as $iValue) {
                $manager_email = $iValue;
                if (uString::isEmail($manager_email)) {
                    $this->uFunc->sendMail($html, "Запрошен счет", $manager_email);
                }
            }


            //Notify Customer


            $html = '<h3>' . $name . ', Ваш счет на ' . $this->pr_name . ' готов</h3>
        <p>Скачать счет можно по ссылке:<br>
        <a href="' . $bill_file_path . '">' . $bill_file_path . '</a></p>
        <p></p>
        <p>Посмотреть конфигурацию можно на сайте:<br>
        <a href="' . u_sroot . 'configurator/result/' . $this->conf_id . '">' . u_sroot . 'configurator/result/' . $this->conf_id . '</a></p>
        <p>Или скачайте ее в PDF по ссылке:<br>
        <a href="' . u_sroot . 'configurator/configurations_pdf/' . site_id . '/' . $this->conf_id . '/' . $conf_hash . '/' . $timestamp . '/conf_' . $this->conf_id . '.pdf">' . u_sroot . 'configurator/configurations_pdf/' . site_id . '/' . $this->conf_id . '/' . $conf_hash . '/' . $timestamp . '/conf_' . $this->conf_id . '.pdf</a></p>';

            if (uString::isEmail($email)) $this->uFunc->sendMail($html, "Ваш счет готов", $email);

        }


        echo json_encode(array(
            "status"=>"done",
            "bill_file_path"=>$bill_file_path
        ));
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uFunc=new uFunc($this->uCore);
        $this->uPage=new \uPage\common($this->uCore);
        $this->configurator=new common($this->uCore);

        $this->check_data();

        $this->currency=(int)$this->uFunc->getConf("currency","configurator");
        $this->email=$this->uFunc->getConf('configurator conf email','configurator');
        $this->phone=$this->uFunc->getConf('configurator conf phone','configurator');
        $this->addInfo=$this->uFunc->getConf('configurator conf add info','configurator');

        if(isset($_POST["phone"])) $this->save_customer_data();
        elseif(isset($_POST["vat_number"])) $this->save_company_data();
        else $this->uFunc->error(70,1);
    }
}
new save_customer_data_bg($this);
XHRPOSThttp://dulevo.pro.local/configurator/save_customer_data_bg


//vat_number=532007714177&
//kpp=&
//company_name=%D0%98%D0%9F+%D0%9B%D0%B0%D0%BF%D1%88%D0%B8%D0%BD+%D0%9D%D0%B8%D0%BA%D0%BE%D0%BB%D0%B0%D0%B9+%D0%92%D0%BB%D0%B0%D0%B4%D0%B8%D0%BC%D0%B8%D1%80%D0%BE%D0%B2%D0%B8%D1%87&
//company_address=174400%2C+174400%2C+%D0%9D%D0%BE%D0%B2%D0%B3%D0%BE%D1%80%D0%BE%D0%B4%D1%81%D0%BA%D0%B0%D1%8F+%D0%BE%D0%B1%D0%BB%2C+%D0%91%D0%BE%D1%80%D0%BE%D0%B2%D0%B8%D1%87%D1%81%D0%BA%D0%B8%D0%B9+%D1%80-%D0%BD%2C+%D0%B3+%D0%91%D0%BE%D1%80%D0%BE%D0%B2%D0%B8%D1%87%D0%B8&
//conf_id=2289

