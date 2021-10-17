<?php
require_once 'processors/uSes.php';
class uDrive_my_drive_uploader {
    private $uCore,$folder_id,$folder,$targetDir,$orig_file_name,$filename,$save_file_name,$file_ext,$file_id,$source_filename,$mime_type,$file_size;
    private function sendHeaders() {
        // HTTP headers for no cache etc
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
    }
    private function checkData() {
        //Check for required data
        if(!isset(/*$_REQUEST['hashId'],$_REQUEST['hash'],*/$_REQUEST['folder_id'])) die('{
        "status":"error",
        "type":"check",
        "message":"Haven\'t got all data required"
        }');

        if(!$this->uSes->access(1900)) die('{
        "status":"error",
        "type":"check",
        "message":"forbidden"
        }');

        $this->folder_id=$_REQUEST['folder_id'];
        if(!uString::isDigits($this->folder_id)) die('{
        "status":"error",
        "type":"check",
        "message":"Handled id error"
        }');
        $this->folder_id=(int)$this->folder_id;
//        if(!uString::isDigits($_REQUEST['hashId'])) die('{
//        "status":"error",
//        "type":"check",
//        "message":"hash check error"
//        }');

//        $this->uCore->uFunc->sesHack_test($_REQUEST['hashId'],$_REQUEST['hash']);

        $this->folder='uDrive/files/'.site_id;

//        $this->targetDir = $this->folder.'/'.$_REQUEST['hashId'];
        $this->targetDir = $this->folder.'/'.$this->uSes->get_val('sesId');
    }
    private function uploader() {
        $maxFileAge = 60 * 60; // Temp file age in seconds

        // 5 minutes execution time
        @set_time_limit(5 * 60);

        // Get parameters
        $chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
        $chunks = isset($_REQUEST["chunks"]) ? $_REQUEST["chunks"] : 0;
        $this->filename = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';

        // Clean the fileName for security reasons
        $this->orig_file_name=uString::sanitize_filename($this->filename);
        $this->filename = preg_replace('/[^\w\._]+/', '', $this->filename);
        if(trim($this->filename)=="") $this->filename="file";

        $dot = strrpos($this->filename, '.');
        $this->file_ext = substr($this->filename, $dot+1);

        // Create target dir
        if (!file_exists($this->targetDir)) mkdir($this->targetDir,0755,true);

        // Remove old temp files
        if (is_dir($this->targetDir) && ($dir = opendir($this->targetDir))) {
            while (($file = readdir($dir)) !== false) {
                $filePath = $this->targetDir.'/'.$file;
                // Remove temp files if they are older than the max age
                if (preg_match('/\\.tmp$/', $file) && (filemtime($filePath) < time() - $maxFileAge)) @unlink($filePath);
            }
            closedir($dir);
        }
        else die('{
        "status":"error",
        "type":"uploader",
        "message":"Failed to open temp directory."
        }');

        // Look for the content type header
        if (isset($_SERVER["HTTP_CONTENT_TYPE"])) $contentType = $_SERVER["HTTP_CONTENT_TYPE"];

        if (isset($_SERVER["CONTENT_TYPE"])) $contentType = $_SERVER["CONTENT_TYPE"];

        // Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
        if (strpos($contentType, "multipart") !== false) {
            if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
                // Open temp file
                $out = fopen($this->targetDir . '/' . $this->filename, $chunk == 0 ? "wb" : "ab");
                if ($out) {
                    // Read binary input stream and append it to temp file
                    $in = fopen($_FILES['file']['tmp_name'], "rb");
                    if ($in) {
                        while ($buff = fread($in, 4096)) fwrite($out, $buff);
                    }
                    else die('{
                    "status":"error",
                    "type":"uploader",
                    "message":"Failed to open input stream."
                    }');

                    fclose($in);
                    fclose($out);
                    @unlink($_FILES['file']['tmp_name']);
                }
                else die('{
                    "status":"error",
                    "type":"uploader",
                    "message":"Failed to open output stream. to dir "
                    }');
            }
            else die('{
                    "status":"error",
                    "type":"uploader",
                    "message":"Failed to move uploaded file."
                    }');
        }
        else {
            // Open temp file
            $out = fopen($this->targetDir . '/' . $this->filename, $chunk == 0 ? "wb" : "ab");
            if ($out) {
                // Read binary input stream and append it to temp file
                $in = fopen("php://input", "rb");
                if ($in) {
                    while ($buff = fread($in, 4096)) fwrite($out, $buff);
                }
                else die('{
                    "status":"error",
                    "type":"uploader",
                    "message":"Failed to open input stream."
                    }');
                fclose($in);
                fclose($out);
            }
            else die('{
                    "status":"error",
                    "type":"uploader",
                    "message":"Failed to open output stream."
                    }');
        }

        if (($chunk+1 == $chunks)||$chunks==0) {
            $this->afterFileUploaded();
        }
        else die('{
                    "status":"error",
                    "type":"uploader",
                    "message":"part '.++$chunk.' из '.$chunks.'"
                    }');
    }
    private function afterFileUploaded() {
        $this->after_uploaded_db_work();
        $this->after_uploaded_fs_work();
    }
    private function register_file_type() {
        if(!$query=$this->uCore->query("uDrive","SELECT
        `type_id`
        FROM
        `u235_file_types`
        WHERE
        `ext`='".$this->file_ext."' AND
        `mime_type`='".$this->mime_type."'
        ")) $this->uCore->error(10);
		if(!mysqli_num_rows($query)) {
            if(!$query=$this->uCore->query("uDrive","SELECT
            `type_id`
            FROM
            `u235_file_types`
            ORDER BY
            `type_id` DESC
            LIMIT 1
            ")) $this->uCore->error(20);
            if(mysqli_num_rows($query)) {
                $qr=$query->fetch_object();
                $type_id=$qr->type_id+1;
            }
            else $type_id=1;
            if(!$this->uCore->query("uDrive","INSERT INTO
            `u235_file_types` (
            `type_id`,
            `ext`,
            `mime_type`
            ) VALUES (
            '".$type_id."',
            '".$this->file_ext."',
            '".$this->mime_type."'
            )
            ")) $this->uCore->error(30);
        }
    }
    private function after_uploaded_db_work() {
        //Get new file_id
        if(!$query=$this->uCore->query("uDrive","SELECT
        `file_id`
        FROM
        `u235_files`
        WHERE
        `site_id`='".site_id."'
        ORDER BY
        `file_id` DESC
        LIMIT 1
        ")) $this->uCore->error(40);
        if(mysqli_num_rows($query)>0) {
            $qr=$query->fetch_object();
            $this->file_id=$qr->file_id+1;
        }
        else $this->file_id=1;

        //make hash name for file
        $this->save_file_name=uFunc::genHash();

        if($this->folder_id) {
            //Check if this folder_id exists
            if(!$query=$this->uCore->query("uDrive","SELECT
            `file_id`
            FROM
            `u235_files`
            WHERE
            `file_id`='".$this->folder_id."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(50);
            if(!mysqli_num_rows($query)>0) {
                uFunc::rmdir($this->targetDir);
                $this->uCore->error(60);
            }
        }

        //$this->mime_type=uFunc::ext2mime(strtolower($this->file_ext));
        //if(!$this->mime_type) $this->mime_type='application/octet-stream';
        $this->mime_type=mime_content_type($this->targetDir.'/'.$this->filename);

        $this->source_filename=$_SERVER['DOCUMENT_ROOT'].'/'.$this->targetDir.'/'.$this->filename;
        $this->file_size=filesize($this->source_filename);

        //Save file to db
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
		`owner_id`,
		`site_id`
		) VALUES (
		'".$this->file_id."',
		'".uString::sql2text($this->orig_file_name)."',
		'".$this->file_size."',
		'".$this->file_ext."',
		'".$this->mime_type."',
		'".$this->save_file_name."',
		'".time()."',
		'".$this->folder_id."',
		'".$this->uSes->get_val("user_id")."',
		'".site_id."'
		)")) $this->uCore->error(70);

        $this->register_file_type();
    }
    private function after_uploaded_fs_work() {
        $dir = $_SERVER['DOCUMENT_ROOT'].'/'.$this->folder.'/'.$this->file_id.'/'; //Адрес директории для сохранения файла
        // Create dir
        if (!file_exists($dir)) mkdir($dir,0755,true);

        //copy file
        copy ($this->source_filename,$dir.$this->save_file_name);
        //Delete temp directory
        uFunc::rmdir($this->targetDir);

        /*if(strpos('_'.$this->mime_type,'image')) {
            //make thumb
            $height=$this->uCore->uFunc->getConf("img_thumb_height","content");

            $im = new Imagick($dir.$this->save_file_name);
            $im->setImageFormat('jpeg');

            $im->adaptiveResizeImage (0,$height);
            $im->writeImage($dir.$this->file_id.'_sm.jpg');

            $im->clear();
            $im->destroy();
        }*/
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new uSes($this->uCore);
        $this->sendHeaders();
        $this->checkData();
        $this->uploader();



        if($this->mime_type=='folder') {
            $file_ext_icon='icon-folder';
        }
        else {
            if(isset($this->uCore->uFunc->file_ext2fonticon[$this->file_ext])) $file_ext_icon=$this->uCore->uFunc->file_ext2fonticon[$this->file_ext];
            else $file_ext_icon='icon-file-unknown';
        }

        die('{
        "status":"done",
        "file_id":"'.$this->file_id.'",
        "file_name":"'.rawurlencode($this->orig_file_name).'",
        "file_hashname":"'.$this->save_file_name.'",
        "file_mime":"'.$this->mime_type.'",
        "file_size":"'.$this->file_size.'",
        "file_ext_icon":"'.$file_ext_icon.'",
        "file_timestamp":"'.time().'",
        "folder_id":"'.$this->folder_id.'"
        }');
    }
}
$uDrive=new uDrive_my_drive_uploader ($this);
