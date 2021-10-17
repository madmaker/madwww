<?php
namespace uForms\admin;

use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class admin_forms_delete_bg {
    /**
     * @var int
     */
    private $form_id;
    /**
     * @var uSes
     */
    private $uSes;
    /**
     * @var uFunc
     */
    private $uFunc;
    private $uCore;
    private function checkData() {
        if(!isset($_POST['form_id'])) $this->uFunc->error(10,1);
        $this->form_id=(int)$_POST["form_id"];
    }
    private function update_formStatus($form_id,$site_id=site_id) {
        $timestamp=time();
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("UPDATE
            u235_forms
            SET
            status='deleted',
            timestamp=:timestamp
            WHERE
            form_id=:form_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':timestamp', $timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':form_id', $form_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/,1);}
    }
    private function delete_form_from_uEvents($form_id,$site_id=site_id) {
        if($this->uCore->uFunc->mod_installed('uEvents')) {//delete form from uEvents
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uEvents")->prepare("UPDATE
                u235_events_list
                SET
                form_id=0
                WHERE
                form_id=:form_id AND
                site_id=:site_id
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':form_id', $form_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/,1);}
        }
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) /** @noinspection PhpFullyQualifiedNameUsageInspection */ $this->uCore=new \uCore();

        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        if(!$this->uCore->access(5)) die('forbidden');

        $this->checkData();

        $this->delete_form_from_uEvents($this->form_id);
        $this->update_formStatus($this->form_id);

        echo json_encode(array(
            'status'=>'done'
        ));
    }
}
new admin_forms_delete_bg($this);
