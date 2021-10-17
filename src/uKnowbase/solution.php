<?php
require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";

class uKnowbase_solution{
    public $uFunc;
    public $uSes;
    private $uCore;
    public $sol,$rec_id,$q_files,$edit_allowed,$has_access;
    private function check_data() {
        if(!isset($this->uCore->url_prop[1])) header('Location: '.u_sroot.$this->uCore->mod.'/records');
        $this->rec_id=$this->uCore->url_prop[1];
        if(!uString::isDigits($this->rec_id)) header('Location: '.u_sroot.$this->uCore->mod.'/records');
    }
    private function check_access() {
        $this->has_access=false;
        $this->get_solution();
        $this->edit_allowed=false;
        if($this->uCore->access(38)) return $this->edit_allowed=true;//uKnowbase admin
        if($this->uCore->access(33)) {
            if(!$query=$this->uCore->query("uKnowbase","SELECT
            `user_id`
            FROM
            `u235_records`
            WHERE
            `rec_id`='".$this->rec_id."' AND
            `user_id`='".$this->uSes->get_val("user_id")."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(10);
            if(mysqli_num_rows($query)) $this->edit_allowed=true;
            return true;
        }

        if($this->sol->rec_status=='new') return false;
        if($this->sol->access_limited=='0') return true;

        //get user's comps
        if(!$query=$this->uCore->query("uSup","SELECT
        `com_id`
        FROM
        `u235_com_users`
        WHERE
        `user_id`='".$this->uSes->get_val("user_id")."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(20);

        $q_comps='(1=0';
        while($com=$query->fetch_object()) {
            $q_comps.=" OR `com_id`='".$com->com_id."' ";
        }
        $q_comps.=')';

        //check if at least for one user's company access is allowed
        if(!$query=$this->uCore->query("uKnowbase","SELECT
        `rec_id`
        FROM
        `u235_records_comps`
        WHERE
        ".$q_comps." AND
        `rec_id`='".$this->rec_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(30);

        if(mysqli_num_rows($query)) return true;

        return false;
    }
    private function get_solution() {
        if(!$query=$this->uCore->query("uKnowbase","SELECT
        `is_section`,
        `rec_title`,
        `rec_descr`,
        `rec_solution`,
        `rec_status`,
        `access_limited`
        FROM
        `u235_records`
        WHERE
        `rec_id`='".$this->rec_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(40);
        $this->sol=$query->fetch_object();

        $this->sol->rec_title=uString::sql2text($this->sol->rec_title);
    }
    public function get_files() {
        if(!$this->q_files=$this->uCore->query("uKnowbase","SELECT
        `file_id`,
        `file_name`,
        `file_mime`,
        `file_size`
        FROM
        `u235_records_files`
        WHERE
        `rec_id`='".$this->rec_id."' AND
        `site_id`='".site_id."'
        ORDER BY
        `file_id` ASC
        ")) $this->uCore->error(50);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);

        if($this->uCore->access(2)||$this->uCore->uFunc->getConf("uknowbase_access_only_4_auth","uKnowbase")=='0'){
            $this->check_data();
            if($this->check_access()) {
                $this->has_access=true;
                $this->get_files();
            }
        }
    }
}
$uKb=new uKnowbase_solution($this);
ob_start();

if($this->access(2)||$this->uFunc->getConf("uknowbase_access_only_4_auth","uKnowbase")=='0'){
    if($uKb->has_access) {
        //tinymce
        $this->uFunc->incJs(u_sroot.'js/tinymce/tinymce.min.js');

        //fancybox
        $this->uFunc->incJs(u_sroot.'js/fancybox/jquery.fancybox.pack.js');
        $this->uFunc->incCss(u_sroot.'js/fancybox/jquery.fancybox.css',true);

        //confirmation dg
        $this->uFunc->incJs(u_sroot.'js/bootstrap_plugins/PopConfirm/jquery.popconfirm.min.js',false);

        //common
        $this->uFunc->incJs(u_sroot.'uForms/js/'.$this->page_name.'.min.js',false);
        $this->uFunc->incCss(u_sroot.'uKnowbase/css/uKnowbase.min.css');

        $this->page['page_title']=$uKb->sol->rec_title;
        ?>

        <div class="row">
            <div class="col-md-12 uKnowbase uKnowbase_solution">
                <?if($uKb->edit_allowed){?><button class="btn btn-danger pull-right" style="margin-left: 10px;" id="uKnowbase_solution_delete_btn" onclick="uKnowbase.delete_record()">Удалить решение</button><?}?>
                <?if($uKb->edit_allowed&&$uKb->sol->rec_status=='new'&&$this->uFunc->getConf("auto_publish","uKnowbase")=='0'){?><button class="btn btn-success pull-right" id="uKnowbase_solution_publish_btn" onclick="uKnowbase.publish()">Опубликовать решение</button>&nbsp;<?}?>
                <a href="<?=u_sroot.$this->mod?>/records" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-arrow-left"></span> Назад к базе знаний</a>
                <h1 class="page-header"><?=$uKb->sol->rec_title?> <?if($uKb->edit_allowed){?><button class="btn btn-sm btn-default u235_eip" onclick="uKnowbase.edit_title()"><span class="glyphicon glyphicon-pencil"></span></button><?}?></h1>

                <div class="bs-callout bs-callout-warning">
                    <h4>Проблема:</h4>
                    <div class="rec_descr_show"><?=uString::sql2text($uKb->sol->rec_descr,true)?></div>
                    <div class="rec_descr" style="display: none;"><div class="html" id="uKnowbase_solution_rec_descr_html"></div></div>
                </div>
                <div class="bs-callout bs-callout-success">
                    <h4>Решение:</h4>
                    <div class="rec_solution_show"><?=uString::sql2text($uKb->sol->rec_solution,true)?></div>
                    <div class="rec_solution" style="display: none;"><div class="html" id="uKnowbase_solution_rec_sol_html"></div></div>
                </div>
            </div>
        </div>

        <?if($uKb->edit_allowed){?>
            <div id="uKnowbase_filelist"></div>
        <?}?>

        <div class="row">
            <div class="col-md-12">

                <?if($uKb->edit_allowed) {?><div id="uploader" style="display: none"></div><?}?>
            </div>
        </div>

        <?if($uKb->edit_allowed) {?>
            <div class="modal fade" id="uKnowbase_edit_title_dg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                            <h4 class="modal-title" id="myModalLabel">Изменить заголовок записи</h4>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <input type="text" class="form-control" id="uKnowbase_edit_title_input">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                            <button type="button" class="btn btn-primary" onclick="uKnowbase.edit_title_save()">Сохранить</button>
                        </div>
                    </div>
                </div>
            </div>

        <script type="text/javascript">
            if(typeof uKnowbase==="undefined") {
                uKnowbase = {};

                uKnowbase.file_id = [];
                uKnowbase.file_name = [];
                uKnowbase.file_mime = [];
                uKnowbase.file_size = [];
                uKnowbase.file_selected = [];
                uKnowbase.file_show = [];
            }

            uKnowbase.rec_id=<?=$uKb->rec_id?>;
            uKnowbase.rec_title="<?=rawurlencode($uKb->sol->rec_title)?>";
            uKnowbase.is_section=<?=$uKb->sol->is_section?>;

                <?for($i=0;$file=$uKb->q_files->fetch_object();$i++) {?>
            i=<?=$i?>;
            uKnowbase.file_id[i]=<?=$file->file_id?>;
            uKnowbase.file_name[i]="<?=rawurlencode(uString::sql2text($file->file_name))?>";
            uKnowbase.file_mime[i]="<?=$file->file_mime?>";
            uKnowbase.file_size[i]=<?=$file->file_size?>;
            uKnowbase.file_show[i]=true;
            <?}?>

            <?$sessions_hack=$this->uFunc->sesHack();?>
            uKnowbase.sessions_hack_hash='<?=$sessions_hack['hash']?>';
            uKnowbase.sessions_hack_id='<?=$sessions_hack['id']?>';
        </script>
        <?}
    }
    else {?>
        <div class="jumbotron">
            <h1 class="page-header">База знаний</h1>
            <p>У вас нет доступа к этому решению.</p>
            <p><a href="<?=u_sroot.$this->mod?>/records" class="btn btn-success btn-lg"><span class="glyphicon glyphicon-arrow-left"></span> Список решений базы знаний</a></p>
        </div>
    <?}
}
else {?>
        <div class="jumbotron">
            <h1 class="page-header">База знаний</h1>
            <p>Пожалуйста, авторизуйтесь</p>
            <p><a href="javascript:void(0)" class="btn btn-primary btn-lg"  onclick="uAuth_form.open()">Авторизоваться</a></p>
        </div>
<?}
$this->page_content=ob_get_contents();
ob_end_clean();

include "templates/template.php";
