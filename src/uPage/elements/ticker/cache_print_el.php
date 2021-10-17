<?php
try {
    /** @noinspection PhpUndefinedMethodInspection */
    $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
                                text
                                FROM
                                el_config_effects_ticker
                                WHERE 
                                cols_els_id=:cols_els_id AND 
                                site_id=:site_id
                                ");
    $site_id=site_id;
    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

    /** @noinspection PhpUndefinedMethodInspection */
    $res=$stm->fetch(PDO::FETCH_OBJ);
    $text_ar=explode("\n",$res->text);
    for($i=0;$i<count($text_ar);$i++) {
        $text_ar[$i]=htmlspecialchars($text_ar[$i]);
    }
    $typer_string='"'.implode('","',$text_ar).'"';?>
    <div id="uPage_ticker_<?=$cols_els_id?>" className="ticker" data-typer-targets='{"targets" : [<?=$typer_string?>]}'></div>
    <script type="text/javascript">
        function uPage_el_ticker_init(cols_els_id) {
            if(typeof $.prototype.typer!=="function") {
                u235_common.addScript('/js/typers/layervault/src/jquery.typer.min.js');

                setTimeout('uPage_el_ticker_init('+cols_els_id+')',500);
                return 0;
            }
            $("#uPage_ticker_"+cols_els_id).typer();
        }
        $(document).ready(function() {
            uPage_el_ticker_init(<?=$cols_els_id?>);
        });
    </script>
<?}
catch(PDOException $e) {$this->uFunc->error('uPage_elements_ticker_cpe 10'/*.$e->getMessage()*/);}