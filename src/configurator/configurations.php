<?php
namespace configurator;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "configurator/classes/common.php";

class configurations {
    public $configurator;
    public $uFunc;
    private $uSes;
    private $uCore;

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(7)) die("Forbidden");//TODO-nik87 выводить авторизацию вместо этого
        $this->uFunc=new uFunc($this->uCore);

        $this->configurator=new common($this->uCore);

    }
}
$configurator=new configurations($this);
ob_start();?>
<div class="configurator">
    <h1 class="page-header">Конфигурации</h1>
    <table class="table table-condensed table-hover">
        <tr>
            <td>#</td>
            <td>Продукт</td>
            <td>Имя</td>
            <td>E-mail</td>
            <td>Телефон</td>
            <td>Компания</td>
            <td>КП в PDF</td>
            <td>Счет</td>
        </tr>
        <?$configurations=$configurator->configurator->get_configurations("
        conf_id,
        pr_id,
        timestamp,
        name,
        email,
        phone,
        conf_hash,
        company_name,
        bill_number");
        /** @noinspection PhpUndefinedMethodInspection */
        while($conf=$configurations->fetch(\PDO::FETCH_OBJ)) {
            if($pr_info=$configurator->configurator->get_pr_info($conf->pr_id,"pr_name")) {
                $pr_name=$pr_info->pr_name;
            }
            else $pr_name="";?>
           <tr>
               <td><a href="configurator/result/<?=$conf->conf_id?>">#<?=$conf->conf_id?> <span class="icon-link-ext"></span></a></td>
               <td><a href="configurator/page/<?=$conf->pr_id?>"><?=$pr_name?></a></td>
               <td><?=$conf->name?></td>
               <td><?=$conf->email?></td>
               <td><a href="tel:<?=$conf->phone?>"><?=$conf->phone?></a></td>
               <td><?=$conf->company_name?></td>
               <td><a href="configurator/configurations_pdf/<?=site_id?>/<?=$conf->conf_id?>/<?=$conf->conf_hash?>/<?=$conf->timestamp?>/conf_<?=$conf->conf_id?>.pdf">Скачать</a></td>
               <td><?if((int)$conf->bill_number){?><a href="<?=u_sroot.$configurator->uFunc->bill_number2file_path($conf->bill_number)?>">Скачать</a><?}?></td>
           </tr>
        <?}?>
    </table>
</div>
<?$this->page_content=ob_get_contents();
ob_end_clean();

include 'templates/template.php';