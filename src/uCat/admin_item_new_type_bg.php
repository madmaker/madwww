<?php
class uCat_admin_item_new_type {
    private $uCore,$type_title,$base_type_id,$type_id;
    private function check_data() {
        if(!isset($_POST['type_title'],$_POST['base_type_id'])) $this->uCore->error(10);

        $this->base_type_id=(int)$_POST['base_type_id'];
        if($this->base_type_id!=0&&$this->base_type_id!=1) $this->uCore->error(20);

        $this->type_title=trim($_POST['type_title']);
        if(!strlen($this->type_title)) die("{'status':'error','msg':'title_is_empty'}");
    }
    private function create_new_item_type() {
        //get new type_id
        if(!$query=$this->uCore->query("uCat","SELECT
        `type_id`
        FROM
        `items_types`
        WHERE
        `site_id`='".site_id."'
        ORDER BY
        `type_id` DESC
        LIMIT 1
        ")) $this->uCore->error(30);
        if(mysqli_num_rows($query)) {
            $qr=$query->fetch_object();
            $this->type_id=$qr->type_id+1;
        }
        else $this->type_id=1;

        if(!$this->uCore->query("uCat","INSERT INTO
        `items_types` (
        `base_type_id`,
        `type_id`,
        `type_title`,
        `site_id`
        ) VALUES (
        '".$this->base_type_id."',
        '".$this->type_id."',
        '".uString::sql2text($this->type_title)."',
        '".site_id."'
        )
        ")) $this->uCore->error(40);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(25)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->create_new_item_type();

        echo "{
        'status':'done',
        'type_id':'".$this->type_id."',
        'type_title':'".rawurlencode($this->type_title)."'
        }";
    }
}
$uCat=new uCat_admin_item_new_type($this);