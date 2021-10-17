<div class="modal fade" id="uNavi_eip_edit_menu_dg" tabindex="-1" role="dialog" aria-labelledby="uNavi_eip_edit_menu_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uNavi_eip_edit_menu_dgLabel"><?=$this->uInt->text(array('uNavi','eip_dialogs'),"Menu editor - dg title"/*Редактор меню*/)?></h4>
                <div style="display: table; width: 100%;">
                    <div class="btn-group" style="display: table; float:right;">
                        <button type="button" class="btn btn-success btn-sm" onclick="uNavi_eip.add_menu_item()"><span class="glyphicon glyphicon-plus"></span> <?=$this->uInt->text(array('uNavi','eip_dialogs'),"Add menu item - btn"/*Добавить пункт*/)?></button>
                    </div>
                </div>
            </div>
            <div class="modal-body" id="uNavi_eip_edit_menu_cnt"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="uNavi_eip_edit_menu_item_dg" tabindex="-1" role="dialog" aria-labelledby="uNavi_eip_edit_menu_item_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uNavi_eip_edit_menu_item_dgLabel"><?=$this->uInt->text(array('uNavi','eip_dialogs'),"Menu item editor - dg title"/*Редактор пункта меню*/)?></h4>
            </div>
            <div class="modal-body" id="uNavi_eip_edit_menu_item_cnt">

            </div>
            <div class="modal-footer">
                <button type="button" id="uNavi_eip_edit_menu_item_delete_btn" class="btn btn-danger" ><?=$this->uInt->text(array('uNavi','eip_dialogs'),"Delete - btn"/*Удалить*/)?></button>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->uInt->text(array('uNavi','eip_dialogs'),"Close - btn"/*Закрыть*/)?></button>
                <button type="button" class="btn btn-success" onclick="uNavi_eip.save_menu_item()"><?=$this->uInt->text(array('uNavi','eip_dialogs'),"Save - btn"/*Сохранить*/)?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uNavi_eip_add_new_menu_item_dg" tabindex="-1" role="dialog" aria-labelledby="uNavi_eip_add_new_menu_item_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uNavi_eip_add_new_menu_item_dgLabel"><?=$this->uInt->text(array('uNavi','eip_dialogs'),"New menu item - dg title"/*Новый пункт меню*/)?></h4>
            </div>
            <div class="modal-body" id="uNavi_eip_add_new_menu_item_cnt">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->uInt->text(array('uNavi','eip_dialogs'),"Close - btn"/*Закрыть*/)?></button>
                <button type="button" class="btn btn-primary" onclick="uNavi_eip.save_menu_item()"><?=$this->uInt->text(array('uNavi','eip_dialogs'),"Create - btn"/*Создать*/)?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uNavi_eip_delete_menu_item_dg" tabindex="-1" role="dialog" aria-labelledby="uNavi_eip_delete_menu_item_dgLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uNavi_eip_delete_menu_item_dgLabel"><?=$this->uInt->text(array('uNavi','eip_dialogs'),"Delete menu item - dg title"/*Удалить пункт меню?*/)?></h4>
            </div>
            <div class="modal-body">
                <p class="well-lg bg-danger"><?=$this->uInt->text(array('uNavi','eip_dialogs'),"Delete menu item - dg confirmation txt"/*Действительно удалить этот пункт меню?<br>Его нельзя будет вернуть*/)?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-dismiss="modal"><?=$this->uInt->text(array('uNavi','eip_dialogs'),"Cancel - btn"/*Отмена*/)?></button>
                <button type="button" class="btn btn-danger" onclick="uNavi_eip.delete_menu_item_exec()"><?=$this->uInt->text(array('uNavi','eip_dialogs'),"Delete - btn"/*Удалить*/)?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uNavi_eip_breadcrumbs_dg" tabindex="-1" role="dialog" aria-labelledby="uNavi_eip_breadcrumbs_dgLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uNavi_eip_breadcrumbs_dgLabel"><?=$this->uInt->text(array('uNavi','eip_dialogs'),"Sitemap editor - dg title"/*Редактор карты сайта*/)?></h4>
            </div>
            <div class="modal-body" id="uNavi_eip_breadcrumbs_cnt"></div>
        </div>
    </div>
</div>