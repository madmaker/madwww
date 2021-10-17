<?php
require_once 'processors/uSes.php';
require_once 'processors/classes/uFunc.php';
require_once 'uPage/inc/common.php';

// HTTP headers for no cache etc
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
Header('Content-type: text/html; charset=utf-8');

//Check for required data
if(!isset($_REQUEST['page_id'])) die('{"status" : "error", "type" : "check", "message" : "Havent received all required data"}');

if(!uString::isDigits($_REQUEST['page_id'])) die('{"status" : "error", "type" : "check", "message" : "Error: page"}');

$uSes=new uSes($this);
$uFunc=new \processors\uFunc($this);
$uPage=new \uPage\common($this);

$page_id=$_REQUEST['page_id'];

$allow=false;

if(!$uSes->access(7)) die('{"status" : "error", "type" : "check", "message" : "forbidden 1"}');

$folder='page_avatars';

$targetDir = 'uEditor/'.$folder.'/'.site_id.'/tmp'.$uSes->get_val('ses_id').$page_id;

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
    $newFilename=$page_id.'.jpg';

    //check if page belongs to this site id
    if(!$query=$this->query("pages","SELECT
    `show_avatar`
    FROM
    `u235_pages_html`
    WHERE
    `site_id`='".site_id."' AND
    `page_id`='".$page_id."'
    ")) $this->error(2);
    if(!mysqli_num_rows($query)) $this->error(3);
    $qr=$query->fetch_object();
    $show_avatar=$qr->show_avatar;

    $dir = 'uEditor/'.$folder.'/'.site_id.'/'.$page_id; //Адрес директории для сохранения картинки
    uFunc::rmdir($dir);
    mkdir($dir,0755,true);
    if(!uFunc::create_empty_index($dir)) $this->uCore->error(1);
    $source_filename=$targetDir.'/'.$fileName;

    try {
        $im = new Imagick($source_filename);
    } catch (Exception $e) {
        die('{"status" : "error", "message" : "wrong_format"}');
    }

    //if(!$im->readimage($source_filename)) die('{"status" : "error", "type" : "wrong_format"}');
    $im->setImageFormat('jpeg');
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

    //Update cat avatar timestamp
    if(!$this->query("pages","UPDATE `u235_pages_html`
    SET
    `page_avatar_time`='".(time())."'
    WHERE
    `page_id`='".$page_id."' AND
    `site_id`='".site_id."'
    ")) $this->error(4);

    if($show_avatar&&$this->uFunc->getConf("show_avatars_on_pages","content")=='1') {
        include_once 'uEditor/inc/page_avatar.php';
        $page_avatar=new uEditor_page_avatar($this);
        $page_avatar_addr=$page_avatar->get_avatar(450,$page_id,time());
    }
    else $page_avatar_addr=false;

    //clear cache
    include_once "uEditor/inc/setup_article.php";
    $uEditor=new uEditor_setup_article($this,$page_id);
    $uEditor->clear_cache($page_id);

    //clear uPage cache with this art
    try {
        //get page_id

        /** @noinspection PhpUndefinedMethodInspection */
        $stm=$uFunc->pdo("uPage")->prepare("SELECT
            u235_pages.page_id
            FROM
            u235_pages
            JOIN
            u235_rows
            ON
            u235_rows.page_id=u235_pages.page_id AND 
            u235_rows.site_id=u235_pages.site_id
            JOIN
            u235_cols
            ON
            u235_cols.row_id=u235_rows.row_id AND 
            u235_cols.site_id=u235_rows.site_id
            JOIN
            u235_cols_els
            ON
            u235_cols_els.col_id=u235_cols.col_id AND 
            u235_cols_els.site_id=u235_cols.site_id
            WHERE 
            el_id=:el_id AND 
            el_type='art' AND
            u235_pages.site_id=:site_id");
        $site_id=site_id;
        /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':el_id', $page_id,PDO::PARAM_INT);
        /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
        /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

        /** @noinspection PhpUndefinedMethodInspection */
        while($page=$stm->fetch(PDO::FETCH_OBJ)) {//clear cache for selected pages
            $uPage->clear_cache($page->page_id);
        }
    }
    catch(PDOException $e) {$uFunc->error('390'.$e->getMessage());}

    die ("{
    'status' : 'done',
    'avatar_src':'".$page_avatar_addr."'
    }");
}
die("{'status' : 'continue', 'message' : 'Part ".++$chunk." of $chunks'}");
