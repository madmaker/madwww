<?php
require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
class uFunc{
    public $uFunc;
    public $uSes;
    private $uCore,$mod_installed_ar,
    $conf;
    public $file_ext2fonticon;

    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);

        include_once 'inc/file_ext2fonticon.php';
    }

    //TEMPLATE
    /**deprecated! use processors/classes/uFunc*/public function head_short() {return $this->uFunc->head_short();}
    /**deprecated! use processors/classes/uFunc*/public function incCss($path,$order=0) {$this->uFunc->incCss($path,$order);}
    /**deprecated! use processors/classes/uFunc*/public function incJs($path,$order=0) {$this->uFunc->incJs($path,$order);}
    /**deprecated! use processors/classes/uFunc*/static function printJs($src) {
		  return '<script src="'.$src.'?'.v_timestamp.'" charset="UTF-8" type="text/javascript"></script>';
	}
    /**deprecated! use processors/classes/uFunc*/static function printCss($href) {return '<link href="'.$href.'?'.v_timestamp.'" rel="stylesheet" type="text/css" />'."\n";}

    //PAGE
    /**deprecated! use processors/classes/uFunc*/public function getStatic ($page_name) {return $this->uFunc->getStatic($page_name);}
    /**deprecated! use processors/classes/uFunc*/public function getStatic_by_id ($id) {return $this->uFunc->getStatic_by_id($id);}
    /**deprecated! use processors/classes/uFunc*/public function getStatic_text ($name) {return $this->uFunc->getStatic_text($name);}
    /**deprecated! use processors/classes/uFunc*/public function getStatic_text_by_id ($id) {return $this->uFunc->getStatic_text_by_id($id);}

    //CONF
    /**deprecated! use processors/classes/uFunc*/public function getConf ($field,$mod,$tolerant=false/*Выдавать ошибку, если не найдено или нет*/,$site_id=site_id) {return $this->uFunc->getConf($field,$mod,$tolerant,$site_id);}

    //HELPERS
    /**deprecated! use processors/classes/uFunc*/static function genPass() {
		  mt_srand(time());
		  $hash=  uFunc::genHash();
		  $pass="";
		  for($i=0;$i<10;$i++) {
			if(mt_rand(0,2)) $pass.=strtolower(substr($hash,$i,1));
			else $pass.=strtoupper(substr($hash,$i,1));
		  }
		  return $pass;
    }
    /**deprecated! use processors/classes/uFunc*/public static function genCode() {
			\processors\uFunc::genCode();
    }
    /**deprecated! use processors/classes/uFunc*/static function genHash() {
        mt_srand(time());
        return md5(mt_rand(0,time())*time());
    }
    /**deprecated! use processors/classes/uFunc*/static function passCrypt($pass,$reg_timestamp,$user_email,$user_id,$user_phone) {
        if(!defined(CRYPT_BLOWFISH)) define(CRYPT_BLOWFISH,1);
        $salt = hash('sha512',$user_phone.$user_id.$reg_timestamp);
        $pass=crypt($pass,$salt);
        $pass=hash('gost', $pass.$salt).md5($user_email);
        return $pass;
    }
    /**deprecated! use processors/classes/uFunc*/static function POST($url, $data, $referer='') {
        $data = http_build_query($data);// Convert the data array into URL Parameters like a=b&foo=bar etc.
        $url = parse_url($url);// parse the given URL
        if ($url['scheme'] != 'http') {die('Error: Only HTTP request are supported !');}
        $host = $url['host'];// extract host and path:
        $path = $url['path'];
        $fp = fsockopen($host, 80, $errno, $errstr, 30);// open a socket connection on port 80 - timeout: 30 sec
        if ($fp){// send the request headers:
            fputs($fp, "POST $path HTTP/1.1\r\n");
            fputs($fp, "Host: $host\r\n");
            if ($referer != '')
                fputs($fp, "Referer: $referer\r\n");
            fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
            fputs($fp, "Content-length: ". strlen($data) ."\r\n");
            fputs($fp, "Connection: close\r\n\r\n");
            fputs($fp, $data);
            $result = '';
            while(!feof($fp)) {$result .= fgets($fp, 128);}// receive the results of the request
        }
        else {
            return array(
                'status' => 'err',
                'error' => "$errstr ($errno)"
            );
        }
        fclose($fp);// close the socket connection:
        $result = explode("\r\n\r\n", $result, 2);// split the result header from the content
        $header = isset($result[0]) ? $result[0] : '';
        $content = isset($result[1]) ? $result[1] : '';
        return array(// return as structured array:
            'status' => 'ok',
            'header' => $header,
            'content' => $content
        );
    }
    /**deprecated! use processors/classes/uFunc*/static function ext2mime($ext) {
        $mime_types = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );
        if(isset($mime_types[$ext])) return $mime_types[$ext];
        return false;
    }
    /**deprecated! use processors/classes/uFunc*/static function glyphicons_ar() {
        return array('asterisk',
            'plus',
            'euro',
            'eur',
            'minus',
            'cloud',
            'envelope',
            'pencil',
            'glass',
            'music',
            'search',
            'heart',
            'star',
            'empty',
            'user',
            'film',
            'large',
            'th',
            'th-list',
            'ok',
            'remove',
            'zoom-in',
            'zoom-out',
            'off',
            'signal',
            'cog',
            'trash',
            'home',
            'file',
            'time',
            'road',
            'alt',
            'download',
            'upload',
            'inbox',
            'circle',
            'repeat',
            'refresh',
            'list-alt',
            'lock',
            'flag',
            'headphones',
            'volume-off',
            'volume-down',
            'volume-up',
            'qrcode',
            'barcode',
            'tag',
            'tags',
            'book',
            'bookmark',
            'print',
            'camera',
            'font',
            'bold',
            'italic',
            'text-height',
            'text-width',
            'align-left',
            'align-center',
            'align-right',
            'align-justify',
            'list',
            'indent-left',
            'indent-right',
            'facetime-video',
            'picture',
            'map-marker',
            'adjust',
            'tint',
            'edit',
            'share',
            'check',
            'move',
            'step-backward',
            'fast-backward',
            'backward',
            'play',
            'pause',
            'stop',
            'forward',
            'fast-forward',
            'step-forward',
            'eject',
            'chevron-left',
            'chevron-right',
            'plus-sign',
            'minus-sign',
            'remove-sign',
            'ok-sign',
            'question-sign',
            'info-sign',
            'screenshot',
            'remove-circle',
            'ok-circle',
            'ban-circle',
            'arrow-left',
            'arrow-right',
            'arrow-up',
            'arrow-down',
            'share-alt',
            'resize-full',
            'resize-small',
            'exclamation-sign',
            'gift',
            'leaf',
            'fire',
            'eye-open',
            'eye-close',
            'warning-sign',
            'plane',
            'calendar',
            'random',
            'comment',
            'magnet',
            'chevron-up',
            'chevron-down',
            'retweet',
            'shopping-cart',
            'folder-close',
            'folder-open',
            'resize-vertical',
            'resize-horizontal',
            'hdd',
            'bullhorn',
            'bell',
            'certificate',
            'thumbs-up',
            'thumbs-down',
            'hand-right',
            'hand-left',
            'hand-up',
            'hand-down',
            'circle-arrow-right',
            'circle-arrow-left',
            'circle-arrow-up',
            'circle-arrow-down',
            'globe',
            'wrench',
            'tasks',
            'filter',
            'briefcase',
            'fullscreen',
            'dashboard',
            'paperclip',
            'heart-empty',
            'link',
            'phone',
            'pushpin',
            'usd',
            'gbp',
            'sort',
            'sort-by-alphabet',
            'sort-by-alphabet-alt',
            'sort-by-order',
            'sort-by-order-alt',
            'sort-by-attributes',
            'sort-by-attributes-alt',
            'unchecked',
            'expand',
            'collapse-down',
            'collapse-up',
            'log-in',
            'flash',
            'log-out',
            'new-window',
            'record',
            'save',
            'open',
            'saved',
            'import',
            'export',
            'send',
            'floppy-disk',
            'floppy-saved',
            'floppy-remove',
            'floppy-save',
            'floppy-open',
            'credit-card',
            'transfer',
            'cutlery',
            'header',
            'compressed',
            'earphone',
            'phone-alt',
            'tower',
            'stats',
            'sd-video',
            'hd-video',
            'subtitles',
            'sound-stereo',
            'sound-dolby',
            'sound-5-1',
            'sound-6-1',
            'sound-7-1',
            'copyright-mark',
            'registration-mark',
            'cloud-download',
            'cloud-upload',
            'tree-conifer',
            'tree-deciduous',
            'cd',
            'save-file',
            'open-file',
            'level-up',
            'copy',
            'paste',
            'alert',
            'equalizer',
            'king',
            'queen',
            'pawn',
            'bishop',
            'knight',
            'baby-formula',
            'tent',
            'blackboard',
            'bed',
            'apple',
            'erase',
            'hourglass',
            'lamp',
            'duplicate',
            'piggy-bank',
            'scissors',
            'bitcoin',
            'btc',
            'xbt',
            'yen',
            'jpy',
            'ruble',
            'rub',
            'scale',
            'ice-lolly',
            'ice-lolly-tasted',
            'education',
            'option-horizontal',
            'option-vertical',
            'menu-hamburger',
            'modal-window',
            'oil',
            'grain',
            'sunglasses',
            'text-size',
            'text-color',
            'text-background',
            'object-align-top',
            'object-align-bottom',
            'object-align-horizontal',
            'object-align-left',
            'object-align-vertical',
            'object-align-right',
            'triangle-right',
            'triangle-left',
            'triangle-bottom',
            'triangle-top',
            'console',
            'superscript',
            'subscript',
            'menu-left',
            'menu-right',
            'menu-down',
            'menu-up'
        );
    }
    /**deprecated! use processors/classes/uFunc*/static function journal($data/*Текст ошибки. Данные для записи в журнал*/,$journal/*Имя файла журнала*/) { //Функция записи журнала
        if(!file_exists("journals")) mkdir("journals");
        @file_put_contents("journals/".$journal.'.html',"\n".date('d.m.Y, H:i:s',time()).'. User: '.$_SESSION['SESSION']['user_id'].'. Request_URI:'.$_SERVER['REQUEST_URI'].'. site_id:'.site_id.' IP:'.$_SERVER["REMOTE_ADDR"].'. Cookie_sesId:'.$_COOKIE["ses_id"].'.  Cookie_user_id:'.$_COOKIE['user_id'].'. Info:'.$data,FILE_APPEND);
    }

    //FILES AND FOLDERS
    /**deprecated! use processors/classes/uFunc*/static function rmdir($dir) {//Удаляет папку вместе со всеми файлами внутри
			if (is_dir($dir)) {
				//BUILDER_journal('ENTER DIR : '.$dir,'rmdir');
				$objects = scandir($dir);
				foreach ($objects as $object) {
					if ($object != "." && $object != "..") {
						 if (filetype($dir."/".$object) == "dir") uFunc::rmdir($dir."/".$object); else unlink($dir."/".$object);
						//BUILDER_journal($dir."/".$object,'rmdir');
					}
				}
				reset($objects);
				rmdir($dir);
			}
    }
    /**deprecated! use processors/classes/uFunc*/static function copy_dir($src,$dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    uFunc::copy_dir($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
    /**deprecated! use processors/classes/uFunc*/static function create_empty_index($dir) {
        if(!is_dir($dir)) return false;
        $dir_ar=explode('/',$dir);
        $cur_dir='';
        for($i=0;$i<count($dir_ar);$i++) {
            $cur_dir.=$dir_ar[$i].'/';
            if(!file_exists($cur_dir.'index.html')) copy('processors/index.html',$cur_dir.'index.html');
        }
        return true;
    }

    //MESSAGING
    /**deprecated! use processors/classes/uFunc*/public function mail($html,$title,$to_email,$from_txt='',$from_email='',$u_sroot=u_sroot,$site_id=site_id,$reply_line='',$use_smtp=false,$smtp_settings=array()) {
        return $this->uFunc->mail($html,$title,$to_email,$from_txt,$from_email,$u_sroot,$site_id,$reply_line,$use_smtp,$smtp_settings);
    }

    //SESSION AND AUTHORISATION
    /**deprecated! use processors/classes/uFunc*/public function sesHack($data="") {return $this->uFunc->sesHack($data);}

    /**deprecated! use processors/classes/uFunc
     * @param int $id
     * @param string $hash
     * @param int $page_access
     * @return bool
     */
    public function sesHack_test($id, $hash, $page_access=0) {return $this->uFunc->sesHack_test($id,$hash,$page_access);}
    /**deprecated! use processors/classes/uFunc*/public function get_uAuth_usersinfo_fields() {return $this->uFunc->get_uAuth_usersinfo_fields();}
    /**deprecated! use processors/classes/uFunc*/public function uAuth_usersinfo_field_id2title($field_id) {return $this->uFunc->uAuth_usersinfo_field_id2title($field_id);}
    /**deprecated! use processors/classes/uFunc*/public function uAuth_usersinfo_field_id2val($field_id) {return $this->uFunc->uAuth_usersinfo_field_id2val($field_id);}
    /**deprecated! use processors/classes/uFunc*/public function uAuth_users_field2val($field) {return $this->uFunc->uAuth_users_field2val($field);}
    /**deprecated! use processors/classes/uFunc*/public function insAuthDialog() {return $this->uFunc->insAuthDialog();}

    //NAVIGATION
    /**deprecated! use processors/classes/uFunc*/public function uMenu_list() {return $this->uFunc->uMenu_list();}

    //HTML BLOCKS
    /**deprecated! use processors/classes/uFunc*/public function insertHtmlBlock($pos) {return $this->uFunc->insertHtmlBlock($pos);}

    //MODULES
    /**deprecated! use processors/classes/uFunc*/public function mod_installed($mod_name,$site_id=site_id) {return $this->uFunc->mod_installed($mod_name,$site_id);}
}
