<?php
try {
    /** @noinspection PhpUndefinedMethodInspection */
    $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
    height
    FROM
    el_config_spacer
    WHERE 
    cols_els_id=:cols_els_id AND 
    site_id=:site_id
    ");
    $site_id=site_id;
    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $element->cols_els_id,PDO::PARAM_INT);
    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

    /** @noinspection PhpUndefinedMethodInspection */
    $res=$stm->fetch(PDO::FETCH_OBJ);?>
    if (typeof uPage_setup_uPage=== "undefined") {uPage_setup_uPage={};}
    if (typeof uPage_setup_uPage.spacer2data=== "undefined") {uPage_setup_uPage.spacer2data=[]}

    uPage_setup_uPage.spacer2data[<?=$element->cols_els_id?>]=[];
    uPage_setup_uPage.spacer2data[<?=$element->cols_els_id?>]['height']=<?=$res->height?>;
<?}
catch(PDOException $e) {$this->uFunc->error('uPage_elements_spacer_admin_page_builder 10'/*.$e->getMessage()*/);}