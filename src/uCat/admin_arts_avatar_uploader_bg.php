<?php
namespace uCat\admin;
use Imagick;
use PDO;
use PDOException;
use processors\uFunc;
use uCat_art_avatar;
use uSes;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class admin_arts_avatar_uploader_bg {
    public $uFunc;
    public $uSes;
    public $targetDir;
    public $chunk;
    public $chunks;
    public $fileName;
    public $art_id;
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
        if(!isset($_REQUEST['art_id'])) die('{"status" : "error", "type" : "check", "message" : "Не переданы все обязательные данные"}');

        if(!uString::isDigits($_REQUEST['art_id'])) die('{"status" : "error", "type" : "check", "message" : "Ошибка передачи данных: страница"}');

        $this->art_id=$_REQUEST['art_id'];
    }
    private function upload() {
        $maxFileAge = 60 * 60; // Temp file age in seconds
        
        $this->targetDir = 'uCat/art_avatars/'.site_id.'/tmp/'.$this->art_id;


// 5 minutes execution time
        @set_time_limit(5 * 60);


// Get parameters
        $this->chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
        $this->chunks = isset($_REQUEST["chunks"]) ? $_REQUEST["chunks"] : 0;
        $this->fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';

// Clean the fileName for security reasons
        $this->fileName =uString::text2filename(uString::rus2eng($this->fileName),true);

// Make sure the fileName is unique but only if chunking is disabled
        if ($this->chunk < 1 && file_exists($this->targetDir . "/" . $this->fileName)) {
            $ext = strrpos($this->fileName, '.');
            $fileName_a = substr($this->fileName, 0, $ext);
            $fileName_b = substr($this->fileName, $ext);

            $count = 1;
            while (file_exists($this->targetDir . "/" . $fileName_a . '_' . $count . $fileName_b)) $count++;
            
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

            $dir = 'uCat/art_avatars/'.site_id.'/'; //Адрес директории для сохранения картинки
            $source_filename=$this->targetDir.'/'.$this->fileName;

            //check if art belongs to this site id
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
                art_id
                FROM
                u235_articles
                WHERE
                site_id=:site_id AND
                art_id=:art_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':art_id', $this->art_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if(!$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(10);
            }
            catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}

            //Update art avatar timestamp
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uCat")->prepare("
                UPDATE 
                u235_articles
                SET
                art_avatar_time=:art_avatar_time
                WHERE
                art_id=:art_id AND
                site_id=:site_id
                ");
                $art_avatar_time=time();
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':art_id', $this->art_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':art_avatar_time', $art_avatar_time,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
            

            $this->uFunc->rmdir($dir.$this->art_id);
            if (!file_exists($dir.$this->art_id)) mkdir($dir.$this->art_id,0755,true);
            if(!uFunc::create_empty_index($dir.$this->art_id)) $this->uFunc->error(40);

            $img = new Imagick($source_filename);
            $img->setImageFormat('jpeg');
            $img->writeImage($dir.$this->art_id.'/orig.jpg');

            $img->clear();
            $img->destroy();

            //Delete temp directory
            uFunc::rmdir($this->targetDir);

            require_once 'inc/art_avatar.php';
            $avatar=new uCat_art_avatar($this->uCore);

            die ("{
            'status' : 'done',
            'art_id' : '".$this->art_id."',
            'art_avatar_addr':'".$avatar->get_avatar('art_page',$this->art_id)."'
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
new admin_arts_avatar_uploader_bg($this);