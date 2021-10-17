<?php
require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class uSlider_slide_bg_uploader {
    private $uSes;
    private $uCore,$slide_id,$folder,$targetDir,$orig_file_name,$filename,$save_file_name,$file_ext,$source_filename,$mime_type,$file_size;

    private function finish($status,$type='',$msg='',$slide_id=0) {
        die('{"status" : "'.$status.'", "type" : "'.$type.'", "message" : "'.$msg.'", "slide_id":"'.$slide_id.'"}');
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
        if(!isset($_REQUEST['slide_id'])) $this->finish("error", "check", "Haven't got all data required");

        $this->slide_id=$_REQUEST['slide_id'];
        if(!uString::isDigits($this->slide_id)) $this->finish("error", "check","Handled id error");

        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");

        $this->folder='slides_bg/'.site_id.'/'.$this->slide_id;

        $this->targetDir = 'uSlider/'.$this->folder.'/tmp'.$this->slide_id;
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
        $this->filename =uString::text2filename(uString::rus2eng($this->filename),true);
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
        /** @noinspection PhpUndefinedVariableInspection */
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

        //clear uPage cache
        require_once "uSlider/inc/common.php";
        $uSlider=new \uSlider\common($this->uCore);
        $slider_id=$uSlider->slide_id2slider_id($this->slide_id);
        $uSlider->clear_cache_by_slider_id($slider_id);
    }
    private function after_uploaded_db_work() {
        if(!isset($this->uFunc)) $this->uFunc=new \processors\uFunc($this->uCore);

        $this->mime_type=uFunc::ext2mime(strtolower($this->file_ext));
        if(!$this->mime_type) $this->mime_type='application/octet-stream';

        if(!strpos('_'.$this->mime_type,'image')) $this->finish("error", "check", "wrong file format");

        //update slide's img_timestamp
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSlider")->prepare("UPDATE
            u235_slides
            SET
            img_timestamp=:img_timestamp
            WHERE
            slide_id=:slide_id AND
            site_id=:site_id
            ");
            $img_timestamp=time();
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':img_timestamp', $img_timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slide_id', $this->slide_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}

        $this->source_filename=$_SERVER['DOCUMENT_ROOT'].'/'.$this->targetDir.'/'.$this->filename;
        $this->file_size=filesize($this->source_filename);
    }
    private function after_uploaded_fs_work() {
        if(!isset($this->uFunc)) $this->uFunc=new \processors\uFunc($this->uCore);

        $this->save_file_name=$this->slide_id.'.jpg';
        $dir = $_SERVER['DOCUMENT_ROOT'].'/uSlider/'.$this->folder.'/'; //Адрес директории для сохранения файла
        // Create dir
        if(!file_exists($dir)) mkdir($dir,0755,true);
        if(!uFunc::create_empty_index('uSlider/'.$this->folder)) $this->uFunc->error(20);

        //copy file
        copy ($this->source_filename,$dir.$this->save_file_name);
        //Delete temp directory
        uFunc::rmdir($this->targetDir);

        if(class_exists("Imagick")) {
            try {
                $im = new Imagick($dir . $this->save_file_name);
                $im->setImageFormat('jpeg');

                // Set to use jpeg compression
                $im->setImageCompression(Imagick::COMPRESSION_JPEG);
                // Set compression level (1 lowest quality, 100 highest quality)
                $im->setImageCompressionQuality(100);

                $im->writeImage($dir.$this->save_file_name);
                $im->clear();
                $im->destroy();
            } catch (ImagickException $e) {}
        }
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new uSes($this->uCore);

        $this->sendHeaders();
        $this->checkData();
        $this->uploader();
        $this->finish('done', '','',$this->slide_id);
    }
}
$uSlider=new uSlider_slide_bg_uploader ($this);