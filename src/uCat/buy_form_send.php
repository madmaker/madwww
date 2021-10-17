<?php

namespace uCat\cart;

use HTMLPurifier;
use HTMLPurifier_Config;
use PDO;
use PDOException;
use processors\uFunc;
use uCat\common;
use uString;

require_once 'processors/classes/uFunc.php';
require_once 'uCat/classes/common.php';
//require_once 'lib/htmlpurifier/library/HTMLPurifier.auto.php';

class buy_form_send{
    /**
     * @var HTMLPurifier
     */
    private $purifier;
    /**
     * @var common
     */
    private $uCat;
    /**
     * @var uFunc
     */
    private $uFunc;
    private $order_id,$user_name,$user_email,$item_title,$item_id,$comment;
    private $var_id;

    private function checkData() {
        if(!isset($_POST['user_name'],$_POST['user_email'],$_POST['item_id'],$_POST['var_id'],$_POST['comment'])) {
            $this->uFunc->error(10);
        }
        $this->user_name=$this->purifier->purify(htmlspecialchars(trim($_POST['user_name'])));
        $this->user_email=$this->purifier->purify(htmlspecialchars(trim($_POST['user_email'])));
        if(!uString::isDigits($_POST['item_id'])) {
            $this->uFunc->error(11);
        }
        $this->item_id=(int)$_POST['item_id'];
        if(!uString::isDigits($_POST['var_id'])) {
            $this->uFunc->error(12);
        }
        $this->var_id=(int)$_POST['var_id'];
        $this->comment=$this->purifier->purify(htmlspecialchars(trim($_POST['comment'])));
    }
    private function define_item_title() {
        $item=$this->uCat->item_id2data($this->item_id, 'item_title');
        if(!$item) {
            $this->item_title = 'Товар не найден';
        }
        else {
            $this->item_title = $this->uCat->item_id2data($this->item_id, 'item_title')->item_title;
        }
        if($this->var_id) {
            $var_data=$this->uCat->var_id2data($this->var_id);
            if($var_data) {
                $this->item_title .= ' (вариант ' . $this->uCat->var_id2data($this->var_id)->var_type_title . ')';
            }
        }
    }
    private function saveOrder() {
        //get new order id
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo('uCat')->prepare('SELECT 
            order_id
            FROM 
            u235_buy_form_orders
            WHERE 
            site_id=:site_id
            ORDER BY 
            order_id DESC
            LIMIT 1
            ');
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($result=$stm->fetch(PDO::FETCH_OBJ)) {
                $this->order_id = $result->order_id + 1;
            }
            else {
                $this->order_id = 1;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo('uCat')->prepare('INSERT INTO
            u235_buy_form_orders (
            order_id,
            user_name,
            user_email,
            item_id,
            item_title,
            comment,
            timestamp,
            site_id
            ) VALUES (
            :order_id,
            :user_name,
            :user_email,
            :item_id,
            :item_title,
            :comment,
            :timestamp,
            :site_id
            )
            ');
            $site_id=site_id;
            $timestamp=time();
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':order_id', $this->order_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_name', $this->user_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_email', $this->user_email,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_title', $this->item_title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':comment', $this->comment,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':timestamp', $timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }
    private function sendNotification() {
        $html='<div class="msg_text">
            <h1>Новая заявка на покупку</h1>
            <p><label>#</label>:'.$this->order_id.'</p>
            <p><label>Имя клиента</label>:'.uString::sql2text($this->user_name).'</p>
            <p><label># товара</label>:<a href="'.u_sroot.'uCat/item/'.$this->item_id.'">'.$this->item_id.'</a></p>
            <p><label>Товар</label>:<a href="'.u_sroot.'uCat/item/'.$this->item_id.'">'.$this->item_title.'</a></p>
            <p><label>Email/телефон клиента</label>:'.$this->user_email.'</p>
            <p><label>Комментарий</label>:'.$this->comment.'</p>
            </div>
        ';
        $this->uFunc->sendMail($html,'Новая заявка на покупку',site_email);

        $this->user_email=uString::sql2text($this->user_email);
        if(uString::isEmail($this->user_email)) {
            $html='<div class="msg_text">
                <h1>Ваша заявка принята</h1>
                <p>Здравствуйте, '.$this->user_name.'</p>
                <p>Ваша заявка на покупку товара "'.$this->item_title.'" на сайте '.site_name.' принята.</p>
                <p>Спасибо за интерес, проявленный к нашей компании</p>
                <p>&nbsp;</p>
                <p><label>Номер заявки</label>:'.$this->order_id.'</p>
                <p><label>Название товара</label>:'.$this->item_title.'</p>
                <p><label>Email/телефон для связи</label>:'.$this->user_email.'</p>
                <p><label>Комментарий</label>:'.$this->comment.'</p>
                </div>
            ';
            $this->uFunc->sendMail($html,'Ваша заявка принята',$this->user_email);
        }
    }
    public function __construct (&$uCore) {
        $this->uFunc=new uFunc($uCore);
        $this->uCat=new common($uCore);
        $config = HTMLPurifier_Config::createDefault();
        $this->purifier = new HTMLPurifier($config);

        $this->checkData();
        $this->define_item_title();
        $this->saveOrder();
        $this->sendNotification();
        echo "{'status' : 'done'}";
    }
}
new buy_form_send($this);
