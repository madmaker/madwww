<?php
namespace uSupport;

use PDO;
use PDOException;
use processors\uFunc;
use uSes;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class company_info_avatar_uploader {
    private $uFunc,$uSes, $com_id, $folder, $chunk, $chunks, $fileName, $targetDir, $contentType, $uCore;

    private function check_data() {
        // HTTP headers for no cache etc
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        //Check for required data
        if(!isset($_REQUEST['com_id'])) die('{"status" : "error", "type" : "check", "message" : "Не переданы все обязательные данные"}');

        $this->com_id=$_REQUEST['com_id'];
        if(!uString::isDigits($this->com_id)) die('{"status" : "error", "type" : "check", "message" : "Ошибка передачи данных: com_id"}');


        if(!$this->uSes->access(201)) {//if user is not operator
            //check if current user is admin of this company
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
                user_id
                FROM
                u235_com_users
                WHERE
                user_id=:user_id AND
                com_id=:com_id AND
                admin=1 AND
                site_id=:site_id
                ");
                $site_id=site_id;
                $user_id=$this->uSes->get_val("user_id");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $this->com_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if(!$stm->fetch(PDO::FETCH_OBJ)) die("forbidden");
            }
            catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
        }
    }
    private function prepare_upload() {

        $this->folder='com_avatars/'.site_id;

        $this->targetDir = 'uSupport/'.$this->folder.'/tmp'.$_REQUEST['hashId'];

        $maxFileAge = 60 * 60; // Temp file age in seconds

        // 5 minutes execution time
        @set_time_limit(5 * 60);

        // Uncomment this one to fake upload time
        // usleep(5000);

        // Get parameters
        $this->chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
        $this->chunks = isset($_REQUEST["chunks"]) ? $_REQUEST["chunks"] : 0;
        $this->fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';

        // Clean the fileName for security reasons
        $this->fileName=uString::text2filename(uString::rus2eng($this->fileName),true);
        //$this->fileName = preg_replace('/[^\w\._]+/', '', $this->fileName);

        // Make sure the fileName is unique but only if chunking is disabled
        if ($this->chunk < 1 && file_exists($this->targetDir . DIRECTORY_SEPARATOR . $this->fileName)) {
            $ext = strrpos($this->fileName, '.');
            $fileName_a = substr($this->fileName, 0, $ext);
            $fileName_b = substr($this->fileName, $ext);

            $count = 1;
            while (file_exists($this->targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
                $count++;
            $this->fileName = $fileName_a . '_' . $count . $fileName_b;
        }

        // Create target dir
        if (!file_exists($this->targetDir)) mkdir($this->targetDir,0755,true);

        // Remove old temp files
        if (is_dir($this->targetDir) && ($dir = opendir($this->targetDir))) {
            while (($file = readdir($dir)) !== false) {
                $filePath = $this->targetDir . DIRECTORY_SEPARATOR . $file;
                // Remove temp files if they are older than the max age
                if (preg_match('/\\.tmp$/', $file) && (filemtime($filePath) < time() - $maxFileAge)) @unlink($filePath);
            }
            closedir($dir);
        }
        else die('{"status" : "error", "type" : "uploader", "message" : "Failed to open temp directory."}');

// Look for the content type header
        if (isset($_SERVER["HTTP_CONTENT_TYPE"])) $this->contentType = $_SERVER["HTTP_CONTENT_TYPE"];

        if (isset($_SERVER["CONTENT_TYPE"])) $this->contentType = $_SERVER["CONTENT_TYPE"];
    }
    private function upload_file() {
        // Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
        if (strpos($this->contentType, "multipart") !== false) {
            if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
                // Open temp file
                $out = fopen($this->targetDir . DIRECTORY_SEPARATOR . $this->fileName, $this->chunk == 0 ? "wb" : "ab");
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
            $out = fopen($this->targetDir . DIRECTORY_SEPARATOR . $this->fileName, $this->chunk == 0 ? "wb" : "ab");
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
    }
    private function save2db() {
        if (($this->chunk+1 == $this->chunks)||$this->chunks==0) {

            //Check file name to not be used in database
            //$newFilename=$this->com_id.'.jpg';
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSup")->prepare("UPDATE
                u235_comps
                SET
                logo_timestamp=:logo_timestamp
                WHERE
                com_id=:com_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                $logo_timestamp=time();
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':logo_timestamp', $logo_timestamp,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $this->com_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}

            $dir = $_SERVER['DOCUMENT_ROOT'].'/uSupport/'.$this->folder.'/'; //Адрес директории для сохранения картинки
            $this->uFunc->rmdir($dir.$this->com_id);
            if (!file_exists($dir.$this->com_id)) mkdir($dir.$this->com_id,0755,true);
            if(!$this->uFunc->create_empty_index('uSupport/'.$this->folder)) $this->uFunc->error(40);
            $source_filename=$_SERVER['DOCUMENT_ROOT'].'/'.$this->targetDir.'/'.$this->fileName;

            $im = new \Imagick($source_filename);
            $im->setImageFormat('jpeg');
            $im->stripImage();

            $im->writeImage($dir.$this->com_id.'/orig.jpg');

            $im->clear();
            $im->destroy();

            //Delete temp directory
            $this->uFunc->rmdir($this->targetDir);

            include_once 'inc/com_avatar.php';
            $avatar=new \uSup_com_avatar($this);

            die ("{'status' : 'done', 'url' : '".rawurlencode($avatar->get_avatar('com_page',$this->com_id,time()))."'}");
        }
        die("{'status' : 'continue', 'message' : 'Часть ".++$this->chunk." из $this->chunks'}");
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);

        $this->check_data();
        $this->prepare_upload();
        $this->upload_file();
        $this->save2db();
    }
}
new company_info_avatar_uploader($this);