<?php
require_once "uPage/elements/art/common.php";
/** @noinspection PhpFullyQualifiedNameUsageInspection */
$el_common=new \uPage\admin\art($this->uPage);
/** @noinspection PhpUndefinedVariableInspection */
$conf=$el_common->get_el_config_art($cols_els_id);

$q_get="page_name,page_alias,page_avatar_time".
($conf->show_title?",page_title":"").
($conf->show_short_text?",page_short_text":"").
($conf->show_text?",page_text":"");

try {
    $stm=$this->uFunc->pdo("pages")->prepare("SELECT
".$q_get."
FROM
u235_pages_html
WHERE
page_id=:page_id AND
site_id=:site_id
");
$site_id=site_id;
/** @noinspection PhpUndefinedMethodInspection */
/** @noinspection PhpUndefinedVariableInspection */
$stm->bindParam(':page_id', $el_id,PDO::PARAM_INT);
/** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
/** @noinspection PhpUndefinedMethodInspection */$stm->execute();
}
catch(PDOException $e) {$this->uFunc->error('uPage/elements/art/cache_print/10'/*.$e->getMessage()*/);}
if(!$art=$stm->fetch(PDO::FETCH_OBJ)) {
    $this->uFunc->error("uPage/elements/art/cache_print/20");
}

if($conf->show_avatar) {
require_once 'uEditor/inc/page_avatar.php';
$page_avatar=new uEditor_page_avatar($this->uCore);
}

/** @noinspection TypeUnsafeComparisonInspection */
if(site_id==6) {
    $href = u_protocol.'://'.$_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
}
else {
    $href = strlen($art->page_alias) ? (u_sroot . $art->page_alias) : (u_sroot . 'page/' . $art->page_name);
}

/** @noinspection PhpUndefinedVariableInspection */
$cnt='<div class="uPage_art">'.($conf->show_title ?'<h3>'.
    ($conf->title_is_link2art?('<a href="'.$href .'">'):'').
        uString::sql2text($art->page_title,1).
        ($conf->title_is_link2art?('</a>'):'').
    '</h3>'
:""
).
($conf->show_avatar&&$art->page_avatar_time?'<a href="'.$href.'"><img alt="" class="pull-left img-responsive page_avatar uEditor_page_avatar" src="'.$page_avatar->get_avatar(450, $el_id).'" /></a>':'').
($conf->show_short_text?'<div class="short_text" style="display:table">'.
    ($conf->short_text_is_link2art?'<a href="'.$href.'">':'').uString::sql2text($art->page_short_text,1).($conf->short_text_is_link2art?'</a>':'').
    ($conf->show_more_btn?('<div class="short_text_more_btn row">
        <a class="pull-right" href="'.$href.'">'.$this->text("More..."/*Подробнее...*/).'</a>
    </div>')
    :
    ""
    ).'</div>'
:""
).
($conf->show_text ?'<div class="text">'.uString::sql2text($art->page_text,1).'</div>'
: ""
);
$cnt.="</div>";


echo $cnt;
