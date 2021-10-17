<?php
namespace uEvents\admin;

use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class admin_events_types_save_bg {
    public $uFunc;
    public $uSes;
    private $uCore,$type_id,$field;
    private function check_data() {
        if(!isset($_POST['type_id'],$_POST['field'])) $this->uFunc->error(10);
        $this->type_id=$_POST['type_id'];
        if(!uString::isDigits($this->type_id)) $this->uFunc->error(20);
        $this->field=$_POST['field'];
        if($this->field=='type_title') return true;
        if($this->field=='type_url') return true;
        if($this->field=='type_descr') return true;
        $this->uFunc->error(30);
        return false;
    }

    private function update_type_title() {
        if(!isset($_POST['type_title'])) $this->uFunc->error(40);
        $type_title=trim($_POST['type_title']);
        if(!strlen($type_title)) die('{
        "status":"error",
        "msg":"title is empty"
        }');


        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uEvents")->prepare("UPDATE
            u235_events_types
            SET
            type_title=:type_title
            WHERE
            type_id=:type_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $type_title=uString::text2sql($type_title);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':type_id', $this->type_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':type_title', $type_title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}

        echo '{
        "status":"done",
        "type_title":"'.rawurlencode($type_title).'"
        }';

        $this->clear_cache(true);
    }
    private function update_type_url() {
        if(!isset($_POST['type_url'])) $this->uFunc->error(60);
        $type_url=trim($_POST['type_url']);
        if(!strlen($type_url)) die('{
        "status":"error",
        "msg":"url is empty"
        }');
        if(uString::isDigits($type_url)) die('{
        "status":"error",
        "msg":"not only number in url"
        }');
        if(!uString::isFilename_rus($type_url)) die('{
        "status":"error",
        "msg":"url is wrong"
        }');

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uEvents")->prepare("UPDATE
            u235_events_types
            SET
            type_url=:type_url
            WHERE
            type_id=:type_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $type_url=uString::text2sql($type_url);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':type_id', $this->type_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':type_url', $type_url,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}

        echo '{
        "status":"done",
        "type_url":"'.rawurlencode($type_url).'"
        }';

        $this->clear_cache(true);
    }
    private function update_type_descr() {
        if(!isset($_POST['type_descr'])) $this->uFunc->error(80);
        $type_descr=trim($_POST['type_descr']);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uEvents")->prepare("UPDATE
            u235_events_types
            SET
            type_descr=:type_descr
            WHERE
            type_id=:type_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $type_descr=uString::text2sql($type_descr);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':type_id', $this->type_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':type_descr', $type_descr,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('90'/*.$e->getMessage()*/);}

        echo '{
        "status":"done",
        "type_descr":"'.rawurlencode($type_descr).'"
        }';
        $this->clear_cache(false);
    }

    private function clear_cache($clear_same_events=true) {
        //delete cache
        //for type
        uFunc::rmdir('uEvents/cache/events/'.site_id.'/'.$this->type_id);
        if($clear_same_events) {
            //for all same type events
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uEvents")->prepare("SELECT
                event_id
                FROM
                u235_events_list
                WHERE
                event_type_id=:event_type_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':event_type_id', $this->type_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('100'/*.$e->getMessage()*/);}

            /** @noinspection PhpUndefinedVariableInspection */
            /** @noinspection PhpUndefinedMethodInspection */
            while($ev=$stm->fetch(PDO::FETCH_OBJ)) {
                uFunc::rmdir('uEvents/cache/event/'.site_id.'/'.$ev->event_id);
            }
        }
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);

        if(!$this->uSes->access(300)) die("{'status' : 'forbidden'}");
        $this->check_data();
        if($this->field=='type_title') $this->update_type_title();//clear cache for type and dependent events
        elseif($this->field=='type_url') $this->update_type_url();//clear cache for type and dependent events
        elseif($this->field=='type_descr') $this->update_type_descr();//clear cache only for type
        $this->uFunc->set_flag_update_sitemap(1, site_id);
    }
}
new admin_events_types_save_bg($this);