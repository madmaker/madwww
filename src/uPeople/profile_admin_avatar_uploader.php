<?php
class uPeople_admin_avatar_uploader {
    private $uCore,
    $user_id,$folder,$target_dir,$final_dir;

    private function send_headers() {
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
    }
    private function check_data() {
        if(!isset($_REQUEST['hashId'],$_REQUEST['hash'],$_REQUEST['userIds'])) die('{"status" : "error", "type" : "check", "message" : "Не переданы все обязательные данные"}');

        if(!uString::isDigits($_REQUEST['userIds'])) die('{"status" : "error", "type" : "check", "message" : "Ошибка передачи данных: userIds"}');
        if(!uString::isDigits($_REQUEST['hashId'])) die('{"status" : "error", "type" : "check", "message" : "Ошибка передачи данных: hash"}');

        if(!$this->uCore->uFunc->sesHack_test($_REQUEST['hashId'],$_REQUEST['hash'])) $this->finish("error", "check", "hash check error: ");

        if(!$this->uCore->access(10)) die("{'status' : 'forbidden'}");

        $this->user_id=$_REQUEST['userIds'];
        if(!uString::isDigits($this->user_id)) $this->error(1);

        $this->folder='avatars';
        $this->target_dir=$this->uCore->mod.'/'.$this->folder.'/'.site_id.'/tmp'.$_REQUEST['hashId'];
        $this->final_dir=$this->uCore->mod.'/'.$this->folder.'/'.site_id.'/';
    }
    private function upload() {
        $maxFileAge = 60 * 60; // Temp file age in seconds

        // 5 minutes execution time
        @set_time_limit(5 * 60);

        // Get parameters
        $chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
        $chunks = isset($_REQUEST["chunks"]) ? $_REQUEST["chunks"] : 0;
        $fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';

        // Clean the fileName for security reasons
        $fileName =uString::text2filename(uString::rus2eng($fileName),true);
        //$fileName = preg_replace('/[^\w\._]+/', '', $fileName);

        // Make sure the fileName is unique but only if chunking is disabled
        if ($chunk < 1 && file_exists($this->target_dir . DIRECTORY_SEPARATOR . $fileName)) {
            $ext = strrpos($fileName, '.');
            $fileName_a = substr($fileName, 0, $ext);
            $fileName_b = substr($fileName, $ext);

            $count = 1;
            while (file_exists($this->target_dir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
                $count++;
            $fileName = $fileName_a . '_' . $count . $fileName_b;
        }

        // Create target dir
        if (!file_exists($this->target_dir)) @mkdir($this->target_dir,0755,true);

        // Remove old temp files
        if (is_dir($this->target_dir) && ($dir = opendir($this->target_dir))) {
            while (($file = readdir($dir)) !== false) {
                $filePath = $this->target_dir. DIRECTORY_SEPARATOR . $file;
                // Remove temp files if they are older than the max age
                if (preg_match('/\\.tmp$/', $file) && (filemtime($filePath) < time() - $maxFileAge)) @unlink($filePath);
            }
            closedir($dir);
        }
        else die('{"status" : "error", "type" : "uploader", "message" : "Failed to open temp directory."}');

        // Look for the content type header
        if (isset($_SERVER["HTTP_CONTENT_TYPE"])) $contentType = $_SERVER["HTTP_CONTENT_TYPE"];

        if (isset($_SERVER["CONTENT_TYPE"])) $contentType = $_SERVER["CONTENT_TYPE"];

        // Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
        if (strpos($contentType, "multipart") !== false) {
            if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
                // Open temp file
                $out = fopen($this->target_dir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
                if ($out) {
                    // Read binary input stream and append it to temp file
                    $in = fopen($_FILES['file']['tmp_name'], "rb");
                    if ($in) {
                        while ($buff = fread($in, 4096)) fwrite($out, $buff);
                    }
                    else	die('{"status" : "error", "type" : "uploader", "message" : "Failed to open input stream."}');

                    fclose($in);
                    fclose($out);
                    @unlink($_FILES['file']['tmp_name']);
                }
                else	die('{""status" : "error", "type" : "uploader", "message" : "Failed to open output stream."}');
            }
            else	die('{"status" : "error", "type" : "uploader", "message" : "Failed to move uploaded file."}');
        }
        else {
            // Open temp file
            $out = fopen($this->target_dir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
            if ($out) {
                // Read binary input stream and append it to temp file
                $in = fopen("php://input", "rb");
                if ($in) {
                    while ($buff = fread($in, 4096)) fwrite($out, $buff);
                }
                else	die('{"status" : "error", "type" : "uploader", "message" : "Failed to open input stream."}');
                fclose($in);
                fclose($out);
            }
            else	die('{"status" : "error", "type" : "uploader", "message" : "Failed to open output stream"}');
        }

        if (($chunk+1 == $chunks)||$chunks==0) {

            //Update avatar timestamp
            if(!$query=$this->uCore->query('uPeople',"UPDATE
            `u235_people`
            SET
            `avatar_timestamp`='".time()."'
            WHERE
            `user_id`='".$this->user_id."' AND
            `site_id`='".site_id."'
            ")) $this->error(2);

            if(!uFunc::create_empty_index($this->target_dir)) $this->uCore->error(3);

            $source_filename=$this->target_dir.'/'.$fileName;

            $im = new Imagick($source_filename);
            $im->setImageFormat('jpeg');

            // Set to use jpeg compression
            $im->setImageCompression(Imagick::COMPRESSION_JPEG);
// Set compression level (1 lowest quality, 100 highest quality)
            $im->setImageCompressionQuality(75);
// Strip out unneeded meta data
            $im->stripImage();

            $im->resizeImage(200,0,Imagick::FILTER_LANCZOS,1); $im->writeImage($this->final_dir.$this->user_id.'_big.jpg');
            $im->resizeImage(140,0,Imagick::FILTER_LANCZOS,1); $im->writeImage($this->final_dir.$this->user_id.'_mid.jpg');
            $im->resizeImage(40,0,Imagick::FILTER_LANCZOS,1); $im->writeImage($this->final_dir.$this->user_id.'_sm.jpg');

            $im->clear();
            $im->destroy();

            //Delete temp directory
            uFunc::rmdir($this->target_dir);

            die ("{'status' : 'done', 'timestamp' : '".time()."'}");
        }
        die("{'status' : 'continue', 'message' : 'Часть ".++$chunk." из $chunks'}");
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;

        $this->send_headers();
        $this->check_data();
        $this->upload();
    }
}
$uPeople=new uPeople_admin_avatar_uploader($this);
