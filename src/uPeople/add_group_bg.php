<?php
class uPeople_add_group_bg {
    private $uCore,
        $gr_title;

    private function check_data() {
        if(!isset($_POST['gr_title'])) $this->uCore->error(1);
        $this->gr_title=$_POST['gr_title'];
    }

    private function create_group() {
        //get new gr_id
        if(!$query=$this->uCore->query("uPeople","SELECT
        `gr_id`
        FROM
        `u235_groups`
        WHERE
        `site_id`='".site_id."'
        ORDER BY
        `gr_id` DESC
        LIMIT 1
        ")) $this->uCore->error(2);
        if(mysqli_num_rows($query)) {
            $group=$query->fetch_object();
            $gr_id=$group->gr_id+1;
        }
        else $gr_id=1;

        //create new group
        if(!$this->uCore->query("uPeople","INSERT INTO
        `u235_groups` (
        `gr_id`,
        `gr_title`,
        `site_id`
        ) VALUES (
        '".$gr_id."',
        '".uString::text2sql($this->gr_title)."',
        '".site_id."'
        )
        ")) $this->uCore->error(3);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(10)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->create_group();

        echo "{'status' : 'done'}";
    }
}
$uPeople=new uPeople_add_group_bg($this);
