<?php
namespace configurator;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once 'uDrive/classes/common.php';

class products {
    /**
     * @var string
     */
    public $currency;
    private $uDrive;
    private $uFunc;
    public $uSes;
    private $uCore;

    public function get_products($site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("SELECT 
            pr_id,
            pr_name,
            pr_text,
            pr_pos,
            uDrive_folder_id,
            pr_price
            FROM 
            products 
            WHERE 
            site_id=:site_id
            ORDER BY
            pr_pos ASC
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}
        return 0;
    }

    public function define_product_uDrive_folder_id($pr_id,$pr_title,$cur_folder_id=0,$site_id=site_id) {
        if(!(int)$cur_folder_id) {
            if(!isset($this->uDrive)) {
                require_once "uDrive/classes/common.php";
                $this->uDrive=new \uDrive\common($this->uCore);
            }
            $uDrive_folder_id = $this->uDrive->get_module_folder_id("configurator");
            $pr_title=trim($pr_title);
            $cur_folder_id=$this->uDrive->create_folder($pr_title,$uDrive_folder_id);

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("configurator")->prepare("UPDATE 
                products
                SET
                uDrive_folder_id=:folder_id
                WHERE
                pr_id=:pr_id AND
                site_id=:site_id");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':folder_id', $cur_folder_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':pr_id', $pr_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
        }
        return $cur_folder_id;
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uSes=new uSes($this->uCore);
        $this->uFunc=new uFunc($this->uCore);

        if($this->uSes->access(7)) {
            //Подсказки ИНН
            $this->uFunc->incCSS("/js/dadata_suggestions/suggestions.min.css");
            $this->uFunc->incJs("/js/dadata_suggestions/jquery.suggestions.js");

            $this->uFunc->incJs("configurator/js/products_admin.js");
            $this->uFunc->incCss("configurator/css/products.min.css");
            $this->uDrive=new \uDrive\common($this->uCore);
        }
        $this->currency=(int)$this->uFunc->getConf("currency","configurator");
    }
}
$configurator=new products($this);
ob_start();
if($configurator->uSes->access(7)) {
    include_once "configurator/dialogs/products.php";
}
?>
<div class="configurator products">
    <?$products_stm=$configurator->get_products();
    /** @noinspection PhpUndefinedMethodInspection */
    for($i=0; $product=$products_stm->fetch(PDO::FETCH_OBJ); $i++) {
        $uDrive_folder_id=$configurator->define_product_uDrive_folder_id($product->pr_id,$product->pr_name,$product->uDrive_folder_id);
        ?>
        <div class="product" id="pr_<?=$product->pr_id?>"
             data-udrive_folder_id="<?=$uDrive_folder_id?>"
             data-pr_pos="<?=$product->pr_pos?>"
        >
            <?if($configurator->uSes->access(7)) {?><div class="u235_eip">
                <button class="btn btn-danger" onclick="configurator.products_admin.delete_product_prompt(<?=$product->pr_id?>)"><span class="icon-cancel"></span> Удалить продукт</button>
                <button class="btn btn-default" onclick="configurator.products_admin.edit_pr_pos_init(<?=$product->pr_id?>)"><span class="icon-sort-alt-up"></span> Поменять местами</button></div><?}?>
            <div class="col-md-8">
                <h2 class="pr_name"><a id="pr_name_<?=$product->pr_id?>" href="configurator/page/<?=$product->pr_id?>"><?=$product->pr_name?></a></h2>
            </div>
            <div class="col-md-4">
                <div class="pr_price"><a href="configurator/page/<?=$product->pr_id?>">от <span id="pr_price_<?=$product->pr_id?>"><?=number_format ( $product->pr_price ,0,'.' , ' ' )?></span> <?php
                        if($configurator->currency===0) {?><span class="icon-rouble"></span><?}
                        elseif($configurator->currency===1) {?><span class="icon-euro"></span><?}
                        elseif($configurator->currency===2) {?>$<?}
                        ?></a></div>
            </div>
            <div class="col-md-12">
                <div class="pr_text"  id="pr_text_<?=$product->pr_id?>"><?=$product->pr_text?></div>
            </div>
        </div>
    <?}

    if($i) {
        if($configurator->uSes->access(7)){?>
        <div id="uDrive_my_drive_uploader_init"></div>
        <?include_once 'uDrive/inc/my_drive_manager.php';
        }
    }
    else {?>
    <div class="jumbotron">
        <h2>Ничего не найдено</h2>
        <?if($configurator->uSes->access(7)){?>
            <p>Вы можете создать продукт прямо сейчас.</p>
            <p><button class="btn btn-primary" onclick="configurator.products_admin.new_product_init()"><span class="icon-plus"></span> Создать продукт</button></p>
        <?}?>
        <p><a href="<?=u_sroot?>">Перейти на главную страницу</a></p>
    </div>
    <?}?>
</div>

<?$this->page_content=ob_get_contents();
ob_end_clean();

include 'templates/template.php';
