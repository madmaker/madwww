<?php
class uCat_admin_items_picture_delete_bg {
    private $uCore;
    private $img_id,$item_id;
    private function checkData() {
        if(!isset($_POST['file_id'],$_POST['item_id'])) $this->uCore->error(1);
        $this->img_id=$_POST['file_id'];
        $this->item_id=$_POST['item_id'];
        if(!uString::isDigits($this->img_id)&&$this->img_id!='all') $this->uCore->error(2);
        if(!uString::isDigits($this->item_id)) $this->uCore->error(3);
    }
    private function delFile() {
        if($this->img_id!='all') {
            @uFunc::rmdir($this->uCore->mod.'/item_pictures/'.site_id.'/'.$this->item_id.'/'.$this->img_id);
            if(!$this->uCore->query('uCat',"DELETE FROM
            `u235_items_pictures`
            WHERE
            `img_id`='".$this->img_id."' AND
            `item_id`='".$this->item_id."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(4);
        }
        else {
            @uFunc::rmdir($this->uCore->mod.'/item_pictures/'.site_id.'/'.$this->item_id);
            if(!$this->uCore->query('uCat',"DELETE FROM
            `u235_items_pictures`
            WHERE
            `item_id`='".$this->item_id."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(6);
        }
        echo "{'status' : 'success'}";
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(25)) die("{'status' : 'forbidden'}");

        $this->checkData();
        $this->delFile();
    }
}
$uCat=new uCat_admin_items_picture_delete_bg ($this);
