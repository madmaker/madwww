<?
class uCat_admin_sects_list_ar_ajax {
    private $uCore;
    private $q_sects;
    private function getSects(){
        //Sections list
        if(!$this->q_sects=$this->uCore->query('uCat',"SELECT
        `sect_id`,
        `sect_title`
        FROM
        `u235_sects`
        WHERE
        `site_id`='".site_id."'
        ORDER BY
        `sect_id` ASC")) $this->uCore->error(1);
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;

        if(!$this->uCore->access(25)) die('forbidden');

        $this->getSects();

        $sects='';
        for($i=0;$sect=$this->q_sects->fetch_object();$i++) {
            $sects.='<option value="'.$sect->sect_id.'">'.uString::sql2text($sect->sect_title,true).'</option>';
        }

        if(!empty($sects)) {
            echo '<select class="form-control" id="'.(isset($_POST['selectboxID'])?$_POST['selectboxID']:'uCat_new_cat_sects_select').'" onchange="'.(isset($_POST['onchange'])?$_POST['onchange']:'').'">'.$sects.'</select>
            <span class="input-group-btn">
                <button class="btn btn-success uTooltip" title="Создать новый раздел" type="button" onclick="uCat_common.create_sect()"><span class="glyphicon glyphicon-plus"></span></button>
            </span>';
        }
        else echo 'none';
    }
}
$uCat=new uCat_admin_sects_list_ar_ajax($this);
