<?php
namespace uDrive;
use PDO;
use PDOException;
use processors\uFunc;
use stdClass;
use uString;

require_once 'processors/classes/uFunc.php';

class file_update {
    private $uCore,
    $folder_levels,$folder_exists;
    public $file_id;
    public function recheck_file_usage($file_id) {
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$query=$this->uCore->query('uDrive',"SELECT
        `usage_id`
        FROM
        `u235_files_usage`
        WHERE
        `file_id`='".$file_id."' AND
        `site_id`='".site_id."'
        LIMIT 1
        ")) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(1);
        if(!mysqli_num_rows($query)) {
            /** @noinspection PhpUndefinedMethodInspection */
            if(!$this->uCore->query("uDrive","UPDATE
            `u235_files`
            SET
            `file_is_used`='0'
            WHERE
            `file_id`='".$file_id."' AND
            `site_id`='".site_id."'
            ")) /** @noinspection PhpUndefinedMethodInspection */
                $this->uCore->error(2);
        }
    }

    private function check_data() {
        if(!isset($_POST['file_id'])) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(3);
        $this->file_id=$_POST['file_id'];
    }
    private function get_folder_level($folder_id) {
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$query=$this->uCore->query("uDrive","SELECT
            `folder_id`
            FROM
            `u235_files`
            WHERE
            `file_id`='".$folder_id."' AND
            `site_id`='".site_id."'
            ")) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(4);
        if(!mysqli_num_rows($query)) die ('{
            "status":"error",
            "msg":"problem with target folder"
            }');
        /** @noinspection PhpUndefinedMethodInspection */
        $qr=$query->fetch_object();
        return (int)$qr->folder_id;
    }
    private function set_folder_levels($folder_id) {
        if(!isset($this->folder_levels)) {
            $folder_id=$this->get_folder_level($folder_id);
            if($folder_id) {
                $this->folder_levels[$folder_id]=1;
                $this->get_folder_level($folder_id);
            }
        }
    }
    private function update_folder_id() {
        $folder_id=$_POST['folder_id'];
        if(!uString::isDigits($folder_id)) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(5);
        $folder_id=(int)$folder_id;

        $files_ar=explode(',',$this->file_id);

        if($folder_id) {
            //check if this file_id exists
            /** @noinspection PhpUndefinedMethodInspection */
            if(!$query=$this->uCore->query("uDrive","SELECT
            `file_id`
            FROM
            `u235_files`
            WHERE
            `file_id`='".$folder_id."' AND
            `site_id`='".site_id."'
            ")) /** @noinspection PhpUndefinedMethodInspection */
                $this->uCore->error(6);
            if(!mysqli_num_rows($query)) /** @noinspection PhpUndefinedMethodInspection */
                $this->uCore->error(7);
        }

        $files_list=$skipped_files_list='';
        for($i=0;$i<(count($files_ar)-1);$i++) {
            if(uString::isDigits($files_ar[$i])) {
                $file_id=(int)$files_ar[$i];
                if($folder_id==$file_id) {//folder can't be dropped to itself
                    $skipped_files_list.=$file_id.',';
                    continue;
                }

                if($folder_id) {
                    //get file_info
                    /** @noinspection PhpUndefinedMethodInspection */
                    if(!$query=$this->uCore->query("uDrive","SELECT
                    `file_mime`
                    FROM
                    `u235_files`
                    WHERE
                    `file_id`='".$file_id."' AND
                    `site_id`='".site_id."'
                    ")) /** @noinspection PhpUndefinedMethodInspection */
                        $this->uCore->error(8);
                    if(!mysqli_num_rows($query)) {
                        $skipped_files_list.=$file_id.',';
                        continue;
                    }
                    /** @noinspection PhpUndefinedMethodInspection */
                    $file=$query->fetch_object();

                    if($file->file_mime=='folder') {//parent folder can't be dropped to it's child in any level
                        $this->set_folder_levels($folder_id);
                        if(isset($this->folder_levels[$file_id])) {
                            $skipped_files_list.=$file_id.',';
                            continue;
                        }
                    }
                }

                $files_list.=$file_id.',';
                //update file
                /** @noinspection PhpUndefinedMethodInspection */
                if(!$this->uCore->query("uDrive","UPDATE
                `u235_files`
                SET
                `folder_id`='".$folder_id."'
                WHERE
                `file_protected`='0' AND
                `file_id`='".$file_id."' AND
                `site_id`='".site_id."'
                ")) /** @noinspection PhpUndefinedMethodInspection */
                    $this->uCore->error(9);
            }
        }

        echo '{
        "status":"done",
        "files":"'.$files_list.'",
        "skipped_files_list":"'.$skipped_files_list.'",
        "folder_id":"'.$folder_id.'"
        }';
    }
    private function copy_folders_content($orig_folder_id,$new_folder_id) {
        //get folder's content
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$query=$this->uCore->query("uDrive","SELECT
        `file_id`,
        `file_name`,
        `file_size`,
        `file_ext`,
        `file_mime`,
        `file_hashname`,
        `file_timestamp`
        FROM
        `u235_files`
        WHERE
        `folder_id`='".$orig_folder_id."' AND
        `site_id`='".site_id."'
        ")) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(10);
        if(!mysqli_num_rows($query)) return 0;
        /** @noinspection PhpUndefinedMethodInspection */
        while($file=$query->fetch_object()) {
            //get new file_id
            /** @noinspection PhpUndefinedMethodInspection */
            if(!$query1=$this->uCore->query("uDrive","SELECT
            `file_id`
            FROM
            `u235_files`
            WHERE
            `site_id`='".site_id."'
            ORDER BY
            `file_id` DESC
            LIMIT 1
            ")) /** @noinspection PhpUndefinedMethodInspection */
                $this->uCore->error(11);
            if(mysqli_num_rows($query1)) {
                /** @noinspection PhpUndefinedMethodInspection */
                $qr=$query1->fetch_object();
                $new_file_id=$qr->file_id+1;
            }
            else $new_file_id=1;

            //insert file
            /** @noinspection PhpUndefinedMethodInspection */
            if(!$this->uCore->query("uDrive","INSERT INTO
            `u235_files` (
            `file_id`,
            `file_name`,
            `file_size`,
            `file_ext`,
            `file_mime`,
            `file_hashname`,
            `file_timestamp`,
            `folder_id`,
            `site_id`
            ) VALUES (
            '".$new_file_id."',
            '".$file->file_name."',
            '".$file->file_size."',
            '".$file->file_ext."',
            '".$file->file_mime."',
            '".$file->file_hashname."',
            '".$file->file_timestamp."',
            '".$new_folder_id."',
            '".site_id."'
            )
            ")) /** @noinspection PhpUndefinedMethodInspection */
                $this->uCore->error(12);

            if($file->file_mime=='folder') {
                $this->copy_folders_content($file->file_id,$new_file_id);
            }
        }
        return 0;
    }
    private function copypaste_files() {
        $files_ar=explode(',',$this->file_id);

        $target_folder_id=$_POST['folder_id'];
        if(!uString::isDigits($target_folder_id)) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(13);
        $target_folder_id=(int)$target_folder_id;

        $cur_folder_id=$_POST['cur_folder_id'];
        if(!uString::isDigits($cur_folder_id)) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(14);
        $cur_folder_id=(int)$cur_folder_id;

        if($target_folder_id) {
            //check if this target_folder_id exists
            /** @noinspection PhpUndefinedMethodInspection */
            if(!$query=$this->uCore->query("uDrive","SELECT
            `file_id`
            FROM
            `u235_files`
            WHERE
            `file_id`='".$target_folder_id."' AND
            `site_id`='".site_id."'
            ")) /** @noinspection PhpUndefinedMethodInspection */
                $this->uCore->error(15);
            if(!mysqli_num_rows($query)) /** @noinspection PhpUndefinedMethodInspection */
                $this->uCore->error(16);
        }

        $files_list=$files_info='';
        if($cur_folder_id!=$target_folder_id) {//we need only file_mime
            $q_file_data="
            `file_mime`,
            `folder_id`
            ";
        }
        else {//we must get all file_info
            $q_file_data="
            `file_name`,
            `file_size`,
            `file_ext`,
            `file_mime`,
            `file_hashname`,
            `file_timestamp`,
            `folder_id`
            ";
        }

        for($i=0;$i<(count($files_ar)-1);$i++) {
            if(uString::isDigits($files_ar[$i])) {
                $file_id=(int)$files_ar[$i];
                if($target_folder_id==$file_id) {//folder can't be copypasted to itself
                    continue;
                }

                //get file info
                /** @noinspection PhpUndefinedMethodInspection */
                if(!$query=$this->uCore->query("uDrive","SELECT
                ".$q_file_data."
                FROM
                `u235_files`
                WHERE
                `file_id`='".$file_id."' AND
                `site_id`='".site_id."'
                ")) /** @noinspection PhpUndefinedMethodInspection */
                    $this->uCore->error(17);
                if(mysqli_num_rows($query)) {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $file=$query->fetch_object();

                    //get new file_id
                    /** @noinspection PhpUndefinedMethodInspection */
                    if(!$query1=$this->uCore->query("uDrive","SELECT
                    `file_id`
                    FROM
                    `u235_files`
                    WHERE
                    `site_id`='".site_id."'
                    ORDER BY
                    `file_id` DESC
                    LIMIT 1
                    ")) /** @noinspection PhpUndefinedMethodInspection */
                        $this->uCore->error(18);
                    if(mysqli_num_rows($query1)) {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $qr=$query1->fetch_object();
                        $new_file_id=$qr->file_id+1;
                    }
                    else $new_file_id=1;

                    //insert file
                    /** @noinspection PhpUndefinedMethodInspection */
                    if(!$this->uCore->query("uDrive","INSERT INTO
                    `u235_files` (
                    `file_id`,
                    `file_name`,
                    `file_size`,
                    `file_ext`,
                    `file_mime`,
                    `file_hashname`,
                    `file_timestamp`,
                    `folder_id`,
                    `site_id`
                    ) VALUES (
                    '".$new_file_id."',
                    '".$file->file_name."',
                    '".$file->file_size."',
                    '".$file->file_ext."',
                    '".$file->file_mime."',
                    '".$file->file_hashname."',
                    '".$file->file_timestamp."',
                    '".$target_folder_id."',
                    '".site_id."'
                    )
                    ")) /** @noinspection PhpUndefinedMethodInspection */
                        $this->uCore->error(19);

                    if($file->file_mime=='folder') {
                        $this->set_folder_levels($target_folder_id);//parent folder can't be dropped to it's child in any level
                        if(isset($this->folder_levels[$file_id])) continue;

                        $this->copy_folders_content($file_id,$new_file_id);
                    }

                    $files_list.=$file_id.'='.$new_file_id.',';

                    if($file->folder_id!=$target_folder_id&&$cur_folder_id==$target_folder_id) {//data for browser
                        $files_info.='
                        "file_name_'.$new_file_id.'":"'.rawurlencode(uString::sql2text($file->file_name,1)).'",
                        "file_hashname_'.$new_file_id.'":"'.$file->file_hashname.'",
                        "file_size_'.$new_file_id.'":"'.$file->file_size.'",
                        "file_ext_'.$new_file_id.'":"'.rawurlencode($file->file_ext).'",
                        "file_ext_icon_'.$new_file_id.'":"'.($file->file_mime=='folder'?'icon-folder':(isset($this->uCore->uFunc->file_ext2fonticon[$file->file_ext])?$this->uCore->uFunc->file_ext2fonticon[$file->file_ext]:'icon-file-unknown')).'",
                        "file_mime_'.$new_file_id.'":"'.$file->file_mime.'",
                        "file_hashname_'.$new_file_id.'":"'.$file->file_hashname.'",
                        "file_timestamp_'.$new_file_id.'":"'.$file->file_timestamp.'",
                        ';
                    }
                }
            }
        }

        /** @noinspection PhpUndefinedVariableInspection */
        if(!isset($file->folder_id)) {
            if(!isset($file)) $file=new stdClass();
            $file->folder_id=$cur_folder_id;
        }

        echo '{
        "status":"done",
        "action":"copypaste",
        "files":"'.$files_list.'",
        '.$files_info.'
        "folder_id":"'.$target_folder_id.'",
        "from_folder_id":"'.$file->folder_id.'"
        }';
    }
    private function cutpaste_files() {
        $files_ar=explode(',',$this->file_id);

        $target_folder_id=$_POST['folder_id'];
        if(!uString::isDigits($target_folder_id)) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(20);
        $target_folder_id=(int)$target_folder_id;

        $cur_folder_id=$_POST['cur_folder_id'];
        if(!uString::isDigits($cur_folder_id)) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(21);
        $cur_folder_id=(int)$cur_folder_id;

        if($target_folder_id) {
            //check if target_folder exists
            /** @noinspection PhpUndefinedMethodInspection */
            if(!$query=$this->uCore->query("uDrive","SELECT
            `file_id`
            FROM
            `u235_files`
            WHERE
            `file_id`='".$target_folder_id."' AND
            `site_id`='".site_id."'
            ")) /** @noinspection PhpUndefinedMethodInspection */
                $this->uCore->error(22);
            if(!mysqli_num_rows($query)) /** @noinspection PhpUndefinedMethodInspection */
                $this->uCore->error(23);
        }

        $files_list=$files_info=$skipped_files_list='';
        if($cur_folder_id!=$target_folder_id) {//we need only file_mime
            $q_file_data="
            `file_mime`
            ";
        }
        else {//we must get all file_info
            $q_file_data="
            `file_name`,
            `file_size`,
            `file_ext`,
            `file_mime`,
            `file_hashname`,
            `file_timestamp`
            ";
        }
        for($i=0;$i<(count($files_ar)-1);$i++) {
            if(uString::isDigits($files_ar[$i])) {
                $file_id=(int)$files_ar[$i];
                if($target_folder_id==$file_id) {//folder can't be cutpasted to itself
                    $skipped_files_list.=$file_id.',';
                    continue;
                }

                //get file info
                /** @noinspection PhpUndefinedMethodInspection */
                if(!$query=$this->uCore->query("uDrive","SELECT
                    ".$q_file_data."
                FROM
                `u235_files`
                WHERE
                `file_id`='".$files_ar[$i]."' AND
                `site_id`='".site_id."'
                ")) /** @noinspection PhpUndefinedMethodInspection */
                    $this->uCore->error(24);
                if(!mysqli_num_rows($query)) {
                    $skipped_files_list.=$file_id.',';
                    continue;
                }
                /** @noinspection PhpUndefinedMethodInspection */
                $file=$query->fetch_object();

                if($cur_folder_id==$target_folder_id) {//data for browser
                    $files_info.='
                    "file_name_'.$files_ar[$i].'":"'.uString::sql2text(rawurlencode($file->file_name),1).'",
                    "file_hashname_'.$files_ar[$i].'":"'.$file->file_hashname.'",
                    "file_size_'.$files_ar[$i].'":"'.$file->file_size.'",
                    "file_ext_'.$files_ar[$i].'":"'.rawurlencode($file->file_ext).'",
                    "file_ext_icon_'.$files_ar[$i].'":"'.($file->file_mime=='folder'?'icon-folder':(isset($this->uCore->uFunc->file_ext2fonticon[$file->file_ext])?$this->uCore->uFunc->file_ext2fonticon[$file->file_ext]:'icon-file-unknown')).'",
                    "file_mime_'.$files_ar[$i].'":"'.$file->file_mime.'",
                    "file_hashname_'.$files_ar[$i].'":"'.$file->file_hashname.'",
                    "file_timestamp_'.$files_ar[$i].'":"'.$file->file_timestamp.'",
                    ';
                }

                if($file->file_mime=='folder') {//parent folder can't be dropped to it's child in any level
                    $this->set_folder_levels($target_folder_id);
                    if(isset($this->folder_levels[$file_id])) {
                        $skipped_files_list.=$file_id.',';
                        continue;
                    }
                }

                $files_list.=$file_id.',';
                //update file
                /** @noinspection PhpUndefinedMethodInspection */
                if(!$this->uCore->query("uDrive","UPDATE
                `u235_files`
                SET
                `folder_id`='".$target_folder_id."'
                WHERE
                `file_id`='".$file_id."' AND
                `site_id`='".site_id."'
                ")) /** @noinspection PhpUndefinedMethodInspection */
                    $this->uCore->error(25);
            }
        }

        echo '{
        "status":"done",
        "action":"cutpaste",
        "files":"'.$files_list.'",
        '.$files_info.'
        "skipped_files_list":"'.$skipped_files_list.'",
        "folder_id":"'.$target_folder_id.'",
        "cur_folder_id":"'.$cur_folder_id.'"
        }';
    }
    public function recycle_files_from_folder($folder_id,$action,$delete_filter="delete_unused",$ignore_protected=0) {
        if($action=='recycle') $q_deleted='1';
        elseif($action=='delete') $q_deleted='2';
        elseif($action=='restore') $q_deleted='0';
        else $this->uCore->error(26);

        //get folder's files
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$query=$this->uCore->query("uDrive","SELECT
        `file_id`,
        `file_mime`,
        `file_is_used`,
        `folder_id`
        FROM
        `u235_files`
        WHERE
        ".(!$ignore_protected?"`file_protected`='0' AND":"")."
        `folder_id`='".$folder_id."' AND
        `site_id`='".site_id."'
        ")) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(27);
        /** @noinspection PhpUndefinedMethodInspection */
        while($file=$query->fetch_object()) {
            if($action=='recycle'||$action=='delete') {
                if ($file->file_mime == 'folder') $this->recycle_files_from_folder($file->file_id, $action, $delete_filter, $ignore_protected);
            }

            if($file->file_is_used=='1'&&$action=='delete'&&$delete_filter=='delete_unused') {
                //update file
                /** @noinspection PhpUndefinedMethodInspection */
                if(!$this->uCore->query("uDrive","UPDATE
                `u235_files`
                SET
                `deleted_directly`='1'
                WHERE
                `file_protected`='0' AND
                `file_id`='".$file->file_id."' AND
                `site_id`='".site_id."'
                ")) /** @noinspection PhpUndefinedMethodInspection */
                    $this->uCore->error(28);
            }
            else {
                //check if file's folder exits
                $q_reset_folder_id='';
                if($file->folder_id!='0') {
                    if(!$this->check_if_folder_exists($file->folder_id)) {
                        $q_reset_folder_id="`folder_id`='0',";
                    }
                }
                //update file
                /** @noinspection PhpUndefinedMethodInspection */
                /** @noinspection PhpUndefinedVariableInspection */
                if(!$this->uCore->query("uDrive","UPDATE
                `u235_files`
                SET
                ".$q_reset_folder_id."
                ".($action=='recycle'?"
                `deleted_directly`='0',
                `file_protected`='0',
                ":'')."
                `deleted`='".$q_deleted."'
                WHERE
                ".(!$ignore_protected?"`file_protected`='0' AND":"")."
                `file_id`='".$file->file_id."' AND
                `site_id`='".site_id."'
                ")) /** @noinspection PhpUndefinedMethodInspection */
                    $this->uCore->error(29);
            }

            if($action=='restore') {
                if ($file->file_mime == 'folder') $this->recycle_files_from_folder($file->file_id, $action, $delete_filter, $ignore_protected);
            }

        }
    }
    private function check_if_folder_exists($folder_id) {
            //check if folder exists
            /** @noinspection PhpUndefinedMethodInspection */
            if(!$query=$this->uCore->query("uDrive","SELECT
            `file_id`
            FROM
            `u235_files`
            WHERE
            `deleted`='0' AND
            `file_id`='".$folder_id."' AND
            `site_id`='".site_id."'
            ")) /** @noinspection PhpUndefinedMethodInspection */
                $this->uCore->error(30);
            if(mysqli_num_rows($query)) return 1;
            else return 0;
    }
    public function recycle_files($action,$ignore_protected=0) {
        $files_ar=explode(',',$this->file_id);
        $delete_filter='delete_unused';

        if($action=='recycle') $q_deleted='1';
        elseif($action=='delete') {
            $q_deleted='2';
            if(isset($_POST['delete_filter'])) {
                if($_POST['delete_filter']=='delete_all') $delete_filter='delete_all';
            }
        }
        elseif($action=='restore') $q_deleted='0';
        else /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(31);


        $folder_id=0;
        for($i=0;$i<(count($files_ar));$i++) {
            if(uString::isDigits($files_ar[$i])) {
                $file_id=(int)$files_ar[$i];

                //get file info
                /** @noinspection PhpUndefinedMethodInspection */
                if(!$query=$this->uCore->query("uDrive","SELECT
                `file_mime`,
                `folder_id`,
                `file_is_used`
                FROM
                `u235_files`
                WHERE
                ".(!$ignore_protected?"`file_protected`='0' AND":"")."
                `file_id`='".$file_id."' AND
                `site_id`='".site_id."'
                ")) /** @noinspection PhpUndefinedMethodInspection */
                    $this->uCore->error(32);
                if(!mysqli_num_rows($query)) continue;
                /** @noinspection PhpUndefinedMethodInspection */
                $file=$query->fetch_object();

                $folder_id=(int)$file->folder_id;
                if($action=='recycle'||$action=='delete') {
                    if($file->file_mime=='folder') $this->recycle_files_from_folder($file_id,$action,$delete_filter,$ignore_protected);
                }

                if($file->file_is_used=='1'&&$action=='delete'&&$delete_filter=='delete_unused') {
                    //update file
                    /** @noinspection PhpUndefinedMethodInspection */
                    if(!$this->uCore->query("uDrive","UPDATE
                    `u235_files`
                    SET
                    `deleted_directly`='1'
                    WHERE
                    `file_protected`='0' AND
                    `file_id`='".$file_id."' AND
                    `site_id`='".site_id."'
                    ")) /** @noinspection PhpUndefinedMethodInspection */
                        $this->uCore->error(33);
                }
                else {
                    //check if file's folder exits
                    $q_reset_folder_id='';
                    if($file->folder_id!='0') {
                        if(!$this->check_if_folder_exists($file->folder_id)) {
                            $q_reset_folder_id="`folder_id`='0',";
                            $folder_id=0;
                        }
                    }
                    //update file
                    /** @noinspection PhpUndefinedMethodInspection */
                    /** @noinspection PhpUndefinedVariableInspection */
                    if(!$this->uCore->query("uDrive","UPDATE
                    `u235_files`
                    SET
                    ".$q_reset_folder_id."
                    ".($action=='recycle'?"
                    `deleted_directly`='1',
                    `file_protected`='0',
                    ":'')."
                    `deleted`='".$q_deleted."'
                    WHERE
                    ".(!$ignore_protected?"`file_protected`='0' AND":"")."
                    `file_id`='".$file_id."' AND
                    `site_id`='".site_id."'
                    ")) /** @noinspection PhpUndefinedMethodInspection */
                        $this->uCore->error(34);
                }

                if($action=='restore') {
                    if($file->file_mime=='folder') $this->recycle_files_from_folder($file_id,$action,$delete_filter,$ignore_protected);
                }
            }
        }

        echo '{
        "status":"done",
        "folder_id":"'.$folder_id.'"
        }';
    }
    private function check_file_usage($files_ar) {
        $used_files_list=$files_names='';
        for($i=0;$i<(count($files_ar)-1);$i++) {
            if(uString::isDigits($files_ar[$i])) {
                $file_id=(int)$files_ar[$i];

                //get file info
                /** @noinspection PhpUndefinedMethodInspection */
                if(!$query=$this->uCore->query("uDrive","SELECT
                `file_name`,
                `file_mime`,
                `file_is_used`
                FROM
                `u235_files`
                WHERE
                `file_id`='".$file_id."' AND
                `site_id`='".site_id."'
                ")) /** @noinspection PhpUndefinedMethodInspection */
                    $this->uCore->error(35);
                if(!mysqli_num_rows($query)) continue;
                /** @noinspection PhpUndefinedMethodInspection */
                $file=$query->fetch_object();

                if($file->file_mime=='folder') {
                    //get folder's files
                    /** @noinspection PhpUndefinedMethodInspection */
                    if(!$query=$this->uCore->query("uDrive","SELECT
                    `file_id`
                    FROM
                    `u235_files`
                    WHERE
                    `folder_id`='".$file_id."' AND
                    `site_id`='".site_id."'
                    ")) /** @noinspection PhpUndefinedMethodInspection */
                        $this->uCore->error(36);
                    $files_ar1=array();
                    /** @noinspection PhpUndefinedMethodInspection */
                    while($file1=$query->fetch_object()) {
                        $files_ar1[count($files_ar1)]=$file1->file_id;
                    }
                    $files_list_ar=$this->check_file_usage($files_ar1);

                    $used_files_list.=$files_list_ar[0];
                    $files_names.=$files_list_ar[1];
                }

                if($file->file_is_used) {
                    $files_names.='"file_'.$file_id.'_name":"'.rawurlencode(uString::sql2text($file->file_name,1)).'",';
                    $used_files_list.=$file_id.',';
                }
            }
        }
        return array($used_files_list,$files_names);
    }
    private function check_usage_before_deletion() {
        $files_ar=explode(',',$this->file_id);

        $files_list_ar=$this->check_file_usage($files_ar);
        $used_files_list=$files_list_ar[0];
        $files_names=$files_list_ar[1];

        echo '{
        "status":"done",
        "files_list":"'.$this->file_id.'",
        '.$files_names.'
        "used_files_list":"'.$used_files_list.'"
        }';
    }
    private function check_usage_before_clean_recycled() {
        //get all deleted files that marked as file_is_used
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$query=$this->uCore->query("uDrive","SELECT
        `file_id`,
        `file_name`
        FROM
        `u235_files`
        WHERE
        `deleted`='1' AND
        `file_is_used`='1' AND
        `file_mime`!='folder' AND
        `site_id`='".site_id."'
        ")) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(37);

        $used_files_list=$files_names='';
        /** @noinspection PhpUndefinedMethodInspection */
        while($file=$query->fetch_object()) {
            $used_files_list.=$file->file_id.',';
            $files_names.='"file_'.$file->file_id.'_name":"'.rawurlencode(uString::sql2text($file->file_name,1)).'",';
        }

        echo '{
        "status":"done",
        '.$files_names.'
        "used_files_list":"'.$used_files_list.'"
        }';
    }
    private function clean_recycled_bin() {
        if(!isset($_POST['action'])) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(38);
        $action=$_POST['action'];
        if($action!='delete_all') $action='delete_unused';

        if($action=='delete_unused') {
            /** @noinspection PhpUndefinedMethodInspection */
            if(!$this->uCore->query("uDrive","UPDATE
            `u235_files`
            SET
            `deleted_directly`='1'
            WHERE
            `file_protected`='0' AND
            `deleted`='1' AND
            `file_is_used`='1' AND
            `file_mime`!='folder' AND
            `site_id`='".site_id."'
            ")) /** @noinspection PhpUndefinedMethodInspection */
                $this->uCore->error(39);

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$this->uCore->query("uDrive","UPDATE
            `u235_files`
            SET
            `deleted`='2'
            WHERE
            `file_protected`='0' AND
            `deleted`='1' AND
            `file_is_used`='0' AND
            `site_id`='".site_id."'
            ")) /** @noinspection PhpUndefinedMethodInspection */
                $this->uCore->error(40);
        }
        else {
            //update file
            /** @noinspection PhpUndefinedMethodInspection */
            if(!$this->uCore->query("uDrive","UPDATE
            `u235_files`
            SET
            `deleted`='2'
            WHERE
            `file_protected`='0' AND
            `deleted`='1' AND
            `site_id`='".site_id."'
            ")) /** @noinspection PhpUndefinedMethodInspection */
                $this->uCore->error(41);
        }

        echo '{"status":"done"}';
    }
    private function update_file_name() {
        if(!uString::isDigits($this->file_id)) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(42);

        $file_name=trim($_POST['file_name']);
        $file_name=trim(uString::sanitize_filename($file_name));
        if(!strlen($file_name)) $file_name='_';

        /** @noinspection PhpUndefinedMethodInspection */
        if(!$this->uCore->query("uDrive","UPDATE
        `u235_files`
        SET
        `file_name`='".uString::text2sql($file_name)."'
        WHERE
        `file_protected`='0' AND
        `file_id`='".$this->file_id."' AND
        `site_id`='".site_id."'
        ")) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(43);

        echo '{
        "status":"done",
        "file_id":"'.$this->file_id.'",
        "file_name":"'.rawurlencode($file_name).'"
        }';
    }
    private function add_file_usage() {
        if(!isset($_POST['file_mod'],$_POST['handler_type'],$_POST['handler_id'])) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(44);
        $file_mod=$_POST['file_mod'];
        $handler_type=$_POST['handler_type'];
        $handler_id=$_POST['handler_id'];

        $allowed_mods=array('uCat');
        $allowed_handler_types['uCat']=array('sect','cat','item','art');//uCat

        if(!in_array($file_mod,$allowed_mods)) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(45);
        if(!in_array($handler_type,$allowed_handler_types[$file_mod])) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(46);
        if(!uString::isDigits($handler_id)) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(47);
        $handler_id=(int)$handler_id;

        /** @noinspection PhpUndefinedMethodInspection */
        if(!$query=$this->uCore->query("uDrive","SELECT
        `usage_id`
        FROM
        `u235_files_usage`
        WHERE
        `file_id`='".$this->file_id."' AND
        `file_mod`='".$file_mod."' AND
        `handler_type`='".$handler_type."' AND
        `handler_id`='".$handler_id."' AND
        `site_id`='".site_id."'
        ")) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(48);
        if(mysqli_num_rows($query)) return 1;
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$query=$this->uCore->query("uDrive","SELECT
        `usage_id`
        FROM
        `u235_files_usage`
        WHERE
        `site_id`='".site_id."'
        ORDER BY
        `usage_id` DESC
        LIMIT 1
        ")) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(49);
        if(mysqli_num_rows($query)) {
            /** @noinspection PhpUndefinedMethodInspection */
            $qr=$query->fetch_object();
            $usage_id=$qr->usage_id+1;
        }
        else $usage_id=1;
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$this->uCore->query("uDrive","INSERT INTO
        `u235_files_usage` (
        `usage_id`,
        `file_id`,
        `file_mod`,
        `handler_type`,
        `handler_id`,
        `site_id`
        ) VALUES (
        '".$usage_id."',
        '".$this->file_id."',
        '".$file_mod."',
        '".$handler_type."',
        '".$handler_id."',
        '".site_id."'
        )
        ")) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(50);
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$this->uCore->query("uDrive","UPDATE
        `u235_files`
        SET
        `file_is_used`='1'
        WHERE
        `file_protected`='0' AND
        `file_id`='".$this->file_id."' AND
        `site_id`='".site_id."'
        ")) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(51);
        return 0;
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uFunc=new uFunc($this->uCore);
    }
    public function run() {
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$this->uCore->access(1900)) die("{'status' : 'forbidden'}");

        $this->check_data();
        if(isset($_POST['folder_id'],$_POST['copypaste'])) $this->copypaste_files();
        elseif(isset($_POST['folder_id'],$_POST['cutpaste'])) $this->cutpaste_files();
        elseif(isset($_POST['folder_id'])) $this->update_folder_id();
        elseif(isset($_POST['file_name'])) $this->update_file_name();
        elseif(isset($_POST['recycle'])) $this->recycle_files('recycle');
        elseif(isset($_POST['check_usage_before_deletion'])) $this->check_usage_before_deletion();
        elseif(isset($_POST['delete'])) $this->recycle_files('delete');
        elseif(isset($_POST['restore'])) $this->recycle_files('restore');
        elseif(isset($_POST['clean_recycled'])) $this->clean_recycled_bin();
        elseif(isset($_POST['check_usage_before_clean_recycled'])) $this->check_usage_before_clean_recycled();
        elseif(isset($_POST['add_usage'])) $this->add_file_usage();
        else /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(52);
    }
}
if(!isset($this->uCore)) {
    $uDrive=new file_update($this);
    $uDrive->run();
}
