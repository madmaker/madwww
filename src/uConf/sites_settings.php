<?php
namespace uConf\admin;

use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';

class sites_settings {
    /**
     * @var uSes
     */
    public $uSes;
    /**
     * @var bool
     */
    public $mods;
    /**
     * @var uFunc
     */
    private $uFunc;
    public $q_sites,$q_mods;
    private function get_sites() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo('common')->prepare("SELECT
            site_id,
            site_name
            FROM
            u235_sites
            WHERE
            status='active' AND
            main='1'
            ORDER BY
            site_name
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}

        return false;
    }
    private function get_modules() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo('common')->prepare('SELECT
            mod_id,
            mod_name
            FROM
            u235_sites_modules
            WHERE
            site_id=0
            ORDER BY
            mod_name
            ');
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            /** @noinspection PhpStatementHasEmptyBodyInspection */
            for($i=0; $mods[$i]=$stm->fetch(PDO::FETCH_OBJ); $i++) {

            }
            return $mods;
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}

        return false;
    }
    public function __construct (&$uCore) {
        $this->uFunc=new uFunc($uCore);
        $this->uSes=new uSes($uCore);

        $this->q_sites=$this->get_sites();
        $this->mods=$this->get_modules();
    }
}
$uConf=new sites_settings($this);

$this->uFunc->incJs(u_sroot.'uConf/js/sites_settings.min.js');
$this->uFunc->incJs(u_sroot.'js/bootstrap_plugins/PopConfirm/jquery.popconfirm.min.js');

ob_start();?>

<h1 class="page-header"><?=$this->page['page_title']?></h1>

    <div role="tabpanel">

        <!-- Nav tabs -->
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#sites" aria-controls="sites" role="tab" data-toggle="tab">Сайты</a></li>
            <li role="presentation"><a href="#install_mod" aria-controls="install_mod" role="tab" data-toggle="tab">Настройка модулей</a></li>
            <li role="presentation"><a href="#aliases" aria-controls="aliases" role="tab" data-toggle="tab">Зеркала</a></li>
        </ul>

        <!-- Tab panes -->
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane fade in active" id="sites">
                <div class="row">
                    <div class="col-md-12">
                        <label>Имя сайта</label>
                    </div>
                </div>
                <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <select title="" class="form-control" id="uConf_sites_sites_select"></select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <button type="button" class="btn btn-default btn-danger" id="uConf_ban_site_btn" onclick="uConf.ban_site()">Заблокировать сайт</button>
                            </div>
                        </div>
                    <p class="clearfix"> </p>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <button type="button" class="btn btn-default btn-primary" onclick="uConf.new_site_dg_init()">Добавить сайт</button>
                        </div>
                    </div>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane fade" id="install_mod">
                <div class="row">
                    <div class="form-group col-md-6">
                        <label>1. Выберите сайт</label>
                        <select class="form-control" id="uConf_sites_mods_select" onchange="uConf.check_mods()" title=""></select>

                        <label>2. Выберите модуль</label>
                        <select class="form-control" disabled id="uConf_mods_select" title="">
                            <option value="default" id="uConf_mods_opt_default">Выберите модуль, который не установлен</option><?
                            for($i=0;$mod=$uConf->mods[$i];$i++) {
                                if($mod->mod_name !== 'common'&&$mod->mod_name !== 'content'&&$mod->mod_name !== 'mainpage'&&$mod->mod_name !== 'uAuth'&&$mod->mod_name !== 'uConf'&&$mod->mod_name !== 'uCore'&&$mod->mod_name !== 'uEditor'&&$mod->mod_name !== 'uForms'&&$mod->mod_name !== 'uRubrics'&&$mod->mod_name !== 'uSlider') {?>
                                    <option value="<?=$mod->mod_id?>" id="uConf_mods_opt_<?=$mod->mod_id?>"><?=$mod->mod_name?></option>
                                <?}?>
                            <?}?>
                        </select>
                        <div class="form-group">
                            <p>&nbsp;</p>
                            <button type="button" class="btn btn-default btn-primary" id="uConf_install_mod_pr_btn" disabled onclick="uConf.install_mod()">Установить модуль</button>
                        </div>
                    </div>

                    <div class="col-md-6" id="uConf_installed_mods"></div>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane fade" id="aliases">
                <div class="row">
                    <div class="col-md-6">
                        <label>1. Выберите сайт</label>
                        <select class="form-control" id="uConf_sites_aliases_select" onchange="uConf.get_aliases()" title=""></select>
                    </div>
                    <div class="col-md-6" id="uConf_aliases"></div>
                </div>
                <p class="clearfix">&nbsp;</p>
                <div class="row">
                    <div class="col-md-12">
                        <button class="btn btn-primary" onclick="uConf.new_alias_dg_init()" id="uConf_add_alias_btn">Добавить псевдоним</button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Modals -->
    <div class="modal fade" id="uConf_new_site_dg" tabindex="-1" role="dialog" aria-labelledby="uConf_new_site_dgLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="uConf_new_site_dgLabel">Новый сайт</h4>
                </div>
                <div class="modal-body">
                    <div class="text-info" id="uConf_new_site_text_info" style="display: none"></div>
                    <div class="text-danger" id="uConf_new_site_text_danger" style="display: none"></div>
                    <div class="form-group">
                        <label for="uConf_new_site_domain">Домен сайта</label>
                        <input id="uConf_new_site_domain" type="text" class="form-control" placeholder="madplugin.ru">
                        <span class="help-block">Домен, например google.ru, madplugin.ru, mysite.company.com</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                    <button type="button" class="btn btn-primary" onclick="uConf.new_site();">Создать</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="uConf_new_alias_dg" tabindex="-1" role="dialog" aria-labelledby="uConf_new_alias_dgLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="uConf_new_alias_dgLabel">Новое зеркало</h4>
                </div>
                <div class="modal-body">
                    <div class="text-info" id="uConf_new_alias_text_info" style="display: none"></div>
                    <div class="text-danger" id="uConf_new_alias_text_danger" style="display: none"></div>
                    <div class="form-group">
                        <label for="uConf_new_alias_domain">Домен зеркала сайта</label>
                        <input id="uConf_new_alias_domain" type="text" class="form-control" placeholder="www.madplugin.ru">
                        <span class="help-block">Домен, например www.google.ru, www.madplugin.ru</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                    <button type="button" class="btn btn-primary" onclick="uConf.new_alias();">Добавить зеркало</button>
                </div>
            </div>
        </div>
    </div>




<script type="text/javascript">
    if(typeof uConf==="undefined") {
        uConf = {};

        uConf.mods = [];
        uConf.site_id = [];
        uConf.site_name = [];
        uConf.site_show = [];
        uConf.site_id2i = [];
    }
    <?for($i=0;$mod=$uConf->mods[$i];$i++) {?>
    uConf.mods[uConf.mods.length]=<?=$mod->mod_id?>;
    <?}?>
    let i=0;
    <?while($site=$uConf->q_sites->fetch(PDO::FETCH_OBJ)) {?>
    i = uConf.site_id.length;
    uConf.site_id[i]=<?=$site->site_id?>;
    uConf.site_name[i]="<?=$site->site_name?>";
    uConf.site_show[i]=true;
    uConf.site_id2i[uConf.site_id[i]]=i;
    <?}?>
</script>

<?$this->page_content=ob_get_contents();
ob_end_clean();

include "templates/u235/template.php";
