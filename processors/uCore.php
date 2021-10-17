<?php
require_once 'uConf.php';
require_once 'uSes.php';
require_once 'uFunc.php';
require_once 'uInt.php';
require_once 'uString.php';
require_once 'uMenu.php';
require_once 'uBc.php';
require_once 'processors/classes/uFunc.php';

class uCore {
    public $uFunc_new;
    public $page_content;
    public $is_homepage;
    /**
     * @var string
     */
    public $page_rightBar;
    private $db_handler;
    public $tFolder,$page,$page_temp,$mod;
    /**
     * Array of URL properties after REQUEST_URI divided by /
     *
     * NOTICE!!! Starts at 1. Not 0
     *
     * For example, mysite.com/mod/page/var1/var2 should be $uCore->url_prop[1] = var1, $uCore->url_prop[2] = var2
     * @var array
     */
    public $url_prop;
    public $page_panel,$page_name;
    public $uMenu,$uConf,$uFunc,$uSes,$uBc;
    protected $test;
    public $site;
    public $uInt;

    public $inc_fileIsLoaded;
    public $inc_js_order_minus_1;//order -1
    public $inc_js_order_0;//order 0
    public $inc_js_order_1;//order 1
    public $inc_js_order_2;//order 2

    public $inc_css_order_minus_1;//order -1
    public $inc_css_order_0;//order 0
    public $inc_css_order_1;//order 1
    public $inc_css_order_2;//order 2

    public $inc_fileIsPrinted;

    public function text($mod_page_ar,$string) {
        return $this->uInt->text($mod_page_ar,$string);
    }
    public function uInt_js($mod,$page) {
        $this->uInt->js($mod,$page);
    }
    public function uInt_print_js($mod,$page) {
        $this->uInt->print_js($mod,$page);
    }
    public function __construct() {
        $this->is_homepage=0;
        $this->page_content='';
		$this->uConf=new uConf();
        $this->uFunc=new uFunc($this);
        $this->uSes=new uSes($this);
        $this->uMenu=new uMenu($this);
        $this->uBc=new uBc($this);
        $this->page_panel='';
        $this->uFunc_new=new \processors\uFunc($this);

        $this->uInt=new uInt($this);

        if(!uString::isUrl_rus($_SERVER['SERVER_NAME'])) {
            define('site_id', 1);
        }
        else {
            try {
                $stm=$this->uFunc_new->pdo("common")->prepare("SELECT 
                site_id
                FROM 
                u235_sites
                WHERE 
                site_name=:site_name
                ");
                $site_name=$_SERVER['SERVER_NAME'];
                $stm->bindParam(':site_name', $site_name,PDO::PARAM_STR);
                $stm->execute();
            }
            catch(PDOException $e) {$this->uFunc_new->error('uCore 10'/*.$e->getMessage()*/);}

            /** @noinspection PhpUndefinedVariableInspection */
            if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) {
                if(!function_exists('idn_to_utf8')) {
                    uFunc::journal($_SERVER['SERVER_NAME'],"no_idn2utf8_site_is_not_found");
                    define('site_id',1);
                }
                else {
                    $server=idn_to_utf8($_SERVER['SERVER_NAME']);
                    try {
                        $stm=$this->uFunc_new->pdo("common")->prepare("SELECT 
                        site_id
                        FROM 
                        u235_sites
                        WHERE 
                        site_name=:site_name
                        ");
                        $stm->bindParam(':site_name', $server,PDO::PARAM_STR);
                        /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                        /** @noinspection PhpUndefinedMethodInspection */
                    }
                    catch(PDOException $e) {$this->uFunc_new->error('uCore 20'/*.$e->getMessage()*/);}

                    /** @noinspection PhpUndefinedMethodInspection */
                    if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) {
                        uFunc::journal($_SERVER['SERVER_NAME'],"site_is_not_found");
                        define('site_id',1);
                    }
                    else define('site_id',$qr->site_id);
                }
            }
            else define('site_id',$qr->site_id);
        }

        //get the site default domain name
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc_new->pdo("common")->prepare("SELECT 
                site_name,
                `ssl`
                FROM 
                u235_sites
                WHERE 
                site_id=:site_id AND 
                main=1 
                ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                //check protocol
                    if((int)$qr->ssl) {
                        if (!defined("u_protocol")) define("u_protocol", "https");
                    }
                    else {
                        if (!defined("u_protocol")) define("u_protocol", "http");
                    }

                if($qr->site_name!=$_SERVER['SERVER_NAME']) {
                    if(!defined("u_sroot")) define('u_sroot',u_protocol.'://'.$qr->site_name.'/');
                }

                if($qr->site_name!=$_SERVER['SERVER_NAME']) {
                    header('Location: ' . u_protocol . '://' . $qr->site_name . $_SERVER['REQUEST_URI']);
                    exit;
                }

            }
        }
        catch(PDOException $e) {$this->uFunc_new->error('uCore 30'/*.$e->getMessage()*/);}

        $this->uConf->define_site_root();

        $site_lang=$this->uInt->set_lang();
        define("site_lang",$site_lang);

        define('site_email',$this->uFunc->getConf('site_email','content',true));
        define('site_name',$this->uFunc->getConf('site_name','content',true));
        define('site_domain',$this->uFunc->getConf('site_domain','content',true));

		 //Разбираем запрос. Бьем по "/" в массив.
		 $request=explode("/",substr($_SERVER['REQUEST_URI'],0,(strpos($_SERVER['REQUEST_URI'],"?") ? strpos($_SERVER['REQUEST_URI'],"?") : strlen($_SERVER['REQUEST_URI']))));

		 //ищем 2 параметра: module/page.
        if(isset($request[1])) {
            if(trim($request[1])=='sitemap.xml') {
                $content=$this->uFunc->getConf('sitemap.xml','content');

                if($content !== "") {
                    die($content);
                }
                else {
                    $file_patch = $_SERVER['DOCUMENT_ROOT']."/Sitemap/sitemap_".site_id.".xml";
                    if(file_exists($file_patch)) {
                        $content = file_get_contents($_SERVER['DOCUMENT_ROOT']."/Sitemap/sitemap_".site_id.".xml");
                    }
                    else {
                        $content = "";
                    }

                    die($content);
                }
            }
            elseif(trim($request[1])=='robots.txt') {
                $content=$this->uFunc->getConf('robots.txt','content');

                if($content !== "") {
                    die($content);
                }
                else {
                    $file_patch = $_SERVER['DOCUMENT_ROOT']."/Sitemap/robots_".site_id.".txt";
                    if(file_exists($file_patch)) {
                        $content = file_get_contents($_SERVER['DOCUMENT_ROOT']."/Sitemap/robots_".site_id.".txt");
                    }
                    else {
                        $content = "";
                    }

                    die($content);
                }
            }
        }

        if(!isset($request[1],$request[2])) return $this->check_alias_page();

		 for($i=1;$i<=2;$i++) {//Проврка переданных mod и page
			 $request[$i]=trim($request[$i]);
			 if(!uString::isFilename($request[$i])) $request[$i]=rawurldecode($request[$i]);
		 }
		 for($i=3;$i<=count($request);$i++) @$this->url_prop[($i-2)]=$request[$i];

        if(
            ($request[1]=="page"||$request[1]=="static")
        &&site_id==6) $request[1]='uPage';
		 //articles (uEditor)
		 if($request[1]=="page"||$request[1]=="static") {//Если запрошена текстовая страница - достаем из базы этот материал
             try {
                 /** @noinspection PhpUndefinedMethodInspection */
                 $stm=$this->uFunc_new->pdo("pages")->prepare("SELECT
                 page_id,
                 page_title,
                 page_name,
                 page_access,
                 page_show_title_in_content,
                 navi_parent_page_id,
                 navi_parent_menu_id,
                 meta_keywords,
                 meta_description,
                 views_counter
                 FROM
                 u235_pages_html
                 WHERE
                 (page_name=:page_name OR page_id=:page_name) AND
                 site_id=:site_id
                 ");
                 $page_name=$request[2];
                 $site_id=site_id;
                 /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_name', $page_name,PDO::PARAM_STR);
                 /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                 /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
             }
             catch(PDOException $e) {$this->uFunc_new->error('uCore 60'/*.$e->getMessage()*/);}

             /** @noinspection PhpUndefinedMethodInspection */
             /** @noinspection PhpUndefinedVariableInspection */
             if($qr=$stm->fetch(PDO::FETCH_ASSOC)) {//Если найдено
				 $this->page=$qr;//Записываем все данные
				 return $this->setup_staticPage();//Грузим построитель статей
			 }
             else {
                 $request[2]=htmlspecialchars($request[2]);
                 uFunc::journal('next try with no quotes: '.$request[2],'builder_404');//Записываем ошибку;
                 try {
                     /** @noinspection PhpUndefinedMethodInspection */
                     $stm=$this->uFunc_new->pdo("pages")->prepare("SELECT
                     page_id,
                     page_title,
                     page_name,
                     page_access,
                     page_show_title_in_content,
                     navi_parent_page_id,
                     navi_parent_menu_id,
                     meta_keywords,
                     meta_description
                     FROM
                     u235_pages_html
                     WHERE
                     (page_name=:page_name OR page_id=:page_name) AND
                     site_id=:site_id
                     ");
                     $page_name=$request[2];
                     $site_id=site_id;
                     /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_name', $page_name,PDO::PARAM_STR);
                     /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                     /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                 }
                 catch(PDOException $e) {$this->uFunc_new->error('uCore 70'/*.$e->getMessage()*/);}

                 /** @noinspection PhpUndefinedMethodInspection */
                 if($qr=$stm->fetch(PDO::FETCH_ASSOC)) {//Если найдено
                     $this->page=$qr;//Записываем все данные
                     return $this->setup_staticPage();//Грузим построитель статей
                 }
             }
		 }
		 //uPage
		 elseif($request[1]=="uPage") {//Если запрошена страница uPage
             //Сначала проверяем, нет ли с таким именем зарегистрированного скрипта в pages_list
             try {
                 /** @noinspection PhpUndefinedMethodInspection */
                 $stm=$this->uFunc_new->pdo("pages")->prepare("SELECT
                 *
                 FROM
                 u235_pages_list
                 WHERE
                 page_mod='uPage' AND
                 page_name=:page_name
                 ");
                 $page_name=$request[2];
                 /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_name', $page_name,PDO::PARAM_STR);
                 /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
             }
             catch(PDOException $e) {$this->uFunc_new->error('uCore 80'/*.$e->getMessage()*/);}

             /** @noinspection PhpUndefinedMethodInspection */
             /** @noinspection PhpUndefinedVariableInspection */
             if($qr=$stm->fetch(PDO::FETCH_ASSOC)) {//Если найдено
                 $this->page=$qr;
                 return $this->setup_modulePage();//Грузим страницу из БД pages
			 }
             else {//Ищем страницу uPage
                 //Если $request[2] - ID страницы
                 if(uString::isDigits($request[2])) {
                     try {
                         /** @noinspection PhpUndefinedMethodInspection */
                         $stm=$this->uFunc_new->pdo("uPage")->prepare("SELECT
                         page_id,
                         page_title,
                         page_url,
                         page_description,
                         page_keywords,
                         page_width,
                         navi_parent_page_id,
                         show_title,
                         views_counter
                         FROM
                         u235_pages
                         WHERE
                         page_id=:page_id AND
                         site_id=:site_id
                         ");
                         $page_id=$request[2];
                         $site_id=site_id;
                         /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
                         /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                         /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                     }
                     catch(PDOException $e) {$this->uFunc_new->error('uCore 90'/*.$e->getMessage()*/);}
                 }
                 else {//$request[2] - не ID страницы. Ищем по URL
                     try {
                         /** @noinspection PhpUndefinedMethodInspection */
                         $stm=$this->uFunc_new->pdo("uPage")->prepare("SELECT
                         page_id,
                         page_title,
                         page_url,
                         page_description,
                         page_keywords,
                         page_width,
                         navi_parent_page_id,
                         show_title,
                         views_counter
                         FROM
                         u235_pages
                         WHERE
                         (
                             page_url=:page_url OR 
                             old_text_page_name=:page_url
                             ) AND
                         site_id=:site_id
                         ");
                         $page_url=$request[2];
                         $site_id=site_id;
                         /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_url', $page_url,PDO::PARAM_STR);
                         /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                         /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                     }
                     catch(PDOException $e) {$this->uFunc_new->error('uCore 100'/*.$e->getMessage()*/);}
                 }

                 /** @noinspection PhpUndefinedMethodInspection */
                 if($page=$stm->fetch(PDO::FETCH_ASSOC)) {//Если найдено
                     $this->page=$page;
                     return $this->setup_uPage_page();//Грузим страницу из БД uPage
                 }
                 else {
                     //Если страница так и не загружена - грузим страницу по умолчанию
                     uFunc::journal('uPage is not found','builder_404');//Записываем ошибку
                     return $this->setup_default();//Грузим страницу по умолчанию.
                 }
             }
		 }
		 else {//Если задан модуль - не page/static
             try {
                 /** @noinspection PhpUndefinedMethodInspection */
                 $stm=$this->uFunc_new->pdo("pages")->prepare("SELECT
                 *
                 FROM
                 u235_pages_list
                 WHERE
                 page_mod=:page_mod AND
                 page_name=:page_name
                 ");
                 $page_mod=$request[1];
                 $page_name=$request[2];
                 /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_name', $page_name,PDO::PARAM_STR);
                 /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_mod', $page_mod,PDO::PARAM_STR);
                 /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
             }
             catch(PDOException $e) {$this->uFunc_new->error('uCore 110'/*.$e->getMessage()*/);}
             /** @noinspection PhpUndefinedMethodInspection */
             /** @noinspection PhpUndefinedVariableInspection */
             if($qr=$stm->fetch(PDO::FETCH_ASSOC)) {//Если найдено в БД
				 $this->page=$qr;
				 return $this->setup_modulePage();//Грузим страницу
			 }
			 else return $this->check_alias_page(); //Если НЕ найдено в БД - проверяем в алиасах
		 }
		 //Если страница так и не загружена - грузим страницу по умолчанию
		 uFunc::journal('staticpage not found','builder_404');//Записываем ошибку
		 return $this->setup_default();//Грузим страницу по умолчанию.
	}
    /*deprecated! use processors/uSes*/public function access ($access/*код доступа*/) {return $this->uSes->access($access);}
	private function setup_staticPage() {
        $this->uSes->logInCheck();//Проводим опознавание пользователя
		//Проверяем, есть ли у пользователя доступ к странице
		if(!$this->access($this->page['page_access'])) {//Если доступа к странице нет, то отображаем страницу по умолчанию
            uFunc::journal('page_access1','builder_access_deny');
			return $this->setup_default();
		}
		$this->page['page_mod']=$this->mod='page';//Модуль staticPages - page
		$this->page_name=&$this->page['page_name'];
        ob_start();

        include_once "uEditor/inc/setup_article.php";

        $setup_article=new uEditor_setup_article($this,$this->page['page_id']);

        if($this->access(7)) {
            $this->page_content.=$setup_article->admin_page_builder();
        }
        else $this->page_content.=$setup_article->cache_builder();

        $return_cnt=ob_get_contents();
        ob_end_clean();
        $this->page_content.=$return_cnt;
        include "templates/template.php";
        return true;
	 }
	private function setup_modulePage() {
         $this->mod=&$this->page['page_mod'];
         $this->page_name=&$this->page['page_name'];

         if($this->mod==="mainpage"&&$this->page_name==="index") return $this->setup_default();

        if($this->access(7)) {
            $this->page_content.='<div class="uNavi_dialogs" style="display:none"></div>';
            $this->uFunc->incJs(u_sroot.'js/u235/jquery/jquery.uranium235plugins.js');
        }

		 //Если к странице не задано, чтобы ядро не проверяло сессию и доступ
		 if($this->page['noSesCheck']=='0') {
			 $this->uSes->logInCheck();//Проводим опознавание пользователя

             //print_r($_SESSION['acl']);
			 if(!$this->access($this->page['page_access'])) {//Если доступа к странице нет, то отображаем страницу по умолчанию
				 uFunc::journal('page_access2','builder_access_deny');
				 return $this->setup_default();
			 }
		 }


        /** @noinspection PhpIncludeInspection */
        include $this->mod."/".$this->page_name.".php";
        return true;
	}
    private function setup_uPage_page() {
        $this->uSes->logInCheck();//Проводим опознавание пользователя
        $this->page['page_mod']=$this->mod='uPage';
        $this->page_name=$this->page['page_name']=$this->page['page_url'];
        ob_start();

        if($this->access(7)) {
            $this->page_content.='<div class="uNavi_dialogs" style="display:none"></div>';
            $this->uFunc->incJs(u_sroot.'js/u235/jquery/jquery.uranium235plugins.js');
        }

        include_once "uPage/inc/setup_uPage_page.php";
        $uPage=new uPage_setup_uPage_page($this,$this->page['page_id']);
        if($this->access(7)) $uPage->admin_page_builder();
        else $uPage->cache_builder();

        $return_cnt=ob_get_contents();
        ob_end_clean();
        $this->page_content.=$return_cnt;
        include "templates/template.php";
        return true;
    }
    private function setup_uEvents_page($event_id) {
        $this->uSes->logInCheck();//Проводим опознавание пользователя

        $this->page['page_id']=1803;
        $this->page['page_mod']='uEvents';
        $this->page['page_name']='event';
        $this->page['navi_parent_menu_id']=0;
        $this->page['navi_parent_page_id']=0;

        require_once "uEvents/event.php";
        $uEvent=new \uEvents\event($this);
        $uEvent->event_id=$event_id;
        if(!$uEvent->check_data()) {
            header('Location: '.u_sroot);
            exit('4');
        }
        ob_start();
        if($this->access(7)) {
            $this->page_content.='<div class="uNavi_dialogs" style="display:none"></div>';
            $this->uFunc->incJs(u_sroot.'js/u235/jquery/jquery.uranium235plugins.js');
        }
        $uEvent->get_page();

        $return_cnt=ob_get_contents();
        ob_end_clean();
        $this->page_content.=$return_cnt;
        include "templates/template.php";
        return 0;
//        $this->uSes->logInCheck();//Проводим опознавание пользователя
        $this->page['page_access']=0;
//        $this->page['page_title']='';
//        $this->page['page_template_id']='';
//        $this->page['navi_personal_menu']=;
//        $this->page['page_category']=;
        $this->page['noSesCheck']=0;
        $this->url_prop[0]="uEvents";
        $this->url_prop[1]=$event_id;
        $this->setup_modulePage();
//        ob_start();
//
//        if($this->access(7)) {
//            $this->page_content.='<div class="uNavi_dialogs" style="display:none"></div>';
//            $this->uFunc->incJs(u_sroot.'js/u235/jquery/jquery.uranium235plugins.js');
//        }
//
//        require_once "uEvents/event.php";
//        return 1;
//
//        include_once "uPage/inc/setup_uPage_page.php";
//        $uPage=new uPage_setup_uPage_page($this,$this->page['page_id']);
//        if($this->access(7)) $uPage->admin_page_builder();
//        else $uPage->cache_builder();
//
//        $return_cnt=ob_get_contents();
//        ob_end_clean();
//        $this->page_content.=$return_cnt;
//        include "templates/template.php";
        return true;
    }
    private function check_alias_page() {
        $alias1=$alias=substr($_SERVER['REQUEST_URI'],1,(strpos($_SERVER['REQUEST_URI'],"?") ? strpos($_SERVER['REQUEST_URI'],"?")-1 : strlen($_SERVER['REQUEST_URI'])));//Т.к. 0-й символ - слэш /, то надо начинать поиск не с 0, а с 1-го символа
        if(substr($alias,-1)=='/') $alias1=substr($alias,0,-1);
        //Русские буквы передаются ввиде херни. Возможно нужно декодировать
        $alias_rus=urldecode($alias);
        $alias_rus1=urldecode($alias1);

        if(uString::isUrl_rus($alias)) {//Алиас - любой адекватный url
            try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc_new->pdo("uPage")->prepare("SELECT
			*
			FROM
			u235_pages
			WHERE
			(
			page_url=:alias OR
			page_url=:alias1 OR
			page_url=:alias_rus OR
			page_url=:alias_rus1 OR
			old_text_page_name=:alias OR
			old_text_page_name=:alias1 OR
			old_text_page_name=:alias_rus OR
			old_text_page_name=:alias_rus1
			) AND
			site_id=:site_id
			");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':alias', $alias,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':alias1', $alias1,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':alias_rus', $alias_rus,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':alias_rus1', $alias_rus1,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc_new->error('1583887672'/*.$e->getMessage()*/);}

            /** @noinspection PhpUndefinedMethodInspection */
            /** @noinspection PhpUndefinedVariableInspection */
            if($page=$stm->fetch(PDO::FETCH_ASSOC)) {
                $this->page=$page;//Записываем данные
                return $this->setup_uPage_page();//Грузим текстовую страницу
            }

            $alias_urlencoded=uString::text2sql($alias);
            $alias1_urlencoded=uString::text2sql($alias1);
            $alias_rus_urlencoded=uString::text2sql($alias_rus);
            $alias_rus1_urlencoded=uString::text2sql($alias_rus1);
            //EVENTS
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc_new->pdo("uEvents")->prepare("SELECT
                event_id
                FROM
                u235_events_list
                WHERE
                (
                event_url=:alias OR
                event_url=:alias1 OR
                event_url=:alias_rus OR
                event_url=:alias_rus1
                ) AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':alias', $alias_urlencoded,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':alias1', $alias1_urlencoded,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':alias_rus', $alias_rus_urlencoded,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':alias_rus1', $alias_rus1_urlencoded,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc_new->error('uCore 130'/*.$e->getMessage()*/);}

            /** @noinspection PhpUndefinedMethodInspection */
            /** @noinspection PhpUndefinedVariableInspection */
            if($event=$stm->fetch(PDO::FETCH_OBJ)) {
                return $this->setup_uEvents_page($event->event_id);//Грузим текстовую страницу
            }


            if(site_id!=6) {
                //TEXTS
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc_new->pdo("pages")->prepare("SELECT
                    *
                    FROM
                    u235_pages_html
                    WHERE
                    (
                    page_alias=:alias OR
                    page_alias=:alias1 OR
                    page_alias=:alias_rus OR
                    page_alias=:alias_rus1
                    ) AND
                    site_id=:site_id
                    ");
                    $site_id=site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':alias', $alias,PDO::PARAM_STR);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':alias1', $alias1,PDO::PARAM_STR);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':alias_rus', $alias_rus,PDO::PARAM_STR);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':alias_rus1', $alias_rus1,PDO::PARAM_STR);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc_new->error('uCore 140'/*.$e->getMessage()*/);}

                /** @noinspection PhpUndefinedMethodInspection */
                if($page=$stm->fetch(PDO::FETCH_ASSOC)) {//Если найдено в БД
                    $this->page=$page;//Записываем данные
                    return $this->setup_staticPage();//Грузим текстовую страницу
                }
            }
        }

        return $this->setup_default();
    }
	private function setup_default (/*$sesChecked=false*/) {//Показывает пользователю страницу по умолчанию

        $this->is_homepage=1;
        $mp_page_id = $this->uFunc->getConf("mainpage_page_id", "content");
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc_new->pdo("uPage")->prepare("SELECT
            page_id,
            page_title,
            page_url,
            page_description,
            page_keywords,
            page_width,
            navi_parent_page_id,
            show_title,
            views_counter
            FROM
            u235_pages
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $mp_page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc_new->error('uCore 150'/*.$e->getMessage()*/);}





        if(site_id==57||site_id==67) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc_new->pdo("pages")->prepare("SELECT
                 *
                 FROM
                 u235_pages_list
                 WHERE
                 page_mod=:page_mod AND
                 page_name=:page_name
                 ");
                $page_mod="obooking";
                $page_name="calendar";
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_name', $page_name,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_mod', $page_mod,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc_new->error('uCore 110'/*.$e->getMessage()*/);}
            /** @noinspection PhpUndefinedMethodInspection */
            /** @noinspection PhpUndefinedVariableInspection */
            if($qr=$stm->fetch(PDO::FETCH_ASSOC)) {//Если найдено в БД
                $this->page=$qr;
                return $this->setup_modulePage();//Грузим страницу
            }
        }

        if(site_id==31||site_id==17) {
            if(!isset($_COOKIE["mergerphp"])) {
                setcookie("mergerphp",1,(time() + 2592000),'/');//Создаем куки с sesId и userId
                header('Location: https://ideal.fi/merger');
                exit;
            }
        }






        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        if($page=$stm->fetch(PDO::FETCH_ASSOC)) {//Если найдено
            $this->page=$page;
            return $this->setup_uPage_page();//Грузим страницу из БД uPage
        }

        return false;
	 }
    /*deprecated! use processors/classes/uFunc*/public function error($key,$no_html=0) {return $this->uFunc_new->error($key,$no_html);}
    /*deprecated! use processors/classes/uFunc*/public function pdo($db) {return $this->uFunc_new->pdo($db);}
    public function query($db,$query) {
        if(!isset($this->db_handler[$db])) {
            $this->db_handler[$db]=new mysqli(db_host/*host*/ , $this->uConf->sql['user'], $this->uConf->sql['pass'], 'madmakers_'.$db);
            if(!$this->db_handler) $this->uFunc_new->error('uCore 160');
            /** @noinspection PhpUndefinedMethodInspection */
            $this->db_handler[$db]->set_charset("utf8");
        }
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->db_handler[$db]->query($query);
    }
}
