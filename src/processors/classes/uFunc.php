<?php
namespace processors;
use fEmail;
use fEnvironmentException;
use fSMTP;
use fValidationException;
use mPDF;
use PDO;
use PDOException;
use Pelago\Emogrifier;
use RuntimeException;
use uSes;
use uString;

if(isset($GLOBALS['BUILDER']['cron'])) {
    require_once 'processors/uSes.php';
//    require_once 'lib/flourishlib/init.php';
//    require_once 'lib/emogrifier/Classes/Emogrifier.php';
}

class uFunc {
    public $file_ext2fonticon;
    private $uSes;
    private $uCore,
    $conf,$pdo_handler;

    //COMMON
    public function site_id2u_sroot($site_id) {
        if(!isset($this->site_id2u_sroot_ar)) {
            $this->site_id2u_sroot_ar = [];
        }
        if(!isset($this->site_id2u_sroot_ar[$site_id])) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm = $this->pdo('common')->prepare('SELECT 
                site_name,
                `ssl`
                FROM 
                u235_sites
                WHERE
                main=1 AND
                site_id=:site_id
                ');
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if (!$res = $stm->fetch(PDO::FETCH_OBJ)) {
                    $this->error('proc uFunc 10');
                }
                if ((int)$res->ssl) {
                    $u_sroot = 'https://';
                }
                else {
                    $u_sroot = 'http://';
                }

                $this->site_id2u_sroot_ar[$site_id]=$u_sroot . $res->site_name;
            } catch (PDOException $e) {$this->error('proc uFunc 20'/*.$e->getMessage()*/);}
        }
        return $this->site_id2u_sroot_ar[$site_id].'/';
    }

    public static function slack($message, $room = 'engineering', $icon = ':longbox:') {
        $room = ($room) ?: 'engineering';
        $data = 'payload=' . json_encode(array(
                'channel' =>  "#{$room}",
                'text' =>  $message,
                'icon_emoji' =>  $icon
            ));

        // You can get your webhook endpoint from your Slack settings
        if(site_id==8) {
            $ch = curl_init('https://hooks.slack.com/services/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');
        }
        elseif(site_id==44) {
            $ch = curl_init('https://hooks.slack.com/services/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');
        }
        else {
            return 0;
        }
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        // Laravel-specific log writing method
        // Log::info("Sent to Slack: " . $message, array('context' => 'Notifications'));
        return $result;
    }
    public static function color_inverse($color){
        $color = str_replace('#', '', $color);
        if (strlen($color) !== 6){ return '000000'; }
        $rgb = '';
        for ($x=0;$x<3;$x++){
            $c = 255 - hexdec(substr($color,(2*$x),2));
            $c = ($c < 0) ? 0 : dechex($c);
            $rgb .= (strlen($c) < 2) ? '0'.$c : $c;
        }
        return '#'.$rgb;
    }
    public static function color2monochrome($color){
        $color = str_replace('#', '', $color);
        if (strlen($color) !== 6){ return '000000'; }
        $color_sum=0;
        for ($x=0;$x<3;$x++){
            $c = hexdec(substr($color,(2*$x),2));
            if($c>127) {
                $color_sum++;
            }
        }
        if($color_sum>1) {
            return '#fff';
        }

        return '#000';
    }

    //FILES AND FOLDERS
    public static function rmdir($dir) {//Удаляет папку вместе со всеми файлами внутри
        if (is_dir($dir)) {
            //BUILDER_journal('ENTER DIR : '.$dir,'rmdir');
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object !== '.' && $object !== '..') {
                    if (filetype($dir. '/' .$object) === 'dir') {
                        self::rmdir($dir . '/' . $object);
                    } else {
                        unlink($dir . '/' . $object);
                    }
                    //BUILDER_journal($dir."/".$object,'rmdir');
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }
    public function copy_dir($src,$dst) {
        $dir = opendir($src);
        if (!mkdir($dst) && !is_dir($dst)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $dst));
        }
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file !== '.' ) && ( $file !== '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    $this->copy_dir($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
    public static function create_empty_index($dir) {
        if(!is_dir($dir)) {
            return false;
        }
        $dir_ar=explode('/',$dir);
        $cur_dir='';
        foreach ($dir_ar as $iValue) {
            $cur_dir.= $iValue .'/';
            if(!file_exists($cur_dir.'index.html')) {
                $file = fopen($cur_dir.'index.html', 'wb') or die(1583221213);
                fclose($file);
            }
        }
        return true;
    }

    //TEMPLATE
    private $show_left_bar=-1;
    public $show_uCat_left_menu=0;
    public function show_left_bar() {
        if($this->show_left_bar===-1) {
            $this->show_left_bar=0;

            //Условия, при которых левое меню отображается

            $this->show_uCat_left_menu=0;
            if($this->mod_installed('uCat')) {//Левое меню каталога
                $uCat_pages_show_left_bar=(int)$this->getConf('pages_show_left_bar', 'uCat');
                if($uCat_pages_show_left_bar) {//Отображать на всех страницах
                    $this->show_left_bar=$this->show_uCat_left_menu=1;
                }

                elseif($this->uCore->mod=== 'mainpage') {
                    if($this->uCore->page_name==='index') {//Главная страница
                        $mainpage_show_left_bar=(int)$this->getConf('mainpage_show_left_bar', 'uCat');
                        if($mainpage_show_left_bar) {
                            $this->show_left_bar = $this->show_uCat_left_menu = 1;
                        }
                    }
                }

                elseif($this->uCore->mod=== 'uPage') {
                    $mainpage_page_id=(int)$this->getConf('mainpage_page_id', 'content');
                    $cur_page_id=(int)$this->uCore->page['page_id'];
                    if($mainpage_page_id===$cur_page_id) {//Главная страница
                        $mainpage_show_left_bar=(int)$this->getConf('mainpage_show_left_bar', 'uCat');
                        if($mainpage_show_left_bar) {
                            $this->show_left_bar = $this->show_uCat_left_menu = 1;
                        }
                    }
                }

                elseif ($this->uCore->mod=== 'uCat') {
                    if($this->uCore->page_name=== 'cats') {
                        $uCat_sect_show_left_bar=(int)$this->getConf('sect_show_left_bar', 'uCat');
                        if($uCat_sect_show_left_bar) {
                            $this->show_left_bar = $this->show_uCat_left_menu = 1;
                        }
                    }
                    elseif($this->uCore->page_name=== 'items') {
                        $uCat_show_left_menu_on_cats=(int)$this->getConf('show_left_menu_on_cats', 'uCat');
                        if($uCat_show_left_menu_on_cats) {
                            $this->show_left_bar = $this->show_uCat_left_menu = 1;
                        }
                    }
                    elseif ($this->uCore->page_name === 'item') {
                        $uCat_item_info_show_left_bar=(int)$this->getConf('item_info_show_left_bar', 'uCat');
                        if($uCat_item_info_show_left_bar) {
                            $this->show_left_bar = $this->show_uCat_left_menu = 1;
                        }
                    }
                }

            }

//            $this->show_obooking_menu=0;
//            if($this->mod_installed("obooking")) {
//                if (!$this->show_left_bar) {
//                    if ($this->uCore->mod === "obooking") $this->show_left_bar = $this->show_obooking_menu = 1;
//                }
//            }



            //Условия, при которых левое меню или другие меню НЕ отображается
            if($this->show_left_bar) {
                if(($this->uCore->mod === 'mainpage') && $this->uCore->page_name === 'index') {//Главная страница
                    $mainpage_show_left_bar=(int)$this->getConf('mainpage_show_left_bar', 'uCat');
                    if(!$mainpage_show_left_bar) {
                        return $this->show_left_bar = $this->show_uCat_left_menu = 0;
                    }
                }

                if ($this->uCore->mod === 'uCat') {
                    if ($this->uCore->page_name === 'cart') {
                        return $this->show_left_bar = $this->show_uCat_left_menu = 0;
                    }
                    if ($this->uCore->page_name === 'checkout_login') {
                        return $this->show_left_bar = $this->show_uCat_left_menu = 0;
                    }
                    if ($this->uCore->page_name === 'checkout_contractor') {
                        return $this->show_left_bar = $this->show_uCat_left_menu = 0;
                    }
                    if ($this->uCore->page_name === 'checkout_delivery') {
                        return $this->show_left_bar = $this->show_uCat_left_menu = 0;
                    }
                    if ($this->uCore->page_name === 'checkout_confirm') {
                        return $this->show_left_bar = $this->show_uCat_left_menu = 0;
                    }
                    if ($this->uCore->page_name === 'order_info') {
                        return $this->show_left_bar = $this->show_uCat_left_menu = 0;
                    }
                    if($this->uCore->page_name=== 'cats') {
                        $uCat_sect_show_left_bar=(int)$this->getConf('sect_show_left_bar', 'uCat');
                        if(!$uCat_sect_show_left_bar) {
                            return $this->show_left_bar = $this->show_uCat_left_menu = 0;
                        }
                    }
                    if($this->uCore->page_name=== 'items') {
                        $uCat_show_left_menu_on_cats=(int)$this->getConf('show_left_menu_on_cats', 'uCat');
                        if(!$uCat_show_left_menu_on_cats) {
                            return $this->show_left_bar = $this->show_uCat_left_menu = 0;
                        }
                    }
                    if ($this->uCore->page_name === 'item') {
                        $uCat_item_info_show_left_bar=(int)$this->getConf('item_info_show_left_bar', 'uCat');
                        if(!$uCat_item_info_show_left_bar) {
                            return $this->show_left_bar = $this->show_uCat_left_menu = 0;
                        }
                    }
                }

                if ($this->uCore->mod === 'uPage') {
                    $cur_page_id = (int)$this->uCore->page['page_id'];
                    if ((int)$this->getConf('header_page_id', 'content') === $cur_page_id) {
                        return $this->show_left_bar = $this->show_uCat_left_menu = 0;
                    }//Шапка
                    if ((int)$this->getConf('header_page_id_others', 'content') === $cur_page_id) {
                        return $this->show_left_bar = $this->show_uCat_left_menu = 0;
                    }//Шапка
                    if ((int)$this->getConf('footer_page_id_others', 'content') === $cur_page_id) {
                        return $this->show_left_bar = $this->show_uCat_left_menu = 0;
                    }//Подвал
                    if ((int)$this->getConf('footer_page_id', 'content') === $cur_page_id) {
                        return $this->show_left_bar = $this->show_uCat_left_menu = 0;
                    }//Подвал
                    if((int)$this->getConf('mainpage_page_id', 'content')===$cur_page_id) {//Главная страница
                        $mainpage_show_left_bar=(int)$this->getConf('mainpage_show_left_bar', 'uCat');
                        if(!$mainpage_show_left_bar) {
                            return $this->show_left_bar = $this->show_uCat_left_menu = 0;
                        }
                    }
                }

                if($this->uCore->mod=== 'obooking') {//В obooking не отображать меню каталога
                    return $this->show_left_bar=0;
                }
                if($this->uCore->mod=== 'configurator') {//В obooking не отображать меню каталога
                    return $this->show_left_bar=0;
                }
            }
        }
        return $this->show_left_bar;
    }
    private $show_right_bar=-1;
    public function show_right_bar() {
        if($this->show_right_bar===-1) {
            $this->show_right_bar=0;

            //Условия, при которых правую панель нужно показывать
            if(isset($this->page_rightBar) && $this->page_rightBar !== '' && $this->uCore->mod === 'uCat') {
                if($this->uCore->page_name==='items') {
                    $this->show_right_bar = 1;
                }
                elseif($this->uCore->page_name === 'search') {
                    $this->show_right_bar = 1;
                }
            }

            //Условия, при которых правую панель нужно скрыть
        }
        return $this->show_right_bar;
    }

    public function head_short() {//Упрощенная версия BUILDER_return_page_head_tag() - только html-теги

        $this->uCore->uConf->define_site_root();
        /** @noinspection SpellCheckingInspection */
        return "<!DOCTYPE html>\n
		 <!--suppress HtmlRequiredLangAttribute -->
        <html>\n
		 <!--suppress HtmlRequiredTitleElement -->
        <head>\n
			 <base href='".u_sroot."'>\n
			 <meta charset=\"UTF-8\">\n
			 <link href='".u_sroot. 'default.css?' .v_timestamp."' rel='stylesheet' type='text/css'>\n
		 ";
    }
    public function incJs($path,$order=0) {
        if(!isset($this->uCore->inc_js_order_minus_1)) {
            $this->uCore->inc_js_order_minus_1 = [];
        }
        if(!isset($this->uCore->inc_js_order_0)) {
            $this->uCore->inc_js_order_0 = [];
        }
        if(!isset($this->uCore->inc_js_order_1)) {
            $this->uCore->inc_js_order_1 = [];
        }
        if(!isset($this->uCore->inc_js_order_2)) {
            $this->uCore->inc_js_order_2 = [];
        }

        if(!isset($this->uCore->inc_fileIsLoaded[$path])) {
            $this->uCore->inc_fileIsLoaded[$path]=1;
            if($order===-1) {
                $this->uCore->inc_js_order_minus_1[] = $path;
            }
            elseif($order===0) {
                $this->uCore->inc_js_order_0[] = $path;
            }
            elseif($order===1) {
                $this->uCore->inc_js_order_1[] = $path;
            }
            elseif($order===2) {
                $this->uCore->inc_js_order_2[] = $path;
            }
        }
    }
    public function incCss($path,$order=0) {
        if(!isset($this->uCore->inc_css_order_minus_1)) {
            $this->uCore->inc_css_order_minus_1 = [];
        }
        if(!isset($this->uCore->inc_css_order_0)) {
            $this->uCore->inc_css_order_0 = [];
        }
        if(!isset($this->uCore->inc_css_order_1)) {
            $this->uCore->inc_css_order_1 = [];
        }
        if(!isset($this->uCore->inc_css_order_2)) {
            $this->uCore->inc_css_order_2 = [];
        }

        if(!isset($this->uCore->inc_fileIsLoaded[$path])) {
            $this->uCore->inc_fileIsLoaded[$path]=1;
            if($order===-1) {
                $this->uCore->inc_css_order_minus_1[] = $path;
            }
            elseif($order===0) {
                $this->uCore->inc_css_order_0[] = $path;
            }
            elseif($order===1) {
                $this->uCore->inc_css_order_1[] = $path;
            }
            elseif($order===2) {
                $this->uCore->inc_css_order_2[] = $path;
            }
        }
    }
    public function printJs($src) {
        return '<script src="'.$src.'?'.v_timestamp.'" charset="UTF-8" type="text/javascript"></script>'."\n";
    }
    public function printCss($path) {
        if(!isset($this->uCore->inc_fileIsPrinted[$path])) {
            $this->uCore->inc_fileIsPrinted[$path]=1;
            return '<link href="' . $path . '?' . v_timestamp . '" rel="stylesheet" type="text/css" />' . "\n";
        }
        return '';
    }
    public function returnCss($order=0/*0 - выведет сначала обычные, потом важные, 1-только обычные, 2 - только важные*/) {//Возвращает все запомненные в $this->uFunc->incCss() css. Эту функцию нужно использовать внутри тега <head></head>
        $cnt='';
        if($order===-1) {
            $count=count($this->uCore->inc_css_order_minus_1);
            for ($i = 0; $i < $count; $i++) {
                $cnt .= $this->printCss($this->uCore->inc_css_order_minus_1[$i]);
            }
        }
        if($order===0) {
            $count=count($this->uCore->inc_css_order_0);
            for ($i = 0; $i < $count; $i++) {
                $cnt .= $this->printCss($this->uCore->inc_css_order_0[$i]);
            }
        }
        if($order===1) {
            $count = count($this->uCore->inc_css_order_1);
            for($i=0;$i<$count;$i++) {
                $cnt .= $this->printCss($this->uCore->inc_css_order_1[$i]);
            }
        }
        if($order===2) {
            $count = count($this->uCore->inc_css_order_2);
            for($i=0;$i<$count;$i++) {
                $cnt .= $this->printCss($this->uCore->inc_css_order_2[$i]);
            }
        }
        return $cnt;
    }
    public function returnJs() {
        $cnt='';
        $count=count($this->uCore->inc_js_order_minus_1);
        for($i=0;$i<$count;$i++) {
            $cnt .= $this->printJs($this->uCore->inc_js_order_minus_1[$i]);
        }
        $count=count($this->uCore->inc_js_order_0);
        for($i=0;$i<$count;$i++) {
            $cnt .= $this->printJs($this->uCore->inc_js_order_0[$i]);
        }
        $count=count($this->uCore->inc_js_order_1);
        for($i=0;$i<$count;$i++) {
            $cnt .= $this->printJs($this->uCore->inc_js_order_1[$i]);
        }
        $count=count($this->uCore->inc_js_order_2);
        for($i=0;$i<$count;$i++) {
            $cnt .= $this->printJs($this->uCore->inc_js_order_2[$i]);
        }
        return $cnt;
    }
    public function insert_callback_widget() {?>
        <div class="mad-callback">

            <!--Кнопка-->

            <div class="widget-wrapper mad-wgt common-window mad-wgt-index left-x black-one-color black-two-color opacity-true dark- selected-dynamic" style="position: fixed; top: auto;">
                <div class="widget-wrapper__center">
                    <div class="widget-content">
                        <h5 class="title-widget">Заказать звонок</h5>
                        <p class="text-widget">Мы позвоним<br>в рабочее время</p>
                        <div class="box-select-group" style="display: none;">
                            <div class="select-list">
                                <div class="select-item"><!--suppress HtmlFormInputWithoutLabel -->
                                    <select class="select-day-week">
                                        <option value="1528144200">сегодня</option>
                                        <option value="1528230600">завтра</option>
                                        <option value="1528317000">в среду</option>
                                    </select></div>
                                <div class="select-item"><!--suppress HtmlFormInputWithoutLabel -->
                                    <select class="select-time widget-start-time">
                                        <option value="1528077600">05:00 - 05:30</option>
                                        <option value="1528079400">05:30 - 06:00</option>
                                        <option value="1528081200">06:00 - 06:30</option>
                                        <option value="1528083000">06:30 - 07:00</option>
                                        <option value="1528084800">07:00 - 07:30</option>
                                        <option value="1528086600">07:30 - 08:00</option>
                                        <option value="1528088400">08:00 - 08:30</option>
                                        <option value="1528090200">08:30 - 09:00</option>
                                        <option value="1528092000">09:00 - 09:30</option>
                                        <option value="1528093800">09:30 - 10:00</option>
                                        <option value="1528095600">10:00 - 10:30</option>
                                        <option value="1528097400">10:30 - 11:00</option>
                                        <option value="1528099200">11:00 - 11:30</option>
                                        <option value="1528101000">11:30 - 12:00</option>
                                        <option value="1528102800">12:00 - 12:30</option>
                                        <option value="1528104600">12:30 - 13:00</option>
                                        <option value="1528106400">13:00 - 13:30</option>
                                        <option value="1528108200">13:30 - 14:00</option>
                                        <option value="1528110000">14:00 - 14:30</option>
                                        <option value="1528111800">14:30 - 15:00</option>
                                        <option value="1528113600">15:00 - 15:30</option>
                                        <option value="1528115400">15:30 - 16:00</option>
                                        <option value="1528117200">16:00 - 16:30</option>
                                        <option value="1528119000">16:30 - 17:00</option>
                                        <option value="1528120800">17:00 - 17:30</option>
                                        <option value="1528122600">17:30 - 18:00</option>
                                        <option value="1528124400">18:00 - 18:30</option>
                                        <option value="1528126200">18:30 - 19:00</option>
                                        <option value="1528128000">19:00 - 19:30</option>
                                        <option value="1528129800">19:30 - 20:00</option>
                                        <option value="1528131600">20:00 - 20:30</option>
                                        <option value="1528133400">20:30 - 21:00</option>
                                    </select></div>
                            </div>
                        </div>
                        <div class="box-phone-number">
                    <span class="box-phone-span-input ru-box-phone-span-input" data-content="<?=site_id==46?'+34':'+7'?>"><input
                                placeholder="(ххх) ххх-хх-хх" class="call-input" mask="(999) 999-99-99" restrict="reject"
                                clean="true" style="padding-left: 30px; height: 36px; width: 190px;"
                                data-padding="30"></span>
                            <div class="button-call button-widget">Позвоните мне</div>
                        </div>
                        <div class="text-policy-dynamic">Нажимая на кнопку "Заказать звонок", вы даете согласие c Политикой
                            обработки персональных данных
                        </div>
                    </div>
                    <div class="thanks-content">
                        <h5 class="title-widget">Спасибо,</h5>
                        <p class="text-widget">Спасибо! Заявку получили, сейчас позвоним.</p></div>
                    <div class="wait-content">
                        <div class="clock"></div>
                        <h5 class="title-widget">Подождите,</h5>
                        <p class="text-widget">Ваша заявка обрабатывается!</p></div>
                </div>
                <div class="close-popup" onclick="uLeadGen_callback.close_dg()" style="color:black;"><span class="icon-cancel"></span></div>


                <button class="button-widget-open" onclick="uLeadGen_callback.open_dg()"><span class="icon-phone-1"></span></button>
            </div>
            <div class="mad-overlay" style="display: none;"></div>






            <!--Мы перезвоним вам через в рабочее время. Линия-->

            <div class="widget-wrapper mad-wgt common-window mad-wgt-index   left-x black-one-color black-two-color  opacity-true  dark- selected-dynamic widget-show"
                 id="nonbusinesstime_widget"
                 style="position: fixed; top: auto; display: none;">
                <div class="widget-wrapper__center">
                    <div class="widget-content">
                        <h5 class="title-widget">Заказать звонок</h5>
                        <p class="text-widget">Мы позвоним<br>в рабочее время</p>
                        <div class="box-select-group" style="display: none;">
                            <div class="select-list">
                                <div class="select-item"><!--suppress HtmlFormInputWithoutLabel -->
                                    <select class="select-day-week">
                                        <option value="1528144200">сегодня</option>
                                        <option value="1528230600">завтра</option>
                                        <option value="1528317000">в среду</option>
                                    </select></div>
                                <div class="select-item"><!--suppress HtmlFormInputWithoutLabel -->
                                    <select class="select-time widget-start-time">
                                        <option value="1528077600">05:00 - 05:30</option>
                                        <option value="1528079400">05:30 - 06:00</option>
                                        <option value="1528081200">06:00 - 06:30</option>
                                        <option value="1528083000">06:30 - 07:00</option>
                                        <option value="1528084800">07:00 - 07:30</option>
                                        <option value="1528086600">07:30 - 08:00</option>
                                        <option value="1528088400">08:00 - 08:30</option>
                                        <option value="1528090200">08:30 - 09:00</option>
                                        <option value="1528092000">09:00 - 09:30</option>
                                        <option value="1528093800">09:30 - 10:00</option>
                                        <option value="1528095600">10:00 - 10:30</option>
                                        <option value="1528097400">10:30 - 11:00</option>
                                        <option value="1528099200">11:00 - 11:30</option>
                                        <option value="1528101000">11:30 - 12:00</option>
                                        <option value="1528102800">12:00 - 12:30</option>
                                        <option value="1528104600">12:30 - 13:00</option>
                                        <option value="1528106400">13:00 - 13:30</option>
                                        <option value="1528108200">13:30 - 14:00</option>
                                        <option value="1528110000">14:00 - 14:30</option>
                                        <option value="1528111800">14:30 - 15:00</option>
                                        <option value="1528113600">15:00 - 15:30</option>
                                        <option value="1528115400">15:30 - 16:00</option>
                                        <option value="1528117200">16:00 - 16:30</option>
                                        <option value="1528119000">16:30 - 17:00</option>
                                        <option value="1528120800">17:00 - 17:30</option>
                                        <option value="1528122600">17:30 - 18:00</option>
                                        <option value="1528124400">18:00 - 18:30</option>
                                        <option value="1528126200">18:30 - 19:00</option>
                                        <option value="1528128000">19:00 - 19:30</option>
                                        <option value="1528129800">19:30 - 20:00</option>
                                        <option value="1528131600">20:00 - 20:30</option>
                                        <option value="1528133400">20:30 - 21:00</option>
                                    </select></div>
                            </div>
                        </div>
                        <div class="box-phone-number">
                    <span class="box-phone-span-input ru-box-phone-span-input" data-content="<?=site_id==46?'+34':'+7'?>"><input
                                placeholder="(ххх) ххх-хх-хх" class="call-input" mask="(999) 999-99-99" restrict="reject"
                                clean="true" style="padding-left: 30px; height: 35px; width: 190px;"
                                data-padding="30"></span>
                            <div class="button-call button-widget">Позвоните мне</div>
                        </div>
                        <div class="text-policy-dynamic">Нажимая на кнопку "Заказать звонок", вы даете согласие c Политикой
                            обработки персональных данных
                        </div>
                    </div>
                    <div class="thanks-content">
                        <h5 class="title-widget">Спасибо,</h5>
                        <p class="text-widget">Спасибо! Заявку получили, сейчас позвоним.</p></div>
                    <div class="wait-content">
                        <div class="clock"></div>
                        <h5 class="title-widget">Подождите,</h5>
                        <p class="text-widget">Ваша заявка обрабатывается!</p></div>
                </div>
                <div class="close-popup"  onclick="uLeadGen_callback.close_dg()"  style="color:black;"><span class="icon-cancel"></span></div>
                <button class="button-widget-open" onclick="uLeadGen_callback.open_dg()"></button>
            </div>






            <!--Мы перезвоним вам через 30 секунд. Линия-->


            <div class="widget-wrapper mad-wgt common-window mad-wgt-index   left-x black-one-color black-two-color  opacity-true  dark- selected-dynamic widget-show" style="position: fixed; display: none; /*bottom: auto;*/"
                 id="callback_window_business_time">
                <div class="widget-wrapper__center">

                    <div class="widget-content">
                        <h5 class="title-widget">Заказать звонок</h5>
                        <p class="text-widget"><?=$this->getConf('call me back widget text', 'content')?></p>
                        <div class="box-phone-number">
                    <span class="box-phone-span-input ru-box-phone-span-input" data-content="<?=site_id==46?'+34':'+7'?>"><input
                                placeholder="(ххх) ххх-хх-хх" class="call-input" mask="(999) 999-99-99" restrict="reject"
                                clean="true" style="padding-left: 30px; height: 35px; width: 190px;"
                                data-padding="30" id="callback_window_business_time_input"></span>
                            <div class="button-call button-widget" onclick="uLeadGen_callback.callback_now('callback_window_business_time');"><?=(site_id!=46?'Позвоните мне':'Call me')?></div>
                        </div>
                        <?=(site_id!=46?'<div class="text-policy-dynamic">Нажимая на кнопку "Позвоните мне", вы даете согласие c Политикой
                            обработки персональных данных
                        </div>':'')?>
                    </div>

                    <div class="thanks-content">
                        <h5 class="title-widget">Спасибо,</h5>
                        <p class="text-widget"><?=(site_id!=46?'Спасибо! Заявку получили, сейчас позвоним.':'Thank you. We\'ll call you back')?></p></div>

                    <div class="wait-content">
                        <div class="clock"></div>
                        <h5 class="title-widget">Подождите,</h5>
                        <p class="text-widget">Ваша заявка обрабатывается!</p></div>
                </div>

                <div class="close-popup" onclick="uLeadGen_callback.close_dg()" style="color:black;"><span class="icon-cancel"></span></div>
                <button class="button-widget-open" onclick="uLeadGen_callback.open_dg()"></button>
            </div>



            <!--Мы перезвоним вам через в рабочее время. Окно-->


            <div id="callback_window" style="display:none;">
                <div class="mad-overlay" style="display: block; opacity: 0.5; background: black; width: 100%; height: 100%; position: fixed; top:0; left: 0; z-index: 1250;"></div>
                <div class="widget-wrapper mad-wgt common-window fast-callback radius mad-wgt-index dark- opacity-true black-one-color black-two-color widget-show">
                    <div class="widget-wrapper__center">
                        <div class="widget-content">
                            <h5 class="title-widget">Всего 25 секунд!</h5>
                            <p class="text-widget">и наш консультант перезвонит вам</p>
                            <div class="box-phone-number">
                    <span class="box-phone-span-input ru-box-phone-span-input" data-content="<?=site_id==46?'+34':'+7'?>"><input
                                placeholder="(ххх) ххх-хх-хх" class="call-input" mask="(999) 999-99-99" restrict="reject"
                                clean="true" style="padding-left: 30px; width: 190px;" data-padding="30"></span>
                                <div class="button-call button-widget"><span class="icon-phone-1"></span></div>
                            </div>
                            <div class="timer">
                                <span class="clock-icon"></span>&nbsp;<span class="clock-timer">00:25.00</span>
                            </div>
                        </div>
                        <div class="timer-content">
                            <h5 class="title-widget">Спасибо,</h5>
                            <p class="text-widget">Мы вам уже звоним</p>
                            <div class="timer">
                                <span class="clock-icon"></span>&nbsp;<span class="clock-timer">00:25.00</span>
                            </div>
                        </div>
                        <div class="text-policy">Нажимая на кнопку "Заказать звонок", вы даете согласие c Политикой обработки
                            персональных данных
                        </div>
                    </div>
                    <div class="close-popup" onclick="uLeadGen_callback.close_dg()"><span class="icon-cancel"></span></div>
                </div>
            </div>
        </div>
    <?}

    //HELPERS
    public static function journal($data/*Текст ошибки. Данные для записи в журнал*/, $journal/*Имя файла журнала*/) { //Функция записи журнала
        if(!file_exists('journals') && !mkdir('journals') && !is_dir('journals')) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', 'journals'));
        }
        @file_put_contents('journals/' .$journal.'.html',"\n".date('d.m.Y, H:i:s').'. User: '.$_SESSION['SESSION']['user_id'].'. Request_URI:'.$_SERVER['REQUEST_URI'].'. site_id:'.site_id.' IP:'.$_SERVER['REMOTE_ADDR'].'. Cookie_sesId:'.$_COOKIE['ses_id'].'.  Cookie_user_id:'.$_COOKIE['user_id'].'. Info:'.$data,FILE_APPEND);
    }
    public function error($key,$no_html=0) {//Функция для вывода ошибки.
        //Выводит сообщение об ошибке
        //Записывает лог ошибок на сервере
        //Все серьезные ошибки, с которыми не может разобраться пользователь сам, нужно выводить через эту функцию. Потом сможем посмотреть журнал.
        self::journal($key,'builder_error');
        if(!$no_html) {
            echo $this->head_short();
            echo '</head>
             <body><div class="staticContent">
                 <h1>'.$this->text('Error').' '.$this->uCore->mod.'/'.$this->uCore->page_name.'/#'.$key.'</h1>
                 <p>'.$this->text('Error occurred - text').'
             </div>
             </body>
             </html>';
            die();
        }

        $txt=$this->text('Error occurred - text').' (#'.$this->uCore->mod.'/'.$this->uCore->page_name.'/#'.$key.')';
        if($no_html) {
            $txt = strip_tags($txt);
        }
        echo $txt;
        exit;
        /** @noinspection PhpUnreachableStatementInspection */
        return 0;
    }
    public function pdo($db) {
        if(isset($this->pdo_handler[$db])) {
            return $this->pdo_handler[$db];
        }

        $dbname='madmakers_'.$db;
        $db_host=db_host;

        try {
            $handler=new PDO("mysql:host=$db_host;dbname=$dbname;charset=utf8", $this->uCore->uConf->sql['user'], $this->uCore->uConf->sql['pass']);
            // set the PDO error mode to exception
            $handler->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch(PDOException $e){
            $this->error('1586309347'.$e->getMessage(),1);
        }

        $this->pdo_handler[$db]=&$handler;

        return $handler;
    }
    public static function genPass() {
        mt_srand(time());
        $hash=  \uFunc::genHash();
        $pass= '';
        for($i=0;$i<10;$i++) {
            if(mt_rand(0,2)) {
                $pass .= strtolower($hash[$i]);
            }
            else {
                $pass .= strtoupper($hash[$i]);
            }
        }
        return $pass;
    }
    public static function passCrypt($pass, $reg_timestamp, $user_email, $user_id, $user_phone) {
        if(!defined(CRYPT_BLOWFISH)) {
            define(CRYPT_BLOWFISH, 1);
        }
        $salt = hash('sha512',$user_phone.$user_id.$reg_timestamp);
        $pass=crypt($pass,$salt);
        $pass=hash('gost', $pass.$salt).md5($user_email);
        return $pass;
    }
    public function genHash() {
        mt_srand(time());
        return md5(mt_rand(0,time())*time());
    }
    public static function genCode($length=6) {
        $arr = array('1', '2', '3', '4', '5','6', '7', '8', '9', '0');
        // Генерируем пароль
        $pass = '';
        for ($i = 0; $i < $length; $i++) {
            // Вычисляем случайный индекс массива
            $index = mt_rand(0, count($arr) - 1);
            $pass .= $arr[$index];
        }
        return $pass;
    }
    public static function ext2mime($ext) {
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
        if(isset($mime_types[$ext])) {
            return $mime_types[$ext];
        }
        return false;
    }
    public function glyphicons_ar() {
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
    public static function POST($url, $data, $referer='') {
        $data = http_build_query($data);// Convert the data array into URL Parameters like a=b&foo=bar etc.
        $url = parse_url($url);// parse the given URL
        $host = $url['host'];// extract host and path:
        $path = $url['path'];
        if ($url['scheme'] !== 'http') {
            $fp = fsockopen('ssl://' .$host, 443, $errno, $errstr, 30);// open a socket connection on port 80 - timeout: 30 sec
        }
        else {
            $fp = fsockopen($host, 80, $errno, $errstr, 30);// open a socket connection on port 80 - timeout: 30 sec
        }
        if ($fp){// send the request headers:
            fwrite($fp, "POST $path HTTP/1.1\r\n");
            fwrite($fp, "Host: $host\r\n");
            if ($referer !== '') {
                fwrite($fp, "Referer: $referer\r\n");
            }
            fwrite($fp, "Content-type: application/x-www-form-urlencoded\r\n");
            fwrite($fp, 'Content-length: ' . strlen($data) ."\r\n");
            fwrite($fp, "Connection: close\r\n\r\n");
            fwrite($fp, $data);
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
    private $web_colors  =array(
        '800000',// "Maroon",
        '8B0000',// "DarkRed",
        'B22222',// "FireBrick",
        'FF0000',// "Red",
        'FA8072',// "Salmon",
        'FF6347',// "Tomato",
        'FF7F50',// "Coral",
        'FF4500',// "OrangeRed",
        'D2691E',// "Chocolate",
        'F4A460',// "SandyBrown",
        'FF8C00',// "DarkOrange",
        'FFA500',// "Orange",
        'B8860B',// "DarkGoldenrod",
        'DAA520',// "Goldenrod",
        'FFD700',// "Gold",
        '808000',// "Olive",
        'FFFF00',// "Yellow",
        '9ACD32',// "YellowGreen",
        'ADFF2F',// "GreenYellow",
        '7FFF00',// "Chartreuse",
        '7CFC00',// "LawnGreen",
        '008000',// "Green",
        '00FF00',// "Lime",
        '32CD32',// "LimeGreen",
        '00FF7F',// "SpringGreen",
        '00FA9A',// "MediumSpringGreen",
        '40E0D0',// "Turquoise",
        '20B2AA',// "LightSeaGreen",
        '48D1CC',// "MediumTurquoise",
        '008080',// "Teal",
        '008B8B',// "DarkCyan",
        '00FFFF',// "Aqua",
        '00FFFF',// "Cyan",
        '00CED1',// "DarkTurquoise",
        '00BFFF',// "DeepSkyBlue",
        '1E90FF',// "DodgerBlue",
        '4169E1',// "RoyalBlue",
        '000080',// "Navy",
        '00008B',// "DarkBlue",
        '0000CD',// "MediumBlue",
        '0000FF',// "Blue",
        '8A2BE2',// "BlueViolet",
        '9932CC',// "DarkOrchid",
        '9400D3',// "DarkViolet",
        '800080',// "Purple",
        '8B008B',// "DarkMagenta",
        'FF00FF',// "Fuchsia",
        'FF00FF',// "Magenta",
        'C71585',// "MediumVioletRed",
        'FF1493',// "DeepPink",
        'FF69B4',// "HotPink",
        'DC143C',// "Crimson",
        'A52A2A',// "Brown",
        'CD5C5C',// "IndianRed",
        'BC8F8F',// "RosyBrown",
        'F08080',// "LightCoral",
        'FFFAFA',// "Snow",
        'FFE4E1',// "MistyRose",
        'E9967A',// "DarkSalmon",
        'FFA07A',// "LightSalmon",
        'A0522D',// "Sienna",
        'FFF5EE',// "SeaShell",
        '8B4513',// "SaddleBrown",
        'FFDAB9',// "Peachpuff",
        'CD853F',// "Peru",
        'FAF0E6',// "Linen",
        'FFE4C4',// "Bisque",
        'DEB887',// "Burlywood",
        'D2B48C',// "Tan",
        'FAEBD7',// "AntiqueWhite",
        'FFDEAD',// "NavajoWhite",
        'FFEBCD',// "BlanchedAlmond",
        'FFEFD5',// "PapayaWhip",
        'FFE4B5',// "Moccasin",
        'F5DEB3',// "Wheat",
        'FDF5E6',// "Oldlace",
        'FFFAF0',// "FloralWhite",
        'FFF8DC',// "Cornsilk",
        'F0E68C',// "Khaki",
        'FFFACD',// "LemonChiffon",
        'EEE8AA',// "PaleGoldenrod",
        'BDB76B',// "DarkKhaki",
        'F5F5DC',// "Beige",
        'FAFAD2',// "LightGoldenrodYellow",
        'FFFFE0',// "LightYellow",
        'FFFFF0',// "Ivory",
        '6B8E23',// "OliveDrab",
        '556B2F',// "DarkOliveGreen",
        '8FBC8F',// "DarkSeaGreen",
        '006400',// "DarkGreen",
        '228B22',// "ForestGreen",
        '90EE90',// "LightGreen",
        '98FB98',// "PaleGreen",
        'F0FFF0',// "Honeydew",
        '2E8B57',// "SeaGreen",
        '3CB371',// "MediumSeaGreen",
        'F5FFFA',// "Mintcream",
        '66CDAA',// "MediumAquamarine",
        '7FFFD4',// "Aquamarine",
        '2F4F4F',// "DarkSlateGray",
        'AFEEEE',// "PaleTurquoise",
        'E0FFFF',// "LightCyan",
        'F0FFFF',// "Azure",
        '5F9EA0',// "CadetBlue",
        'B0E0E6',// "PowderBlue",
        'ADD8E6',// "LightBlue",
        '87CEEB',// "SkyBlue",
        '87CEFA',// "LightskyBlue",
        '4682B4',// "SteelBlue",
        'F0F8FF',// "AliceBlue",
        '708090',// "SlateGray",
        '778899',// "LightSlateGray",
        'B0C4DE',// "LightsteelBlue",
        '6495ED',// "CornflowerBlue",
        'E6E6FA',// "Lavender",
        'F8F8FF',// "GhostWhite",
        '191970',// "MidnightBlue",
        '6A5ACD',// "SlateBlue",
        '483D8B',// "DarkSlateBlue",
        '7B68EE',// "MediumSlateBlue",
        '9370DB',// "MediumPurple",
        '4B0082',// "Indigo",
        'BA55D3',// "MediumOrchid",
        'DDA0DD',// "Plum",
        'EE82EE',// "Violet",
        'D8BFD8',// "Thistle",
        'DA70D6',// "Orchid",
        'FFF0F5',// "LavenderBlush",
        'DB7093',// "PaleVioletRed",
        'FFC0CB',// "Pink",
        'FFB6C1',// "LightPink",
        '000000',// "Black",
        '696969',// "DimGray",
        '808080',// "Gray",
        'A9A9A9',// "DarkGray",
        'C0C0C0',// "Silver",
        'D3D3D3',// "LightGrey",
        'DCDCDC',// "Gainsboro",
        'F5F5F5',// "WhiteSmoke",
        'FFFFFF'// "White"
);
    public function getColor($num) {
        if(isset($this->web_colors[$num])) {
            return $this->web_colors[$num];
        }
        return '';
    }

    //CONF
    public function getConf ($field,$mod,$tolerant=false/*Выдавать ошибку, если не найдено или нет*/,$site_id=site_id) {
        if(!isset($this->conf[$site_id][$mod][$field])) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */$stm=$this->pdo('pages')->prepare('SELECT value FROM u235_conf WHERE field=:field AND `mod`=:mod AND site_id=:site_id');
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field',   $field,     PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':mod',     $mod,       PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,   PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->error('proc uFunc 40'/*.$e->getMessage()*/);}

            /** @noinspection PhpUndefinedVariableInspection *//** @noinspection PhpUndefinedMethodInspection */
            if($result=$stm->fetch(PDO::FETCH_OBJ)) {
                $this->conf[$site_id][$mod][$field]=uString::sql2text($result->value,true);
            }
            else {
                self::journal($field,'getconf');
                if(!$tolerant) /** @noinspection PhpUndefinedMethodInspection */ {
                    $this->uCore->error('proc uFunc 50' . $field);
                }
                else {
                    return '';
                }
            }
        }
        return $this->conf[$site_id][$mod][$field];
    }
    public function setConf ($value,$field,$mod,$pdo_data_type,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */$stm=$this->pdo('pages')->prepare('UPDATE u235_conf SET value=:value WHERE field=:field AND `mod`=:mod AND site_id=:site_id');
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':value',   $value,     $pdo_data_type);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field',   $field,     PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':mod',     $mod,       PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,   PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->error('proc uFunc 60'/*.$e->getMessage()*/);}
    }

    //MESSAGING
    public function sendMail($emailHtmlContent, $subject, $recipient, $senderName=site_name, $senderEmail=site_email, $u_sroot=u_sroot, $site_id=site_id, $reply_line='') {
        $logo_url=$this->getConf('email_logo_url','content','return false',$site_id);

        $css=file_get_contents('processors/includes/mail.min.css');

        $emailHtmlContent="<!DOCTYPE html>\n
            <!--suppress HtmlRequiredLangAttribute -->
            <html>\n
            <!--suppress HtmlRequiredTitleElement -->
            <head>\n
            <style>".$css. '</style>
            </head>
            <body>' .
            $reply_line.
            "<a href='".$u_sroot."'><img alt='' src='".$logo_url."'></a>
            <!--content-->".
            $emailHtmlContent.
            '<!----content-->
            </body>
            </html>';
        //--Вставка страницы в оправку

        $emogrifier = new Emogrifier();

        $emogrifier->setHtml($emailHtmlContent);
        $emogrifier->setCss($css);

        $mergedHtml = $emogrifier->emogrify();

        $pos = strpos($_SERVER['HTTP_HOST'], '.local');
        if ($pos) {
            include 'processors/inc/testmail.php';
        }

        $useMadsmtp=(int)$this->getConf('use_madsmtp','content',false,$site_id);

        //WITH MAD SMTP
        if($useMadsmtp) {
            $madsmtpTokenId=(int)$this->getConf('madsmtp token id','content',false,$site_id);
            $madsmtpToken=$this->getConf('madsmtp token','content',false,$site_id);

            $senderName= str_replace(array('<', '>'), '', $senderName);


            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => madsmtp_protocol . '://' . madsmtp_host_backend . ':' . madsmtp_port,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => http_build_query(array(
                    'task' =>'sendMail',
                    'id'=>$madsmtpTokenId,
                    'token'=>$madsmtpToken,
                    'content' =>$emailHtmlContent,
                    'subject' =>$subject,
                    'recipient' =>$recipient,
                    'senderName' =>$senderName
                )),
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/x-www-form-urlencoded"
                ),
            ));

            $response = curl_exec($curl);

            if(!$response) {
                return false;
            }
            $res= json_decode($response, true);
            return $res['status'] === 'done';
        }

        //WITH SITE'S SERVER
        $email = new fEmail();
        $email->addRecipient($recipient);
        $email->setFromEmail($senderEmail);
        $email->setSubject($subject);
        $email->setHTMLBody($mergedHtml);
        $email->setBody(uString::removeHTML($mergedHtml));


        try {
            $email->send();
            return true;
        }
        catch (fValidationException $e) {
            return false;
        }
    }
    public function sendSms($text, $recipient_phone, $site_id=site_id) {
        $pos = strpos($_SERVER['HTTP_HOST'], '.local');
        if ($pos) {
            include 'processors/inc/testmail.php';
        }

        $use_MadSMS=(int)$this->getConf('use MAD SMS to send SMS','content',false,$site_id);

        //WITH MAD SMS
        if($use_MadSMS) {
            $madsmsTokenId=(int)$this->getConf('ID of MAD SMS token','content',false,$site_id);
            $madsmsToken=$this->getConf('MAD SMS token','content',false,$site_id);

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL,madsms_protocol.'://'.madsms_host.':'.madsms_port);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode(array(
                'task' =>'sendSMS',
                'token_id'=>$madsmsTokenId,
                'token'=>$madsmsToken,
                'text' =>$text,
                'recipient_phone' =>$recipient_phone
            )));

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            if(!$server_output = curl_exec($ch)) {
                curl_close ($ch);
                return false;
            }
            curl_close ($ch);
            $res= json_decode($server_output, true);

            return $res['status'] === 'done';
        }

        return false;
    }
    public function mail($html, $title, $to_email, $from_txt='', $from_email='', $u_sroot=u_sroot, $site_id=site_id, $reply_line='', $use_smtp=false, $smtp_settings=array()) {
//        if($_SERVER['SERVER_NAME']=='localhost') return false;
        //smtp_settings:
        //port
        //server_name
        //use_ssl
        //user_name
        //password

        $logo_url=$this->getConf('email_logo_url','content','return false',$site_id);
        //if(!$logo_url) $logo_url=$this->getConf('email_logo_url','content','return false',);

        if($from_txt==='') {
            $from_txt = site_name;
        }
        if($from_email==='') {
            $from_email = site_email;
        }
        if($reply_line==='') {
            $reply_line = false;
        }

        $css=file_get_contents('processors/includes/mail.min.css');

        $html="<!DOCTYPE html>\n
            <!--suppress HtmlRequiredLangAttribute -->
            <html>\n
            <!--suppress HtmlRequiredTitleElement -->
            <head>\n
            <style>".$css. '</style>
            </head>
            <body>' .
            ($reply_line?:'').
            "<a href='".$u_sroot."'><img alt='' src='".$logo_url."'></a>
            <!--content-->".
            $html.
            '<!----content-->
            </body>
            </html>';
        //--Вставка страницы в оправку

        $emogrifier = new Emogrifier();

        $emogrifier->setHtml($html);
        $emogrifier->setCss($css);

        $mergedHtml = $emogrifier->emogrify();

        $pos = strpos($_SERVER['HTTP_HOST'], '.local');
        if ($pos) {
            include 'processors/classes/testmail.php';
        }

        if($from_email===$to_email&&
            ($this->uCore->mod !== 'uForms'&&$this->uCore->page_name !== 'form_rec_save_bg')&&
            ($this->uCore->mod !== 'uCat'&&$this->uCore->page_name !== 'buy_form_send')
        ) {
            self::journal('attempt to send msg to myself:'.$from_email,'sendMail_err');
        }
        else {

//            $from_txt=str_replace('"','',$from_txt);
            $from_txt=str_replace('<','',$from_txt);
            $from_txt=str_replace('>','',$from_txt);

            if($use_smtp) {
                if($smtp_settings['port']==='0') {
                    if($smtp_settings['use_ssl']) {
                        $smtp_settings['port'] = 465;
                    }
                    else {
                        $smtp_settings['port'] = 25;
                    }
                }
                try {
                    $smtp = new fSMTP($smtp_settings['server_name'], $smtp_settings['port'], $smtp_settings['use_ssl']);
                } catch (fEnvironmentException $e) {
                    return 0;
                }
                try {
                    $smtp->authenticate($smtp_settings['user_name'], $smtp_settings['password']);
                } catch (fValidationException $e) {
                    return 0;
                }
            }
            $email = new fEmail();
            $email->addRecipient($to_email);
            $email->setFromEmail($from_email);
            $email->setSubject($title);
            $email->setHTMLBody($mergedHtml);
            $email->setBody(uString::removeHTML($mergedHtml));
            if($use_smtp) {
                try {
                    /** @noinspection PhpUndefinedVariableInspection */
                    $email->send($smtp);
                } catch (fValidationException $e) {
                    return 0;
                }
                $smtp->close();
            }
            else {
                try {
                    $email->send();
                } catch (fValidationException $e) {
                    return 0;
                }
            }
        }

        self::journal('<div>
        <p><b>title:</b><br>'.$title.'</p>
        <p><b>to_email:</b><br>'.$to_email.'</p>
        <p><b>from_txt:</b><br>'.$from_txt.'</p>
        <p><b>from_email:</b><br>'.$from_email.'</p>
        <p><b>u_sroot:</b><br>'.$u_sroot.'</p>
        <p><b>site_id:</b><br>'.$site_id.'</p>
        <p><b>reply_line:</b><br>'.$reply_line.'</p>
        <p><b>use_smtp:</b><br>'.$use_smtp.'</p>
        <p><b>html:</b><br>'.$html.'</p>
        </div>','sendMail.log');

        return true;
    }

    //MODULES
    private $mod_installed_ar;
    public function mod_installed($mod_name,$site_id=site_id) {
        if(!isset($this->mod_installed_ar[$site_id][$mod_name])) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->pdo('common')->prepare('SELECT mod_id FROM u235_sites_modules WHERE mod_name=:mod_name AND site_id=:site_id');
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':mod_name', $mod_name,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->error('proc uFunc 70');}

            /** @noinspection PhpUndefinedVariableInspection */
            /** @noinspection PhpUndefinedMethodInspection */
            if($stm->fetch(PDO::FETCH_OBJ)) {
                $this->mod_installed_ar[$site_id][$mod_name] = true;
            }
            else {
                $this->mod_installed_ar[$site_id][$mod_name] = false;
            }
        }
        return $this->mod_installed_ar[$site_id][$mod_name];
    }

    //TEXT
    public function getStatic ($page_name) {try {
        /** @noinspection PhpUndefinedMethodInspection */
        $stm=$this->pdo('pages')->prepare('SELECT
             *
             FROM
             u235_pages_html
             WHERE
             site_id=:site_id AND
             page_name=:page_name
             ');
        $site_id=site_id;
        /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
        /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_name', $page_name,PDO::PARAM_STR);
        /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
    }
    catch(PDOException $e) {$this->error('proc uFunc 75'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        $text=$stm->fetch(PDO::FETCH_ASSOC);
        if(isset($text['page_text'])) {
            $text['page_text'] = uString::sql2text($text['page_text'], 1);
        }

        return $text;
    }
    public function getStatic_text ($page_name) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->pdo('pages')->prepare('SELECT
             page_text
             FROM
             u235_pages_html
             WHERE
            site_id=:site_id AND
             page_name=:page_name
             ');
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_name', $page_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->error('proc uFunc 80'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$page_text=$stm->fetch(PDO::FETCH_OBJ)) {
            return $page_name;
        }
        return uString::sql2text($page_text->page_text,1);
    }
    public function getStatic_by_id ($page_id) {
        if(!uString::isDigits($page_id)) {
            return '';
        }
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->pdo('pages')->prepare('SELECT
             *
             FROM
             u235_pages_html
             WHERE
             site_id=:site_id AND
             page_id=:page_id
             ');
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->error('proc uFunc 90'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        if($data=$stm->fetch(PDO::FETCH_OBJ)) {
            $data->page_text = uString::sql2text($data->page_text, true);
        }
        return $data;
    }
    public function getStatic_text_by_id ($page_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->pdo('pages')->prepare('SELECT 
            page_text
            FROM 
            u235_pages_html 
            WHERE 
            page_id=:page_id AND
            site_id=:site_id
            ');
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            $page=$stm->fetch(PDO::FETCH_OBJ);
            if(!$page) {
                return '';
            }
            return uString::sql2text($page->page_text,true);
        }
        catch(PDOException $e) {$this->error('proc uFunc 100'/*.$e->getMessage()*/);}
        return '';
    }
    public function getStatic_data_by_id ($page_id, $query= 'page_id', $site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->pdo('pages')->prepare('SELECT 
            ' .$query. '
            FROM 
            u235_pages_html 
            WHERE 
            page_id=:page_id AND
            site_id=:site_id
            ');
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->error('proc uFunc 110'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedMethodInspection PhpUndefinedVariableInspection */
        return $stm->fetch(PDO::FETCH_OBJ);
    }

    //uAUTH
    public function insAuthDialog() {
        ob_start();
        $this->uSes->get_val('user_id');
        include_once 'uAuth/dialogs/login.php';
        return ob_get_clean();
    }
    public function get_uAuth_usersinfo_fields() {
        $fields=array();
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->pdo('uAuth')->prepare('SELECT
            field_id,
            label,
            visible,
            editable,
            field_type
            FROM
            u235_usersinfo_site_labels
            WHERE
            site_id=:site_id
            ORDER BY 
            sort
            ');
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            for($i=0; $qr=$stm->fetch(PDO::FETCH_ASSOC); $i++) {
                $fields[$i]=$qr;
            }
        }
        catch(PDOException $e) {$this->error('proc uFunc 120'/*.$e->getMessage()*/);}

        return $fields;
    }
    public function uAuth_usersinfo_field_id2title($field_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->pdo('uAuth')->prepare('SELECT
            label
            FROM
            u235_usersinfo_site_labels
            WHERE
            site_id=:site_id AND
            field_id=:field_id
            ');
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_id', $field_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ)->label;
        }
        catch(PDOException $e) {$this->error('proc uFunc 130'/*.$e->getMessage()*/);}
        return 0;
    }
    public function uAuth_usersinfo_field_id2val($field_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->pdo('uAuth')->prepare('SELECT
            field_' .$field_id. '
            FROM
            u235_usersinfo
            WHERE
            site_id=:site_id AND
            user_id=:user_id
            ');
            $site_id=site_id;
            $user_id=$this->uSes->get_val('user_id');
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            $qr=$stm->fetch(PDO::FETCH_ASSOC);
            return $qr['field_'.$field_id];
        }
        catch(PDOException $e) {$this->error('proc uFunc 140'/*.$e->getMessage()*/);}
        return 0;
    }
    public function uAuth_users_field2val($field) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->pdo('uAuth')->prepare('SELECT DISTINCT
                ' .$field. '
                FROM
                u235_users
                JOIN
                u235_usersinfo
                ON
                u235_usersinfo.user_id=u235_users.user_id
                WHERE
                u235_users.user_id=:user_id AND
                u235_usersinfo.site_id=:site_id
                ');
            $site_id=site_id;
            $user_id=$this->uSes->get_val('user_id');
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e) {$this->error('proc uFunc 150'/*.$e->getMessage()*/);}
        return 0;
    }

    //uNavi
    public function uMenu_list() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->pdo('uNavi')->prepare("SELECT DISTINCT
            u235_cats.cat_id,
            u235_cats.cat_title,
            u235_menu.id,
            u235_menu.indent,
            u235_menu.title
            FROM
            u235_cats
            JOIN 
            u235_menu
            ON 
            u235_menu.cat_id=u235_cats.cat_id AND
            u235_menu.site_id=u235_cats.site_id
            WHERE
            (u235_cats.status='' OR  u235_cats.status IS NULL) AND
            (u235_menu.status='' OR  u235_menu.status IS NULL) AND
            u235_cats.site_id=:site_id
            ORDER BY 
            u235_cats.cat_title,
            u235_menu.position,
            u235_menu.title
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            for($i=0; $menu_list[$i]=$stm->fetch(PDO::FETCH_OBJ); $i++) {
                continue;
            }
            return $menu_list;
        }
        catch(PDOException $e) {$this->error('proc uFunc 160'/*.$e->getMessage()*/);}
        return 0;
    }

    //uBlocks
    public function insertHtmlBlock($pos) {
        if(!isset($this->q_uBlocks)) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->pdo('pages')->prepare('SELECT DISTINCT
                u235_ublocks_list.block_id,
                u235_ublocks_list.block_html,
                u235_ublocks_list.block_pos
                FROM
                u235_ublocks_pages
                JOIN 
                u235_ublocks_list
                ON
                u235_ublocks_list.block_id=u235_ublocks_pages.block_id AND
                u235_ublocks_list.site_id=u235_ublocks_pages.site_id
                WHERE
                u235_ublocks_pages.site_id=:site_id AND
                u235_ublocks_pages.page_id=:page_id
                ORDER BY 
                u235_ublocks_list.block_title
                ');
                $site_id=site_id;
                $page_id=$this->uCore->page['page_id'];
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                $html='<div id="uBlocks_pos_'.$pos.'">';
                /** @noinspection PhpUndefinedMethodInspection */
                while($data=$stm->fetch(PDO::FETCH_ASSOC)) {
                    if($data['block_pos']==$pos) {
                        $html.='<div class="uBlocks_block">
                    '.uString::sql2text($data['block_html'],true).'
                    </div>';
                    }
                }
                $html.='</div>';
                return $html;
            }
            catch(PDOException $e) {$this->error('proc uFunc 170'/*.$e->getMessage()*/);}
        }
        return 0;
    }

    //SESSION
    public function sesHack($data= '') {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->pdo('uSes')->prepare('SELECT
            hack_id
            FROM
            u235_hacks
            ORDER BY
            hack_id DESC
            LIMIT 1;
            ');
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->error('proc uFunc 220'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
            $id = $qr->hack_id + 1;
        }
        else {
            $id = 1;
        }

        mt_srand(time());
        $hash=md5(mt_rand(0,time())*time());

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->pdo('uSes')->prepare('INSERT INTO
            u235_hacks (
            hack_id,
            hack_value,
            sesId,
            `time`,
            `data`,
            page_id
            ) VALUES (
            :hack_id,
            :hack_value,
            :sesId,
            :time,
            :data,
            :page_id
            )');
            $sesId=$this->uSes->get_val('sesId');
            $time=time();
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':hack_id', $id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':time', $time,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->uCore->page['page_id'],PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':hack_value', $hash,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sesId', $sesId,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':data', $data,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->error('proc uFunc 230'/*.$e->getMessage()*/);}

        $values['id']=$id;
        $values['hash']=$hash;

        return $values;
    }
    public function sesHack_test($id,$hash,$page_access=0) {//Обязательно нужно передать id хак-сессии, hash хак-сессии и access для страницы, которая проверяет хак-сессию. У страниц, использующих хак-сессию, права в БД должны стоять 0. Потому что эти страницы не могут проверять кукисы пользователей. Обычно это страницы, которые вызываются флешем.
        //Удаляем все старые хак-сессии
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->pdo('uSes')->prepare('DELETE FROM 
            u235_hacks 
            WHERE 
            `time`<:time
            ');
            $time=time()-ses_hack_lifetime;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':time', $time,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->error('proc uFunc 240'/*.$e->getMessage()*/);}

        //Проверяем данные, переданные в функцию
        if(!uString::isDigits($id)) {
            return false;
        }
        if(!uString::isHash($hash)) {
            return false;
        }
        //Достаем из БД запрошенную хак-сессию
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->pdo('uSes')->prepare('SELECT
              user_id,
              u235_list.sesId,
              u235_list.id,
              data,
              page_id
              FROM
              u235_hacks
              JOIN 
              u235_list
              ON
              u235_list.sesId=u235_hacks.sesId
              WHERE
              hack_id=:hack_id AND
              hack_value=:hack_value AND
              u235_hacks.time>:time AND
              REMOTE_ADDR=:REMOTE_ADDR
              ');
            $time=time()-ses_hack_lifetime;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':hack_id', $id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':time', $time,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':hack_value', $hash,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':REMOTE_ADDR', $_SERVER['REMOTE_ADDR'],PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->error('proc uFunc 250'/*.$e->getMessage()*/);}

        //Если хак-сесия найдена, то продолжаем
        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        if($data=$stm->fetch(PDO::FETCH_ASSOC)) {
            //Записываем данные из БД в переменные
            unset($_SESSION['SESSION']['ses_id']);
            $this->uSes->set_val('sesId',$data['sesId']);//Мы знаем id обычной сессии
            $this->uSes->set_val('user_id',(int)$data['user_id']);//Теперь у нас инициирована переменная с id пользователя

            //Освежаем эту хак-сессию.
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->pdo('uSes')->prepare('UPDATE 
                u235_hacks 
                SET 
                time=:time 
                WHERE 
                hack_id=:hack_id AND 
                hack_value=:hack_value
                ');
                $time=time();

                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':time', $time,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':hack_id', $id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':hack_value', $hash,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->error('proc uFunc 260'/*.$e->getMessage()*/);}

            //Проверяем, есть ли у пользователя права на доступ к странице, создавшей хак-сессию.

            $this->uSes->getUserACL();
            if($this->uSes->access($page_access)) {
                return true;
            }
            return false;
        }
        return false; //Если хак-сесия НЕ найдена, то выходим
    }
    public function text($str) {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->uCore->text(array('processors','uFunc'),$str);
    }
    public function dir_size($dirname) {
        $totalsize=0;
        if ($dirstream = @opendir($dirname)) {
            while (false !== ($filename = readdir($dirstream))) {
                if ($filename !== '.' && $filename !== '..')
                {
                    if (is_file($dirname. '/' .$filename)) {
                        $totalsize += filesize($dirname . '/' . $filename);
                    }

                    if (is_dir($dirname. '/' .$filename)) {
                        $totalsize += $this->dir_size($dirname . '/' . $filename);
                    }
                }
            }
            closedir($dirstream);
        }
        return $totalsize;
    }
    public function format_size($size){
        /** @noinspection PhpUndefinedMethodInspection */
        $metrics[0] = $this->uCore->text(array('templates', 'uranium_menu'), 'FSbyte');
        /** @noinspection PhpUndefinedMethodInspection */
        $metrics[1] = $this->uCore->text(array('templates', 'uranium_menu'), 'FSKbyte');
        /** @noinspection PhpUndefinedMethodInspection */
        $metrics[2] = $this->uCore->text(array('templates', 'uranium_menu'), 'FSMbyte');
        /** @noinspection PhpUndefinedMethodInspection */
        $metrics[3] = $this->uCore->text(array('templates', 'uranium_menu'), 'FSGbyte');
        /** @noinspection PhpUndefinedMethodInspection */
        $metrics[4] = $this->uCore->text(array('templates', 'uranium_menu'), 'FSTbyte');
        $metric = 0;
        while(floor($size/1024) > 0){
            ++$metric;
            $size /= 1024;
        }
        return round($size,1). ' ' .(isset($metrics[$metric])?$metrics[$metric]:'??');
    }
    public function generate_uuid() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
    }
    public function set_flag_update_sitemap($update_sitemap, $site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->pdo('common')->prepare('UPDATE 
            sitemap 
            SET 
            update_sitemap=:update_sitemap 
            WHERE 
            site_id=:site_id
            ');

            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':update_sitemap', $update_sitemap,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->error('proc uFunc 270'/*.$e->getMessage()*/);}
    }

    //BILL
    public function get_company_bank_details($site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->pdo('common')->prepare('SELECT 
            bank_id,
            bank_name,
            bank_account_number,
            company_vat_number,
            company_account_number,
            company_name,
            company_tax_info_1,
            company_address,
            company_stamp_url,
            company_signature_url,
            vat_percent,
            company_signature_post,
            company_signature_name,
            bill_prefix
            FROM 
            company_bank_details 
            WHERE 
            site_id=:site_id
            ');
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->error('proc uFunc 280'/*.$e->getMessage()*/);}
        return 0;
    }
    private function get_new_bill_number() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->pdo('common')->prepare('SELECT 
            bill_number
            FROM 
            bills 
            ORDER BY 
            bill_number DESC 
            LIMIT 1
            ');
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                return $qr->bill_number + 1;
            }
        }
        catch(PDOException $e) {$this->error('proc uFunc 290'/*.$e->getMessage()*/);}
        return 1;
    }
    public function num2str($num,$currency= 'RUR') {

        $nul='ноль';
        $ten=array(
            array('','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'),
            array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять'),
        );
        $a20=array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать' ,'пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать');
        $tens=array(2=>'двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят' ,'восемьдесят','девяносто');
        $hundred=array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот','восемьсот','девятьсот');
        if($currency=== 'EUR') {
            $unit = array( // Units
                array('цент', 'центов', 'центов', 1),
                array('Евро', 'Евро', 'Евро', 0),
                array('тысяча', 'тысячи', 'тысяч', 1),
                array('миллион', 'миллиона', 'миллионов', 0),
                array('миллиард', 'милиарда', 'миллиардов', 0),
            );
        }
        else {
            $unit = array( // Units
                array('копейка', 'копейки', 'копеек', 1),
                array('рубль', 'рубля', 'рублей', 0),
                array('тысяча', 'тысячи', 'тысяч', 1),
                array('миллион', 'миллиона', 'миллионов', 0),
                array('миллиард', 'милиарда', 'миллиардов', 0),
            );
        }
        //
        list($rub,$kop) = explode('.',sprintf('%015.2f', (float)$num));
        $out = array();
        if ((int)$rub >0) {
            foreach(str_split($rub,3) as $uk=>$v) { // by 3 symbols
                if (!(int)$v) {
                    continue;
                }
                $uk = count($unit)-$uk-1; // unit key
                $gender = $unit[$uk][3];
                list($i1,$i2,$i3) = array_map('intval',str_split($v,1));
                // mega-logic
                $out[] = $hundred[$i1]; # 1xx-9xx
                if ($i2>1) {
                    $out[] = $tens[$i2] . ' ' . $ten[$gender][$i3];
                } # 20-99
                else {
                    $out[] = $i2 > 0 ? $a20[$i3] : $ten[$gender][$i3];
                } # 10-19 | 1-9
                // units without rub & kop
                if ($uk>1) {
                    $out[] = $this->morph($v, $unit[$uk][0], $unit[$uk][1], $unit[$uk][2]);
                }
            } //foreach
        }
        else {
            $out[] = $nul;
        }
        $out[] = $this->morph((int)$rub, $unit[1][0],$unit[1][1],$unit[1][2]); // rub
        $out[] = $kop.' '.$this->morph($kop,$unit[0][0],$unit[0][1],$unit[0][2]); // kop
        return trim(preg_replace('/ {2,}/', ' ', implode(' ',$out)));
    }

    private function morph($n, $f1, $f2, $f5) {
        $n = abs((int)$n) % 100;
        if ($n>10 && $n<20) {
            return $f5;
        }
        $n %= 10;
        if ($n>1 && $n<5) {
            return $f2;
        }
        if ($n==1) {
            return $f1;
        }
        return $f5;
    }
    public function create_bill($items_ar, $customer_info, $add_info= '', $currency= 'руб.', $site_id=site_id) {
        if($currency=== 'EUR') {
            $currency_txt = '&euro;';
        }
        elseif($currency=== 'RUR') {
            $currency_txt = 'руб.';
        }
        elseif($currency=== 'USD') {
            $currency_txt = '$';
        }
        else {
            $currency_txt = '';
        }

//        $items_ar=array(
//            array(/*Название товара*/"Молоток синий",/*Количество */1,/*Единица измерения*/"Шт",/*Цена*/1900)
//        );
//
//        $customer_info=array(
//            "ООО Рога и копыта",/*Наименование компании*/
//            "532007744477",/*ИНН*/
//            "532007744477",/*КПП. Пустая строка, если нет*/
//            "195256, Санкт-Петербург, Невский 13 корп 2 офис 20"/*Юридический адрес. 633010, Новосибирская обл, г Бердск, ул Ленина, д 94, оф 3*/
//        );
//
//        $add_info="Заказ №235";


        $month_number2word=array('', 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');
        if(!$company_bank_details=$this->get_company_bank_details($site_id)) {
            return false;
        }

        $bill_number=$this->get_new_bill_number();
        $bill_hash=$this->genHash();
        $bill_timestamp=time();
        $dir= 'bills/' .$site_id. '/' .$bill_hash. '/' .$bill_timestamp;
        $file=$dir. '/bill_' .$bill_number. '.pdf';
        $bill_status=0;//new


        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->pdo('common')->prepare('INSERT INTO 
            bills (
                   bill_number, 
                   bill_timestamp, 
                   bill_hash, 
                   site_id, 
                   bill_status
                   ) VALUES (
                   :bill_number, 
                   :bill_timestamp, 
                   :bill_hash, 
                   :site_id, 
                   :bill_status
                   )
            ');
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':bill_number', $bill_number,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':bill_timestamp', $bill_timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':bill_hash', $bill_hash,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':bill_status', $bill_status,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->error('proc uFunc 300'/*.$e->getMessage()*/);}


        ob_start();?>
        <!--suppress CssNoGenericFontName -->
        <table style="border:none;  line-height: 12px; font-size: 12px; font-family: Helvetica; width: 100%">
            <tr>
                <td colspan="2" style="border-color:black; border-style: solid; border-width:1px 1px 0 1px; padding: 0 5px;"><?=$company_bank_details->bank_name?></td>
                <td style="border-color:black; border-style: solid; border-width:1px 1px 0 0; padding: 0 5px;">БИК</td>
                <td style="border-color:black; border-style: solid; border-width:1px 1px 0 0; padding: 0 5px;"><?=$company_bank_details->bank_id?></td>
            </tr>
            <tr>
                <td colspan="2"  style="border-color:black; border-style: solid; border-width:0 1px 0 1px; padding: 0 5px;"></td>
                <td style="border-color:black; border-style: solid; border-width:1px 1px 0 0; padding: 0 5px;">Сч. No</td>
                <td style="border-color:black; border-style: solid; border-width:0 1px 0 0; padding: 0 5px;"><?=$company_bank_details->bank_account_number?></td>
            </tr>
            <tr>
                <td colspan="2" style="border-color:black; border-style: solid; border-width:0 1px 1px 1px; padding: 0 5px;">Банк получателя</td>
                <td style="border-color:black; border-style: solid; border-width:0 1px 1px 0; padding: 0 5px;"></td>
                <td style="border-color:black; border-style: solid; border-width:0 1px 1px 0; padding: 0 5px;"></td>
            </tr>
            <tr>
                <td style="border-color:black; border-style: solid; border-width:0 1px 1px 1px; padding: 0 5px;">ИНН <?=$company_bank_details->company_vat_number?></td>
                <td style="border-color:black; border-style: solid; border-width:0 1px 1px 0; padding: 0 5px;"><?=($company_bank_details->company_tax_info_1!== '' ?('КПП '.$company_bank_details->company_tax_info_1): '')?></td>
                <td style="border-color:black; border-style: solid; border-width:0 1px 0 0; padding: 0 5px;">Сч. No</td>
                <td style="border-color:black; border-style: solid; border-width:0 1px 0 0; padding: 0 5px;"><?=$company_bank_details->company_account_number?></td>
            </tr>
            <tr>
                <td colspan="2" style="border-color:black; border-style: solid; border-width:0 1px 0 1px; padding: 0 5px;"><?=$company_bank_details->company_name?></td>
                <td style="border-color:black; border-style: solid; border-width:0 1px 0 0; padding: 0 5px;"></td>
                <td style="border-color:black; border-style: solid; border-width:0 1px 0 0; padding: 0 5px;"></td>
            </tr>
            <tr>
                <td colspan="2" style="border-color:black; border-style: solid; border-width:0 1px 0 1px; padding: 0 5px;">&nbsp;</td>
                <td style="border-color:black; border-style: solid; border-width:0 1px 0 0; padding: 0 5px;"></td>
                <td style="border-color:black; border-style: solid; border-width:0 1px 0 0; padding: 0 5px;"></td>
            </tr>
            <tr>
                <td colspan="2" style="border-color:black; border-style: solid; border-width:0 1px 1px 1px; padding: 0 5px;">Получатель</td>
                <td style="border-color:black; border-style: solid; border-width:0 1px 1px 0; padding: 0 5px;"></td>
                <td style="border-color:black; border-style: solid; border-width:0 1px 1px 0; padding: 0 5px;"></td>
            </tr>

            <tr>
                <td colspan="4" style="border-color:black; border-style: solid; border-width:0 0 1px 0; padding: 20px 5px 5px 5px; font-size:20px; line-height: 20px;"><h3>Счет на оплату № <?=$company_bank_details->bill_prefix?><?=$bill_number?> от <?=date('d',$bill_timestamp)?> <?=$month_number2word[date('n',$bill_timestamp)]?> <?=date('Y',$bill_timestamp)?> г.</h3></td>
            </tr>
            <tr>
                <td colspan="4" style="border-color:black; border-style: solid; border-width:0 0 0 0; padding: 20px 5px 5px 5px; font-size:20px; line-height: 20px;"><?=$add_info?></td>
            </tr>
            <tr>
                <td colspan="4" style="border:none; padding: 0 5px;">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="4" style="border:none; padding: 0 5px;">Поставщик: <strong>
                        <?=$company_bank_details->company_name?>, ИНН
                        <?=$company_bank_details->company_vat_number?>, <?=$company_bank_details->company_tax_info_1!== '' ?('КПП ' .$company_bank_details->company_tax_info_1. ','): '' ?> <?=$company_bank_details->company_address?></strong></td>
            </tr>
            <tr>
                <td colspan="4" style="border:none; padding: 0 5px;">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="4" style="border:none; padding: 0 5px;">Покупатель: <strong><?=$customer_info[0]?>, ИНН <?=$customer_info[1]?>, <?=$customer_info[2]!== '' ?('КПП ' .$customer_info[2]. ','): '' ?><?=$customer_info[3]?></strong></td>
            </tr>
            <tr>
                <td colspan="4" style="border:none; padding: 0 5px;">&nbsp;</td>
            </tr>
        </table>

        <!--suppress CssNoGenericFontName -->
        <table style="border:none;  line-height: 12px; font-size: 12px; font-family: Helvetica; width: 100%; empty-cells: hide;">
            <tr>
                <td style="border-color:black; border-style: solid; border-width:1px; padding: 0 5px; font-weight: bold;">№</td>
                <td style="border-color:black; border-style: solid; border-width:1px 1px 1px 0; padding: 0 5px; font-weight: bold;">Товары (работы, услуги)</td>
                <td style="border-color:black; border-style: solid; border-width:1px 1px 1px 0; padding: 0 5px; font-weight: bold;">кол-во</td>
                <td style="border-color:black; border-style: solid; border-width:1px 1px 1px 0; padding: 0 5px; font-weight: bold;">Ед.</td>
                <td style="border-color:black; border-style: solid; border-width:1px 1px 1px 0; padding: 0 5px; font-weight: bold;">НДС</td>
                <td style="border-color:black; border-style: solid; border-width:1px 1px 1px 0; padding: 0 5px; font-weight: bold;">Цена</td>
                <td style="border-color:black; border-style: solid; border-width:1px 1px 1px 0; padding: 0 5px; font-weight: bold;">Сумма</td>
            </tr><?php
            $total_price=0;
            $items_count=count($items_ar);
            $company_bank_details->vat_percent=(int)$company_bank_details->vat_percent;
            foreach ($items_ar as $i => $iValue) {
                $item= $iValue;
                $vat=(!$company_bank_details->vat_percent? 'Без НДС' :($company_bank_details->vat_percent. '%'));
                ?><tr>
                    <td style="border-color:black; border-style: solid; border-width:1px 1px 1px 1px; padding: 0 5px;"><?=$i+1?></td>
                    <td style="border-color:black; border-style: solid; border-width:1px 1px 1px 0; padding: 0 5px;"><?=$item[0]/*item_title*/?></td>
                    <td style="border-color:black; border-style: solid; border-width:1px 1px 1px 0; padding: 0 5px;"><?=$item[1]/*item_count*/?></td>
                    <td style="border-color:black; border-style: solid; border-width:0 1px 1px 0; padding: 0 5px;"><?=$item[2]/*item_unit*/?></td>
                    <td style="border-color:black; border-style: solid; border-width:0 1px 1px 0; padding: 0 5px;"><?=$vat/*item_vat*/?></td>
                    <td style="border-color:black; border-style: solid; border-width:0 1px 1px 0; padding: 0 5px; text-align: right"><?=number_format ( $item[3] , 2, ',', ' ')/*item_price*/?></td>
                    <td style="border-color:black; border-style: solid; border-width:0 1px 1px 0; padding: 0 5px; text-align: right"><?=number_format ( $total_price+=$item[3]*$item[1] , 2, ',', ' ')?></td>
                </tr><?php
            }
            ?>
            <tr>
                <td colspan="7"  style="border:none; padding: 0 5px;">&nbsp;</td>
            </tr>
            <?if($company_bank_details->vat_percent) {
                $vat = round(($total_price / ((100 + $company_bank_details->vat_percent) / 100) - $total_price) * (-1), 2);
            } ?>
            <tr>
                <td colspan="4" style="border:none; padding: 0 5px;"></td>
                <td colspan="2" style="border:none; padding: 0 5px;"><strong><?=$company_bank_details->vat_percent?('НДС '.$company_bank_details->vat_percent.'%'):'Без НДС'?></strong></td>
                <td style="border:none; padding: 0 5px;"><strong><?= /** @noinspection PhpUndefinedVariableInspection */$company_bank_details->vat_percent?$vat: '' ?></strong></td>
            </tr>
            <tr>
                <td colspan="4" style="border:none; padding: 0 5px;">Всего наименований <?=$items_count?> на сумму <?=number_format ( $total_price , 2, ',', ' ')?> <?=$currency_txt?></td>
                <td colspan="2" style="border:none; padding: 0 5px;"><strong>Итого к оплате:</strong></td>
                <td style="border:none; padding: 0 5px;"><strong><?=number_format ( $total_price , 2, ',', ' ')?> <?=$currency_txt?></strong></td>
            </tr>
            <tr>
                <?$price_str=$this->num2str($total_price,$currency);
                $first_letter=mb_substr($price_str,0,1);
                $other_text=mb_substr($price_str,1);
                $price_str='<span style="text-transform:uppercase">'.$first_letter.'</span>'.$other_text;
                ?>
                <td colspan="4" style="border:none; padding: 0 5px;"><strong><?=$price_str?></strong></td>
                <td colspan="2" style="border:none; padding: 0 5px;"></td>
                <td style="border:none; padding: 0 5px;"></td>
            </tr>
            <tr>
                <td colspan="7"  style="border-color:black; border-style: solid; border-width:0 0 1px 0; padding: 0 5px;">&nbsp;</td>
            </tr>
        </table>

        <!--suppress CssNoGenericFontName -->
        <table style="border:none;  line-height: 12px; font-size: 12px; font-family: Helvetica; width: 100%;">
            <tr>
                <td><strong>Поставщик</strong>
                <td><?=$company_bank_details->company_signature_post?></td>
                <td><?=$company_bank_details->company_signature_url!== '' ?('<img alt="" src="'.$company_bank_details->company_signature_url.'" style="max-width: 170px;">'): '' ?></td>
                <td><?=$company_bank_details->company_signature_name?></td>
            </tr>
            <tr><td colspan="3"  style="border:none;"><?=$company_bank_details->company_stamp_url!== '' ?('<img alt="" src="'.$company_bank_details->company_stamp_url.'" style="max-width: 150px;">'): '' ?></td></tr>
        </table>
        <?$html=ob_get_clean();

        if(!file_exists($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }
        if(!self::create_empty_index($dir)) {
            $this->error('proc uFunc 310');
        }

        require_once 'lib/MPDF/mpdf.php';
        $mpdf=new mPDF('utf-8'/*, 'A4-L'*/);
        $stylesheet = file_get_contents('lib/MPDF/mpdf.css');
        $mpdf->WriteHTML($stylesheet,1);
        $mpdf->WriteHTML($html,2);
        $mpdf->Output($file, 'F');

        return $bill_number;
    }
    public function bill_number2file_path($bill_number,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->pdo('common')->prepare('SELECT 
            bill_timestamp,
            bill_hash
            FROM 
            bills 
            WHERE
            bill_number=:bill_number AND
            site_id=:site_id
            ');
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':bill_number', $bill_number,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                $dir= 'bills/' .$site_id. '/' .$qr->bill_hash. '/' .$qr->bill_timestamp;
                return $dir. '/bill_' .$bill_number. '.pdf';
            }
        }
        catch(PDOException $e) {$this->error('proc uFunc 320'/*.$e->getMessage()*/,1);}
        return 0;
    }

    public function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(isset($GLOBALS['BUILDER']['cron'])) {
            $this->uSes=new uSes($this->uCore,$this);
            include_once 'processors/inc/file_ext2fonticon.php';
        }
    }
}
