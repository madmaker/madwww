<?php
class uSup_requests_admin_set_rec_id_bg {
    private $uCore,
        $tic_id,$rec_id;

    private function check_data() {
        if(!isset($_POST['tic_id'],$_POST['rec_id'])) $this->uCore->error(10);
        $this->tic_id=&$_POST['tic_id'];
        $this->rec_id=&$_POST['rec_id'];
        if(!uString::isDigits($this->tic_id)) $this->uCore->error(20);
        if(!uString::isDigits($this->rec_id)) $this->uCore->error(30);
    }
    private function check_rec_id() {
        if(!$query=$this->uCore->query("uKnowbase","SELECT
        `rec_id`
        FROM
        `u235_records`
        WHERE
        `rec_id`='".$this->rec_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(40);
        if(!mysqli_num_rows($query)) $this->uCore->error(50);
    }
    private function set_rec_id() {
        if(!$query=$this->uCore->query("uSup","SELECT
        `id`
        FROM
        `u235_uKnowbase_solutions_requests`
        WHERE
        `tic_id`='".$this->tic_id."' AND
        `sol_id`='".$this->rec_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(60);

        if(!$this->uCore->query("uSup","UPDATE
        `u235_requests`
        SET
        `uknowbase_solution_isset`='1',
        `uknowbase_no_solution_reason`='',
        `uknowbase_no_solution_user_id`='0'
        WHERE
        `tic_id`='".$this->tic_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(70);

        if(mysqli_num_rows($query)) {
            if(!$query=$this->uCore->query("uSup","DELETE FROM
            `u235_uKnowbase_solutions_requests`
            WHERE
            `tic_id`='".$this->tic_id."' AND
            `sol_id`='".$this->rec_id."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(80);

            if(!$query=$this->uCore->query("uSup","SELECT
            `id`
            FROM
            `u235_uKnowbase_solutions_requests`
            WHERE
            `tic_id`='".$this->tic_id."' AND
            `site_id`='".site_id."'
            LIMIT 1
            ")) $this->uCore->error(90);

            if(mysqli_num_rows($query)) {
                $sol_isset='1';
            }
            else {
                $sol_isset='0';
                if(!$this->uCore->query("uSup","UPDATE
                `u235_requests`
                SET
                `uknowbase_solution_isset`='0'
                WHERE
                `tic_id`='".$this->tic_id."' AND
                `site_id`='".site_id."'
                ")) $this->uCore->error(100);
            }

            echo "{
            'status' : 'done',
            'tic_id' : '".$this->tic_id."',
            'rec_id' : '".$this->rec_id."',
            'action' : 'deleted',
            'sol_isset' : '".$sol_isset."'
            }";
        }
        else {
            if(!$query=$this->uCore->query("uSup","SELECT
            `id`
            FROM
            `u235_uKnowbase_solutions_requests`
            WHERE
            `site_id`='".site_id."'
            ORDER BY
            `id` DESC
             LIMIT 1
            ")) $this->uCore->error(110);
            if(mysqli_num_rows($query)) {
                $qr=$query->fetch_object();
                $id=$qr->id+1;
            }
            else $id=1;

            if(!$query=$this->uCore->query("uSup","INSERT INTO
            `u235_uKnowbase_solutions_requests` (
            `id`,
            `tic_id`,
            `sol_id`,
            `site_id`
            ) VALUES (
            '".$id."',
            '".$this->tic_id."',
            '".$this->rec_id."',
            '".site_id."'
            )
            ")) $this->uCore->error(120);

            echo "{
            'status' : 'done',
            'tic_id' : '".$this->tic_id."',
            'rec_id' : '".$this->rec_id."',
            'action' : 'assigned',
            'sol_isset' : '1'
            }";
        }
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(9)&&!$this->uCore->access(8)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->check_rec_id();
        $this->set_rec_id();
    }
}
$uSup=new uSup_requests_admin_set_rec_id_bg($this);
