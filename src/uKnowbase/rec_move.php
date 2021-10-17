<?php
require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";

class uKnowbase_rec_move {
    public $uFunc;
    public $uSes;
    private $uCore,$q_rec_indent,$rec_position,$rec_id,$rec,$apply4children,
        $has_children,$first_non_child_pos,$first_non_child_id,$q_children;
    private function check_data() {
        if(!isset($_POST['rec_id'],$_POST['indent'],$_POST['pos'],$_POST['apply4children'])) $this->uCore->error(1);
        $this->rec_id=$_POST['rec_id'];
        if(!uString::isDigits($this->rec_id)) $this->uCore->error(2);
        $rec_indent=$_POST['indent'];
        if(!uString::isDigits($rec_indent)) $this->uCore->error(3);
        $above_rec_id=$_POST['pos'];
        if(!uString::isDigits($above_rec_id)) $this->uCore->error(4);
        if($above_rec_id=='0') $this->rec_position=1;
        else $this->rec_position=$this->get_position_of_record($above_rec_id)+1;//get above record's position +1
        $this->apply4children=$_POST['apply4children'];
        if($this->apply4children=='true') $this->apply4children=1;
        else $this->apply4children=0;

        //get record info
        if(!$query=$this->uCore->query("uKnowbase","SELECT
        `rec_id`,
        `rec_position`,
        `rec_indent`,
        `user_id`
        FROM
        `u235_records`
        WHERE
        `rec_id`='".$this->rec_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(5);
        if(!mysqli_num_rows($query)) return false;
        $this->rec=$query->fetch_object();

        $rec_indent=$rec_indent-$this->rec->rec_indent;
        if($rec_indent>0) $this->q_rec_indent="`rec_indent`=`rec_indent`+".$rec_indent;
        else $this->q_rec_indent="`rec_indent`=`rec_indent`-".abs($rec_indent);

        //check if user have access to edit this record
        if($this->uCore->access(38)) return true;
        //check if user is owner of this record and
        if($this->rec->user_id==$this->uSes->get_val("user_id")) return true;
    }
    private function get_position_of_record($rec_id) {
        if(!$query=$this->uCore->query("uKnowbase","SELECT
        `rec_position`
        FROM
        `u235_records`
        WHERE
        `rec_id`='".$rec_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(6);
        if(!mysqli_num_rows($query)) $this->uCore->error(7);
        $rec=$query->fetch_object();
        return $rec->rec_position;
    }
    private function check_if_position_is_free($rec_position) {
        if(!$query=$this->uCore->query("uKnowbase","SELECT
        `rec_id`
        FROM
        `u235_records`
        WHERE
        `rec_position`='".$rec_position."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(8);
        if(mysqli_num_rows($query)) return false;
        return true;
    }
    private function check_if_any_positions_exists_after($rec_position) {
        if(!$query=$this->uCore->query("uKnowbase","SELECT
        `rec_id`
        FROM
        `u235_records`
        WHERE
        `rec_position`>".$rec_position." AND
        `site_id`='".site_id."'
        LIMIT 1
        ")) $this->uCore->error(9);
        return mysqli_num_rows($query);
    }
    private function check_if_there_are_children() {
        //check if next rec is child
        if(!$query=$this->uCore->query("uKnowbase","SELECT
        `rec_id`,
        `rec_indent`
        FROM
        `u235_records`
        WHERE
        `rec_position`>".$this->rec->rec_position." AND
        `site_id`='".site_id."'
        ORDER BY
        `rec_position` ASC
        LIMIT 1
        ")) $this->uCore->error(10);
        if(mysqli_num_rows($query)) {
            $rec=$query->fetch_object();
            if($rec->rec_indent>$this->rec->rec_indent) $this->has_children=true;
            else $this->has_children=false;
        }
        else $this->has_children=false;
        if($this->has_children) {
            //get first rec that is not child
            if(!$query=$this->uCore->query("uKnowbase","SELECT
            `rec_id`,
            `rec_position`
            FROM
            `u235_records`
            WHERE
            `rec_position`>".$this->rec->rec_position." AND
            `rec_indent`<=".$this->rec->rec_indent." AND
            `site_id`='".site_id."'
            ORDER BY
            `rec_position` ASC
            LIMIT 1
            ")) $this->uCore->error(11);
            if(mysqli_num_rows($query)) {
                $rec=$query->fetch_object();
                $this->first_non_child_id=$rec->rec_id;
                $this->first_non_child_pos=$rec->rec_position;
            }
            else {
                $this->first_non_child_id=$this->first_non_child_pos=0;
            }
        }
    }
    private function get_clildren() {
        if(!$this->q_children=$this->uCore->query("uKnowbase","SELECT
        `rec_id`,
        `rec_position`,
        `rec_indent`
        FROM
        `u235_records`
        WHERE
        `rec_position`>".$this->rec->rec_position." AND
        `rec_position`<".$this->first_non_child_pos." AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(12);
    }
    private function save_position($rec_position,$rec_id) {
        if(!$this->uCore->query("uKnowbase","UPDATE
        `u235_records`
        SET
        `rec_position`='".$rec_position."',
        ".$this->q_rec_indent."
        WHERE
        `rec_id`='".$rec_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(13);
    }
    private function find_last_position() {
        if(!$query=$this->uCore->query("uKnowbase","SELECT
        `rec_position`
        FROM
        `u235_records`
        WHERE
        `site_id`='".site_id."'
        ORDER BY
        `rec_postion` DESC
        LIMIT 1
        ")) $this->uCore->error(14);
        if(mysqli_num_rows($query)) {
            $rec=$query->fetch_object();
            return $rec->rec_position;
        }
        else return 0;
    }
    private function move_down_starting_with_pos($pos) {
        if(!$this->uCore->query("uKnowbase","UPDATE
        `u235_records`
        SET
        `rec_position`=`rec_position`+1
        WHERE
        `rec_position`>='".$pos."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(15);
    }
    private function move_up_starting_with_pos($pos) {
        if(!$this->uCore->query("uKnowbase","UPDATE
        `u235_records`
        SET
        `rec_position`=`rec_position`-1
        WHERE
        `rec_position`>=".$pos." AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(16);
    }
    private function prevent_duplicate_positions() {
        //get duplicate rec_id
        if(!$query=$this->uCore->query("uKnowbase","SELECT `rec_id`
        FROM
        `u235_records`
        WHERE
        `site_id`='".site_id."'
        GROUP BY
        `rec_position`
        HAVING COUNT(DISTINCT `rec_id`) >1
        LIMIT 1
        ")) $this->uCore->error(17);
        if(mysqli_num_rows($query)) {
            $rec=$query->fetch_object();
            //move rec_position down
            if(!$this->uCore->query("uKnowbase","UPDATE
            `u235_records`
            SET
            `rec_position`=`rec_position`+1
            WHERE
            `rec_id`='".$rec->rec_id."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(18);
            $this->prevent_duplicate_positions();
        }
    }
    private function rec_move($rec) {
        //check if new position is free
        if($this->check_if_position_is_free($this->rec_position)) {
            //check if there are positions after this
            if($this->check_if_any_positions_exists_after($this->rec_position)) {
                //save new position
                $this->save_position($this->rec_position,$rec->rec_id);
            }
            else {
                //find last position
                $last_pos=$this->find_last_position();
                //save new position
                $this->save_position($last_pos+1,$rec->rec_id);
                //move all positions up starting with old one
                $this->move_down_starting_with_pos($last_pos+1);
            }
        }
        else {//new position is busy
            //move all positions down starting with new one
            $this->move_down_starting_with_pos($this->rec_position);
            //save new position
            $this->save_position($this->rec_position,$rec->rec_id);
            //move all positions up starting with old one
            if($this->rec_position>$rec->rec_position)//only if we move down
                $this->move_up_starting_with_pos($rec->rec_position);
        }
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);

        if(!$this->uCore->access(33)) die("{'status' : 'forbidden'}");
        $this->has_children=false;

        if(!$this->check_data()) die("{'status' : 'forbidden'}");

        if($this->apply4children) $this->check_if_there_are_children();
        if($this->has_children) {
            $this->get_clildren();
            $this->rec_move($this->rec);
            while($rec=$this->q_children->fetch_object()) {
                $this->rec_position++;
                $this->rec_move($rec);
            }
        }
        else $this->rec_move($this->rec);

        $this->prevent_duplicate_positions();
        echo "{
        'status' : 'done',
        'rec_id' : '".$this->rec_id."'
        }";
    }
}
$newClass=new uKnowbase_rec_move($this);
