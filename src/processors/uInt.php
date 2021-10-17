<?php
require_once "processors/classes/uFunc.php";

class uInt {
    public $uFunc;
    private $uCore,$lang_ars;
    public $lang="ru_RU";
    public function set_lang() {
        $this->lang=$this->uFunc->getConf("site_lang","content",0,site_id);
        return $this->lang;
    }
    public function js($mod,$page) {
        $this->uFunc->incJs(staticcontent_url.'js/'.$mod.'/uInt/'.$this->lang.'/'.$page.'.min.js');
    }
    public function print_js($mod,$page) {
        echo $this->uFunc->printJs(u_sroot.$mod.'/uInt/'.$this->lang.'/js/'.$page.'.min.js');
    }
    public function text($mod_page_ar/*array ('Mod name', 'page name')*/,$string) {
        //include_once $mod_page_ar[0].'/uInt/'.$this->lang.'/php/'.$mod_page_ar[1].'.php';
        $ar_name='uInt_'.$mod_page_ar[0].'_'.$mod_page_ar[1];

        if(!isset($this->lang_ars[$ar_name])) {
            include_once $mod_page_ar[0].'/uInt/'.$this->lang.'/php/'.$mod_page_ar[1].'.php';
            if(!isset($$ar_name)) return $string;
            $this->lang_ars[$ar_name]=$$ar_name;
        }

        if(!isset($this->lang_ars[$ar_name][$string])) return "uInt-".$ar_name."-".$string;
        return $this->lang_ars[$ar_name][$string];
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
    }
}
