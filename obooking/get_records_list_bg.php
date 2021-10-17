<?php
namespace obooking;
use DateTime;
use PDO;
use PDOException;
use processors\uFunc;
use stdClass;
use uSes;
use uString;

require_once "processors/uSes.php";
require_once 'processors/classes/uFunc.php';
require_once 'obooking/classes/common.php';

class get_records_list_bg{
    private $day_of_week;
    private $timestamp_start;
    private $day_shift_num;
    private $day_timestamp;
    private $office_name;
    private $office_id;
    private $obooking;
    private $uFunc;
    private function check_data() {
        if(!isset($_POST['office_id'],$_POST['date'],$_POST['shift'])) {
            $this->uFunc->error(10);
        }
        $shift=(int)$_POST['shift'];
        $this->day_timestamp=$_POST['date'];
        if(!uString::isDigits($this->day_timestamp)) {
            $format = 'd.m.Y H:i';
            $dateobj = DateTime::createFromFormat($format, $this->day_timestamp.' 00:00');
            $iso_datetime = $dateobj->format(Datetime::ATOM);
            $this->day_timestamp=strtotime($iso_datetime);
        }
        $this->day_timestamp+=$shift*86400;

        $today_midnight_timestamp=date('d.m.Y');
        $format = 'd.m.Y H:i';
        $dateobj = DateTime::createFromFormat($format, $today_midnight_timestamp.' 00:00');
        $iso_datetime = $dateobj->format(Datetime::ATOM);
        $today_midnight_timestamp=strtotime($iso_datetime);

        $this->day_shift_num=($today_midnight_timestamp-$this->day_timestamp)/86400;

        $this->office_id=(int)$_POST['office_id'];
        if(!$office=$this->obooking->get_office_info('office_name',$this->office_id)) {
            $this->uFunc->error(20);
        }
        $this->office_name=$office->office_name;
    }
    private function get_records($class_id, $date= '01.01.1970', $site_id=site_id) {
        $format = 'd.m.Y H:i';
        $dateobj = DateTime::createFromFormat($format, date('d.m.Y',$date).' 00:00');
        $iso_datetime = $dateobj->format(Datetime::ATOM);
        $this->timestamp_start=strtotime($iso_datetime);
        $timestamp_end=$this->timestamp_start+86400;

        try {
            $stm=$this->uFunc->pdo('obooking')->prepare('
            SELECT 
            rec_id,
            rec_type,
            class_id,
            manager_id,
            timestamp,
            duration,
            notes,
            price,
            payment_status
            FROM 
            records
            WHERE
            class_id=:class_id AND
            timestamp>=:timestamp_start AND
            timestamp<:timestamp_end AND
            site_id=:site_id
            ');
            $stm->bindParam(':class_id', $class_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->bindParam(':timestamp_start', $this->timestamp_start,PDO::PARAM_INT);
            $stm->bindParam(':timestamp_end', $timestamp_end,PDO::PARAM_INT);
            $stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('1586901652'.$e->getMessage());}
        return false;
    }

    private function show_records() {
        $records_ar=[];
        $records_id2info=[];
        ?>
        <h3>
            <a href="javascript:void(0)" onclick="obooking_calendar.office_selector_init();" id="obooking_calendar_office_selector"><?=$this->office_name?></a> <button type="button" class="btn btn-primary btn-sm" onclick="obooking_calendar.new_record_init()"><span class="icon-plus"></span> Создать запись</button>
            <a href="javascript:void(0);" onclick="obooking_calendar.shift_date(-1)"><span class="icon-left-open"></span></a>
            <a href="javascript:void(0);" id="obooking_calendar_date_selector"><?=$this->obooking->day_of_week_number2word(date('w',$this->day_timestamp)).', '.date('d.m.Y', $this->day_timestamp)?></a>
            <a href="javascript:void(0);" onclick="obooking_calendar.shift_date(1)"><span class="icon-right-open"></span></a>
            <?if($this->day_shift_num) {?>
            <a href="javascript:void(0);" onclick="obooking_calendar.shift_date(<?=$this->day_shift_num?>)">
                <button class="btn btn-primary btn-sm">К сегодня</button>
            </a>
            <?}
            else {?>
                сегодня
            <?}?>
        </h3>
        <table class="table table-condensed table-striped table-bordered">
            <?php
            $q_classes=$this->obooking->get_classes($this->office_id, 'class_id,class_name');
            $classes_ar=$q_classes->fetchAll(PDO::FETCH_OBJ);
            ?>
            <tr>
                <th></th>
                <?php
                foreach ($classes_ar as $iValue) {
                    $class_id= $iValue->class_id;
                    $q_records=$this->get_records($class_id,$this->day_timestamp);
                    while($record=$q_records->fetch(PDO::FETCH_OBJ)) {
                        $record_hours=date('H',$record->timestamp);
                        $record_minutes=date('i',$record->timestamp);
                        if($record_minutes>0&&$record_minutes<15/*30*/) {
                            $record_minutes = 0;
                        }
                        elseif($record_minutes>15/*30*/&&$record_minutes<30) {
                            $record_minutes = 15/*30*/
                            ;
                        }
                        elseif($record_minutes>30/*30*/&&$record_minutes<45) {
                            $record_minutes = 30/*30*/
                            ;
                        }
                        elseif($record_minutes>45/*30*/) {
                            $record_minutes = 45/*30*/
                            ;
                        }
                        $rec_id=$record->rec_id;
                        $records_ar[$class_id.'-'.$record_hours.'-'.$record_minutes]=$rec_id;
                        $records_ar_duration_limiter[$class_id.'-'.$record_hours.'-'.$record_minutes]=1;
                        for($duration=(int)$record->duration-900/*1800*/;$duration>0;$duration-=900/*1800*/) {
                            $record_minutes+=15/*30*/;
                            if($record_minutes>45/*30*/) {
                                $record_minutes=0;
                                ++$record_hours;
                            }
                            if($record_minutes<10) {
                                $record_minutes = '0' . $record_minutes;
                            }
                            if($record_hours<10) {
                                $record_hours = '0' . $record_hours;
                            }
                            $records_ar[$class_id.'-'.$record_hours.'-'.$record_minutes]=$rec_id;
                            $records_ar_duration_limiter[$class_id.'-'.$record_hours.'-'.$record_minutes]=0;
                        }
                        $records_id2info[$rec_id]=$record;
                    }
                    ?>
                    <th><?= $iValue->class_name?><br><?php
                        $this->day_of_week=date('w',$this->timestamp_start);
                        $this->day_of_week--;
                        if($this->day_of_week<0) {
                            $this->day_of_week = 6;
                        }

                        $stm_class_managers=$this->obooking->get_managers_of_class_for_day($class_id,$this->day_of_week);
                        while($manager=$stm_class_managers->fetch(PDO::FETCH_OBJ)) {
                            print $manager->manager_name;
                            print ' ';
                            print $manager->manager_lastname;
                        }
                        ?></th>
                <?} ?>
                <th></th>
            </tr>
            <?php
            $manager_number=[];
            $prev_manager_id4class=[];
            for($i=9;$i<23;$i++) {
                if($i<10) {
                    $i_str = '0' . $i;
                }
                else {
                    $i_str = $i;
                }

                for($j=0;$j<60;$j+=15/*30*/) {
                    if($j<10) {
                        $j_str = '0' . $j;
                    }
                    else {
                        $j_str = $j;
                    } ?>
                    <tr>
                        <td <?php
                        if($j_str=== '30') {
                            echo "class='text-muted'";
                        }
                        ?>><?php
                            if($j_str=== '00'/*||$j_str=="30"*/) {
                                echo $i_str . " <small class='text-muted'><sup>" . $j_str . '</sup></small>';
                            }
                            ?></td>
                        <?php foreach ($classes_ar as $kValue) {
                            unset($cur_rec);

                            $class= $kValue;
                            if(!isset($prev_manager_id4class[$class->class_id])) {
                                $prev_manager_id4class[$class->class_id] = 0;
                            }
                            if(!isset($manager_number[$class->class_id])) {
                                $manager_number[$class->class_id] = 0;
                            }
                            $manager4hour=$this->obooking->get_managers_of_class_for_day_for_hour($class->class_id,$this->day_of_week,$i);
                            $cur_rec_id=0;
//                            $duration_rows=1;
                            $records_duration_limiter_status=1;
//                            $rec_type="vacant";
                            if(isset($records_ar[$class->class_id.'-'.$i_str.'-'.$j_str])) {
                                /** @noinspection PhpUndefinedVariableInspection */
                                $records_duration_limiter_status=$records_ar_duration_limiter[$class->class_id.'-'.$i_str.'-'.$j_str];
                                if($records_duration_limiter_status) {
                                    $cur_rec_id=$records_ar[$class->class_id.'-'.$i_str.'-'.$j_str];
                                    $cur_rec=$records_id2info[$cur_rec_id];
                                    $manager_id = (int)$cur_rec->manager_id;
                                    $rec_type = (int)$cur_rec->rec_type;
                                    $timestamp = (int)$cur_rec->timestamp;
                                    $duration = (int)$cur_rec->duration;
                                    $payment_status = (int)$cur_rec->payment_status;
                                    $duration_rows = $duration / 60 / 15/*30*/;
                                    $manager_info = $this->obooking->get_manager_info('manager_name, manager_lastname', $manager_id);
                                    $rec_info = $this->obooking->get_rec_type_info('rec_type_name', $rec_type);
                                }
                            }
                            if($records_duration_limiter_status) {?>
                            <td
                                    data-rec_id="<?=$cur_rec_id?>"
                                <?php
                                if(isset($cur_rec)) {?>
                                    rowspan="<?= /** @noinspection PhpUndefinedVariableInspection */$duration_rows?>"
                                    class="rec_type_<?= /** @noinspection PhpUndefinedVariableInspection */$rec_type?> <?php
                                    $time=time();
                                    if($timestamp>=$time&&$timestamp+$duration<$time) {
                                        print ' rec_type_current ';
                                    }
                                    /** @noinspection PhpStatementHasEmptyBodyInspection */
                                    if($timestamp+$duration<=time()&&!$payment_status) {
//                                        print " rec_type_unpaid ";//TODO-nik87 сделать, чтобы неоплаченные занятия отмечались правильно
                                    }
                                    print " $timestamp ";
                                    print ' ' .time(). ' ';
                                    print " $duration ";
                                    print " $payment_status ";
                                    ?>"
                                    onclick="obooking_calendar.edit_record_init(this)"
                                    data-duration="<?=$duration?>"
                                <?}
                                else {
                                    if($manager4hour) {
                                        $tell_manager_name=0;
                                        $manager4hour->manager_id=(int)$manager4hour->manager_id;
                                        if($prev_manager_id4class[$class->class_id]!==$manager4hour->manager_id) {
                                            $prev_manager_id4class[$class->class_id]=$manager4hour->manager_id;
                                            $manager_number[$class->class_id]=1-$manager_number[$class->class_id];
                                            $tell_manager_name=1;
                                        }?>
                                        data-manager_id="<?=$manager4hour->manager_id?>"
                                        data-manager_name="<?=rawurlencode($manager4hour->manager_name)?>"
                                        data-manager_lastname="<?=rawurlencode($manager4hour->manager_lastname)?>"
                                        data-manager_phone="<?=rawurlencode($manager4hour->manager_phone)?>"
                                        data-manager_email="<?=rawurlencode($manager4hour->manager_email)?>"
                                    <?}
                                    else {?>
                                        data-manager_id="0"
                                    <?}?>
                                    class="vacant <?php
                                    if($manager4hour) {
                                        print 'cell_with_manager_' . $manager_number[$class->class_id];
                                    }
                                    ?>"
                                    onclick="obooking_calendar.new_record_init(this)"
                                    data-time="<?=$i_str?>:<?=$j_str?>"
                                    data-class_id="<?=$class->class_id?>"
                                    data-class_name="<?=rawurlencode($class->class_name)?>"
                                    data-office_id="<?=$this->office_id?>"
                                    data-office_name="<?=rawurlencode($this->office_name)?>"
                                <?}
                                ?>
                            >
                                <?if(isset($cur_rec)) {
                                    if(!isset($rec_info)) {
                                        $rec_info = new stdClass();
                                    }
//                                    if(!isset($client_info)) {
//                                        $client_info = new stdClass();
//                                    }

                                    print $rec_info->rec_type_name;
                                    print ' <span class="icon-rouble"></span>';
                                    print $cur_rec->price;

                                    print '<br>';
                                    print '<span class="icon-user-secret"></span>'; print $manager_info->manager_name; print ' '; print $manager_info->manager_lastname;
                                    print '<div style="border-bottom:1px solid black; margin-bottom:2px; margin-top:2px;"></div>';

                                    $clients_stm=$this->obooking->get_rec_clients_info('records_clients.client_id AS client_id,client_name,client_lastname,client_balance,client_phone',$cur_rec_id);
                                    while($client_info=$clients_stm->fetch(PDO::FETCH_OBJ)) {
                                        print ' <span class="icon-user"></span>';
                                        print $client_info->client_name;
                                        print ' ';
                                        print $client_info->client_lastname;
                                        print ' <span class="icon-rouble"></span>';
                                        print $client_info->client_balance;
                                        if($client_info->client_phone!== '') {
                                            print ' <span class="icon-phone-1"></span>';
                                            print $client_info->client_phone;
                                        }
                                        if($card_data=$this->obooking->get_client_longest_card($client_info->client_id)) {
                                            print ' &nbsp;';
                                            print '<span class="icon-credit-card"></span> ';
//                                            print $this->obooking->card_type_id2name($card_data->card_type_id);
//                                            print ' <b>№</b> ';
//                                            print $card_data->card_number;
                                            if($card_data->valid_thru<time()) {
                                                print ' <span class="bg-danger"> ';
                                            }
//                                            print ' <span class="icon-clock"></span>';
//                                            print date('d.m.Y', $card_data->start_date);
//                                            print '-';
                                            print date('d.m.Y', $card_data->valid_thru);
                                            if($card_data->valid_thru<time()) {
                                                print ' - Карта просрочена!</span>';
                                            }
                                        }
                                        print '<br>';
                                    }
                                    if($cur_rec->notes!== '') {
                                        print '<hr>';
                                        print nl2br($cur_rec->notes);
                                    }
                                }
                                elseif($manager4hour) {
                                    /** @noinspection PhpUndefinedVariableInspection */
                                    if($tell_manager_name) {
                                        print $manager4hour->manager_name;
                                        print ' ';
                                        print $manager4hour->manager_lastname;
                                        print '.&nbsp;';
                                    }
                                }

                                /*if(isset($cur_rec)) {
                                    if ($timestamp + $duration <= time() && !$payment_status) {
                                        print '<div>
                                            <p></p>
                                            <button class="btn btn-default btn-outline">Оплата</button>
                                        </div>';
                                    }
                                }*/
                                unset($cur_rec);
                                ?></td>
                            <?}?>
                        <?} ?>
                        <!--<td <?php
                        if($j_str=== '30') {
                            echo "class='text-muted'";
                        }
                        ?>><?php
                            if($j_str=== '00' ||$j_str=== '30') {
                                echo "$i_str <small class='text-muted'><sup>$j_str</sup></small>";
                            }
                            ?></td>-->
                    </tr>
                <?}?>
            <?}?>
        </table>
        <script type="text/javascript">
            obooking_calendar.date=<?=$this->day_timestamp?>;
        </script>
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

        $this->check_data();
        $this->show_records();
    }
}
new get_records_list_bg($this);
