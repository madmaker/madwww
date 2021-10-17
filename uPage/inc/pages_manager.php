<?php
/**how to use
 * IN DIALOG
 include_once 'uPage/inc/pages_manager.php';?>
 <div id="uPage_pages_manager_uploader_init"></div>
 <script type="text/javascript">
 //uCat_cats_admin.uPage_folder_id=<?=$uCat->sect->uPage_folder_id;?>;//ID ПАПКИ страницы
 uPage_pages_manager.init('uPage_pages',uPage_pages_manager.cur_folder_id,1,"uCat_cats_admin.insert_tinymce_url",'uCat','sect',<?=$uCat->sect_id?>);
 </script>
 *
 * uPage_pages_manager_uploader - id, который нужно дать загрузчику
 * <?=$uCat->sect->uPage_folder_id;?> - id папки страницы по умолчанию
 * 1 - в диалоге (0 - нет)
 * uCat_cats_admin.insert_tinymce_url - какую функцию js выполнить, когда будет нажата кнопка "Использовать файл". Будет выполнен код uCat_cats_admin.insert_tinymce_url(i), где i - номер файла в массиве uPage_pages_manager.page_id[i], uPage_pages_manager.page_name[i] и т.п.
 * uCat - page_usage_mod
 * sect - page_usage_handler_type
 * <?=$uCat->sect_id?> - page_usage_handler_id
 * uPage_pages_manager.open_folder(<?=$uPage->folder_id?>,<?=$uPage->recycled?>); - открывает диалог и указанную папку



 * ON PAGE
include_once 'uPage/inc/pages_manager.php';
?>
<div id="uPage_pages_manager_uploader_init"></div>
<script type="text/javascript">
uPage_pages_manager.init('uPage_pages_manager_uploader',<?=$uPage->folder_id?>);
uPage_pages_manager.open_folder(<?=$uPage->folder_id?>,<?=$uPage->recycled?>);
</script>
*/
if(!isset($this->uCore)) $this->uCore=&$this;
//pages_manager uint
$this->uCore->uInt_js('uPage','pages_list');
//keymaster
$this->uFunc->incJs(u_sroot . 'js/keymaster/keymaster.min.js',0);
//phpjs
$this->uFunc->incJs(u_sroot . 'js/phpjs/functions/datetime/date.min.js',0);
$this->uFunc->incJs(u_sroot . 'js/phpjs/functions/strings/explode.min.js',0);
$this->uFunc->incJs(u_sroot . 'js/phpjs/functions/strings/str_replace.min.js',0);
//bootstrap-contextmenu
$this->uFunc->incJs(u_sroot . 'js/bootstrap_plugins/bootstrap-contextmenu/bootstrap-contextmenu.min.js',0);

$this->uFunc->incCss(u_sroot . 'uEditor/css/pages_manager.min.css');

//uPage manager
$this->uFunc->incJs(u_sroot . 'uPage/js/pages_manager.min.js',1);

?>

<!--Dialogs-->
<div class="modal fade" id="uPage_new_folder_dg" tabindex="-1" role="dialog" aria-labelledby="uPage_new_folder_dgLabel" aria-hidden="true" style="z-index: 66001">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uPage_new_folder_dgLabel"><?=$this->uCore->uInt->text(array('uPage', 'pages_manager'),"New folder - btn text"/*Новая папка*/)?></h4>
            </div>
            <div class="modal-body">
                <input type="text" class="form-control" placeholder="<?=$this->uCore->uInt->text(array('uPage', 'pages_manager'),"New folder - input placeholder"/*Новая папка*/)?>" id="uPage_new_folder_title_input">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->uCore->uInt->text(array('uPage', 'pages_manager'),"Cancel - btn text"/*Отмена*/)?></button>
                <button type="button" class="btn btn-primary" onclick="uPage_pages_manager.create_folder_do()"><?=$this->uCore->uInt->text(array('uPage', 'pages_manager'),"Create - btn text"/*Создать*/)?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uPage_pages_manager_rename_page_dg" tabindex="-1" role="dialog" aria-labelledby="uPage_pages_manager_rename_page_dgLabel" aria-hidden="true" style="z-index: 66001">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uPage_pages_manager_rename_page_dgLabel"><?=$this->uCore->uInt->text(array('uPage', 'pages_manager'),"Rename - dg title"/*Переименовать*/)?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="uPage_pages_manager_rename_page_name"><?=$this->uCore->uInt->text(array('uPage', 'pages_manager'),"New page name - input label"/*Имя файла/папки*/)?></label>
                    <input type="text" class="form-control" id="uPage_pages_manager_rename_page_title">
                    <input type="hidden" id="uPage_pages_manager_rename_page_id">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->uCore->uInt->text(array('uPage', 'pages_manager'),"Cancel - btn text")?></button>
                <button type="button" class="btn btn-primary" onclick="uPage_pages_manager.rename_page_do()"><?=$this->uCore->uInt->text(array('uPage', 'pages_manager'),"Save - btn text")?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uPage_move_page_dg" tabindex="-1" role="dialog" aria-labelledby="uPage_move_page_dgLabel" aria-hidden="true" style="z-index: 66001">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uPage_move_page_dgLabel"><?=$this->uCore->uInt->text(array('uPage', 'pages_manager'),"Move to - dg title"/*Переместить в...*/)?></h4>
            </div>
            <div class="modal-body">
                <div>
                    <ol id="uPage_pages_manager_move_breadcrumb" class="breadcrumb"></ol>
                </div>
                <div id="uPage_pages_manager_move_page_listcontainer"></div>
                <div id="uPage_pages_manager_move_js_vars"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->uCore->uInt->text(array('uPage', 'pages_manager'),"Cancel - btn text")?></button>
                <button type="button" class="btn btn-primary" onclick="uPage_pages_manager.move_page()"><?=$this->uCore->uInt->text(array('uPage', 'pages_manager'),"Move here - btn text"/*Переместить сюда*/)?></button>
            </div>
        </div>
    </div>
</div>