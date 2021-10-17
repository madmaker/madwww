<?php
require_once 'inc/art_avatar.php';
require_once 'processors/classes/uFunc.php';

class uCat_article {
    private $uCore;
    public $art_id,$art,$content_only,$art_avatar,$articles_items;
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);

        $this->checkData();
        $this->getArticle();
        if(!$this->content_only) $this->get_attached_items();

        $this->art_avatar=new uCat_art_avatar($this->uCore);

        $this->define_breadcrumb();
        $this->increase_views_counter();
    }
    private function checkData() {
        if(isset($_GET['content_only'])) $this->content_only=true;
        else $this->content_only=false;
        if(!isset($this->uCore->url_prop[1])) $this->error();
        $this->art_id=$this->uCore->url_prop[1];
        if(!uString::isDigits($this->art_id)) $this->error();
    }
    private function error() {
        //echo 'error';
        header('Location: '.u_sroot.$this->uCore->mod.'/articles');
    }

    private function increase_views_counter($site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
            u235_articles
            SET
            views_counter=views_counter+1
            WHERE
            art_id=:art_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':art_id', $this->art_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1583243506'/*.$e->getMessage()*/);}
    }

    private function uDrive_get_new_file_id() {
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$query=$this->uCore->query("uDrive","SELECT
            `file_id`
            FROM
            `u235_files`
            WHERE
            `site_id`='".site_id."'
            ORDER BY
            `file_id` DESC
            LIMIT 1
            ")) $this->uFunc->error(10);
        if(mysqli_num_rows($query)) {
            /** @noinspection PhpUndefinedMethodInspection */
            $qr=$query->fetch_object();
            return $qr->file_id+1;
        }
        return 1;
    }
    private function get_uDrive_folder_id() {
        //define uDrive art default folder
        if($this->art->uDrive_folder_id=='0') {
            //get uCat_arts folder_id
            /** @noinspection PhpUndefinedMethodInspection */
            if(!$query=$this->uCore->query("uDrive","SELECT
            `folder_id`
            FROM
            `u235_mod_folders`
            WHERE
            `module`='uCat_arts' AND
            `site_id`='".site_id."'
            ")) $this->uFunc->error(20);
            if(mysqli_num_rows($query)) {
                /** @noinspection PhpUndefinedMethodInspection */
                $qr=$query->fetch_object();
                $uCat_arts_folder_id=$qr->folder_id;
            }
            else {//set new uCat_arts folder_id
                //get uCat folder_id
                /** @noinspection PhpUndefinedMethodInspection */
                if(!$query=$this->uCore->query("uDrive","SELECT
                `folder_id`
                FROM
                `u235_mod_folders`
                WHERE
                `module`='uCat' AND
                `site_id`='".site_id."'
                ")) $this->uFunc->error(30);
                if(mysqli_num_rows($query)) {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $qr=$query->fetch_object();
                    $uCat_folder_id=$qr->folder_id;
                }
                else {
                    $uCat_folder_id=$this->uDrive_get_new_file_id();
                    /** @noinspection PhpUndefinedMethodInspection */
                    if(!$this->uCore->query("uDrive","INSERT INTO
                    `u235_files` (
                    `file_id`,
                    `file_name`,
                    `file_mime`,
                    `file_timestamp`,
                    `folder_id`,
                    `owner_id`,
                    `file_protected`,
                    `site_id`
                    ) VALUES (
                    '".$uCat_folder_id."',
                    'MAD Каталог',
                    'folder',
                    '".time()."',
                    '0',
                    '0',
                    '1',
                    '".site_id."'
                    )")) $this->uFunc->error(40);
                    /** @noinspection PhpUndefinedMethodInspection */
                    if(!$this->uCore->query("uDrive","INSERT INTO
                    `u235_mod_folders` (
                    `module`,
                    `folder_id`,
                    `site_id`
                    ) VALUES (
                    'uCat',
                    '".$uCat_folder_id."',
                    '".site_id."'
                    )")) $this->uFunc->error(50);
                }

                //get new uCat_arts folder_id
                $uCat_arts_folder_id=$this->uDrive_get_new_file_id();
                /** @noinspection PhpUndefinedMethodInspection */
                if(!$this->uCore->query("uDrive","INSERT INTO
                `u235_files` (
                `file_id`,
                `file_name`,
                `file_mime`,
                `file_timestamp`,
                `folder_id`,
                `owner_id`,
                `file_protected`,
                `site_id`
                ) VALUES (
                '".$uCat_arts_folder_id."',
                'Статьи',
                'folder',
                '".time()."',
                '".$uCat_folder_id."',
                '0',
                '1',
                '".site_id."'
                )")) $this->uFunc->error(60);
                /** @noinspection PhpUndefinedMethodInspection */
                if(!$this->uCore->query("uDrive","INSERT INTO
                `u235_mod_folders` (
                `module`,
                `folder_id`,
                `site_id`
                ) VALUES (
                'uCat_arts',
                '".$uCat_arts_folder_id."',
                '".site_id."'
                )")) $this->uFunc->error(70);
            }

            //set new uDrive_folder_id for art
            $art_title=trim(uString::sanitize_filename(uString::sql2text($this->art->art_title)));
            if(!strlen($art_title)) $art_title='Раздел '.$this->art_id;
            //get new art folder_id (uDrive_folder_id)
            $this->art->uDrive_folder_id=$this->uDrive_get_new_file_id();
            /** @noinspection PhpUndefinedMethodInspection */
            if(!$this->uCore->query("uDrive","INSERT INTO
            `u235_files` (
            `file_id`,
            `file_name`,
            `file_mime`,
            `file_timestamp`,
            `folder_id`,
            `owner_id`,
            `file_protected`,
            `site_id`
            ) VALUES (
            '".$this->art->uDrive_folder_id."',
            '".uString::text2sql($art_title)."',
            'folder',
            '".time()."',
            '".$uCat_arts_folder_id."',
            '0',
            '1',
            '".site_id."'
            )")) $this->uFunc->error(80);

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$this->uCore->query("uCat","UPDATE
            `u235_articles`
            SET
            `uDrive_folder_id`='".$this->art->uDrive_folder_id."'
            WHERE
            `art_id`='".$this->art_id."' AND
            `site_id`='".site_id."'
            ")) $this->uFunc->error(90);
        }
    }
    private function getArticle() {
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$query=$this->uCore->query("uCat","SELECT
        `art_id`,
        `art_title`,
        `art_text`,
        `art_avatar_time`,
        `art_author`,
        `uDrive_folder_id`,
        views_counter
        FROM
        `u235_articles`
        WHERE
        `u235_articles`.`art_id`='".$this->art_id."' AND
        `u235_articles`.`site_id`='".site_id."'
        ")) $this->uFunc->error(100);
        if(!mysqli_num_rows($query)) $this->error();
        /** @noinspection PhpUndefinedMethodInspection */
        $this->art=$query->fetch_object();
        $this->get_uDrive_folder_id();
    }
    private function get_attached_items() {
        /** @noinspection PhpUndefinedMethodInspection */
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT 
            u235_articles_items.item_id,
            item_title,
            item_url
            FROM 
            u235_articles_items
            JOIN
            u235_items
            ON
            u235_items.item_id=u235_articles_items.item_id AND
            u235_items.site_id=u235_articles_items.site_id
            WHERE 
            u235_articles_items.art_id=:art_id AND
            u235_articles_items.site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':art_id', $this->art_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            for($i=0;$this->articles_items[$i]=$stm->fetch(PDO::FETCH_OBJ);$i++);
            unset($this->articles_items[$i]);
        }
        catch(PDOException $e) {$this->uFunc->error('110'/*.$e->getMessage()*/);}
    }

    private function get_items_cat($item_id) {
        if(!$query=$this->uCore->query("uCat","SELECT
        `u235_cats`.`cat_id`,
        `cat_url`,
        `cat_title`
        FROM
        `u235_cats_items`,
        `u235_cats`
        WHERE
        `u235_cats_items`.`cat_id`=`u235_cats`.`cat_id` AND
        `u235_cats_items`.`site_id`='".site_id."' AND
        `u235_cats`.`site_id`='".site_id."' AND
        `u235_cats_items`.`item_id`='".$item_id."'
        LIMIT 1
        ")) $this->uFunc->error(120);
        return $query->fetch_object();
    }
    private function get_cats_sect($cat_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            u235_sects_cats.sect_id,
            sect_url,
            sect_title
            FROM
            u235_sects_cats
            JOIN 
            u235_sects
            ON
            u235_sects_cats.sect_id=u235_sects.sect_id AND
            u235_sects_cats.site_id=u235_sects.site_id
            WHERE
            u235_sects_cats.site_id=:site_id AND
            u235_sects_cats.cat_id=:cat_id
            LIMIT 1
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('130'/*.$e->getMessage()*/);}
    }
    private function define_breadcrumb() {
        $this->uCore->uBc->add_info->html='';
        //attached item
        for($i=0;$i<count($this->articles_items);$i++) {
            $item = $this->articles_items[$i];
            if ($item) {//get item's cat
                $cat = $this->get_items_cat($item->item_id);
                if ($cat) {//get cat's sect
                    $sect = $this->get_cats_sect($cat->cat_id);
                    if ($sect) {
                        $this->uCore->uBc->add_info->html .= '<li><a href="' . u_sroot . $this->uCore->mod . '/cats/' . (strlen($sect->sect_url) ? uString::sql2text($sect->sect_url) : $sect->sect_id) . '">' . uString::sql2text($sect->sect_title, 1) . '</a></li>';
                    }
                    $this->uCore->uBc->add_info->html .= '<li><a href="' . u_sroot . $this->uCore->mod . '/items/' . (strlen($cat->cat_url) ? uString::sql2text($cat->cat_url) : $cat->cat_id) . '">' . uString::sql2text($cat->cat_title, 1) . '</a></li>';
                }
                $this->uCore->uBc->add_info->html .= '<li><a href="' . u_sroot . $this->uCore->mod . '/item/' . (strlen($item->item_url) ? uString::sql2text($item->item_url) : $item->item_id) . '">' . uString::sql2text($item->item_title, 1) . '</a></li>';
            }
        }
        $this->uCore->uBc->add_info->html.='<li class="active"><a id="uCat_art_breadcrumb" href="'.u_sroot.$this->uCore->mod.'/article/'.$this->art->art_id.'">'.uString::sql2text($this->art->art_title,1).'</a></li>';
    }
}
$uCat=new uCat_article($this);

$this->page['page_title']=uString::sql2text($uCat->art->art_title).'. '.$this->uFunc->getConf("art_label","uCat");

$this->uFunc->incJs(u_sroot.'uCat/js/article_admin.min.js');

ob_start();
?>

<?if(!$uCat->content_only) {?>
<div class="uCat article">
    <h1 class="page-header"><span id="uCat_art_title"><?=uString::sql2text($uCat->art->art_title)?></span> <?if($this->access(25)){?><button class="u235_eip uTooltip btn btn-primary btn-sm" title="Редактировать заголовок статьи" onclick="uCat.art_title_dg_init()"><span class="glyphicon glyphicon-pencil"></span></button>
            <button class="u235_eip uTooltip btn btn-primary btn-sm" title="Редактировать главное изображение статьи" onclick="uCat.change_avatar()"><span class="glyphicon glyphicon-picture"></span></button>
            <button class="btn btn-primary btn-sm uTooltip u235_eip" onclick="uCat.attach_items('attach')">Прикрепить товары</button>
        <?}?><small id="uCat_art_items"><?
                for ($i = 0; $i < count($uCat->articles_items); $i++) {
                    $item = $uCat->articles_items[$i];
                    if ($i) echo '. ';
                    else echo '<br>'; ?>
                    <a href="<?= u_sroot?>uCat/item/<?= $item->item_id ?>"><?= uString::sql2text($item->item_title) ?></a>
                    <?
                }
        ?></small></h1>
    <p class="text-muted"><span id="uCat_article_author_label" <?if(trim($uCat->art->art_author)=='') {?>style="display: none;"<?}?>>Автор: </span>
        <span id="uCat_article_art_author"><?=uString::sql2text($uCat->art->art_author,1)?></span>
        <button class="u235_eip btn btn-default btn-sm" onclick="uCat.art_author_dg_init()"><span class="glyphicon glyphicon-pencil"></span> Указать автора</button>
    </p>
    <?
    $art_avatar=$uCat->art_avatar->get_avatar('art_page',$uCat->art_id,$uCat->art->art_avatar_time);?>
        <div class="pull-left img <?=!$art_avatar?'hidden':''?>" id="uCat_art_avatar_container">
            <img class="img-responsive" id="uCat_art_avatar" src="<?=$art_avatar?$art_avatar:''?>">
        </div>
    <div class="info">
<div id="uCat_art_text">
<?}?>
<?=uString::sql2text($uCat->art->art_text,true)?>
<?if(!$uCat->content_only) {?>
    </div>
    </div>
    <button class="pull-right btn btn-danger btn-sm uTooltip u235_eip" onclick="uCat.delete_art()">Удалить статью</button>

    <div id="item_views_counter" class="text-muted pull-right"><span class="icon-eye"></span> <?=$uCat->art->views_counter ?></div>
</div>
<?}?>

<?if(!$uCat->content_only) {
    if($this->access(25)) {
        /** @noinspection PhpIncludeInspection */
        include_once 'uDrive/inc/my_drive_manager.php';?>
        <div id="uDrive_my_drive_uploader_init"></div>
        <script type="text/javascript">
            if(typeof uCat_article_admin==="undefined") uCat_article_admin={};
            if(typeof uDrive_manager==="undefined") uDrive_manager={};

            uCat_article_admin.uDrive_folder_id=<?=$uCat->art->uDrive_folder_id;?>;
            $(document).ready(function() {
                uDrive_manager.init('uDrive_my_drive_uploader',<?=$uCat->art->uDrive_folder_id;?>, 1, "uCat_article_admin.insert_tinymce_url", 'uCat', 'art',<?=$uCat->art_id?>);
            });
        </script>
        <?
        //tinymce
        $this->uFunc->incJs(u_sroot.'js/tinymce/tinymce.min.js');?>
        <script type="text/javascript">
            if(typeof uCat==="undefined") uCat={};

            uCat.art_id=<?=$uCat->art_id?>;
        </script>
        <?include_once 'dialogs/art_admin.php';
    }
    ?>
<?$this->page_content=ob_get_contents();
ob_end_clean();

    /** @noinspection PhpIncludeInspection */
    include "templates/template.php";
}
else {
    $txt=ob_get_contents();
    ob_end_clean();
    echo $txt;
}
