<?php
namespace uCat\admin;
use processors\uFunc;
use uCat\common;use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uCat/classes/common.php";

class import{/**
 * @var common
 */public $uCat;
    public $import_files_ar;
    /**
     * @var int
     */
    public $import_files_ar_count;
    /**
 * @var uFunc
 */private $uFunc;
    /**
 * @var uSes
 */private $uSes;private $uCore;

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(5)) die("{'status' : 'forbidden'}");
        $this->uFunc=new uFunc($this->uCore);
        $this->uCat=new common($this->uCore);

        $this->uFunc->incJs(u_sroot."/uCat/js/import.min.js");

        $import_files_stm=$this->uCat->get_import_files("list_name,lines_total,lines_to_skip,lines_imported,status");
        /** @noinspection PhpUndefinedMethodInspection */
        $this->import_files_ar=$import_files_stm->fetchAll(\PDO::FETCH_OBJ);
        $this->import_files_ar_count=count($this->import_files_ar);
    }
}
$uCat=new import($this);

ob_start();
?>
<div class="uCat uCat_imp_exp">
    <h1 class="page-header">Импорт</h1>
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                        <div id="uCat_import_uploader"></div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <h5>Панель настроек</h5>
                        <div class="custom-control custom-checkbox">
                            <label class="custom-control-label" for="count-colunm-check">
                            <input type="checkbox" class="custom-control-input" id="count-colunm-check">
                                Сколько строк пропустить
                            </label>
                            <input type="number" id="count-colunm" min="1" max="1" value="1">
                        </div>
                        <div class="custom-control custom-checkbox" id="panel-param-csv" style="display: none;">
                            <input type="checkbox" class="custom-control-input" id="csv-file-check">
                            <label class="custom-control-label" for="csv-file-check">Настроить параметры CSV </label>
                            <div class="form-group" style="display: none;">
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                                    <label for="csv-encoding">Кодировка</label>
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                                    <select id="select-csv-encoding" class="csv-option-select" name="csv-encoding" disabled>
                                        <option value="empty">Выберите пункт</option>
                                        <option value="utf-8">Юникод(utf-8)</option>
                                        <option value="cp1251">Windows-1251</option>
                                        <option value="koi8-r">KOI8-R</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                                    <label for="csv-newline">Разделитель полей</label>
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                                    <select id="select-csv-newline" class="csv-option-select" name="csv-newline" disabled>
                                        <option value="empty">Выберите пункт</option>
                                        <option value="comma">,</option>
                                        <option value="semicolon">;</option>
                                        <option value="colon">:</option>
                                        <option value="tab">Табуляция</option>
                                        <option value="space">Пробел</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group" style="display: none;">
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                                    <label for="csv-text-separator">Разделитель текста</label>
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                    <select id="select-csv-text-separator" class="csv-option-select" name="csv-text-separator" disabled>
                                        <option value="empty">Выберите пункт</option>
                                        <option value="single_quote">'</option>
                                        <option value="double_quote">"</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <button class="csv-apply-btn btn btn-success" disabled>Применить</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <table id="import-table" class="table table-striped table-bordered"></table>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-center">
                    <button class="import-preview-btn btn btn-default" disabled>Предпросмотр</button>
                    <button class="import-btn btn btn-success" disabled>Импортировать</button>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <h3>Импортированные прайсы</h3>
                    <table class="table table-striped">
                        <tr>
                            <th>Название</th>
                            <th>Всего строк</th>
                            <th>Статус</th>
                        </tr>
                        <?for($i=0;$i<$uCat->import_files_ar_count;$i++) {
                            $file=$uCat->import_files_ar[$i];?>
                            <tr>
                                <td><?=$file->list_name?></td>
                                <td><?
                                    $lines2import=$file->lines_total-$file->lines_to_skip;
                                    if($lines2import>0) print $lines2import;
                                    ?></td>
                                <td>
                                    <?=$file->lines_imported?> обработано
                                    <br>
                                    <?$file->status=(int)$file->status;
                                    if($file->status===0) print "Подготовка";
                                    elseif($file->status===1) print "Обработка";
                                    elseif($file->status===2) print "Завершено";
                                    ?>
                                </td>
                            </tr>
                        <?}?>
                    </table>
                </div>
            </div>


    <ul class="list-group">
        <li class="list-group-item active">
            <h4 class="list-group-item-heading">Обязательные поля?</h4>
            <p class="list-group-item-text">Для того чтобы сделать импорт необходимо выбрать колонки НАИМЕНОВАНИЕ ТОВАРА и ЦЕНА.</p>
        </li>
        <li class="list-group-item">
            <h4 class="list-group-item-heading">Если не указывать раздел и категорию?</h4>
            <p class="list-group-item-text">Если не указывать НАИМЕНОВАНИЕ РАЗДЕЛА и НАИМЕНОВАНИЕ КАТЕГОРИИ, то все товары будут прикреплены к разделу "БЕЗ РАЗДЕЛА" и категории "БЕЗ КАТЕГОРИИ".</p>
        </li>
        <li class="list-group-item">
            <h4 class="list-group-item-heading">Если указать только Наименование категории?</h4>
            <p class="list-group-item-text">Если не указанно НАИМЕНОВАНИЕ РАЗДЕЛА, то все категории будут прикреплены к разделу "БЕЗ РАЗДЕЛА".</p>
        </li>
        <li class="list-group-item">
            <h4 class="list-group-item-heading">Если указать только Наименование раздела?</h4>
            <p class="list-group-item-text">Если не указанно НАИМЕНОВАНИЕ КАТЕГОРИИ, то все товары будут прикреплены к категории "БЕЗ КАТЕГОРИИ", но при этом категория не будет прикреплена к разделу.</p>
        </li>
        <li class="list-group-item">
            <h4 class="list-group-item-heading">Если указать Наименование раздела и Наименование категории?</h4>
            <p class="list-group-item-text">Если указаны НАИМЕНОВАНИЕ РАЗДЕЛА и НАИМЕНОВАНИЕ КАТЕГОРИИ, то все товары будут прикреплены к категориям, а категории к разделам. В соответствии с указанной иерархией.</p>
        </li>
    </ul>
</div>
<?
$this->page_content=ob_get_contents();
ob_end_clean();
include 'templates/u235/template.php';
