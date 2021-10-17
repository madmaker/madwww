<?php
namespace obooking;
use DateTime;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;
use uString;

require_once "processors/uSes.php";
require_once 'processors/classes/uFunc.php';
require_once 'obooking/classes/common.php';

class save_record
{
    /**
     * @var int
     */
    private $rec_id;
    /**
     * @var float|int
     */
    private $rec_duration;
    /**
     * @var false|int
     */
    private $rec_timestamp;
    private $obooking;
    private $uFunc;
    private function check_data()
    {
        if (
            !isset(
                $_POST['rec_id'],
                $_POST['time'],
                $_POST['duration'],
                $_POST['class_id'],
                $_POST['record_date'],
                $_POST['client'],
                $_POST['manager_id'],
                $_POST['type'],
                $_POST['notes'],
                $_POST['price'],
                $_POST['price_without_card']
            )
        ) {
            $this->uFunc->error(10);
        }

        $_POST['notes'] = trim($_POST['notes']);
        $_POST['price'] = (float) $_POST['price'];
        $_POST['price_without_card'] = (float) $_POST['price_without_card'];

        $_POST['time'] = trim($_POST['time']);
        $_POST['duration'] = trim($_POST['duration']);

        if ($_POST['time'] === '') {
            echo json_encode([
                'status' => 'error',
                'msg' => 'time is empty',
            ]);
            exit();
        }
        if ($_POST['duration'] === '') {
            echo json_encode([
                'status' => 'error',
                'msg' => 'duration is empty',
            ]);
            exit();
        }
        if (!uString::isTime($_POST['time'])) {
            echo json_encode([
                'status' => 'error',
                'msg' => 'time is wrong',
            ]);
            exit();
        }
        if (!uString::isTime($_POST['duration'])) {
            echo json_encode([
                'status' => 'error',
                'msg' => 'duration is wrong',
            ]);
            exit();
        }

        if (!uString::isDigits($_POST['class_id'])) {
            $this->uFunc->error(20);
        }
        if (
            !$this->obooking->get_class_info(
                'class_id',
                $_POST['class_id'],
                site_id
            )
        ) {
            $this->uFunc->error(30);
        }
        if (!uString::isDate($_POST['record_date'])) {
            $this->uFunc->error(40);
        }
        if ($_POST['client'] === '') {
            $this->uFunc->error(50);
        }
        if (!uString::isDigits($_POST['manager_id'])) {
            $this->uFunc->error(60);
        }
        if (
            !$this->obooking->get_manager_info(
                'manager_id',
                $_POST['manager_id'],
                site_id
            )
        ) {
            $this->uFunc->error(70);
        }
        if (!uString::isDigits($_POST['type'])) {
            $this->uFunc->error(80);
        }
        if (
            !$this->obooking->get_rec_type_info(
                'rec_type_id',
                $_POST['type'],
                site_id
            )
        ) {
            $this->uFunc->error(90);
        }


        $timeAr=explode(':',$_POST['time']);
        $hour=$timeAr[0];
        $min=$timeAr[1];
        if($min>=0&&$min<=15) {
            if($min<7.5) {
                $min = '00';
            }
            else {
                $min = '15';
            }
        }
        elseif($min>15&&$min<=30) {
            if($min<22.5) {
                $min = '15';
            }
            else {
                $min = '30';
            }
        }
        elseif($min>30) {
            if($min<37.5) {
                $min = '30';
            }
            else {
                $min = '45';
            }
        }

        $dateobj = DateTime::createFromFormat('d.m.Y H:i',$_POST['record_date'] . ' ' . $hour.':'.$min);
        $iso_datetime = $dateobj->format(Datetime::ATOM);
        $timestamp = strtotime($iso_datetime);

        $duration_ar = explode(':', $_POST['duration']);
        $this->rec_duration = $duration_ar[0] * 3600;
        $this->rec_duration += $duration_ar[1] * 60;

        if (!(int) $_SESSION['SESSION']['timezone_difference']) {
            $_SESSION['SESSION']['timezone_difference'] = 3600;
        }

        $this->rec_timestamp = $timestamp;

        $this->rec_id = (int) $_POST['rec_id'];

        if (
            !$this->obooking->time_is_free(
                $_POST['class_id'],
                $this->rec_timestamp,
                $this->rec_duration,
                $this->rec_id
            )
        ) {
            echo json_encode([
                'status' => 'error',
                'msg' => 'time is busy',
            ]);
            exit();
        }
    }

    private function save_record($site_id = site_id)
    {
        if (
            !($class_info = $this->obooking->get_class_info(
                'office_id',
                $_POST['class_id']
            ))
        ) {
            $this->uFunc->error(100, 1);
        }
        $office_id = (int) $class_info->office_id;

        $clients_ar = explode('##', $_POST['client']);
        foreach ($clients_ar as $i => $iValue) {
            $clients_ar[$i] = (int) str_replace('#', '', $clients_ar[$i]);
        }

        if (
            $rec_type_info = $this->obooking->get_rec_type_info(
                'rec_type_name',
                $_POST['type']
            )
        ) {
            $rec_type_name = $rec_type_info->rec_type_name;
        } else {
            $rec_type_name = '';
        }

        if (
            $office_info = $this->obooking->get_office_info(
                'office_name',
                $office_id
            )
        ) {
            $office_name = $office_info->office_name;
        } else {
            $office_name = '';
        }

        if (
            $class_info = $this->obooking->get_class_info(
                'class_name',
                $_POST['class_id']
            )
        ) {
            $class_name = $class_info->class_name;
        } else {
            $class_name = '';
        }

        if (
            $manager_info = $this->obooking->get_manager_info(
                'manager_name,manager_lastname',
                $_POST['manager_id']
            )
        ) {
            $manager_name =
                $manager_info->manager_name .
                ' ' .
                $manager_info->manager_lastname;
        } else {
            $manager_name = '';
        }

        $description_end =
            ': ' .
            $rec_type_name .
            '<br>
                        Филиал: ' .
            $office_name .
            '<br>
                        Класс: ' .
            $class_name .
            '<br>
                        Наставник: ' .
            $manager_name .
            '<br>
                        Время занятия: ' .
            date('d.m.Y H:i', $this->rec_timestamp);

        if ($this->rec_id) {
            try {
                $stm = $this->uFunc->pdo('obooking')->prepare('UPDATE  
                records 
                SET
                rec_type=:rec_type, 
                office_id=:office_id, 
                class_id=:class_id, 
                manager_id=:manager_id, 
                timestamp=:timestamp,
                duration=:duration,
                notes=:notes,
                price=:price,
                price_without_card=:price_without_card
                WHERE
                rec_id=:rec_id AND
                site_id=:site_id 
                ');
                $site_id = site_id;
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                $stm->bindParam(':rec_id', $this->rec_id, PDO::PARAM_INT);
                $stm->bindParam(':rec_type', $_POST['type'], PDO::PARAM_INT);
                $stm->bindParam(':office_id', $office_id, PDO::PARAM_INT);
                $stm->bindParam(
                    ':class_id',
                    $_POST['class_id'],
                    PDO::PARAM_INT
                );
                $stm->bindParam(
                    ':manager_id',
                    $_POST['manager_id'],
                    PDO::PARAM_INT
                );
                $stm->bindParam(
                    ':timestamp',
                    $this->rec_timestamp,
                    PDO::PARAM_INT
                );
                $stm->bindParam(
                    ':duration',
                    $this->rec_duration,
                    PDO::PARAM_INT
                );
                $stm->bindParam(':notes', $_POST['notes'], PDO::PARAM_STR);
                $stm->bindParam(':price', $_POST['price'], PDO::PARAM_STR);
                $stm->bindParam(
                    ':price_without_card',
                    $_POST['price_without_card'],
                    PDO::PARAM_STR
                );
                $stm->execute();
            } catch (PDOException $e) {
                $this->uFunc->error('110' /*.$e->getMessage()*/);
            }

            try {
                $stm = $this->uFunc->pdo('obooking')->prepare('SELECT 
                client_id 
                FROM 
                records_clients 
                WHERE 
                rec_id=:rec_id AND
                site_id=:site_id
                ');
                $stm->bindParam(':rec_id', $this->rec_id, PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                $stm->execute();

                $current_clients_ar = [];
                $clients2action = [];
                for ($i = 0; ($client = $stm->fetch(PDO::FETCH_OBJ)); $i++) {
                    $current_clients_ar[$i] = (int) $client->client_id;
                    if (in_array($current_clients_ar[$i], $clients_ar)) {
                        $clients2action[$current_clients_ar[$i]] = 'exists';
                    } else {
                        $clients2action[$current_clients_ar[$i]] = 'delete';
                    }
                }

                foreach ($clients_ar as $iValue) {
                    if (!in_array($iValue, $current_clients_ar)) {
                        $clients2action[$iValue] = 'add';
                    }
                }

                foreach ($clients2action as $key => $value) {
                    if ($value === 'add') {
                        try {
                            $stm = $this->uFunc->pdo('obooking')
                                ->prepare("INSERT INTO 
                            records_clients (
                             rec_id, 
                             client_id, 
                             status, 
                             trial,
                             site_id
                             ) VALUES (
                             :rec_id, 
                             :client_id, 
                             '-1', 
                             0,
                             :site_id
                             )
                            ");
                            $stm->bindParam(
                                ':rec_id',
                                $this->rec_id,
                                PDO::PARAM_INT
                            );
                            $stm->bindParam(':client_id', $key, PDO::PARAM_INT);
                            $stm->bindParam(
                                ':site_id',
                                $site_id,
                                PDO::PARAM_INT
                            );
                            $stm->execute();
                        } catch (PDOException $e) {
                            $this->uFunc->error('120' . $e->getMessage());
                        }

                        $this->obooking->save_records_history(
                            time(),
                            $key,
                            'Записан(а) на занятие' . $description_end
                        );
                    } elseif ($value === 'delete') {
                        try {
                            $stm = $this->uFunc->pdo('obooking')
                                ->prepare('DELETE FROM 
                            records_clients
                            WHERE
                            rec_id=:rec_id AND 
                            client_id=:client_id AND
                            site_id=:site_id
                            ');
                            $stm->bindParam(
                                ':rec_id',
                                $this->rec_id,
                                PDO::PARAM_INT
                            );
                            $stm->bindParam(':client_id', $key, PDO::PARAM_INT);
                            $stm->bindParam(
                                ':site_id',
                                $site_id,
                                PDO::PARAM_INT
                            );
                            $stm->execute();
                        } catch (PDOException $e) {
                            $this->uFunc->error('130' /*.$e->getMessage()*/);
                        }

                        $this->obooking->save_records_history(
                            time(),
                            $key,
                            'Убран(а) с занятия' . $description_end
                        );
                    }
                }
            } catch (PDOException $e) {
                $this->uFunc->error('140' /*.$e->getMessage()*/);
            }
        } else {
            try {
                $stm = $this->uFunc->pdo('obooking')->prepare('INSERT INTO
                records (
                site_id,
                rec_type,
                office_id,
                class_id,
                manager_id,
                timestamp,
                duration,
                notes,
                price,
                price_without_card
                ) VALUES (
                :site_id,
                :rec_type,
                :office_id,
                :class_id,
                :manager_id,
                :timestamp,
                :duration,
                :notes,
                :price,
                :price_without_card
                )
                ');
                $site_id = site_id;
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                $stm->bindParam(':rec_type', $_POST['type'], PDO::PARAM_INT);
                $stm->bindParam(':office_id', $office_id, PDO::PARAM_INT);
                $stm->bindParam(
                    ':class_id',
                    $_POST['class_id'],
                    PDO::PARAM_INT
                );
                $stm->bindParam(
                    ':manager_id',
                    $_POST['manager_id'],
                    PDO::PARAM_INT
                );
                $stm->bindParam(
                    ':timestamp',
                    $this->rec_timestamp,
                    PDO::PARAM_INT
                );
                $stm->bindParam(
                    ':duration',
                    $this->rec_duration,
                    PDO::PARAM_INT
                );
                $stm->bindParam(':notes', $_POST['notes'], PDO::PARAM_STR);
                $stm->bindParam(':price', $_POST['price'], PDO::PARAM_STR);
                $stm->bindParam(
                    ':price_without_card',
                    $_POST['price_without_card'],
                    PDO::PARAM_STR
                );
                $stm->execute();
            } catch (PDOException $e) {
                $this->uFunc->error('150' /*.$e->getMessage()*/);
            }

            $this->rec_id = $this->uFunc->pdo('obooking')->LastInsertId();

            foreach ($clients_ar as $key => $value) {
                try {
                    $stm = $this->uFunc->pdo('obooking')->prepare("INSERT INTO 
                        records_clients (
                         rec_id, 
                         client_id, 
                         status, 
                         trial,
                         site_id
                         ) VALUES (
                         :rec_id, 
                         :client_id, 
                         '-1', 
                         0,
                         :site_id
                         )
                        ");
                    $stm->bindParam(':rec_id', $this->rec_id, PDO::PARAM_INT);
                    $stm->bindParam(':client_id', $value, PDO::PARAM_INT);
                    $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                    $stm->execute();
                } catch (PDOException $e) {
                    $this->uFunc->error('155' . $e->getMessage());
                }

                $this->obooking->save_records_history(
                    time(),
                    $value,
                    'Записан(а) на занятие' . $description_end
                );
            }
        }
    }

    private function set_trial($site_id = site_id)
    {
        if (!isset($_POST['is_trial'], $_POST['rec_id'], $_POST['client_id'])) {
            $this->uFunc->error(160);
        }
        $is_trial = (int) $_POST['is_trial'] ? 0 : 1; //invert
        $rec_id = (int) $_POST['rec_id'];
        $client_id = (int) $_POST['client_id'];

        try {
            $stm = $this->uFunc->pdo('obooking')->prepare('UPDATE 
            records_clients 
            SET
            trial=:trial
            WHERE
            rec_id=:rec_id AND
            client_id=:client_id AND
            site_id=:site_id
            ');
            $stm->bindParam(':trial', $is_trial, PDO::PARAM_INT);
            $stm->bindParam(':rec_id', $rec_id, PDO::PARAM_INT);
            $stm->bindParam(':client_id', $client_id, PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            $stm->execute();
        } catch (PDOException $e) {
            $this->uFunc->error('170' /*.$e->getMessage()*/);
        }
    }

    public function __construct(&$uCore) {
        $uSes=new uSes($uCore);
        if(!$uSes->access(2)) {
            print json_encode([
                'status'=>'forbidden'
            ]);
            exit;
        }
        $this->obooking=new common($uCore);
        $user_id=(int)$uSes->get_val('user_id');
        $is_admin=$this->obooking->is_admin($user_id);

        if(!$is_admin) {
            print json_encode([
                'status'=>'forbidden'
            ]);
            exit;
        }

        $this->uFunc = new uFunc($uCore);

        if (isset($_POST['action'])) {
            if ($_POST['action'] === 'delete') {
                $this->obooking->delete_record($_POST['rec_id'], site_id);
            } elseif ($_POST['action'] === 'is_trial') {
                $this->set_trial();
            }
        } else {
            $this->check_data();
            $this->save_record();
        }

        echo json_encode([
            'status' => 'done',
        ]);
    }
}
new save_record($this);
