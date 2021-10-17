<?php
namespace uEditor\admin;

use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once 'processors/classes/uFunc.php';

class pages_list{
    public $folder_id,$recycled;
    /**
     * @var uFunc
     */
    private $uFunc;
    private $uCore;

    public function text($str) {
        return $this->uCore->text(array('uEditor','pages_list'),$str);
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
                /** @noinspection PhpUndefinedMethodInspection */
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
            $stm=$this->uFunc->pdo("pages")->prepare("SELECT
            page_id
            FROM
            u235_pages_html
            WHERE
            page_id=:page_id AND
            page_category='folder' AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $folder_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}
        return 0;
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) /** @noinspection PhpFullyQualifiedNameUsageInspection */ $this->uCore=new \uCore();
        $this->uFunc=new uFunc($this->uCore);

        $this->uCore->page['page_title']=$this->text("Page name"/*Тексты*/);
        $this->uFunc->incCss("uEditor/css/pages_list.min.css");

        $this->check_data();
    }
}

$uEditor=new pages_list($this);

ob_start()?>
<div id="uEditor_pages_init"></div>
<?include_once 'uEditor/inc/pages_manager.php';?>
    <script type="text/javascript">
        $(document).ready(function() {
            uEditor_pages_manager.init('uEditor_pages', uEditor_pages_manager.cur_folder_id,0);
            uEditor_pages_manager.open_folder(<?=$uEditor->folder_id?>,<?=$uEditor->recycled?1:0?>);
        })
    </script>
<?
$this->page_content=ob_get_contents();
ob_end_clean();
include 'templates/u235/template.php';
