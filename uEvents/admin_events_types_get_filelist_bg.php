<?php
class uEvent_events_types_filelist {
    private $uCore;
    public $q_files,$type_id;
    private function checkData() {
        if(!isset($_POST['type_id'])) $this->uCore->error(1);
        $this->type_id=$_POST['type_id'];
        if(!uString::isDigits($this->type_id)) $this->uCore->error(2);
    }
    private function getFiles() {
        if(!$this->q_files=$this->uCore->query("uEvents","SELECT
        `file_id`,
        `file_name`,
        `file_name_hash`,
        `file_ext`
        FROM
        `u235_events_types_files`
        WHERE
        `type_id`='".$this->type_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(3);
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(300)) die('forbidden');
        $this->checkData();
        $this->getFiles();
    }

    public function text($str) {
        return $this->uCore->text(array('uEvents','admin_events_types_get_filelist_bg'),$str);
    }
}
$uEvent=new uEvent_events_types_filelist($this);

if(mysqli_num_rows($uEvent->q_files)) {
    $height=$this->uFunc->getConf("img_thumb_height","content")+50;?>
    <div class="row">
    <?while($data=$uEvent->q_files->fetch_assoc()) {
        $filename=$data['file_name'];
        $dotPos=stripos($filename,'.');
        $fileExt=$data['file_ext'];
        ?>
        <div class="col-sm-3 col-md-2" id="uEvents_events_type_fManager_file_<?=$data['file_id']?>" onclick="
            uEvents_events_admin.insertTinyMCEUrl('<?='uEvents/events_types_file/'.$data['file_id']?>',<?=$data['file_id'];?>,'<?=$fileExt?>')
            ">
            <div class="thumbnail fancybox uTooltip" title="<?=uString::sql2text($data['file_name'],1)?>" href="<?='uEvents/events_types_file/'.$data['file_id']?>" style="overflow:hidden; height:<?=$height?>px;"><img src="<?=u_sroot?><?
                $image_f_types=array('jpg','jpeg','png','gif','tiff');
                $known_f_types=array('.ai','.eps','.cdr','.svg','.app','.avi','.fla','.flv','.mpv','.mp4','.swf','.wmv','.css','.doc','.docx','.pages','.rtf','.txt','.exe','.html','.htm','.js','.php','.xml','.indd','.jpg','.bmp','.raw','.tiff','.mp3','.acc','.aif','.numbers','.sql','.xls','.xlsx','.pdf','.png','.gif','.rss','.atom','.ttf','.otf','.zip','.7z','.gzip');
                if(in_array($fileExt,$image_f_types)) echo 'uEvents/events_types_file/'.$data['file_id'].'/sm';
                elseif(in_array($fileExt,$known_f_types)) echo 'uEditor/file_icons/'.str_replace('.','',$fileExt).'.png';
                else echo 'uEditor/file_icons/other.png';
            ?>">
                <div class="caption" style="/*white-space:nowrap;*/"><?=mb_substr(uString::sql2text($data['file_name'],1),0,15,'utf-8')?></div>
            </div>
        </div>
    <?}?>
    </div>
<?}
else {?>
<p><?=$uEvents->text('There are no files yet'/*<p>Пока файлов нет</p>*/)?></p>
<?}
