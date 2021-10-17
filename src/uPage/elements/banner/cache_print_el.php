<?php
try {
    /** @noinspection PhpUndefinedMethodInspection */
    $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
    background_stretch,
    background_repeat_x,
    background_repeat_y,
    background_color,
    background_img,
    background_position,
    min_height,
    font_color,
    font_size,
    text 
    FROM
    el_config_banner
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

    $backgroundRepeat="no-repeat";
    $res->background_repeat_x=(int)$res->background_repeat_x;
    $res->background_repeat_y=(int)$res->background_repeat_y;
    if($res->background_repeat_x&&$res->background_repeat_y) $backgroundRepeat="repeat";
    else if($res->background_repeat_x&&!$res->background_repeat_y) $backgroundRepeat="repeat-x";
    else if(!$res->background_repeat_x&&$res->background_repeat_y) $backgroundRepeat="repeat-y";

    $backgroundPosition="";
    $res->background_position=(int)$res->background_position;
    if($res->background_position==1) $backgroundPosition="background-position: left;";
    else if($res->background_position==2) $backgroundPosition="background-position: center;";
    else if($res->background_position==3) $backgroundPosition="background-position: right;";

    else if($res->background_position==4) $backgroundPosition="background-position: middle;";
    else if($res->background_position==5) $backgroundPosition="background-position: top;";
    else if($res->background_position==6) $backgroundPosition="background-position: bottom;";

    else if($res->background_position==7) $backgroundPosition="background-position: left top;";
    else if($res->background_position==8) $backgroundPosition="background-position: center top;";
    else if($res->background_position==9) $backgroundPosition="background-position: right top;";

    else if($res->background_position==10) $backgroundPosition="background-position: left middle;";
    else if($res->background_position==11) $backgroundPosition="background-position: center middle;";
    else if($res->background_position==12) $backgroundPosition="background-position: right middle;";

    else if($res->background_position==13) $backgroundPosition="background-position: left bottom;";
    else if($res->background_position==14) $backgroundPosition="background-position: center bottom;";
    else if($res->background_position==15) $backgroundPosition="background-position: right bottom;";

    $res->background_stretch=(int)$res->background_stretch;
    $res->min_height=(int)$res->min_height;
    $res->font_size=(float)$res->font_size;
    ?>
    <div>
        <style type="text/css">
            #uPage_row_<?=$row_id?> {
                <?=strlen($res->background_img)?('background-image:url("'.$res->background_img.'");'):''?>
                <?=strlen($res->background_color)?('background-color:#'.$res->background_color.';'):''?>
                <?=$res->background_stretch?'background-size: cover;':''?>
                background-repeat: <?=$backgroundRepeat?>;
                <?=$backgroundPosition?>
            }
            #uPage_banner_<?=$cols_els_id?> {
            display: table-cell;
            vertical-align: middle;
            <?=$res->min_height?'height: '.$res->min_height.'px;':''?>
            <?=strlen($res->font_color)?('color:#'.$res->font_color.';'):''?>
            <?=$res->font_size?('font-size:'.$res->font_size.'em;'):''?>
            }
        </style>
        <div id="uPage_banner_<?=$cols_els_id?>" class="uPage_banner"><div class="banner_content"><?=$res->text?></div></div>
    </div>
<?}
catch(PDOException $e) {$this->uFunc->error('/uPage/elements/banner/cache_print_el/10'/*.$e->getMessage()*/);}