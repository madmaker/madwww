<?php
require_once "processors/classes/uFunc.php";

class cat_filter {
    private $uFunc;
    private $uCore,$q_fields,$cat_id;
    private function get_field_values($field_id) {
        if(uString::isDigits($field_id)) $field="field_".$field_id;
        else $field="item_price";

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT
            ".$field."
            FROM
            u235_items
            JOIN
            u235_cats_items
            ON
            u235_cats_items.item_id=u235_items.item_id AND
            u235_cats_items.site_id=u235_items.site_id
            WHERE
            parts_autoadd=0 AND
            u235_cats_items.cat_id=:cat_id AND
            u235_items.site_id=:site_id
            ORDER BY
            ".$field." ASC
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $this->cat_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            $qr=$stm->fetchAll(PDO::FETCH_ASSOC);
            $i=0;
            foreach ($qr as $key => $value) {
                $val=$value[$field];
                if(!empty($val)&&$val!='0') {
                    $data[$i]=$val;
                    $i++;
                }
            }
            if(isset($data)) return $data;
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}

        return array();
    }
    public function make_filter() {
        $currency='р';
        if(site_id==54) {
            $currency='Eur';
        }
        $cnt='<div class="row">';
        $vals_ar=$this->get_field_values('price');
        if(isset($vals_ar[0])) {
            $cnt.='<div class="col-xs-6 col-md-12">
        <div class="form-group">
        <label>Цена</label>
        <div class="input-group">
            <input type="text" id="amount_field_price" class="form-control" /><div class="input-group-addon">'.$currency.'</div>
        </div>
        <div id="slider-range_field_price"></div>';
            $cnt.='<script type="text/javascript">
            $(document).ready(function() {
            jQuery( "#slider-range_field_price" ).slider({
                range: true,
                min: ' . $vals_ar[0] . ',
                max: ' . $vals_ar[count($vals_ar) - 1] . ',
                values: [ ' . $vals_ar[0] . ',' . $vals_ar[count($vals_ar) - 1] . '],
                slide: function( event, ui ) {
                    jQuery( "#amount_field_price" ).val( ui.values[ 0 ] + " - " + ui.values[ 1 ]);
                },
                change:function(event,ui) {
                    uCat.filter_set_range("price",jQuery( "#amount_field_price" ).val());
                }
            });
            jQuery ( "#amount_field_price" ).val( jQuery( "#slider-range_field_price" ).slider( "values", 0 ) + " - " + jQuery( "#slider-range_field_price" ).slider( "values", 1 ));
            });
        </script>';
            $cnt.='</div>
        </div>';
        }

        foreach ($this->q_fields as $key => $field) {
            $vals_ar=$this->get_field_values($field->field_id);
            if($vals_ar) {$cnt.='<div class="col-xs-6 col-md-12">';
                if($field->filter_type_val=='checkbox') {
                    $cnt.='<div class="form-group">
                        <label>'.$field->field_title.'</label>';
                    $cnt.='';

                    for($i=0;$i<count($vals_ar);$i++) {
                        if($field->field_sql_type=='TINYTEXT') $val=uString::sql2text($vals_ar[$i],true);
                        else $val=$vals_ar[$i];

                        if($field->field_style=='date') {
                            if(!empty($val)) $val=date('d.m.Y',$val);
                            else continue;
                        }
                        elseif($field->field_style=='datetime') {
                            if(!empty($val)) $val=date('d.m.Y H:i',$val);
                            else continue;
                        }

                        if(!empty($val)) {
                            $cnt.='';
                            if($field->field_style=='date'||$field->field_style=='datetime'||$field->field_style=='time'||$field->field_style=='link') {
                                $cnt.='<div>
                                    <label>
                                    <input type="checkbox" class="uCat_filter_checkbox_'.$field->field_id.'" onchange="uCat.filter_set_ch('.$field->field_id.',\''.uString::sql2text($vals_ar[$i],true).'\')" onclick="uCat.filter_set_ch('.$field->field_id.',\''.uString::sql2text($vals_ar[$i],true).'\')" value="'.uString::sql2text($vals_ar[$i],true).'"> <span >'.$val.'</span>'.
                                    /*<input class="uCat_filter_checkbox_'.$field->field_id.'" type="hidden" value="'.uString::sql2text($vals_ar[$i],true).'">*/
                                    '</label>
                                    </div>';
                            }
                            else {
                                $cnt.='<div>
                                    <label>
                                    <input type="checkbox" class="uCat_filter_checkbox_'.$field->field_id.'" onchange="uCat.filter_set_ch('.$field->field_id.',\''.$val.'\')" value="'.uString::sql2text($vals_ar[$i],true).'"> <span >'.$val.'</span>'.
                                    /*<input class="uCat_filter_checkbox_'.$field->field_id.'" type="hidden" value="'.uString::sql2text($vals_ar[$i],true).'">*/
                                    '</label>
                                    </div>';
                            }
                        }
                    }
                    $cnt.='
                        </div>';
                }
                elseif($field->filter_type_val=='range') {
                    $step=1;
                    for($i=0;$i<count($vals_ar);$i++) {
                        $dotpos=strpos('0'.$vals_ar[$i],'.');
                        if($dotpos) {
                            $afterDot=explode('.',$vals_ar[$i]);
                            $newStep=1/(strlen($afterDot[1])*10);
                            if($step>$newStep) $step=$newStep;
                        }
                    }

                    $cnt.='<div class="form-group">
                        <label>'.uString::sql2text($field->field_title);
                    if($field->field_style=='datetime') $cnt.=', '.uString::sql2text($field->field_units);

                    $cnt.='</label>
                        <div class="'.($field->field_style!='datetime'?'input-group':'').'">';
                    if($field->field_style=='date') $cnt.='<input type="text" id="amount_field_'.$field->field_id.'_view" class="form-control">
                        <input type="text" id="amount_field_'.$field->field_id.'" style="display:none">';
                    elseif($field->field_style=='datetime') $cnt.='<textarea id="amount_field_'.$field->field_id.'_view" disabled class="form-control" style="height:55px"></textarea>
                            <input type="text" id="amount_field_'.$field->field_id.'" style="display:none">';
                    else $cnt.='<input type="text" id="amount_field_'.$field->field_id.'" class="form-control">';
                    if($field->field_style!='datetime')$cnt.='<div class="input-group-addon">'.uString::sql2text($field->field_units).'</div>';
                    $cnt.='</div>
                        <div id="slider-range_field_'.$field->field_id.'"></div>';
                    $cnt.='<script type="text/javascript">
                    $(document).ready(function() {
                            jQuery( "#slider-range_field_'.$field->field_id.'" ).slider({
                              range: true,
                              min: '.$vals_ar[0].',
                              max: '.$vals_ar[count($vals_ar)-1].',
                              values: [ '.$vals_ar[0].','.$vals_ar[count($vals_ar)-1].'],
                              step: '.$step.',
                              slide: function( event, ui ) {';
                    if($field->field_style=='date') $cnt.='
                        jQuery( "#amount_field_'.$field->field_id.'_view" ).val( date("d.m.Y",ui.values[ 0 ]) + " - " + date("d.m.Y",ui.values[1]));';
                    elseif($field->field_style=='datetime') $cnt.='
                        jQuery( "#amount_field_'.$field->field_id.'_view" ).val( "от "+date("d.m.Y H:i",ui.values[ 0 ]) + "\nдо " + date("d.m.Y H:i",ui.values[1]));';
                    $cnt.='
                        jQuery( "#amount_field_'.$field->field_id.'" ).val( ui.values[ 0 ] + " - " + ui.values[1]);
                        },
                                change:function(event,ui) {
                                    uCat.filter_set_range('.$field->field_id.',jQuery( "#amount_field_'.$field->field_id.'" ).val());
                                }
                            });';
                    if($field->field_style=='date') $cnt.='
                        jQuery ( "#amount_field_'.$field->field_id.'_view" ).val( date("d.m.Y",jQuery( "#slider-range_field_'.$field->field_id.'" ).slider( "values", 0 )) + " - " + date("d.m.Y",jQuery( "#slider-range_field_'.$field->field_id.'" ).slider( "values", 1 )));';
                    elseif($field->field_style=='datetime') $cnt.='
                        jQuery ( "#amount_field_'.$field->field_id.'" ).val("от "+ date("d.m.Y H:i",jQuery( "#slider-range_field_'.$field->field_id.'" ).slider( "values", 0 )) + "\nдо " + date("d.m.Y H:i",jQuery( "#slider-range_field_'.$field->field_id.'" ).slider( "values", 1 )));';
                    $cnt.='
                        jQuery ( "#amount_field_'.$field->field_id.'" ).val( jQuery( "#slider-range_field_'.$field->field_id.'" ).slider( "values", 0 ) +                            " - " + jQuery( "#slider-range_field_'.$field->field_id.'" ).slider( "values", 1 ));
                        });
                        </script>';
                    $cnt.='</div>';
                }
                $cnt.='</div>';
            }
        };
        reset($this->q_fields);
        if(!empty($cnt)) $cnt.='
            </div>
        <button class="btn btn-primary btn-sm" onclick="uCat.filter_apply()">Применить</button>
        <button class="btn btn-default btn-sm" onclick="uCat.filter_reset()">Сбросить</button>';

        return '<div class="uCat_filter highlight" id="uCat_filter_bar">'.$cnt.'</div>';
    }

    function __construct (&$uCore,$q_fields,$cat_id) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);

        $this->q_fields=$q_fields;
        $this->cat_id=$cat_id;
    }
}
