<?php
class uViblog_admin_list_filelist_ajax {
    private $uCore;
    public $q_files;
    private function getFiles() {
        if(!$this->q_files=$this->uCore->query("uViblog","SELECT
        `file_id`,
        `file_name`
        FROM
        `u235_descr_files`
        WHERE
        `site_id`='".site_id."'
        ORDER BY
        `file_id` DESC
        ")) $this->uCore->error(3);
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(4)) die('forbidden');

        $this->getFiles();
    }
}
$uViblog=new uViblog_admin_list_filelist_ajax ($this);

ob_start();
?>
<ul>
<?
if(mysqli_num_rows($uViblog->q_files)) {
    while($data=$uViblog->q_files->fetch_assoc()) {
        $filename=&$data['file_name'];
        $dotPos=stripos($filename,'.');
        $fileExt=strtolower(substr($filename,$dotPos));
        ?>
        <li id="uViblog_in_place_fManager_file_<? echo $data['file_id'];?>" onclick="uViblog.insertTinyMCEUrl('<?=u_sroot.'uViblog/descr_files/'.site_id.'/'.$filename;?>',<? echo $data['file_id'];?>,'<?=$fileExt;?>')">
            <?
            ?>
            <span class="image fancybox" href="<?=u_sroot.'uViblog/descr_files/'.site_id.'/'.$filename;?>"><img src="<?=u_sroot;?><?
                $image_f_types=array('.jpg','.jpeg','.png','.gif','.tiff');
                $known_f_types=array('.ai','.eps','.cdr','.svg','.app','.avi','.fla','.flv','.mpv','.mp4','.swf','.wmv','.css','.doc','.docx','.pages','.rtf','.txt','.exe','.html','.htm','.js','.php','.xml','.indd','.jpg','.bmp','.raw','.tiff','.mp3','.acc','.aif','.numbers','.sql','.xls','.xlsx','.pdf','.png','.gif','.rss','.atom','.ttf','.otf','.zip','.7z','.gzip');
                if(in_array($fileExt,$image_f_types)) echo 'uViblog/descr_files/'.site_id.'/'.$filename;
                elseif(in_array($fileExt,$known_f_types)) echo 'uEditor/file_icons/'.str_replace('.','',$fileExt).'.png';
                else echo 'uEditor/file_icons/other.png';
            ?>"></span>
            <span class="filename"><? echo $data['file_name'];?></span>
        </li>
    <?
    }
}
else {?>
    <p>Пока файлов нет</p>
    <?}?>
</ul>

<?
$this->page_content=ob_get_contents();
ob_end_clean();
echo $this->page_content;
?>
