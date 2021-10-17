<?php
class uEditor_page_edit_filelist_ajax {
    private $uCore;
    public $q_files,$page_id;

    public function text($str) {
        return $this->uCore->text(array('uEditor','page_edit_filelist_ajax'),$str);
    }

    private function checkData() {
        if(!isset($_POST['page_id'])) $this->uCore->error(1);
        $this->page_id=$_POST['page_id'];
        if(!uString::isDigits($this->page_id)) $this->uCore->error(2);
    }
    private function getFiles() {
        if(!$this->q_files=$this->uCore->query("pages","SELECT
        `file_id`,
        `file_name`
        FROM
        `u235_pages_files`
        WHERE `page_id`='".$this->page_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(3);
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(7)) die('forbidden');
        $this->checkData();
        $this->getFiles();
    }
}
$uEditor=new uEditor_page_edit_filelist_ajax($this);

ob_start();
if(mysqli_num_rows($uEditor->q_files)) {
    $height=$this->uFunc->getConf("img_thumb_height","content")+50;?>
    <div class="row">
    <?while($data=$uEditor->q_files->fetch_assoc()) {
        $filename=&$data['file_name'];
        $dotPos=stripos($filename,'.');
        $fileExt=strtolower(substr($filename,$dotPos));?>
        <div class="col-sm-3 col-md-2" id="uEditor_in_place_fManager_file_<?=$data['file_id']?>" onclick="<?if(isset($_POST['uPage'])) {?>
            uPage_setup_uPage.insertTinyMCEUrl('<?='uEditor/files/'.site_id.'/'.$uEditor->page_id.'/'.$data['file_name']?>',<?=$data['file_id'];?>,'<?=$fileExt?>')
            <?} else {?>
            uEditor.insertTinyMCEUrl('<?='uEditor/files/'.site_id.'/'.$uEditor->page_id.'/'.$data['file_name']?>',<?=$data['file_id'];?>,'<?=$fileExt?>')
            <?}?>">
            <div class="thumbnail fancybox uTooltip" title="<?=$data['file_name']?>" href="<?='uEditor/files/'.site_id.'/'.$uEditor->page_id.'/'.$data['file_name']?>" style="overflow:hidden; height:<?=$height?>px;"><img src="<?=u_sroot.$this->mod?><?
                $image_f_types=array('.jpg','.jpeg','.png','.gif','.tiff');
                $known_f_types=array('.ai','.eps','.cdr','.svg','.app','.avi','.fla','.flv','.mpv','.mp4','.swf','.wmv','.css','.doc','.docx','.pages','.rtf','.txt','.exe','.html','.htm','.js','.php','.xml','.indd','.jpg','.bmp','.raw','.tiff','.mp3','.acc','.aif','.numbers','.sql','.xls','.xlsx','.pdf','.png','.gif','.rss','.atom','.ttf','.otf','.zip','.7z','.gzip');
                if(in_array($fileExt,$image_f_types)) echo '/files/'.site_id.'/'.$uEditor->page_id.'/'.$filename;
                elseif(in_array($fileExt,$known_f_types)) echo '/file_icons/'.str_replace('.','',$fileExt).'.png';
                else echo '/file_icons/other.png';
            ?>">
                <div class="caption" style="/*white-space:nowrap;*/"><?=mb_substr($data['file_name'],0,15,'utf-8')?></div>
            </div>
        </div>
    <?}?>
    </div>
<?}
else {?>
    <p><?=$uEditor->text("Files are not found"/*Пока файлов нет*/)?></p>
    <?}?>

<?
$this->page_content=ob_get_contents();
ob_end_clean();
echo $this->page_content;
?>
