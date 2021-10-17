<?php
//require_once 'lib/htmlpurifier/library/HTMLPurifier.auto.php';
class uString {
	static function isDigits($string) {
		if(preg_match("#^[-]*[\d]+$#",$string))
			return true;
		return false;
	}
	static function isInt($string) {
		if(preg_match("#^[-]*[\d]+$#",$string))
			return true;
		return false;
	}
	static function isFloat($string) {
        if(preg_match("/^[-]*([1-9][0-9]*|0)(\.[0-9]*)?$/",$string))
			return true;
		return false;
	}
	static function isPrice($string) {
		if(preg_match("/^([1-9][0-9]*|0)(\.[0-9]{2})?$/",$string))
			return true;
		return false;
	}
	static function isPhone($phone) {
    	if(preg_match("/^\+?([0-9]){7,}$/",
              $phone))
			  return true;
	    else return false;
	}
	static function isHash($str) {	return preg_match("#^[\w\d]+$#i",$str) ? true : false;	}
	static function isName_ruen($text) {return preg_match("#^[\w- \.а-я]+$#iu",$text) ? true : false;}
	static function isEmail($text) {
        return filter_var($text, FILTER_VALIDATE_EMAIL);
        //return preg_match("#^[\w\d_\-]+[\w\d\._\-]*@[\w\d\._\-]+\.[\w\d]+$#",$text) ? true : false;
    }
	static function isDomain_name($text) {return preg_match("#^[\w\d\._\-а-я]{3,}\.[\w\dа-я\-]+$#iu",$text) ? true : false;}
	static function isIP($text){if(preg_match("#^[\d]{1,3}\.[\d]{1,3}\.[\d]{1,3}\.[\d]{1,3}$#",$text)) return true; return false; }
    static function isDate($text){if(preg_match("#^[\d]{1,2}\.[\d]{1,2}\.[\d]{4}$#",$text)) return true; return false; }
    static function isDate_en($text){if(preg_match("#^[\d]{1,2}/[\d]{1,2}/[\d]{4}$#",$text)) return true; return false; }
    public static function isTime($text){if(preg_match("#^[\d]{1,2}:[\d]{1,2}$#",$text)) {return true;} return false; }
	static function isFilename($text) {if(preg_match("#^[ \w\d-_\.\s,\"]+$#",$text)) return true; return false;}
	static function isFilename_rus($text) {if(preg_match("#^[ \w\d-_\.\sа-я,\"]+$#iu",$text)) return true; return false;}
	static function isUrl_rus($text) {if(preg_match("#^[http://|https://]*[\w\d-_\./а-я?&=%]+$#iu",$text)) return true; return false;}
	static function text2sql($text,$htmlspecialchars=true) {
		$entity_from=array('#', '"', "'", '`', '\\','/','<','>','_','%');
		$entity_to=array('#hash', '#2quote', '#quote', '#accent', '#bksl','#sl','#lt','#rt','#ul','#perc');
		//$text=stripslashes($text);//Только там, где заедает экранирование кавычек
		$text=str_replace($entity_from, $entity_to, $text);
		$text=strip_tags($text);
		if($htmlspecialchars) $text=htmlspecialchars($text, ENT_QUOTES);
		return addslashes($text);
	}
	static function text2mail($text) {
		$text=strip_tags($text);
		$text=htmlspecialchars($text, ENT_QUOTES);
		return addslashes($text);
	}
	static function text2screen($text) {
		return uString::text2mail($text);
	}
	static function sql2text($text,$htmltags=false/*false - теги будут в виде &-посл*/) {
		$entity_from=array('#perc','#ul','#rt','#lt','#sl','#bksl','#accent','#quote','#2quote', '#hash');
		$entity_to=array('%','_','>','<','/','\\','`',"'", '"', '#');

		$text=stripslashes($text);
		if($htmltags) $text=htmlspecialchars_decode($text, ENT_QUOTES);
        else $text=htmlspecialchars($text);
		return str_replace($entity_from, $entity_to, $text);
	}
	static function text2filename($str,$rename_insecure_files=false) {
        $str=preg_replace('/\s+/', '_', $str);
        $file_name=preg_replace("#[^\w\d-_\.]#", '', $str);
        if($rename_insecure_files) {
            if($file_name=='!production.htaccess') $file_name='!production.htaccess.txt';
            elseif($file_name=='php.ini') $file_name='php.ini.php';
            elseif($file_name=='index.html') $file_name='index.html.txt';
            elseif($file_name=='index.htm') $file_name='index.htm.txt';
            elseif($file_name=='index.php') $file_name='index.php.txt';
        }
        return $file_name;
	}
	static function text2article_number($str) {
	    $str=uString::rus2eng($str);
        $str=preg_replace("#[^\w\d]#", '', $str);
        return $str;
	}
    static function sanitize_filename($filename) {
        return mb_eregi_replace ('[<$>`&*\'|?"=/:\\\@]', ' ',$filename ,'ixm');
    }
    static function sanitize_text($text) {
        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);

        return $purifier->purify(trim($text));
    }
	static function rus2eng($str) {
		$rus= array('а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я','А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я');
		$eng=array('a','b','v','g','d','e','yo','zh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','kh','ts','ch','sh','sch','','i','','e','yu','ya','A','B','V','G','D','E','YO','ZH','Z','I','Y','K','L','M','N','O','P','R','S','T','U','F','H','TS','CH','SH','SCH','','I','','E','YU','YA');
		return str_replace($rus, $eng, $str);
	}
	static function rmMsWord($text) {
	 return preg_replace ( '#<!--[\s]*\[if gte mso[\s\S\n]*endif\][\s]*-->#' , '' , $text );
	}
    static function hide_email_part($email) {
        //return preg_replace('/(?<=.).(?=.*@)/u','*',$email);

        $em   = explode("@",$email);
        $name = implode(array_slice($em, 0, count($em)-1), '@');
        $len_sm  = floor(strlen($name)/2);
        $len_bg  = ceil(strlen($name)/2);

        return substr($name,0, $len_sm) . str_repeat('*', $len_bg) . "@" . end($em);
    }
    static function hide_phone_part($phone) {

        $phone_length=strlen($phone);
        if($phone>1) {
            $half_length=$phone % $phone_length;
            $phone_hidden=mb_substr($phone,$half_length);
            for($i=0;$i<($phone_length-$half_length);$i++) {
                $phone_hidden="*".$phone_hidden;
            }
        }
        else $phone_hidden="*";

        return $phone_hidden;
    }
    static function replace4sqlLike($str) {
        return str_replace('%','',str_replace('_','',$str));
    }
    static function removeHTML($html) {
//        if(!class_exists('HTMLPurifier_Config')) require_once('lib/htmlpurifier/library/HTMLPurifier.auto.php');
        $config = HTMLPurifier_Config::createDefault();
        $config->set('Core.Encoding', 'UTF-8'); // not using UTF-8
        $config->set('HTML.Allowed', ''); // Allow Nothing
        $purifier = new HTMLPurifier($config);
        return $purifier->purify($html);
    }
    static function repairHtml($html) {
        $html=mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        $doc = new DOMDocument();
        @$doc->loadHTML($html);
        return $doc->saveHTML();
    }
    static function clean_css($css) {
        $css=preg_replace("/<\\/?style(\\s+.*?>|>)/", "", $css);
        $css=preg_replace("/<\\/?script(\\s+.*?>|>)/", "", $css);
        $css=preg_replace("/<\\/?body(\\s+.*?>|>)/", "", $css);
        $css=preg_replace("/<\\/?html(\\s+.*?>|>)/", "", $css);
        return $css;
    }
    static function isHexColor($color) {
        return preg_match('#^[a-f0-9]{1,6}$#i', $color);
    }
    static function isMadColor($color) {
	if(
		$color==="@mad_primary_color"||
		$color==="@mad_primary_color_inverse"||
		$color==="@mad_primary_color_highlight"||
		$color==="@mad_primary_color_highlight_inverse"||
		$color==="@mad_primary_over_font_color"||
		$color==="@mad_primary_over_font_color_inverse"||
		$color==="@mad_site_font_color"||
		$color==="@mad_site_font_color_inverse"
	) return 1;
	return 0;
    }
}
