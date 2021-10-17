<?
require_once 'inc/art_avatar.php';
class uCat_articles {
    private $uCore;
    public $q_cats,$curPage,$art_per_page,$art_count,$art_avatar;
    function __construct(&$uCore) {
        $this->uCore=&$uCore;

        $this->checkData();

        $this->art_avatar=new uCat_art_avatar($this->uCore);

        $this->getArts();
    }
    private function checkData() {
        $this->curPage=0;
        if(isset($_GET['page'])) {
            if(uString::isDigits($_GET['page'])) $this->curPage=$_GET['page'];
        }
        $this->art_per_page=$this->uCore->uFunc->getConf("articles_number_per_page","uCat");
    }
    private function getArts(){
        //art list
        if(!$this->q_cats=$this->uCore->query('uCat',"SELECT
        `art_id`,
        `art_title`,
        `art_text`,
        `art_avatar_time`
        FROM
        `u235_articles`
        WHERE
        `site_id`='".site_id."'
        ORDER BY
        `art_id` DESC
        LIMIT ".($this->curPage*$this->art_per_page).",".$this->art_per_page."
        ")) $this->uCore->error(1);

        //get arts count
        if(!$query=$this->uCore->query('uCat',"SELECT
        COUNT(`art_id`)
        FROM
        `u235_articles`
        WHERE
        `site_id`='".site_id."'
        ")) $this->uCore->error(2);
        $qr=$query->fetch_assoc();
        $this->art_count=$qr["COUNT(`art_id`)"];
    }
    public function insertPageNums() {
        $pageNumber=ceil($this->art_count/$this->art_per_page);
        $cnt='<ul class="pagination">';
        $butNum=4;//number of buttons before and after
        if($pageNumber>1) {
            $start=0;
            $end=$pageNumber;
            if($pageNumber>$butNum*2) {
                $start=($this->curPage-$butNum)<0?0:($this->curPage-$butNum);
                $end=($this->curPage+$butNum)>$pageNumber?$pageNumber:($this->curPage+$butNum);
                if(($start+$end)<$pageNumber) $end=($start+$butNum*2)<$pageNumber?$start+$butNum*2:$pageNumber;
            }
            if($start>0) {
                $cnt.='<li><a href="'.u_sroot.$this->uCore->mod.'/'.$this->uCore->page_name.'?page='.($start-1).'">&laquo;</a></li>';
            }
            for($i=$start;$i<$end;$i++) {
                $cnt.='<li '; if($this->curPage==$i) $cnt.='class="active"'; $cnt.='><a href="'.u_sroot.$this->uCore->mod.'/'.$this->uCore->page_name.'?page='.$i.'">'.($i+1).'</a></li>';
            }
            if($end<$pageNumber) {
                $cnt.='<li><a href="'.u_sroot.$this->uCore->mod.'/'.$this->uCore->page_name.'?page='.($end).'">&raquo;</a></li>';
            }
        }
        $cnt.='</ul>';
        return $cnt;
    }
}
$uCat=new uCat_articles($this);

$arts_label=$this->uFunc->getConf("arts_label","uCat");
$this->page['page_title']=$arts_label;
ob_start();
?>

<div class="uCat articles">
<h1><?=$arts_label?></h1>
    <?=$uCat->insertPageNums()?>
<? for($i=0;$art=$uCat->q_cats->fetch_object();$i++) {
    $txt=uString::sql2text($art->art_text,true);
    $pos=mb_strpos($txt,'<!-- my page break -->',0, 'UTF-8');
    if(!$pos) {
        $pos=mb_strpos($txt,'<!-- pagebreak -->',0, 'UTF-8');
        if(!$pos) {
            $txt=mb_substr(strip_tags($txt),0,600,'UTF-8').'...';
        }
        else $txt=mb_substr($txt,0,$pos,'UTF-8');
    }
    else $txt=mb_substr($txt,0,$pos,'UTF-8');
    ?>
    <div class="row">
        <div class="col-md-12"><div class="info">
            <? if($art_avatar=$uCat->art_avatar->get_avatar('arts_list',$art->art_id,$art->art_avatar_time)) {?>
                <div class="image">
                    <a href="<? echo u_sroot.'uCat/article/'.$art->art_id;?>">
                        <img src="<?=$art_avatar?>">
                    </a>
                </div>
            <?}?>
            <h1 class="title"><a href="<? echo u_sroot.'uCat/article/'.$art->art_id;?>"><? echo uString::sql2text($art->art_title);?></a></h1>
            <div class="descr"><?=$txt?></div>
        </div></div>
    </div>
<?}?>
    <?if(mysqli_num_rows($uCat->q_cats)>=$uCat->art_per_page) {?>
    <?=$uCat->insertPageNums()?>
    <?}?>
</div>
<? $this->page_content=ob_get_contents();
ob_end_clean();
include "templates/template.php";
?>
