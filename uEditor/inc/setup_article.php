<?php
require_once "processors/classes/uFunc.php";
require_once 'uDrive/classes/common.php';

class uEditor_setup_article {
    public $uFunc;
    private $uCore,$page_id,$page;
    public $uDrive_folder_id;
    private $uDrive;

    public function text($str) {
        return $this->uCore->text(array('uEditor','setup_article'),$str);
    }

    private function define_uDrive_folder_id() {
        if(!(int)$this->uDrive_folder_id) {
            $uDrive_folder_id = $this->uDrive->get_module_folder_id("uEditor");
            $page_title=$this->uCore->page['page_title'];
            if(!strlen($page_title)) $page_title=$this->text("Article - folder title"/*Статья */)." ". $this->page_id;
            $this->uDrive_folder_id=$this->uDrive->create_folder($page_title,$uDrive_folder_id);

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("pages")->prepare("UPDATE 
                u235_pages_html
                SET
                uDrive_folder_id=:folder_id
                WHERE
                page_id=:page_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':folder_id', $this->uDrive_folder_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}
        }
    }

    public function get_page_info() {
        if(!$query=$this->uCore->query("pages","SELECT
        `page_alias`,
        `navi_parent_menu_id`,
        `meta_description`,
        `meta_keywords`,
        `page_text`,
        `page_short_text`,
        `page_avatar_time`,
        `show_avatar`,
        `page_timestamp_show`,
        `page_timestamp`,
        `uDrive_folder_id`
        FROM
        `u235_pages_html`
        WHERE
        `page_id`='".$this->page_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(20);
        if(!mysqli_num_rows($query)) $this->uCore->error(30);
        $this->page=$query->fetch_object();
        $this->uDrive_folder_id=(int)$this->page->uDrive_folder_id;
    }
    private function cache_build_page($dir) {
        if(!file_exists($dir)) mkdir($dir,0755,true);
        $cache = fopen($dir.'/cache.html', 'w');

        ob_start();

        $this->get_page_info();

        $this->page->page_text=uString::sql2text($this->page->page_text,true);//Деконвертируем из БД в HTML
        $this->page->page_text=mb_convert_encoding($this->page->page_text, 'HTML-ENTITIES', 'UTF-8');
        $doc = new DOMDocument();
        @$doc->loadHTML($this->page->page_text);
        $this->page->page_text = $doc->saveHTML();

        if($this->page->page_avatar_time!='0'&&
        $this->page->show_avatar=='1'&&
        $this->uCore->uFunc->getConf("show_avatars_on_pages","content")=='1') {
        include_once 'uEditor/inc/page_avatar.php';
        $page_avatar=new uEditor_page_avatar($this->uCore);
        $page_avatar_addr=$page_avatar->get_avatar(450,$this->page_id,$this->page->page_avatar_time);
        }
        else $page_avatar_addr=false;

        $page_content='<img src="'.$page_avatar_addr.'" class="page_avatar uEditor_page_avatar" '.(!$page_avatar_addr?'style="display:none;"':'').'>';
        $page_content.='<div class="staticContent">';
        $page_content.=$this->page->page_text;
        $page_content.='</div>';
        $page_content.='<div class="text-muted page_content_page_timestamp" '.($this->page->page_timestamp_show=='0'?'style="display:none"':'').'>'.date('d.m.Y H:i',$this->page->page_timestamp).'</div>';

        fwrite($cache, $page_content);
        ob_end_clean();
    }
    private function increase_views_counter($site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("UPDATE
            u235_pages_html
            SET
            views_counter=views_counter+1
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
    }
    public function cache_builder() {
        $dir='uEditor/cache/'.site_id.'/'.$this->page_id;

        $this->increase_views_counter();


        if(!file_exists($dir.'/cache.html')) $this->cache_build_page($dir);
        return file_get_contents ( $dir.'/cache.html');
    }
    public function admin_page_builder() {
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uDrive=new \uDrive\common($this->uCore);

        $this->increase_views_counter();

        $this->get_page_info();
        $this->define_uDrive_folder_id();

        include_once 'uEditor/inc/edit_in_place_dialogs.php';

        $this->uCore->uFunc->incJs(u_sroot.'js/u235/jquery/jquery.uranium235plugins.js');

        //uEditor in place
        $this->uCore->uFunc->incJs(u_sroot.'js/tinymce/tinymce.min.js');
        $this->uCore->uInt_js('uEditor','uEditor_in_place');
        $this->uCore->uFunc->incJs(u_sroot.'uEditor/js/uEditor_in_place.min.js');
        $this->uCore->uFunc->incCss(u_sroot.'uEditor/css/uEditor.min.css');
        $hash=$this->uCore->uFunc->sesHack();
        $page_content='<script type="text/javascript">
            if(typeof uEditor==="undefined") uEditor={};
            uEditor.sessions_hack_hash="'.$hash['hash'].'";
            uEditor.sessions_hack_id="'.$hash['id'].'";
            
            uEditor.uDrive_folder_id="'.$this->uDrive_folder_id.'";
            </script>';

        $this->page->page_text=uString::sql2text($this->page->page_text,true);//Деконвертируем из БД в HTML
        $this->page->page_text=mb_convert_encoding($this->page->page_text, 'HTML-ENTITIES', 'UTF-8');
        $doc = new DOMDocument();
        @$doc->loadHTML($this->page->page_text);
        $this->page->page_text = $doc->saveHTML();

        if($this->page->page_avatar_time!='0'&&
            $this->page->show_avatar=='1'&&
            $this->uCore->uFunc->getConf("show_avatars_on_pages","content")=='1') {
            include_once 'uEditor/inc/page_avatar.php';
            $page_avatar=new uEditor_page_avatar($this->uCore);
            $page_avatar_addr=$page_avatar->get_avatar(450,$this->page_id,$this->page->page_avatar_time);
        }
        else $page_avatar_addr=false;

        $page_content.='<img src="'.$page_avatar_addr.'" class="page_avatar" id="uEditor_page_avatar" '.(!$page_avatar_addr?'style="display:none;"':'').'>
        <div class="staticContent" id="staticContent">'.
        $this->page->page_text.
        '</div>
        <div class="text-muted" id="page_content_page_timestamp" '.($this->page->page_timestamp_show=='0'?'style="display:none"':'').'>'.date('d.m.Y H:i',$this->page->page_timestamp).'</div>
        
        <div id="uDrive_my_drive_uploader_init"></div>';

        include_once 'uDrive/inc/my_drive_manager.php';

        return $page_content;
    }
    public function clear_cache($page_id) {
        uFunc::rmdir("uEditor/cache/".site_id.'/'.$page_id);
    }
    function __construct (&$uCore,$page_id) {
        $this->uCore=&$uCore;
        $this->page_id=$page_id;
        $this->uFunc=new \processors\uFunc($this->uCore);
    }
}
