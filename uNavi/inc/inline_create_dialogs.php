<?php
//get menu cat types
try {
    require_once "processors/classes/uFunc.php";
//    if(isset($this->uCore)) $uCore=$this->uCore;
//    else $uCore=$this;
//    $uCore->uFunc->uFunc=new \processors\uFunc($uCore);

    /** @noinspection PhpUndefinedMethodInspection */
    if(isset($this->uFunc->uFunc))
        $tmp_uFunc=&$this->uFunc->uFunc;//Костыль. На старых сайтах работает $this->uFunc->pdo. На новых $this->uFunc->uFunc->pdo
    else $tmp_uFunc=&$this->uFunc;
    $stm=$tmp_uFunc->pdo("uNavi")->prepare("SELECT
    type_id,
    type_title
    FROM
    u235_cattypes
    ORDER BY 
    type_title
    ");
    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
}
catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}
?>
<div class="modal fade" id="uNavi_common_create_menu_dg" tabindex="-1" role="dialog" aria-labelledby="uNavi_common_create_menu_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uNavi_common_create_menu_dgLabel"><?=$this->uInt->text(array('uNavi','inline_create_dialogs'),"New menu - dg title"/*Новое меню*/)?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label><?=$this->uInt->text(array('uNavi','inline_create_dialogs'),"Menu title - label"/*Название меню*/)?></label>
                    <input type="text" id="uNavi_common_create_menu_title" class="form-control" value="<?=$this->uInt->text(array('uNavi','inline_create_dialogs'),"Menu title - placeholder"/*Новое меню*/)?>">
                </div>

                <div class="form-group">
                    <label><?=$this->uInt->text(array('uNavi','inline_create_dialogs'),"Menu type - label"/*Тип*/)?></label>
                    <div class="input-group">
                        <select id="uNavi_common_create_menu_type" class="form-control">
                            <?while($cat=$stm->fetch(PDO::FETCH_OBJ)) {?>
                                <option value="<?=$cat->type_id?>"><?=$cat->type_title?></option>
                            <?}?>
                        </select>
                        <span class="input-group-btn"><button class="btn btn-default" type="button" onclick="uNavi_common.show_type_description()">?</button></span>
                    </div>
                </div>

                <div class="form-group">
                    <label><?=$this->uInt->text(array('uNavi','inline_create_dialogs'),"Menu access - label"/*Кому показывать?*/)?></label>
                    <div class="input-group">
                        <select id="uNavi_common_create_menu_access" class="form-control">
                            <option value="1"><?=$this->uInt->text(array('uNavi','inline_create_dialogs'),"Everyone - Menu access option"/*Всем*/)?></option>
                            <option value="2"><?=$this->uInt->text(array('uNavi','inline_create_dialogs'),"Only for authorised - Menu access option"/*Только авторизованным*/)?></option>
                            <option value="11"><?=$this->uInt->text(array('uNavi','inline_create_dialogs'),"Only for unauthorised - Menu access option"/*Только НЕавторизованным*/)?></option>
                        </select>
                        <span class="input-group-btn"><button class="btn btn-default" type="button" onclick="uNavi_common.show_access_description()">?</button></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->uInt->text(array('uNavi','inline_create_dialogs'),"Close - btn"/*Закрыть*/)?></button>
                <button type="button" class="btn btn-primary" onclick="uNavi_common.create_menu_exec();"><span class="icon-ok"></span> <?=$this->uInt->text(array('uNavi','inline_create_dialogs'),"Create - btn"/*Создать*/)?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uNavi_cats_access_desctiption_dg" tabindex="-1" role="dialog" aria-labelledby="uNavi_cats_access_desctiption_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uNavi_cats_access_desctiption_dgLabel"><?=$this->uInt->text(array('uNavi','inline_create_dialogs'),"Menu access description - dg title"/*Кому показывать - описание*/)?></h4>
            </div>
            <div class="modal-body"><?=$this->uInt->text(array('uNavi','inline_create_dialogs'),"Menu access - description")?></div>
        </div>
    </div>
</div>

<div class="modal fade" id="uNavi_cats_type_desctiption_dg" tabindex="-1" role="dialog" aria-labelledby="uNavi_cats_type_desctiption_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uNavi_cats_type_desctiption_dgLabel"><?=$this->uInt->text(array('uNavi','inline_create_dialogs'),"Menu type description - dg title"/*Типы меню - описание*/)?></h4>
            </div>
            <div class="modal-body"><p><?=$this->uInt->text(array('uNavi','inline_create_dialogs'),"Menu type description - dg text"/*Существует несколько типов меню. Они отличаются стилем*/)?></p>

                <h3><?=$this->uInt->text(array('uNavi','inline_create_dialogs'),"Menu type description - type 2"/*Меню - список*/)?></h3>
                <div class="uNavi_menu uNavi_menu_2">
                    <ul class="cat2 uNavi_cat">
                        <li  class="uNavi_item"><a href="javascript:void(0)">Lorem</a></li>
                        <li  class="active uNavi_item"><a href="javascript:void(0)">Ipsum</a></li>
                        <li>
                            Aliquam
                            <ul>
                                <li  class="uNavi_item"><a href="javascript:void(0)">Duis tempor</a></li>
                                <li  class="uNavi_item"><a href="javascript:void(0)">Nullam imperdiet</a></li>
                                <li  class="uNavi_item"><a href="javascript:void(0)">Quisque</a></li>
                            </ul>
                        </li>
                        <li  class="uNavi_item"><a href="javascript:void(0)">In vel leo</a></li>
                        <li  class="uNavi_item"><a href="javascript:void(0)">Etiam molestie</a></li>
                    </ul>
                </div>

                <h3><?=$this->uInt->text(array('uNavi','inline_create_dialogs'),"Menu type description - type 4"/*Меню - панель*/)?></h3>
                <div class="uNavi_menu uNavi_menu_4">
                    <ul class="nav cat4 uNavi_cat">
                        <li  class="uNavi_item"><a href="javascript:void(0)">Lorem</a></li>
                        <li  class="active uNavi_item"><a href="javascript:void(0)">Ipsum</a></li>
                        <li class="uNavi_item">
                            <a class="dropdown-toggle" aria-haspopup="true" data-toggle="dropdown" href="javascript:void(0)">Aliquam</a>
                            <ul class="dropdown-menu">
                                <li  class="uNavi_item"><a href="javascript:void(0)">Duis tempor</a></li>
                                <li  class="uNavi_item"><a href="javascript:void(0)">Nullam imperdiet</a></li>
                                <li  class="uNavi_item"><a href="javascript:void(0)">Quisque</a></li>
                            </ul>
                        </li>
                        <li  class="uNavi_item"><a href="javascript:void(0)">In vel leo</a></li>
                        <li  class="uNavi_item"><a href="javascript:void(0)">Etiam molestie</a></li>
                    </ul>
                </div>

                <h3><?=$this->uInt->text(array('uNavi','inline_create_dialogs'),"Menu type description - type 6"/*Меню с изображениями*/)?></h3>
                <ul class="nav cat_6 uNavi_cat">
                    <li class="uNavi_item active">
                        <a class="active" href="javascript:void(0)"><img src="<?=u_sroot?>uNavi/img/cat_type_6_example_icons/btn1.jpg">
                            <span>Lorem ipsum</span>
                        </a>
                        <a class="active" href="javascript:void(0)"><img src="<?=u_sroot?>uNavi/img/cat_type_6_example_icons/btn2.jpg">
                            <span>Aliquam</span>
                        </a>
                        <a class="active" href="javascript:void(0)"><img src="<?=u_sroot?>uNavi/img/cat_type_6_example_icons/btn3.jpg">
                            <span>Duis tempor</span>
                        </a>
                        <a class="active" href="javascript:void(0)"><img src="<?=u_sroot?>uNavi/img/cat_type_6_example_icons/btn4.jpg">
                            <span>Nullam imperdiet</span>
                        </a>
                    </li>
                </ul>

            </div>
        </div>
    </div>
</div>