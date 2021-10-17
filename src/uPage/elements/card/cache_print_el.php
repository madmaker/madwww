<?php
try {
    /** @noinspection PhpUndefinedMethodInspection */
    $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
    card_background_color,
    card_img_url,
    card_img_size,
    card_img_position,
    card_text,
    card_min_height_lg,
    card_min_height_md,
    card_min_height_sm,
    card_min_height_xs,
    card_font_size,
    card_font_color
    FROM
    el_config_card
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

    $card_img_position="";
    $res->card_img_position=(int)$res->card_img_position;
    if($res->card_img_position==1) $card_img_position="background-position: left;";
    else if($res->card_img_position==2) $card_img_position="background-position: center;";
    else if($res->card_img_position==3) $card_img_position="background-position: right;";

    else if($res->card_img_position==4) $card_img_position="background-position: center;";
    else if($res->card_img_position==5) $card_img_position="background-position: top;";
    else if($res->card_img_position==6) $card_img_position="background-position: bottom;";

    else if($res->card_img_position==7) $card_img_position="background-position: left top;";
    else if($res->card_img_position==8) $card_img_position="background-position: center top;";
    else if($res->card_img_position==9) $card_img_position="background-position: right top;";

    else if($res->card_img_position==10) $card_img_position="background-position: left center;";
    else if($res->card_img_position==11) $card_img_position="background-position: center center;";
    else if($res->card_img_position==12) $card_img_position="background-position: right center;";

    else if($res->card_img_position==13) $card_img_position="background-position: left bottom;";
    else if($res->card_img_position==14) $card_img_position="background-position: center bottom;";
    else if($res->card_img_position==15) $card_img_position="background-position: right bottom;";

    $res->card_img_size=(int)$res->card_img_size;
    $res->card_min_height_xs=(int)$res->card_min_height_xs;
    $res->card_min_height_sm=(int)$res->card_min_height_sm;
    $res->card_min_height_md=(int)$res->card_min_height_md;
    $res->card_min_height_lg=(int)$res->card_min_height_lg;
    ?>
    <div>
        <style type="text/css">
            #uPage_card_<?=$cols_els_id?> {
            <?=strlen($res->card_img_url)?('background-image:url("'.$res->card_img_url.'");'):""?>
            background-size: <?=$res->card_img_size?'contain':"cover"?>;
            <?=$card_img_position?>
            }
            @media(max-width:767px) {
                #uPage_card_<?=$cols_els_id?> .card_content_container {
                    <?=$res->card_min_height_xs?('height:'.(int)($res->card_min_height_xs*0.9).'px'):""?>
                }
                #uPage_card_<?=$cols_els_id?> {
                    <?=$res->card_min_height_xs?('height:'.$res->card_min_height_xs.'px'):""?>
                }
            }
            @media(min-width:768px) {
                #uPage_card_<?=$cols_els_id?> .card_content_container {
                <?=$res->card_min_height_sm?('height:'.(int)($res->card_min_height_sm*0.9).'px'):""?>
                }
                #uPage_card_<?=$cols_els_id?> {
                <?=$res->card_min_height_sm?('height:'.$res->card_min_height_sm.'px'):""?>
                }
            }

            @media(min-width:992px) {
                #uPage_card_<?=$cols_els_id?> .card_content_container {
                <?=$res->card_min_height_md?('height:'.(int)($res->card_min_height_md*0.9).'px'):""?>
                }
                #uPage_card_<?=$cols_els_id?> {
                <?=$res->card_min_height_md?('height:'.$res->card_min_height_md.'px'):""?>
                }
            }

            @media(min-width:1200px) {
                #uPage_card_<?=$cols_els_id?> .card_content_container {
                <?=$res->card_min_height_lg?('height:'.(int)($res->card_min_height_lg*0.9).'px'):""?>
                }
                #uPage_card_<?=$cols_els_id?> {
                <?=$res->card_min_height_lg?('height:'.$res->card_min_height_lg.'px'):""?>
                }
            }
            #uPage_card_<?=$cols_els_id?> .card_content{
                <?=strlen($res->card_background_color)?('background-color:#'.$res->card_background_color.';'):""?>
                <?=strlen($res->card_font_size)?('font-size:'.$res->card_font_size.'em;'):""?>
            }
            #uPage_card_<?=$cols_els_id?> .card_content,
            #uPage_card_<?=$cols_els_id?> .card_content a.btn-outline.btn-default,
            #uPage_card_<?=$cols_els_id?> .card_content a,
            #uPage_card_<?=$cols_els_id?> .card_content a:visited,
            #uPage_card_<?=$cols_els_id?> .card_content a.btn-outline.btn-default:visited,
            #uPage_card_<?=$cols_els_id?> .card_content a:hover {
                <?=strlen($res->card_font_color)?('color:#'.$res->card_font_color.';'):""?>
            }

            #uPage_card_<?=$cols_els_id?> .btn-outline.btn-default {
            <?=strlen($res->card_font_color)?('border-color:#'.$res->card_font_color.';'):""?>
            <?=strlen($res->card_font_color)?('color:#'.$res->card_font_color.'!important;'):""?>
            }
            #uPage_card_<?=$cols_els_id?> .card_content a.btn-outline.btn-default:hover {
            <?=strlen($res->card_background_color)?('color:#'.$res->card_background_color.'!important;'):""?>
            <?=strlen($res->card_font_color)?('background-color:#'.$res->card_font_color.';'):""?>
            }
        </style>
        <div id="uPage_card_<?=$cols_els_id?>" class="uPage_card"><div class="card_content"><div class="card_wrapper"><div id="uPage_card_text_container_<?=$cols_els_id?>" class="card_content_container"><?=$res->card_text?></div></div></div></div>
    </div>
<?}
catch(PDOException $e) {$this->uFunc->error('/uPage/elements/card/cache_print_el/10'/*.$e->getMessage()*/);}