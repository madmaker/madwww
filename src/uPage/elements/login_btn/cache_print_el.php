<?php
try {
    /** @noinspection PhpUndefinedMethodInspection */
    $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
                                btn_primary,
                                btn_info,
                                btn_success,
                                btn_danger,
                                btn_warning,
                                btn_sm,
                                btn_xs,
                                btn_lg,
                                btn_text,
                                replace_with_logout,
                                logout_text
                                FROM
                                el_config_login_btn
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
    <button
        id="uPage_login_btn_<?=$cols_els_id?>"
        class="uPage_login_btn btn btn-default
                            <?=(int)$res->btn_primary? " btn-primary " : ""?>
                            <?=(int)$res->btn_info? " btn-info " : ""?>
                            <?=(int)$res->btn_success? " btn-success " : ""?>
                            <?=(int)$res->btn_warning? " btn-warning " : ""?>
                            <?=(int)$res->btn_danger? " btn-danger " : ""?>
                            <?=(int)$res->btn_sm? " btn-sm " : ""?>
                            <?=(int)$res->btn_xs? " btn-xs " : ""?>
                            <?=(int)$res->btn_lg? " btn-lg " : ""?>
                            <?=!(int)$res->replace_with_logout&&$this->uCore->access(2)? " hidden " : ""?>
"
        <?
        echo '<? 
                            if($this->uCore->access(2)) echo \'href="\'.u_sroot.\'uAuth/logout"\';
                            else echo \'onclick="uAuth_form.open();"\';
                            ?>';
        ?>
    >
        <?
        echo '<? 
                        if($this->uCore->access(2)) echo "'.addslashes($res->logout_text).'";
                        else echo "'.addslashes($res->btn_text).'";
                        ?>';
        ?>
    </button>
<?}
catch(PDOException $e) {$this->uFunc->error('uPage_elements_login_btn_cache_print_el 10'/*.$e->getMessage()*/);}