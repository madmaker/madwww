<?php
namespace uRubrics\admin;

use PDO;
use PDOException;
use processors\uFunc;
use uPage\common;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uPage/inc/common.php";

class rubrics_admin_edit_bg{
    private $uPage;
    private $uSes;
    private $uFunc;
    private $uCore,$rubric_id,$rubric_name;

    public function text($str) {
        return $this->uCore->text(array('uRubrics','rubrics_admin_edit_bg'),$str);
    }

    private function check_data() {
        if(!isset($_POST['rubric_id'])) $this->uFunc->error(10);
        if(!uString::isDigits($_POST['rubric_id'])) $this->uFunc->error(20);
        $this->rubric_id=$_POST['rubric_id'];
    }
    private function update_rubric() {
        $rubric_name=$this->text('News - default news name');
        $pages_limit_on_news_list=10;
        $pages_limit=20;
        $display_style=0;

        if(!isset(
            $_POST["rubric_name"],
            $_POST["pages_limit_on_news_list"],
            $_POST["pages_limit"],
            $_POST["display_style"]
        )) $this->uFunc->error(0,1);

        $_POST['rubric_name']=trim($_POST['rubric_name']);
        if($_POST['rubric_name']!=="") $rubric_name=$_POST['rubric_name'];

        $_POST['pages_limit']=(int)$_POST['pages_limit'];
        if($_POST['pages_limit']>=0&&$_POST['pages_limit']<=50) $pages_limit=$_POST['pages_limit'];

        $_POST['pages_limit_on_news_list']=(int)$_POST['pages_limit_on_news_list'];
        if($_POST['pages_limit_on_news_list']>0&&$_POST['pages_limit_on_news_list']<=20) $pages_limit_on_news_list=$_POST['pages_limit_on_news_list'];

        $_POST['display_style']=(int)$_POST['display_style'];
        if($_POST['display_style']>0&&$_POST['display_style']<=4) $display_style=$_POST['display_style'];

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("UPDATE
            u235_urubrics_list
            SET
            rubric_name=:rubric_name,
            display_style=:display_style,
            pages_limit=:pages_limit,
            pages_limit_on_news_list=:pages_limit_on_news_list
            WHERE
            rubric_id=:rubric_id AND
            site_id=:site_id
            ");

            $rubric_name=uString::text2sql($rubric_name);
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':rubric_name', $rubric_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':display_style', $display_style,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':pages_limit', $pages_limit,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':pages_limit_on_news_list', $pages_limit_on_news_list,PDO::PARAM_INT);

            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':rubric_id', $this->rubric_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }
    private function update_rubric_name() {
        if(!isset($_POST['rubric_name'])) $this->uFunc->error(0,1);
        $rubric_name=trim($_POST['rubric_name']);
        if($rubric_name=="") die("{'status' : 'error', 'msg' : 'title_empty'}");
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("UPDATE
            u235_urubrics_list
            SET
            rubric_name=:rubric_name
            WHERE
            rubric_id=:rubric_id AND
            site_id=:site_id
            ");
            $rubric_name=uString::text2sql($this->rubric_name);
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':rubric_id', $this->rubric_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':rubric_name', $rubric_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uSes=new \uSes($this->uCore);
        if(!$this->uSes->access(7)) die('forbidden');

        $this->uFunc=new uFunc($this->uCore);
        $this->uPage=new common($this->uCore);


        $this->check_data();
        if(isset($_POST["display_style"])) $this->update_rubric();
        else $this->update_rubric_name();

        $this->uPage->clean_cache4uRubrics($this->rubric_id);

        echo json_encode(array(
        'status' => 'done',
        'rubric_id' => $this->rubric_id
        ));
    }
}
new rubrics_admin_edit_bg($this);
