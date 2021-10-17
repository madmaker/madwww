<?
require_once 'lib/simple_html_dom.php';
class uViblog_records {
    private $uCore;
    public $count_per_page;
    public $q_videos,$mode,$page,$limit_start,$haveMore;
    private function defMode() {
        $count=$this->uCore->uFunc->getConf("record_show_count","uViblog");
        if(!uString::isDigits($count)) $count=5;
        $this->count_per_page=$count;

        $this->mode='normal';
        if(isset($_GET['mode'])) {
            $mode=$_GET['mode'];
            if($mode=='values_only') $this->mode='values_only';
        }
        $this->page=$this->limit_start=0;
        if(isset($_GET['page'])) {
            $page=$_GET['page'];
            if(uString::isDigits($page)) {
                $this->page=$page;
                $this->limit_start=$this->page*$this->count_per_page;
            }
        }
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;

        $this->defMode();
        $this->getRecords();
    }
    private function getRecords(){
        //Sections list
        if(!$this->q_videos=$this->uCore->query("uViblog","SELECT
        `video_id`,
        `video_title`,
        `video_descr`,
        `video_code`
        FROM
        `u235_list`
        WHERE
        `video_status` IS NULL AND
        `site_id`='".site_id."'
        ORDER BY `video_id` DESC
        LIMIT ".$this->limit_start.", ".$this->count_per_page."
        ")) $this->uCore->error(1);

        //get count
        if(!$query=$this->uCore->query("uViblog","SELECT
        COUNT(`video_id`)
        FROM
        `u235_list`
        WHERE
        `video_status` IS NULL AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(1);
        $qr=$query->fetch_assoc();
        $count=$qr['COUNT(`video_id`)'];
        $pagesCount=$count/$this->count_per_page;
        if($pagesCount>$this->page+1) $this->haveMore=true;
        else $this->haveMore=false;
    }
}
$uViblog=new uViblog_records($this);

$this->uFunc->incJs(u_sroot.'uViblog/js/'.$this->page_name.'.js');
$this->uFunc->incCss(u_sroot.'templates/site_'.site_id.'/css/'.'uViblog/uViblog.css');

$this->page['page_title']=$this->uFunc->getConf("how_to_call","uViblog");
ob_start();
?>
<? if($uViblog->mode=='normal') {?>
<div class="uViblog uViblog_records">
    <h1><?=$this->uFunc->getConf("how_to_call","uViblog")?></h1>
<?}?>
<? for($i=0;$data=$uViblog->q_videos->fetch_object();$i++) {
$html=str_get_html(uString::sql2text($data->video_code,true));
foreach($html->find('iframe') as $el) {
    $el->width='100%';
    $el->height='100%';
}
?>
    <div class="row record">
        <div class="col-md-4 video"><?=$html?></div>
        <div class="col-md-6 descr">
            <h1 class="title"><?=uString::sql2text($data->video_title)?></h1>
            <div class="descr"><?=uString::sql2text($data->video_descr,true)?></div>
        </div>
    </div>
    <div class="row"><div class="col-md-12"><div class="sep">&nbsp;</div></div></div>
<?}?>
<?if($uViblog->haveMore) {?>
    <div class="row moreButton"><div class="col-md-12"><button class="show_more" onclick="uViblog.getMore(<?=$uViblog->page+1?>)">Показать еще</button></div></div>
<?}?>
<? if($uViblog->mode=='normal') {?></div><?}?>
<? $this->page_content=ob_get_contents();
ob_end_clean();
if($uViblog->mode=='values_only') echo $this->page_content;
else include "templates/template.php";
?>
