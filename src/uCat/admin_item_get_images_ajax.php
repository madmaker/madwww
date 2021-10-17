<?php
require_once 'inc/item_img.php';
class uCat_admin_item_get_images_ajax {
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
$uCat=new uCat_admin_item_get_images_ajax ($this);

if(mysqli_num_rows($uCat->q_files)){?>
<div class="row">
    <div class="col-md-12">
        <div class="item_pictures">
            <h2><?=$this->uFunc->getConf("how to call item_images","uCat")?></h2>
            <div class="wrapper-with-margin">
                <div id="uCat_item_pictures_slider" class="owl-carousel">
                    <?
                    while($img=$uCat->q_files->fetch_object()) {
                        $orig=$uCat->item_img->get_img('orig',$uCat->item_id,$img->img_id,$img->timestamp);
                        $sm=$uCat->item_img->get_img('item_page',$uCat->item_id,$img->img_id,$img->timestamp);
                        if($orig&&$sm){?>
                        <div>
                            <a class="fancybox" rel="item_pictures" href="<?=$orig?>">
                                <img class="img" src="<?=$sm?>">
                            </a>
                        </div>
                        <?}?>
                    <?}?>
                </div>
            </div>
        </div>
    </div>
</div>
<?}?>
