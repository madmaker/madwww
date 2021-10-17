<?php
ini_set("memory_limit","256M");
set_time_limit(300);
use processors\uFunc;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uCat/classes/import.php";

class import_file_uploader_bg {
    public $uFunc;
    public $uSes;
    public $targetDir;
    public $chunk;
    public $chunks;
    public $fileName;
    public $import_export;
    private $uCore;
    private function headers() {
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        Header('Content-type: text/html; charset=utf-8');
    }

    private function upload() {
        $this->targetDir = 'uCat/import_upload/'.site_id.'/tmp/';
        $maxFileAge = 60 * 60; // Temp file age in seconds

// 5 minutes execution time
        @set_time_limit(5 * 60);

// Get parameters
        $this->chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
        $this->chunks = isset($_REQUEST["chunks"]) ? $_REQUEST["chunks"] : 0;
        $this->fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';

// Clean the fileName for security reasons
        $this->fileName = uString::sanitize_filename($this->fileName);

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
            $dir = 'uCat/import_upload/'.site_id.'/'; //Адрес директории для сохранения картинки
            $source_filename=$this->targetDir.'/'.$this->fileName;
            copy($source_filename, $dir.$this->fileName);
            $_SESSION['filepath'] = $dir.$this->fileName;
            $expansion = explode(".", $this->fileName);

            $xlsarr = $this->import->getXLS($source_filename);
            $xlsarr[0]=array_slice($xlsarr[0],0,50);

            //Delete temp directory
            uFunc::rmdir($this->targetDir);

            //SELECTBOXES options for columns
            $selectbox_options_ar=array(
                "itemid"=>"Товар - ID",
                "itemname"=>"Товар - Наименование",
                "item_img_url"=>"Товар - URL изображения",
                "itemdescr"=>"Товар - Описание",
                "price"=>"Товар - Цена",
                "quantity"=>"Товар - Остаток",
                "unitid"=>"Товар - ID Единицы измерения",
                "unit"=>"Товар - Наименование единицы измерения",
                "article"=>"Товар - Артикул",
                "catid"=>"Категория - ID",
                "catname"=>"Категория - Наименование",
                "sectid"=>"Раздел - ID",
                "sectname"=>"Раздел - Наименование"
            );


            $result = array(
                'status' => 'done',
                'data' => $xlsarr,
                'selectbox_options_ar'=>$selectbox_options_ar,
                'exp' => end($expansion)
            );

            die (json_encode($result, JSON_UNESCAPED_UNICODE));
        }
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        $this->import= new import_class($this->uCore);
        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");

        $this->headers();
        $this->upload();
        $this->save();
        die("{'status' : 'continue', 'message' : 'Часть ".++$this->chunk." из $this->chunks'}");
    }
}
new import_file_uploader_bg($this);
