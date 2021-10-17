<?
use processors\uFunc;

require_once 'processors/classes/uFunc.php';
class uCat_admin_cats_list_ar_ajax {
    private $uCore;
    private $sect_id,$q_cats;
    private function check_data() {
        if(!isset($_POST['sect_id'])) $this->uFunc->error(10);
        $this->sect_id=$_POST['sect_id'];
        if(!uString::isDigits($this->sect_id)) $this->uFunc->error(20);
    }
    private function getCats(){
        //Cats list
        if(!$this->q_cats=$this->uCore->query('uCat',"SELECT DISTINCT
        `u235_cats`.`cat_id`,
        `cat_title`
        FROM
        `u235_sects_cats`,
        `u235_cats`
        WHERE
        `u235_cats`.`site_id`='".site_id."' AND
        `u235_sects_cats`.`sect_id`='".$this->sect_id."' AND
        `u235_sects_cats`.`cat_id`=`u235_cats`.`cat_id` AND
        `u235_sects_cats`.`site_id`='".site_id."'
        ORDER BY
        `cat_title` ASC")) $this->uFunc->error(30);
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);

        $this->check_data();

        $this->getCats();

        $cats='';
        for($i=0;$cat=$this->q_cats->fetch_object();$i++) {
            $cats.='<option value="'.$cat->cat_id.'">'.uString::sql2text($cat->cat_title,true).'</option>';
        }

        if(!empty($cats)) {
            echo '<select class="form-control" id="uCat_cats_select">'.$cats.'</select>
            <span class="input-group-btn">
                <button class="btn btn-success uTooltip" title="Создать новую категорию" type="button" onclick="uCat_common.create_cat()"><span class="glyphicon glyphicon-plus"></span></button>
            </span>';
        }
        else echo 'none';
    }
}
$uCat=new uCat_admin_cats_list_ar_ajax ($this);
