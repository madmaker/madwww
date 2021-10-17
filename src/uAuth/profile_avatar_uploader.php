<?php

namespace uAuth;

use Imagick;
use ImagickException;
use PDO;
use PDOException;
use processors\uFunc;
use uAuth_avatar;
use uSes;
use uString;

require_once 'processors/uSes.php';
require_once 'inc/avatar.php';

class profile_avatar_uploader {
    /**
     * @var uSes
     */
    private $uSes;

    private function check_data() {
        if(!isset($_REQUEST['userId'])) {
            print json_encode([
                'status'=>'error',
                'type'=>'check',
                'message'=>'wrong request',
                'post'=>$_REQUEST
            ]);
            exit;
        }

        if(!uString::isDigits($_REQUEST['userId'])) {
            print json_encode([
                'status'=>'error',
                'type'=>'check',
                'message'=>'wrong user_id'
            ]);
            exit;
        }

        $userId=(int)$_REQUEST['userId'];

        $currentUserId=(int)$this->uSes->get_val('user_id');

        $isProfileOwner=$currentUserId===$userId;
        $isRoot=$this->uSes->access(29);
//        $isRoot=0;

        if(!$isProfileOwner&&!$isRoot) {
            print json_encode([
                'status'=>'error',
                'type'=>'check',
                'message'=>'forbidden'
            ]);
            exit;
        }

        return $userId;
    }

    public function __construct(&$uCore) {
        $this->uSes = new uSes($uCore);
        $uFunc=new uFunc($uCore);

        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');

        $userId=$this->check_data();

        $targetDir = 'uAuth/avatars/tmp'.$userId;

        //Setting script max execution time
        @set_time_limit(300);

        // Get parameters
        $chunk = isset($_REQUEST['chunk']) ? $_REQUEST['chunk'] : 0;
        $chunks = isset($_REQUEST['chunks']) ? $_REQUEST['chunks'] : 0;
        $fileName = isset($_REQUEST['name']) ? $_REQUEST['name'] : '';

        // Clean the fileName for security reasons
        $fileName =preg_replace('/[^\w\._]+/', '', $fileName);

        // Make sure the fileName is unique but only if chunking is disabled
        if ($chunk < 1 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
            $ext = strrpos($fileName, '.');
            $fileName_a = substr($fileName, 0, $ext);
            $fileName_b = substr($fileName, $ext);

            $count = 1;
            while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b)) {
                $count++;
            }
            $fileName = $fileName_a . '_' . $count . $fileName_b;
        }

        // Create target dir
        if (!file_exists($targetDir) && !mkdir($targetDir) && !is_dir($targetDir)) {
            print json_encode([
                'status' => 'error',
                'type' => 'uploader',
                'message' => 'Directory was not created'
            ]);
            exit;
        }

        // Remove old temp files
        if (is_dir($targetDir) && ($dir = opendir($targetDir))) {
            while (($file = readdir($dir)) !== false) {
                $filePath = $targetDir . DIRECTORY_SEPARATOR . $file;
                // Remove temp files if they are older than the max age
                if (preg_match('/\\.tmp$/', $file) && (filemtime($filePath) < time() - 3600)) {
                    @unlink($filePath);
                }
            }
            closedir($dir);
        }
        else {
            print json_encode([
                'status' => 'error',
                'type' => 'uploader',
                'message' => 'Failed to open temp directory.'
            ]);
            exit;
        }

        // Look for the content type header
        if (isset($_SERVER['HTTP_CONTENT_TYPE'])) {
            $contentType = $_SERVER['HTTP_CONTENT_TYPE'];
        }
        elseif (isset($_SERVER['CONTENT_TYPE'])) {
            $contentType = $_SERVER['CONTENT_TYPE'];
        }
        else {
            print json_encode([
                'status'=>'error',
                'msg'=>'undefined content type'
            ]);
            exit;
        }

        // Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
        if (strpos($contentType, 'multipart') !== false) {
            if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
                // Open temp file
                $out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk === 0 ? 'wb' : 'ab');
                if ($out) {
                    // Read binary input stream and append it to temp file
                    $in = fopen($_FILES['file']['tmp_name'], 'rb');
                    if ($in) {
                        while ($buff = fread($in, 4096)) {
                            fwrite($out, $buff);
                        }
                    }
                    else {
                        print json_encode([
                            'status' => 'error',
                            'type' => 'uploader',
                            'message' => 'Failed to open input stream'
                        ]);
                        exit;
                    }

                    fclose($in);
                    fclose($out);
                    @unlink($_FILES['file']['tmp_name']);
                }
                else {
                    print json_encode([
                        'status' => 'error',
                        'type' => 'uploader',
                        'message' => 'Failed to open output stream'
                    ]);
                    exit;
                }
            }
            else {
                print json_encode([
                    'status' => 'error',
                    'type' => 'uploader',
                    'message' => 'Failed to move uploaded file'
                ]);
                exit;
            }
        }
        else {
            // Open temp file
            $out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk === 0 ? 'wb' : 'ab');
            if ($out) {
                // Read binary input stream and append it to temp file
                $in = fopen('php://input', 'rb');
                if ($in) {
                    while ($buff = fread($in, 4096)) {
                        fwrite($out, $buff);
                    }
                }
                else {
                    print json_encode([
                        'status' => 'error',
                        'type' => 'uploader',
                        'message' => 'Failed to open input stream'
                    ]);
                    exit;
                }
                fclose($in);
                fclose($out);
            }
            else {
                print json_encode([
                    'status' => 'error',
                    'type' => 'uploader',
                    'message' => 'Failed to open output stream'
                ]);
                exit;
            }
        }

        if (($chunk+1 == $chunks)||$chunks==0) {
            //Check file name to not be used in database
            try {
                $stm=$uFunc->pdo('uAuth')->prepare('UPDATE
                u235_users
                SET
                avatar_timestamp=:avatar_timestamp
                WHERE
                user_id=:user_id
                ');
                $avatar_timestamp=time();
                $stm->bindParam(':avatar_timestamp', $avatar_timestamp,PDO::PARAM_INT);
                $stm->bindParam(':user_id', $userId,PDO::PARAM_INT);
                $stm->execute();
            }
            catch(PDOException $e) {$uFunc->error('1587239906'/*.$e->getMessage()*/,1);}

            $dir = $_SERVER['DOCUMENT_ROOT'].'/uAuth/avatars/'; //Адрес директории для сохранения картинки

            @uFunc::rmdir($dir.$userId);
            if (!file_exists($dir . $userId) && !mkdir($concurrentDirectory = $dir . $userId, 0755, true) && !is_dir($concurrentDirectory)) {
                print json_encode([
                    'status' => 'error',
                    'type' => 'uploader',
                    'message' => sprintf('Directory "%s" was not created', $concurrentDirectory)
                ]);
                exit;
            }

            if(!uFunc::create_empty_index('uAuth/avatars')) {
                $uFunc->error(1587239982);
            }
            $source_filename=$targetDir.'/'.$fileName;


            try {
                $im = new Imagick($source_filename);
            } catch (ImagickException $e) {
                print json_encode([
                    'status' => 'error',
                    'type' => 'uploader',
                    'message' => 'Could  not compress image'
                ]);
                exit;
            }
            $im->setImageFormat('jpeg');

            //Set to use jpeg compression
            $im->setImageCompression(Imagick::COMPRESSION_JPEG);
            //Set compression level (1 lowest quality, 100 highest quality)
            $im->setImageCompressionQuality(100);
            //Strip out unneeded meta data
            $im->stripImage();

            $im->writeImage($dir.$userId.'/orig.jpg');

            $im->clear();
            $im->destroy();

            //Delete temp directory
            uFunc::rmdir($targetDir);

            $avatar=new uAuth_avatar($uCore);

            print json_encode([
                'status' => 'success',
                'avatar_src' => $avatar->get_avatar('profile',$userId,time())
            ]);
            exit;
        }
        print json_encode([
            'status' => 'continue',
            'message' => 'Part ' .++$chunk." of $chunks"
        ]);
        exit;
    }
}

new profile_avatar_uploader($this);

