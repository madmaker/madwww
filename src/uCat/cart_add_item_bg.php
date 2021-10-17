<?php
namespace uCat\cart;

use PDO;
use processors\uFunc;
use stdClass;
use uSes;
use uString;
use uCat\common;

require_once 'processors/uSes.php';
require_once 'processors/classes/uFunc.php';
require_once 'uCat/classes/common.php';

class add_item {
    public $order_status;
    /**
     * @var array
     */
    public $descr_field_title;
    private $order_id;
    private $item_id;
    private $uCat;
    private $uSes;
    private $uFunc;
    private $item_article_number,$var_id;
    private $quantity_switcher,$equal_quantity,$quantity_in_cart,$quantity_in_market,$unit_item;
    public $uCore;
    private function check_data() {
        if(isset($_POST['item_article_number'])) {
            $this->item_article_number=trim($_POST['item_article_number']);
            //may be multiple items divided by comma
            $items_ar=explode(',',$this->item_article_number);
            for($i=0;$i<count($items_ar);$i++) {
                $this->var_id=0;
                $item=trim($items_ar[$i]);
                    $this->item_article_number=$item;

                if(!$ar=$this->uCat->var_article_number_exists($this->item_article_number)) {
                    if(!$this->item_id=$this->uCat->item_article_number_exists($this->item_article_number)) continue;
                        $this->var_id=0;
                    }
                else {
                    $this->item_id = $ar->item_id;
                    $this->var_id = $ar->var_id;
                }

                if($this->quantity_switcher) {
                    $this->quantity_in_cart = (int)$this->uCat->get_item_quantity_in_order($this->order_id,$this->item_id,$this->var_id);
                    $this->quantity_in_market = (int)$this->uCat->get_item_quantity($this->item_id,$this->var_id);

                    if($this->quantity_in_cart < $this->quantity_in_market) $this->uCat->order_add_item($this->order_id, $this->item_id,$this->var_id);
                    else $this->equal_quantity = 1;

                    $this->unit_item = $this->uCat->unit_of_item($this->item_id);
                }
                else $this->uCat->order_add_item($this->order_id, $this->item_id,$this->var_id);
            }
            return 1;
        }
        elseif(isset($_POST['item_id'])) {
            $this->item_id=$_POST['item_id'];

            if($this->item_id=='delete_all') {
                $this->uCat->order_delete_item($this->order_id);
                return 1;
            }

            $this->item_id=(int)$this->item_id;

            if(isset($_POST['var_id'])) $this->var_id=(int)$_POST['var_id'];
            else $this->var_id=0;

            if(isset($_POST['delete'])) {
                $this->uCat->order_delete_item($this->order_id,$this->item_id,$this->var_id);
                return 1;
            }

            if($this->var_id) {
                if(!$this->uCat->var_exists($this->var_id)) return 0;

                if($this->quantity_switcher) {
                    $this->quantity_in_cart = (int)$this->uCat->get_item_quantity_in_order($this->order_id,$this->item_id,$this->var_id);
                    $this->quantity_in_market = (int)$this->uCat->get_item_quantity($this->item_id, $this->var_id);
                    if(!$this->quantity_in_cart) $this->quantity_in_cart = 0;

                    if($this->quantity_in_cart < $this->quantity_in_market) {
                        if(isset($_POST['quantity'])) $this->uCat->order_add_item($this->order_id, $this->item_id, $this->var_id,$_POST['quantity']);
                        else $this->uCat->order_add_item($this->order_id, $this->item_id, $this->var_id);
                    }
                    else $this->equal_quantity = 1;
                }
                else {
                    if(isset($_POST['quantity'])) $this->uCat->order_add_item($this->order_id, $this->item_id, $this->var_id,$_POST['quantity']);
                    else $this->uCat->order_add_item($this->order_id, $this->item_id, $this->var_id);
                }

                $this->unit_item = $this->uCat->unit_of_item($this->item_id);
            }
            else {
                if(!$this->uCat->item_exists($this->item_id)) return 0;

                if($this->quantity_switcher) {
                    $this->quantity_in_cart = (int)$this->uCat->get_item_quantity_in_order($this->order_id,$this->item_id);
                    $this->quantity_in_market = (int)$this->uCat->get_item_quantity($this->item_id, $this->var_id);

                    if($this->quantity_in_cart < $this->quantity_in_market) {
                        if(isset($_POST['quantity'])) $this->uCat->order_add_item($this->order_id, $this->item_id, $this->var_id,$_POST['quantity']);
                        else $this->uCat->order_add_item($this->order_id, $this->item_id, $this->var_id);
                    }
                    else $this->equal_quantity = 1;
                }
                else {
                    if(isset($_POST['quantity'])) $this->uCat->order_add_item($this->order_id, $this->item_id, $this->var_id,$_POST['quantity']);
                    else $this->uCat->order_add_item($this->order_id, $this->item_id, $this->var_id);
                }

                $this->unit_item = $this->uCat->unit_of_item($this->item_id);
            }
        }
        else {
            if(isset($_POST['change_item_count'])) {
                $this->change_item_count();
            }
            else {
                $this->uFunc->error(10,1);
            }
        }
        return 0;
    }

    private function get_cart_items() {
        $uCat=new stdClass();
        $uCat->uCore=&$this->uCore;
        $uCat->uCat=&$this->uCat;
        $uCat->uFunc=&$this->uFunc;
//        $order_id=$this->uCat->get_order_id($this->uSes->get_val("ses_id"));
        $uCat->order_status=$this->uCat->order_id2data($this->order_id,'order_status')->order_status;

        $q_fields="";
        $uCat->descr_field=[];
        $uCat->descr_field_title=[];
        if($fields=$this->uCat->get_show_in_cart_fields()) {
            /** @noinspection PhpUndefinedMethodInspection */
            for($i=0; $field=$fields->fetch(PDO::FETCH_OBJ); $i++) {
                $q_fields.="field_".$field->field_id.",";
                $uCat->descr_field[$i]=(int)$field->field_id;
                $uCat->descr_field_title[$i]= uString::sql2text($field->field_title);
            }
        }

        $q_items=$this->uCat->get_order_items($this->order_id,
            $q_fields."
        orders_items.item_id,
        item_article_number,
        var_id,
        u235_items.item_type,
        item_count");

        ob_start();
        /** @noinspection PhpUndefinedMethodInspection */
        if($item=$q_items->fetch(PDO::FETCH_OBJ)) {
            echo '<script type="text/javascript">
                if(typeof uCat_cart==="undefined") uCat_cart={};
                uCat_cart.order_id='.$this->order_id.';
            </script>';
            include 'uCat/templates/cart/cart_content.php';
        }
        else {
            include 'uCat/templates/cart/cart_content_empty.php';
        }
        $cart_content=ob_get_contents();
        ob_end_clean();
        return $cart_content;
    }
    private function get_cart_total() {
        $total_count=$total_price=0;
        $q_items=$this->uCat->order_get_every_item_count($this->order_id);

        /** @noinspection PhpUndefinedMethodInspection */
        while($item=$q_items->fetch(PDO::FETCH_OBJ)) {
            $item_price=$this->uCat->get_item_price($this->order_id,$item->item_id,(int)$item->var_id);
            $total_count+=$item->item_count;
            $total_price+=$item->item_count*$item_price;
        }

        if(isset($_POST['update_cart_page'])) $cart_content=$this->get_cart_items();
        else $cart_content='';

        return array($total_count,$total_price,$cart_content);
    }

    private function change_item_count() {
        //get cart's items
        if(!isset($_POST['request'])) $this->uFunc->error(90,1);
        $request=json_decode($_POST['request'],1);

        for($i=0;isset($request[$i]);$i++) {
            $item_id=$request[$i]['item_id'];
            $var_id=$request[$i]['var_id'];
            $count=$request[$i]['count'];
            if(!uString::isDigits($item_id)) continue;
            if(!uString::isDigits($var_id)) continue;
            if(!uString::isDigits($count)) continue;

            $this->uCat->order_set_item_count($this->order_id,$item_id,$count,$var_id);
        }
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;

        $this->uCat=new common($this->uCore);
        $this->uSes=new uSes($this->uCore);
        $this->uFunc=new uFunc($this->uCore);


        if(isset($_POST["order_id"])) {
            $order_info=$this->uCat->order_id2data($_POST["order_id"],"order_status");

            $order_status=$order_info->order_status;

            if($this->uSes->access(25)) {
                if (
                    $order_status === "new"||
                    $order_status === "items selected"||
                    $order_status === "order is processed"||
                    $order_status === "order is confirmed"
                ) $this->order_id=(int)$_POST["order_id"];
                elseif (
                    $order_status !== "order completed"&&
                    $order_status !== "order canceled"
                ) $this->order_id=(int)$_POST["order_id"];
            }
            else {
                if (
                    $order_status === "new"||
                    $order_status === "items selected"||
                    $order_status === "order is processed"||
                    $order_status === "order is confirmed"
                ) $this->order_id=(int)$_POST["order_id"];
            }
        }

        if(!isset($this->order_id)) $this->order_id=$this->uCat->get_order_id();


        $this->quantity_switcher = (int)$this->uFunc->getConf('item_quantity_show','uCat');
        $this->equal_quantity=0;
        $this->quantity_in_market=0;
        $this->unit_item = "";

        if(!isset($_POST['check'])) {
            $this->check_data();
        }
        $total=$this->get_cart_total();


        echo json_encode(array("status"=>"done",
        "count"=>$total[0],
        "price"=>$total[1],
        "equal_quantity"=>$this->equal_quantity,
        "quantity"=>$this->quantity_in_market,
        "unit"=>$this->unit_item,
        "item_id"=>$this->item_id,
        "var_id"=>$this->var_id,
        "cart_content"=>$total[2]));
        exit;
    }
}
new add_item($this);
