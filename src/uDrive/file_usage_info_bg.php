<?php
class uDrive_file_usage_info {
    private $uCore,$file_id;
    public $q_files_usage;
    private function check_data() {
        if(!isset($_POST['file_id'])) $this->uCore->error(1);
        $this->file_id=$_POST['file_id'];
        if(!uString::isDigits($this->file_id)) $this->uCore->error(2);
        $this->file_id=(int)$this->file_id;
    }
    private function get_file_usage() {
        if(!$this->q_files_usage=$this->uCore->query("uDrive","SELECT
        `file_mod`,
        `handler_type`,
        `handler_id`
        FROM
        `u235_files_usage`
        WHERE
        `file_id`='".$this->file_id."' AND
        `site_id`='".site_id."'
        ORDER BY
        `file_mod` ASC,
        `handler_type` ASC,
        `handler_id` ASC
        ")) $this->uCore->error(3);
        if(!mysqli_num_rows($this->q_files_usage)) {//update that file is not used anywhere
            if(!$this->uCore->query("uDrive","UPDATE
            `u235_files`
            SET
            `file_is_used`='0'
            WHERE
            `file_id`='".$this->file_id."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(4);
        }
    }

    public function get_uCat_sect_title($sect_id) {
        if(!$query=$this->uCore->query("uCat","SELECT
        `sect_title`
        FROM
        `u235_sects`
        WHERE
        `sect_id`='".$sect_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(5);
        if(!mysqli_num_rows($query)) return $this->text('sect is not found'/*'Раздел не найден'*/);
        $qr=$query->fetch_object();
        return uString::sql2text($qr->sect_title,1);
    }
    public function get_uCat_cat_title($cat_id) {
        if(!$query=$this->uCore->query("uCat","SELECT
        `cat_title`
        FROM
        `u235_cats`
        WHERE
        `cat_id`='".$cat_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(5);
        if(!mysqli_num_rows($query)) return $this->text('cat is not found'/*'Категория не найдена'*/);
        $qr=$query->fetch_object();
        return uString::sql2text($qr->cat_title,1);
    }
    public function get_uCat_item_title($item_id) {
        if(!$query=$this->uCore->query("uCat","SELECT
        `item_title`
        FROM
        `u235_items`
        WHERE
        `item_id`='".$item_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(5);
        if(!mysqli_num_rows($query)) return $this->text('item is not found'/*'Товар не найден'*/);
        $qr=$query->fetch_object();
        return uString::sql2text($qr->item_title,1);
    }
    public function get_uCat_art_title($art_id) {
        if(!$query=$this->uCore->query("uCat","SELECT
        `art_title`
        FROM
        `u235_articles`
        WHERE
        `art_id`='".$art_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(5);
        if(!mysqli_num_rows($query)) return $this->text('art is not found'/*'Статья не найдена'*/);
        $qr=$query->fetch_object();
        return uString::sql2text($qr->art_title,1);
    }


    public function text($text) {
        return $this->uCore->text(array($this->uCore->mod,$this->uCore->page_name),$text);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(1900)) die("forbidden");

        $this->check_data();
        $this->get_file_usage();
    }
}
$uDrive=new uDrive_file_usage_info($this);
ob_start();

if(mysqli_num_rows($uDrive->q_files_usage)) {
?>

<ul style="list-style: none; margin-left: 0; padding: 0;"><?
    $cur_mod=$cur_handler='';
    $mod_lvl=$handler_lvl=0;
    while($file_usage=$uDrive->q_files_usage->fetch_object()) {
        if($file_usage->file_mod=='uCat') {
            if($file_usage->handler_type=='sect') {
                $handler_name=$uDrive->get_uCat_sect_title($file_usage->handler_id);
                $handler_url=u_sroot.'uCat/cats/'.$file_usage->handler_id;
            }
            elseif($file_usage->handler_type=='cat') {

                $handler_name=$uDrive->get_uCat_cat_title($file_usage->handler_id);
                $handler_url=u_sroot.'uCat/items/'.$file_usage->handler_id;
            }
            elseif($file_usage->handler_type=='item') {
                $handler_name=$uDrive->get_uCat_item_title($file_usage->handler_id);
                $handler_url=u_sroot.'uCat/item/'.$file_usage->handler_id;
            }
            elseif($file_usage->handler_type=='art') {
                $handler_name=$uDrive->get_uCat_art_title($file_usage->handler_id);
                $handler_url=u_sroot.'uCat/art/'.$file_usage->handler_id;
            }
            else {
                $handler_name=$handler_url='';
            }
        }
        else {
            $handler_name=$handler_url='';
        }
        if($cur_mod!=$file_usage->file_mod) {
            if($mod_lvl==0) {
                $mod_lvl=1;
            } else {?>
                </ul></li>
                </ul></li>
            <?}?>
            <li><h3><?=$uDrive->text($file_usage->file_mod)?></h3>
            <ul style="list-style: none; margin-left: 10px; padding: 0;">
        <?if($cur_handler!=$file_usage->handler_type) {
                if($handler_lvl==0) {
                    $handler_lvl=1;
                }
                else {?>
                    </ul></li>
                <?}?>
                <li><h4><?=$uDrive->text($file_usage->handler_type)?></h4>
                <ul style="list-style: none; margin-left: 10px; padding: 0;">
            <?}
        }?>
        <li><a target="_blank" href="<?=$handler_url?>"><?=$handler_name?></a></li>
    <?}
    ?></ul></li></ul></li></ul>

<?}
else {?>
    <p><?=$this->text('file is used nowhere'/*'Похоже, что файл нигде не используется.'*/)?></p>
<?}?>
<div class="bs-callout bs-callout-default"><?=$uDrive->text('file usage explanation'/*'Информация об использовании файла берется на основе его вставки в редакторы и на страницы сайта во время редактирования. Эта информация может быть не точной, однако рекомендуем проверять, чтобы файлы нигде не использовались, перед удалением, чтобы избежать битых ссылок и ошибок.'*/)?></div>
<?
$this->page_content=ob_get_contents();
ob_end_clean();
echo $this->page_content;
