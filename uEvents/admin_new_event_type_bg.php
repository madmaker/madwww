<?php
use processors\uFunc;
require_once "processors/classes/uFunc.php";
class uEvents_new_event_type_bg {
    private $uCore,$uFunc,$type_title,$type_id,$type_url;
    private function check_data() {
        if(!isset($_POST['type_title'])) $this->uCore->error(10);
        $this->type_title=trim($_POST['type_title']);
        if(!strlen($this->type_title)) die('{
        "status":"error",
        "msg":"title is empty"
        }');
    }
    private function make_new_url() {
        $url=uString::rus2eng($this->type_title);
        $url=uString::text2filename($url);

        $busy=true;
        $i=0;
        $test_url=$url;
        while($busy) {
            if(!$query=$this->uCore->query("uEvents","SELECT
            `type_id`
            FROM
            `u235_events_types`
            WHERE
            `type_url`='".$test_url."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(20);
            if(!mysqli_num_rows($query)) $busy=false;
            else {
                $i++;
                $test_url=$url.'_'.$i;
            }
        }
        return $test_url;
    }
    private function create_new_event_type() {
        //get new id
        if(!$query=$this->uCore->query("uEvents","SELECT
        `type_id`
        FROM
        `u235_events_types`
        WHERE
        `site_id`='".site_id."'
        ORDER BY
        `type_id` DESC
        LIMIT 1
        ")) $this->uCore->error(30);
        if(mysqli_num_rows($query)) {
            $qr=$query->fetch_object();
            $this->type_id=$qr->type_id+1;
        }
        else $this->type_id=1;

        $this->type_url=$this->make_new_url();
        if(!$this->uCore->query("uEvents","INSERT INTO
        `u235_events_types` (
        `type_id`,
        `type_title`,
        `type_url`,
        `site_id`
        ) VALUES (
        '".$this->type_id."',
        '".uString::text2sql($this->type_title)."',
        '".$this->type_url."',
        '".site_id."'
        )
        ")) $this->uCore->error(40);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc = new uFunc($this->uCore);
        if(!$this->uCore->access(300)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->create_new_event_type();
        $this->uFunc->set_flag_update_sitemap(1, site_id);
        echo '{
        "status":"done",
        "type_id":"'.$this->type_id.'",
        "type_title":"'.rawurlencode($this->type_title).'",
        "type_url":"'.rawurlencode($this->type_url).'"
        }';
    }
}
$uEvents=new uEvents_new_event_type_bg($this);
