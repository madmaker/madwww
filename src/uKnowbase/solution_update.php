<?php
require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";

class uKnowbase_solution_update {
    public $uFunc;
    public $uSes;
    private $uCore,$rec_id;
    private function check_data() {
        if(!isset($_POST['rec_id'])) $this->uFunc->error(1);
        $this->rec_id=$_POST['rec_id'];
        if(!uString::isDigits($this->rec_id)) $this->uFunc->error(2);
    }
    private function check_access($rec_id) {
        if($this->uSes->access(38)) return true;
        elseif($this->uSes->access(33)) {
            if(!$query=$this->uCore->query("uKnowbase","SELECT
            `rec_id`
            FROM
            `u235_records`
            WHERE
            `rec_id`='".$rec_id."' AND
            `user_id`='".$this->uSes->get_val("user_id")."' AND
            `site_id`='".site_id."'
            ")) $this->uFunc->error(3);
            if(mysqli_num_rows($query)) return true;
        }

        return false;
    }
    private function save_title() {
        if(!$this->uCore->query("uKnowbase","UPDATE
        `u235_records`
        SET
        `rec_title`='".uString::text2sql($_POST['rec_title'])."',
        `timestamp`='".time()."'
        WHERE
        `rec_id`='".$this->rec_id."' AND
        `site_id`='".site_id."'
        ")) $this->uFunc->error(4);

        echo "{
            'status' : 'done',
            'rec_id' : '".$this->rec_id."',
            'rec_title' : '".rawurlencode($_POST['rec_title'])."'
            }";
        exit;
    }
    private function save_is_section() {
        if(!$this->uCore->query("uKnowbase","UPDATE
        `u235_records`
        SET
        `is_section`=1-`is_section`,
        `timestamp`='".time()."'
        WHERE
        `rec_id`='".$this->rec_id."' AND
        `site_id`='".site_id."'
        ")) $this->uFunc->error(5);

        echo "{
        'status' : 'done',
        'rec_id' : '".$this->rec_id."'
        }";
        exit;
    }
    private function save_text() {
        if(!$this->uCore->query("uKnowbase","UPDATE
        `u235_records`
        SET
        `rec_descr`='".uString::text2sql($_POST['rec_descr'])."',
        `rec_solution`='".uString::text2sql($_POST['rec_solution'])."',
        `timestamp`='".time()."'
        WHERE
        `rec_id`='".$this->rec_id."' AND
        `site_id`='".site_id."'
        ")) $this->uFunc->error(6);
        echo "{
        'status' : 'done',
        'rec_id' : '".$this->rec_id."'
        }";
        exit;
    }
    private function publish() {
        if(!$this->uCore->query("uKnowbase","UPDATE
        `u235_records`
        SET
        `rec_status`='active',
        `timestamp`='".time()."'
        WHERE
        `rec_id`='".$this->rec_id."' AND
        `site_id`='".site_id."'
        ")) $this->uFunc->error(7);
        echo "{
        'status' : 'done',
        'rec_id' : '".$this->rec_id."'
        }";
        exit;
    }
    private function limit_access() {
        if(!isset($_POST['com_id'])) $this->uFunc->error(8);
        $com_id=$_POST['com_id'];
        if(!uString::isDigits($com_id)) $this->uFunc->error(9);
        //check if company exists
        if(!$query=$this->uCore->query("uSup","SELECT
        `com_id`
        FROM
        `u235_comps`
        WHERE
        `com_id`='".$com_id."' AND
        `site_id`='".site_id."'
        ")) $this->uFunc->error(10);
        if(!mysqli_num_rows($query)) die('{"status":"forbidden"}');

        //check if com_id rec_id exists
        if(!$query=$this->uCore->query("uKnowbase","SELECT
        `rec_id`
        FROM
        `u235_records_comps`
        WHERE
        `com_id`='".$com_id."' AND
        `rec_id`='".$this->rec_id."' AND
        `site_id`='".site_id."'
        ")) $this->uFunc->error(11);
        if(mysqli_num_rows($query)) {//we must delete rec
            if(!$this->uCore->query("uKnowbase","DELETE FROM
            `u235_records_comps`
            WHERE
            `com_id`='".$com_id."' AND
            `rec_id`='".$this->rec_id."' AND
            `site_id`='".site_id."'
            ")) $this->uFunc->error(12);
            //let's check if we have any more access limits
            if(!$query=$this->uCore->query("uKnowbase","SELECT
            `rec_id`
            FROM
            `u235_records_comps`
            WHERE
            `rec_id`='".$this->rec_id."' AND
            `site_id`='".site_id."'
            ")) $this->uFunc->error(13);
            if(!mysqli_num_rows($query)) {//we must set record that it has no access limits
                if(!$this->uCore->query("uKnowbase","UPDATE
                `u235_records`
                SET
                `access_limited`='0',
                `timestamp`='".time()."'
                WHERE
                `rec_id`='".$this->rec_id."' AND
                `site_id`='".site_id."'
                ")) $this->uFunc->error(14);
                echo "{
                'status' : 'done',
                'access_limit_changed' : '1',
                'rec_id' : '".$this->rec_id."'
                }";
                exit;
            }
        }
        else {//we must add rec
            if(!$this->uCore->query("uKnowbase","INSERT INTO
            `u235_records_comps` (
            `com_id`,
            `rec_id`,
            `site_id`
            ) VALUES (
            '".$com_id."',
            '".$this->rec_id."',
            '".site_id."'
            )
            ")) $this->uFunc->error(15);
            //we must set record that it has access limits
            if(!$this->uCore->query("uKnowbase","UPDATE
                `u235_records`
                SET
                `access_limited`='1',
                `timestamp`='".time()."'
                WHERE
                `rec_id`='".$this->rec_id."' AND
                `site_id`='".site_id."'
                ")) $this->uFunc->error(16);
                echo "{
                    'status' : 'done',
                    'access_limit_changed' : '1',
                    'rec_id' : '".$this->rec_id."'
                    }";
                exit;
        }

        echo "{
        'status' : 'done',
        'access_limit_changed' : '0',
        'rec_id' : '".$this->rec_id."'
        }";
        exit;
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        
        if(!$this->uSes->access(33)) die("{'status' : 'forbidden'}");

        $this->check_data();
        if(!$this->check_access($this->rec_id)) die("{'status' : 'forbidden'}");

        if(isset($_POST['rec_title'])) $this->save_title();
        elseif(isset($_POST['rec_descr'],$_POST['rec_solution'])) $this->save_text();
        elseif(isset($_POST['is_section'])) $this->save_is_section();
        elseif(isset($_POST['publish'])) $this->publish();
        elseif(isset($_POST['limit_access'])) $this->limit_access();

        die("{'status' : 'forbidden'}");
    }
}
$newClass=new uKnowbase_solution_update($this);
