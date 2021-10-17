<?php
require_once 'inc/item_img.php';
class uCat_admin_items_imagelist_ajax {
    private $uCore;
    public $item_id,$q_files,$item_img;
    private function check_data() {
        if(!isset($_POST['item_id'])) $this->uCore->error(1);
        $this->item_id=$_POST['item_id'];
        if(!uString::isDigits($this->item_id)) $this->uCore->error(2);
    }
    private function getFiles() {
        if(!$this->q_files=$this->uCore->query('uCat',"SELECT
        `img_id`,
        `timestamp`
        FROM
        `u235_items_pictures`
        WHERE
        `item_id`='".$this->item_id."' AND
        `site_id`='".site_id."'
        ORDER BY
        `img_id` DESC
        ")) $this->uCore->error(3);
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(25)) die('forbidden');

        $this->check_data();
        $this->getFiles();

        $this->item_img=new uCat_item_img($this->uCore);
    }
}
$uCat=new uCat_admin_items_imagelist_ajax ($this);?>
<div class="row">
<?
if(mysqli_num_rows($uCat->q_files)) {
    while($img=$uCat->q_files->fetch_object()) {
        $orig=$uCat->item_img->get_img('orig',$uCat->item_id,$img->img_id,$img->timestamp);
        $sm=$uCat->item_img->get_img('item_page',$uCat->item_id,$img->img_id,$img->timestamp);
        if($orig&&$sm) {?>
        <div class="col-sm-3 col-md-2" id="uCat_in_place_fManager_file_<?=$img->img_id?>" onclick="uCat.item_images_handle_file(<?=$img->img_id?>,<?=$img->timestamp?>)">
            <div class="thumbnail fancybox" rel="uCat_in_place_images_file_manager" href="<?=$orig?>"><img src="<?=$sm?>"></div>
        </div>
        <?}
    }
}
else {?>
    <div class="well">Пока изображений нет</div>
    <?}?>
</div>
