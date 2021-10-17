<?php
namespace uPage\admin;
use PDO;
use PDOException;
use uPage\common;
use uString;

class tabs{
    private $last_sample_img_index;
    private $uPage;
    private $uFunc;
    private $uCore;

    public function text($str) {
        return $this->uCore->text(array('uPage','tabs'),$str);
    }

    public function clear_tabs_cache($cols_els_id,$site_id=site_id) {
        $this->uFunc->rmdir("uPage/cache/tabs/".$site_id."/".$cols_els_id);
    }
    public function get_el_settings($cols_els_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            autoplay_speed
            FROM
            el_config_tabs
            WHERE
            cols_els_id=:cols_els_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('tabs_common 10'/*.$e->getMessage()*/,1);}
        return 0;
    }

    private function get_new_tab_id() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            tab_id 
            FROM 
            tabs 
            ORDER BY 
            tab_id DESC
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr->tab_id+1;
            else return 1;
        }
        catch(PDOException $e) {$this->uFunc->error('tabs_common 20'/*.$e->getMessage()*/,1);}
        return 1;
    }
    public function copy_el($cols_els_id,$new_col_id,$el,$source_site_id=site_id,$dest_site_id=0) {
        $cur_cols_els_id=$el->cols_els_id;
        $q_tabs=$this->cols_els_id2q_tabs($cur_cols_els_id,$source_site_id);
        $conf=$this->get_el_settings($cur_cols_els_id,$source_site_id);
        //attach art to col
        $this->uPage->create_el($cols_els_id,$new_col_id,'tabs',$el->el_pos,$el->el_style,0,$dest_site_id);

        /** @noinspection PhpUndefinedMethodInspection */
        while($tab=$q_tabs->fetch(PDO::FETCH_OBJ)) {
            $tab_id=$this->get_new_tab_id();
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm = $this->uFunc->pdo("uPage")->prepare("INSERT INTO
                tabs (
                cols_els_id, 
                site_id, 
                tab_id, 
                tab_header, 
                tab_content, 
                tab_pos,
                tab_img_timestamp,
                img_ext
                ) VALUES (
                :cols_els_id, 
                :site_id, 
                :tab_id, 
                :tab_header, 
                :tab_content, 
                :tab_pos,
                :tab_img_timestamp,
                :img_ext     
                )
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tab_id', $tab_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tab_header', $tab->tab_header,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tab_content', $tab->tab_content,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tab_pos', $tab->tab_pos,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tab_img_timestamp', $tab->tab_img_timestamp,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':img_ext', $tab->img_ext,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            } catch (PDOException $e) {$this->uFunc->error('tabs_common 30'/*.$e->getMessage()*/,1);}
        }

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO  
            el_config_tabs (
            cols_els_id, 
            site_id, 
            autoplay_speed
            ) VALUES (
            :cols_els_id, 
            :site_id, 
            :autoplay_speed     
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':autoplay_speed', $conf->autoplay_speed,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('tabs_common 40'/*.$e->getMessage()*/,1);}

        return $cols_els_id;
    }

    private function cols_els_id2q_tabs($cols_els_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            tab_id,
            tab_header,
            tab_content,
            tab_pos,
            tab_img_timestamp,
            img_ext
            FROM 
            tabs 
            WHERE
            cols_els_id=:cols_els_id AND
            site_id=:site_id
            ORDER BY tab_pos ASC
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('tabs_common 50'/*.$e->getMessage()*/,1);}
        return 0;
    }
    private function tab_id2data($tab_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            cols_els_id,
            site_id,
            tab_id,
            tab_header,
            tab_content,
            tab_pos,
            tab_img_timestamp,
            img_ext 
            FROM 
            tabs 
            WHERE
            tab_id=:tab_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tab_id', $tab_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('tabs_common 60'/*.$e->getMessage()*/,1);}

        return 0;
    }


    private function create_sample_tab($cols_els_id,$site_id=site_id) {
        $dir='uPage/elements/tabs/site_images/'.$site_id.'/'.$cols_els_id;
        if(!file_exists($dir)) mkdir($dir,0755,true);

        $tab_id=$this->get_new_tab_id();
        $tab_header='<b>Lor</b>em ipsum '.$tab_id;
        $tab_content='<h3>Lorem ipsum '.$tab_id.'</h3><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse a lorem id leo dictum vulputate. Nullam non urna condimentum, aliquet lorem quis, ullamcorper mauris. Aenean imperdiet tristique leo, eu ornare justo cursus at. In hac habitasse platea dictumst. Vivamus aliquam ut odio egestas imperdiet. Nullam at augue convallis, vehicula eros in, tristique eros. Aliquam id suscipit ex. Duis ut mi leo. Curabitur non scelerisque est. Aliquam a tincidunt erat. Cras sit amet nunc sit amet elit rhoncus facilisis.</p><p>Vivamus sed ornare purus, in fringilla quam. Aenean posuere ipsum eu neque sollicitudin, tincidunt facilisis velit vulputate. Vivamus vel metus ac justo vulputate rhoncus quis et purus. Sed sagittis risus rutrum ligula pharetra viverra. Vivamus vulputate a leo nec tristique. Nam rhoncus, lectus a varius lobortis, odio massa tristique dui, non dictum metus neque id elit. Cras volutpat mauris id tellus fermentum, quis tincidunt metus consequat. Duis velit quam, tempus ac aliquam id, vestibulum non nisi.</p>';

        $ext="jpg";
        $tab_img_timestamp=time();
        copy("uPage/elements/tabs/img/sample".(1-$this->last_sample_img_index).".jpg", $dir . "/" . $tab_id . "." . $ext);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO 
                tabs (
                cols_els_id, 
                site_id, 
                tab_id, 
                tab_header, 
                tab_content, 
                tab_pos,
                tab_img_timestamp,
                img_ext
                ) VALUES (
                :cols_els_id, 
                :site_id, 
                :tab_id, 
                :tab_header, 
                :tab_content, 
                :tab_id,
                :tab_img_timestamp,
                :img_ext
                )
                ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tab_id', $tab_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tab_header', $tab_header,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tab_content', $tab_content,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tab_img_timestamp', $tab_img_timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':img_ext', $ext,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('tabs_common 70'.$e->getMessage(),1);}
        return array(
                "tab_id"=>$tab_id,
                "tab_content"=>$tab_content,
                "tab_header"=>$tab_header,
                "tab_img_timestamp"=>$tab_img_timestamp
        );
    }
    public function attach_el2col($col_id,$site_id=site_id) {
        $el_pos=$this->uPage->define_new_el_pos($col_id);

        //get new cols_els_id
        $cols_els_id=$this->uPage->get_new_cols_els_id();

        //attach art to col
        $res=$this->uPage->add_el2db($cols_els_id,$el_pos,'tabs',$col_id,0);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO 
            el_config_tabs (
            cols_els_id, 
            site_id
            ) VALUES (
            :cols_els_id, 
            :site_id     
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */
        }
        catch(PDOException $e) {$this->uFunc->error('tabs_common 80'/*.$e->getMessage()*/,1);}

        for($i=0;$i<2;$i++) {
            $this->create_sample_tab($cols_els_id,$site_id);
        }

        echo '{'.$res[0].'}';
        exit;
    }

    public function cache_tabs($cols_els_id,$site_id=site_id) {
        $dir='uPage/cache/tabs/'.$site_id.'/'.$cols_els_id;
        $img_dir='uPage/elements/tabs/site_images/'.$site_id.'/'.$cols_els_id;

        //До этой строчки никаких запросов в БД!!!!
        if(!file_exists($dir.'/tabs.html')||!file_exists($dir.'/tabs.js')) {
            if(!file_exists($dir)) mkdir($dir,0755,true);

            $conf=$this->get_el_settings($cols_els_id,$site_id);
            if(!$q_tabs=$this->cols_els_id2q_tabs($cols_els_id,$site_id)) $this->uFunc->error("tabs_common 90",1);

            $file = fopen($dir.'/tabs.html', 'w');
            /** @noinspection PhpUndefinedMethodInspection */
            $tabs_ar=$q_tabs->fetchAll(PDO::FETCH_OBJ);
            $tabs_count=count($tabs_ar);
            if($tabs_count===1) $col_width=12;
            elseif($tabs_count===2) $col_width=6;
            elseif($tabs_count===3) $col_width=4;
            elseif($tabs_count===4) $col_width=3;
            elseif($tabs_count===5) $col_width=2;
            elseif($tabs_count===6) $col_width=2;
            else $col_width=(int)($tabs_count/4);

            ob_start();?>
            <div id="uPage_tabs_<?=$cols_els_id?>" class="uPage_tabs_container">
                <div class="uPage_tabs_headers row" id="uPage_tabs_headers_<?=$cols_els_id?>">
                <?$active=0;
                for($i=0;$i<$tabs_count;$i++) {
                    $tab=$tabs_ar[$i];?>
                    <div id="uPage_tabs_tab_header_<?=$tab->tab_id?>" href="#uPage_tabs_tab_<?=$tab->tab_id?>" class="uPage_tab_header uPage_tab_headers_<?=$cols_els_id?> col-md-<?=$col_width?>  <?if(!$active) {$active=$tab->tab_id; echo "active";}?>" onclick="$('#uPage_tabs_tab_header_<?=$tab->tab_id?>').tab('show');$('#uPage_tabs_headers_<?=$cols_els_id?> .uPage_tab_header').removeClass('active');$(this).addClass('active')" aria-controls="uPage_tabs_tab_<?=$tab->tab_id?>'" role="tab" data-toggle="tab">
                        <?=$tab->tab_header?>
                    </div>
                <?}?>
                </div>
                <div id="uPage_tabs_<?=$cols_els_id?>_content" class="tab-content">
                    <?$active=0;
                    for($i=0;$i<$tabs_count;$i++) {
                        $tab=$tabs_ar[$i];
                        $tab->tab_img_timestamp=(int)$tab->tab_img_timestamp;?>
                        <div role="tabpanel" class="tab-pane fade <?if(!$active) {$active=$tab->tab_id; echo "in active";}?>"  id="uPage_tabs_tab_<?=$tab->tab_id?>">
                            <div class="container-fluid">
                                <?if($tab->tab_img_timestamp) {?>
                                <div class="col-md-4">
                                    <img alt="" src="<?=$img_dir?>/<?=$tab->tab_id?>.<?=$tab->img_ext?>" class="img-responsive uPage_tab_image">
                                </div>
                                <?}?>
                                <div class="col-md-<?=$tab->tab_img_timestamp?8:12?>">
                                    <?=$tab->tab_content?>
                                </div>
                            </div>
                        </div>
                    <?}?>
                </div>
            </div>
            <?
            fwrite($file, ob_get_contents());
            fclose($file);
            ob_end_clean();


            $file = fopen($dir.'/tabs.js', 'w');
            ob_start();

            if($conf->autoplay_speed=(int)$conf->autoplay_speed) {
            ?>
            <!--suppress ES6ConvertVarToLetConst -->
                <script type="text/javascript">
                var uPage_tabs_<?=$cols_els_id?>_active_tab_id = setInterval(function() {
                    let tabs = $('#uPage_tabs_headers_<?=$cols_els_id?>>div');
                    let active = tabs.filter('.active');
                    let next = active.next('div');
                    let first=tabs.first('div');

                    $(active).removeClass("active");
                    if(next.length) $(next).addClass("active").tab('show');
                    else $(first).addClass("active").tab('show');

                    if($("#uPage_tabs_editor_dg").hasClass("in")) {
                        clearInterval(uPage_tabs_<?=$cols_els_id?>_active_tab_id);
                        return 0;
                    }
                }, <?=$conf->autoplay_speed*1000?>);

                </script>
            <?}
                fwrite($file, ob_get_contents());
            fclose($file);
            ob_end_clean();
        }
    }

    public function load_el_content($cols_els_id,$return=0,$site_id=site_id) {
        $this->cache_tabs($cols_els_id,$site_id);
        $dir='uPage/cache/tabs/'.$site_id.'/'.$cols_els_id;

        $result_ar=(object)array(
            "status"=>"done",
            "cols_els_id"=>$cols_els_id,

            "html"=>file_get_contents($dir."/tabs.html"),
            "js"=>file_get_contents($dir."/tabs.js")
        );

        if($return) return $result_ar;
        else echo json_encode($result_ar);

        return 0;
    }

    public function load_el_editor($cols_els_id,$site_id=site_id) {
        $conf=$this->get_el_settings($cols_els_id,$site_id);
        $conf->autoplay_speed=(int)$conf->autoplay_speed;
        ?>
        <div class="container-fluid">
        <script type="text/javascript">
        if(typeof uPage_setup_uPage.tabs==="undefined") {
            uPage_setup_uPage.tabs={};

            uPage_setup_uPage.tabs.tab_id=[];
            uPage_setup_uPage.tabs.tab_header=[];
            uPage_setup_uPage.tabs.tab_content=[];
            uPage_setup_uPage.tabs.tab_pos=[];
            uPage_setup_uPage.tabs.tab_img_timestamp=[];
            uPage_setup_uPage.tabs.img_ext=[];
            uPage_setup_uPage.tabs.tab_show=[];
            uPage_setup_uPage.tabs.tab_id2index=[];
        }

        uPage_setup_uPage.tabs.autoplay_speed=<?=/** @noinspection PhpUndefinedFieldInspection */$conf->autoplay_speed?>;

        <?
        $q_tabs=$this->cols_els_id2q_tabs($cols_els_id,$site_id);?>
        <?
        /** @noinspection PhpUndefinedMethodInspection */
        for($i=0;$tab=$q_tabs->fetch(PDO::FETCH_OBJ);$i++) {?>
        uPage_setup_uPage.tabs.tab_id[<?=$i?>]=<?=$tab->tab_id?>;
        uPage_setup_uPage.tabs.tab_header[<?=$i?>]=decodeURIComponent("<?=rawurlencode($tab->tab_header)?>");
        uPage_setup_uPage.tabs.tab_content[<?=$i?>]=decodeURIComponent("<?=rawurlencode($tab->tab_content)?>");
        uPage_setup_uPage.tabs.tab_pos[<?=$i?>]=<?=$tab->tab_pos?>;
        uPage_setup_uPage.tabs.tab_img_timestamp[<?=$i?>]=<?=$tab->tab_img_timestamp?>;
        uPage_setup_uPage.tabs.img_ext[<?=$i?>]="<?=$tab->img_ext?>";
        uPage_setup_uPage.tabs.tab_show[<?=$i?>]=1;
        uPage_setup_uPage.tabs.tab_id2index[<?=$tab->tab_id?>]=<?=$i?>;
        <?}?>
        </script>

        <div id="uPage_setup_uPage_tabs"></div>

        <button type="button" class="btn btn-success" onclick="uPage_setup_uPage.tabs.add_tab();"><span class="glyphicon glyphicon-plus"></span> <?=$this->text("Add new tab - btn label")?></button>
        <h4><?=$this->text("Tab settings - dg section header")?></h4>
        <div class="form-group col-md-4">
            <label for="uPage_tabs_settings_autoplay_speed"><?=$this->text("Tab autoslide speed - input label")?></label>
            <select id="uPage_tabs_settings_autoplay_speed" class="form-control">
                <option value="0" <?=$conf->autoplay_speed===0?"selected":""?>><?=$this->text("No auotslide - selectbox option")?></option>
                <option value="1" <?=$conf->autoplay_speed===1?"selected":""?>><?=$this->text("Tab autoslide speed value - 1 second")?></option>
                <option value="2" <?=$conf->autoplay_speed===2?"selected":""?>><?=$this->text("Tab autoslide speed value - 2 second")?></option>
                <option value="3" <?=$conf->autoplay_speed===3?"selected":""?>><?=$this->text("Tab autoslide speed value - 3 second")?></option>
                <option value="4" <?=$conf->autoplay_speed===4?"selected":""?>><?=$this->text("Tab autoslide speed value - 5 second")?></option>
                <option value="5" <?=$conf->autoplay_speed===5?"selected":""?>><?=$this->text("Tab autoslide speed value - 10 second")?></option>
                <option value="6" <?=$conf->autoplay_speed===6?"selected":""?>><?=$this->text("Tab autoslide speed value - 20 second")?></option>
                <option value="7" <?=$conf->autoplay_speed===6?"selected":""?>><?=$this->text("Tab autoslide speed value - 30 second")?></option>
                <option value="8" <?=$conf->autoplay_speed===7?"selected":""?>><?=$this->text("NTab autoslide speed value - 1 minute")?></option>
                <option value="9" <?=$conf->autoplay_speed===8?"selected":""?>><?=$this->text("NTab autoslide speed value - 3 minute")?></option>
            </select>
        </div>
        </div>
    <?}


    private function move_tabs($cols_els_id,$dir,$site_id=site_id) {
        if(!isset($_REQUEST["tab_id"])) $this->uFunc->error("tabs_common 100",1);
        $tab_id=(int)$_REQUEST["tab_id"];

        //get current tab's pos
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            tab_pos,
            tab_id
            FROM
            tabs
            WHERE
            tab_id=:tab_id AND 
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tab_id', $tab_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('tabs_common 105'/*.$e->getMessage()*/,1);}
        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$tab=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error("tabs_common 110",1);

        $tab_id=$tab->tab_id;

        if($dir=='up') {
            $all_tabs_cur_pos=$tab->tab_pos-1;
            $all_tabs="tab_pos+1";
            $this_tab=$tab->tab_pos-1;
        }
        else {
            $all_tabs_cur_pos=$tab->tab_pos+1;
            $all_tabs="tab_pos-1";
            $this_tab=$tab->tab_pos+1;
        }

        //move upper tabs down/up
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
            tabs
            SET
            tab_pos=".$all_tabs."
            WHERE
            tab_pos=:tab_pos AND
            tab_id=:tab_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tab_pos', $all_tabs_cur_pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tab_id', $tab_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('tabs_common 120'/*.$e->getMessage()*/,1);}

        //move current tab up/down
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
            tabs
            SET
            tab_pos=:tab_pos
            WHERE
            tab_id=:tab_id AND
            tab_id=:tab_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tab_pos', $this_tab,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tab_id', $tab_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tab_id', $tab_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('tabs_common 130'/*.$e->getMessage()*/,1);}

        //clear cache
        $this->clear_tabs_cache($cols_els_id);
    }
    private function get_tabs($cols_els_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            tab_id,
            tab_pos
            FROM
            tabs
            WHERE
            cols_els_id=:cols_els_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('tabs_common 140'/*.$e->getMessage()*/,1);}
        return 0;
    }
    
    private function save_tab_content($cols_els_id,$tab_content,$site_id=site_id) {
        if(!isset($_REQUEST["tab_id"])) $this->uFunc->error("tabs_common 145",1);
        $tab_id=(int)$_REQUEST["tab_id"];

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE 
            tabs
            SET
            tab_content=:tab_content
            WHERE
            site_id=:site_id AND
            tab_id=:tab_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tab_content', $tab_content,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tab_id', $tab_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('tabs_common 150'/*.$e->getMessage()*/,1);}

        //clear cache
        $this->clear_tabs_cache($cols_els_id);

        echo json_encode(array(
            "status"=>"done",
            "tab_id"=>$tab_id,
            "tab_content"=>$tab_content
        ));
        exit;
    }
    private function save_tab_header($cols_els_id,$tab_header,$site_id=site_id) {
        if(!isset($_REQUEST["tab_id"])) $this->uFunc->error("tabs_common 155",1);
        $tab_id=(int)$_REQUEST["tab_id"];

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE 
            tabs
            SET
            tab_header=:tab_header
            WHERE
            site_id=:site_id AND
            tab_id=:tab_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tab_header', $tab_header,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tab_id', $tab_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('tabs_common 160'/*.$e->getMessage()*/,1);}

        //clear cache
        $this->clear_tabs_cache($cols_els_id);

        echo json_encode(array(
            "status"=>"done",
            "tab_id"=>$tab_id,
            "tab_header"=>$tab_header
        ));
        exit;
    }
    private function save_tabs_settings($cols_els_id,$autoplay_speed,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE 
            el_config_tabs
            SET
            autoplay_speed=:autoplay_speed
            WHERE
            site_id=:site_id AND
            cols_els_id=:cols_els_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':autoplay_speed', $autoplay_speed,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('tabs_common 170'/*.$e->getMessage()*/,1);}

        //clear cache
        $this->clear_tabs_cache($cols_els_id);

        echo json_encode(array(
            "status"=>"done",
            "cols_els_id"=>$cols_els_id,
            "autoplay_speed"=>$autoplay_speed
        ));
        exit;
    }
    private function delete_tab_image($cols_els_id,$tab_id,$site_id=site_id) {
        if(!$tab_data=$this->tab_id2data($tab_id,$site_id)) $this->uFunc->error("tabs_common 180",1);
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE 
            tabs
            SET
            tab_img_timestamp=0
            WHERE
            tab_id=:tab_id AND
            cols_els_id=:cols_els_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tab_id', $tab_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('tabs_common 190'/*.$e->getMessage()*/,1);}

        $dir='uPage/elements/tabs/site_images/'.$site_id.'/'.$cols_els_id;
        $file=$dir.'/'.$tab_id.'.'.$tab_data->img_ext;
        if(file_exists($file)) {
            unlink($file);
        }

        //clear cache
        $this->clear_tabs_cache($cols_els_id);

        echo json_encode(array(
            "status"=>"done",
            "tab_id"=>$tab_id
        ));
        exit;
    }

    //IMAGE UPLOADER
    private function sendHeaders() {
        // HTTP headers for no cache etc
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
    }
    private function uploader($cols_els_id,$site_id=site_id) {
        if(!isset($_REQUEST["tab_id"])) $this->uFunc->error("tabs_common 195",1);
        $tab_id=(int)$_REQUEST["tab_id"];

        $folder='uPage/elements/tabs/site_images/'.$site_id.'/'.$cols_els_id;
        $targetDir = $folder.'/tmp'.$tab_id;
        
        $maxFileAge = 60 * 60; // Temp file age in seconds

        // 5 minutes execution time
        @set_time_limit(5 * 60);

        // Get parameters
        $chunk = isset($_REQUEST["chunk"]) ? $_REQUEST["chunk"] : 0;
        $chunks = isset($_REQUEST["chunks"]) ? $_REQUEST["chunks"] : 0;
        $filename = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';

        // Clean the fileName for security reasons
        $filename =uString::text2filename(uString::rus2eng($filename),true);

        $dot = strrpos($filename, '.');
        $img_ext = substr($filename, $dot+1);

        // Create target dir
        if (!file_exists($targetDir)) mkdir($targetDir ,0755,true);

        // Remove old temp files
        if (is_dir($targetDir ) && ($dir = opendir($targetDir ))) {
            while (($file = readdir($dir)) !== false) {
                $filePath = $targetDir .'/'.$file;
                // Remove temp files if they are older than the max age
                if (preg_match('/\\.tmp$/', $file) && (filemtime($filePath) < time() - $maxFileAge)) @unlink($filePath);
            }
            closedir($dir);
        }
        else $this->finish("error", "uploader", "Failed to open temp directory.",$cols_els_id,$tab_id);

        // Look for the content type header
        if (isset($_SERVER["HTTP_CONTENT_TYPE"])) $contentType = $_SERVER["HTTP_CONTENT_TYPE"];

        if (isset($_SERVER["CONTENT_TYPE"])) $contentType = $_SERVER["CONTENT_TYPE"];

        // Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
        /** @noinspection PhpUndefinedVariableInspection */
        if (strpos($contentType, "multipart") !== false) {
            if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
                // Open temp file
                $out = fopen($targetDir  . '/' . $filename, $chunk == 0 ? "wb" : "ab");
                if ($out) {
                    // Read binary input stream and append it to temp file
                    $in = fopen($_FILES['file']['tmp_name'], "rb");
                    if ($in) {
                        while ($buff = fread($in, 4096)) fwrite($out, $buff);
                    }
                    else $this->finish("error", "uploader", "Failed to open input stream.",$cols_els_id,$tab_id);

                    fclose($in);
                    fclose($out);
                    @unlink($_FILES['file']['tmp_name']);
                }
                else $this->finish("error", "uploader", "Failed to open output stream.",$cols_els_id,$tab_id);
            }
            else $this->finish("error", "uploader", "Failed to move uploaded file.",$cols_els_id,$tab_id);
        }
        else {
            // Open temp file
            $out = fopen($targetDir  . '/' . $filename, $chunk == 0 ? "wb" : "ab");
            if ($out) {
                // Read binary input stream and append it to temp file
                $in = fopen("php://input", "rb");
                if ($in) {
                    while ($buff = fread($in, 4096)) fwrite($out, $buff);
                }
                else $this->finish("error", "uploader", "Failed to open input stream.",$cols_els_id,$tab_id);
                fclose($in);
                fclose($out);
            }
            else $this->finish("error", "uploader", "Failed to open output stream",$cols_els_id,$tab_id);
        }

        if (($chunk+1 == $chunks)||$chunks==0) {
            $this->afterFileUploaded($cols_els_id,$img_ext,$tab_id,$targetDir,$filename,$folder);
            return $img_ext;
        }
        else $this->finish('continue', '','Part '.++$chunk.' of '.$chunks,$cols_els_id,$tab_id,$img_ext);
        return $img_ext;
    }
    private function afterFileUploaded($cols_els_id,$img_ext,$tab_id,$targetDir,$filename,$folder) {
        $source_filename=$this->after_uploaded_db_work($img_ext,$tab_id,$targetDir,$filename);
        $this->after_uploaded_fs_work($tab_id,$folder,$source_filename,$targetDir,$img_ext);

        //clear cache
        $this->clear_tabs_cache($cols_els_id);
    }
    private function after_uploaded_db_work($img_ext,$tab_id,$targetDir,$filename) {
        if(!isset($this->uFunc)) $this->uFunc=new \processors\uFunc($this->uCore);

        $mime_type=$this->uFunc->ext2mime(strtolower($img_ext));
        if(!$mime_type) $mime_type='application/octet-stream';

        if(!strpos('_'.$mime_type,'image')) $this->finish("error", "check", "wrong file format");

        //update tab_img_timestamp
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
            tabs
            SET
            tab_img_timestamp=:tab_img_timestamp
            WHERE
            tab_id=:tab_id AND
            site_id=:site_id
            ");
            $tab_img_timestamp=time();
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tab_img_timestamp', $tab_img_timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tab_id', $tab_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('tabs_common 200'/*.$e->getMessage()*/,1);}

        return $targetDir .'/'.$filename;
    }
    private function after_uploaded_fs_work($tab_id,$folder,$source_filename,$targetDir,$img_ext) {
        if(!isset($this->uFunc)) $this->uFunc=new \processors\uFunc($this->uCore);

        $save_file_name=$tab_id.'.'.$img_ext;
        // Create dir
        if(!$this->uFunc->create_empty_index($folder)) $this->uFunc->error("tabs_common 210",1);

        //copy file
        copy ($source_filename,$folder.'/'.$save_file_name);
        //Delete temp directory
        $this->uFunc->rmdir($targetDir);
    }
    private function finish($status,$type='',$msg='',$cols_els_id=0,$tab_id=0,$img_ext="") {
        die(json_encode(array(
            "status" => $status,
            "type" => $type,
            "message" => $msg,
            "tab_id"=>$tab_id,
            "cols_els_id"=>$cols_els_id,
            "img_ext"=>$img_ext
        )));
    }
    //---IMAGE UPLOADER

    private function delete_tab($cols_els_id,$site_id=site_id) {
        if(!isset($_REQUEST["tab_id"])) $this->uFunc->error("tabs_common 145",1);
        $tab_id=(int)$_REQUEST["tab_id"];

        if(!$tab_data=$this->tab_id2data($tab_id,$site_id)) $this->uFunc->error("tabs_common 220".$tab_id,1);
        $dir='uPage/elements/tabs/site_images/'.$site_id.'/'.$cols_els_id;
        $file=$dir.'/'.$tab_id.'.'.$tab_data->img_ext;
        if(file_exists($file)) unlink($file);


        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("DELETE FROM 
            tabs
            WHERE
            tab_id=:tab_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tab_id', $tab_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('tabs_common 230'/*.$e->getMessage()*/);}

        $this->clear_tabs_cache($cols_els_id,$site_id);

        echo json_encode(array("status"=>"done"));
    }

    public function save_el_conf($cols_els_id,$site_id=site_id) {
        if(isset($_REQUEST["tab_content"])) {
            $this->save_tab_content($cols_els_id,$_REQUEST["tab_content"],$site_id);
        }
        else if(isset($_REQUEST["tab_header"])) {
            $this->save_tab_header($cols_els_id,$_REQUEST["tab_header"],$site_id);
        }
        else if(isset($_REQUEST["dir"])) {
            $this->move_tabs($cols_els_id,$_REQUEST["dir"],$site_id);
            $q_tabs=$this->get_tabs($cols_els_id,$site_id);

            $tabs_ar=[];
            /** @noinspection PhpUndefinedMethodInspection */
            while($tab=$q_tabs->fetch(PDO::FETCH_OBJ)) {
                $tabs_ar['tab_'.$tab->tab_id.'_pos']=$tab->tab_pos;
            }
            $tabs_ar['status']='done';

            echo json_encode($tabs_ar);
            exit;
        }
        else if(isset($_REQUEST["autoplay_speed"])) {
            $this->save_tabs_settings($cols_els_id,$_REQUEST["autoplay_speed"],$site_id);
        }
        else if(isset($_REQUEST["delete_tab_image"])) {
            $this->delete_tab_image($cols_els_id,$site_id);
        }
        else if(isset($_REQUEST["chunk"])) {
            $this->sendHeaders();
            $img_ext=$this->uploader($cols_els_id,$site_id);
            $this->finish('done', '', '', $cols_els_id,(int)$_REQUEST["tab_id"],$img_ext);
            exit;
        }
        else if(isset($_REQUEST["new_tab"])) {
            $tab_info=$this->create_sample_tab($cols_els_id,$site_id);
            echo json_encode(array(
                    "status"=>"done",
                "tab_id"=>$tab_info["tab_id"],
                "tab_header"=>$tab_info["tab_header"],
                "tab_content"=>$tab_info["tab_content"],
                "tab_img_timestamp"=>$tab_info["tab_img_timestamp"]
            ));
            $this->clear_tabs_cache($cols_els_id,$site_id);
            exit;
        }
        else if(isset($_REQUEST["delete_tab"])) {
            $this->delete_tab($cols_els_id,$site_id);
            exit;
        }
    }

    function __construct (&$uPage) {
        $this->uPage=&$uPage;
        $this->uCore=&$this->uPage->uCore;
        if(!isset($this->uPage)) $this->uPage=new common($this->uCore);
        $this->uFunc=&$this->uPage->uFunc;

        $this->last_sample_img_index=0;
    }
}