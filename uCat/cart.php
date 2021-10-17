<?php
namespace uCat\cart;
require_once 'processors/classes/uFunc.php';
require_once 'uCat/classes/common.php';
require_once 'processors/uSes.php';
require_once "uPage/inc/setup_uPage_page.php";
require_once "uPage/inc/common.php";

use PDO;
use processors\uFunc;
use uCat\common;
use uCore;
use uSes;
use uString;

class cart {
    public $order_status;
    public $cart_has_real_items;
    public $uSes;
    public $uFunc;
    public $uCat;
    public $user_name;
    public $user_phone;
    public $user_email;
    public $delivery_address;
    public $delivery_comment;
    public $order_cash_payment_option_on;
    public $order_card_payment_option_on;
    public $sberbank_acquiring_status;
    public $customer_type;
    public $payment_method;
    public $sale_to_individuals;
    public $sale_to_companies;
    public $default_payment_method;
    public $order_id;
    public $uAuth;
    public $vat_number;
    public $company_name;
    public $company_address;
    public $tax_info_1;
    public $number_of_site_delivery_types;
    public $delivery_type_ar;
    public $delivery_point_variants_ar;
    public $delivery_points_ar;
    public $delivery_var_id;
    public $delivery_price;
    public $descr_field;
    /**
     * @var array
     */
    public $descr_field_title;
    public $uCore;
    public $q_items;

    public function define_delivery($total_price) {
        $this->delivery_price=0;
//        $this->delivery_var_id=0;

        if(!$stm_delivery_types=$this->uCat->get_delivery_types("del_type_id, 
            del_type_name, 
            del_type_descr, 
            del_type, 
            is_default,
            del_show,
            pos")) {
            $this->uFunc->error(0);
        }
        $this->delivery_type_ar=$stm_delivery_types->fetchAll(PDO::FETCH_OBJ);
        $this->number_of_site_delivery_types=count($this->delivery_type_ar);

        if($this->number_of_site_delivery_types&&$this->cart_has_real_items) {
            foreach ($this->delivery_type_ar as $iValue) {
                $delivery_type= $iValue;
                $iValue->selected=0;
                $delivery_type->del_type_id=(int)$delivery_type->del_type_id;
                $delivery_type->is_default=(int)$delivery_type->is_default;

                $stm_delivery_points=$this->uCat->get_delivery_points($delivery_type->del_type_id,"point_id,
                point_name,
                point_descr,
                is_default,
                point_show,
                pos");
                $this->delivery_points_ar[$delivery_type->del_type_id]=$stm_delivery_points->fetchAll(PDO::FETCH_OBJ);

                foreach ($this->delivery_points_ar[$delivery_type->del_type_id] as $jValue) {
                    $delivery_point= $jValue;
                    $jValue->selected=0;
                    $delivery_point->is_default=(int)$delivery_point->is_default;
                    $delivery_point->point_id=(int)$delivery_point->point_id;

                    $stm_delivery_point_variants=$this->uCat->get_delivery_point_variants($delivery_point->point_id,"var_id,
                    var_name,
                    var_descr,
                    delivery_price,
                    avail_at_price_since,
                    avail_at_price_till,
                    set_at_price_since,
                    manager_must_confirm,
                    manager_sets_delivery_price,
                    var_show,
                    pos");
                    $selected=0;
                    $this->delivery_point_variants_ar[$delivery_point->point_id]=$stm_delivery_point_variants->fetchAll(PDO::FETCH_OBJ);
                    foreach ($this->delivery_point_variants_ar[$delivery_point->point_id] as $kValue) {
                        $delivery_point_variant= $kValue;

                        $delivery_point_variant->var_id=(int)$delivery_point_variant->var_id;
                        $delivery_point_variant->avail_at_price_since=(float)$delivery_point_variant->avail_at_price_since;
                        $delivery_point_variant->avail_at_price_till=(float)$delivery_point_variant->avail_at_price_till;
                        $delivery_point_variant->set_at_price_since=(float)$delivery_point_variant->set_at_price_since;
                        $delivery_point_variant->delivery_price=(float)$delivery_point_variant->delivery_price;

                        $kValue->disabled=0;
                        if($total_price<$delivery_point_variant->avail_at_price_since) {
                            $kValue->disabled = 1;
                        }
                        elseif($total_price>=$delivery_point_variant->avail_at_price_till&&$delivery_point_variant->avail_at_price_till!=0) {
                            $kValue->disabled = 1;
                        }

                        $kValue->selected=0;
                        if($this->delivery_var_id===$delivery_point_variant->var_id) {
                            $kValue->selected=$selected=1;
                            $jValue->selected=1;
                            $iValue->selected=1;
                            $this->delivery_price=$delivery_point_variant->delivery_price;
                        }
                        elseif(!$this->delivery_var_id&&$total_price>=$delivery_point_variant->set_at_price_since&&!$selected&&!$kValue->disabled) {//TODO-nik87 потом придумать, как оператору назначать цену доставки вручную кроме как создавать новый вариант доставки
                            $kValue->selected=$selected=1;
                            $jValue->selected=1;
                            $iValue->selected=1;
                            $this->delivery_price=$delivery_point_variant->delivery_price;
                        }

                        if(!$this->delivery_var_id&&$delivery_type->is_default&&$delivery_point->is_default&& $kValue->selected) {
                            $this->delivery_var_id=$delivery_point_variant->var_id;
                        }
                    }
                }
            }
        }
        return $this->delivery_price;
    }
    public function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) {
            $this->uCore = new uCore();
        }

        $this->uCat=new common($this->uCore);
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);

        $this->delivery_var_id=-1;//Assign default value to delivery_var_id
        $delivery_type=0;//Assign default value to delivery_type
        $this->delivery_price=0;//Assign default value to delivery_price

        if(isset($_GET["order_id"])&&$this->uSes->access(2)) {
            $supposed_order_id=(int)$_GET["order_id"];

            $order_info=$this->uCat->order_id2data($supposed_order_id,"
            user_id,
            order_status,
            user_name,
            user_phone,
            user_email,
            delivery_var_id,
            delivery_address,
            delivery_comment,
            customer_type,
            vat_number,
            tax_info_1,
            company_name,
            company_address,
            payment_method
            ");

            if(
            ((int)$order_info->user_id===$this->uSes->get_val("user_id")&&
                (
                        $order_info->order_status==="new"||
                        $order_info->order_status==="items selected"
                )) ||
            $this->uSes->access(25)) {
                $this->order_id = $supposed_order_id;
            }
        }
        if(!isset($this->order_id)) {
            $this->order_id = $this->uCat->get_order_id();
            $order_info = $this->uCat->order_id2data($this->order_id, "
            order_status,
            user_name,
            user_phone,
            user_email,
            delivery_var_id,
            delivery_address,
            delivery_comment,
            customer_type,
            vat_number,
            tax_info_1,
            company_name,
            company_address,
            payment_method
        ");
        }

        $q_fields="";
        $this->descr_field=[];
        $this->descr_field_title=[];
        if($fields=$this->uCat->get_show_in_cart_fields()) {
            for($i=0; $field=$fields->fetch(PDO::FETCH_OBJ); $i++) {
                $q_fields.="field_".$field->field_id.",";
                $this->descr_field[$i]=(int)$field->field_id;
                $this->descr_field_title[$i]= uString::sql2text($field->field_title);
            }
        }

        $this->q_items=$this->uCat->get_order_items($this->order_id,
            $q_fields."
        orders_items.item_id,
        item_article_number,
        var_id,
        item_count,
        u235_items.item_type
        ");

        $this->cart_has_real_items=$this->uCat->order_has_real_items($this->order_id);
        $this->order_status=$order_info->order_status;
        $this->order_cash_payment_option_on=(int)$this->uFunc->getConf("order_cash_payment_option_on","uCat");
        $this->order_card_payment_option_on=(int)$this->uFunc->getConf("order_card_payment_option_on","uCat");
        $this->sberbank_acquiring_status=(int)$this->uFunc->getConf("sberbank_acquiring_status","uCat");
        $this->sale_to_individuals=(int)$this->uFunc->getConf("sale_to_individuals","uCat");
        $this->sale_to_companies=(int)$this->uFunc->getConf("sale_to_companies","uCat");
        $this->default_payment_method=(int)$this->uFunc->getConf("default_payment_method","uCat");

        if($this->order_status==="new") {
            if($this->uSes->access(2)) {
                $user_id = $this->uSes->get_val("user_id");
                if($user_preferences=$this->uCat->user_id2user_preferences($user_id,"user_name,
                    user_email,
                    user_phone,
                    delivery_var_id,
                    delivery_addr,
                    delivery_comment,
                    customer_type,
                    vat_number,
                    company_name,
                    tax_info1,
                    company_addr,
                    payment_method")) {
                    $this->user_name=$user_preferences->user_name;
                    $this->user_email=$user_preferences->user_email;
                    $this->user_phone=$user_preferences->user_phone;
                    $this->delivery_var_id=(int)$user_preferences->delivery_var_id;
                    $this->delivery_address=$user_preferences->delivery_addr;
                    $this->delivery_comment=$user_preferences->delivery_comment;
                    $this->customer_type=(int)$user_preferences->customer_type;
                    $this->vat_number=(int)$user_preferences->vat_number;
                    $this->company_name=$user_preferences->company_name;
                    $this->tax_info_1=(int)$user_preferences->tax_info1;
                    $this->company_address=$user_preferences->company_addr;
                    $this->payment_method=(int)$user_preferences->payment_method;

                    if($this->delivery_var_id) {
                        if($var_data=$this->uCat->delivery_point_variant_id2data($this->delivery_var_id,"point_id,delivery_price")) {
                            $point_id=(int)$var_data->point_id;
                            $point_data=$this->uCat->delivery_point_id2data($point_id,"del_type_id");
                            $del_type_id=(int)$point_data->del_type_id;
                            $this->delivery_price=(float)$point_data->delivery_price;
                            $del_type_data=$this->uCat->delivery_type_id2data($del_type_id,"del_type");
                            $delivery_type=(int)$del_type_data->del_type;
                        }
                        else {
                            $this->delivery_var_id=0;
                        }
                    }
                }
                else {
                    require_once "uAuth/classes/common.php";
                    $this->uAuth = new \uAuth\common($this->uCore);
                    $user_data = $this->uAuth->user_id2user_data($user_id, "firstname,lastname,email,cellphone");
                    $firstname = uString::sql2text(trim($user_data->firstname));
                    $lastname = uString::sql2text(trim($user_data->lastname));

                    $this->user_name = trim($firstname . " " . $lastname);
                    $this->user_email = $user_data->email;
                    $this->user_phone = $user_data->cellphone;
                    $this->delivery_var_id=0;
                }
            }

            if(!$this->delivery_var_id) {
                $this->delivery_address="";
                $this->delivery_comment="";
                $this->delivery_price=0;
                $delivery_type=0;
            }
            if(!isset($this->customer_type)) {
                $this->customer_type = (int)$this->uFunc->getConf("default_customer_type", "uCat");

                $this->vat_number=0;
                $this->company_name="";
                $this->tax_info_1=0;
                $this->company_address="";

                if ($this->customer_type === 1) {
                    $this->payment_method = 3;
                }
                else if (!isset($this->payment_method)) {
                    $this->payment_method = $this->default_payment_method;

                    if (!$this->order_cash_payment_option_on && !$this->cart_has_real_items && $this->payment_method === 0) {
                        if ($this->order_card_payment_option_on && $this->cart_has_real_items ) {
                            $this->payment_method = 1;
                        }
                        else {
                            $this->payment_method = 2;
                        }
                    }
                    if (!$this->order_card_payment_option_on  && !$this->cart_has_real_items && $this->payment_method === 1) {
                        if ($this->order_cash_payment_option_on && !$this->cart_has_real_items ) {
                            $this->payment_method = 0;
                        }
                        else {
                            $this->payment_method = 2;
                        }
                    }
                    if (!$this->sberbank_acquiring_status && $this->payment_method === 2) {
                        if ($this->order_cash_payment_option_on) {
                            $this->payment_method = 0;
                        }
                        elseif ($this->order_card_payment_option_on) {
                            $this->payment_method = 1;
                        }
                    }
                }
            }


            $this->uCat->order_update($this->order_id,$update_ar=[
                ['delivery_var_id',$this->delivery_var_id,PDO::PARAM_INT],
                ['delivery_type',$delivery_type,PDO::PARAM_INT],
                ['delivery_price',$this->delivery_price,PDO::PARAM_STR],
                ['payment_method',$this->payment_method,PDO::PARAM_INT],
                ['customer_type',$this->customer_type,PDO::PARAM_INT]
            ],"",site_id,1588598979);
        }
        else {
            $this->user_name=$order_info->user_name;
            $this->user_phone=$order_info->user_phone;
            $this->user_email=$order_info->user_email;
            $this->delivery_var_id=(int)$order_info->delivery_var_id;
//            $this->delivery_type=(int)$order_info->delivery_type;
            $this->delivery_address=$order_info->delivery_address;
            $this->delivery_comment=$order_info->delivery_comment;
            $this->customer_type=(int)$order_info->customer_type;
            $this->vat_number=(int)$order_info->vat_number;
            $this->tax_info_1=(int)$order_info->tax_info_1;
            $this->company_name=$order_info->company_name;
            $this->company_address=$order_info->company_address;
            $this->payment_method=(int)$order_info->payment_method;
        }

        $this->uFunc->incCss('uCat/css/cart.min.css');


        //bootstrap-touchSpin
        $this->uFunc->incCss('js/bootstrap_plugins/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.css');
        $this->uFunc->incJs('js/bootstrap_plugins/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js');

        //popConfirm
        $this->uFunc->incJs('js/bootstrap_plugins/PopConfirm/jquery.popconfirm.min.js');

        $this->uFunc->incJs(u_sroot.'uCat/js/cart.min.js');
        if($this->uSes->access(25)) {
            $this->uFunc->incJs("uCat/js/cart_admin.min.js");
        }

        //Подсказки ИНН
        $this->uFunc->incCSS("/js/dadata_suggestions/suggestions.min.css");
        $this->uFunc->incJs("/js/dadata_suggestions/jquery.suggestions.js");
    }
}
$uCat=new cart($this);

ob_start();

$step_counter=1;?>

<div class="container-fluid">

    <?php
    if($item=$uCat->q_items->fetch(PDO::FETCH_OBJ)){
    /** @noinspection PhpUnusedLocalVariableInspection */
    $q_items=&$uCat->q_items;//Used in included cart_content.php
    ?>
    <div id="uCat_cart_content" class="col-md-12 cart_row">
        <h2><?=$step_counter++?>. Проверьте товары в корзине</h2>
    <?include 'uCat/templates/cart/cart_content.php';
    if(!isset($total_price)) {
        $total_price = 0;
    }//$total_price задается ВСЕГДА в uCat/templates/cart/cart_content.php. Эта строка должна быть под uCat/templates/cart/cart_content.php - чтобы IDE мозг не трахала?>
    </div>

    <div class="row" style="display: table">&nbsp;</div>

    <div id="uCat_cart_login" class="col-md-12 cart_row cart_row_inactive">
    <h2><?=$step_counter++?>. Введите ваши данные</h2>
        <div class="row">
            <div class="col-md-12">
                <form class="form-horizontal" autocomplete="on" method="post" action="#">
                    <div class="form-group " id="uCat_cart_user_name_form_gr">
                        <label class="control-label col-md-3 col-lg-3 col-sm-3" for="uCat_cart_user_name" <?=$uCat->uSes->access(2)?('value="'.$uCat->uSes->get_val("user_id").'"'):''?>>Ваше имя *</label>
                        <div class="col-md-9 col-sm-9 col-lg-9">
                            <input id="uCat_cart_user_name" <?=($uCat->user_name!==""?(' value="'.$uCat->user_name.'" '):"")?> type="text" placeholder="Иван Иванов" class="form-control" name=name autocomplete=name  onkeyup="uCat_cart_page.on_user_name_keyup()" onblur="uCat_cart_page.on_user_name_blur()">
                            <p class="help-block hidden">Проверьте Имя. Оно не должно быть таким коротким</p>
                        </div>
                    </div>
                    <div class="form-group" id="uCat_cart_user_email_form_gr">
                        <label for="uCat_cart_user_email" class="control-label col-md-3 col-lg-3 col-sm-3">Ваш E-mail *</label>
                        <div class="col-md-9 col-sm-9 col-lg-9">
                            <input id="uCat_cart_user_email" <?=($uCat->user_email!==""?(' value="'.$uCat->user_email.'" '):"")?> type=email placeholder="ivan@madwww.ru" class="form-control" name=email  autocomplete=email onkeyup="uCat_cart_page.on_email_keyup()" onblur="uCat_cart_page.on_email_blur()">
                            <p class="help-block hidden">Проверьте E-mail. Это должен быть действуюший адрес электронной почты, например myname@gmail.com</p>
                        </div>
                    </div>

                    <div class="form-group" id="uCat_cart_user_phone_form_gr">
                        <label class="control-label col-md-3 col-lg-3 col-sm-3" for="uCat_cart_user_phone">Ваш Телефон *</label>
                        <div class="col-md-9 col-sm-9 col-lg-9">
                            <input type=tel  <?=($uCat->user_phone!==""?(' value="'.$uCat->user_phone.'" '):"")?> placeholder="+78126482845" class="form-control" name=phone  autocomplete=tel id="uCat_cart_user_phone" onkeyup="uCat_cart_page.on_phone_keyup()" onblur="uCat_cart_page.on_phone_blur()">
                            <p class="help-block hidden">Проверьте телефон. Это должен быть действуюший номер телефона в международном формате, например +79213750085</p>
                        </div>
                    </div>
                    <input type=submit value="Continue..." class="hidden">
                </form>
                <?if(!$uCat->uSes->access(2)) {?><p class="help-block">Если у вас есть учетная запись на нашем сайте, то вы можете <a href="javascript:void(0)"  onclick="uAuth_form.open()">авторизоваться</a></p><?}?>
            </div>
        </div>
    </div>

    <?if($uCat->number_of_site_delivery_types&&$uCat->cart_has_real_items) {
    $del_col_width=12/$uCat->number_of_site_delivery_types;
    $del_type=0;?>
    <div class="col-md-12 cart_row cart_row_inactive" id="uCat_cart_delivery">
        <h2><?=$step_counter++?>. Выберите способ получения</h2>

        <div class="row"><?php
            for($i=0;$i<$uCat->number_of_site_delivery_types;$i++) {
                $delivery_type=$uCat->delivery_type_ar[$i];
                $delivery_type->is_default=(int)$delivery_type->is_default;
                $delivery_type->del_type=(int)$delivery_type->del_type;
                if($delivery_type->is_default) {
                    $del_type = $delivery_type->del_type;
                }
            $delivery_type->del_type_id=(int)$delivery_type->del_type_id;
                ?>
                <div class="col-sm-6 col-md-<?=$del_col_width?> col-xs-12">
                    <div class="uCat_checkout_delivery_card <?php
                    if($delivery_type->selected) {
                        echo " bg-success selected ";
                    }
                    ?>"
                         id="uCat_checkout_delivery_card_<?=$delivery_type->del_type_id?>"
                         data-field_type="delivery_type"
                         data-del_type=<?=$delivery_type->del_type?>"
                         data-del_type_id="<?=$delivery_type->del_type_id?>"
                         onclick="uCat_cart_page.delivery_changed(this)">
                        <div class="caption">
                            <h3><?=$delivery_type->del_type_name?><span class="icon-ok text-success def_delivery_type_0_icon def_delivery_type_icon <?php
                                if($delivery_type->selected) {
                                    echo " ";
                                }
                                else {
                                    echo " hidden ";
                                }
                                ?>"></span></h3>
                            <?=$delivery_type->del_type_descr?>
                        </div>
                    </div>
                </div>
            <?}?>
        </div>
        <div class="row"><?php
            for($i=0;$i<$uCat->number_of_site_delivery_types;$i++) {
                $delivery_type=$uCat->delivery_type_ar[$i];
                $delivery_type->is_default=(int)$delivery_type->is_default;
                $delivery_type->del_type=(int)$delivery_type->del_type;
                if($delivery_type->selected) {
                    $del_type = $delivery_type->del_type;
                }
                $delivery_type->del_type_id=(int)$delivery_type->del_type_id;
                ?>

                <div class="uCat_cart_points <?php
                if($delivery_type->selected) {
                    echo " ";
                }
                else {
                    echo " hidden ";
                }
                ?>" id="uCat_cart_points_<?=$delivery_type->del_type_id?>">
                <?$delivery_points_ar_count=count($uCat->delivery_points_ar[$delivery_type->del_type_id]);
                if($delivery_points_ar_count>1) {
                    if($delivery_type->del_type===0) {?><h3>Введите наиболее удобный для вас адрес получения</h3><?}
                    else {?><h3>Выберите пункт, соответствующий вашему адресу доставки</h3><?}
                }
                for($j=0;$j<$delivery_points_ar_count;$j++) {
                    $delivery_point=$uCat->delivery_points_ar[$delivery_type->del_type_id][$j];
                    $delivery_point->point_id=(int)$delivery_point->point_id;
                    ?>
                    <div class="uCat_cart_point_container <?=$delivery_point->selected?'selected':''?>" id="uCat_cart_point_container_<?=$delivery_point->point_id?>">
                        <div class="uCat_cart_point_name"
                             data-field_type="delivery_point"
                             data-point_id="<?=$delivery_point->point_id?>"
                             data-del_type="<?=$delivery_type->del_type?>"
                             data-del_type_id="<?=$delivery_type->del_type_id?>"
                             onclick="uCat_cart_page.delivery_changed(this)"><span id="uCat_cart_point_name_icon_<?=$delivery_point->point_id?>" class="uCat_cart_point_name_icon icon-check<?=$delivery_point->selected?' text-success':'-empty'?>"></span><?=$delivery_point->point_name?></div>
                        <div class="point_descr"><?=$delivery_point->point_descr?></div>

                        <div class="uCat_cart_point_variants <?=$delivery_point->selected?'':'hidden'?>" id="uCat_cart_point_variants_<?=$delivery_point->point_id?>">
                            <?php
                        $delivery_point_variants_ar_count=count($uCat->delivery_point_variants_ar[$delivery_point->point_id]);
                        for($k=0;$k<$delivery_point_variants_ar_count;$k++) {
                            $delivery_point_variant=$uCat->delivery_point_variants_ar[$delivery_point->point_id][$k];

                            $delivery_point_variant->var_id=(int)$delivery_point_variant->var_id;
                            $delivery_point_variant->avail_at_price_since=(float)$delivery_point_variant->avail_at_price_since;
                            $delivery_point_variant->avail_at_price_till=(float)$delivery_point_variant->avail_at_price_till;
                            $delivery_point_variant->set_at_price_since=(float)$delivery_point_variant->set_at_price_since;
                            ?>
                            <div data-var_id="<?=$delivery_point_variant->var_id?>" id="uCat_cart_delivery_option_var_<?=$delivery_point_variant->var_id?>" class="uCat_cart_delivery_option_var <?=$delivery_point_variant->disabled?'disabled':''?> <?=$delivery_point_variant->selected?'selected':''?>">
                                <div class="uCat_cart_point_var_name"
                                     id="uCat_delivery_var_<?=$delivery_point_variant->var_id?>"
                                     data-field_type="delivery_var"
                                     data-del_type="<?=$delivery_type->del_type?>"
                                     data-del_type_id="<?=$delivery_type->del_type_id?>"
                                     data-point_id="<?=$delivery_point->point_id?>"
                                     data-var_id="<?=$delivery_point_variant->var_id?>"
                                     data-delivery_price="<?=$delivery_point_variant->delivery_price?>"
                                     data-avail_at_price_since="<?=$delivery_point_variant->avail_at_price_since?>"
                                     data-avail_at_price_till="<?=$delivery_point_variant->avail_at_price_till?>"
                                     data-set_at_price_since="<?=$delivery_point_variant->set_at_price_since?>"
                                     onclick="uCat_cart_page.delivery_changed(this)"><span id="uCat_cart_point_var_check_icon_<?=$delivery_point_variant->var_id?>" class="icon-check<?=$delivery_point_variant->selected?' text-success':'-empty'?> uCat_cart_point_var_check_icon"></span><?=$delivery_point_variant->var_name?></div>
                                <div class="uCat_cart_point_var_descr"><?=$delivery_point_variant->var_descr?></div>
                            </div>
                        <?}?>
                        </div>
                    </div>
                <?}?>
                </div>
            <?}?>
        </div>

        <div class="<?=$del_type===1?' ':'hidden'?> uCat_delivery_type col-md-12" id="uCat_delivery_address_container">
            <div class="row uCat_delivery_info uCat_delivery_info_1" id="uCat_delivery_local_delivery_info">
                <div class="col-md-12">
                    <div class="row">
                        <div class="form-group uCat_delivery_address_form_gr">
                            <label class="control-label" for="uCat_delivery_address">Адрес доставки *</label>
                            <input <?=($uCat->delivery_address!==""?(' value="'.htmlspecialchars($uCat->delivery_address).'" '):"")?>  type="text" id="uCat_delivery_address" class="form-control" placeholder="Начните вводить адрес доставки"  onkeyup="uCat_cart_page.on_delivery_address_keyup()" onblur="uCat_cart_page.on_delivery_address_blur()" name=address-line1 autocomplete=address-line1>
                            <p class="help-block hidden">Адрес должнен быть заполнен. Иначе мы не узнаем, куда доставить заказ... :(</p>

                        </div>
                        <div class="form-group uCat_delivery_comment_form_gr">
                            <label class="control-label" for="uCat_delivery_comment">Дополнительная информация</label>
                            <textarea id="uCat_delivery_comment" class="form-control" onblur="uCat_cart_page.on_delivery_comment_blur()"><?=($uCat->delivery_comment!==""?strip_tags($uCat->delivery_comment):"")?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <?}?>


    <div class="col-md-12 cart_row cart_row_inactive" id="uCat_cart_payment_type">
        <h2><?=$step_counter?>. Выберите способ оплаты</h2>
        <?if($uCat->sale_to_individuals&&$uCat->sale_to_companies){?>
        <div class="row">
            <?if($uCat->sale_to_individuals) {?>
                <div class="col-sm-6 col-md-6 col-xs-12" id="uCat_checkout_payment_card_0" onclick="uCat_cart_page.customer_type_changed(0)">
                    <div class="uCat_checkout_payment_card <?=$uCat->customer_type===0?" bg-success ":""?>">
                        <div class="caption">
                            <h3>Физическое лицо<span class="icon-ok text-success customer_type_0_icon customer_type_icon <?=$uCat->customer_type===0?' ':'hidden'?>"></span></h3>
                        </div>
                    </div>
                </div>
            <?}?>
            <?if($uCat->sale_to_companies) {?>
                <div class="col-sm-6 col-md-6 col-xs-12" id="uCat_checkout_payment_card_1" onclick="uCat_cart_page.customer_type_changed(1)">
                    <div class="uCat_checkout_payment_card <?=$uCat->customer_type===1?" bg-success ":""?>">
                        <div class="caption">
                            <h3>Организация<span class="icon-ok text-success customer_type_1_icon customer_type_icon <?=$uCat->customer_type===1?' ':'hidden'?>"></span></h3>
                        </div>
                    </div>
                </div>
            <?}?>
        </div>
        <?}?>

        <div class="<?=$uCat->customer_type===0?' ':'hidden'?> uCat_payment_type" id="uCat_payment_type_0">
            <div class="row uCat_payment_txt uCat_payment_txt_0">
                <div class="col-md-12">
                    <?$btn_number=0?>
                    <?=$uCat->order_cash_payment_option_on&&$uCat->cart_has_real_items?('
                    <div onclick="uCat_cart_page.payment_method_changed(0)" id="uCat_payment_type_btn_0" class="uCat_payment_type_btn uCat_payment_type_btn_number_'.($btn_number++)." ".($uCat->payment_method===0?'active':'').'">
                        <img alt="" src="'.staticcontent_url.'images/uCat/payment_methods/cash.jpg"><br>
                        <h3><span class="icon-ok text-success"></span> Наличными</h3>
                    </div>
'):''?>
                    <?=$uCat->sberbank_acquiring_status!==0?('
                    <div onclick="uCat_cart_page.payment_method_changed(2)" id="uCat_payment_type_btn_2" class="uCat_payment_type_btn uCat_payment_type_btn_number_'.($btn_number++)." ".($uCat->payment_method===2?'active':'').'">
                        <img alt="" src="'.staticcontent_url.'images/uCat/payment_methods/online.jpg"><br>
                        <h3><span class="icon-ok text-success"></span> Картой на сайте</h3></h3>
                    </div>
                    '):''?>

                    <?=$uCat->order_card_payment_option_on&&$uCat->cart_has_real_items?('
                    <div onclick="uCat_cart_page.payment_method_changed(1)" id="uCat_payment_type_btn_1" class="uCat_payment_type_btn uCat_payment_type_btn_number_'.($btn_number)." ".($uCat->payment_method===1?'active':'').'">
                        <img alt="" src="'.staticcontent_url.'images/uCat/payment_methods/pos.jpg"><br>
                        <h3><span class="icon-ok text-success"></span> Картой</h3>
                    </div>
                    '):''?>
                </div>
            </div>
        </div>

        <div class="<?=$uCat->customer_type===1?' ':'hidden'?> uCat_payment_type" id="uCat_payment_type_1">
            <div class="row uCat_payment_txt uCat_payment_txt_1">

                <div class="form-group" id="uCat_vat_number_form_gr">
                    <label class="control-label col-md-3 col-lg-3 col-sm-3 col-xs-12" for="uCat_vat_number">ИНН *</label>
                    <div class="col-md-9 col-sm-9 col-lg-9 col-xs-12">
                        <input type="text" id="uCat_vat_number" class="form-control" onkeyup="uCat_cart_page.on_vat_number_keyup()" onblur="uCat_cart_page.on_vat_number_blur()" <?=($uCat->vat_number!==0?(' value="'.$uCat->vat_number.'" '):"")?>>
                        <p class="help-block hidden">ИНН для ИП должен состоять из 12 цифр, а для юр. лица - 12 цифр</p>
                    </div>
                </div>

                <div>&nbsp;</div>

                <div class="form-group" id="uCat_company_name_form_gr">
                    <label class="control-label col-md-3 col-lg-3 col-sm-3 col-xs-12" for="uCat_company_name">Название компании *</label>
                    <div class="col-md-9 col-sm-9 col-lg-9 col-xs-12">
                        <input type="text" id="uCat_company_name" class="form-control" onkeyup="uCat_cart_page.on_company_name_keyup()" onblur="uCat_cart_page.on_vat_number_blur()" <?=($uCat->company_name!==""?(' value="'.htmlspecialchars($uCat->company_name).'" '):"")?>>
                        <p class="help-block hidden">Название компании должно быть заполнено</p>
                    </div>
                </div>

                <div>&nbsp;</div>

                <div class="form-group">
                    <label class="control-label col-md-3 col-lg-3 col-sm-3 col-xs-12" for="uCat_tax_info_1">КПП <sub>(не обязательно)</sub></label>
                    <div class="col-md-9 col-sm-9 col-lg-9 col-xs-12">
                        <input type="text" id="uCat_tax_info_1" class="form-control" <?=($uCat->tax_info_1!==0?(' value="'.$uCat->tax_info_1.'" '):"")?>>
                    </div>
                </div>

                <div>&nbsp;</div>

                <div class="form-group" id="uCat_company_address_form_gr">
                    <label class="control-label col-md-3 col-lg-3 col-sm-3 col-xs-12" for="uCat_company_address">Юридический адрес *</label>
                    <div class="col-md-9 col-sm-9 col-lg-9 col-xs-12">
                        <input type="text" id="uCat_company_address" class="form-control" onkeyup="uCat_cart_page.on_company_address_keyup()" onblur="uCat_cart_page.on_company_address_blur()" <?=($uCat->company_address!==""?(' value="'.htmlspecialchars($uCat->company_address).'" '):"")?>>
                        <p class="help-block hidden">Юридический адрес должен быть заполнен</p>
                    </div>
                </div>

                <div id="res_tmp"></div>

            </div>
        </div>

    </div>


    <div class="col-md-12 cart_row cart_row_inactive" id="uCat_cart_order_confirm">
        <button class="btn btn-primary btn-lg hidden" id="uCat_confirm_order_btn" onclick="uCat_cart_page.confirm_order()">Оформить заказ</button>
        <button class="btn btn-primary btn-lg hidden" id="uCat_confirm_order_and_pay_online_btn" onclick="uCat_cart_page.confirm_order()">Оформить заказ и оплатить онлайн</button>
        <button class="btn btn-primary btn-lg hidden" id="uCat_confirm_order_download_invoice_btn" onclick="uCat_cart_page.confirm_order()">Оформить заказ и скачать счет</button>
    </div>

    <div class="col-md-12 cart_row bs-callout bs-callout-primary" id="uCat_cart_order_no_confirm_btn_descr">
        <p>Заполните все требуемые поля для оформления заказа</p>
        <button class="btn btn-primary btn-lg disabled">Оформить заказ</button>
    </div>

    <script type="text/javascript">
        if(typeof uCat_cart_page==="undefined") uCat_cart_page={};
        if(typeof uCat_cart==="undefined") uCat_cart={};
        uCat_cart.cart_total_price=<?=$total_price?>;

        uCat_cart_page.order_id=<?=$uCat->order_id?>;
        uCat_cart_page.delivery_var_id=<?=(int)$uCat->delivery_var_id?>;
        uCat_cart_page.delivery_price=<?=$uCat->delivery_price?>;
        uCat_cart_page.customer_type=<?=$uCat->customer_type?>;
        uCat_cart_page.payment_method=<?=$uCat->payment_method?>;
        uCat_cart_page.default_payment_method=<?=$uCat->default_payment_method?>;
        uCat_cart_page.has_real_items=<?=$uCat->cart_has_real_items?>;
        uCat_cart_page.number_of_site_delivery_types=<?=$uCat->number_of_site_delivery_types?>;
    </script>

<?}
else {
    include 'uCat/templates/cart/cart_content_empty.php';?>
    <script type="text/javascript">
        if(typeof uCat_cart_page==="undefined") uCat_cart_page={};
        uCat_cart_page.order_id=<?=$uCat->order_id?>;
    </script>
<?}?>
    <?if($uCat->uSes->access(25)) {?>
        <button class="btn btn-danger" onclick="uCat_cart_page.cancel_order_confirm('cancel')">Отменить заказ</button>
        <div class="bs-callout bs-callout-primary">Кнопка отмены выше отображается только вам, как администратору</div>
    <?}?>
</div>
    <?php
include_once 'uCat/dialogs/uCat_cart.php';
if($uCat->uSes->access(25)) {
include_once 'uDrive/inc/my_drive_manager.php';
    include_once 'uCat/dialogs/cart_admin.php';?>
    <div id="uDrive_my_drive_uploader_init"></div>
<?}
$uCat->uCore->page_content=ob_get_clean();

include 'templates/template.php';
