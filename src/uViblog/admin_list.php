<?
class uViblog_admin_list {
    private $uCore;
    public $status,$headerStatus;
    public $q_videos,$hash;
    function __construct(&$uCore) {
        $this->uCore=&$uCore;

        $this->hash=$this->uCore->uFunc->sesHack();

        $this->defStatus();
        $this->getCats();
        $this->setPanel();
    }
    private function defStatus(){
        if(isset($_GET['deleted'])) {
            $this->status="='deleted'";
            $this->headerStatus=' (Удаленные)';
        }
        else {
            $this->status="IS NULL";
            $this->headerStatus='';
        }
    }
    private function getCats(){
        //Sections list
        if(!$this->q_videos=$this->uCore->query("uViblog","SELECT
        `video_id`,
        `video_title`,
        `video_descr`,
        `video_code`
        FROM
        `u235_list`
        WHERE
        `video_status` ".$this->status." AND
        `site_id`='".site_id."'
        ORDER BY `video_id` ASC
        ")) $this->uCore->error(1);
    }
    private function setPanel() {
        $this->uCore->page_panel='<ul class="u235_top_menu">';
        if($this->status=="='deleted'") { $this->uCore->page_panel.='
            <li><a href="javascript:void(0)" id="uViblog_restoreBtn">Восстановить</a></li>
            <li><a href="javascript:void(0)" id="uViblog_watchBtn">Просмотр</a></li>
            <li><a href="'.u_sroot.$this->uCore->mod.'/'.$this->uCore->page_name.'" id="but_trash">Активные</a></li>
            ';
        } else { $this->uCore->page_panel.='
            <li><a href="javascript:void(0)" id="uViblog_editBtn">Редактировать</a></li>
            <li><a href="javascript:void(0)" id="uViblog_watchBtn">Просмотр</a></li>
            <li><a class="delBtn" href="javascript:void(0)" id="uViblog_deleteBtn">Удалить</a></li>
            <li><a href="javascript:void(0)" id="uViblog_createBtn">Создать</a></li>
            <li><a href="'.u_sroot.$this->uCore->mod.'/'.$this->uCore->page_name.'?deleted" id="but_trash">Удаленные</a></li>
            ';
        }
        $this->uCore->page_panel.='</ul>';
    }
}
$uViblog=new uViblog_admin_list($this);

//$this->uFunc->incJs(u_sroot.'/js/u235/uString.js');
$this->uFunc->incJs(u_sroot.'/js/fancybox/jquery.fancybox.pack.js');
$this->uFunc->incCss(u_sroot.'/js/fancybox/jquery.fancybox.css');
//tinymce
$this->uFunc->incJs(u_sroot.'js/tinymce/tinymce.min.js');

$this->uFunc->incJs(u_sroot.'uViblog/js/'.$this->page_name.'.min.js');
$this->uFunc->incCss(u_sroot.'templates/u235/css/uViblog/uViblog.css');

$this->page['page_title']=$this->uFunc->getConf("how_to_call","uViblog");
ob_start();
?>

<h1><?=$this->uFunc->getConf("how_to_call","uViblog")?><?=$uViblog->headerStatus?></h1>


    <div class="uViblog admin_list u235_admin">
        <div class="list"></div>
    </div>


    <script type="text/javascript">
        if(typeof uViblog==="undefined") {
            uViblog = {};

            uViblog.video_id = [];
            uViblog.video_title = [];
            uViblog.video_descr = [];
            uViblog.video_code = [];
            uViblog.video_show = [];
            uViblog.video_sel = [];
            uViblog.video_id2index = [];
        }

        //SECTIONS
        <? for($i=0;$data=$uViblog->q_videos->fetch_object();$i++) { ?>
        i=<?=$i?>;
        uViblog.video_id[i]=<?=$data->video_id?>;
        uViblog.video_title[i]="<?=rawurlencode(uString::sql2text($data->video_title))?>";
        uViblog.video_descr[i]="<?=rawurlencode(uString::sql2text($data->video_descr,true))?>";
        uViblog.video_code[i]="<?=rawurlencode(uString::sql2text($data->video_code,true))?>";
        uViblog.video_show[i]=true;
        uViblog.video_sel[i]=false;
        uViblog.video_id2index[uViblog.video_id[i]]=i;
        <?}?>
        uViblog.sessions_hack_hash="<?=$uViblog->hash['hash']?>";
        uViblog.sessions_hack_id=<?=$uViblog->hash['id']?>;
        uViblog.page_status="<?=$uViblog->status?>";
    </script>


<div style="display:none">
    <div title="Новая запись" id="uViblog_createNew_dg" class="uDialog"></div>
    <div title="Ошибка" id="uViblog_createNew_er_dg"></div>

    <div title="Описание" id="uViblog_video_descr_dg">
        <div class="html" id="uViblog_video_descr_dg_html"></div>
    </div>
    <div title="Код записи" id="uViblog_video_code_dg">
        <textarea id="uViblog_video_code_textarea"></textarea>
    </div>
    <div class="u235_files" title="Файлы для записей"><div class="filelist"></div><div id="uploader_descr"></div></div>
</div>


<? $this->page_content=ob_get_contents();
ob_end_clean();
include "templates/u235/template.php";
?>
