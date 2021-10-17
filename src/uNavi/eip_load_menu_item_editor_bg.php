<?php
namespace uNavi\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
class uNavi_eip_load_menu_item_editor {
    public $uFunc;
    private $uSes;
    private $uCore;
    public $item,
        $item_id,$cat_id,$cat_style;

    public function text($str) {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->uCore->text(array('uNavi','eip_load_menu_item_editor_bg'),$str);
    }

    private function check_data() {
        if(!isset($_POST['item_id'])) $this->uFunc->error(10);
        $this->item_id=$_POST['item_id'];
        if(!uString::isDigits($this->item_id)&&$this->item_id!='new') $this->uFunc->error(20);

        if($this->item_id=='new') {
            if(!$_POST['cat_id']) $this->uFunc->error(30);
            $this->cat_id=$_POST['cat_id'];
            if(!uString::isDigits($this->cat_id)) $this->uFunc->error(40);

            //get cat data
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uNavi")->prepare("SELECT
                style
                FROM
                u235_cats
                JOIN
                u235_cattypes
                ON
                u235_cats.cat_type=u235_cattypes.type_id
                WHERE
                u235_cats.cat_id=:cat_id AND
                u235_cats.site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $this->cat_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(50);
                $this->cat_style=$qr->style;
            }
            catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}
        }
    }
    private function get_menu_item() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare("SELECT
            access,
            position,
            title,
            link,
            target,
            indent,
            icon_regular_filename,
            icon_hover_filename,
            timestamp,
            show_label,
            is_system_btn
            FROM
            u235_menu
            WHERE
            id=:id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':id', $this->item_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$this->item=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(70);
        }
        catch(PDOException $e) {$this->uFunc->error('80'/*.$e->getMessage()*/);}
    }
    private function get_cat() {
        //get cat data
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare("SELECT
            u235_cats.cat_id,
            style
            FROM
            u235_menu
            JOIN u235_cats
            ON
            u235_menu.cat_id=u235_cats.cat_id AND
            u235_menu.site_id=u235_cats.site_id
            JOIN u235_cattypes
            ON
            u235_cattypes.type_id=u235_cats.cat_type
            WHERE
            u235_menu.id=:id AND
            u235_menu.site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':id', $this->item_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(90);
            $this->cat_style=$qr->style;
            $this->cat_id=$qr->cat_id;
        }
        catch(PDOException $e) {$this->uFunc->error('100'/*.$e->getMessage()*/);}
    }

    /** @noinspection PhpInconsistentReturnPointsInspection */
    public function get_items_list() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare("SELECT
            title,
            id,
            position,
            indent
            FROM
            u235_menu
            WHERE
            site_id=:site_id AND
            cat_id=:cat_id AND
            status=''
            ORDER BY
            position ASC
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $this->cat_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            $indent=0;
            $cnt='';
            /** @noinspection PhpUndefinedMethodInspection */
            while($data=$stm->fetch(PDO::FETCH_OBJ)) {
                while($data->indent>$indent) $indent++;
                while($data->indent<$indent) $indent--;

                $cnt.='<option value="'.($data->position+1).'" style="padding-left:'.($data->indent*15+20).'px" ';
                if($this->item->position-1>=$data->position) $cnt.=' selected ';
                $cnt.='>'.$data->title.'</option>';
            }
            return $cnt;
        }
        catch(PDOException $e) {$this->uFunc->error('110'/*.$e->getMessage()*/);}
        return 0;
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
        if(!$this->uSes->access(7)) die('forbidden');

        $this->check_data();
        if($this->item_id!='new') {
            $this->get_cat();
            $this->get_menu_item();
        }
    }
}
$uNavi=new uNavi_eip_load_menu_item_editor($this);?>

    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#uNavi_eip_edit_menu_item_tab_1" aria-controls="uNavi_eip_edit_menu_item_tab_1" role="tab" data-toggle="tab"><?=$uNavi->text("common tab name"/*Основные*/)?></a></li>
        <li role="presentation"><a href="#uNavi_eip_edit_menu_item_tab_2" aria-controls="uNavi_eip_edit_menu_item_tab_2" role="tab" data-toggle="tab"><?=$uNavi->text("advanced tab name"/*Расширенные*/)?></a></li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="uNavi_eip_edit_menu_item_tab_1">

            <input type="hidden" id="uNavi_eip_edit_menu_item_id" value="<?=$uNavi->item_id?>">
            <input type="hidden" id="uNavi_eip_edit_menu_cat_id" value="<?=$uNavi->cat_id?>">
            <div class="form-group">
                <label for="uNavi_eip_edit_menu_item_title"><?=$uNavi->text("Item title - label"/*Заголовок*/)?></label>
                <input id="uNavi_eip_edit_menu_item_title" type="text" class="form-control" value="<?=$uNavi->item_id!='new'?htmlspecialchars(/*uString::sql2text(*/$uNavi->item->title/*,1)*/):''?>">
            </div>

            <div class="form-group">
                <label for="uNavi_eip_edit_menu_item_link"><?=$uNavi->text("Link - label"/*Ссылка (URL)*/)?></label>
                <input id="uNavi_eip_edit_menu_item_link" type="text" class="form-control" value="<?=$uNavi->item_id!='new'?htmlspecialchars(addslashes(/*uString::sql2text(*/$uNavi->item->link/*,1)*/)):''?>">
            </div>

            <div class="form-group">
                <label for="uNavi_eip_edit_menu_item_position"><?=$uNavi->text("Position - label"/*Местоположение относительно других пунктов*/)?></label>
                <select id="uNavi_eip_edit_menu_item_position" class="form-control">
                    <option value="-1"><?=$uNavi->text("Position on top - option"/*В начале*/)?></option>
                    <?=$uNavi->get_items_list()?>
                </select>
                <div class="checkbox" <?=$uNavi->item_id=='new'?'style="display:none"':''?>>
                    <label>
                        <input type="checkbox" checked id="uNavi_eip_edit_menu_item_position_apply4children">
                        <span><?=$uNavi->text("Move with children - label"/* переместить вместе с дочерними пунктами*/)?></span>
                    </label>
                </div>
            </div>
        </div>
        <div role="tabpanel" class="tab-pane" id="uNavi_eip_edit_menu_item_tab_2">

            <div class="form-group">
                <label for="uNavi_eip_edit_menu_item_indent"><?=$uNavi->text("Level - label"/*Уровень*/)?></label>
                <select id="uNavi_eip_edit_menu_item_indent" class="form-control">
                    <?for($i=0;$i<6;$i++) {?>
                        <option value="<?=$i?>" <?=$uNavi->item_id!='new'?($uNavi->item->indent==$i?'selected':''):($i==0?'selected':'')?>><?=$i?></option>
                    <?}?>
                </select>
            </div>

            <div class="form-group">
                <label for="uNavi_eip_edit_menu_item_target"><?=$uNavi->text("Target - label"/*Где открыть?*/)?></label>
                <select id="uNavi_eip_edit_menu_item_target" class="form-control">
                    <option value="_blank" <?=$uNavi->item_id!='new'?($uNavi->item->target=='_blank'?'selected':''):''?>><?=$uNavi->text("Target - blank - option"/*В новой вкладке*/)?></option>
                    <option value="_self" <?=$uNavi->item_id!='new'?($uNavi->item->target=='_self'?'selected':''):'selected'?>><?=$uNavi->text("Target - here - option"/*В текущей вкладке*/)?></option>
                </select>
            </div>

            <div class="form-group">
                <label for="uNavi_eip_edit_menu_item_access"><?=$uNavi->text("Access - label"/*Доступ?*/)?></label>
                <select id="uNavi_eip_edit_menu_item_access" class="form-control">
                    <option value="0"><?=$uNavi->text("Everyone - access title"/*Любой посетитель*/)?></option>
                    <option value="2"><?=$uNavi->text("Authorized only - access title"/*Только авторизованные*/)?></option>
                    <option value="11"><?=$uNavi->text("Unauthorized only - access title"/*Только НЕавторизованные*/)?></option>
                </select>
            </div>

            <div class="form-group">
                <label for="uNavi_eip_edit_menu_item_icon_selectbox" class="text-muted"><?=$uNavi->text("Insert icon to item text - label"/*Вставить иконку в текст*/)?></label>
                <div class="input-group">
                    <select class="form-control" id="uNavi_eip_edit_menu_item_icon_selectbox" onchange=""><?
                        $glyphar=$uNavi->uFunc->glyphicons_ar();
                        for($i=0;$i<count($glyphar);$i++) {?>
                            <option value="glyphicon glyphicon-<?=$glyphar[$i]?>" class="glyphicon glyphicon-<?=$glyphar[$i]?>">&nbsp;<?=$glyphar[$i]?></option>
                        <?}?>
                    </select>
                    <span class="input-group-btn">
            <!--suppress JSJQueryEfficiency -->
            <button class="btn btn-default" type="button" onclick="$('#uNavi_eip_edit_menu_item_title').val('<span class=\''+$('#uNavi_eip_edit_menu_item_icon_selectbox').val()+'\'></span>'+$('#uNavi_eip_edit_menu_item_title').val())"><?=$uNavi->text("Insert icon to item text - btn"/*Добавить иконку в текст*/)?></button>
        </span>
                </div>
            </div>

            <div class="form-group">
                <label for="uNavi_eip_edit_menu_item_show_label"><input type="checkbox" id="uNavi_eip_edit_menu_item_show_label" <?=$uNavi->item_id!='new'?((int)$uNavi->item->show_label?"checked='checked'":""):"checked='checked'"?>> <?=$uNavi->text("Show label"/*Показывать текст*/)?></label>
            </div>

            <div class="form-group">
                <label for="uNavi_eip_edit_menu_item_is_system_btn"><?=$uNavi->text("is system btn"/*Это системная кнопка?*/)?></label>
                <select id="uNavi_eip_edit_menu_item_is_system_btn" class="form-control">
                    <option value="0" <?=$uNavi->item_id!='new'?((int)$uNavi->item->is_system_btn===0?'selected':''):''?>><?=$uNavi->text("Not a system btn"/*Нет*/)?></option>
                    <?if($uNavi->uFunc->mod_installed("uCat")) {?>
                        <option value="1" <?=$uNavi->item_id!='new'?((int)$uNavi->item->is_system_btn===1?'selected':''):''?>><?=$uNavi->text("uCat account btn"/*Каталог. Личный кабинет/Вход*/)?></option>
                    <?}?>
                    <?if($uNavi->uFunc->mod_installed("advert")) {?>
                        <option value="2" <?=$uNavi->item_id!='new'?((int)$uNavi->item->is_system_btn===2?'selected':''):''?>><?=$uNavi->text("advert account btn"/*Объявления. Личный кабинет/Вход*/)?></option>
                    <?}?>
                </select>
            </div>

            <?if($uNavi->cat_style=='with icons') {
                if($uNavi->item_id!='new') {
                    if(!($uNavi->item->icon_regular_filename===NULL)){
                        $regular=u_sroot.'uNavi/item_icons/'.site_id.'/'.$uNavi->item_id.'/regular/'.$uNavi->item_id.'.'.$uNavi->item->icon_regular_filename.'?'.$uNavi->item->timestamp;
                        if(!($uNavi->item->icon_hover_filename===NULL))
                            $hover=u_sroot.'uNavi/item_icons/'.site_id.'/'.$uNavi->item_id.'/hover/'.$uNavi->item_id.'.'.$uNavi->item->icon_hover_filename.'?'.$uNavi->item->timestamp;
                    }
                    ?>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label><?=$uNavi->text("Button icon - label"/*Значок кнопки*/)?></label>
                            <div id="uNavi_item_regular_icon_container" class="thumbnail well" <?=isset($regular)?'':'style="display:none"'?>><?=isset($regular)?'<img src="'.$regular.'">':''?></div>
                            <div id="uNavi_item_regular_icon_uploader"></div>
                            <div id="uNavi_item_regular_icon_filelist" class="uNavi_item_icon_filelist"></div>
                            <div class="btn-group btn-group-justified" id="uNavi_item_regular_icon_delete_btn" <?=isset($regular)?'':'style="display:none"'?>>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-danger" onclick="uNavi_eip.delete_icon(<?=$uNavi->item_id?>,'regular')"><span class="glyphicon glyphicon-remove"></span> <?=$uNavi->text("Delete image - btn"/*Удалить изображение*/)?></button>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-6">
                            <label><?=$uNavi->text("Button icon onmouseover - label"/*Значок кнопки при наведении*/)?></label>
                            <div id="uNavi_item_hover_icon_container" class="thumbnail well" <?=isset($hover)?'':'style="display:none"'?>><?=isset($hover)?'<img src="'.$hover.'">':''?></div>
                            <div id="uNavi_item_hover_icon_uploader"></div>
                            <div id="uNavi_item_hover_icon_filelist" class="uNavi_item_icon_filelist"></div>
                            <div class="btn-group btn-group-justified" id="uNavi_item_hover_icon_delete_btn" <?=isset($hover)?'':'style="display:none"'?>>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-danger" onclick="uNavi_eip.delete_icon(<?=$uNavi->item_id?>,'hover')"><span class="glyphicon glyphicon-remove"></span> <?=$uNavi->text("Delete image - btn"/*Удалить изображение*/)?></button
                                </div>
                            </div>
                        </div>
                    </div>
                <?}
                else {?>
                    <p class="well bg-warning"><?=$uNavi->text("Image upload - hint"/*Загрузка иконок будет доступна только после сохранения*/)?></p>
                <?}
            }?>

        </div>
    </div>




