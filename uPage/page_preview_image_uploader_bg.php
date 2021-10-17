<?php
namespace uPage\admin;
use Imagick;
use PDO;
use PDOException;
use processors\uFunc;
use uPage\common;
use uSes;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class page_preview_image_uploader_bg {
    private $uPage;
    private $page_id;
    private $uFunc;
    private $uSes;
    private $uCore;
    private function check_data() {
        // HTTP headers for no cache etc
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        Header('Content-type: text/html; charset=utf-8');

        //Check for required data
        if(!isset($_REQUEST['page_id'])) {
            echo json_encode(array(
                "status" => "error",
                "type" => "check",
                "message" => "Haven't received all required data"
            ));
            exit;
        }

        if(!uString::isDigits($_REQUEST['page_id'])) {
            echo json_encode(array(
                "status" => "error",
                "type" => "check",
                "message" => "Error: page"
            ));
            exit;
        }

        $this->page_id=$_REQUEST['page_id'];
    }

    private function upload() {
        $targetDir = 'uPage/preview_images/'.site_id.'/tmp'.$this->uSes->get_val('ses_id').$this->page_id;

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
        $fileName = preg_replace('/[^\w\._]+/', '', $fileName);

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
        else {
            echo json_encode(array(
                "status" => "error", 
                "type" => "uploader", 
                "message" => "Failed to open temp directory."
            ));
            exit;
        }

        // Look for the content type header
        if (isset($_SERVER["HTTP_CONTENT_TYPE"])) $contentType = $_SERVER["HTTP_CONTENT_TYPE"];
        else if (isset($_SERVER["CONTENT_TYPE"])) $contentType = $_SERVER["CONTENT_TYPE"];
        else $contentType="";

        // Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
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
                    else {
                        echo json_encode(array(
                            "status" => "error", 
                            "type" => "uploader", 
                            "message" => "Failed to open input stream."
                        ));
                        exit;
                    }

                    fclose($in);
                    fclose($out);
                    @unlink($_FILES['file']['tmp_name']);
                }
                else {
                    echo json_encode(array(
                        "status" => "error", "type" => "uploader", "message" => "Failed to open output stream."
                    ));
                    exit;
                }
            }
            else {
                echo json_encode(array(
                    "status" => "error", 
                    "type" => "uploader", 
                    "message" => "Failed to move uploaded file."
                ));
                exit;
            }
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
                else {
                    echo json_encode(array(
                        "status" => "error", 
                        "type" => "uploader", 
                        "message" => "Failed to open input stream."
                    ));
                    exit;
                }
                fclose($in);
                fclose($out);
            }
            else	die('{"status" : "error", "type" : "uploader", "message" : "Failed to open output stream"}');
        }

        if (($chunk+1 == $chunks)||$chunks==0) {
            //check if page belongs to this site id
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
                page_id 
                FROM 
                u235_pages
                WHERE 
                page_id=:page_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if(!$stm->fetch(\PDO::FETCH_OBJ)) {
                    echo json_encode(array(
                        "status"=>"error",
                        "type"=>"db",
                        "message"=>"page does not belongs to current site"
                    ));
                    exit;
                }
            }
            catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}



            $dir = 'uPage/preview_images/'.site_id.'/'.$this->page_id; //Адрес директории для сохранения картинки
            $this->uFunc->rmdir($dir);
            mkdir($dir,0755,true);
            if(!uFunc::create_empty_index($dir)) $this->uCore->error(1);
            $source_filename=$targetDir.'/'.$fileName;

            try {
                $im = new Imagick($source_filename);
            }
            catch (\ImagickException $e) {
                echo json_encode(array(
                    "status" => "error",
                    "message" => "wrong_format"
                ));
                exit;
            }

            $im->setImageFormat('jpeg');
            $im->setImageAlphaChannel(11); // Imagick::ALPHACHANNEL_REMOVE
            $im->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
            // Set to use jpeg compression
            $im->setImageCompression(Imagick::COMPRESSION_JPEG);
            // Set compression level (1 lowest quality, 100 highest quality)
            $im->setImageCompressionQuality(75);
            // Strip out unneeded meta data
            $im->stripImage();
            $im->writeImage($dir.'/orig.jpg');

            $im->clear();
            $im->destroy();

            //Delete temp directory
            uFunc::rmdir($targetDir);

            $preview_img_timestamp=time();

            //Update cat avatar timestamp
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE 
                u235_pages
                SET
                preview_img_timestamp=:preview_img_timestamp
                WHERE
                page_id=:page_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':preview_img_timestamp', $preview_img_timestamp,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}

            //clear cache
            require_once "uPage/inc/common.php";
            $this->uPage=new common($this->uCore);
            $this->uPage->clear_cache($this->page_id);

            require_once "uPage/inc/page_preview_img.php";
            $page_preview_img=new \page_preview_img($this->uCore);


            echo json_encode(array(
            "status" => "done",
            "page_preview_img" => $page_preview_img->get_img_url(500,$this->page_id,$preview_img_timestamp)
            ));
            exit;
        }

        echo json_encode(array(
            'status' => 'continue',
            'message' => "Part ".++$chunk." of $chunks"
        ));
        exit;
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(7)) {
            echo json_encode(array(
                "status" => "error",
                "type" => "check",
                "message" => "forbidden"
            ));
            exit;
        }
        $this->uFunc=new uFunc($this->uCore);

        $this->check_data();

        $this->upload();
    }
}
new page_preview_image_uploader_bg($this);