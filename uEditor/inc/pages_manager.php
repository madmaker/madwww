<?php
/**how to use
 * IN DIALOG
 include_once 'uEditor/inc/pages_manager.php';?>
 <div id="uEditor_pages_manager_uploader_init"></div>
 <script type="text/javascript">
 //uCat_cats_admin.uEditor_folder_id=<?=$uCat->sect->uEditor_folder_id;?>;//ID ПАПКИ страницы
 uEditor_pages_manager.init('uEditor_pages',uEditor_pages_manager.cur_folder_id,1,"uCat_cats_admin.insert_tinymce_url",'uCat','sect',<?=$uCat->sect_id?>);
 </script>
 *
 * uEditor_pages_manager_uploader - id, который нужно дать загрузчику
 * <?=$uCat->sect->uEditor_folder_id;?> - id папки страницы по умолчанию
 * 1 - в диалоге (0 - нет)
 * uCat_cats_admin.insert_tinymce_url - какую функцию js выполнить, когда будет нажата кнопка "Использовать файл". Будет выполнен код uCat_cats_admin.insert_tinymce_url(i), где i - номер файла в массиве uEditor_pages_manager.page_id[i], uEditor_pages_manager.page_name[i] и т.п.
 * uCat - page_usage_mod
 * sect - page_usage_handler_type
 * <?=$uCat->sect_id?> - page_usage_handler_id
 * uEditor_pages_manager.open_folder(<?=$uEditor->folder_id?>,<?=$uEditor->recycled?>); - открывает диалог и указанную папку



 * ON PAGE
include_once 'uEditor/inc/pages_manager.php';
?>
<div id="uEditor_pages_manager_uploader_init"></div>
<script type="text/javascript">
uEditor_pages_manager.init('uEditor_pages_manager_uploader',<?=$uEditor->folder_id?>);
uEditor_pages_manager.open_folder(<?=$uEditor->folder_id?>,<?=$uEditor->recycled?>);
</script>
*/
if(!isset($this->uCore)) $this->uCore=&$this;
//pages_manager uint
$this->uCore->uInt_js('uEditor','pages_list');
//keymaster
$this->uFunc->incJs(u_sroot . 'js/keymaster/keymaster.min.js',0);
//phpjs
$this->uFunc->incJs(u_sroot . 'js/phpjs/functions/datetime/date.js',0);
$this->uFunc->incJs(u_sroot . 'js/phpjs/functions/strings/explode.js',0);
$this->uFunc->incJs(u_sroot . 'js/phpjs/functions/strings/str_replace.js',0);
//bootstrap-contextmenu
$this->uFunc->incJs(u_sroot . 'js/bootstrap_plugins/bootstrap-contextmenu/bootstrap-contextmenu.min.js',0);

$this->uFunc->incCss(u_sroot . 'uEditor/css/pages_manager.min.css');

//uEditor manager
$this->uFunc->incJs(u_sroot . 'uEditor/js/pages_manager.js',1);

?>

<!--Dialogs-->
<div class="modal fade" id="uEditor_new_folder_dg" tabindex="-1" role="dialog" aria-labelledby="uEditor_new_folder_dgLabel" aria-hidden="true" style="z-index: 66001">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEditor_new_folder_dgLabel"><?=$this->uCore->uInt->text(array('uEditor', 'pages_manager'),"New folder - btn text"/*Новая папка*/)?></h4>
            </div>
            <div class="modal-body">
                <input type="text" class="form-control" placeholder="<?=$this->uCore->uInt->text(array('uEditor', 'pages_manager'),"New folder - input placeholder"/*Новая папка*/)?>" id="uEditor_new_folder_title_input">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->uCore->uInt->text(array('uEditor', 'pages_manager'),"Cancel - btn text"/*Отмена*/)?></button>
                <button type="button" class="btn btn-primary" onclick="uEditor_pages_manager.create_folder_do()"><?=$this->uCore->uInt->text(array('uEditor', 'pages_manager'),"Create - btn text"/*Создать*/)?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uEditor_pages_manager_rename_page_dg" tabindex="-1" role="dialog" aria-labelledby="uEditor_pages_manager_rename_page_dgLabel" aria-hidden="true" style="z-index: 66001">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEditor_pages_manager_rename_page_dgLabel"><?=$this->uCore->uInt->text(array('uEditor', 'pages_manager'),"Rename - dg title"/*Переименовать*/)?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="uEditor_pages_manager_rename_page_name"><?=$this->uCore->uInt->text(array('uEditor', 'pages_manager'),"New page name - input label"/*Имя файла/папки*/)?></label>
                    <input type="text" class="form-control" id="uEditor_pages_manager_rename_page_title">
                    <input type="hidden" id="uEditor_pages_manager_rename_page_id">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->uCore->uInt->text(array('uEditor', 'pages_manager'),"Cancel - btn text")?></button>
                <button type="button" class="btn btn-primary" onclick="uEditor_pages_manager.rename_page_do()"><?=$this->uCore->uInt->text(array('uEditor', 'pages_manager'),"Save - btn text")?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uEditor_move_page_dg" tabindex="-1" role="dialog" aria-labelledby="uEditor_move_page_dgLabel" aria-hidden="true" style="z-index: 66001">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEditor_move_page_dgLabel"><?=$this->uCore->uInt->text(array('uEditor', 'pages_manager'),"Move to - dg title"/*Переместить в...*/)?></h4>
            </div>
            <div class="modal-body">
                <div>
                    <ol id="uEditor_pages_manager_move_breadcrumb" class="breadcrumb"></ol>
                </div>
                <div id="uEditor_pages_manager_move_page_listcontainer"></div>
                <div id="uEditor_pages_manager_move_js_vars"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->uCore->uInt->text(array('uEditor', 'pages_manager'),"Cancel - btn text")?></button>
                <button type="button" class="btn btn-primary" onclick="uEditor_pages_manager.move_page()"><?=$this->uCore->uInt->text(array('uEditor', 'pages_manager'),"Move here - btn text"/*Переместить сюда*/)?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uEditor_where_used_dg" tabindex="-1" role="dialog" aria-labelledby="uEditor_where_used_dgLabel" aria-hidden="true" style="z-index: 66001">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uEditor_where_used_dgLabel"><?=$this->uCore->uInt->text(array('uEditor', 'pages_manager'),"Where text is used - dg title")?></h4>
            </div>
            <div class="modal-body" id="uEditor_where_used_dg_body"></div>
        </div>
    </div>
</div>
