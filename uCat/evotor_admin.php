<?php
ob_start();
$this->uFunc_new->incJs(u_sroot."uCat/js/evotor_admin.min.js");
?>
<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-center">
    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
        <P>Загрузить в кассу все товары из интернет-магазина. Более подробную информацию можно получить при заказе интернет-магазина.</P>
        <button class="btn btn-primary items-in-terminal">Загрузить в кассу</button>
    </div>
    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
        <P>Загрузить в интернет-магазин все товары из кассы. Более подробную информацию можно получить при заказе интернет-магазина.</P>
        <button class="btn btn-primary items-in-market">Загрузить в интернет-магазин</button>
    </div>
</div>
<?
$this->page_content=ob_get_contents();
ob_end_clean();
include "templates/u235/template.php";