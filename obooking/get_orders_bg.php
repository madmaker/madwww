<?php
namespace obooking;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;
use uString;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "obooking/classes/common.php";
require_once "obooking/orders_get_filter_settings_bg.php";

class get_orders_bg{
    /**
     * @var orders_get_filter_settings_bg
     */
    private $orders_get_filter_settings_bg;
    private $obooking;
    private $uFunc;

    public function orders_list($site_id=site_id) {?>
        <div class="input-group">
            <!--suppress HtmlFormInputWithoutLabel -->
            <input type="text" id="obooking_orders_filter" class="form-control" placeholder="Фильтр" onkeyup="obooking_inline_edit.orders_filter()">
            <span class="input-group-btn">
                <button class="btn btn-default" type="button" onclick="obooking_inline_edit.orders_filter()"><span class="icon-search"></span></button>
            </span>
        </div>

        <table class="table table-condensed table-responsive">
            <tr>
                <td style="white-space: nowrap; cursor: pointer;" title="Дата последнего изменения заявки">
                    <b onclick="obooking_orders.filter_open_timestamps_list()" class="icon-filter"></b>
                    <b onclick="obooking_orders.setup_sorting('timestamp')">Дата</b>
                    <?php
                    if($field_is_sorted=$this->orders_get_filter_settings_bg->field_is_sorted('timestamp')) {
                        $icon_add=$field_is_sorted["direction"]==="ASC"?"-up":"-down";
                        $sort_order=$field_is_sorted["order"]+1;
                    }
                    else {
                        $sort_order = $icon_add = "";
                    }
                    ?>
                    <b onclick="obooking_orders.setup_sorting('timestamp')">
                        <b class="icon-sort<?=$icon_add?>"></b>
                        <b><?=$sort_order?></b>
                    </b><?php
                    $timestamps_start=$this->orders_get_filter_settings_bg->get_filtered_timestamps("start");
                    $timestamps_end=$this->orders_get_filter_settings_bg->get_filtered_timestamps("end");
                    if($timestamps_start||$timestamps_end) {
                    ?>
                        <div><span onclick="obooking_orders.toggle_timestamp2filter()" class="icon-cancel" title="удалить этот фильтр"></span> <?php
                            if($timestamps_start) {
                                print 'с ';
                                print date('d.m.Y',$timestamps_start);
                            }
                            if($timestamps_start&&$timestamps_end) {
                                print ' ';
                            }
                            if($timestamps_end) {
                                print 'по ';
                                print date('d.m.Y',$timestamps_end);
                            }
                            ?></div>
                        <?php
                        if($timestamps_start&&!$timestamps_end) {
                            $q_timestamps_filter=" timestamp>=".$timestamps_start." AND ";
                        }
                        elseif(!$timestamps_start&&$timestamps_end) {
                            $q_timestamps_filter=" timestamp<=".($timestamps_end+86400)." AND ";
                        }
                        else {
                            $q_timestamps_filter = " (timestamp>=" . $timestamps_start . " AND timestamp<=" . ($timestamps_end + 86400) . ") AND ";
                        }
                    }
                    else {
                        $q_timestamps_filter = "";
                    } ?>
                </td>
                <td style="white-space: nowrap; cursor: pointer;">
                    <b onclick="obooking_orders.filter_open_next_contact_dates_list()" class="icon-filter"></b>
                    <b onclick="obooking_orders.setup_sorting('next_contact_date')">Дата следующего контакта</b>
                    <?php
                    if($field_is_sorted=$this->orders_get_filter_settings_bg->field_is_sorted('next_contact_date')) {
                        $icon_add=$field_is_sorted["direction"]==="ASC"?"-up":"-down";
                        $sort_order=$field_is_sorted["order"]+1;
                    }
                    else {
                        $sort_order = $icon_add = "";
                    }
                    ?>
                    <b onclick="obooking_orders.setup_sorting('next_contact_date')">
                        <b class="icon-sort<?=$icon_add?>"></b>
                        <b><?=$sort_order?></b>
                    </b><?php
                    $next_contact_dates_start=$this->orders_get_filter_settings_bg->get_filtered_next_contact_dates("start");
                    $next_contact_dates_end=$this->orders_get_filter_settings_bg->get_filtered_next_contact_dates("end");
                    if($next_contact_dates_start||$next_contact_dates_end) {
                    ?>
                        <div><span onclick="obooking_orders.toggle_next_contact_date2filter()" class="icon-cancel" title="удалить этот фильтр"></span> <?php
                            if($next_contact_dates_start) {
                                print 'с ';
                                print date('d.m.Y',$next_contact_dates_start);
                            }
                            if($next_contact_dates_start&&$next_contact_dates_end) {
                                print ' ';
                            }
                            if($next_contact_dates_end) {
                                print 'по ';
                                print date('d.m.Y',$next_contact_dates_end);
                            }
                            ?></div>
                        <?php
                        if($next_contact_dates_start&&!$next_contact_dates_end) {
                            $q_next_contact_dates_filter=" next_contact_date>=".$next_contact_dates_start." AND ";
                        }
                        elseif(!$next_contact_dates_start&&$next_contact_dates_end) {
                            $q_next_contact_dates_filter=" next_contact_date<=".($next_contact_dates_end+86400)." AND ";
                        }
                        else {
                            $q_next_contact_dates_filter = " (next_contact_date>=" . $next_contact_dates_start . " AND next_contact_date<=" . ($next_contact_dates_end + 86400) . ") AND ";
                        }
                    }
                    else {
                        $q_next_contact_dates_filter = "";
                    } ?>
                </td>
                <td style="white-space: nowrap; cursor: pointer;">
                    <b onclick="obooking_orders.filter_open_offices_list()" class="icon-filter"></b>
                    <b onclick="obooking_orders.setup_sorting('office_name')">Филиал</b>
                    <?php
                    if($field_is_sorted=$this->orders_get_filter_settings_bg->field_is_sorted('office_name')) {
                        $icon_add=$field_is_sorted["direction"]==="ASC"?"-up":"-down";
                        $sort_order=$field_is_sorted["order"]+1;
                    }
                    else {
                        $sort_order = $icon_add = "";
                    }
                    ?>
                    <b onclick="obooking_orders.setup_sorting('office_name')">
                        <b class="icon-sort<?=$icon_add?>"></b>
                        <b><?=$sort_order?></b>
                    </b><?php
                    $offices_ar=$this->orders_get_filter_settings_bg->get_filtered_offices();
                    $offices_filter=[];
                    foreach($offices_ar as $office_id=>$is_filtered) {
                        if(!$is_filtered) {
                            continue;
                        }
                        $offices_filter[]=$office_id;
                        $office_name=$this->obooking->office_id2office_name($office_id);
                        ?>
                        <div><span onclick="obooking_orders.toggle_office2filter(<?=$office_id?>)" class="icon-cancel" title="удалить этот фильтр"></span> <?=$office_name?></div>
                    <?}
                    if(count($offices_filter)) {
                        $q_offices_filter = "(orders.office_id=" . implode(" OR orders.office_id=", $offices_filter) . ') AND ';
                    }
                    else {
                        $q_offices_filter = '';
                    }
                    ?></td>
                <td style="white-space: nowrap; cursor: pointer;">
                    <b onclick="obooking_orders.filter_open_trial_dates_list()" class="icon-filter"></b>
                    <b onclick="obooking_orders.setup_sorting('trial_date')">Дата пробного</b>
                    <?php
                    if($field_is_sorted=$this->orders_get_filter_settings_bg->field_is_sorted('trial_date')) {
                        $icon_add=$field_is_sorted["direction"]==="ASC"?"-up":"-down";
                        $sort_order=$field_is_sorted["order"]+1;
                    }
                    else {
                        $sort_order = $icon_add = "";
                    }
                    ?>
                    <b onclick="obooking_orders.setup_sorting('trial_date')">
                        <b class="icon-sort<?=$icon_add?>"></b>
                        <b><?=$sort_order?></b>
                    </b><?php
                    $trial_dates_start=$this->orders_get_filter_settings_bg->get_filtered_trial_dates("start");
                    $trial_dates_end=$this->orders_get_filter_settings_bg->get_filtered_trial_dates("end");
                    if($trial_dates_start||$trial_dates_end) {
                        ?>
                        <div><span onclick="obooking_orders.toggle_trial_date2filter()" class="icon-cancel" title="удалить этот фильтр"></span> <?php
                            if($trial_dates_start) {
                                print 'с ';
                                print date('d.m.Y',$trial_dates_start);
                            }
                            if($trial_dates_start&&$trial_dates_end) {
                                print ' ';
                            }
                            if($trial_dates_end) {
                                print 'по ';
                                print date('d.m.Y',$trial_dates_end);
                            }
                            ?></div>
                        <?php
                        if($trial_dates_start&&!$trial_dates_end) {
                            $q_trial_dates_filter=" trial_date>=".$trial_dates_start." AND ";
                        }
                        elseif(!$trial_dates_start&&$trial_dates_end) {
                            $q_trial_dates_filter=" trial_date<=".($trial_dates_end+86400)." AND ";
                        }
                        else {
                            $q_trial_dates_filter = " (trial_date>=" . $trial_dates_start . " AND trial_date<=" . ($trial_dates_end + 86400) . ") AND ";
                        }
                    }
                    else {
                        $q_trial_dates_filter = "";
                    } ?>
                </td>
                <td style="white-space: nowrap; cursor: pointer;">
                    <b onclick="obooking_orders.setup_sorting('client_name')">Ученик Телефон Email</b>
                    <?php
                    if($field_is_sorted=$this->orders_get_filter_settings_bg->field_is_sorted('client_name')) {
                        $icon_add=$field_is_sorted["direction"]==="ASC"?"-up":"-down";
                        $sort_order=$field_is_sorted["order"]+1;
                    }
                    else {
                        $sort_order = $icon_add = "";
                    }
                    ?>
                    <b onclick="obooking_orders.setup_sorting('client_name')">
                        <b class="icon-sort<?=$icon_add?>"></b>
                        <b><?=$sort_order?></b>
                    </b>
                </td>
                <td style="white-space: nowrap; cursor: pointer;">
                    <b onclick="obooking_orders.filter_open_statuses_list()" class="icon-filter"></b>
                    <b onclick="obooking_orders.setup_sorting('status_name')">Статус</b>
                    <?php
                    if($field_is_sorted=$this->orders_get_filter_settings_bg->field_is_sorted('status_name')) {
                        $icon_add=$field_is_sorted["direction"]==="ASC"?"-up":"-down";
                        $sort_order=$field_is_sorted["order"]+1;
                    }
                    else {
                        $sort_order = $icon_add = "";
                    }
                    ?>
                    <b onclick="obooking_orders.setup_sorting('status_name')">
                        <b class="icon-sort<?=$icon_add?>"></b>
                        <b><?=$sort_order?></b>
                    </b><?php
                    $statuses_ar=$this->orders_get_filter_settings_bg->get_filtered_statuses();
                    $statuses_filter=[];
                    foreach($statuses_ar as $status_id=>$is_filtered) {
                        if(!$is_filtered) {
                            continue;
                        }
                        $statuses_filter[]=$status_id;
                        $status_name=$this->obooking->status_id2status_name($status_id);
                        ?>
                        <div><span onclick="obooking_orders.toggle_status2filter(<?=$status_id?>)" class="icon-cancel" title="удалить этот фильтр"></span> <?=$status_name?></div>
                    <?}
                    if(count($statuses_filter)) {
                        $q_statuses_filter = "(orders.status_id=" . implode(" OR orders.status_id=", $statuses_filter) . ') AND ';
                    }
                    else {
                        $q_statuses_filter = '';
                    }
                    ?></td>
                <td style="white-space: nowrap; cursor: pointer;">
                    <b onclick="obooking_orders.filter_open_courses_list()" class="icon-filter"></b>
                    <b onclick="obooking_orders.setup_sorting('course_name')">Направление</b>
                    <?php
                    if($field_is_sorted=$this->orders_get_filter_settings_bg->field_is_sorted('course_name')) {
                        $icon_add=$field_is_sorted["direction"]==="ASC"?"-up":"-down";
                        $sort_order=$field_is_sorted["order"]+1;
                    }
                    else {
                        $sort_order = $icon_add = "";
                    }
                    ?>
                    <b onclick="obooking_orders.setup_sorting('course_name')">
                        <b class="icon-sort<?=$icon_add?>"></b>
                        <b><?=$sort_order?></b>
                    </b><?php
                    $courses_ar=$this->orders_get_filter_settings_bg->get_filtered_courses();
                    $courses_filter=[];
                    foreach($courses_ar as $course_id=>$is_filtered) {
                        if(!$is_filtered) {
                            continue;
                        }
                        $courses_filter[]=$course_id;
                        $course_name=$this->obooking->course_id2course_name($course_id);
                        ?>
                        <div><span onclick="obooking_orders.toggle_course2filter(<?=$course_id?>)" class="icon-cancel" title="удалить этот фильтр"></span> <?=$course_name?></div>
                    <?}
                    if(count($courses_filter)) {
                        $q_courses_filter = "(oc.course_id=" . implode(" OR oc.course_id=", $courses_filter) . ') AND ';
                    }
                    else {
                        $q_courses_filter = '';
                    }
                    ?></td>
                <td style="white-space: nowrap; cursor: pointer;">
                    <b onclick="obooking_orders.filter_open_managers_list()" class="icon-filter"></b>
                    <b onclick="obooking_orders.setup_sorting('manager_name')">Наставник</b>
                    <?php
                    if($field_is_sorted=$this->orders_get_filter_settings_bg->field_is_sorted('manager_name')) {
                        $icon_add=$field_is_sorted["direction"]==="ASC"?"-up":"-down";
                        $sort_order=$field_is_sorted["order"]+1;
                    }
                    else {
                        $sort_order = $icon_add = "";
                    }
                    ?>
                    <b onclick="obooking_orders.setup_sorting('manager_name')">
                        <b class="icon-sort<?=$icon_add?>"></b>
                        <b><?=$sort_order?></b>
                    </b><?php
                    $managers_ar=$this->orders_get_filter_settings_bg->get_filtered_managers();
                    $managers_filter=[];
                    foreach($managers_ar as $manager_id=>$is_filtered) {
                        if(!$is_filtered) {
                            continue;
                        }
                        $managers_filter[]=$manager_id;
                        $manager_info=$this->obooking->get_manager_info("manager_name,manager_lastname",$manager_id);
                        $manager_name=$manager_info->manager_name;
                        $manager_lastname=$manager_info->manager_lastname;
                        ?>
                        <div><span onclick="obooking_orders.toggle_manager2filter(<?=$manager_id?>)" class="icon-cancel" title="удалить этот фильтр"></span> <?=$manager_name?> <?=$manager_lastname?></div>
                    <?}
                    if(count($managers_filter)) {
                        $q_managers_filter = "(orders.manager_id=" . implode(" OR orders.manager_id=", $managers_filter) . ') AND ';
                    }
                    else {
                        $q_managers_filter = '';
                    }
                    ?></td>
                <td style="white-space: nowrap; cursor: pointer;">
                    <b onclick="obooking_orders.filter_open_sources_list()" class="icon-filter"></b>
                    <b onclick="obooking_orders.setup_sorting('source_name')">Источник</b>
                    <?php
                    if($field_is_sorted=$this->orders_get_filter_settings_bg->field_is_sorted('source_name')) {
                        $icon_add=$field_is_sorted["direction"]==="ASC"?"-up":"-down";
                        $sort_order=$field_is_sorted["order"]+1;
                    }
                    else {
                        $sort_order = $icon_add = "";
                    }
                    ?>
                    <b onclick="obooking_orders.setup_sorting('source_name')">
                        <b class="icon-sort<?=$icon_add?>"></b>
                        <b><?=$sort_order?></b>
                    </b><?php
                    $sources_ar=$this->orders_get_filter_settings_bg->get_filtered_sources();
                    $sources_filter=[];
                    foreach($sources_ar as $source_id=>$is_filtered) {
                        if(!$is_filtered) {
                            continue;
                        }
                        $sources_filter[]=$source_id;
                        $source_name=$this->obooking->source_id2source_name($source_id);
                        ?>
                        <div><span onclick="obooking_orders.toggle_source2filter(<?=$source_id?>)" class="icon-cancel" title="удалить этот фильтр"></span> <?=$source_name?></div>
                    <?}
                    if(count($sources_filter)) {
                        $q_sources_filter = "(orders.source_id=" . implode(" OR orders.source_id=", $sources_filter) . ') AND ';
                    }
                    else {
                        $q_sources_filter = '';
                    }
                    ?></td>
                <td style="white-space: nowrap; cursor: pointer;">
                    <b onclick="obooking_orders.filter_open_how_did_find_outs_list()" class="icon-filter"></b>
                    <b onclick="obooking_orders.setup_sorting('how_did_find_out_name')">Откуда узнали</b>
                    <?php
                    if($field_is_sorted=$this->orders_get_filter_settings_bg->field_is_sorted('how_did_find_out_name')) {
                        $icon_add=$field_is_sorted["direction"]==="ASC"?"-up":"-down";
                        $sort_order=$field_is_sorted["order"]+1;
                    }
                    else {
                        $sort_order = $icon_add = "";
                    }
                    ?>
                    <b onclick="obooking_orders.setup_sorting('how_did_find_out_name')">
                        <b class="icon-sort<?=$icon_add?>"></b>
                        <b><?=$sort_order?></b>
                    </b><?php
                    $how_did_find_outs_ar=$this->orders_get_filter_settings_bg->get_filtered_how_did_find_outs();
                    $how_did_find_outs_filter=[];
                    foreach($how_did_find_outs_ar as $how_did_find_out_id=>$is_filtered) {
                        if(!$is_filtered) {
                            continue;
                        }
                        $how_did_find_outs_filter[]=$how_did_find_out_id;
                        $how_did_find_out_name=$this->obooking->how_did_find_out_id2how_did_find_out_name($how_did_find_out_id);
                        ?>
                        <div><span onclick="obooking_orders.toggle_how_did_find_out2filter(<?=$how_did_find_out_id?>)" class="icon-cancel" title="удалить этот фильтр"></span> <?=$how_did_find_out_name?></div>
                    <?}
                    if(count($how_did_find_outs_filter)) {
                        $q_how_did_find_outs_filter = "(orders.how_did_find_out_id=" . implode(" OR orders.how_did_find_out_id=", $how_did_find_outs_filter) . ') AND ';
                    }
                    else {
                        $q_how_did_find_outs_filter = '';
                    }
                    ?></td>
                <td></td>
            </tr>
            <?php
            $sort_order_ar=$this->orders_get_filter_settings_bg->get_sort_order();
            $order_by_ar=[];
            foreach ($sort_order_ar as $iValue) {
                if(isset($iValue["field"], $iValue["direction"])) {
                    $order_by_ar[] = $iValue["field"] . " " . $iValue["direction"];
                }
            }
            if(count($order_by_ar)) {
                $q_order = implode(",", $order_by_ar);
            }
            else {
                $q_order = "order_id DESC";
            }

            try {
                $stm=$this->uFunc->pdo("obooking")->prepare("SELECT DISTINCT 
                orders.order_id,
                orders.office_id,
                office_name,
                trial_date,
                orders.client_id,
                client_name,
                phone,
                email,
                orders.status_id,
                status_name,
                managers.manager_id,
                manager_name,
                manager_lastname,
                comment,
                orders.source_id,
                source_name,
                orders.how_did_find_out_id,
                how_did_find_out_name,
                c.course_id,
                course_name,
                timestamp,
                next_contact_date,
                sys_status
                FROM 
                orders
                LEFT JOIN order_courses oc on orders.order_id = oc.order_id
                LEFT JOIN courses c on oc.course_id = c.course_id
                LEFT JOIN order_managers on orders.order_id = order_managers.order_id
                LEFT JOIN managers ON order_managers.manager_id=managers.manager_id
                LEFT JOIN order_statuses ON orders.status_id=order_statuses.status_id AND orders.site_id=order_statuses.site_id
                LEFT JOIN order_sources ON orders.source_id=order_sources.source_id AND orders.site_id=order_sources.site_id
                LEFT JOIN order_how_did_find_outs ON orders.how_did_find_out_id=order_how_did_find_outs.how_did_find_out_id AND orders.site_id=order_how_did_find_outs.site_id
                LEFT JOIN offices ON orders.office_id=offices.office_id AND orders.site_id=offices.site_id
                WHERE 
                orders.site_id=:site_id AND
                $q_offices_filter
                $q_timestamps_filter
                $q_next_contact_dates_filter
                $q_trial_dates_filter
                $q_statuses_filter
                $q_managers_filter
                $q_sources_filter
                $q_how_did_find_outs_filter
                $q_courses_filter
                sys_status>0
                ORDER BY 
                $q_order
                ");
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('1580863511'/*.$e->getMessage()*/,1);}

            $ordersAr=[];
            $order_id2coursesAr=[];
            $order_id2managersAr=[];
            /** @noinspection PhpUndefinedVariableInspection */
            while($order=$stm->fetch(PDO::FETCH_OBJ)) {
                $order->order_id=(int)$order->order_id;

                if(!array_key_exists($order->order_id,$order_id2coursesAr)) {
                    $order_id2coursesAr[$order->order_id]=[];
                    if(!array_key_exists($order->order_id,$order_id2managersAr)) {
                        $order_id2managersAr[$order->order_id]=[];
                        $ordersAr[]=$order;
                    }
                }

                $order_id2coursesAr[$order->order_id][]=array(
                    'course_id'=>(int)$order->course_id,
                    'course_name'=>$order->course_name
                );

                $order_id2managersAr[$order->order_id][]=array(
                    'manager_id'=>(int)$order->manager_id,
                    'manager_name'=>$order->manager_name,
                    'manager_lastname'=>$order->manager_lastname
                );
            }

            foreach ($ordersAr as $iValue) {
                $order= $iValue;

                $order->trial_date=(int)$order->trial_date;
                if($order->trial_date) {
                    $trial_date = date('d.m.Y', $order->trial_date);
                }
                else {
                    $trial_date = "";
                }

                $order->timestamp=(int)$order->timestamp;
                if($order->timestamp) {
                    $timestamp = date('d.m.Y', $order->timestamp);
                }
                else {
                    $timestamp = "";
                }

                $order->next_contact_date=(int)$order->next_contact_date;
                if($order->next_contact_date) {
                    $next_contact_date = date('d.m.Y', $order->next_contact_date);
                }
                else {
                    $next_contact_date = "";
                }

                $order->sys_status=(int)$order->sys_status;

                ?>
                <tr id="obooking_order_row_<?=$order->order_id?>" class="obooking_order_row <?=$order->sys_status===1?'bg-primary':''?> ">
                    <td onclick="obooking_inline_edit.edit_order_init(<?=$order->order_id?>)" class="order_timestamp" data-timestamp="<?=$order->timestamp?>"><?=$timestamp?></td>
                    <td onclick="obooking_inline_edit.edit_order_init(<?=$order->order_id?>)" class="order_next_contact_date" data-next_contact_date="<?=$order->next_contact_date?>"><?=$next_contact_date?></td>
                    <td onclick="obooking_inline_edit.edit_order_init(<?=$order->order_id?>)" class="order_office_id" data-office_id="<?=$order->office_id?>"><?=$order->office_name?></td>
                    <td onclick="obooking_inline_edit.edit_order_init(<?=$order->order_id?>)" class="order_trial_date" data-trial_date_timestamp="<?=$order->trial_date?>"><?=$trial_date?></td>
                <td onclick="obooking_inline_edit.edit_order_init(<?=$order->order_id?>)">
                    <em style="font-style: normal" class="order_client_name"><?php
                        $client_id=(int)$order->client_id;
                        if($client_id) {
                            print "<a title='Открыть карточку ученика' href='/obooking/clients/$client_id' target='_blank'><span class='icon-users'></span></a>&nbsp;";
                        }

                        print $order->client_name;
                        ?></em><br>
                    <em style="font-style: normal" class="order_phone"><?php
                        if(uString::isPhone($order->phone)) {
                            print '<a title="Позвонить" href="tel:'.$order->phone.'"><span class="icon-phone-1"></span></a> ';
                        }
                        print $order->phone;
                        ?></em><br>
                    <em style="font-style: normal" class="order_email"><?php
                        if(uString::isEmail($order->email)) {
                            print '<a title="Отправить email" href="mailto:'.$order->email.'"><span class="icon-mail-alt"></span></a> ';
                        }

                        print $order->email;
                        ?></em>
                </td>
                    <td onclick="obooking_inline_edit.edit_order_init(<?=$order->order_id?>)" class="order_status" data-order_status="<?=$order->status_id?>"><?=$order->status_name?></td>
                    <td onclick="obooking_inline_edit.edit_order_init(<?=$order->order_id?>)" class="order_course" id="obooking_order_row_<?=$order->order_id?>_courses"><?php
                        if(array_key_exists($order->order_id,$order_id2coursesAr)) {
                            $coursesAr = $order_id2coursesAr[$order->order_id];
                            $coursesArCount = count($coursesAr);
                            for ($j = 0; $j < $coursesArCount-1; $j++) {
                                $course=$coursesAr[$j];
                                print $course["course_name"];
                                print ', ';
                            }
                            $course=$coursesAr[$j];
                            print $course["course_name"];
                        }
                        ?></td>
                    <td onclick="obooking_inline_edit.edit_order_init(<?=$order->order_id?>)" class="order_manager_id" data-manager_id="<?=$order->manager_id?>"  id="obooking_order_row_<?=$order->order_id?>_managers"><?php
                        if(array_key_exists($order->order_id,$order_id2managersAr)) {
                            $managersAr = $order_id2managersAr[$order->order_id];
                            $managersArCount = count($managersAr);
                            for ($j = 0; $j < $managersArCount-1; $j++) {
                                $manager=$managersAr[$j];
                                print $manager["manager_name"];
                                print ' ';
                                print $manager["manager_lastname"];
                                print ', ';
                            }
                            $manager=$managersAr[$j];
                            print $manager["manager_name"];
                            print ' ';
                            print $manager["manager_lastname"];
                        }
                        ?></td>
                    <td onclick="obooking_inline_edit.edit_order_init(<?=$order->order_id?>)" class="order_source" data-source_id="<?=$order->source_id?>"><?=$order->source_name?></td>
                    <td onclick="obooking_inline_edit.edit_order_init(<?=$order->order_id?>)" class="order_how_did_find_out" data-how_did_find_out_id="<?=$order->how_did_find_out_id?>"><?=$order->how_did_find_out_name?></td>
                    <td><!--<div class="obooking_orders_buttons"><button type="button" class="btn btn-danger btn-xs" title="Удалить ученика" onclick="obooking_inline_edit.delete_order_confirm(<?/*=$order->order_id*/?>)"><span class="icon-cancel"></span></button></div>--></td>
<!--                </tr>-->
                <?if($order->comment!=="") {?>
                    <tr  class="<?=$order->sys_status===1?'bg-primary':''?>">
                        <td style="border-top:none" colspan="10" onclick="obooking_inline_edit.edit_order_init(<?=$order->order_id?>)" class="order_comment">
                            <?=nl2br($order->comment)?>
                        </td>
                    </tr>
                <?}?>
            <?} ?>
        </table>
    <?}

    public function __construct (&$uCore) {
        $uSes=new uSes($uCore);
        if(!$uSes->access(2)) {
            print 'forbidden';
            exit;
        }
        $this->obooking=new common($uCore);
        $user_id=(int)$uSes->get_val('user_id');
        $is_admin=$this->obooking->is_admin($user_id);

        if(!$is_admin) {
            print 'forbidden';
            exit;
        }

        $this->uFunc=new uFunc($uCore);

        $this->orders_get_filter_settings_bg=new orders_get_filter_settings_bg($uCore);
        $this->orders_list();
    }
}
new get_orders_bg($this);
