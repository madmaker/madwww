<?php
namespace uCat\admin;
use Imagick;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class admin_items_image_uploader_bg {
    public $uFunc;
    public $uSes;
    public $item_id;
    public $chunk;
    public $chunks;
    public $fileName;
    public $targetDir;
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
        if(!isset($_REQUEST['item_id'])) die('{"status" : "error", "type" : "check", "message" : "Не переданы все обязательные данные"}');

        if(!uString::isDigits($_REQUEST['item_id'])) die('{"status" : "error", "type" : "check", "message" : "Ошибка передачи данных: item id"}');

        $this->item_id=$_REQUEST['item_id'];
        if(!uString::isDigits($this->item_id)) $this->uFunc->error(10);
        if($this->item_id=='0') $this->uFunc->error(20);
    }
    private function upload() {
        $this->targetDir = 'uCat/item_pictures/'.site_id.'/'.$this->item_id.'/tmp'.$this->item_id;
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
            //get new img_id
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
                img_id
                FROM
                u235_items_pictures
                WHERE
                item_id=:item_id AND
                site_id=:site_id
                ORDER BY
                img_id DESC
                LIMIT 1
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}

            /** @noinspection PhpUndefinedMethodInspection */
            /** @noinspection PhpUndefinedVariableInspection */
            if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) $img_id=1;
            else $img_id=$qr->img_id+1;
            
            //INSERT NEW img to item
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uCat")->prepare("INSERT INTO
                u235_items_pictures (
                img_id,
                item_id,
                site_id
                ) VALUES (
                :img_id,
                :item_id,
                :site_id
                )
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':img_id', $img_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
            
            $dir = 'uCat/item_pictures/'.site_id.'/'.$this->item_id.'/'.$img_id; //Адрес директории для сохранения картинки
            $this->uFunc->rmdir($dir);
            
            if (!file_exists($dir)) @mkdir($dir,0755,true);
            if(!uFunc::create_empty_index($dir)) $this->uFunc->error(50);
            $source_filename=$this->targetDir.'/'.$this->fileName;

            //update item pictures count
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
                COUNT(img_id)
                FROM
                u235_items_pictures
                WHERE
                item_id=:item_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}

            /** @noinspection PhpUndefinedMethodInspection */
            $qr=$stm->fetch(PDO::FETCH_ASSOC);
            $item_pictures=$qr['COUNT(`img_id`)'];

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
                u235_items
                SET
                item_pictures=:item_pictures
                WHERE
                item_id=:item_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_pictures', $item_pictures,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}
            

            $im = new Imagick($source_filename);
            $im->setImageFormat('jpeg');

            // Set to use jpeg compression
            $im->setImageCompression(Imagick::COMPRESSION_JPEG);
            // Set compression level (1 lowest quality, 100 highest quality)
            $im->setImageCompressionQuality(100);
            // Strip out unneeded meta data
            $im->stripImage();

            $im->writeImage($dir.'/orig.jpg');

            $im->clear();
            $im->destroy();

            //Delete temp directory
            uFunc::rmdir($this->targetDir);
            die ("{'status' : 'done', 'item_id' : '".$this->item_id."','item_pictures':'".$item_pictures."'}");
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
new admin_items_image_uploader_bg($this);