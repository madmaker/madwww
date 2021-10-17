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
    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

    /** @noinspection PhpUndefinedMethodInspection */
    $res=$stm->fetch(PDO::FETCH_OBJ);?>
    <div class="uPage_spacer" style="height:<?=$res->height?>em">&nbsp;</div>
<?}
catch(PDOException $e) {$this->uFunc->error('uPage_elements_spacer_cache_print_el 10'/*.$e->getMessage()*/);}