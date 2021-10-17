<?
class uPeople_profile_field_drop_ajax {
    private $uCore;

    private function check_data() {
        if(!isset($_POST['ids'])) $this->uCore->error(1);
    }
    private function delete_do($ids_list) {
        if(!$this->uCore->query("uPeople","DELETE FROM
        `u235_fields`
        WHERE
        `site_id`='".site_id."' AND
        (".$ids_list.")
        ")) $this->uCore->error(2);
    }
    private function delete() {
        $idsAr=explode("#", $_POST['ids']);
        $ids_count=count($idsAr);

        $ids_list='1=0';
        for($i=1;$i<$ids_count;$i++) {
            $field_id=$idsAr[$i];
            if(!uString::isDigits($field_id)) $this->uCore->error(3);
            $ids_list.=" OR `field_id`='".$field_id."'";

            //update user's fields
            if(!$this->uCore->query("uPeople","UPDATE
            `u235_people`
            SET
            `field_".$field_id."`=''
            WHERE
            `site_id` = '".site_id."'
            ")) $this->uCore->error(4);
        }
        //update status to new
        $this->delete_do($ids_list);

        echo 'done';
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(10)) die('forbidden');

        $this->check_data();
        $this->delete();
    }
}
$uPeople=new uPeople_profile_field_drop_ajax($this);
