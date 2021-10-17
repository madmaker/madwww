<?php
use processors\uFunc;

require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';

class uForms_form_admin_file_uploader {
    private $uCore,$form_id,$folder,$targetDir,$filename,$file_ext,$file_id,$source_filename,$mime_type,$file_size;
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
        header('Content-type: text/html; charset=utf-8');
    }
    private function checkData() {
        //Check for required data
        if(!isset($_REQUEST['form_id'])) $this->finish("error", "check", "Haven't got all data required");

        $this->form_id=$_REQUEST['form_id'];

        if(!uString::isDigits($this->form_id)) $this->finish("error", "check","Handled id error");

        $this->folder='form_files/'.site_id.'/'.$this->form_id;

        $this->targetDir = $this->uCore->mod.'/'.$this->folder.'/tmp'.$this->uSes->get_val('sesId');
    }
    private function check_access() {
        if(!$this->uSes->access(5)) $this->finish('error','check','You have no rights to upload files');
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
        $dot = strrpos($this->filename, '.');
        $this->file_ext = substr($this->filename, $dot+1);
        $this->filename=uString::text2filename(uString::rus2eng($this->filename),true);

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
        else $this->finish('continue', '','Part '.++$chunk.' of '.$chunks);
    }
    private function afterFileUploaded() {
        $this->after_uploaded_db_work();
        $this->after_uploaded_fs_work();
    }
    private function after_uploaded_db_work() {
        //Check if this form_id exists
        if(!$query=$this->uCore->query("uForms","SELECT
        `form_id`
        FROM
        `u235_forms`
        WHERE
        `form_id`='".$this->form_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(1);
        if(!mysqli_num_rows($query)>0) {
            uFunc::rmdir($this->targetDir);
            $this->uCore->error(2);
        }


        //Get new file_id
        if(!$query=$this->uCore->query("uForms","SELECT
        `file_id`
        FROM
        `u235_forms_files`
        WHERE
        `form_id`='".$this->form_id."' AND
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

        //check file name to be unique for this item
        $this->mime_type=uFunc::ext2mime(strtolower($this->file_ext));
        if(!$this->mime_type) $this->mime_type='application/octet-stream';

        $this->source_filename=$_SERVER['DOCUMENT_ROOT'].'/'.$this->targetDir.'/'.$this->filename;
        $this->file_size=filesize($this->source_filename);

        //Save file to db
        if(!$this->uCore->query("uForms","INSERT INTO
        `u235_forms_files` (
        `form_id`,
        `file_id`,
        `file_name`,
        `file_size`,
        `file_mime`,
        `timestamp`,
        `site_id`
        ) VALUES (
        '".$this->form_id."',
        '".$this->file_id."',
        '".$this->filename."',
        '".$this->file_size."',
        '".$this->mime_type."',
        '".time()."',
        '".site_id."'
        )")) $this->uCore->error(4);
    }
    private function after_uploaded_fs_work() {
        $dir = $this->uCore->mod.'/'.$this->folder.'/'.$this->file_id.'/'; //Адрес директории для сохранения файла
        // Create dir
        if (!file_exists($dir)) mkdir($dir,0755,true);

        if(!uFunc::create_empty_index($this->uCore->mod.'/'.$this->folder.'/'.$this->file_id)) $this->uCore->error(5);

        //copy file
        copy ($this->source_filename,$dir.$this->filename);
        //Delete temp directory
        uFunc::rmdir($this->targetDir);

        if(strpos('_'.$this->mime_type,'image')) {
            //make thumb
            $height=$this->uCore->uFunc->getConf("img_thumb_height","content");
            if($height)

            if(!class_exists('Imagick')) return false;
            $im = new Imagick($dir.$this->filename);
            $im->setImageFormat('jpeg');

            // Set to use jpeg compression
            $im->setImageCompression(Imagick::COMPRESSION_JPEG);
            // Set compression level (1 lowest quality, 100 highest quality)
            $im->setImageCompressionQuality(100);
            // Strip out unneeded meta data
            $im->stripImage();

            $im->resizeImage(0,$height,Imagick::FILTER_LANCZOS,1);
            $im->writeImage($dir.$this->file_id.'_sm.jpg');

            $im->clear();
            $im->destroy();
        }
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);

        $this->sendHeaders();
        $this->checkData();
        $this->check_access();
        $this->uploader();
        $this->finish('done', '','',$this->file_id,rawurlencode($this->filename),$this->mime_type,$this->file_size);
    }
}
$newClass=new uForms_form_admin_file_uploader  ($this);
