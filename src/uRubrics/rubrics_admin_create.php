<?php
require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";

class rubrics_admin_create {
    private $uFunc;
    private $uSes;
    private $uCore,$rubric_name,$rubric_id;
    private function check_data() {
        if(!isset($_POST['rubric_title'])) $this->uFunc->error(10);
        $this->rubric_name=trim($_POST['rubric_title']);
        if(!strlen($this->rubric_name)) die("{'status' : 'error', 'msg' : 'title_empty'}");
    }
    private function get_new_rubric_id() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("SELECT
            rubric_id
            FROM
            u235_urubrics_list
             WHERE
             site_id=:site_id
            ORDER BY
            rubric_id DESC
            LIMIT 1
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) $this->rubric_id=$qr->rubric_id+1;
            else $this->rubric_id=1;
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
    }
    private function create_rubric() {
        $this->get_new_rubric_id();

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("INSERT INTO u235_urubrics_list (
            rubric_id,
            rubric_name,
            `timestamp`,
            site_id
            ) VALUES (
            :rubric_id,
            :rubric_name,
            :timestamp,
            :site_id
            )
            ");
            $timestamp=time();
            $rubric_name=uString::text2sql($this->rubric_name,1);
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':rubric_id', $this->rubric_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':rubric_name', $rubric_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':timestamp', $timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}

        echo json_encode(array(
        'status' => 'done',
        'rubric_id' => $this->rubric_id,
        'rubric_name' => $this->rubric_name
        ));
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new uCore();
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");
        
        $this->uFunc=new \processors\uFunc($this->uCore);

        $this->check_data();
        $this->create_rubric();
    }
}
new rubrics_admin_create ($this);