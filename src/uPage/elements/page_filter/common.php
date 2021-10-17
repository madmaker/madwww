<?php
namespace uPage\admin;
use PDO;
use PDOException;
use uPage\common;

class page_filter{
    private $uPage;
    private $uFunc;
    private $uCore;

    public function text($str) {
        return $this->uCore->text(array('uPage','page_filter'),$str);
    }

    public function clear_page_filter_cache($cols_els_id,$site_id=site_id) {
        $this->uFunc->rmdir("uPage/cache/page_filter/".$site_id."/".$cols_els_id);
    }

    private function get_new_option_id() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            option_id 
            FROM 
            page_filter_options
            ORDER BY 
            option_id DESC
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr->option_id+1;
            else return 1;
        }
        catch(PDOException $e) {$this->uFunc->error('page filter common 10'/*.$e->getMessage()*/,1);}
        return 1;
    }
    private function get_new_option_value_id() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            value_id 
            FROM 
            page_filter_option_values
            ORDER BY 
            value_id DESC
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr->value_id+1;
            else return 1;
        }
        catch(PDOException $e) {$this->uFunc->error('page filter common 20'/*.$e->getMessage()*/,1);}
        return 1;
    }
    private function get_new_variant_id() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            var_id 
            FROM 
            page_filter_variants
            ORDER BY 
            var_id DESC
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr->var_id+1;
            else return 1;
        }
        catch(PDOException $e) {$this->uFunc->error('page filter common 30'/*.$e->getMessage()*/,1);}
        return 1;
    }

    private function cols_els_id2options($cols_els_id,$q_select="option_id",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            $q_select
            FROM 
            page_filter_options 
            WHERE
            cols_els_id=:cols_els_id AND
            site_id=:site_id
            ORDER BY 
            option_pos ASC
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('page filter common 40'/*.$e->getMessage()*/,1);}
        return 0;
    }
    public function copy_el($cols_els_id,$new_col_id,$el,$source_site_id=site_id,$dest_site_id=0) {
        $cur_cols_els_id=$el->cols_els_id;
        $this->uPage->create_el($cols_els_id,$new_col_id,'page_filter',$el->el_pos,$el->el_style,0,$dest_site_id);
        $this->clear_page_filter_cache($cols_els_id,$dest_site_id);

        $source_value_id2dest_value_id=[];
        //copy options and values
        $q_options=$this->cols_els_id2options($cur_cols_els_id,"*",$source_site_id);
        /** @noinspection PhpUndefinedMethodInspection */
        while($option=$q_options->fetch(PDO::FETCH_OBJ)) {
            $option_id=$this->get_new_option_id();
            $option->cols_els_id=$cols_els_id;
            $option->site_id=$dest_site_id;
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm = $this->uFunc->pdo("uPage")->prepare("INSERT INTO
                page_filter_options (
                cols_els_id, 
                site_id, 
                option_id, 
                option_name,
                option_pos
                ) VALUES (
                :cols_els_id, 
                :site_id, 
                :option_id, 
                :option_name, 
                :option_pos 
                )
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_id', $option_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_name', $option->option_name,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_pos', $option->option_pos,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            } catch (PDOException $e) {$this->uFunc->error('page filter common 45'/*.$e->getMessage()*/,1);}

            //copy option values
            $q_values=$this->option_id2values($option->option_id,"*",$source_site_id);
            /** @noinspection PhpUndefinedMethodInspection */
            while($value=$q_values->fetch(PDO::FETCH_OBJ)) {
                $value_id=$this->get_new_option_value_id();
                $source_value_id2dest_value_id[(int)$value->value_id]=$value_id;
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO 
                    page_filter_option_values (
                    option_id, 
                    value_id, 
                    value_name, 
                    site_id, 
                    value_pos
                    ) VALUES (
                    :option_id, 
                    :value_id, 
                    :value_name, 
                    :site_id, 
                    :value_pos          
                    )
                    ");
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_id', $option_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':value_id', $value_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':value_name', $value->value_name,PDO::PARAM_STR);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':value_pos', $value->value_pos,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('page filter common 50'/*.$e->getMessage()*/);}
            }
        }

        //copy variants and variants values
        $q_variants=$this->cols_els_id2variants($cur_cols_els_id,$source_site_id);
        /** @noinspection PhpUndefinedMethodInspection */
        while($variant=$q_variants->fetch(PDO::FETCH_OBJ)) {
            $var_id=$this->get_new_variant_id();
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO 
                    page_filter_variants (
                    var_id,
                    url,
                    site_id,
                    cols_els_id
                    ) VALUES (
                    :var_id,
                    :url,
                    :site_id,
                    :cols_els_id
                    )
                    ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':var_id', $var_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':url', $variant->url,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('page filter common 55'/*.$e->getMessage()*/);}

            $q_var_values=$this->var_id2var_options_values($variant->var_id,$source_site_id);
            /** @noinspection PhpUndefinedMethodInspection */
            while($value=$q_var_values->fetch(PDO::FETCH_OBJ)) {
                $value_id=$source_value_id2dest_value_id[(int)$value->value_id];
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO
                    page_filter_variants_option_values (
                    var_id, 
                    value_id, 
                    site_id
                    ) VALUES (
                    :var_id, 
                    :value_id, 
                    :site_id          
                    )
                    ");
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':var_id', $var_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':value_id', $value_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('page filter common 60'/*.$e->getMessage()*/);}
            }
        }

        return $cols_els_id;
    }

    //CREATE NEW
    private function cols_els_id2variants($cols_els_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT DISTINCT 
            var_id, 
            url
            FROM 
            page_filter_variants
            WHERE
            cols_els_id=:cols_els_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('page filter common 65'/*.$e->getMessage()*/);}
        return 0;
    }

    private function create_option($cols_els_id,$option_name,$site_id=site_id) {
        $option_id=$this->get_new_option_id();
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO 
                page_filter_options (
                cols_els_id, 
                site_id, 
                option_id, 
                option_name,
                option_pos
                ) VALUES (
                :cols_els_id, 
                :site_id, 
                :option_id, 
                :option_name, 
                :option_id 
                )
                ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_id', $option_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_name', $option_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('page filter common 70'.$e->getMessage(),1);}

        return $option_id;
    }
    private function create_option_value($option_id,$value_name,$site_id=site_id) {
        $value_id=$this->get_new_option_value_id();
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO 
                page_filter_option_values (
                option_id, 
                value_id, 
                value_name,
                site_id,
                value_pos
                ) VALUES (
                :option_id, 
                :value_id, 
                :value_name,
                :site_id,
                :value_id
                )
                ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_id', $option_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':value_id', $value_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':value_name', $value_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('page filter common 75'.$e->getMessage(),1);}

        return $value_id;
    }
    private function create_variant($cols_els_id,$url,$options_values_id_ar,$site_id) {
//        $options_values_id_ar=array(0,3,7,2);
        $var_id=$this->get_new_variant_id();

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO 
            page_filter_variants (
            var_id, 
            url, 
            site_id,
            cols_els_id
            ) VALUES (
            :var_id, 
            :url, 
            :site_id,                                          
            :cols_els_id                                          
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':var_id', $var_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':url', $url,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('page filter common 80'/*.$e->getMessage()*/);}

        $option_found=[];
        $options_values_id_ar_count=count($options_values_id_ar);
        for($i=0;$i<$options_values_id_ar_count;$i++) {
            $value_id=(int)$options_values_id_ar[$i];
            $value_data=$this->value_id2val_data($value_id,"page_filter_option_values.option_id",$site_id);
            $option_id=(int)$value_data->option_id;
            $option_found[$option_id]=1;

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uPage")->prepare("REPLACE INTO 
                page_filter_variants_option_values (
                var_id, 
                value_id, 
                site_id
                ) VALUES (
                :var_id, 
                :value_id, 
                :site_id                                                                
                )
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':var_id', $var_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':value_id', $value_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('page filter common 90'/*.$e->getMessage()*/);}
        }

        return $var_id;
    }

    private function option_id2values($option_id,$q_select="value_id",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            $q_select
            FROM 
            page_filter_option_values 
            WHERE
            option_id=:option_id AND
            site_id=:site_id
            ORDER BY 
            value_pos ASC
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_id', $option_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('page filter common 110'/*.$e->getMessage()*/);}
        return 0;
    }

    private function create_sample_option_values($option_id,$site_id=site_id) {
        for ($i = 0; $i < 2; $i++) {
            $value_name = $this->text("Default value name ").' '.($i+1);
            /*$value_id = */$this->create_option_value($option_id, $value_name, $site_id);
        }
    }
    private function create_sample_options($cols_els_id,$site_id=site_id) {
        for($i=0;$i<2;$i++) {
            $option_name = $this->text("Default option name").' '.($i+1);
            $option_id = $this->create_option($cols_els_id, $option_name, $site_id);

            //Create option_values
            $this->create_sample_option_values($option_id, $site_id);
        }
    }

    public function attach_el2col($col_id,$site_id=site_id) {
        $el_pos=$this->uPage->define_new_el_pos($col_id);

        //get new cols_els_id
        $cols_els_id=$this->uPage->get_new_cols_els_id();
        $this->clear_page_filter_cache($cols_els_id,$site_id);

        //attach art to col
        $res=$this->uPage->add_el2db($cols_els_id,$el_pos,'page_filter',$col_id,0);

        $this->create_sample_options($cols_els_id,$site_id);

        //Create sample variants
        $options_stm=$this->cols_els_id2options($cols_els_id,"option_id,option_name",$site_id);//get all options
        /** @noinspection PhpUndefinedMethodInspection */
        $options_ar=$options_stm->fetchAll(PDO::FETCH_OBJ);
        $options_ar_count=count($options_ar);
        $options_values_ar=[];
        for($i=0;$i<$options_ar_count;$i++) {
            $option=$options_ar[$i];
            $option_id=(int)$option->option_id;

            $option_values_stm = $this->option_id2values($option_id, "value_id",$site_id);//get all values of every option
            /** @noinspection PhpUndefinedMethodInspection */
            $options_values_ar[$i] = $option_values_stm->fetchAll(PDO::FETCH_OBJ);
        }
        $url=u_sroot;
        $option_values_id_ar=array($options_values_ar[0][0]->value_id,$options_values_ar[1][0]->value_id);
        $this->create_variant($cols_els_id,$url,$option_values_id_ar,$site_id);//create new variant
        $option_values_id_ar=array($options_values_ar[0][1]->value_id,$options_values_ar[1][1]->value_id);
        $this->create_variant($cols_els_id,$url,$option_values_id_ar,$site_id);//create new variant

        echo '{'.$res[0].'}';
        exit;
    }
    //--CREATE NEW

    //SHOW EL
    public function cache_page_filter($cols_els_id,$site_id=site_id) {
        $dir='uPage/cache/page_filter/'.$site_id.'/'.$cols_els_id;

        //До этой строчки никаких запросов в БД!!!!
        if(!file_exists($dir.'/page_filter.html')||!file_exists($dir.'/page_filter.js')) {
            if(!file_exists($dir)) mkdir($dir,0755,true);

            $file = fopen($dir.'/page_filter.html', 'w');

            ob_start();?>
            <nav class="navbar navbar-default uPage_page_filter_container">
                <div class="container-fluid">
                    <!-- Collect the nav links, forms, and other content for toggling -->
                    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                        <ul class="nav navbar-nav"><?if($site_id==54) {?>
                                <li style="width: 33%" class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                                       aria-haspopup="true" aria-expanded="false">Продукт<span
                                                class="icon-down-open"></span></a>
                                    <ul class="dropdown-menu">
                                        <li><a><label><input type="checkbox" class="form-control uCat_filter_checkbox_1" onchange="uPage_page_filter_<?=$cols_els_id?>_filter_set_ch(1,'Уличная уборка')" value="Уличная уборка">Уличная уборка</label></a></li>
                                        <li><a><label><input type="checkbox" class="form-control uCat_filter_checkbox_1" onchange="uPage_page_filter_<?=$cols_els_id?>_filter_set_ch(1,'Уборка помещений')" value="Уборка помещений">Уборка помещений</label></a></li>
                                        <li><a><label><input type="checkbox" class="form-control uCat_filter_checkbox_1" onchange="uPage_page_filter_<?=$cols_els_id?>_filter_set_ch(1,'Уличная уборка и уборка помещений')" value="Уличная уборка и уборка помещений">Уличная уборка и уборка помещений</label></a></li>
                                        <li><a><label><input type="checkbox" class="form-control uCat_filter_checkbox_7" onchange="uPage_page_filter_<?=$cols_els_id?>_filter_set_ch(7,'Подметать')" value="Подметальные машины">Подметальные машины</label></a></li>

                                    </ul>
                                </li>
                                <li style="width: 33%" class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                                       aria-haspopup="true" aria-expanded="false">Тип уборки<span
                                                class="icon-down-open"></span></a>
                                    <ul class="dropdown-menu">
                                        <li><a><label><input type="checkbox" class="form-control uCat_filter_checkbox_7" onchange="uPage_page_filter_<?=$cols_els_id?>_filter_set_ch(7,'Подметать')" value="Подметать">Подметать</label></a></li>
                                        <li><a><label><input type="checkbox" class="form-control uCat_filter_checkbox_7" onchange="uPage_page_filter_<?=$cols_els_id?>_filter_set_ch(7,'Мыть')" value="Мыть">Мыть</label></a></li>
                                    </ul>
                                </li>
                                <li style="width: 33%" class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                                       aria-haspopup="true" aria-expanded="false">Силовая установка<span
                                                class="icon-down-open"></span></a>
                                    <ul class="dropdown-menu">
                                        <li><a><label><input type="checkbox" class="form-control uCat_filter_checkbox_9" onchange="uPage_page_filter_<?=$cols_els_id?>_filter_set_ch(9,'Дизель')" value="Дизель">Дизель</label></a></li>
                                        <li><a><label><input type="checkbox" class="form-control uCat_filter_checkbox_9" onchange="uPage_page_filter_<?=$cols_els_id?>_filter_set_ch(9,'Бензин')" value="Бензин">Бензин</label></a></li>
                                        <li><a><label><input type="checkbox" class="form-control uCat_filter_checkbox_9" onchange="uPage_page_filter_<?=$cols_els_id?>_filter_set_ch(9,'Электричество')" value="Электричество">Электричество</label></a></li>
                                        <li><a><label><input type="checkbox" class="form-control uCat_filter_checkbox_9" onchange="uPage_page_filter_<?=$cols_els_id?>_filter_set_ch(9,'Двигатель на сжиженном газе с водяным охлаждением')" value="Двигатель на сжиженном газе с водяным охлаждением">Двигатель на сжиженном газе с водяным охлаждением</label></a></li>
                                        <li><a><label><input type="checkbox" class="form-control uCat_filter_checkbox_9" onchange="uPage_page_filter_<?=$cols_els_id?>_filter_set_ch(9,'Механическая')" value="Механическая">Механическая</label></a></li>
                                    </ul>
                                </li>
                        <?}
                        else {
                            $options_stm = $this->cols_els_id2options($cols_els_id, "option_id,option_name", $site_id);
                            /** @noinspection PhpUndefinedMethodInspection */
                            $options_ar = $options_stm->fetchAll(PDO::FETCH_OBJ);
                            $options_ar_count = count($options_ar);
                            $node_width = (int)(100 / $options_ar_count);
                            for ($i = 0; $i < $options_ar_count; $i++) {
                                $option = $options_ar[$i]; ?>
                                <li style="width: <?= $node_width ?>%" class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                                       aria-haspopup="true" aria-expanded="false"><?= $option->option_name ?> <span
                                                class="icon-down-open"></span></a>
                                    <ul class="dropdown-menu">
                                        <?
                                        $values_stm = $this->option_id2values($option->option_id, "value_id,value_name", $site_id);
                                        /** @noinspection PhpUndefinedMethodInspection */
                                        while ($value = $values_stm->fetch(PDO::FETCH_OBJ)) {
                                            ?>
                                            <li><a><label><input
                                                                data-value_id="<?= $value->value_id ?>"
                                                                type="checkbox"
                                                                class="form-control"
                                                                onclick="uPage_page_filter_<?= $cols_els_id ?>_select()"
                                                        >
                                                        <?= $value->value_name ?></label></a></li>
                                            <?
                                        } ?>
                                    </ul>
                                </li>
                            <?
                            }
                        }?>
                        </ul>
                        <div class="navbar-form navbar-right">
                            <?if(site_id==54) {?><a class="btn btn-default" onclick="uPage_page_filter_<?=$cols_els_id?>_select()"><span class="icon-search"></span> <?=$this->text("Search btn text")?></a><?}
                            else {?>
                            <a class="btn btn-default" id="uPage_page_filter_<?=$cols_els_id?>_submit_btn"><span class="icon-search"></span> <?=$this->text("Search btn text")?></a>
                            <?}?>
                        </div>
                    </div><!-- /.navbar-collapse -->
                </div><!-- /.container-fluid -->
            </nav>
            <?
            fwrite($file, ob_get_contents());
            fclose($file);
            ob_end_clean();


            $file = fopen($dir.'/page_filter.js', 'w');
            ob_start();

//            if($conf->autoplay_speed=(int)$conf->autoplay_speed) {
            ?>
            <!--suppress ES6ConvertVarToLetConst -->
            <?if(site_id==54) {?>
                <script type="text/javascript">
                    uPage_page_filter_<?=$cols_els_id?>_filter_field_id=[];
                    uPage_page_filter_<?=$cols_els_id?>_filter_field_val=[];
                    uPage_page_filter_<?=$cols_els_id?>_filter_set_ch=function (field_id,field_val) {
                        field_id=parseInt(field_id);
                        var last=uPage_page_filter_<?=$cols_els_id?>_filter_field_id.length;
                        var found=false;
                        for(var i=0;i<last;i++) {
                            if(uPage_page_filter_<?=$cols_els_id?>_filter_field_id[i]===field_id&&uPage_page_filter_<?=$cols_els_id?>_filter_field_val[i]===field_val) {
                                for(;i<(last-1);i++) {
                                    uPage_page_filter_<?=$cols_els_id?>_filter_field_id[i]=uPage_page_filter_<?=$cols_els_id?>_filter_field_id[i+1];
                                    uPage_page_filter_<?=$cols_els_id?>_filter_field_val[i]=uPage_page_filter_<?=$cols_els_id?>_filter_field_val[i+1];
                                }
                                uPage_page_filter_<?=$cols_els_id?>_filter_field_id.pop();
                                uPage_page_filter_<?=$cols_els_id?>_filter_field_val.pop();
                                found=true;

                                break;
                            }
                        }
                        if(!found) {
                            uPage_page_filter_<?=$cols_els_id?>_filter_field_id[last]=field_id;
                            uPage_page_filter_<?=$cols_els_id?>_filter_field_val[last]=field_val;
                        }
                    };

                    $('.uPage_page_filter_container .dropdown-menu').click(function(e) {
                        e.stopPropagation();
                    });

                    uPage_page_filter_<?=$cols_els_id?>_select=function(pageNum) {
                        pageNum = typeof pageNum!== 'undefined' ? pageNum: '0';
                        // if(uCat.cat_descr_only_on_first_page) {
                        //     if(pageNum) $("#uCat_cat_descr").hide();
                        //     else $("#uCat_cat_descr").show();
                        // }

                        let last=uPage_page_filter_<?=$cols_els_id?>_filter_field_id.length;
                        let data="&sort=item_title&order=ASC"+
                            '&cat_id='+28+
                            '&page='+pageNum+
                            '&list_view=plane';
                        for(var i= 0,j=0;i<last;i++) {
                            data+='&field_id_'+j+'='+uPage_page_filter_<?=$cols_els_id?>_filter_field_id[i]+
                                '&field_val_'+j+'='+encodeURIComponent(uPage_page_filter_<?=$cols_els_id?>_filter_field_val[i]);
                            j++;
                        }

                        var curTimestamp=new Date().getTime();
                        // $('div.items_container').html('<div class="well well-lg loading bg-primary">Загрузка каталога.</div>');
                        //noinspection JSUnresolvedVariable
                        // history.pushState('data', '', u_sroot+u_mod+"/items/"+uCat.cat_id+"?"+data);
                        //noinspection JSUnresolvedVariable
                        document.location=u_sroot+"uCat/items/28?"+curTimestamp+data;
                        return 0;
                        $.ajax({
                            type: "GET",
                            url: u_sroot+"uCat//items/28?"+curTimestamp+"&results_only=true",
                            data: data,
                            timeout:20000,
                            success: function(answer){
                                $('div.items_container').html(answer);
                                if(uCat.edit_mode) $(".u235_eip").show();

                                $('.uTooltip').uitooltip();
                                // $('.selectpicker')/*.selectpicker()*/;

                                obj = $('.items_count_spinner');
                                $.each(obj, function(index, value) {
                                    max_val = $(this).data('max');
                                    if(max_val == '0') {
                                        min_val = 0;
                                    }
                                    else {
                                        min_val = 1;
                                    }
                                    $(this).TouchSpin({max:max_val,min:min_val});
                                });

                                uCat.tune_items_height();
                            },
                            error: function(){
                                try{uCat.save_notice.remove()}catch(e){}
                                uTemplate_common.ajax_timeout();
                                return false;
                            }
                        });
                    };
                </script>
            <?}
            else {?>
            <script type="text/javascript">
                var uPage_page_filter_<?=$cols_els_id?>={};
                uPage_page_filter_<?=$cols_els_id?>.variants=[];
                <?
                $variants_stm=$this->cols_els_id2variants($cols_els_id,$site_id);
                /** @noinspection PhpUndefinedMethodInspection */
                while($var=$variants_stm->fetch(PDO::FETCH_OBJ)) {
                    $var_id=(int)$var->var_id;
                    $url=$var->url;?>
                    var i=uPage_page_filter_<?=$cols_els_id?>.variants.length;
                    uPage_page_filter_<?=$cols_els_id?>.variants[i]=[];
                    uPage_page_filter_<?=$cols_els_id?>.variants[i]["var_id"]=<?=$var_id?>;
                    uPage_page_filter_<?=$cols_els_id?>.variants[i]["url"]=decodeURIComponent("<?=rawurlencode($url)?>");
                    uPage_page_filter_<?=$cols_els_id?>.variants[i]["values"]=[];
                    <?
                    $values_stm=$this->var_id2var_options_values($var_id,$site_id);
                    /** @noinspection PhpUndefinedMethodInspection */
                    while($value=$values_stm->fetch(PDO::FETCH_OBJ)) {
                        $value_id=(int)$value->value_id;
                        ?>
                        var j=uPage_page_filter_<?=$cols_els_id?>.variants[i]["values"].length;
                        uPage_page_filter_<?=$cols_els_id?>.variants[i]["values"][j]=<?=$value_id?>;
                    <?}
                }?>

                uPage_page_filter_<?=$cols_els_id?>_select=function() {
                    let submit_btn_obj=$("#uPage_page_filter_<?=$cols_els_id?>_submit_btn");
                    let checkboxes_ar=$("#uPage_el_<?=$cols_els_id?>_container input[type='checkbox']");
                    let checkboxes_ar_length=checkboxes_ar.length;

                    let variants_length=uPage_page_filter_<?=$cols_els_id?>.variants.length;
                    for(let i=0;i<variants_length;i++) {//Бегу по вариантам, которые есть
                        let variant=uPage_page_filter_<?=$cols_els_id?>.variants[i];
                        let values=variant["values"];
                        let values_length=values.length;
                        let found=0;
                        let expected;
                        for(let j=0;j<values_length;j++) {//Бегу по values каждого варианта
                            let value_id=values[j];
                            expected=0;
                            for(let k=0;k<checkboxes_ar_length;k++) {//Бегу по выбранным values из checkbox
                                let checkbox=$(checkboxes_ar[k]);
                                if(!checkbox.prop("checked")) continue;
                                expected++;
                                let checkbox_value_id=parseInt(checkbox.data("value_id"));
                                //Нужно проверить, соответствует ли values из checkbox и value варианта
                                if(checkbox_value_id===value_id) found++;
                            }
                        }
                        if(found===expected&&values_length===expected) {
                            submit_btn_obj.prop("href",variant["url"]).removeClass("disabled");
                            submit_btn_obj.parent().removeClass("disabled");
                            return 1;
                        }
                    }
                    submit_btn_obj.addClass("disabled");
                    submit_btn_obj.parent().addClass("disabled");
                    return 0;
                };

                uPage_page_filter_<?=$cols_els_id?>_select();
                </script>
                <?}?>
            <?
//        }
                fwrite($file, ob_get_contents());
            fclose($file);
            ob_end_clean();
        }
    }

    public function load_el_content($cols_els_id,$return=0,$site_id=site_id) {
        $this->cache_page_filter($cols_els_id,$site_id);
        $dir='uPage/cache/page_filter/'.$site_id.'/'.$cols_els_id;

        $result_ar=(object)array(
            "status"=>"done",
            "cols_els_id"=>$cols_els_id,

            "html"=>file_get_contents($dir."/page_filter.html"),
            "js"=>file_get_contents($dir."/page_filter.js")
        );

        if($return) return $result_ar;
        else echo json_encode($result_ar);

        return 0;
    }


    //EDITOR
    private function value_id2val_data($value_id,$q_select="value_name,option_name",$site_id=site_id) {
        if(!isset($this->value_id2opt_data_ar)) $this->value_id2opt_data_ar=[];
        if(!isset($this->value_id2opt_data_ar[$site_id])) $this->value_id2opt_data_ar[$site_id]=[];
        if(!isset($this->value_id2opt_data_ar[$site_id][$value_id])) $this->value_id2opt_data_ar[$site_id][$value_id]=[];

        if(!isset($this->value_id2opt_data_ar[$site_id][$value_id][$q_select])) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
                $q_select 
                FROM 
                page_filter_option_values
                JOIN 
                page_filter_options
                ON
                page_filter_option_values.option_id=page_filter_options.option_id
                WHERE
                value_id=:value_id AND
                page_filter_option_values.site_id=:site_id
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':value_id', $value_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                /** @noinspection PhpUndefinedMethodInspection */
                $this->value_id2opt_data_ar[$site_id][$value_id][$q_select]=$stm->fetch(PDO::FETCH_OBJ);
            }
            catch(PDOException $e) {$this->uFunc->error('page filter common 130'/*.$e->getMessage()*/);}
        }
        return $this->value_id2opt_data_ar[$site_id][$value_id][$q_select];
    }
    private function var_id2var_options_values($var_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            value_id 
            FROM 
            page_filter_variants_option_values 
            WHERE 
            var_id=:var_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':var_id', $var_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('page filter common 140'/*.$e->getMessage()*/);}

        return 0;
    }
    public function load_el_editor($cols_els_id,$site_id=site_id) {
        ?>
        <div class="container-fluid">
        <script type="text/javascript"></script>

        <h3><?=$this->text("Variants - section name")?></h3>
        <div id="uPage_setup_uPage_page_filter_editor">
            <?
            $variants_stm=$this->cols_els_id2variants($cols_els_id,$site_id);
            /** @noinspection PhpUndefinedMethodInspection */
            while($variant=$variants_stm->fetch(PDO::FETCH_OBJ)) {?>
                <div>
                <?$var_id=(int)$variant->var_id;?>
                    <div class="form-group">
                        <label><?=$this->text("Page url - label")?></label>
                        <div class="input-group">
                            <!--suppress HtmlFormInputWithoutLabel -->
                            <input
                                    type="text"
                                    class="form-control"
                                    data-var_id="<?=$var_id?>"
                                    value="<?=$variant->url?>"
                                    onblur="uPage_setup_uPage.page_filter.save_var_url(this)"
                            >
                            <div class="input-group-btn">
                                <button type="button" class="btn btn-danger" onclick="uPage_setup_uPage.page_filter.delete_var_init(<?=$var_id?>)"><span class="icon-cancel"></span> <?=$this->text("Delete - btn name")?></button>
                            </div>
                        </div>
                        <span class="help-block">
                        <?$var_values_stm=$this->var_id2var_options_values($var_id,$site_id);
                        /** @noinspection PhpUndefinedMethodInspection */
                        while($var_value=$var_values_stm->fetch(PDO::FETCH_OBJ)) {
                            $value_id=(int)$var_value->value_id;
                            $value_data=$this->value_id2val_data($value_id,"value_name,option_name,page_filter_option_values.option_id",$site_id);
                            $value_name=$value_data->value_name;
                            $option_name=$value_data->option_name;
                            $option_id=(int)$value_data->option_id;?>
                            <b class="page_filter_option_name_<?=$option_id?>"><?=$option_name?></b> - <span class="page_filter_value_name_<?=$value_id?>"><?=$value_name?></span>.
                        <?}?>
                        </span>
                    </div>
                </div>
            <?}
        ?>
        </div>

        <h3><?=$this->text("Create new variant")?> <small><button class="btn btn-primary" onclick="uPage_setup_uPage.page_filter.add_option()"><span class="icon-plus"></span> <?=$this->text("Create option - btn label")?></button></small></h3>
        <div id="uPage_page_filter_new_variant_container">

            <?$options_stm=$this->cols_els_id2options($cols_els_id,"option_id,option_name",$site_id);
            /** @noinspection PhpUndefinedMethodInspection */
            $options_ar=$options_stm->fetchAll(PDO::FETCH_OBJ);
            $options_ar_count=count($options_ar);
            for($i=0;$i<$options_ar_count;$i++) {
                $option=$options_ar[$i];
                $option_id=(int)$option->option_id?>

                        <h5><?=$this->text("Option - new option settings container header")?></h5>
                <div class="input-group">
                    <!--suppress HtmlFormInputWithoutLabel -->
                    <input
                            type="text"
                            class="form-control"
                            value="<?=$option->option_name?>"
                            data-option_id="<?=$option_id?>"
                            onblur="uPage_setup_uPage.page_filter.save_option_name(this)"
                    >
                    <span class="input-group-btn">
                        <button class="btn btn-primary" type="button" onclick="uPage_setup_uPage.page_filter.add_value(<?=$option_id?>)"><span class="icon-plus"></span> <?=$this->text("Create value - btn label")?></button>
                        <button
                                type="button"
                                class="btn btn-default"
                                data-option_id="<?=$option_id?>"
                                onclick="uPage_setup_uPage.page_filter.move_option('up',this)"><span class="icon-up-open"></span> <?=$this->text("Move up - btn label")?></button>
                        <button
                                type="button"
                                class="btn btn-default"
                                data-option_id="<?=$option_id?>"
                                onclick="uPage_setup_uPage.page_filter.move_option('down',this)"><span class="icon-down-open"></span> <?=$this->text("Move down - btn label")?></button>
                        <button type="button" class="btn btn-danger" onclick="uPage_setup_uPage.page_filter.delete_option_init(<?=$option_id?>)"><span class="icon-cancel"></span> <?=$this->text("Delete - btn name")?></button>
                    </span>
                </div>
                <?$values_stm=$this->option_id2values($option->option_id,"value_id,value_name",$site_id);?>
                <h5><?=$this->text("Values - section header")?></h5>
                <?/** @noinspection PhpUndefinedMethodInspection */
                while($value=$values_stm->fetch(PDO::FETCH_OBJ)) {
                        $value_id=(int)$value->value_id;?>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <!--suppress HtmlFormInputWithoutLabel -->
                                    <input type="checkbox" data-value_id="<?=$value_id?>">
                                </span>
                                <!--suppress HtmlFormInputWithoutLabel -->
                                <input
                                        type="text"
                                        class="form-control"
                                        value="<?=$value->value_name ?>"
                                        data-value_id="<?=$value_id?>"
                                        onblur="uPage_setup_uPage.page_filter.save_value_name(this)"
                                >
                                <div class="input-group-btn">
                                    <button
                                            type="button"
                                            class="btn btn-default"
                                            data-value_id="<?=$value_id?>"
                                            onclick="uPage_setup_uPage.page_filter.move_value('up',this)"
                                    ><span class="icon-up-open"></span> <?=$this->text("Move up - btn label")?></button>
                                    <button
                                            type="button"
                                            class="btn btn-default"
                                            data-value_id="<?=$value_id?>"
                                            onclick="uPage_setup_uPage.page_filter.move_value('down',this)"
                                    ><span class="icon-down-open"></span> <?=$this->text("Move down - btn label")?></button>
                                    <button type="button" class="btn btn-danger" onclick="uPage_setup_uPage.page_filter.delete_value_init(<?=$value_id?>)"><span class="icon-cancel"></span> <?=$this->text("Delete - btn name")?></button>
                                </div>
                            </div>
                <?}?>
                <p>&nbsp;</p>
            <?}?>
        <div><button type="button" class="btn btn-primary" onclick="uPage_setup_uPage.page_filter.add_var()"><?=$this->text("Save selected variant - btn label")?></button></div>


        <div class="bs-callout bs-callout-primary">
            <p><?=$this->text("Hint 1")?></p>
            <p><?=$this->text("Hint 2")?></p>
        </div>
    <?}


    private function move_value($cols_els_id,$dir,$site_id=site_id) {
        if(!isset($_REQUEST["value_id"])) $this->uFunc->error("page filter common 150",1);
        $value_id=(int)$_REQUEST["value_id"];

        //get current value's pos
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            value_pos,
            value_id
            FROM
            page_filter_option_values
            WHERE
            value_id=:value_id AND 
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':value_id', $value_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('page filter common 160'/*.$e->getMessage()*/,1);}
        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$value=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error("page filter common 170",1);

        $value_id=$value->value_id;

        if($dir=='up') {
            $all_values_cur_pos=$value->value_pos-1;
            $all_values="value_pos+1";
            $this_value=$value->value_pos-1;
        }
        else {
            $all_values_cur_pos=$value->value_pos+1;
            $all_values="value_pos-1";
            $this_value=$value->value_pos+1;
        }

        //move upper page_filter down/up
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
            page_filter_option_values
            SET
            value_pos=".$all_values."
            WHERE
            value_pos=:value_pos AND
            value_id=:value_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':value_pos', $all_values_cur_pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':value_id', $value_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('page filter common 180'/*.$e->getMessage()*/,1);}

        //move current value up/down
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
            page_filter_option_values
            SET
            value_pos=:value_pos
            WHERE
            value_id=:value_id AND
            value_id=:value_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':value_pos', $this_value,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':value_id', $value_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':value_id', $value_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('page filter common 190'/*.$e->getMessage()*/,1);}

        //clear cache
        $this->clear_page_filter_cache($cols_els_id);
    }
    private function move_option($cols_els_id,$dir,$site_id=site_id) {//TODO-nik87 option_pos менять только внутри cols_els_id - иначе для всего site_id меняется.
        //TODO-nik87 при передвижении наверх искать первый элемент выше и, если текущий option_pos не соответствует "выше"+1, то текущий в переменной переписать на "выше" +1. Иначе если pos=250, а выше - 100 - придется 150 раз кликать кнопку.
        //TODO-nik87 тоже самое повторить для вниз, для value_pos, для табов
        if(!isset($_REQUEST["option_id"])) $this->uFunc->error("page filter common 200",1);
        $option_id=(int)$_REQUEST["option_id"];

        //get current option's pos
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            option_pos,
            option_id
            FROM
            page_filter_options
            WHERE
            option_id=:option_id AND 
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_id', $option_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('page filter common 210'/*.$e->getMessage()*/,1);}
        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$option=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error("page filter common 220",1);

        $option_id=$option->option_id;

        if($dir=='up') {
            $all_options_cur_pos=$option->option_pos-1;
            $all_options="option_pos+1";
            $this_option=$option->option_pos-1;
        }
        else {
            $all_options_cur_pos=$option->option_pos+1;
            $all_options="option_pos-1";
            $this_option=$option->option_pos+1;
        }

        //move upper page_filter down/up
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
            page_filter_options
            SET
            option_pos=".$all_options."
            WHERE
            option_pos=:option_pos AND
            option_id=:option_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_pos', $all_options_cur_pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_id', $option_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('page filter common 230'/*.$e->getMessage()*/,1);}

        //move current option up/down
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
            page_filter_options
            SET
            option_pos=:option_pos
            WHERE
            option_id=:option_id AND
            option_id=:option_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_pos', $this_option,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_id', $option_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_id', $option_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('page filter common 240'/*.$e->getMessage()*/,1);}

        //clear cache
        $this->clear_page_filter_cache($cols_els_id);
    }

    //URL
    private function save_url($cols_els_id,$url,$site_id=site_id) {
        if(!isset($_REQUEST["var_id"])) $this->uFunc->error("page filter common 250",1);
        $var_id=(int)$_REQUEST["var_id"];

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE 
            page_filter_variants
            SET
            url=:url
            WHERE
            var_id=:var_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':url', $url,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':var_id', $var_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('page filter common 260'/*.$e->getMessage()*/,1);}

        //clear cache
        $this->clear_page_filter_cache($cols_els_id);

        echo json_encode(array(
            "status"=>"done"
        ));
        exit;
    }

    //option name
    private function save_option_name($cols_els_id,$option_name,$site_id=site_id) {
        if(!isset($_REQUEST["option_id"])) $this->uFunc->error("page filter common 270",1);
        $option_id=(int)$_REQUEST["option_id"];

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE 
            page_filter_options
            SET
            option_name=:option_name
            WHERE
            option_id=:option_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_name', $option_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_id', $option_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('page filter common 280'/*.$e->getMessage()*/,1);}

        //clear cache
        $this->clear_page_filter_cache($cols_els_id);

        echo json_encode(array(
            "status"=>"done"
        ));
        exit;
    }
    //value name
    private function save_value_name($cols_els_id,$value_name,$site_id=site_id) {
        if(!isset($_REQUEST["value_id"])) $this->uFunc->error("page filter common 290",1);
        $value_id=(int)$_REQUEST["value_id"];

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE 
            page_filter_option_values
            SET
            value_name=:value_name
            WHERE
            value_id=:value_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':value_name', $value_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':value_id', $value_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('page filter common 300'/*.$e->getMessage()*/,1);}

        //clear cache
        $this->clear_page_filter_cache($cols_els_id);

        echo json_encode(array(
            "status"=>"done"
        ));
        exit;
    }

    private function delete_option_variants($option_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("DELETE page_filter_variants FROM
            page_filter_variants
            JOIN 
            page_filter_variants_option_values
            ON
            page_filter_variants.var_id=page_filter_variants_option_values.var_id
            JOIN page_filter_option_values
            ON
            page_filter_option_values.value_id=page_filter_variants_option_values.value_id
            WHERE
            option_id=:option_id AND
            page_filter_variants.site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_id', $option_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('page filter common 310'/*.$e->getMessage()*/);}
    }
    private function delete_option($cols_els_id,$site_id=site_id) {
        if(!isset($_REQUEST["option_id"])) $this->uFunc->error("page filter common 320",1);
        $option_id=(int)$_REQUEST["option_id"];

        //delete all variants of option
        $this->delete_option_variants($option_id);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("DELETE FROM 
            page_filter_options
            WHERE
            option_id=:option_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_id', $option_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('page filter common 330'/*.$e->getMessage()*/);}

        $this->clear_page_filter_cache($cols_els_id,$site_id);

        echo json_encode(array("status"=>"done"));
    }
    private function delete_value($cols_els_id,$site_id=site_id) {
        if(!isset($_REQUEST["value_id"])) $this->uFunc->error("page filter common 340",1);
        $value_id=(int)$_REQUEST["value_id"];

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("DELETE FROM 
            page_filter_option_values
            WHERE
            value_id=:value_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':value_id', $value_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('page filter common 350'/*.$e->getMessage()*/);}

        $this->clear_page_filter_cache($cols_els_id,$site_id);

        echo json_encode(array("status"=>"done"));
    }
    private function delete_variant($cols_els_id,$site_id=site_id) {
        if(!isset($_REQUEST["var_id"])) $this->uFunc->error("page filter common 360",1);
        $var_id=(int)$_REQUEST["var_id"];

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("DELETE FROM 
            page_filter_variants
            WHERE
            var_id=:var_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':var_id', $var_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('page filter common 370'/*.$e->getMessage()*/);}

        $this->clear_page_filter_cache($cols_els_id,$site_id);

        echo json_encode(array("status"=>"done"));
    }

    public function save_el_conf($cols_els_id,$site_id=site_id) {
        if(isset($_REQUEST["url"])) {
            $this->save_url($cols_els_id,$_REQUEST["url"],$site_id);
        }
        if(isset($_REQUEST["option_name"])) {
            $this->save_option_name($cols_els_id,$_REQUEST["option_name"],$site_id);
        }
        if(isset($_REQUEST["value_name"])) {
            $this->save_value_name($cols_els_id,$_REQUEST["value_name"],$site_id);
        }
        else if(isset($_REQUEST["dir"],$_REQUEST["value_id"])) {
            $this->move_value($cols_els_id,$_REQUEST["dir"],$site_id);
            echo json_encode(array("status"=>"done"));
            exit;
        }
        else if(isset($_REQUEST["dir"],$_REQUEST["option_id"])) {
            $this->move_option($cols_els_id,$_REQUEST["dir"],$site_id);
            echo json_encode(array("status"=>"done"));
            exit;
        }
//        else if(isset($_REQUEST["autoplay_speed"])) {
//            $this->save_tabs_settings($cols_els_id,$_REQUEST["autoplay_speed"],$site_id);
//        }
        else if(isset($_REQUEST["new_option"])) {
            $option_name = $this->text("Default option name");
            $option_id = $this->create_option($cols_els_id, $option_name, $site_id);

            //Create option_values
            $this->create_sample_option_values($option_id, $site_id);

            $this->clear_page_filter_cache($cols_els_id,$site_id);

            echo json_encode(array("status"=>"done"));
            exit;
        }
        else if(isset($_REQUEST["new_value"])) {
            if(!isset($_REQUEST["option_id"])) $this->uFunc->error("page filter common 380",1);
            $value_name = $this->text("Default value name ");
            /*$value_id = */$this->create_option_value($_REQUEST["option_id"], $value_name, $site_id);

            $this->clear_page_filter_cache($cols_els_id,$site_id);

            echo json_encode(array("status"=>"done"));
            exit;
        }
        else if(isset($_REQUEST["new_var"])) {
            if(!isset($_REQUEST["checkboxes_selected_ar"])) $_REQUEST["checkboxes_selected_ar"]=[];
            $this->create_variant($cols_els_id,u_sroot,$_REQUEST["checkboxes_selected_ar"],$site_id);
            $this->clear_page_filter_cache($cols_els_id,$site_id);

            echo json_encode(array("status"=>"done"));
            exit;
        }
        else if(isset($_REQUEST["delete_option"])) {
            $this->delete_option($cols_els_id,$site_id);
            exit;
        }
        else if(isset($_REQUEST["delete_value"])) {
            $this->delete_value($cols_els_id,$site_id);
            exit;
        }
        else if(isset($_REQUEST["delete_var"])) {
            $this->delete_variant($cols_els_id,$site_id);
            exit;
        }
    }

    function __construct (&$uPage) {
        $this->uPage=&$uPage;
        $this->uCore=&$this->uPage->uCore;
        if(!isset($this->uPage)) $this->uPage=new common($this->uCore);
        $this->uFunc=&$this->uPage->uFunc;
    }
}