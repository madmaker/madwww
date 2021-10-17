<?php
namespace gallery;
use Imagick;
use PDO;
use PDOException;
use processors\uFunc;
use uPage\admin\gallery;
use uSes;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class images_uploader_bg {
    public $uFunc;
    public $uSes;
    public $gallery_id;
    public $chunk;
    public $chunks;
    public $fileName;
    public $targetDir;
    private $orig_filename;
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
        if(!isset($_REQUEST['gallery_id'])) {
            echo json_encode(array(
                "status" => "error",
                "type" => "check",
                "message" => "Required data is not received"
            ));
            exit;
        }

        if(!uString::isDigits($_REQUEST['gallery_id'])) {
            echo json_encode(array(
                "status" => "error",
                "type" => "check",
                "message" => "Required data is not received 1"
            ));
            exit;
        }

        $this->gallery_id=$_REQUEST['gallery_id'];
        if(!uString::isDigits($this->gallery_id)) $this->uFunc->error(10,1);
        if($this->gallery_id=='0') $this->uFunc->error(20,1);
    }
    private function upload() {
        $this->targetDir = 'gallery/img/site_images/'.site_id.'/'.$this->gallery_id.'/tmp'.$this->gallery_id;
        $maxFileAge = 60 * 60; // Temp file age in seconds

        // 5 minutes execution time
        @set_time_limit(5 * 60);

        // Get parameters
        $this->chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
        $this->chunks = isset($_REQUEST["chunks"]) ? $_REQUEST["chunks"] : 0;
        $this->fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';
        $this->orig_filename=$this->fileName;

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
                        "status" => "error",
                        "type" => "uploader",
                        "message" => "Failed to open output stream."
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
            $out = fopen($this->targetDir . "/" . $this->fileName, $this->chunk == 0 ? "wb" : "ab");
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
            else {
                echo json_encode(array(
                    "status" => "error",
                    "type" => "uploader",
                    "message" => "Failed to open output stream"
                ));
                exit;
            }
        }
    }
    private function save() {
        if (($this->chunk+1 == $this->chunks)||$this->chunks==0) {
            if(!isset($this->gallery)) {
                require_once "gallery/classes/common.php";
                $this->gallery=new common($this->uCore);
            }
            $img_id=$this->gallery->get_new_img_id();

            //INSERT NEW img to gallery
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("gallery")->prepare("INSERT INTO
                images (
                img_id,
                gallery_id,
                site_id
                ) VALUES (
                :img_id,
                :gallery_id,
                :site_id
                )
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':img_id', $img_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':gallery_id', $this->gallery_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/,1);}
            
            $dir = 'gallery/gallery_pictures/'.site_id.'/'.$this->gallery_id.'/'.$img_id; //Адрес директории для сохранения картинки
            $this->uFunc->rmdir($dir);

            if(!isset($this->gallery)) {
                require_once "gallery/classes/common.php";
                $this->gallery=new common($this->uCore);
                $this->gallery->clear_cache($this->gallery_id);
            }
            if(!isset($this->uPage)) {
                require_once "uPage/inc/common.php";
                $this->uPage=new \uPage\common($this->uCore);

                $stm_pages=$this->uPage->el_id2page_ids($this->gallery_id);

                /** @noinspection PhpUndefinedMethodInspection */
                while($page=$stm_pages->fetch(PDO::FETCH_OBJ)) {
                    $this->uPage->clear_cache($page->page_id);
                }
            }
            
            if (!file_exists($dir)) @mkdir($dir,0755,true);
            if(!uFunc::create_empty_index($dir)) $this->uFunc->error(50,1);
            $source_filename=$this->targetDir.'/'.$this->fileName;

            try {
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
            } catch (\ImagickException $e) {}

            //Delete temp directory
            uFunc::rmdir($this->targetDir);
            echo json_encode(array(
                'status' => 'done',
                'gallery_id' => $this->gallery_id/*,
                'gallery_pictures'=>$gallery_pictures*/
            ));
            exit;
        }
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(7)) {
            echo json_encode(array("status"=>"forbidden"));
            exit;
        }
        $this->uFunc=new uFunc($this->uCore);

        $this->headers();
        $this->check_data();
        $this->upload();
        $this->save();

        echo json_encode(array(
            "status" =>"continue",
            "message"=>("Часть ".++$this->chunk." из ".$this->chunks)
        ));
        exit;
    }
}
new images_uploader_bg($this);