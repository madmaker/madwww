<?php
namespace uDrive;

use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once 'processors/classes/uFunc.php';
class my_drive{
    public $folder_id,$recycled;
    private $uCore;

    public function text($str) {
        return $this->uCore->text(array('uDrive','my_drive'),$str);
    }

    private function check_data() {
        //url arguments
        //1 - folder_id
        //2 - folder_name - просто для красоты URL. Его не используем
        if(isset($this->uCore->url_prop[1])) {
            if(uString::isDigits($this->uCore->url_prop[1])) {
                $this->recycled=0;
                //check if this folder_id exists
                $query=$this->get_folder_info($this->uCore->url_prop[1]);
                if($qr=$query->fetch(PDO::FETCH_OBJ)) $this->folder_id=$this->uCore->url_prop[1];
            }
            elseif($this->uCore->url_prop[1]=='recycled') {
                $this->recycled=1;
                $this->folder_id=$this->uCore->url_prop[1];
            }
        }
        if(!isset($this->folder_id)) $this->folder_id=0;
    }

    private function get_folder_info($folder_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uDrive")->prepare("SELECT
            file_id
            FROM
            u235_files
            WHERE
            file_id=:file_id AND
            file_mime='folder' AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':file_id', $folder_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);

        $this->uCore->page['page_title']=$this->text("Page name"/*Диск сайта*/);

        $this->check_data();
    }
}

$uDrive=new my_drive($this);

ob_start()?>
<div id="uDrive_my_drive_uploader_init"></div>
<?include_once 'uDrive/inc/my_drive_manager.php';

?>
    <script type="text/javascript">
        $(document).ready(function() {
            if(typeof uDrive_manager==="undefined") uDrive_manager={};

            uDrive_manager.init('uDrive_my_drive_uploader', uDrive_manager.cur_folder_id,0);
            uDrive_manager.open_folder(<?=$uDrive->folder_id?>,<?=$uDrive->recycled?1:0?>);
        })
    </script>
<?
$this->page_content=ob_get_contents();
ob_end_clean();
include 'templates/u235/template.php';