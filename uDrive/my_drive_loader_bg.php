<?php
require_once 'processors/uSes.php';
class uDrive_my_drive_loader{
    public $folder_id,$recycled,$mod_file,$file_mod,$file_handler,$uBc;
    private $uCore,
        $q_files_where;

    private function check_data() {
        $this->recycled=$this->folder_id=$this->mod_file=0;
        if(isset($_POST['folder_id'])) {
            if(uString::isDigits($_POST['folder_id'])) {
                    //check if this folder_id exists
                if($_POST['folder_id']!='0'){
                    $query=$this->get_folder_info($_POST['folder_id']);
                    if(mysqli_num_rows($query)) {
                        $qr=$query->fetch_object();
                        if($qr->deleted=='1') $this->recycled=1;

                        $this->folder_id=$_POST['folder_id'];

                        if($this->recycled) {
                            $this->q_files_where="`folder_id`='".$this->folder_id."' AND `deleted`!='2' AND";
                            $this->get_parent_folders_tree($this->folder_id,uString::sql2text($qr->file_name,1),(int)$qr->folder_id,1,$qr->deleted_directly);
                        }
                        else {
                            $this->q_files_where="`folder_id`='".$this->folder_id."' AND `deleted`='0' AND";
                            $this->get_parent_folders_tree($this->folder_id,uString::sql2text($qr->file_name,1),(int)$qr->folder_id,0,$qr->deleted_directly);
                        }
                    }
                }
            }
            if(isset($_POST['recycled'])) {
                if($_POST['recycled']=='1') {
                    $this->recycled=1;
                    $this->get_parent_folders_tree(0,$this->text('recycled bin'),$this->folder_id);

                    $this->q_files_where="`deleted_directly`='1' AND `deleted`='1' AND";
                }
            }
        }

        if(!isset($this->q_files_where)) {
            $this->q_files_where="`folder_id`='0' AND
                    `deleted`='0' AND";
        }
    }

    private function get_folder_info($folder_id) {
        if(!$query=$this->uCore->query("uDrive","SELECT
        `file_name`,
        `folder_id`,
        `deleted`,
        `deleted_directly`
        FROM
        `u235_files`
        WHERE
        `file_id`='".$folder_id."' AND
        `file_mime`='folder' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(1);
        return $query;
    }
    private function get_parent_folders_tree($file_id,$folder_title,$folder_id,$recycled=0,$deleted_directly=0) {
        if(!isset($this->uBc)) {
            $this->uBc='';
        }
            $this->uBc='<li><a href="javascript:void(0)" onclick="uDrive_manager.'.(isset($_POST['in_dialog'])?'move_':'').'open_folder('.$file_id.')">'.$folder_title.'</li>'.$this->uBc;
            if($folder_id) {
                $query=$this->get_folder_info($folder_id);
                if(mysqli_num_rows($query)) {
                    $qr=$query->fetch_object();
                    if($deleted_directly=='0') {
                        $this->get_parent_folders_tree((int)$folder_id,uString::sql2text($qr->file_name,1),(int)$qr->folder_id);
                    }
                }
            }
        if($recycled&&$this->folder_id) {
            $this->uBc='<li><a href="javascript:void(0)" onclick="uDrive_manager.open_folder(0,1)">'.$this->text('recycled bin').'</a></li>'.$this->uBc;
        }
    }
    public function get_file_list(){
        if(!$query=$this->uCore->query("uDrive","SELECT
        `file_id`,
        `file_name`,
        `file_hashname`,
        `file_size`,
        `file_ext`,
        `file_mime`,
        `deleted`,
        `file_protected`,
        `file_is_used`,
        `file_timestamp`,
        `file_access`
        FROM
        `u235_files`
        WHERE
        ".$this->q_files_where."
        `site_id`='".site_id."'
        ".(isset($_POST['in_dialog'])?" AND `file_mime`='folder'":"")."
        ORDER BY
        `file_name` ASC
        ")) $this->uCore->error(2);
        return $query;
    }
    public function text($string) {
        return $this->uCore->text(array('uDrive','my_drive'),$string);
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(1900));

        $this->check_data();
    }
}

$uDrive=new uDrive_my_drive_loader($this);

$files=$uDrive->get_file_list();

ob_start();

if(!isset($_POST['in_dialog'])) $file_prefix='';
else $file_prefix='move_';
?>
    <script type="text/javascript">
        if(typeof uDrive_manager==="undefined") uDrive_manager={};

        uDrive_manager.<?=$file_prefix?>cur_folder_id=<?=$uDrive->folder_id?>;
        uDrive_manager.<?=$file_prefix?>recycled=<?=$uDrive->recycled?1:0?>;
<?
        for($i=0;$file=$files->fetch_object();$i++) { ?>
        uDrive_manager.<?=$file_prefix?>file_id[<?=$i?>]=<?=$file->file_id?>;
        uDrive_manager.<?=$file_prefix?>file_name[<?=$i?>]="<?=rawurlencode(uString::sql2text($file->file_name))?>";
            <?if(!isset($_POST['in_dialog'])) {?>
                uDrive_manager.file_hashname[<?=$i?>]="<?=$file->file_hashname?>";
                uDrive_manager.file_id2i[<?=$file->file_id?>]=<?=$i?>;
                uDrive_manager.file_size[<?=$i?>]=<?=(int)$file->file_size?>;
                uDrive_manager.deleted[<?=$i?>]=<?=$file->deleted?>;
                uDrive_manager.file_protected[<?=$i?>]=<?=$file->file_protected?>;
                uDrive_manager.file_access[<?=$i?>]=<?=$file->file_access?>;
                uDrive_manager.file_is_used[<?=$i?>]=<?=$file->file_is_used?>;
                uDrive_manager.file_ext[<?=$i?>]="<?=$file->file_ext?>";
                uDrive_manager.file_ext_icon[<?=$i?>]="<?=$file->file_mime=='folder'?($file->file_protected=='1'?'icon-folder-circled text-danger':'icon-folder'):(isset($this->uFunc->file_ext2fonticon[$file->file_ext])?$this->uFunc->file_ext2fonticon[$file->file_ext]:'icon-file-unknown')?>";
                uDrive_manager.file_mime[<?=$i?>]="<?=$file->file_mime?>";
                uDrive_manager.file_timestamp[<?=$i?>]="<?=$file->file_timestamp?>";
                uDrive_manager.file_show[<?=$i?>]=1;
            <?}
        }?>
    </script>
<?
    $js_vars=ob_get_contents();
    ob_end_clean();

    echo '{
    "status":"done",
    "js_vars":"'.rawurlencode($js_vars).'",
    "bc_html":"'.rawurlencode($uDrive->uBc).'"
    }';
