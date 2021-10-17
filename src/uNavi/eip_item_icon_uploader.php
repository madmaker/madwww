<?php
require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class uNavi_eip_item_icon_uploader {
    private $uCore,
        $item_id,$icon_type,$filename,$cat_id,
        $folder,$targetDir,$source_filename;
    private function finish($status,$type='',$msg='') {
        die('{"status" : "'.$status.'", "type" : "'.$type.'", "message" : "'.$msg.'""}');
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
        if(!isset($_REQUEST['item_id'])) $this->finish("error", "check", "Haven't got all data required item_id");
        if(!isset($_REQUEST['icon_type'])) $this->finish("error", "check", "Haven't got all data required icon_type");
        $this->icon_type=$_REQUEST['icon_type'];
        if($this->icon_type!='hover') $this->icon_type='regular';

        $this->item_id=$_REQUEST['item_id'];
        if(!uString::isDigits($this->item_id)) $this->finish("error", "check","Handled id error");

        $this->folder='item_icons/'.site_id.'/'.$this->item_id.'/'.$this->icon_type.'/';

        $this->targetDir = $this->uCore->mod.'/'.$this->folder.'tmp'.$this->item_id;
    }
    private function uploader() {
        $maxFileAge = 60 * 60; // Temp file age in seconds

        // 5 minutes execution time
        @set_time_limit(5 * 60);

        // Get parameters
        $chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
        $chunks = isset($_REQUEST["chunks"]) ? $_REQUEST["chunks"] : 0;
        $filename = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';

        // Clean the fileName for security reasons
        $this->source_filename=$filename;

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
        if (strpos($contentType, "multipart") !== false) {
            if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
                // Open temp file
                $out = fopen($this->targetDir . '/' . $filename, $chunk == 0 ? "wb" : "ab");
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
            $out = fopen($this->targetDir . '/' . $filename, $chunk == 0 ? "wb" : "ab");
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
        $dir = $this->uCore->mod.'/'.$this->folder.'/'; //Адрес директории для сохранения файла
        // Create dir
        if (!file_exists($dir)) mkdir($dir,0755,true);
        if(!uFunc::create_empty_index($dir)) $this->uCore->error(10);


        $dot = strrpos($this->source_filename, '.');
        $this->filename=$file_ext = substr($this->source_filename, $dot+1);

        $mime_type=uFunc::ext2mime(strtolower($file_ext));
        if(!$mime_type) $mime_type='application/octet-stream';

        if(!strpos('_'.$mime_type,'image')) {
            $this->uFunc->rmdir($this->targetDir);
            die('{
            "status":"error",
            "message":"wrong file format"
            }');
        }
        else {
            //Check if this item_id exists
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uNavi")->prepare("SELECT
                cat_id
                FROM
                u235_menu
                WHERE
                id=:item_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) {
                    $this->uFunc->rmdir($this->targetDir);
                    $this->uFunc->error(20);
                }
                $this->cat_id=$qr->cat_id;
            }
            catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}

            copy($this->targetDir.'/'.$this->source_filename,$dir.$this->item_id.'.'.$file_ext);
            $this->uFunc->rmdir($this->targetDir);

            //Save file to db
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uNavi")->prepare("UPDATE
                u235_menu
                SET
                icon_".$this->icon_type."_filename=:filename,
                timestamp=:timestamp
                WHERE
                id=:item_id AND
                site_id=:site_id
                ");
                $timestamp=time();
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':timestamp', $timestamp,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':filename', $file_ext,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
        }
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        $this->uMenu=new uMenu($this->uCore);

        $this->sendHeaders();
        $this->checkData();

        if(!$this->uSes->access(7)) die('{"status":"forbidden"}');

        $this->uploader();

        echo '{
        "status" : "done",
        "icon_type" : "'.$this->icon_type.'",
        "item_id" : "'.$this->item_id.'",
        "filename" : "'.$this->filename.'",
        "timestamp":"'.time().'",
        "cat_id2update":"'.$this->cat_id.'",
        "cat_new_html":"'.rawurlencode($this->uCore->uMenu->return_cat_id_content($this->cat_id)).'"
        }';

        $this->uMenu->clean_cache($this->cat_id);
    }
}
$newClass=new uNavi_eip_item_icon_uploader  ($this);