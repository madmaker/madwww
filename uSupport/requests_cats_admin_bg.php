<?php
class uSupport_requests_cats_admin {
    private $uCore;
    public $qCats;
    private function getCats() {
        if(!$this->qCats=$this->uCore->query("uSup","SELECT
        `cat_id`,
        `cat_title`
        FROM
        `u235_requests_cats`
        WHERE
        `site_id`='".site_id."'
        ORDER BY
        `cat_title`
        ")) $this->error(10);
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(8)&&!$this->uCore->access(9)) die('forbidden');
        $this->getCats();
    }
}
$uSup=new uSupport_requests_cats_admin ($this);
?>
<? if(isset($_POST['selectbox'])) {?>
    <div class="form-group">
        <label>Выберите категорию запроса</label>
        <select id="uSup_change_cat_sb" class="form-control">
        <?while($cat=$uSup->qCats->fetch_object()) {?>
            <option value="<?=$cat->cat_id?>"><?=uString::sql2text($cat->cat_title,1)?></option>
        <?}?>
        </select>
    </div>
    <?}
elseif(isset($_POST['cat_editor'])) {?>
    <div class="form-group">
        <label>Название категории</label>
        <input type="hidden" id="uSup_category_editor_cat_id" value="<?=$_POST['cat_id']?>">
        <input type="text" id="uSup_category_editor_cat_title" class="form-control" value="<?while($cat=$uSup->qCats->fetch_object()) {
            if($cat->cat_id==$_POST['cat_id']){?><?=addslashes(uString::sql2text($cat->cat_title,1))?><?}
        }?>">
    </div>
    <?} else {?>
    <table class="table table-striped table-condensed table-hover">
        <?while($cat=$uSup->qCats->fetch_object()) {?>
        <tr id="uSup_cat_list_tr_<?=$cat->cat_id?>">
            <td><button class="btn btn-default btn-xs uTooltip" title="Изменить категорию" onclick="uSup_req_show_common.requests_cat_editor(<?=$cat->cat_id?>)"><span class="glyphicon glyphicon-pencil"></span></button> </td>
            <td><button class="btn btn-danger btn-xs uTooltip" title="Удалить категорию" onclick="uSup_req_show_common.requests_cat_delete_confirm(<?=$cat->cat_id?>)"><span class="glyphicon glyphicon-remove"></span></button> </td>
            <td># <?=$cat->cat_id?></td>
            <td><?=uString::sql2text($cat->cat_title,1)?></td>
        </tr>
        <?}?>
    </table>
<?}
