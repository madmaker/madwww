<?php
class uForms_form_admin_filelist_ajax {
    private $uCore;
    public $form_id,$q_files;

    public function text($str) {
        return $this->uCore->text(array('uForms','form_admin_filelist_ajax'),$str);
    }

    private function check_data() {
        if(!isset($_POST['form_id'])) $this->uCore->error(1);
        $this->form_id=$_POST['form_id'];
        if(!uString::isDigits($this->form_id)) $this->uCore->error(2);
    }
    private function getFiles() {
        if(!$this->q_files=$this->uCore->query('uForms',"SELECT
        `file_id`,
        `file_name`,
        `timestamp`
        FROM
        `u235_forms_files`
        WHERE
        `form_id`='".$this->form_id."' AND
        `site_id`='".site_id."'
        ORDER BY
        `file_id` DESC
        ")) $this->uCore->error(3);
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(5)) die('forbidden');

        $this->check_data();
        $this->getFiles();
    }
}
$uForms=new uForms_form_admin_filelist_ajax($this);

ob_start();

if(mysqli_num_rows($uForms->q_files)) {
    $height=$this->uFunc->getConf("img_thumb_height","content")+50;?>
    <div class="row">
        <?while($file=$uForms->q_files->fetch_object()) {
            $file_name=$file->file_name;
            $file_id=$file->file_id;
            $dotPos=stripos($file_name,'.');
            $fileExt=strtolower(substr($file_name,$dotPos));
            ?>
            <div class="col-sm-3 col-md-2" id="uForms_in_place_fManager_file_<?=$file_id?>" onclick="uForms.insertTinyMCEUrl('<?=u_sroot.'uForms/form_files/'.site_id.'/'.$uForms->form_id.'/'.$file_id.'/'.$file_name;?>',<?=$file_id?>,'<?=$fileExt;?>')">
                <div class="thumbnail fancybox uTooltip" title="<?=$file_name?>" href="<?=u_sroot.'uForms/form_files/'.site_id.'/'.$uForms->form_id.'/'.$file_id.'/'.$file_name.'?'.$file->timestamp;?>" style="overflow:hidden; height:<?=$height?>px;" rel="fancybox_uploade"><img src="<?=u_sroot;?><?
                    $image_f_types=array('.jpg','.jpeg','.png','.gif','.tiff');
                    $known_f_types=array('.ai','.eps','.cdr','.svg','.app','.avi','.fla','.flv','.mpv','.mp4','.swf','.wmv','.css','.doc','.docx','.pages','.rtf','.txt','.exe','.html','.htm','.js','.php','.xml','.indd','.jpg','.bmp','.raw','.tiff','.mp3','.acc','.aif','.numbers','.sql','.xls','.xlsx','.pdf','.png','.gif','.rss','.atom','.ttf','.otf','.zip','.7z','.gzip');
                    if(in_array($fileExt,$image_f_types)) echo 'uForms/form_files/'.site_id.'/'.$uForms->form_id.'/'.$file_id.'/'.$file_id.'_sm.jpg?'.$file->timestamp;
                    elseif(in_array($fileExt,$known_f_types)) echo 'uEditor/file_icons/'.str_replace('.','',$fileExt).'.png';
                    else echo 'uEditor/file_icons/other.png';
                    ?>">
                    <div class="caption"><?=mb_substr($file_name,0,15,'utf-8')?></div>
                </div>
            </div>
        <?
        }?>
    </div>
<?}
else {?>
    <p><?=$uForms->text("No files found - msg"/*Пока файлов нет*/)?></p>
<?}?>

<?
$this->page_content=ob_get_contents();
ob_end_clean();
echo $this->page_content;
