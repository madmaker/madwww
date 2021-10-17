<?
class uCat_admin_avails_ajax {
    private $uCore;
    public $q_avails,$avail_type_id2title;
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(25)) die('forbidden');

        $this->getAvails();
        $this->getAvail_types();
    }
    private function getAvail_types() {
        if(!$query=$this->uCore->query("uCat","SELECT
        `avail_type_id`,
        `avail_type_title`
        FROM
        `u235_items_avail_types`
        ORDER BY
        `avail_type_id` ASC
        ")) $this->uCore->error(10);
        while($data=$query->fetch_object()) {
            $this->avail_type_id2title[$data->avail_type_id]=uString::sql2text($data->avail_type_title);
        }
    }
    private function getAvails(){
        //Avails list
        if(!$this->q_avails=$this->uCore->query('uCat',"SELECT
        `avail_id`,
        `avail_label`,
        `avail_descr`,
        `avail_type_id`
        FROM
        `u235_items_avail_values`
        WHERE
        `site_id`='".site_id."'
        ORDER BY `avail_id` ASC
        ")) $this->uCore->error(20);
    }
}
$uCat=new uCat_admin_avails_ajax($this);?>
<table class="table table-condensed table-striped">
    <tr><th colspan="2">Название</th><th>Описание</th><th colspan="2">Тип</th> </tr>
<?while($avail=$uCat->q_avails->fetch_object()) { ?>
    <tr>
        <td><button class="btn btn-default btn-xs uTooltip" title="Редактировать доступность" onclick="uCat_item_admin.item_availability_settings(<?=$avail->avail_id?>)"><span class="glyphicon glyphicon-pencil"></span></button></td>
        <td><?=uString::sql2text($avail->avail_label,true)?></td>
        <td><?=uString::sql2text($avail->avail_descr,true)?></td>
        <td><?=$uCat->avail_type_id2title[$avail->avail_type_id]?></td>
        <td><button class="btn btn-danger btn-xs" onclick="uCat.item_availability_delete(<?=$avail->avail_id?>)"><span class="glyphicon glyphicon-remove"></span></button></td>
<?}?>
    </tr>
</table>
