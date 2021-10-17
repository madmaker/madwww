<?php
namespace obooking;
use DateTime;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "obooking/classes/common.php";

class calendar_demo {
    public $obooking;
    public $office_name;
    public $office_id;
    public $offices_ar;
    /**
     * @var uSes
     */
    public $uSes;
    /**
     * @var false|string
     */
    private $day_of_week;
    /**
     * @var false|int
     */
    private $timestamp_start;
    private $uFunc;
    private $uCore;
    private $day_timestamp;
    private $day_shift_num;

    private function check_data() {
        if(isset($_GET["office"])) $this->office_id=(int)$_GET["office"];
        else $this->office_id=$this->obooking->get_first_office_id();

        $this->day_timestamp=time();

        $today_midnight_timestamp=date('d.m.Y');
        $format = "d.m.Y H:i";
        $dateobj = DateTime::createFromFormat($format, $today_midnight_timestamp.' 00:00');
        $iso_datetime = $dateobj->format(Datetime::ATOM);
        $today_midnight_timestamp=strtotime($iso_datetime);

        $this->day_shift_num=($today_midnight_timestamp-$this->day_timestamp)/86400;

        if(!$office=$this->obooking->get_office_info("office_name",$this->office_id)) $this->uFunc->error(20);
        $this->office_name=$office->office_name;
    }

    private function get_offices_list() {
        $q_offices=$this->obooking->get_offices();
        /** @noinspection PhpUndefinedMethodInspection */
        $this->offices_ar=$q_offices->fetchAll(PDO::FETCH_OBJ);
    }
    private function get_records($class_id,$date="01.01.1970",$site_id=site_id) {
        $format = "d.m.Y H:i";
        $dateobj = DateTime::createFromFormat($format, date("d.m.Y",$date).' 00:00');
        $iso_datetime = $dateobj->format(Datetime::ATOM);
        $this->timestamp_start=strtotime($iso_datetime);
        $timestamp_end=$this->timestamp_start+86400;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("
            SELECT 
            rec_id,
            rec_type,
            class_id,
            client_id,
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
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':class_id', $class_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':timestamp_start', $this->timestamp_start,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':timestamp_end', $timestamp_end,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('30'.$e->getMessage());}
        return false;
    }

    public function show_records() {

        $q_classes = $this->obooking->get_classes($this->office_id, "class_id,class_name");
        /** @noinspection PhpUndefinedMethodInspection */
        $classes_ar = $q_classes->fetchAll(PDO::FETCH_OBJ);

        $this->day_timestamp-=86400;
        for ($dd = 0; $dd < 7; $dd++) {
            $this->day_timestamp+=86400;
            $records_ar = [];
            $records_id2info = [];
            ?>
            <h4>
                <?= $this->obooking->day_of_week_number2word(date('w', $this->day_timestamp)) . ', ' . date('d.m.Y', $this->day_timestamp) ?>
            </h4>
            <table class="table table-condensed table-striped table-bordered">
                <tr>
                    <th style="width: 70px"></th>
                    <?
                    $classes_number = count($classes_ar);
                    for ($i = 0; $i < $classes_number; $i++) {
                        $class_id = $classes_ar[$i]->class_id;
                        $q_records = $this->get_records($class_id, $this->day_timestamp);
                        /** @noinspection PhpUndefinedMethodInspection */
                        while ($record = $q_records->fetch(PDO::FETCH_OBJ)) {
                            $record_hours = date('H', $record->timestamp);
                            $record_minutes = date('i', $record->timestamp);
                            if ($record_minutes > 0 && $record_minutes < 15/*30*/) $record_minutes = 0;
                            elseif ($record_minutes > 15/*30*/ && $record_minutes < 30) $record_minutes = 15/*30*/
                            ;
                            elseif ($record_minutes > 30/*30*/ && $record_minutes < 45) $record_minutes = 30/*30*/
                            ;
                            elseif ($record_minutes > 45/*30*/) $record_minutes = 45/*30*/
                            ;
                            $rec_id = $record->rec_id;
                            $records_ar[$class_id . '-' . $record_hours . '-' . $record_minutes] = $rec_id;
                            $records_ar_duration_limiter[$class_id . '-' . $record_hours . '-' . $record_minutes] = 1;
                            for ($duration = (int)$record->duration - 900/*1800*/; $duration > 0; $duration -= 900/*1800*/) {
                                $record_minutes += 15/*30*/
                                ;
                                if ($record_minutes > 45/*30*/) {
                                    $record_minutes = 0;
                                    $record_hours += 1;
                                }
                                if ($record_minutes < 10) $record_minutes = '0' . $record_minutes;
                                if ($record_hours < 10) $record_hours = '0' . $record_hours;
                                $records_ar[$class_id . '-' . $record_hours . '-' . $record_minutes] = $rec_id;
                                $records_ar_duration_limiter[$class_id . '-' . $record_hours . '-' . $record_minutes] = 0;
                            }
                            $records_id2info[$rec_id] = $record;
                        }
                        ?>
                        <th style="width: 40%"><?= $classes_ar[$i]->class_name ?><br><?php
                            $this->day_of_week = date("w", $this->timestamp_start);
                            $this->day_of_week--;
                            if ($this->day_of_week < 0) $this->day_of_week = 6;

                            $stm_class_managers = $this->obooking->get_managers_of_class_for_day($class_id, $this->day_of_week);
                            /** @noinspection PhpUndefinedMethodInspection */
                            while ($manager = $stm_class_managers->fetch(PDO::FETCH_OBJ)) {
                                print $manager->manager_name;
                                print " ";
                                print $manager->manager_lastname;
                                print ". ";
                            }
                            ?></th>
                    <?
                    } ?>
                </tr>
                <?
                $manager_number = [];
                $prev_manager_id4class = [];
                for ($i = 11; $i < 22; $i++) {
                    if ($i < 10) $i_str = '0' . $i;
                    else $i_str = $i;

                    for ($j = 0; $j < 60; $j += 15) {
                        if ($j < 10) $j_str = '0' . $j;
                        else $j_str = $j; ?>
                        <tr>
                            <td style="padding: 0 2px; text-align: center;" <?
                            if ($j_str == "30") echo "class='text-muted'";
                            ?>><?
                                if ($j_str == "00"/*||$j_str=="30"*/) echo $i_str . " <small class='text-muted'><sup>" . $j_str . "</sup></small>";
                                ?></td>
                            <?
                            for ($k = 0; $k < $classes_number; $k++) {
                                unset($cur_rec);

                                $class = $classes_ar[$k];
                                if (!isset($prev_manager_id4class[$class->class_id])) $prev_manager_id4class[$class->class_id] = 0;
                                if (!isset($manager_number[$class->class_id])) $manager_number[$class->class_id] = 0;
                                $manager4hour = $this->obooking->get_managers_of_class_for_day_for_hour($class->class_id, $this->day_of_week, $i);
                                $cur_rec_id = 0;
                                $records_duration_limiter_status = 1;
                                if (isset($records_ar[$class->class_id . '-' . $i_str . '-' . $j_str])) {
                                    /** @noinspection PhpUndefinedVariableInspection */
                                    $records_duration_limiter_status = $records_ar_duration_limiter[$class->class_id . '-' . $i_str . '-' . $j_str];
                                    if ($records_duration_limiter_status) {
                                        $cur_rec_id = $records_ar[$class->class_id . '-' . $i_str . '-' . $j_str];
                                        $cur_rec = $records_id2info[$cur_rec_id];
                                        $manager_id = (int)$cur_rec->manager_id;
                                        $rec_type = (int)$cur_rec->rec_type;
                                        $duration = (int)$cur_rec->duration;
                                        $duration_rows = $duration / 60 / 15/*30*/
                                        ;
                                        $manager_info = $this->obooking->get_manager_info("manager_name", $manager_id);
                                        $rec_info = $this->obooking->get_rec_type_info("rec_type_name", $rec_type);
                                    }
                                }
                                if ($records_duration_limiter_status) { ?>
                                    <td
                                            data-rec_id="<?= $cur_rec_id ?>"
                                        <?
                                        if (isset($cur_rec)) { ?>
                                            rowspan="<?= /** @noinspection PhpUndefinedVariableInspection */
                                            $duration_rows ?>"
                                            class="rec_type_1"
                                        <?
                                        } ?>
                                    >
                                        <?
                                        if (isset($cur_rec)) {
                                            if (!isset($rec_info)) $rec_info = new \stdClass();
                                            if (!isset($client_info)) $client_info = new \stdClass();

                                            print $rec_info->rec_type_name;
//                                            print ' <span class="icon-rouble"></span>';
//                                            print $cur_rec->price;

                                            print '<br>';
                                            print '<span class="icon-user-secret"></span> Наставник: ';
                                            print $manager_info->manager_name;
//                                            print '<div style="border-bottom:1px solid black; margin-bottom:2px; margin-top:2px;"></div>';

                                            /** @noinspection PhpUndefinedMethodInspection */
//                                            if ($cur_rec->notes !== "") {
//                                                print "<hr>";
//                                                print nl2br($cur_rec->notes);
//                                            }
                                            /** @noinspection PhpUndefinedVariableInspection */
                                        } elseif ($manager4hour) {
                                            /** @noinspection PhpUndefinedVariableInspection */
                                            if ($tell_manager_name) {
                                                print $manager4hour->manager_name;
                                                print " ";
                                                print $manager4hour->manager_lastname;
                                                print ".&nbsp;";
                                            }
                                        }

                                        unset($cur_rec);
                                        ?></td>
                                <?
                                } ?>
                            <?
                            } ?>
                        </tr>
                    <?
                    } ?>
                <?
                } ?>
            </table>
            <script type="text/javascript">
                obooking_calendar.date =<?=$this->day_timestamp?>;
            </script>
        <?
        }
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();

        $this->uSes=new uSes($this->uCore);
        $this->uFunc=new uFunc($this->uCore);

            $this->obooking=new common($this->uCore);

            $this->check_data();
            $this->get_offices_list();
            $this->uFunc->incJs(staticcontent_url.'js/translator/translator.min.js');
            $this->uFunc->incJs(staticcontent_url.'js/obooking/inline_create.min.js');
            $this->uFunc->incCss(staticcontent_url.'css/obooking/common.min.css');
            $this->uFunc->incCss(staticcontent_url.'css/obooking/calendar.min.css');
            $this->uFunc->incCss(staticcontent_url.'css/obooking/calendar_demo.min.css');

            $this->uCore->page['page_width'] = 1;
    }
}
$obooking=new calendar_demo($this);
ob_start();

    $today_midnight_timestamp = date('d.m.Y');
    $format = "d.m.Y H:i";
    $dateobj = DateTime::createFromFormat($format, $today_midnight_timestamp . ' 00:00');
    $iso_datetime = $dateobj->format(Datetime::ATOM);
    $today_midnight_timestamp = strtotime($iso_datetime);
    ?>
    <script type="text/javascript">
        if (typeof obooking_calendar === "undefined") obooking_calendar = {};
        obooking_calendar.date =<?=$today_midnight_timestamp?>;

        obooking_calendar.office_id =<?=$obooking->office_id?>;

        <?
        $offices_options = "";
        $offices_number = count($obooking->offices_ar);
        for ($i = 0; $i < $offices_number; $i++) {
            $offices_options .= '<option value="' . $obooking->offices_ar[$i]->office_id . '">' . $obooking->offices_ar[$i]->office_name . '</option>';
        }
        ?>

        obooking_calendar.offices_options = decodeURIComponent("<?=rawurlencode($offices_options)?>");
        <?if(isset($_GET["create"])){?>
        obooking_calendar.create_after_page_load = 1;
        <?}

        ?>
    </script>

    <div id="obooking">
        <div id="obooking_calendar_container">
            <?$obooking->show_records();?>
        </div>
    </div>

    <?

$this->page_content=ob_get_contents();
ob_end_clean();

include 'templates/template.php';
