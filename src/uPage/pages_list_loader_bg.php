<?php
namespace uPage\admin;

use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class pages_list_loader_bg{
    public $folder_id,$recycled,$mod_file,$file_mod,$file_handler,$uBc;
    public $folder_id_isset;
    private $uCore,
        $q_files_where;

    private function check_data() {
        $this->recycled=$this->folder_id=$this->mod_file=0;
        if(isset($_POST['folder_id'])) {
            if(uString::isDigits($_POST['folder_id'])) {
                    //check if this folder_id exists
                if($_POST['folder_id']!='0'){
                    $query=$this->get_folder_info($_POST['folder_id']);
                    /** @noinspection PhpUndefinedMethodInspection */
                    if($qr=$query->fetch(\PDO::FETCH_OBJ)) {
                        if($qr->deleted=='1') $this->recycled=1;

                        $this->folder_id=$_POST['folder_id'];

                        $this->folder_id_isset=1;

                        if($this->recycled) {
                            $this->q_files_where=" folder_id=:folder_id AND deleted!=2 AND ";
                            $this->get_parent_folders_tree($this->folder_id,uString::sql2text($qr->page_title,1),(int)$qr->folder_id,1,$qr->deleted_directly);
                        }
                        else {
                            $this->q_files_where=" folder_id=:folder_id AND deleted=0 AND ";
                            $this->get_parent_folders_tree($this->folder_id,uString::sql2text($qr->page_title,1),(int)$qr->folder_id,0,$qr->deleted_directly);
                        }
                    }
                }
            }
            if(isset($_POST['recycled'])) {
                if($_POST['recycled']=='1') {
                    $this->recycled=1;
                    $this->get_parent_folders_tree(0,$this->text('recycled bin'),$this->folder_id);

                    $this->folder_id_isset=0;
                    $this->q_files_where=" deleted_directly=1 AND deleted=1 AND ";
                }
            }
        }

        if(!isset($this->q_files_where)) {
            $this->folder_id_isset=0;
            $this->q_files_where=" folder_id=0 AND deleted=0 AND ";
        }
    }

    private function get_folder_info($folder_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            page_title,
            folder_id,
            deleted,
            deleted_directly
            FROM
            u235_pages
            WHERE
            page_id=:folder_id AND
            page_type='folder' AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':folder_id', $folder_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}
        return 0;
    }
    private function get_parent_folders_tree($file_id,$folder_title,$folder_id,$recycled=0,$deleted_directly=0) {
        if(!isset($this->uBc)) {
            $this->uBc='';
        }
            $this->uBc='<li><a href="javascript:void(0)" onclick="uPage_pages_manager.'.(isset($_POST['in_dialog'])?'move_':'').'open_folder('.$file_id.')">'.$folder_title.'</li>'.$this->uBc;
            if($folder_id) {
                $query=$this->get_folder_info($folder_id);
                if($qr=$query->fetch(\PDO::FETCH_OBJ)) {
                    if($deleted_directly=='0') {
                        $this->get_parent_folders_tree((int)$folder_id,uString::sql2text($qr->page_title,1),(int)$qr->folder_id);
                    }
                }
            }
        if($recycled&&$this->folder_id) {
            $this->uBc='<li><a href="javascript:void(0)" onclick="uPage_pages_manager.open_folder(0,1)">'.$this->text('recycled bin').'</a></li>'.$this->uBc;
        }
    }
    public function get_page_list(){
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            page_id,
            page_title,
            page_url,
            folder_id,
            page_type,
            page_timestamp,
            deleted
            FROM
            u235_pages
            WHERE
            ".$this->q_files_where.
            "site_id=:site_id".
            (isset($_POST['in_dialog'])?" AND page_type='folder' ":"").
            " "."ORDER BY
            page_title ASC
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            if($this->folder_id_isset) /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':folder_id', $this->folder_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('20'.$e->getMessage());}
        return 0;
    }
    public function text($string) {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->uCore->text(array('uDrive','my_drive'),$string);
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
        if(!$this->uSes->access(7));

        $this->check_data();
    }
}

$uPage=new pages_list_loader_bg($this);

$pages=$uPage->get_page_list();

ob_start();

if(!isset($_POST['in_dialog'])) $page_prefix='';
else $page_prefix='move_';
?>
    <script type="text/javascript">

        uPage_pages_manager.<?=$page_prefix?>cur_folder_id=<?=$uPage->folder_id?>;
        uPage_pages_manager.<?=$page_prefix?>recycled=<?=$uPage->recycled?1:0?>;
<?
        /** @noinspection PhpUndefinedMethodInspection */
        for($i=0;$page=$pages->fetch(\PDO::FETCH_OBJ);$i++) { ?>
        uPage_pages_manager.<?=$page_prefix?>page_id[<?=$i?>]=<?=$page->page_id?>;
        uPage_pages_manager.<?=$page_prefix?>page_title[<?=$i?>]=decodeURIComponent("<?=rawurlencode(uString::sql2text($page->page_title,1))?>");
            <?if(!isset($_POST['in_dialog'])) {?>
                uPage_pages_manager.page_url[<?=$i?>]=decodeURIComponent("<?=rawurlencode($page->page_url)?>");
                uPage_pages_manager.page_id2i[<?=$page->page_id?>]=<?=$i?>;
                uPage_pages_manager.deleted[<?=$i?>]=<?=$page->deleted?>;
                uPage_pages_manager.page_type[<?=$i?>]="<?=$page->page_type?>";
                uPage_pages_manager.page_timestamp[<?=$i?>]="<?=$page->page_timestamp?>";
                uPage_pages_manager.page_show[<?=$i?>]=1;
            <?}
        }?>
    </script>
<?
    $js_vars=ob_get_contents();
    ob_end_clean();

    echo '{
    "status":"done",
    "js_vars":"'.rawurlencode($js_vars).'",
    "bc_html":"'.rawurlencode($uPage->uBc).'"
    }';