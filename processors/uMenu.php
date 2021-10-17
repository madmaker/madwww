<?php
use processors\uFunc;

require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';
class uMenu {
    private $uSes;
    private $uFunc;
    private $uCore;
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
    }

    private function makeLink($link) {
        $link=trim($link);
        if(empty($link)) return false;
        return $link;
    }
    private function get($page) {
        $require=" 1=0 ";
        if(strpos($page,"s")===0) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("pages")->prepare("SELECT
			  navi_personal_menu,
			  navi_parent_page_id,
			  page_id
			  FROM
			  u235_pages_html
			  WHERE
			  site_id=:site_id AND
			  page_id=:page_id
			  ");
                $page_id=str_replace("s","",$page);
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('processors/uMenu 10'/*.$e->getMessage()*/);}

            /** @noinspection PhpUndefinedMethodInspection */
            if($data=$stm->fetch(PDO::FETCH_OBJ)) {

                $data->navi_personal_menu=(int)$data->navi_personal_menu;
                if(!$data->navi_personal_menu) $require.=" OR ".$this->get($data->navi_parent_page_id);

                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm1=$this->uFunc->pdo("uNavi")->prepare("SELECT
					cat_id
					FROM
					u235_pagemenu
					WHERE
					site_id=:site_id AND
					page_id=:page_id
					");
                    $page_id="s".$data->page_id;
                    $site_id=site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_id', $page_id,PDO::PARAM_STR);
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('processors/uMenu 20'/*.$e->getMessage()*/);}

                /** @noinspection PhpUndefinedMethodInspection */
                while($category=$stm1->fetch(PDO::FETCH_OBJ)) {
                    $category=$category->cat_id;
                    $require.=" OR `cat_id`='".$category."' ";
                }
            }
        }
        elseif(strpos($page,"p")===0) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
			  navi_personal_menu,
			  navi_parent_page_id,
			  page_id
			  FROM
			  u235_pages
			  WHERE
			   site_id=:site_id AND
			  page_id=:page_id
			  ");
                $page_id=str_replace("p","",$page);
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('processors/uMenu 30'/*.$e->getMessage()*/);}

            /** @noinspection PhpUndefinedMethodInspection */
            if($data=$stm->fetch(PDO::FETCH_OBJ)) {
                $data->navi_personal_menu=(int)$data->navi_personal_menu;
                if(!$data->navi_personal_menu) $require.=" OR ".$this->get($data->navi_parent_page_id);

                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm1=$this->uFunc->pdo("uNavi")->prepare("SELECT
					cat_id
					FROM
					u235_pagemenu
					WHERE
					site_id=:site_id AND
					page_id=:page_id
					");
                    $page_id="p".$data->page_id;
                    $site_id=site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_id', $page_id,PDO::PARAM_STR);
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('processors/uMenu 40'/*.$e->getMessage()*/);}

                /** @noinspection PhpUndefinedMethodInspection */
                while($category=$stm1->fetch(PDO::FETCH_OBJ)) {
                    $category=$category->cat_id;
                    $require.=" OR `cat_id`='".$category."' ";
                }
            }
        }
        else {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("pages")->prepare("SELECT
			  navi_personal_menu,
			  navi_parent_page_id,
			  page_id
			  FROM
			  u235_pages_list
			  WHERE
			  page_id=:page_id
			  ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('processors/uMenu 50'/*.$e->getMessage()*/);}

            /** @noinspection PhpUndefinedMethodInspection */
            if($data=$stm->fetch(PDO::FETCH_OBJ)) {
                $data->navi_personal_menu=(int)$data->navi_personal_menu;
                if(!$data->navi_personal_menu&&$data->navi_parent_page_id!="mainpage"&&$data->navi_parent_page_id!="") $require.=" OR ".$this->get($data->navi_parent_page_id);
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm1=$this->uFunc->pdo("uNavi")->prepare("SELECT
					cat_id
					FROM
					u235_pagemenu
					WHERE
					site_id=:site_id AND
					page_id=:page_id
					");
                    $page_id=$data->page_id;
                    $site_id=site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_id', $page_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('processors/uMenu 60'/*.$e->getMessage()*/);}

                /** @noinspection PhpUndefinedMethodInspection */
                while($cat_id=$stm1->fetch(PDO::FETCH_OBJ)) {
                    $cat_id=$cat_id->cat_id;
                    $require.=" OR `cat_id`='".$cat_id."' ";
                }
            }
        }
        return $require;
    }
    /**deprecated. Use insert_menu*/public function insert($cattype) {
        if($this->uCore->mod=="page") $require=$this->get("s".$this->uCore->page['page_id']);
        elseif($this->uCore->mod=="uPage") $require=$this->get("p".$this->uCore->page['page_id']);
        else $require=$this->get($this->uCore->page['page_id']);

        for($i=1;$i<=count($this->uCore->url_prop);$i++) {//Смотрим, нет ли в переменных данных по открытым меню
            if(isset($this->uCore->url_prop[$i])) {
                if(preg_match('#^m\.([\d]+[\.]*)+$#',$this->uCore->url_prop[$i])) {
                    $menuString=explode('.',str_replace("m.","",$this->uCore->url_prop[$i]));
                    for($j=0;$j<count($menuString);$j++) $menuAr[$menuString[$j]]=true;
                }
            }
        }

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare("SELECT
            cat_id,
            cat_type,
            cat_access,
            cat_title
            FROM
            u235_cats
            WHERE
            site_id=:site_id AND
            cat_type=:cat_type AND
            (status='' OR status is NULL) AND
            (".$require.")
            ORDER BY
            cat_title ASC
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_type', $cattype,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            for($i=0;$data=$stm->fetch(PDO::FETCH_OBJ);$i++) {
                $elements[$i]=$data;
            }

            if(isset($elements[$i-1]->cat_id)) {
                $cnt = '<div class="uNavi_menu uNavi_menu_' . $elements[$i - 1]->cat_id . '">
                <button 
                id="uNavi_edit_btn_' . $elements[$i - 1]->cat_id . '"
                type="button" 
                style="display:none" 
                class="btn btn-primary uNavi_edit_btn /*u235_eip*/ uTooltip" 
                title="' . $this->text("Edit menu - btn text"/*Редактировать меню*/) . '" 
                onclick="uNavi_eip.edit_menu(' . $elements[$i - 1]->cat_id . ')"
                ><span class="glyphicon glyphicon-pencil"></span></button>';
            }
            else $cnt='<div class="uNavi_menu">';
        }
        catch(PDOException $e) {$this->uFunc->error('processors/uMenu 80'/*.$e->getMessage()*/);}

        if(!isset($elements)) return "";
        if($cattype=='3') {
            $cnt.='<div class="row">';
            $rows_count=count($elements);
            $row_width=(int)(12/$rows_count);
        }
        for($i=0;$i<count($elements);$i++) {
            if($this->uSes->access($elements[$i]->cat_access)) {
                if($cattype=='3') {
                    $cnt.='<div class="col-md-'.$row_width.' col-sm-'.(int)(24/$rows_count).'">';
                    $cnt.='<h3 class="menu_title">'.uString::sql2text($elements[$i]->cat_title,true).'</h3>';
                }
                $cnt.=$this->return_cat_type_content($elements[$i]->cat_type,$elements[$i]->cat_id);
                if($cattype=='3') {
                    $cnt.='</div>';
                }
            }
        }
        if($cattype=='3') {
        $cnt.='</div>';
        }
        $cnt.='</div>';

        return $cnt;
    }
    public function insert_menu($cat_id) {
        $cnt='<div id="uNavi_menu_container_'.$cat_id.'" class="uNavi_menu uNavi_menu_container uNavi_menu_'.$cat_id.'" data-cat_id="'.$cat_id.'">';

        $cnt.='<button
        id="uNavi_edit_btn_' . $cat_id . '" 
        type="button" 
        style="display:none" 
        class="btn btn-primary uNavi_edit_btn /*u235_eip */uTooltip" 
        title="'.$this->text("Edit menu - btn text"/*Редактировать меню*/).'" 
        onclick="uNavi_eip.edit_menu('.$cat_id.')"
        ><span class="glyphicon glyphicon-pencil"></span></button>';


        for($i=1;$i<=count($this->uCore->url_prop);$i++) {//Смотрим, нет ли в переменных данных по открытым меню//TODO-nik87 А это где-то используется вообще?
            if(isset($this->uCore->url_prop[$i])) {
                if(preg_match('#^m\.([\d]+[\.]*)+$#',$this->uCore->url_prop[$i])) {
                    $menuString=explode('.',str_replace("m.","",$this->uCore->url_prop[$i]));
                    for($j=0;$j<count($menuString);$j++) $menuAr[$menuString[$j]]=true;
                }
            }
        }

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare("SELECT
            cat_type,
            cat_access,
            cat_title
            FROM
            u235_cats
            WHERE
            site_id=:site_id AND
            `status` IS NULL AND
            cat_id=:cat_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            $cat=$stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('processors/uMenu 90'/*.$e->getMessage()*/);}

        if(!isset($cat)) return "";
        if(!$cat) return "";
        $cat->cat_type=(int)$cat->cat_type;

        if($this->uSes->access($cat->cat_access)) {
            if($cat->cat_type=='3') {
                $cnt.='<h3 class="menu_title">'.$cat->cat_title.'</h3>';
            }
            $cnt.=$this->return_cat_type_content($cat->cat_type,$cat_id);
        }
        $cnt.='</div>';

        return $cnt;
    }
    public function return_cat_id_content($cat_id,$list_only=false) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare("SELECT
            cat_type,
            cat_title
            FROM
            u235_cats
            WHERE
            cat_id=:cat_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) return '';
            return $this->return_cat_type_content($qr->cat_type,$cat_id,$list_only);
        }
        catch(PDOException $e) {$this->uFunc->error('processors/uMenu 100'/*.$e->getMessage()*/);}
        return "";
    }
    private function return_cat_type_content($cat_type,$cat_id,$list_only=false) {
        if($this->uCore->mod=='uPage') $this->uCore->page['navi_parent_menu_id']=0;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare("SELECT
                title,
                link,
                access,
                id,
                target,
                position,
                indent,
                icon_regular_filename,
                icon_hover_filename,
                timestamp,
                show_label,
                is_system_btn
                FROM
                u235_menu
                WHERE
                status=:status AND
                cat_id=:cat_id AND
                site_id=:site_id
                ORDER BY
                position ASC
                ");
            $site_id=site_id;
            $status="";
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':status', $status,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('processors/uMenu 110'/*.$e->getMessage()*/);}

        if($cat_type==1) {//Горизонтальное меню справа в шапке Axis
            ob_start();?>

            <ul class="nav navbar-nav navbar-right cat<?=$cat_type?> uNavi_cat_id_<?=$cat_id?> uNavi_cat">
                <?
                $indent=0;
                /** @noinspection PhpUndefinedMethodInspection */$data1=$stm->fetch(PDO::FETCH_OBJ);
                $last_id=0;
                for($first=true;$data1;$first=false) {
                /** @noinspection PhpUndefinedMethodInspection */$data2=$stm->fetch(PDO::FETCH_OBJ);
                if($this->uSes->access($data1->access)) {
                $data1->link=uString::sql2text($data1->link);
                $data1->link=$this->makeLink($data1->link);

                while($data1->indent>$indent) {;
                $indent++;?>
                <ul class="dropdown-menu">
                    <?}
                    while($data1->indent<$indent) {
                    $indent--;?>
                </ul>
            <?}
            if(!$first) echo '</li>';

            $active=false;
            if(!strlen(trim($data1->link))) {if($_SERVER['REQUEST_URI']=='/') $active=true;}
            else {
                if($_SERVER['REQUEST_URI']=='/'&&$data1->link=='mainpage/index') $active=true;
                elseif(
                    strpos($_SERVER['REQUEST_URI'],$data1->link)===0||
                    strpos($_SERVER['REQUEST_URI'],'/'.$data1->link)===0||
                    strpos($_SERVER['REQUEST_URI'],str_replace(u_sroot,'/',$data1->link))===0||
                    strpos($_SERVER['REQUEST_URI'],str_replace(u_sroot,'',$data1->link))===0
                ) $active=true;
            }
            $last_id=$data1->id;?>

                <li class="uNavi_item uNavi_item_id_<?=$data1->id?>
                            <?if($data2->indent>$indent) {?> dropdown <?}
                            if($data2->indent>1) {?> dropdown-submenu <?}
                if($this->uCore->page['navi_parent_menu_id']==$data1->id) {?> active <?}
                if($active) {?> active <?}?>
                            ">
                    <a
                    class="<?=($active?' active ':'').''.(($data2->indent>$indent)?' dropdown-toggle':'')?>"
                    <?if(isset($data2->indent)) {
                        if($data2->indent>$indent) echo 'data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true"';
                    }?>
                    href="<?=($data1->link)?$data1->link:'#'?>"
                    target="<?=$data1->target?>"
                    data-href=""
                    data-item_id="<?=$data1->id?>"
                    data-onclick=""
                    ><?=(int)$data1->show_label?uString::sql2text($data1->title,true):''?>
                    <?if(isset($data2->indent)) if($data2->indent>$indent) echo '<span class="caret"></span>'?>
                    </a>
                    <?
                    }
                    $data1=$data2;
                    }?>
                </li>
            </ul>

            <script type="text/javascript">
                jQuery(".uNavi_item_id_<?=$last_id?>").addClass('last');
            </script>
            <?
            $cnt=ob_get_contents();
            ob_end_clean();
        }
        elseif($cat_type==2) {//Меню-список в подвале на бамусе, на странице на политехе
            $cnt='<ul class="cat2 uNavi_cat_id_'.$cat_id.' uNavi_cat">';
            $indent=0;
            /** @noinspection PhpUndefinedMethodInspection */while($data1=$stm->fetch(PDO::FETCH_OBJ)) {
                if($this->uSes->access($data1->access)) {
                    $data1->link=uString::sql2text($data1->link);
                    $data1->link=$this->makeLink($data1->link);
                    while($data1->indent>$indent) {
                        $indent++;
                        $cnt.='<li><ul>';
                    }
                    while($data1->indent<$indent) {
                        $indent--;
                        $cnt.='</ul></li>';
                    }

                    $active=false;
                    if(empty($data1->link)) {
                        if($_SERVER['REQUEST_URI']=='/') $active=true;
                    }
                    else {
                        if($_SERVER['REQUEST_URI']=='/'&&$data1->link=='mainpage/index') $active=true;
                        elseif(
                            strpos($_SERVER['REQUEST_URI'],$data1->link)===0||
                            strpos($_SERVER['REQUEST_URI'],'/'.$data1->link)===0||
                            strpos($_SERVER['REQUEST_URI'],str_replace(u_sroot,'/',$data1->link))===0||
                            strpos($_SERVER['REQUEST_URI'],str_replace(u_sroot,'',$data1->link))===0
                        ) $active=true;
                    }

                    $cnt.='<li  class="uNavi_item_id_'.$data1->id.(($this->uCore->page['navi_parent_menu_id']==$data1->id||$active)?' active':'').' uNavi_item">
                    <a 
                    '.(($data1->link)?('href="'.$data1->link.'"'):'').' 
                    target="'.$data1->target.'"
                    data-href=""
                    data-item_id="'.$data1->id.'"
                    data-onclick=""
                    >'.((int)$data1->show_label?uString::sql2text($data1->title,true):'').'</a>
                    </li>'."\n";
                }
            }
            while($indent>0) {
                $indent--;
                $cnt.='</ul></li>';
            }
            $cnt.='</ul>';
        }
        elseif($cat_type==3) {//Тоже самое, что и 2. Просто на одной странцие и 2 и 3 с разными пунктами меню можно отображать.
            $cnt='<ul class="cat3 uNavi_cat_id_'.$cat_id.' uNavi_cat">';
            $indent=0;
            /** @noinspection PhpUndefinedMethodInspection */while($data1=$stm->fetch(PDO::FETCH_OBJ)) {
                if($this->uSes->access($data1->access)) {
                    $data1->link=uString::sql2text($data1->link);
                    $data1->link=$this->makeLink($data1->link);
                    while($data1->indent>$indent) {
                        $indent++;
                        $cnt.='<li><ul>';
                    }
                    while($data1->indent<$indent) {
                        $indent--;
                        $cnt.='</ul></li>';
                    }
                    $active=false;
                    if(empty($data1->link)) {
                        if($_SERVER['REQUEST_URI']=='/') $active=true;
                    }
                    else {
                        if($_SERVER['REQUEST_URI']=='/'&&$data1->link=='mainpage/index') $active=true;
                        elseif(
                            strpos($_SERVER['REQUEST_URI'],$data1->link)===0||
                            strpos($_SERVER['REQUEST_URI'],'/'.$data1->link)===0||
                            strpos($_SERVER['REQUEST_URI'],str_replace(u_sroot,'/',$data1->link))===0||
                            strpos($_SERVER['REQUEST_URI'],str_replace(u_sroot,'',$data1->link))===0
                        ) $active=true;
                    }

                    $cnt.='<li class="'.(!$data1->link?'no_link_el':'').' uNavi_item_id_'.$data1->id.(($this->uCore->page['navi_parent_menu_id']==$data1->id||$active)?' active':'').' uNavi_item">
                    <a 
                    '.(($data1->link)?('href="'.$data1->link.'"'):'').' 
                    target="'.$data1->target.'"
                    data-href=""
                    data-item_id="'.$data1->id.'"
                    data-onclick=""
                    >'.((int)$data1->show_label?uString::sql2text($data1->title,true):'').'</a>
                    </li>'."\n";
                }
            }
            while($indent>0) {
                $indent--;
                $cnt.='</ul></li>';
            }
            $cnt.='</ul>';
        }
        elseif($cat_type==4) {//Верхнее выпадающее меню на большинстве сайтов
        ob_start();
            if(!$list_only) {
        //TODO-nik87 Здесь как-то криво закрывается </li> Сначала открывается <ul class="dropdown-menu">, а потом сразу за этой строчкой идет </li>. Причем этот баг в верстке сайтов делает проблему.
            ?>
<nav class="navbar">

            <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar_<?=$cat_id?>" aria-expanded="false" aria-controls="navbar" style="font-size: 1.5em;">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-menu"></span>
          </button>
        </div>

<div id="navbar_<?=$cat_id?>" class="navbar-collapse collapse">
        <?}?>
            <ul class="nav <?=site_id==6?('nav-justified'):('navbar-nav  cat'.$cat_type)?>  uNavi_cat_id_<?=$cat_id?>"><?//TODO-nik87 Здесь костыль для сайта istorex - для них отдельный тип меню нужно сделать просто?>
                <?
                $indent=0;
                $last_id=0;

                /** @noinspection PhpStatementHasEmptyBodyInspection */
                /** @noinspection PhpUndefinedMethodInspection */
                for($i=0;$data[$i]=$stm->fetch(PDO::FETCH_OBJ);$i++) {
                    //do nothing
                };

                for($i=0;$data[$i];$i++) {

                    if($data[$i+1]) $next_indent=$data[$i+1]->indent;
                    else $next_indent=$data[$i]->indent;

                    if($this->uSes->access($data[$i]->access)) {
                    $data[$i]->link=uString::sql2text($data[$i]->link);
                    $data[$i]->link=$this->makeLink($data[$i]->link);

                    while($data[$i]->indent>$indent) {
                        $indent++;
                        if ($data[$i]->indent >= 0) {
                           echo '<ul class="dropdown-menu">';
                        }?>
                    <?}

                    while($data[$i]->indent<$indent) {
                        $indent--;
                        echo "</ul>";?>
                    <?}

                    $active=false;
                    if(!strlen(trim($data[$i]->link))) {if($_SERVER['REQUEST_URI']=='/') $active=true;}
                    else {
                        if($_SERVER['REQUEST_URI']=='/'&&$data[$i]->link=='mainpage/index') $active=true;
                        elseif(
                            strpos($_SERVER['REQUEST_URI'],$data[$i]->link)===0||
                            strpos($_SERVER['REQUEST_URI'],'/'.$data[$i]->link)===0||
                            strpos($_SERVER['REQUEST_URI'],str_replace(u_sroot,'/',$data[$i]->link))===0||
                            strpos($_SERVER['REQUEST_URI'],str_replace(u_sroot,'',$data[$i]->link))===0
                        ) $active=true;
                    }
                    $last_id=$data[$i]->id;
                    ?>

                    <?
                    if((int)$data[$i]->is_system_btn===1) {//Кнопка каталога - Личный кабинет
                        if($this->uSes->access(2)) {?>
                        <li class="dropdown uNavi_item uNavi_item_id_<?=$data[$i]->id?>">
                            <a
                            href="#"
                            class="dropdown-toggle"
                            data-toggle="dropdown"
                            role="button"
                            aria-haspopup="true"
                            aria-expanded="false"
                            data-href=""
                            data-item_id="<?=$data[$i]->id?>"
                            data-onclick=""
                            ><span class="icon-user"></span> <?=(int)$data[$i]->show_label?uString::sql2text($data[$i]->title,true):''?> <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li>
                                    <!--suppress HtmlUnknownTarget -->
                                    <a
                                    href="/uAuth/profile"
                                    data-href=""
                                    data-item_id="<?=$data[$i]->id?>"
                                    data-onclick=""
                                    ><?
                                        echo $this->uSes->get_val("firstname");
                                        echo " ";
                                        echo $this->uSes->get_val("lastname");
                                        ?>
                                    </a>
                                </li>
                                <!--<li><a>Код клиента: <b><?/*=$this->uSes->get_val("user_id")*/?></b></a></li>-->
                                <li><!--suppress HtmlUnknownTarget -->
                                <a href="/uCat/my_orders">Мои заказы</a></li>
                            </ul>
                         </li>
                         <li class="uNavi_item uNavi_item_id_<?=$data[$i]->id?>">
                            <a href="<?=u_sroot?>uAuth/logout"><span class="icon-logout"></span> Выход</a>
                        <!--</li>-->
                        <?} else {?>
                            <li class="uNavi_item uNavi_item_id_<?=$data[$i]->id?>"><a
                            href="<?=u_sroot?>uCat/what_is_with_my_order"
                            data-href=""
                            data-item_id="<?=$data[$i]->id?>"
                            data-onclick=""
                            >Что с моим заказом?</a></li>
                            <li class="uNavi_item uNavi_item_id_<?=$data[$i]->id?>">
                                <!--suppress HtmlUnknownAnchorTarget -->
                                <a
                                href="javascript:void(0)"

                                onclick="uAuth_form.open()"
                                data-href=""
                                data-item_id="<?=$data[$i]->id?>"
                                data-onclick=""
                                ><span class="icon-login"></span> Вход/Регистрация</span></a>
                            <!--</li>-->
                        <?}
                    }
                    elseif((int)$data[$i]->is_system_btn===2) {//Кнопка объявлений- Личный кабинет
                        if($this->uSes->access(2)) {?>
                        <li class="dropdown uNavi_item uNavi_item_id_<?=$data[$i]->id?>">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="icon-user"></span> <?=(int)$data[$i]->show_label?uString::sql2text($data[$i]->title,true):''?> <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li>
                                    <!--suppress HtmlUnknownTarget -->
                                    <a
                                    href="/uAuth/profile"
                                    data-href=""
                                    data-item_id="<?=$data[$i]->id?>"
                                    data-onclick=""
                                    ><?
                                        echo $this->uSes->get_val("firstname");
                                        echo " ";
                                        echo $this->uSes->get_val("lastname");
                                        ?>
                                    </a>
                                </li>
                                <!--<li><a>Код клиента: <b><?/*=$this->uSes->get_val("user_id")*/?></b></a></li>-->
                                <li><!--suppress HtmlUnknownTarget -->
                                <a
                                href="/advert/my_ads"
                                data-href=""
                                data-item_id="<?=$data[$i]->id?>"
                                data-onclick=""
                                >Мои обмены</a></li>
                            </ul>
                         </li>
                         <li class="uNavi_item uNavi_item_id_<?=$data[$i]->id?>">
                            <a
                            href="<?=u_sroot?>uAuth/logout"
                            data-href=""
                            data-item_id="<?=$data[$i]->id?>"
                            data-onclick=""
                            ><span class="icon-logout"></span> Выход</a>
                        <!--</li>-->
                        <?} else {?>
<!--                            <li class="uNavi_item uNavi_item_id_--><?//=$data[$i]->id?><!--"><a href="--><?//=u_sroot?><!--uCat/what_is_with_my_order">Что с моим заказом?</a></li>-->
                            <li class="uNavi_item uNavi_item_id_<?=$data[$i]->id?>">
                                <!--suppress HtmlUnknownAnchorTarget -->
                                <a
                                href="javascript:void(0)"

                                onclick="uAuth_form.open()"
                                data-href=""
                                data-item_id="<?=$data[$i]->id?>"
                                data-onclick=""
                                ><span class="icon-login"></span> Вход/Регистрация</span></a>
                            <!--</li>-->
                        <?}
                    }

                    else {?>

                        <li class="indent_<?=$data[$i]->indent?> cur_indent_<?=$indent;?> uNavi_item uNavi_item_id_<?=$data[$i]->id?>
                            <?if($next_indent>$data[$i]->indent) {
                            if($next_indent>1) {?> dropdown-submenu <?}?>
                            dropdown <?}
                        if($this->uCore->page['navi_parent_menu_id']==$data[$i]->id) {?> active <?}
                        if($active) {?><?}?>
                            ">
                            <a
                            class=" <?=$active?'':''?> <?=($next_indent>$data[$i]->indent)?' dropdown-toggle':''?>"
                            <?=($next_indent>$data[$i]->indent)?'aria-haspopup="true" data-toggle="dropdown"':''?>
                            href="<?=($data[$i]->link)?$data[$i]->link:''?>"
                            target="<?=$data[$i]->target?>"
                            data-href=""
                            data-item_id="<?=$data[$i]->id?>"
                            data-onclick=""
                            >
                                <?=(int)$data[$i]->show_label?uString::sql2text($data[$i]->title,true):''?> <?=($next_indent>$data[$i]->indent)?'<span class="caret"></span>':''?>
                            </a>
                            <?
                        }

                    }
                }?>
                </li>
            </ul>
            <?if(!$list_only) {?>
            </div>

            <script type="text/javascript">
                jQuery(".uNavi_item_id_<?=$last_id?>").addClass('last');
            </script>
            </nav>
            <?}
            $cnt=ob_get_contents();
            ob_end_clean();
        }
        elseif($cat_type==5) {//Многоколоночное меню. Тоже самое, что 2 и 3, только там на одну колонку, а тут сразу на несколько колонок. На идеале в подвале
            ob_start();?>

            <div class="row uNavi_cat4 uNavi_cat_id_<?=$cat_id?>">
            <?
            $indent=0;
            $last_id=0;

            /** @noinspection PhpStatementHasEmptyBodyInspection */
            /** @noinspection PhpUndefinedMethodInspection */
            for($i=0;$data[$i]=$stm->fetch(PDO::FETCH_OBJ);$i++);

            for($first=true,$i=0;$data[$i];$first=false,$i++) {
                if($data[$i+1]) $next_indent=$data[$i+1]->indent;
                else $next_indent=$data[$i]->indent;

                if($this->uSes->access($data[$i]->access)) {
                    $data[$i]->link=uString::sql2text($data[$i]->link);
                    $data[$i]->link=$this->makeLink($data[$i]->link);

                    while($data[$i]->indent>$indent) {
                        $indent++;?>
                        <ul>
                    <?}
                    while($data[$i]->indent<$indent) {
                        $indent--;
                        echo '</ul>';
                        if(!$indent) echo '</div>';
                    }

                    if(!$first) echo '</li>';

                    $active=false;
                    if(empty($data[$i]->link)) {if($_SERVER['REQUEST_URI']=='/') $active=true;}
                    else {
                        if($_SERVER['REQUEST_URI']=='/'&&$data[$i]->link=='mainpage/index') $active=true;
                        elseif(
                            strpos($_SERVER['REQUEST_URI'],$data[$i]->link)===0||
                            strpos($_SERVER['REQUEST_URI'],'/'.$data[$i]->link)===0||
                            strpos($_SERVER['REQUEST_URI'],str_replace(u_sroot,'/',$data[$i]->link))===0||
                            strpos($_SERVER['REQUEST_URI'],str_replace(u_sroot,'',$data[$i]->link))===0
                        ) $active=true;
                    }
                    $last_id=$data[$i]->id;

                    if($data[$i]->indent=='0'){?>
                        <div class="col-md-3">
                        <h3><?=(int)$data[$i]->show_label?uString::sql2text($data[$i]->title,true):''?></h3>
                    <?}
                    else {?>
                        <li class="list-unstyled
                        <?if($this->uCore->page['navi_parent_menu_id']==$data[$i]->id) {?> active <?}
                        if($active) {?> active <?}?>
                        ">
                        <<?=($data[$i]->link)?'a':'span'?>
                        class="<?=$active?'active':''?> <?=($next_indent>$data[$i]->indent)?' dropdown-toggle':''?>"
                        <?=($next_indent>$data[$i]->indent)?'aria-haspopup="true" data-toggle="dropdown"':''?>
                        <?=($data[$i]->link)?('href="'.$data[$i]->link.'"'):''?>
                        target="<?=$data[$i]->target?>"
                        data-href=""
                        data-item_id="<?=$data[$i]->id?>"
                        data-onclick=""
                        >
                        <?=(int)$data[$i]->show_label?uString::sql2text($data[$i]->title,true):''?> <?=($next_indent>$data[$i]->indent)?'<span class="caret"></span>':''?>
                        </<?=($data[$i]->link)?'a':'span'?>>
                        <?
                    }
                }?>
                </li>
            <?}?>
            </div>

            <script type="text/javascript">
                jQuery(".uNavi_item_id_<?=$last_id?>").addClass('last');
            </script>
            <?
            $cnt=ob_get_contents();
            ob_end_clean();
        }
        elseif($cat_type==6) {//Верхнее меню с иконками на mobilspeed (соц. сети) и madwww. Одноуровневое
            ob_start();?>

            <nav class="navbar">

            <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar_<?=$cat_id?>" aria-expanded="false" aria-controls="navbar" style="font-size: 1.5em;">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-menu"></span>
          </button>
        </div>

<div id="navbar_<?=$cat_id?>" class="navbar-collapse collapse">

            <ul  class="nav navbar-nav  cat<?=$cat_type?>  uNavi_cat_id_<?=$cat_id?>">
            <?/** @noinspection PhpUndefinedMethodInspection */
            while($data=$stm->fetch(PDO::FETCH_OBJ)) {
                unset($hover,$regular);
                if($this->uSes->access($data->access)) {
                    $data->link=$this->makeLink(uString::sql2text($data->link));

                    $active=false;
                    if(empty($data->link)) {if($_SERVER['REQUEST_URI']=='/') $active=true;}
                    else {
                        if($_SERVER['REQUEST_URI']=='/'&&$data->link=='mainpage/index') $active=true;
                        elseif(
                            strpos($_SERVER['REQUEST_URI'],$data->link)===0||
                            strpos($_SERVER['REQUEST_URI'],'/'.$data->link)===0||
                            strpos($_SERVER['REQUEST_URI'],str_replace(u_sroot,'/',$data->link))===0||
                            strpos($_SERVER['REQUEST_URI'],str_replace(u_sroot,'',$data->link))===0
                        ) $active=true;
                    }
                    $last_id=$data->id;

                    if(!($data->icon_regular_filename===NULL)){
                        $regular=u_sroot.'uNavi/item_icons/'.site_id.'/'.$data->id.'/regular/'.$data->id.'.'.$data->icon_regular_filename.'?'.$data->timestamp;
                        if(!($data->icon_hover_filename===NULL))
                            $hover=u_sroot.'uNavi/item_icons/'.site_id.'/'.$data->id.'/hover/'.$data->id.'.'.$data->icon_hover_filename.'?'.$data->timestamp;
                    }
                    ?>
                    <li class="uNavi_item
                    <?if($this->uCore->page['navi_parent_menu_id']==$data->id) {?> active <?}
                    if($active) {?> active <?}?>
                    " id="uNavi_item_<?=$data->id?>"
                        <?=isset($hover)?'onmouseover="jQuery(\'#uNavi_item_'.$data->id.' img\').prop(\'src\',\''.$hover.'\')" onmouseout="jQuery(\'#uNavi_item_'.$data->id.' img\').prop(\'src\',\''.$regular.'\')"':''?>>
                        <a
                        class="<?=$active?'active':''?>"
                        <?=($data->link)?('href="'.$data->link.'"'):''?>
                        target="<?=$data->target?>"
                        data-href=""
                        data-item_id="<?=$data->id?>"
                        data-onclick=""
                        >
                            <?if(isset($regular)){?>
                                <?if(isset($hover)){?>
                                <img src="<?=$hover?>" style="position: absolute; top:-10000px; left: -10000px;" alt="">
                                <?}?>
                                <img src="<?=$regular?>" alt="">
                            <?}?>
                            <span><?=(int)$data->show_label?uString::sql2text($data->title,true):''?></span>
                        </a>
                    </li>
                <?}
            }?>
            </ul>
            </div>
            </nav>

            <?if(isset($last_id)) {?>
            <script type="text/javascript">
                jQuery(".uNavi_item_id_<?=$last_id?>").addClass('last');
            </script>
            <?}
            $cnt=ob_get_contents();
            ob_end_clean();
        }
        return $cnt;
        //TODO-nik87 По ходу ничто не закрывает открытый в insert_menu <div>
    }

    public function build_menu_cache($cat_id) {
        $cache_dir="uNavi/cache/menu/".site_id."/".$cat_id;
        if(!file_exists($cache_dir)) mkdir($cache_dir,0755,true);

        $file = fopen($cache_dir.'/menu.html', 'w');
        $code=$this->insert_menu($cat_id);
        fwrite($file , $code);
        fclose($file);
    }

    public function text($str) {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->uCore->text(array('processors','uMenu'),$str);
    }

    public function clean_cache($cat_id) {
        $this->uFunc->rmdir("uNavi/cache/menu/".site_id."/".$cat_id);
    }
}
