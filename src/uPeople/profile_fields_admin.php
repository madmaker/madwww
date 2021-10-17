<?
class uPeople_profile_fields_admin {
    private $uCore;
    public $q_fields;
    private function get_fields() {
        if(!$this->q_fields=$this->uCore->query('uPeople',"SELECT
        `field_id`,
        `label`,
        `field_comment`,
        `show_on_list`,
        `show_on_page`,
        `sort`,
        `field_type`
        FROM
        `u235_fields`
        WHERE
        `site_id`='".site_id."'
        ORDER BY
        `field_id` ASC
        ")) $this->uCore->error(1);
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;

        $this->get_fields();
    }
}
$uPeople=new uPeople_profile_fields_admin($this);

$pnl=&$GLOBALS['TEMPLATE']['mod_panel'];
$pnl='<ul class="u235_top_menu">
<li><a href="javascript:void(0);" id="uPeople_createBtn">Создать</a></li>
<li><a href="javascript:void(0);" id="uPeople_deleteBtn">Удалить</a></li>
<li><a href="javascript:void(0);" id="uPeople_watchBtn">Просмотр</a></li>
<li><a href="javascript:void(0);" id="uPeople_editBtn">Редактировать</a></li>
</ul>';

$this->uFunc->incCss(u_sroot.'uPeople/css/default.min.css');
$this->uFunc->incCss(u_sroot.'templates/u235/css/uPeople/uPeople.css');
$this->uFunc->incJs(u_sroot.'uPeople/js/profile_fields_admin.min.js');
ob_start();?>

<div><?=$GLOBALS['TEMPLATE']['mod_panel'];?></div>

<div class="uPeople u235_admin" id="uPeople_fields">
    <h1><?=$this->page['page_title']?></h1>
    <div class="uPeople_fields list"></div>
</div>

    <script type="text/javascript">
        if(typeof uPeople==="undefined") {
            uPeople = {};
            uPeople.users_list = [];

            uPeople.field_id = [];
            uPeople.label = [];
            uPeople.field_comment = [];
            uPeople.show_on_list = [];
            uPeople.show_on_page = [];
            uPeople.field_sort = [];
            uPeople.field_type = [];
            uPeople.field_show = [];
            uPeople.field_sel = [];
            uPeople.field_id2ind = [];

            uPeople.field_type_id2title = [];
        }

        <? for($i=0;$field=$uPeople->q_fields->fetch_object();$i++) {?>
        uPeople.field_id        [<?=$i?>]               =<?=$field->field_id?>;
        uPeople.field_sort      [<?=$i?>]               =<?=$field->sort?>;
        uPeople.field_type      [<?=$i?>]               ="<?=$field->field_type?>";
        uPeople.label           [<?=$i?>]               ="<?=rawurlencode(uString::sql2text($field->label))?>";
        uPeople.field_comment   [<?=$i?>]               ="<?=rawurlencode(uString::sql2text($field->field_comment))?>";
        uPeople.show_on_list    [<?=$i?>]               =<?=$field->show_on_list?>;
        uPeople.show_on_page    [<?=$i?>]               =<?=$field->show_on_page?>;
        uPeople.field_show      [<?=$i?>]               =true;
        uPeople.field_sel       [<?=$i?>]               =false;
        uPeople.field_id2ind    [<?=$field->field_id?>] =<?=$i?>;
        <?}?>
    </script>


<div style="display:none">
    <div id="uPeople_new_field_dg" class="uPeople_new_field_dg" title="Новое поле">
        <form role="form">
            <div class="form-group">
                <label for="uPeople_new_field_label">Название поля:</label>
                <input type="text" id="uPeople_new_field_label" name="uPeople_new_field_label" class="form-control">
            </div>
            <div class="form-group">
                <label for="uPeople_new_field_sort" value="0">Сортировка:</label>
                <input type="text" id="uPeople_new_field_sort" name="uPeople_new_field_sort" class="form-control">
            </div>
            <div class="form-group">
                <label for="uPeople_new_field_type">Тип поля:</label>
                <select id="uPeople_new_field_type" name="uPeople_new_field_type" class="form-control">
                    <option value="1">Строка текста</option>
                    <option value="2">Блок текста</option>
                    <option value="3">HTML блок</option>
                </select>
            </div>
            <div class="form-group">
                <p class="help-block">Все поля обязательны.</p>
            </div>
        </form>
    </div>

    <div id="uPeople_delete_confirm_dg" title="Удалить поля?"><p>Вы действительно хотите удалить выбранные поля?</p></div>
    <div id="uPeople_change_field_type_confirm_dg" title="Изменить тип?"></div>
</div>

<?$this->page_content=ob_get_contents();
ob_end_clean();
include "templates/u235/template.php";
