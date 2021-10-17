<?php
// HTTP headers for no cache etc
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
Header('Content-type: text/html; charset=utf-8');

//Check for required data
if(!isset($_REQUEST['hashId'],$_REQUEST['hash'])) die('{"status" : "error", "type" : "check", "message" : "Не переданы все обязательные данные"}');

if(!uString::isDigits($_REQUEST['hashId'])) die('{"status" : "error", "type" : "check", "message" : "Ошибка передачи данных: hash"}');

if(!$this->uFunc->sesHack_test($_REQUEST['hashId'],$_REQUEST['hash'])) die('forbidden');
//$this->uFunc->sesHack_test($_REQUEST['hashId'],$_REQUEST['hash']);

$allow=false;

if(!$this->access(4)) die('forbidden');

$folder='descr_files';
$module=$this->mod;

$targetDir = $module.'/'.$folder.'/'.site_id.'/tmp'.$_REQUEST['hashId'];

$cleanupTargetDir = false; // Remove old files
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
$fileName=uString::text2filename(uString::rus2eng($fileName),true);
//$fileName=uString::rus2eng($fileName);
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
    //Take free id for adding file to database
    if(!$query=$this->query("uViblog","SELECT
    `file_id`
    FROM
    `u235_descr_files`
    ORDER BY
    `file_id` DESC
    LIMIT 1
    ")) die('{"status" : "error", "type" : "db", "message": "Select max(file_id) error"}');
    $fileId=$query->fetch_object();
    if(mysqli_num_rows($query)>0) $fileId=$fileId->file_id+1;
    else $fileId=1;

    //Check file name to not be used in database
    $again=true;
    $newFilename1=$newFilename=$fileName;
    for($i=1;$again;$i++) {
        if(!$query=$this->query("uViblog","SELECT
        `file_name`
        FROM
        `u235_descr_files`
        WHERE
        `file_name`='".$newFilename."' AND
        `site_id`='".site_id."'
        ")) $this->error(1);
        if(mysqli_num_rows($query)>0) {
            $newFilename=$i.$newFilename1;
        }
        else $again=false;
    }

    $dir = $_SERVER['DOCUMENT_ROOT'].'/'.$module.'/'.$folder.'/'.site_id.'/'; //Адрес директории для сохранения картинки
    if(!uFunc::create_empty_index($module.'/'.$folder.'/'.site_id)) $this->uCore->error(2);
    $source_filename=$targetDir.'/'.$fileName;

    $filesize=filesize($source_filename);
    //Write out information about file to db
    if(!$this->query("uViblog","INSERT INTO
    `u235_descr_files` (
		`file_id`,
		`file_name`,
		`site_id`
		) VALUES (
		'".$fileId."',
		'".$newFilename."',
		'".site_id."'
		)")) die('{"status" : "error", "type" : "db", "message": "Insert values error: "}');

    //copy file
    copy ($source_filename,$dir.$newFilename);
    //Delete temp directory
    @uFunc::rmdir($targetDir);
    die ("{'status' : 'done', 'fileid' : '".$fileId."', 'filename' : '".$newFilename."', 'filesize' : '".$filesize."'}");
}
die("{'status' : 'continue', 'message' : 'Часть ".++$chunk." из $chunks'}");
