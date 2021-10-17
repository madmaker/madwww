<?php
namespace uCat\cart;
use PDO;
use processors\uFunc;
use uCat\common;
use uSes;
use uString;


require_once "processors/classes/uFunc.php";
require_once "uCat/classes/common.php";
require_once "processors/uSes.php";

class cart_update_bg {
    private $order_status;
    private $uSes;
    private $order_id;
    private $uCat;
    private $uFunc;
    private $uCore;
    private function check_data() {
        if(!isset($_POST["order_id"])) {
            echo json_encode(array("status"=>"error","msg"=>"no order_id"));
            exit;
        }
        $this->order_id=(int)$_POST["order_id"];
        if($this->uSes->access(25)) return 1;
        if($order_data=$this->uCat->order_id2data($this->order_id,"user_id,order_status")) {
            if($this->uSes->access(25)||(int)$order_data->user_id === $this->uSes->get_val("user_id")) {
                $this->order_status=$order_data->order_status;
                return 1;
            }
        }

        $this->order_id=$this->uCat->get_order_id();
        return 0;
    }

    private function update_order_user_email() {
        $user_email=trim($_POST["user_email"]);
        if(!\uString::isEmail($user_email)) {
            echo json_encode(array("status"=>"error","msg"=>"user_email has wrong format"));
            exit;
        }
        $this->uCat->order_update($this->order_id,$update_ar=[
//            ['order_status',"items selected",PDO::PARAM_STR],
            ['user_email',$user_email,PDO::PARAM_STR]
        ],"",site_id,1588599131);

        echo json_encode(array("status"=>"success","msg"=>"user_email updated"));
    }

    private function update_order_user_phone() {
        $user_phone=trim($_POST["user_phone"]);
        $user_phone=str_replace("(","",$user_phone);
        $user_phone=str_replace(")","",$user_phone);
        $user_phone=str_replace("-","",$user_phone);
        $user_phone=str_replace(" ","",$user_phone);
        if(!\uString::isPhone($user_phone)) {
            echo json_encode(array("status"=>"error","msg"=>"user_phone has wrong format"));
            exit;
        }

        if(!isset($this->order_status)) {
            $order_data=$this->uCat->order_id2data($this->order_id,"order_status");
            $this->order_status=$order_data->order_status;
        }

        $this->uCat->order_update($this->order_id,$update_ar=[
            ['order_status',"items selected",PDO::PARAM_STR],
            ['user_phone',$user_phone,PDO::PARAM_STR]
        ],"",site_id,1588599144);

        if($this->order_status==="new") {
            $this->uCat->notify_about_order_change($this->order_id, "items selected","new");
        }

        echo json_encode(array("status"=>"success","msg"=>"user_phone updated"));
    }

    private function update_order_user_name() {
        $user_name=trim($_POST["user_name"]);
        if(strlen($user_name)<3) {
            echo json_encode(array("status"=>"error","msg"=>"user_name is too short"));
            exit;
        }
        $this->uCat->order_update($this->order_id,$update_ar=[
//            ['order_status',"items selected",PDO::PARAM_STR],
            ['user_name',$user_name,PDO::PARAM_STR]
        ],"",site_id,1588599137);

        echo json_encode(array("status"=>"success","msg"=>"user_name updated"));
    }

    private function update_order_delivery_var_id() {
        $delivery_var_id=(int)($_POST["delivery_var_id"]);

        $delivery_price=0;
        $del_type=0;
        $delivery_name="";
        if($delivery_var_info=$this->uCat->delivery_point_variant_id2data($delivery_var_id,"point_id,var_name,delivery_price")) {
            $delivery_price=$delivery_var_info->delivery_price;
            $delivery_name=$delivery_var_info->var_name;
            if($delivery_point_info=$this->uCat->delivery_point_id2data($delivery_var_info->point_id,"del_type_id,point_name")) {
                $delivery_name=$delivery_point_info->point_name." | ".$delivery_name;
                if($delivery_type_info=$this->uCat->delivery_type_id2data($delivery_point_info->del_type_id,"del_type_name,del_type")) {
                    $delivery_name=$delivery_type_info->del_type_name." | ".$delivery_name;
                    $del_type=(int)$delivery_type_info->del_type;
                }
            }
        }

        $this->uCat->order_update($this->order_id,$update_ar=[
            ['delivery_type',$del_type,PDO::PARAM_INT],
            ['delivery_name',$delivery_name,PDO::PARAM_STR],
            ['delivery_price',$delivery_price,PDO::PARAM_STR],
            ['order_status',"items selected",PDO::PARAM_STR],
            ['delivery_var_id',$delivery_var_id,PDO::PARAM_INT]
        ],"",site_id,1588599088);

        echo json_encode(array("status"=>"success","msg"=>"delivery_var_id updated"));
    }
    private function update_order_delivery_address() {
        $delivery_var_id=(int)$_POST["delivery_var_id"];

        $delivery_price=0;
        $del_type=0;
        $delivery_name="";
        if($delivery_var_info=$this->uCat->delivery_point_variant_id2data($delivery_var_id,"point_id,var_name,delivery_price")) {
            $delivery_price=$delivery_var_info->delivery_price;
            $delivery_name=$delivery_var_info->var_name;
            if($delivery_point_info=$this->uCat->delivery_point_id2data($delivery_var_info->point_id,"del_type_id,point_name")) {
                $delivery_name=$delivery_var_info->point_name." ".$delivery_name;
                if($delivery_type_info=$this->uCat->delivery_type_id2data($delivery_point_info->del_type_id,"del_type_name,del_type")) {
                    $delivery_name=$delivery_type_info->del_type_name." ".$delivery_name;
                    $del_type=(int)$delivery_type_info->del_type;
                }
            }
        }


        $delivery_address=trim($_POST["delivery_address"]);
        if(strlen($delivery_address)<7) {
            echo json_encode(array("status"=>"error","msg"=>"delivery_address is too short"));
            exit;
        }
        $this->uCat->order_update($this->order_id,$update_ar=[
            ['delivery_type',$del_type,PDO::PARAM_INT],
            ['delivery_name',$delivery_name,PDO::PARAM_STR],
            ['delivery_price',$delivery_price,PDO::PARAM_STR],
            ['order_status',"items selected",PDO::PARAM_STR],
            ['delivery_var_id',$delivery_var_id,PDO::PARAM_INT],
            ['delivery_address',$delivery_address,PDO::PARAM_STR]
        ],"",site_id,1588599075);

        echo json_encode(array("status"=>"success","msg"=>"delivery_address updated"));
    }
    private function update_order_delivery_comment() {
        $delivery_var_id=(int)$_POST["delivery_var_id"];

        $delivery_price=0;
        $del_type=0;
        $delivery_name="";
        if($delivery_var_info=$this->uCat->delivery_point_variant_id2data($delivery_var_id,"point_id,var_name,delivery_price")) {
            $delivery_price=$delivery_var_info->delivery_price;
            $delivery_name=$delivery_var_info->var_name;
            if($delivery_point_info=$this->uCat->delivery_point_id2data($delivery_var_info->point_id,"del_type_id,point_name")) {
                $delivery_name=$delivery_var_info->point_name." ".$delivery_name;
                if($delivery_type_info=$this->uCat->delivery_type_id2data($delivery_point_info->del_type_id,"del_type_name,del_type")) {
                    $delivery_name=$delivery_type_info->del_type_name." ".$delivery_name;
                    $del_type=(int)$delivery_type_info->del_type;
                }
            }
        }

        $delivery_comment=trim($_POST["delivery_comment"]);
        $this->uCat->order_update($this->order_id,$update_ar=[
            ['delivery_type',$del_type,PDO::PARAM_INT],
            ['delivery_name',$delivery_name,PDO::PARAM_STR],
            ['delivery_price',$delivery_price,PDO::PARAM_STR],
            ['order_status',"items selected",PDO::PARAM_STR],
            ['delivery_var_id',$delivery_var_id,PDO::PARAM_INT],
            ['delivery_comment',$delivery_comment,PDO::PARAM_STR]
        ],"",site_id,1588599081);

        echo json_encode(array("status"=>"success","msg"=>"delivery_comment updated"));
    }

    private function update_order_customer_type() {
        $customer_type=(int)($_POST["customer_type"]);
        if($customer_type<0&&$customer_type>1) {
            echo json_encode(array("status"=>"error","msg"=>"customer_type is wrong"));
            exit;
        }
        if($customer_type===0) {
            $order_info=$this->uCat->order_id2data($this->order_id,"payment_method");
            $order_payment_method=(int)$order_info->payment_method;

            if($order_payment_method===3) {
                $default_payment_method=(int)$this->uFunc->getConf("default_payment_method","uCat");
                if($default_payment_method===3) {
                    if((int)$this->uFunc->getConf("order_cash_payment_option_on", "uCat")) $payment_method=0;
                    elseif((int)$this->uFunc->getConf("order_card_payment_option_on", "uCat")) $payment_method=1;
                    elseif((int)$this->uFunc->getConf("sberbank_acquiring_status", "uCat")) $payment_method=2;
                    else $payment_method=0;
                }
                else $payment_method=$default_payment_method;
            }
            else $payment_method=$order_payment_method;
        }
        else $payment_method=3;

        $this->uCat->order_update($this->order_id,$update_ar=[
            ['order_status',"items selected",PDO::PARAM_STR],
            ['customer_type',$customer_type,PDO::PARAM_INT],
            ['payment_method',$payment_method,PDO::PARAM_INT]
        ],"",site_id,1588599067);

        echo json_encode(array("status"=>"success","msg"=>"customer_type updated"));
    }
    private function update_order_payment_method() {
        $payment_method=(int)($_POST["payment_method"]);
        if($payment_method<0&&$payment_method>3) {
            echo json_encode(array("status"=>"error","msg"=>"payment_method is wrong"));
            exit;
        }
        $this->uCat->order_update($this->order_id,$update_ar=[
            ['order_status',"items selected",PDO::PARAM_STR],
            ['payment_method',$payment_method,PDO::PARAM_INT]
        ],"",site_id,1588599094);

        echo json_encode(array("status"=>"success","msg"=>"payment_method updated"));
    }
    private function update_order_company_info() {
        $vat_number=(int)trim(($_POST["vat_number"]));
        $company_name=trim(($_POST["company_name"]));
        $tax_info_1=(int)trim(($_POST["tax_info_1"]));
        $company_address=trim(($_POST["company_address"]));
        if(strlen($vat_number)!=10&&strlen($vat_number)!=12) {
            echo json_encode(array("status"=>"error","msg"=>"vat number is wrong"));
            exit;
        }
        if(strlen($company_name)<5) {
            echo json_encode(array("status"=>"error","msg"=>"company_name is too short"));
            exit;
        }
        if(strlen($company_address)<5) {
            echo json_encode(array("status"=>"error","msg"=>"company_address is too short"));
            exit;
        }
        $this->uCat->order_update($this->order_id,$update_ar=[
            ['order_status',"items selected",PDO::PARAM_STR],
            ['company_name',$company_name,PDO::PARAM_STR],
            ['vat_number',$vat_number,PDO::PARAM_INT],
            ['tax_info_1',$tax_info_1,PDO::PARAM_INT],
            ['company_address',$company_address,PDO::PARAM_STR]
        ],"",site_id,1588599021);

        echo json_encode(array("status"=>"success","msg"=>"company info updated"));
    }

    private function register_new_user($user_email,$user_name) {
        if(!isset($this->uAuth)) {
            require_once 'uAuth/classes/common.php';
            $this->uAuth=new \uAuth\common($this->uCore);
        }
        $user=$this->uAuth->userLogin2info('user_id, status',$user_email,'email');

        if($user) {
            if($user->status==='banned') return 0;
            else {
                $usersinfo=$this->uAuth->user_id2usersinfo($user->user_id,'status');

                if($usersinfo) return (int)$user->user_id;
                else {
                    $this->uAuth->add_user2usersinfo($user->user_id,'active');
                    $this->uAuth->emailUserAboutRegistration($user_name,$user_email,'');
                    return (int)$user->user_id;
                }
            }
        }
        else {
            $pass=uFunc::genPass();
            $user_id=(int)$this->uAuth->add_new_user($user_name,"","",$user_email,$pass,"",'active');
            $new_user=$this->uAuth->userLogin2info('user_id',$user_email,'email');
            if(!$new_user) $this->uFunc->error(40);
            $this->uAuth->add_user2usersinfo($new_user->user_id,'active');
            $this->uAuth->emailUserAboutRegistration($user_name,$user_email,$pass);
            return $user_id;
        }
    }
    private function create_bill($order_data) {
        $q_items=$this->uCat->get_order_items($this->order_id,"
        orders_items.item_id,
        item_article_number,
        orders_items.var_id,
        orders_items.item_count,
        orders_items.item_price,
        orders_items.item_title
        ");

        $items_ar=array();
        /** @noinspection PhpUndefinedMethodInspection */
        for($i=0; $item=$q_items->fetch(PDO::FETCH_OBJ); $i++) {
            $item_title = uString::sql2text($item->item_title, 1);

            if ((int)$item->var_id) {
                $var_data = $this->uCat->var_id2data($item->var_id);

                $var_type_id=$this->uCat->var_id2var_type_id($item->var_id);

                $enable_var_options=(int)$this->uFunc->getConf("enable_var_options","uCat");
                $var_type_title=$item_title=uString::sql2text($this->uCat->var_type_id2data($var_type_id)->var_type_title);

                if($enable_var_options) $item_title=$var_type_title;
                else $item_title.='. ('.$var_type_title.')';

                if($this->uFunc->getConf("show_item_article_number","uCat")) $item_title.=' | Артикул: '.$var_data->item_article_number;
            }
            else {
                if($this->uFunc->getConf("show_item_article_number","uCat")) $item_title.=' | Артикул: '.$item->item_article_number;
            }

            $items_ar[$i]=array($item_title,$item->item_count,"Штука",$item->item_price);
        }
        $delivery_price=(float)$order_data->delivery_price;
        if($delivery_price) {
            $items_ar[count($items_ar)]=array($order_data->delivery_name,1,"",$delivery_price);
        }

        $customer_info=array(
            $order_data->company_name,/*Наименование компании*/
            $order_data->vat_number,/*ИНН*/
            ((int)$order_data->tax_info_1?$order_data->tax_info_1:""),/*КПП. Пустая строка, если нет*/
            $order_data->company_address/*Юридический адрес. 633010, Новосибирская обл, г Бердск, ул Ленина, д 94, оф 3*/
        );

        $bill_number=$this->uFunc->create_bill($items_ar,$customer_info,"<h4>Заказ № ".$this->order_id."</h4>");

        return $bill_number;
    }
    private function update_order_confirm() {
        //Check if cart has items

        if(!$this->uCat->order_has_items($this->order_id)) {
            echo json_encode(array("status"=>"error","msg"=>"cart is empty"));
            exit;
        }


        if(!$order_data=$this->uCat->order_id2data($this->order_id,"
        user_name,
        user_phone,
        user_email,
        delivery_type,
        delivery_var_id,
        delivery_name,
        delivery_price,
        delivery_address,
        delivery_comment,
        customer_type,
        vat_number,
        tax_info_1,
        company_name,
        company_address,
        payment_method
        ")) return false;

        $order_data->payment_method=(int)$order_data->payment_method;
        $order_data->delivery_type=(int)$order_data->delivery_type;
        $order_data->delivery_var_id=(int)$order_data->delivery_var_id;

        if($this->uSes->access(2)&&!$this->uSes->access(25)) {
            $user_id=$this->uSes->get_val("user_id");
            $preferences_ar["user_name"]=$order_data->user_name;
            $preferences_ar["user_email"]=$order_data->user_email;
            $preferences_ar["user_phone"]=$order_data->user_phone;
            $preferences_ar["delivery_type"]=$order_data->delivery_type;
            if($order_data->delivery_address!="") $preferences_ar["delivery_addr"]=$order_data->delivery_address;
            if($order_data->delivery_comment!="") $preferences_ar["delivery_comment"]=$order_data->delivery_comment;
            $preferences_ar["customer_type"]=$order_data->customer_type;
            if((int)$order_data->vat_number) $preferences_ar["vat_number"]=$order_data->vat_number;
            if($order_data->company_name!="") $preferences_ar["company_name"]=$order_data->company_name;
            if((int)$order_data->tax_info_1) $preferences_ar["tax_info1"]=$order_data->tax_info_1;
            if($order_data->company_address!="") $preferences_ar["company_addr"]=$order_data->company_address;
            $preferences_ar["payment_method"]=$order_data->payment_method;

            $this->uCat->save_user_preferences($user_id,$preferences_ar);
        }


        $total_price=$this->uCat->order_count_items_price($this->order_id);

        $manager_must_confirm=0;
        if($delivery_var_data=$this->uCat->delivery_point_variant_id2data($order_data->delivery_var_id,"manager_must_confirm,manager_sets_delivery_price")) {
            if((int)$delivery_var_data->manager_must_confirm||(int)$delivery_var_data->manager_sets_delivery_price) $manager_must_confirm=1;
        }

        if(
        $manager_must_confirm||
        $this->uCat->order_has_avail5_items($this->order_id)||
        $this->uCat->order_has_inaccurate_price_items($this->order_id)||
        (!$this->uCat->order_has_real_items($this->order_id)&&(int)$this->uFunc->getConf("order_nopickup_force_confirm",'uCat'))||
        (!$this->uCat->site_has_delivery_types()&&(int)$this->uFunc->getConf("order_nopickup_force_confirm",'uCat'))
        )
        $order_status="order is confirmed";
        else $order_status="order is processed";

        if($order_data->payment_method===3&&$order_status==="order is processed") $bill_number=$this->create_bill($order_data);
        else $bill_number=0;

        if(!$this->uSes->access(2)||$this->uSes->access(25)) $user_id=$this->register_new_user($order_data->user_email,$order_data->user_name);
        else $user_id=$this->uSes->get_val("user_id");

        $this->uCat->order_update($this->order_id,$update_ar=[
            ['total_price',$total_price,PDO::PARAM_STR],
            ['order_status',$order_status,PDO::PARAM_STR],
            ['bill_number',$bill_number,PDO::PARAM_INT],
            ['user_id',$user_id,PDO::PARAM_INT]
        ],"",site_id,1588599030);

        //SEND NOTIFICATIONS
        $this->uCat->notify_about_order_change($this->order_id,$order_status,"items selected");

        echo json_encode(array(
            "status"=>"success",
            "order_id"=>$this->order_id,
            "user_email"=>$order_data->user_email
        ));

        return true;
    }

    private function update_order_status() {
        if(!isset($_POST['order_status'])) $this->uFunc->error(30);
        $order_status=$_POST['order_status'];

        $order_info=$this->uCat->order_id2data($this->order_id,"order_status,user_email,user_name,user_id");

        if($order_status==="processed") $order_status="order is processed";
        elseif($order_status==="paid") $order_status="order has been paid";
        elseif($order_status==="delivering") $order_status="awaiting delivery";
        elseif($order_status==="complete") $order_status="order completed";
        elseif($order_status==="cancel") $order_status="order canceled";
        else $this->uFunc->error(40);

        if($order_status=="order is processed") {
            if(!$this->uSes->access(25)) {
                echo json_encode(array("status"=>"forbidden"));
                exit;
            }
            if(
                $order_info->order_status!=="order is confirmed"&&
                $order_info->order_status!=="items selected"
            ) {
                echo json_encode(array("status"=>"forbidden"));
                exit;
            }
            $this->uCat->notify_about_order_change($this->order_id,"order is processed",$order_info->order_status);

            //REGISTERING A NEW USER
            if($order_info->order_status==="items selected") {
                if(!(int)$order_info->user_id) {
                    if($user_id=$this->register_new_user($order_info->user_email,$order_info->user_name)) {
                        $this->uCat->order_update($this->order_id,[['user_id',$user_id, PDO::PARAM_INT]],"",site_id,1588599104);
                    }
                }
            }
        }
        elseif($order_status=="order has been paid") {
            if(!$this->uSes->access(25)) {
                echo json_encode(array("status"=>"forbidden"));
                exit;
            }
            if(
                $order_info->order_status!=="order is processed"&&
                $order_info->order_status!=="waiting payment"&&
                $order_info->order_status!=="order is confirmed"&&
                $order_info->order_status!=="items selected"
            ) {
                echo json_encode(array("status"=>"forbidden"));
                exit;
            }
            $this->uCat->order_update($this->order_id,[['order_paid',1, PDO::PARAM_INT]],"",site_id,1588599110);
            $this->uCat->notify_about_order_change($this->order_id,"order has been paid",$order_info->order_status);
        }
        elseif($order_status=="awaiting delivery") {
            if(!$this->uSes->access(25)) {
                echo json_encode(array("status"=>"forbidden"));
                exit;
            }
            if(
                $order_info->order_status!=="order has been paid"&&
                $order_info->order_status!=="order is processed"&&
                $order_info->order_status!=="waiting payment"&&
                $order_info->order_status!=="order is confirmed"&&
                $order_info->order_status!=="items selected"
            ) {
                echo json_encode(array("status"=>"forbidden"));
                exit;
            }

            $this->uCat->notify_about_order_change($this->order_id,"awaiting delivery",$order_info->order_status);

        }
        elseif($order_status=="order completed") {
            if(!$this->uSes->access(25)) {
                echo json_encode(array("status"=>"forbidden"));
                exit;
            }
            if(
                $order_info->order_status!=="awaiting delivery"&&
                $order_info->order_status!=="order has been paid"&&
                $order_info->order_status!=="order is processed"&&
                $order_info->order_status!=="waiting payment"&&
                $order_info->order_status!=="order is confirmed"&&
                $order_info->order_status!=="items selected"
            ) {
                echo json_encode(array("status"=>"forbidden"));
                exit;
            }
            $this->uCat->order_update($this->order_id,[['order_paid',1, PDO::PARAM_INT]],"",site_id,1588599116);
            $this->uCat->notify_about_order_change($this->order_id,"order completed",$order_info->order_status);
        }
        elseif($order_status=="order canceled") {
            if(!$this->uSes->access(2)) {
                echo json_encode(array("status"=>"forbidden"));
                exit;
            }
            if(
                $order_info->order_status!=="awaiting delivery"&&
                $order_info->order_status!=="order has been paid"&&
                $order_info->order_status!=="order is processed"&&
                $order_info->order_status!=="waiting payment"&&
                $order_info->order_status!=="order is confirmed"&&
                $order_info->order_status!=="items selected"&&
                !$this->uSes->access(25)
            ) {
                echo json_encode(array("status"=>"forbidden"));
                exit;
            }
            if((int)$order_info->user_id!==$this->uSes->get_val("user_id")&&!$this->uSes->access(25)) $this->uFunc->error(0);
            $this->uCat->notify_about_order_change($this->order_id,"order canceled",$order_info->order_status);
        }


        $this->uCat->order_update($this->order_id,[['order_status',$order_status, PDO::PARAM_STR]],"",site_id,1588599124);

        echo '{"status":"done"}';
        exit;
    }


    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uCat=new common($this->uCore);
        $this->uSes=new uSes($this->uCore);

        $this->check_data();

        if(isset($_POST["user_email"])) $this->update_order_user_email();
        elseif(isset($_POST["confirm_order"])) $this->update_order_confirm();//Самым первым!!!!
        elseif(isset($_POST["user_phone"])) $this->update_order_user_phone();
        elseif(isset($_POST["user_name"])) $this->update_order_user_name();
        elseif(isset($_POST["user_name"])) $this->update_order_user_name();
        elseif(isset($_POST["delivery_address"])) $this->update_order_delivery_address();
        elseif(isset($_POST["delivery_comment"])) $this->update_order_delivery_comment();
        elseif(isset($_POST["delivery_var_id"])) $this->update_order_delivery_var_id();//Обязательно должен идти после delivery_address и delivery_comment иначе они не отработают. delivery_var_id просто есть в ajax-запросах с delivery_address и comment
        elseif(isset($_POST["customer_type"])) $this->update_order_customer_type();
        elseif(isset($_POST["company_name"])) $this->update_order_company_info();
        elseif(isset($_POST["payment_method"])) $this->update_order_payment_method();
        elseif(isset($_POST["order_status"])) $this->update_order_status();
        else {
            echo json_encode(array("status"=>"error","msg"=>"unknown field"));
            exit;
        }
    }
}
new cart_update_bg($this);
