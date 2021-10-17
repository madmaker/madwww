<?php
namespace uCat\admin;
use Imagick;
use PDO;
use PDOException;
use processors\uFunc;
use uCat_sect_avatar;
use uSes;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class admin_sects_avatar_uploader_bg {
    public $uFunc;
    public $uSes;
    public $sect_id;
    public $targetDir;
    public $chunk;
    public $chunks;
    public $fileName;
    private $uCore;
    private function headers() {
        // HTTP headers for no cache etc
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        Header('Content-type: text/html; charset=utf-8');
    }
    private function check_data() {
        //Check for required data
        if(!isset($_REQUEST['sect_id'])) die('{"status" : "error", "type" : "check", "message" : "Не переданы все обязательные данные"}');

        if(!uString::isDigits($_REQUEST['sect_id'])) die('{"status" : "error", "type" : "check", "message" : "Ошибка передачи данных: страница"}');

        $this->sect_id=$_REQUEST['sect_id'];
    }
    private function upload() {
        $this->targetDir = 'uCat/sect_avatars/'.site_id.'/tmp/'.$this->sect_id;
        $maxFileAge = 60 * 60; // Temp file age in seconds

// 5 minutes execution time
        @set_time_limit(5 * 60);

// Get parameters
        $this->chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
        $this->chunks = isset($_REQUEST["chunks"]) ? $_REQUEST["chunks"] : 0;
        $this->fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';

// Clean the fileName for security reasons
        $this->fileName =uString::text2filename(uString::rus2eng($this->fileName),true);
//$this->fileName = preg_replace('/[^\w\._]+/', '', $this->fileName);

// Make sure the fileName is unique but only if chunking is disabled
        if ($this->chunk < 1 && file_exists($this->targetDir . "/" . $this->fileName)) {
            $ext = strrpos($this->fileName, '.');
            $fileName_a = substr($this->fileName, 0, $ext);
            $fileName_b = substr($this->fileName, $ext);

            $count = 1;
            while (file_exists($this->targetDir . "/" . $fileName_a . '_' . $count . $fileName_b))
                $count++;

            $this->fileName = $fileName_a . '_' . $count . $fileName_b;
        }

// Create target dir
        if (!file_exists($this->targetDir)) @mkdir($this->targetDir,0755,true);

// Remove old temp files
        if (is_dir($this->targetDir) && ($dir = opendir($this->targetDir))) {
            while (($file = readdir($dir)) !== false) {
                $filePath = $this->targetDir . "/" . $file;
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
        /** @noinspection PhpUndefinedVariableInspection */
        if (strpos($contentType, "multipart") !== false) {
            if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
                // Open temp file
                $out = fopen($this->targetDir . "/" . $this->fileName, $this->chunk == 0 ? "wb" : "ab");
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
            $out = fopen($this->targetDir . "/" . $this->fileName, $this->chunk == 0 ? "wb" : "ab");
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
    private function save() {
        if (($this->chunk+1 == $this->chunks)||$this->chunks==0) {
            $dir = 'uCat/sect_avatars/'.site_id.'/'; //Адрес директории для сохранения картинки
            $source_filename=$this->targetDir.'/'.$this->fileName;

            //check if cat belongs to this site id
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
                sect_id
                FROM
                u235_sects
                WHERE
                site_id='".site_id."' AND
                sect_id='".$this->sect_id."'
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sect_id', $this->sect_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if(!$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(10);
            }
            catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}

            //Update avatar timestamp
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE 
                u235_sects
                SET
                sect_avatar_time=:sect_avatar_time
                WHERE
                sect_id=:sect_id AND
                site_id=:site_id
                ");
                $sect_avatar_time=time();
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sect_avatar_time', $sect_avatar_time,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sect_id', $this->sect_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}


            $this->uFunc->rmdir($dir.$this->sect_id);
            if (!file_exists($dir.$this->sect_id)) mkdir($dir.$this->sect_id,0755,true);
            if(!$this->uFunc->create_empty_index($dir.$this->sect_id)) $this->uFunc->error(40);

            try {
                $img = new Imagick($source_filename);

                $img->setImageFormat('jpeg');
                $img->setImageAlphaChannel(11); // Imagick::ALPHACHANNEL_REMOVE
                $img->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
                $img->writeImage($dir.$this->sect_id.'/orig.jpg');

                $img->clear();
                $img->destroy();
            } catch (\ImagickException $e) { }

            //Delete temp directory
            uFunc::rmdir($this->targetDir);

            require_once "inc/sect_avatar.php";
            $avatar=new uCat_sect_avatar($this->uCore);

            die ("{
            'status' : 'done',
            'sect_id' : '".$this->sect_id."',
            'sect_avatar_addr':'".$avatar->get_avatar('sects_list',$this->sect_id)."'
            }");
        }
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");

        $this->headers();
        $this->check_data();
        $this->upload();
        $this->save();
        die("{'status' : 'continue', 'message' : 'Часть ".++$this->chunk." из $this->chunks'}");
    }
}
new admin_sects_avatar_uploader_bg($this);