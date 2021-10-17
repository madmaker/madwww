<?php
class uSubscr_rec_editor{
    private $uCore;
    public $rec_id,$rec_title,$rec_html,$q_files,$q_groups,$assigned_groups_ar;
    private function checkData() {
        if(!isset($this->uCore->url_prop[1])) header('Location: '.u_sroot.$this->uCore->mod.'/records');
        $this->rec_id=$this->uCore->url_prop[1];
        if(!uString::isDigits($this->rec_id)) header('Location: '.u_sroot.$this->uCore->mod.'/records');
    }
    private function get_rec() {
        if(!$query=$this->uCore->query("uSubscr","SELECT
        `rec_title`,
        `rec_html`
        FROM
        `u235_records`
        WHERE
        `rec_id`='".$this->rec_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(4);
        $rec=$query->fetch_object();

        $this->rec_title=uString::sql2text($rec->rec_title);
        $this->rec_html=uString::sql2text($rec->rec_html,true);
    }
    private  function get_files() {
        if(!$this->q_files=$this->uCore->query("uSubscr","SELECT
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
        ")) $this->uCore->error(5);
    }
    private function get_groups() {
        //get all groups
        if(!$this->q_groups=$this->uCore->query("uSubscr","SELECT
        `gr_id`,
        `gr_title`
        FROM
        `u235_groups`
        WHERE
        (`status` IS NULL OR `status`='active') AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(6);

        //get assigned groups
        if(!$query=$this->uCore->query("uSubscr","SELECT
        `gr_id`
        FROM
        `u235_records_groups`
        WHERE
        `rec_id`='".$this->rec_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(7);
        while($gr=$query->fetch_object()) {
            $this->assigned_groups_ar[$gr->gr_id]=1;
        }
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->checkData();
        $this->get_rec();
        $this->get_files();
        $this->get_groups();
    }
}
$uSubscr=new uSubscr_rec_editor($this);
//tinymce
$this->uFunc->incJs(u_sroot.'js/tinymce/tinymce.min.js');

//fancybox
$this->uFunc->incJs(u_sroot.'js/fancybox/jquery.fancybox.pack.js');
$this->uFunc->incCss(u_sroot.'js/fancybox/jquery.fancybox.css',true);

$this->uFunc->incJs(u_sroot.'uSubscr/js/'.$this->page_name.'.min.js',false);
$this->uFunc->incCss(u_sroot.'uSubscr/css/default.min.css');

$this->page['page_title']=$uSubscr->rec_title;
ob_start();?>
<div class="row">
    <div class="col-md-12 uSubscr uSubscr_solution">
        <a href="<?=u_sroot.$this->mod?>/records" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-arrow-left"></span> Назад к списку</a>

        <button type="button" class="pull-right btn btn-primary" onclick="uSubscr.run_mailing_dg()">Запустить рассылку</button>

        <h1 class="page-header"><?=$uSubscr->rec_title?> <button class="btn btn-sm btn-default" onclick="uSubscr.edit_title()"><span class="glyphicon glyphicon-pencil"></span></button></h1>

        <div class="clearfix">&nbsp;</div>

        <h4>Группы рассылки: <small><a href="<?=u_sroot.$this->mod?>/groups">Открыть страницу редактора групп</a><br>
            Эта новость будет рассылаться по выбранным группам</small></h4>

        <div class="row">
        <?while($gr=$uSubscr->q_groups->fetch_object()) {?>
            <div class="col-md-3">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" id="uSubscr_gr_id_<?=$gr->gr_id?>" <?=isset($uSubscr->assigned_groups_ar[$gr->gr_id])?' checked ':''?> onchange="uSubscr.assign_gr2rec(<?=$gr->gr_id?>)">
                        <span><?=uString::sql2text($gr->gr_title)?></span>
                    </label>
                </div>
            </div>
        <?}?>
        </div>

            <h4>Текст новости:</h4>
        <p class="text-muted">Учитывайте, что разные почтовые клиенты будут отображать новость по-разному.<br>Где-то могут не работать таблицы, стили, где-то не будут отображаться изображения. Поэтому старайтесь максимально упростить вашу новость и не использовать лишнее.</p>
        <p>Вы можете использовать заменяемый код:</p>
        <ul class="list-unstyled">
            <li>{user_name} - имя пользователя</li>
        </ul>
            <div class="rec_descr bs-callout bs-callout-default"><div class="html" id="uSubscr_solution_rec_descr_html"><?=uString::sql2text($uSubscr->rec_html,true)?></div></div>
    </div>
</div>

    <div id="uSubscr_filelist"></div>

<div class="row">
    <div class="col-md-12">
        <div id="uploader"></div>
    </div>
</div>

    <div class="modal fade" id="uSubscr_edit_title_dg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="myModalLabel">Изменить заголовок записи</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <input type="text" class="form-control" id="uSubscr_edit_title_input">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-primary" onclick="uSubscr.edit_title_save()">Сохранить</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="uSubscr_run_dg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="myModalLabel">Запуск рассылки</h4>
                </div>
                <div class="modal-body">
                    <p class="text-primary" id="uSubscr_run_pr_text" style="display: none"></p>
                    <p>Действительно запускаем рассылку?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-primary" onclick="uSubscr.run_mailing()">Запускаем!</button>
                </div>
            </div>
        </div>
    </div>

<script type="text/javascript">
    if(typeof uSubscr==="undefined") {
        uSubscr = {};
        uSubscr.file_id = [];
        uSubscr.file_name = [];
        uSubscr.file_mime = [];
        uSubscr.file_size = [];
        uSubscr.file_selected = [];
        uSubscr.file_show = [];
    }
    
    uSubscr.rec_id=<?=$uSubscr->rec_id?>;
    uSubscr.rec_title="<?=rawurlencode($uSubscr->rec_title)?>";

    <?for($i=0;$file=$uSubscr->q_files->fetch_object();$i++) {?>
    i=<?=$i?>;
    uSubscr.file_id[i]=<?=$file->file_id?>;
    uSubscr.file_name[i]="<?=rawurlencode(uString::sql2text($file->file_name))?>";
    uSubscr.file_mime[i]="<?=$file->file_mime?>";
    uSubscr.file_size[i]=<?=$file->file_size?>;
    uSubscr.file_size[i]=false;
    uSubscr.file_show[i]=true;
    <?}?>

    <?$sessions_hack=$this->uFunc->sesHack();?>
    uSubscr.sessions_hack_hash='<?=$sessions_hack['hash']?>';
    uSubscr.sessions_hack_id='<?=$sessions_hack['id']?>';
</script>

<?$this->page_content=ob_get_contents();
ob_end_clean();

include "templates/u235/template.php";
