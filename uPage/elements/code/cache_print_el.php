<?php
try {
    /** @noinspection PhpUndefinedMethodInspection */
    $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
        code
        FROM
        el_config_code
        WHERE 
        cols_els_id=:cols_els_id AND 
        site_id=:site_id
        ");
    $site_id=site_id;
    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

    /** @noinspection PhpUndefinedMethodInspection */
    if($res=$stm->fetch(PDO::FETCH_OBJ)) {?>
    <div>
        <div id="uPage_code_<?=$cols_els_id?>" class="uPage_code"><?=$res->code?></div>
    </div>
    <?}
}
catch(PDOException $e) {$this->uFunc->error('uPage_elements_code_cache_print_el 10'/*.$e->getMessage()*/);}