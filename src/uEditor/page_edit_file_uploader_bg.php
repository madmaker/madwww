<?php
// HTTP headers for no cache etc
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
Header('Content-type: text/html; charset=utf-8');

$uSes=new uSes($this);

//Check for required data
if(isset($_REQUEST['handlerId'])) $_REQUEST['page_id']=$_REQUEST['handlerId'];//for uPage. Because there are handler_id instead of page_id;

if(!isset($_REQUEST['page_id'])) die('{"status" : "error", "type" : "check", "message" : "Havent received all required data"}');

if(!uString::isDigits($_REQUEST['page_id'])) die('{"status" : "error", "type" : "check", "message" : "Error: page"}');

$page_id=$_REQUEST['page_id'];
if(!uString::isDigits($page_id)) $this->error(1);


$allow=false;

if(!$this->access(7)) die('{"status" : "error", "type" : "forbidden", "message" : "forbidden"}');

$folder='files';

$targetDir = 'uEditor/'.$folder.'/tmp/'.$this->uSes->get_val('sesId');;

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
$fileName =uString::text2filename(uString::rus2eng($fileName),true);
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
if (!file_exists($targetDir)) {
    if(!mkdir($targetDir,0755,true)) {
        die($targetDir);
    }
}

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
    if(!$query=$this->query("pages","SELECT `file_id` FROM `u235_pages_files` WHERE `site_id`='".site_id."' ORDER BY `file_id` DESC LIMIT 1")) die('{"status" : "error", "type" : "db", "message": "Select max(file_id) error"}');
    $fileId=$query->fetch_object();
    if(mysqli_num_rows($query)>0) $fileId=$fileId->file_id+1;
    else $fileId=1;

    //Check file name to not be used in database
    $again=true;
    $newFilename1=$newFilename=$fileName;
    for($i=1;$again;$i++) {
        if(!$query=$this->query("pages","SELECT `file_name` FROM `u235_pages_files` WHERE `file_name`='".$newFilename."' AND `site_id`='".site_id."'")) $this->error(2);
        if(mysqli_num_rows($query)>0) {
            $newFilename=$i.$newFilename1;
        }
        else $again=false;
    }

    $dir = 'uEditor/files'; //Адрес директории для сохранения картинки
    if(!is_dir($dir.'/'.site_id)) mkdir($dir.'/'.site_id);
    if(!is_dir($dir.'/'.site_id.'/'.$page_id)) mkdir($dir.'/'.site_id.'/'.$page_id);
    if(!uFunc::create_empty_index($dir.'/'.site_id.'/'.$page_id)) $this->uCore->error(3);
    $source_filename=$dir.'/tmp/'.$this->uSes->get_val('sesId').'/'.$fileName;

    $filesize=filesize($source_filename);
    //Write out information about file to db
    if(!$this->query("pages","INSERT INTO `u235_pages_files` (
		`file_id`,
		`file_name`,
		`page_id`,
		`file_size`,
		`timestamp`,
		`site_id`
		) VALUES (
		'".$fileId."',
		'".$newFilename."',
		'".$page_id."',
		'".$filesize."',
		'".time()."',
		'".site_id."'
		)")) die('{"status" : "error", "type" : "db", "message": "Insert values error: "}');

    //copy file
    copy ($source_filename,$dir.'/'.site_id.'/'.$page_id.'/'.$newFilename);
    //Delete temp directory
    uFunc::rmdir($targetDir);
    die ("{'status' : 'done', 'fileid' : '".$fileId."', 'filename' : '".$newFilename."', 'filesize' : '".$filesize."'}");
}
die("{'status' : 'continue', 'message' : 'Part ".++$chunk." of $chunks'}");
