<?php
class uDrive_file_types {
    private $uCore;
    public $registered;

    private function check_data() {
        if(isset($_GET['registered'])) $this->registered=1;
        else $this->registered=0;
    }
    public function get_file_types() {
        if(!$query=$this->uCore->query("uDrive","SELECT
        `type_id`,
        `ext`,
        `mime_type`
        FROM
        `u235_file_types`
        WHERE
        `known`='".$this->registered."'
        ")) $this->uCore->error(10);
        return $query;
    }
    public function text($string) {
        return $this->uCore->text(array('uDrive','file_types'),$string);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;

        $this->uCore->page['page_title']=$this->text("Page name"/*Типы файлов*/);

        $this->check_data();
    }
}
$uDrive=new uDrive_file_types($this);

//uInt JS
$this->uInt_js('uDrive','file_types');
//popconfirm
$this->uFunc->incJs('js/bootstrap_plugins/PopConfirm/jquery.popconfirm.js');

$this->uFunc->incJs('uDrive/js/file_types.js');

ob_start();?>

<h1 class="page-header"><?=$uDrive->registered?$uDrive->text('registered file types'/*'Зарегистрированные типы файлов'*/):$uDrive->text('unregistered file types'/*'Незарегистрированные типы файлов'*/)?></h1>
<div class="btn-group">
    <a href="<?=u_sroot.$this->mod?>/<?=$this->page_name?>" class="btn btn-default <?=$uDrive->registered?'':'active'?>"><?=$uDrive->text('unregistered'/*'Незарегистрированные'*/)?></a>
    <a href="<?=u_sroot.$this->mod?>/<?=$this->page_name?>?registered=1" class="btn btn-default <?=$uDrive->registered?'active':''?>"><?=$uDrive->text('registered'/*'Зарегистрированные'*/)?></a>
</div>
<?$types=$uDrive->get_file_types();
if(mysqli_num_rows($types)) {?>
<table class="table table-striped">
    <tr>
        <th><?=$uDrive->text('extention'/*'Расширение'*/)?></th>
        <th>Mime-type</th>
    </tr>
    <?while($type=$types->fetch_object()) {?>
    <tr id="uDrive_file_types_type_<?=$type->type_id?>">
        <td><?=$uDrive->registered?'':'<button class="btn btn-xs btn-default uDrive_file_types_reg_btn" onclick="uDrive_file_types.register_file_type('.$type->type_id.')"><span class="icon-down-open"></span> '.$uDrive->text('register'/*'Зарегистрировать'*/).'</button> '?><span class="<?=$uDrive->registered?$this->uFunc->file_ext2fonticon[$type->ext]:'icon-file-unknown'?>"></span><?=$type->ext?></td>
        <td><?=$type->mime_type?></td>
    </tr>
    <?}?>
</table>
<?}
else {?>
    <div class="jumbotron">
        <h3><?=$uDrive->registered?$uDrive->text('no registered files found'/*'Зарегистрированных типов файлов нет'*/):$uDrive->text('no unregistered files found'/*'Незарегистрированных типов файлов нет'*/)?></h3>
    </div>
<?}?>

<p class="text-info"><?=$uDrive->text('tip for fontello icons'/*'Для fontello не забываем в fontello.css ставить font-size 128% (больше уже разносит элементы)'*/)?></p>

<?$this->page_content=ob_get_contents();
ob_end_clean();

include "templates/u235/template.php";
