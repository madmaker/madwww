<?php
class mpage_sitemap {
    private $uCore;
    public $pagesAr;

    public function text($str) {
        return $this->uCore->text(array('mainpage','sitemap'),$str);
    }

    private function getData() {
        if(!$query=$this->uCore->query('pages',"SELECT
        `page_mod`,
        `page_title`,
        `page_id`,
        `navi_parent_page_id`,
        `page_name`,
        `page_access`
        FROM
        `u235_pages_list`
        WHERE
        (`navi_parent_page_id`!='' OR `page_id`='0') AND
        `page_title`!=''
        ORDER BY `page_title`
        ")) $this->uCore->error(1);
        while($quAr=$query->fetch_assoc()) {
            //Проверка, имеет ли пользователь доступ к этой странице
            if($this->uCore->access($quAr['page_access'])) {
                if(!isset($this->pagesAr[$quAr['navi_parent_page_id']] ['counter'])) $this->pagesAr[$quAr['navi_parent_page_id']] ['counter']=0;
                $this->pagesAr[$quAr['navi_parent_page_id']] [  $this->pagesAr[$quAr['navi_parent_page_id']] ['counter']  ] ['page_mod']    = $quAr['page_mod'];
                $this->pagesAr[$quAr['navi_parent_page_id']] [  $this->pagesAr[$quAr['navi_parent_page_id']] ['counter']  ] ['page_title']     = $quAr['page_title'];
                $this->pagesAr[$quAr['navi_parent_page_id']] [  $this->pagesAr[$quAr['navi_parent_page_id']] ['counter']  ] ['page_name']     = $quAr['page_name'];
                $this->pagesAr[$quAr['navi_parent_page_id']] [  $this->pagesAr[$quAr['navi_parent_page_id']] ['counter']  ] ['page_id']        = $quAr['page_id'];
                $this->pagesAr[$quAr['navi_parent_page_id']] ['counter']++;
            }
        }

        if(!$query=$this->uCore->query('pages',"SELECT
        `page_title`,
        `page_id`,
        `navi_parent_page_id`,
        `page_name`,
        `page_access`
        FROM
        `u235_pages_html`
        WHERE
        `navi_parent_page_id`!=''
        ORDER BY `page_title`
        ")) $this->uCore->error(2);
        while($quAr=$query->fetch_assoc()) {
            //Проверка, имеет ли пользователь доступ к этой странице
            if($this->uCore->access ($quAr['page_access'])) {
                if(!isset($this->pagesAr[$quAr['navi_parent_page_id']] ['counter'])) $this->pagesAr[$quAr['navi_parent_page_id']] ['counter']=0;
                $this->pagesAr[$quAr['navi_parent_page_id']] [  $this->pagesAr[$quAr['navi_parent_page_id']] ['counter']  ] ['page_mod']    = "static";
                $this->pagesAr[$quAr['navi_parent_page_id']] [  $this->pagesAr[$quAr['navi_parent_page_id']] ['counter']  ] ['page_title']     = $quAr['page_title'];
                $this->pagesAr[$quAr['navi_parent_page_id']] [  $this->pagesAr[$quAr['navi_parent_page_id']] ['counter']  ] ['page_name']     = $quAr['page_name'];
                $this->pagesAr[$quAr['navi_parent_page_id']] [  $this->pagesAr[$quAr['navi_parent_page_id']] ['counter']  ] ['page_id']        = "s-".$quAr['page_id'];
                $this->pagesAr[$quAr['navi_parent_page_id']] ['counter']++;
            }
        }
    }
    public function genList($navi_parent_page_id) {
        $cnt='';
        if(isset($this->pagesAr[$navi_parent_page_id])) {
            if($this->pagesAr[$navi_parent_page_id]['counter']>0) {
                $cnt.='<ul>';
                for($i=0;$i<$this->pagesAr[$navi_parent_page_id]['counter'];$i++) {
                    $cnt.='<li><a href="'.u_sroot.$this->pagesAr[$navi_parent_page_id][$i]['page_mod'].'/'.$this->pagesAr[$navi_parent_page_id][$i]['page_name'].'/">'./*$this->pagesAr[$navi_parent_page_id] [$i]  ['page_mod'].'->'.*//*//Раскоментить, если нужен модуль*/$this->pagesAr[$navi_parent_page_id] [$i] ['page_title']./*'&nbsp;('.$this->pagesAr[$navi_parent_page_id][$i]['page_name'].')'.*//*Раскоментить, если нужно имя страницы*/'</a></li>';
                    $cnt.=$this->genList($this->pagesAr[$navi_parent_page_id][$i]['page_id']);
                }
                $cnt.='</ul>';
            }
        }
        return $cnt;
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;

        $this->uCore->page['page_title']=$this->text("Page name"/*Карта сайта*/);

        $this->getData();
    }
}

$mpage=new mpage_sitemap($this);
$this->uFunc->incCss(u_sroot.'mainpage/css/mainpage.min.css');
ob_start();
?>


<div class="mpage mpage_sitemap">
<h1><?=$mpage->text("Sitemap - pg header"/*Карта сайта*/)?></h1>
    <div class="sitemap">
    <? echo $mpage->genList('mainpage');?>
    </div>
</div>

<?
$this->page_content=ob_get_contents();
ob_end_clean();

include "templates/template.php";
?>
