<?php
/**how to use
 * IN DIALOG
 include_once 'uDrive/inc/my_drive_manager.php';?>
 <div id="uDrive_my_drive_uploader_init"></div>
 <script type="text/javascript">
 //uCat_cats_admin.uDrive_folder_id=<?=$uCat->sect->uDrive_folder_id;?>;//ID ПАПКИ страницы
 uDrive_manager.init('uDrive_my_drive_uploader',<?=$uCat->sect->uDrive_folder_id;?>,1,"uCat_cats_admin.insert_tinymce_url",'uCat','sect',<?=$uCat->sect_id?>);
 </script>
 *
 * uDrive_my_drive_uploader - id, который нужно дать загрузчику
 * <?=$uCat->sect->uDrive_folder_id;?> - id папки страницы по умолчанию
 * 1 - в диалоге (0 - нет)
 * uCat_cats_admin.insert_tinymce_url - какую функцию js выполнить, когда будет нажата кнопка "Использовать файл". Будет выполнен код uCat_cats_admin.insert_tinymce_url(i), где i - номер файла в массиве uDrive_manager.file_id[i], uDrive_manager.file_name[i] и т.п.
 * uCat - file_usage_mod
 * sect - file_usage_handler_type
 * <?=$uCat->sect_id?> - file_usage_handler_id
 * uDrive_manager.open_folder(<?=$uDrive->folder_id?>,<?=$uDrive->recycled?>); - открывает диалог и указанную папку



 * ON PAGE
include_once 'uDrive/inc/my_drive_manager.php';
?>
<div id="uDrive_my_drive_uploader_init"></div>
<script type="text/javascript">
uDrive_manager.init('uDrive_my_drive_uploader',<?=$uDrive->folder_id?>);
uDrive_manager.open_folder(<?=$uDrive->folder_id?>,<?=$uDrive->recycled?>);
</script>
*/
if(!isset($this->uCore)) $this->uCore=&$this;
//my_drive uint
$this->uCore->uInt_js('uDrive','my_drive');
//plupload for uDrive
$this->uFunc->incJs(u_sroot . 'js/plupload/js/jquery.plupload.dragdiv/jquery.plupload.dragdiv.min.js',1);
$this->uFunc->incCss(u_sroot . 'js/plupload/js/jquery.plupload.dragdiv/css/jquery.plupload.dragdiv.min.css');
//filesize
$this->uFunc->incJs(u_sroot . 'js/filesize.js/lib/filesize.min.js',0);
//keymaster
$this->uFunc->incJs(u_sroot . 'js/keymaster/keymaster.min.js',0);
//phpjs
$this->uFunc->incJs(u_sroot . 'js/phpjs/functions/datetime/date.js',0);
$this->uFunc->incJs(u_sroot . 'js/phpjs/functions/strings/explode.js',0);
$this->uFunc->incJs(u_sroot . 'js/phpjs/functions/strings/str_replace.js',0);
//bootstrap-contextmenu
$this->uFunc->incJs(u_sroot . 'js/bootstrap_plugins/bootstrap-contextmenu/bootstrap-contextmenu.min.js',0);

$this->uFunc->incCss(u_sroot . 'uDrive/css/uDrive.min.css');

//uDrive manager
$this->uFunc->incJs(u_sroot . 'uDrive/js/my_drive_manager.min.js',1);

?>

<!--Dialogs-->
<div class="modal fade" id="uDrive_new_folder_dg" tabindex="-1" role="dialog" aria-labelledby="uDrive_new_folder_dgLabel" aria-hidden="true" style="z-index: 66001">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uDrive_new_folder_dgLabel"><?=$this->uCore->uInt->text(array('uDrive', 'my_drive_manager'),"New folder - btn text"/*Новая папка*/)?></h4>
            </div>
            <div class="modal-body">
                <input type="text" class="form-control" onkeypress="u235_common.create_folder_press_enter(event,'input','uDrive_manager.create_folder_do()')" placeholder="<?=$this->uCore->uInt->text(array('uDrive', 'my_drive_manager'),"New folder - input placeholder"/*Новая папка*/)?>" id="uDrive_new_folder_title_input">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->uCore->uInt->text(array('uDrive', 'my_drive_manager'),"Cancel - btn text"/*Отмена*/)?></button>
                <button type="button" class="btn btn-primary" onclick="uDrive_manager.create_folder_do()"><?=$this->uCore->uInt->text(array('uDrive', 'my_drive_manager'),"Create - btn text"/*Создать*/)?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uDrive_my_drive_rename_file_dg" tabindex="-1" role="dialog" aria-labelledby="uDrive_my_drive_rename_file_dgLabel" aria-hidden="true" style="z-index: 66001">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uDrive_my_drive_rename_file_dgLabel"><?=$this->uCore->uInt->text(array('uDrive', 'my_drive_manager'),"Rename - dg title"/*Переименовать*/)?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="uDrive_my_drive_rename_file_name"><?=$this->uCore->uInt->text(array('uDrive', 'my_drive_manager'),"New file name - input label"/*Имя файла/папки*/)?></label>
                    <input type="text" class="form-control" id="uDrive_my_drive_rename_file_name">
                    <input type="hidden" id="uDrive_my_drive_rename_file_id">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->uCore->uInt->text(array('uDrive', 'my_drive_manager'),"Cancel - btn text")?></button>
                <button type="button" class="btn btn-primary" onclick="uDrive_manager.rename_file_do()"><?=$this->uCore->uInt->text(array('uDrive', 'my_drive_manager'),"Save - btn text")?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uDrive_move_file_dg" tabindex="-1" role="dialog" aria-labelledby="uDrive_move_file_dgLabel" aria-hidden="true" style="z-index: 66001">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uDrive_move_file_dgLabel"><?=$this->uCore->uInt->text(array('uDrive', 'my_drive_manager'),"Move to - dg title"/*Переместить в...*/)?></h4>
            </div>
            <div class="modal-body">
                <div>
                    <ol id="uDrive_my_drive_move_breadcrumb" class="breadcrumb"></ol>
                </div>
                <div id="uDrive_my_drive_move_file_list_container"></div>
                <div id="uDrive_my_drive_move_js_vars"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->uCore->uInt->text(array('uDrive', 'my_drive_manager'),"Cancel - btn text")?></button>
                <button type="button" class="btn btn-primary" onclick="uDrive_manager.move_file()"><?=$this->uCore->uInt->text(array('uDrive', 'my_drive_manager'),"Move here - btn text"/*Переместить сюда*/)?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uDrive_file_show_dg" tabindex="-1" role="dialog" aria-labelledby="uDrive_file_show_dgLabel" aria-hidden="true" style="z-index: 66002">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uDrive_file_show_dgLabel"></h4>
            </div>
            <div class="modal-body" id="uDrive_file_show_cnt"></div>
        </div>
    </div>
</div>
<div class="modal fade" id="uDrive_delete_used_files_confirm_dg" tabindex="-1" role="dialog" aria-labelledby="uDrive_delete_used_files_confirm_dgLabel" aria-hidden="true" style="z-index: 66001">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uDrive_delete_used_files_confirm_dgLabel"><?=$this->uCore->uInt->text(array('uDrive', 'my_drive_manager'),"There are files that are used - dg title"/*Есть файлы, которые используются*/)?></h4>
            </div>
            <div class="modal-body" id="uDrive_delete_used_files_confirm_cnt"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?=$this->uCore->uInt->text(array('uDrive', 'my_drive_manager'),"Cancel - btn text")?></button>
                <button type="button" class="btn btn-danger" onclick="uDrive_manager.delete_file3('delete_all')"><?=$this->uCore->uInt->text(array('uDrive', 'my_drive_manager'),"Delete all files - btn text"/*Удалить все*/)?></button>
                <button type="button" class="btn btn-primary" onclick="uDrive_manager.delete_file3('delete_unused')"><?=$this->uCore->uInt->text(array('uDrive', 'my_drive_manager'),"Delete unused files only - btn text"/*Удалить только неиспользуемые*/)?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uDrive_clean_recycled_used_files_confirm_dg" tabindex="-1" role="dialog" aria-labelledby="uDrive_clean_recycled_used_files_confirm_dgLabel" aria-hidden="true" style="z-index: 66001">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uDrive_clean_recycled_used_files_confirm_dgLabel"><?=$this->uCore->uInt->text(array('uDrive', 'my_drive_manager'),"There are files that are used - dg title"/*Есть файлы, которые используются*/)?></h4>
            </div>
            <div class="modal-body" id="uDrive_clean_recycled_used_files_confirm_cnt"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-danger" onclick="uDrive_manager.clear_recycled_bin3('delete_all')"><?=$this->uCore->uInt->text(array('uDrive', 'my_drive_manager'),"Delete all files - btn text"/*Удалить все*/)?></button>
                <button type="button" class="btn btn-primary" onclick="uDrive_manager.clear_recycled_bin3('delete_unused')"><?=$this->uCore->uInt->text(array('uDrive', 'my_drive_manager'),"Delete unused files only - btn text"/*Удалить только неиспользуемые*/)?></button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="uDrive_my_drive_file_usage_dg" tabindex="-1" role="dialog" aria-labelledby="uDrive_my_drive_file_usage_dgLabel" aria-hidden="true" style="z-index: 66001">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uDrive_my_drive_file_usage_dgLabel"><?=$this->uCore->uInt->text(array('uDrive', 'my_drive_manager'),"This file is probably used - dg title"/*Этот файл предположительно используется*/)?></h4>
            </div>
            <div class="modal-body" id="uDrive_my_drive_file_usage_cnt"></div>
        </div>
    </div>
</div>