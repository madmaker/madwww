<?
namespace uForms\admin;

use PDO;
use PDOException;
use processors\uFunc;
use uSes;
use uString;

require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';

class admin_forms {
    public $uFunc;
    public $uSes;
    private $uCore;
    public $hash;
    public $q_forms;

    public function text($str) {
        return $this->uCore->text(array('uForms','admin_forms'),$str);
    }

    public function getForms(){
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT DISTINCT 
            form_id,
            form_title,
            form_descr,
            msg_count,
            cols_els_id
            FROM
            u235_forms
            LEFT JOIN 
            `madmakers_uPage`.`u235_cols_els`
            ON
            el_id=form_id AND
            u235_forms.site_id=`madmakers_uPage`.`u235_cols_els`.site_id AND
            el_type='form'
            WHERE
            (
                status!='deleted' OR
                status IS NULL
            ) AND 
            u235_forms.site_id=:site_id 
            GROUP BY(form_id)
            ORDER BY
            msg_count DESC,
            form_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('10'.$e->getMessage());}
        return 0;
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) /** @noinspection PhpFullyQualifiedNameUsageInspection */ $this->uCore=new \uCore();

        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);

        $this->uCore->page['page_title']=$this->text("Page name"/*Формы сайта*/);

        if($this->uSes->access(6)) {
            $this->uCore->uInt_js('uForms', 'admin_forms');
        }
    }
}
$uForms=new admin_forms($this);

if(isset($_POST['list_only'])) {
    if ($uForms->uSes->access(6)||$uForms->uSes->access(300)) { ?>
        <table class="table table-condensed table-hover">
            <?
            $q_forms = $uForms->getForms();
            /** @noinspection PhpUndefinedMethodInspection */
            for ($i = 0; $form = $q_forms->fetch(PDO::FETCH_OBJ); $i++) { ?>
                <tr>
                    <td>
                        <button class="btn btn-success btn-xs uTooltip"
                                title="<?= $uForms->text("Assign current form - btn txt"/*Назначить эту форму*/) ?>"
                                onclick="uEvents_event_admin.assign_form_do(<?= $form->form_id ?>)"><span
                                    class="glyphicon glyphicon-ok"></span></button>
                    </td>
                    <td>
                        <a href="<?= u_sroot ?>uForms/form/<?= $form->form_id ?>"
                           target="_blank"><?= uString::sql2text($form->form_title, 1) ?></a>
                    </td>
                </tr>
            <?
            } ?>
        </table>
    <?
    }
    else {?>forbidden<?}
}
else {
    ob_start();

    if($uForms->uSes->access(6)) {
        require_once 'uForms/dialogs/admin_forms.php';

        $this->uFunc->incJs('/uForms/js/admin_forms.min.js');?>

        <h1><?=$this->page['page_title']?></h1>


        <div class="uForms u235_admin">
            <div class="list" id="uForms_admin_forms"></div>
        </div>


        <script type="text/javascript">
            if(typeof uForms_admin==="undefined") {
                uForms_admin = {};
                uForms_admin.form_id = [];
                uForms_admin.cols_els_id= [];
                uForms_admin.form_title = [];
                uForms_admin.msg_count = [];
                uForms_admin.form_id2index = [];
            }
            <?
            $q_forms=$uForms->getForms();
            /** @noinspection PhpUndefinedMethodInspection */
            for($i=0;$form=$q_forms->fetch(PDO::FETCH_OBJ);$i++) { ?>
                uForms_admin.form_id[<?=$i?>]=<?=$form->form_id?>;
                uForms_admin.cols_els_id[<?=$i?>]="<?=$form->cols_els_id?>";
                uForms_admin.form_title[<?=$i?>]="<?=rawurlencode(uString::sql2text($form->form_title))?>";
                uForms_admin.msg_count[<?=$i?>]=<?=$form->msg_count?>;
                uForms_admin.form_id2index[uForms_admin.form_id[<?=$i?>]]=<?=$i?>;
            <?}?>
        </script>

    <?}
    else {?>
        <div class="container">
        <div class="jumbotron">
            <h1 class="page-header"><?=$uForms->text("Forbidden")?></h1>
            <?if($uForms->uSes->access(2)) {?>
            <p><?=$uForms->text("You do not have sufficient permissions to access this page")?></p>
        <?} else {?>
            <p><?=$uForms->text("Sign in please")?></p>
            <p><a href="javascript:void(0)" class="btn btn-primary btn-lg"  onclick="uAuth_form.open()"><?=$uForms->text("Sign in - btn label")?></a></p>
            <?}?>
        </div>
        </div>
    <?}
    $this->page_content=ob_get_contents();
    ob_end_clean();
    include "templates/u235/template.php";
}
?>
