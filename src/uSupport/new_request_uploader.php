<?php
require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";

class uSup_new_request_uploader {
    public $uFunc;
    public $uSes;
    private $uCore,
        $tic_id,
        $folder,$targetDir,$orig_file_name,$filename,$save_file_name,$file_ext,$file_id,$source_filename,$mime_type,$file_size;
    private function finish($status,$type='',$msg='',$file_id='',$file_name='',$file_mime='',$file_size='') {
        die('{"status" : "'.$status.'", "type" : "'.$type.'", "message" : "'.$msg.'", "file_id":"'.$file_id.'","file_name":"'.$file_name.'","file_size":"'.$file_size.'","file_mime":"'.$file_mime.'"}');
    }
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
        if(!isset($_REQUEST['req_id'])) $this->finish("error", "check", "Haven't got all data required");

        $this->tic_id=$_REQUEST['req_id'];

        if(!uString::isDigits($this->tic_id)) $this->finish("error", "check","Handled id error");

        $this->folder='msgs_files/'.site_id.'/'.$this->tic_id;

        $this->targetDir = $this->uCore->mod.'/'.$this->folder.'/tmp'.$_REQUEST['req_id'];
    }
    private function check_access() {
        //consultant or operator
        if($this->uCore->access(9)) return true;
        if($this->uCore->access(8)) return true;

        if(!$query=$this->uCore->query("uSup","SELECT
            `user_id`
            FROM
            `u235_requests`
            WHERE
            `tic_id`='".$this->tic_id."' AND
            `tic_status`!='req_closed' AND
            `tic_status`!='case_closed' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(1);
        if(!mysqli_num_rows($query)) return false;//request either not found or status is closed
        $req=$query->fetch_object();

        if($req->user_id==$this->uSes->get_val("user_id")) {
            //check if user is client of any company
            if(!$query=$this->uCore->query("uSup","SELECT
            `user_id`
            FROM
            `u235_com_users`
            WHERE
            `user_id`='".$this->uSes->get_val("user_id")."' AND
            `site_id`='".site_id."'
            LIMIT 1
            ")) $this->uCore->error(3);
            if(mysqli_num_rows($query)) return true;

            //check if we can receive request from users not in companies
            if($this->uCore->uFunc->getConf("receive_only_from_comps_users","uSup")=='0') return true;
        }

        return false;
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
        $this->orig_file_name=uString::text2sql($this->filename);
        $this->filename=uString::text2filename(uString::rus2eng($this->filename),true);
        //$this->filename = preg_replace('/[^\w\._]+/', '', $this->filename);

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
        else $this->finish("error", "uploader", "Failed to open temp directory.");

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
                    else $this->finish("error", "uploader", "Failed to open input stream.");

                    fclose($in);
                    fclose($out);
                    @unlink($_FILES['file']['tmp_name']);
                }
                else $this->finish("error", "uploader", "Failed to open output stream.");
            }
            else $this->finish("error", "uploader", "Failed to move uploaded file.");
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
                else $this->finish("error", "uploader", "Failed to open input stream.");
                fclose($in);
                fclose($out);
            }
            else $this->finish("error", "uploader", "Failed to open output stream");
        }

        if (($chunk+1 == $chunks)||$chunks==0) {
            $this->afterFileUploaded();
        }
        else $this->finish('continue', '','Часть '.++$chunk.' из '.$chunks);
    }
    private function afterFileUploaded() {
        $this->after_uploaded_db_work();
        $this->after_uploaded_fs_work();
    }
    private function after_uploaded_db_work() {
        //Get new file_id
        if(!$query=$this->uCore->query("uSup","SELECT
        `file_id`
        FROM
        `u235_msgs_files`
        WHERE
        `site_id`='".site_id."'
        ORDER BY
        `file_id` DESC
        LIMIT 1
        ")) $this->uCore->error(3);
        if(mysqli_num_rows($query)>0) {
            $qr=$query->fetch_object();
            $this->file_id=$qr->file_id+1;
        }
        else $this->file_id=1;

        //make hash name for file
        $this->save_file_name=uFunc::genHash();

        //Check if this tic_id exists
        if(!$query=$this->uCore->query("uSup","SELECT
        `tic_id`
        FROM
        `u235_requests`
        WHERE
        `tic_id`='".$this->tic_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(4);
        if(!mysqli_num_rows($query)>0) {
            uFunc::rmdir($this->targetDir);
            $this->uCore->error(5);
        }

        $this->mime_type=uFunc::ext2mime(strtolower($this->file_ext));
        if(!$this->mime_type) $this->mime_type='application/octet-stream';

        $this->source_filename=$_SERVER['DOCUMENT_ROOT'].'/'.$this->targetDir.'/'.$this->filename;
        $this->file_size=filesize($this->source_filename);

        //Save file to db
        if(!$this->uCore->query("uSup","INSERT INTO
        `u235_msgs_files` (
        `file_id`,
        `owner_id`,
        `timestamp`,
        `filename`,
        `file_size`,
        `file_mime`,
        `hash`,
        `msg_id`,
        `tic_id`,
        `site_id`
        ) VALUES (
        '".$this->file_id."',
        '".$this->uSes->get_val("user_id")."',
        '".time()."',
        '".$this->orig_file_name."',
        '".$this->file_size."',
        '".$this->mime_type."',
        '".$this->save_file_name."',
        '0',
        '".$this->tic_id."',
        '".site_id."'
        )")) $this->uCore->error(6);
    }
    private function after_uploaded_fs_work() {
        $dir = $_SERVER['DOCUMENT_ROOT'].'/'.$this->uCore->mod.'/'.$this->folder.'/'.$this->file_id.'/'; //Адрес директории для сохранения файла
        // Create dir
        if (!file_exists($dir)) mkdir($dir,0755,true);

        if(!uFunc::create_empty_index($this->uCore->mod.'/'.$this->folder.'/'.$this->file_id)) $this->uCore->error(7);

        //copy file
        copy ($this->source_filename,$dir.$this->save_file_name);
        //Delete temp directory
        uFunc::rmdir($this->targetDir);

        if(strpos('_'.$this->mime_type,'image')&&class_exists('Imagick')) {
            //make thumb
            $height=150;


            $im = new Imagick($dir.$this->save_file_name);
            $im->setImageFormat('jpeg');

            $im->resizeImage(0,$height,Imagick::FILTER_LANCZOS,1);

            // Set to use jpeg compression
            $im->setImageCompression(Imagick::COMPRESSION_JPEG);
// Set compression level (1 lowest quality, 100 highest quality)
            $im->setImageCompressionQuality(75);
// Strip out unneeded meta data
            $im->stripImage();

            $im->writeImage($dir.$this->file_id.'_sm.jpg');

            $im->clear();
            $im->destroy();
        }
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);

        $this->sendHeaders();
        $this->checkData();
        if(!$this->check_access()) die('{"status":"forbidden"}');
        $this->uploader();
        $this->finish('done', '','',$this->file_id,rawurlencode(uString::sql2text($this->orig_file_name)),$this->mime_type,$this->file_size);
    }
}
$newClass=new uSup_new_request_uploader  ($this);
