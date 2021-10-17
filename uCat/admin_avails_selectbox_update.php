<?
class uCat_admin_avails_selectbox_update {
    private $uCore;
    public $q_avails;
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(25)) die('forbidden');

        $this->getAvails();
    }
    private function getAvails(){
        //Avails list
        if(!$this->q_avails=$this->uCore->query('uCat',"SELECT
        `avail_id`,
        `avail_label`
        FROM
        `u235_items_avail_values`
        WHERE
        `site_id`='".site_id."'
        ORDER BY `avail_id` ASC
        ")) $this->uCore->error(1);
    }
}
$uCat=new uCat_admin_avails_selectbox_update($this);?>
<?while($avail=$uCat->q_avails->fetch_object()) {?>
    <option value="<?=$avail->avail_id?>" <?=$_POST['avail_id']==$avail->avail_id?'selected':''?>><?=uString::sql2text($avail->avail_label,true)?></option>
<?}?>
