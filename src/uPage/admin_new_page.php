<?php
namespace uPage\admin;

use PDO;
use PDOException;
use processors\uFunc;
use uNavi\common\uNavi;
use uPage\common;
use uSes;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uPage/inc/common.php";
require_once "uEditor/classes/common.php";
require_once "uNavi/classes/uNavi.php";

class admin_new_page {
    public $page_timestamp;
    public $folder_id;
    public $uFunc, $uForms;
    public $uSes;
    public $uPage;
    public $uEditor;
    public $template_id;
    public $text_folder_id;
    private $uCore,$page_id,$page_title,$page_url,$page_type;
    private function check_data() {
        if(!isset($_POST['page_title'],$_POST['cur_folder_id'],$_POST['template_id'])) $this->uFunc->error(10);
        $this->page_title=trim($_POST['page_title']);
        $this->folder_id=(int)$_POST['cur_folder_id'];
        $this->template_id=(int)$_POST['template_id'];

        if(empty($this->page_title)) die("{
        'status':'error',
        'msg':'title is empty'
        }");
    }
    private function check_if_page_url_is_free($url,$index) {
        if($index===0) $new_url=$url;
        else $new_url=$url.'_'.$index;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            page_id
            FROM
            u235_pages
            WHERE
            page_url=:page_url AND
            site_id=:site_id
            LIMIT 1
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_url', $new_url,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
        return 0;
    }

    private function create_new_page($site_id=site_id) {
        //make page_url
        $url=uString::text2filename(uString::rus2eng($this->page_title));
        //check if page_url is free
        /** @noinspection PhpStatementHasEmptyBodyInspection */
        for($i=0; $this->check_if_page_url_is_free($url,$i); $i++);
        if($i===0) {
            if(uString::isDigits($url)) {//We can't allow url that contains only digits. It may be an ID of page.
                $url='_'.$url;
            }
            $this->page_url=$url;
        }
        else $this->page_url=$url.'_'.$i;

        if($this->folder_id) {
            if ($folder_data = $this->uPage->page_id2data($this->folder_id, "page_type", $site_id)) {
                if($folder_data->page_type!="folder") $this->folder_id = 0;
            }
            else $this->folder_id = 0;
        }

        if(!$template_data=$this->uPage->page_template_id2data($this->template_id,"page_id,site_id")) $this->uFunc->error(30);
        $tmp_page_id=(int)$template_data->page_id;
        $tmp_site_id=(int)$template_data->site_id;
        if(!$tmp_page_data=$this->uPage->page_id2data($tmp_page_id,"*",$tmp_site_id)) $this->uFunc->error(40);

        $tmp_page_data->page_title=$this->page_title;
        $tmp_page_data->page_url=$this->page_url;
        $tmp_page_data->folder_id=$this->folder_id;

        $page_data=$this->uPage->copy_page($tmp_page_data,$tmp_site_id,$site_id);
        $this->page_id=(int)$page_data->page_id;
        $this->page_timestamp=(int)$page_data->page_timestamp;
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;

        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");

        $this->uFunc=new uFunc($this->uCore);
        $this->uPage=new common($this->uCore);


        $this->check_data();
        $this->create_new_page();
        $this->uFunc->set_flag_update_sitemap(1, site_id);
        echo "{
        'status' : 'done',
        'page_id' : '".$this->page_id."',
        'folder_id' : '".$this->folder_id."',
        'page_title' : '".rawurlencode(htmlspecialchars($this->page_title))."',
        'page_url' : '".$this->page_url."',
        'page_type' : '',
        'page_timestamp' : '".$this->page_timestamp."',
        'deleted':'0'
        }";
    }
}
new admin_new_page($this);