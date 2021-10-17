<?php
namespace uCat\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uPage\common;
use uSes;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uPage/inc/common.php";
require_once "uCat/classes/common.php";
//require_once 'inc/item_avatar.php';

class admin_items_avatar_uploader_bg {
    public $uFunc;
    public $uSes;
    public $uPage;
    private $uCat;
    private $uCore;
    private function update_img_time_for_item($item_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm = $this->uFunc->pdo("uCat")->prepare("UPDATE 
                    u235_items
                    SET
                    item_img_time=:item_img_time
                    WHERE
                    item_id=:item_id AND
                    site_id=:site_id
                    ");
            $site_id = site_id;
            $item_img_time = time();
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_img_time', $item_img_time, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $item_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        } catch (PDOException $e) {$this->uFunc->error('35'/*.$e->getMessage()*/);}
    }
    private function update_img_time_for_var($var_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm = $this->uFunc->pdo("uCat")->prepare("UPDATE 
                    items_variants
                    SET
                    img_time=:img_time
                    WHERE
                    var_id=:var_id AND
                    site_id=:site_id
                    ");
            $site_id = site_id;
            $img_time = time();
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':img_time', $img_time , PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':var_id', $var_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        } catch (PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }
    private function do_job() {
// HTTP headers for no cache etc
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        Header('Content-type: text/html; charset=utf-8');

//Check for required data
        if(!isset($_REQUEST['item_id'],$_REQUEST['var_id'])) die('{"status" : "error", "type" : "check", "message" : "Не переданы все обязательные данные"}');

        if(!uString::isDigits($_REQUEST['item_id'])) die('{"status" : "error", "type" : "check", "message" : "Ошибка передачи данных: item id"}');
        if(!uString::isDigits($_REQUEST['var_id'])) die('{"status" : "error", "type" : "check", "message" : "Ошибка передачи данных: item id"}');

        $item_id=(int)$_REQUEST['item_id'];
        $var_id=(int)$_REQUEST['var_id'];
        if(!$this->uSes->access(25)) die('forbidden');

        $targetDir = 'uCat/item_avatars/'.site_id.'/tmp/'.$item_id.'-'.$var_id;

        $maxFileAge = 60 * 60; // Temp file age in seconds

// 5 minutes execution time
        @set_time_limit(5 * 60);

// Uncomment this one to fake upload time
// usleep(5000);

// Get parameters
        $chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
        $chunks = isset($_REQUEST["chunks"]) ? $_REQUEST["chunks"] : 0;
        $fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';

// Clean the fileName for security reasons
        $fileName =uString::text2filename(uString::rus2eng($fileName),true);
//$fileName = preg_replace('/[^\w\._]+/', '', $fileName);

// Make sure the fileName is unique but only if chunking is disabled
        if ($chunk < 1 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
            $ext = strrpos($fileName, '.');
            $fileName_a = substr($fileName, 0, $ext);
            $fileName_b = substr($fileName, $ext);

            $count = 1;
            while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
                $count++;
            $fileName = $fileName_a . '_' . $count . $fileName_b;
        }

// Create target dir
        if (!file_exists($targetDir)) @mkdir($targetDir,0755,true);

// Remove old temp files
        if (is_dir($targetDir) && ($dir = opendir($targetDir))) {
            while (($file = readdir($dir)) !== false) {
                $filePath = $targetDir . DIRECTORY_SEPARATOR . $file;
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
                $out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
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
            $out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
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

            $dir='uCat/item_avatars/'.site_id.'/'; //Адрес директории для сохранения картинки
            $source_filename=$targetDir.'/'.$fileName;

            //check if item belongs to this site id
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
                item_id
                FROM
                u235_items
                WHERE
                site_id=:site_id AND
                item_id=:item_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if(!$stm->fetch(\PDO::FETCH_OBJ)) $this->uFunc->error(10);
            }
            catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}


            if($var_id) {//Update variant avatar timestamp
                $this->update_img_time_for_var($var_id);
                $this->uCat->save_var_avatar($dir,$source_filename,$item_id,$var_id);

                //check if this is default variant for current item
                if($this->uCat->is_default_item_variant($item_id,$var_id)) {
                    $this->update_img_time_for_item($item_id);
                    $this->uCat->save_item_avatar($dir,$source_filename,$item_id);
                }
            }
            else {//Update item avatar timestamp
                $this->update_img_time_for_item($item_id);
                $this->uCat->save_item_avatar($dir,$source_filename,$item_id);

                if($this->uCat->has_variants($item_id)) {
                    //update default var avatar
                    $var_id=$this->uCat->item_id2default_variant_id($item_id);
                    $this->uCat->save_var_avatar($dir,$source_filename,$item_id,$var_id);
                }
            }

            //Delete temp directory
            uFunc::rmdir($targetDir);

//            $avatar=new \item_avatar($this->uCore);

            //Clean uPage cache
            $this->uPage->clear_cache4uCat_latest();

            die ("{
            'status' : 'done',
            'item_id' : '".$item_id."',
            'var_id' : '".$var_id."'
            }");
        }
        die("{'status' : 'continue', 'message' : 'Часть ".++$chunk." из $chunks'}");
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        $this->uPage=new common($this->uCore);
        $this->uCat=new \uCat\common($this->uCore);

        $this->do_job();

    }
}
new admin_items_avatar_uploader_bg($this);
