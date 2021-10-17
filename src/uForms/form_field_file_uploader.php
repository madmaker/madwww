<?php
namespace uForms\form;

use PDO;
use PDOException;
use processors\uFunc;
use uForms;
use uSes;
use uString;

require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';
require_once 'uForms/inc/common.php';

class field_file_uploader {
    private $uCore;
    private $form_id;
    private $field_id;
    private $rec_id;

    private function check_data() {
        //Check for required data
        if(!isset($_REQUEST['form_id'],$_REQUEST['field_id'],$_REQUEST['rec_id'])) die('{"status" : "error", "type" : "check", "message" : "Havent got all required data"}');

        $this->form_id=$_REQUEST['form_id'];
        $this->field_id=$_REQUEST['field_id'];
        $this->rec_id=$_REQUEST['rec_id'];
        if(!uString::isDigits($this->form_id)) die('{"status" : "error", "type" : "check", "message" : "form"}');
        if(!uString::isDigits($this->field_id)) die('{"status" : "error", "type" : "check", "message" : "field"}');
        if($this->rec_id!="new") {
            if(!uString::isDigits($this->rec_id)) die('{"status" : "error", "type" : "check", "message" : "field"}');

            //check if this rec_id exists
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uForms")->prepare("SELECT
                rec_id
                FROM
                u235_records
                WHERE
                rec_id=:rec_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':rec_id', $this->rec_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if(!$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(10);
            }
            catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
        }
    }
    private function upload_file() {
        $targetDir = 'uForms/field_files/'.site_id.'/'.$this->form_id.'/tmp'.$this->uSes->get_val('sesId');
        $maxFileAge = 60 * 60; // Temp file age in seconds

        // 5 minutes execution time
        @set_time_limit(5 * 60);

        // Get parameters
        $chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
        $chunks = isset($_REQUEST["chunks"]) ? $_REQUEST["chunks"] : 0;
        $fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';

        $dot = strrpos($fileName, '.');
        $ext = substr($fileName, $dot);

        $fileName=$this->field_id.$ext;

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
        if (!file_exists($targetDir)) mkdir($targetDir,0755,true);

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


        if (($chunk+1 == $chunks)||$chunks==0) $this->save_file($fileName,$ext,$targetDir);
        die("{'status' : 'continue', 'message' : 'Part ".++$chunk." of $chunks'}");
    }
    private function save_file($fileName,$ext,$targetDir) {
        //check if form exists
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT 
            form_id
            FROM u235_forms
            WHERE 
            form_id=:form_id AND 
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':form_id', $this->form_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(30);
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}


        //check if field_id exists
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT 
            field_id
            FROM 
            u235_fields
            WHERE 
            field_id=:field_id AND 
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_id', $this->field_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(50);
        }
        catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}


        if($this->rec_id=="new") {
            $this->rec_id=$this->uForms->get_new_rec_id();

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uForms")->prepare("INSERT INTO
                u235_records (
                rec_id,
                form_id,
                rec_status,
                rec_timestamp,
                site_id
                ) VALUES (
                :rec_id,
                :form_id,
                'new',
                ".time().",
                :site_id
                )
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':rec_id', $this->rec_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':form_id', $this->form_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('65'/*.$e->getMessage()*/);}
        }
        else {//Check if this rec_id is still new
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm = $this->uFunc->pdo("uForms")->prepare("SELECT
                rec_status
                FROM
                u235_records
                WHERE
                rec_id=:rec_id AND
                site_id=:site_id
                ");
                $site_id = site_id;
                /** @noinspection PhpUndefinedMethodInspection */
                $stm->bindParam(':rec_id', $this->rec_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */
                $stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                $qr = $stm->fetch(PDO::FETCH_OBJ);
                if ($qr) {
                    if ($qr->rec_status != 'new') $this->uFunc->error(70);
                } else if ($qr->rec_status != 'new') $this->uFunc->error(80);
            } catch (PDOException $e) {
                $this->uFunc->error('90'/*.$e->getMessage()*/);
            }
        }

        //Check if file for this field has been uploaded before
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT
            rec_id
            FROM
            u235_form_results
            WHERE
            rec_id=:rec_id AND
            field_id=:field_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':rec_id', $this->rec_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_id', $this->field_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($stm->fetch(PDO::FETCH_OBJ)) {//update field value
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("uForms")->prepare("UPDATE
                    u235_form_results
                    SET
                    field_value=:field_value
                    WHERE
                    rec_id=:rec_id AND
                    field_id=:field_id AND
                    site_id=:site_id
                    ");
                    $site_id=site_id;
                    $field_value=uString::text2sql($fileName);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':rec_id', $this->rec_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_id', $this->field_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_value', $field_value,PDO::PARAM_STR);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('100'/*.$e->getMessage()*/);}
            }
            else {//insert field_value
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("uForms")->prepare("INSERT INTO
                    u235_form_results (
                    field_value,
                    rec_id,
                    field_id,
                    site_id
                    ) VALUES (
                    :field_value,
                    :rec_id,
                    :field_id,
                    :site_id
                    )
                    ");
                    $site_id=site_id;
                    $field_value=uString::text2sql($fileName);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':rec_id', $this->rec_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_id', $this->field_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_value', $field_value,PDO::PARAM_STR);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('110'/*.$e->getMessage()*/);}
            }
        }
        catch(PDOException $e) {$this->uFunc->error('120'/*.$e->getMessage()*/);}

        //copy file
        $folder='uForms/field_files/'.site_id.'/'.$this->form_id.'/'.$this->rec_id;
        if (!file_exists($folder)) mkdir($folder,0755,true);

        if(!$this->uFunc->create_empty_index($folder)) $this->uFunc->error(130);
        copy ($targetDir.DIRECTORY_SEPARATOR.$fileName,$_SERVER['DOCUMENT_ROOT'].'/'.$folder.DIRECTORY_SEPARATOR.$fileName);
        //Delete temp directory
        $this->uFunc->rmdir($targetDir);
        die ("{
        'status' : 'done', 
        'ext' : '".$ext."', 
        'form_id' : '".$this->form_id."',
        'field_id' : '".$this->field_id."',
        'rec_id' : '".$this->rec_id."',
        'orig_filename':'".rawurlencode($_REQUEST["name"])."'
        }");
    }
    private function headers() {
        // HTTP headers for no cache etc
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uForms=new uForms($this->uCore);
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);

        $this->headers();
        $this->check_data();
        $this->upload_file();
    }
}
/*$newClass=*/new field_file_uploader($this);