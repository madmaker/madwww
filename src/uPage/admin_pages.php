<?php
namespace uPage\admin;

use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";
class admin_pages {
    public $folder_id;
    public $recycled;
    public $uFunc;
    private $uCore;
    public $q_pages;

    public function text($str) {
        return $this->uCore->text(array('uPage','admin_pages'),$str);
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
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            page_id
            FROM
            u235_pages
            WHERE
            page_id=:page_id AND
            page_type='folder' AND
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
        $this->uCore->page['page_title']=$this->text("Page name");
        $this->uFunc->incCss("/uPage/css/admin_pages.min.css");

        $this->check_data();
    }
}
$uPage=new admin_pages($this);

ob_start();?>
    <div id="uPage_pages_init"></div>
<?include_once 'uPage/inc/pages_manager.php';?>
    <script type="text/javascript">
        $(document).ready(function() {
            uPage_pages_manager.init('uPage_pages', uPage_pages_manager.cur_folder_id,0);
            uPage_pages_manager.open_folder(<?=$uPage->folder_id?>,<?=$uPage->recycled?1:0?>);
        })
    </script>
<?$this->page_content=ob_get_contents();
ob_end_clean();
include "templates/u235/template.php";
