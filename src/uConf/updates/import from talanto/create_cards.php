<?php
namespace uConf;

use PDO;
use PDOException;
use PDOStatement;
use processors\uFunc;

require_once 'processors/classes/uFunc.php';

/**
 * Class create_cards
 * @package uConf
 */
class create_cards {
    /**
     * @var uFunc
     */
    private $uFunc;

    private $card_types_ar;
    private $card_types_validity_ar;
    private $card_types_price_ar;

    private $subscription_types_ar;
    private $subscription_types_validity_ar;
    private $subscription_types_classes_included_ar;
    private $subscription_types_price_ar;

    /**
     * @param int $site_id
     * @return bool|PDOStatement
     */
    private function get_all_abonements($site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo('obooking')->prepare('SELECT
            id,
            abonement_title,
            duration_days,
            price,
            visits_amount,
            start_date,
            end_date,
            visits_left,
            client
            FROM
            madmakers_obooking.tmp_talanto_abonements
            WHERE
            site_id=:site_id
            ');
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('1587409996'.$e->getMessage());}

        return false;
    }

    /**
     * @return int
     */
    private function get_new_card_type_id() {
        try {
            $stm = $this->uFunc->pdo('obooking')->query('SELECT
                card_type_id
                FROM
                card_types
                ORDER BY
                card_type_id DESC
                LIMIT 1
                ');

            if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                return $qr->card_type_id+1;
            }
        } catch (PDOException $e) {
            $this->uFunc->error('1587410489'.$e->getMessage());
        }
        return 1;
    }

    /**
     * @return int
     */
    private function get_new_subscription_type_id() {
        try {
            $stm = $this->uFunc->pdo('obooking')->query('SELECT
                subscription_type_id
                FROM
                subscription_types
                ORDER BY
                subscription_type_id DESC
                LIMIT 1
                ');

            if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                return $qr->subscription_type_id+1;
            }
        } catch (PDOException $e) {
            $this->uFunc->error('1587415216'.$e->getMessage());
        }
        return 1;
    }

    /**
     * @param $card_type_name
     * @param int $site_id
     * @return mixed
     */
    private function create_card_type($card_type_name, $site_id=site_id) {
        $card_type_id=$this->get_new_card_type_id();

        if(!isset($this->card_types_ar)) {
            $this->card_types_ar = [];
        }
        if(!isset($this->card_types_validity_ar)) {
            $this->card_types_validity_ar = [];
        }
        if(!isset($this->card_types_price_ar)) {
            $this->card_types_price_ar = [];
        }

        if(!isset($this->card_types_ar[$card_type_name])) {
            try {
                $stm = $this->uFunc->pdo('obooking')->prepare('INSERT INTO
                madmakers_obooking.card_types (
                card_type_id,
                card_type_name,
                validity,
                price,
                site_id
                ) VALUES (
                :card_type_id,
                :card_type_name,
                0,
                0,
                :site_id                                                                       
                )
                ');
                $stm->bindParam(':card_type_id', $card_type_id, PDO::PARAM_INT);
                $stm->bindParam(':card_type_name', $card_type_name, PDO::PARAM_STR);
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                $stm->execute();

                $this->card_types_ar[$card_type_name]=$card_type_id;
                $this->card_types_validity_ar[$card_type_id]=0;
                $this->card_types_price_ar[$card_type_id]=0;
            } catch (PDOException $e) {
                $this->uFunc->error('1587414691'.$e->getMessage());
            }
        }
        return $this->card_types_ar[$card_type_name];
    }

    /**
     * @param $subscription_type_name
     * @param int $site_id
     * @return mixed
     */
    private function create_subscription_type($subscription_type_name, $site_id=site_id) {
        $subscription_type_id=$this->get_new_subscription_type_id();

        if(!isset($this->subscription_types_ar)) {
            $this->subscription_types_ar = [];
        }
        if(!isset($this->subscription_types_validity_ar)) {
            $this->subscription_types_validity_ar = [];
        }
        if(!isset($this->subscription_types_price_ar)) {
            $this->subscription_types_price_ar = [];
        }

        if(!isset($this->subscription_types_ar[$subscription_type_name])) {
            try {
                $stm = $this->uFunc->pdo('obooking')->prepare('INSERT INTO
                madmakers_obooking.subscription_types (
                subscription_type_id,
                subscription_type_name,
                validity,
                price,
                site_id
                ) VALUES (
                :subscription_type_id,
                :subscription_type_name,
                0,
                0,
                :site_id                                                                       
                )
                ');
                $stm->bindParam(':subscription_type_id', $subscription_type_id, PDO::PARAM_INT);
                $stm->bindParam(':subscription_type_name', $subscription_type_name, PDO::PARAM_STR);
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                $stm->execute();

                $this->subscription_types_ar[$subscription_type_name]=$subscription_type_id;
                $this->subscription_types_validity_ar[$subscription_type_id]=0;
                $this->subscription_types_price_ar[$subscription_type_id]=0;
                $this->subscription_types_classes_included_ar[$subscription_type_id]=0;
            } catch (PDOException $e) {
                $this->uFunc->error('1587415395'.$e->getMessage());
            }
        }
        return $this->subscription_types_ar[$subscription_type_name];
    }

    /**
     * @param $card_type_id
     * @param $price
     */
    private function increase_card_type_price($card_type_id, $price) {
        if($this->card_types_price_ar[$card_type_id]<$price) {
            $this->card_types_price_ar[$card_type_id]=$price;
        }
    }

    /**
     * duration_days
     * @param $card_type_id
     * @param $validity
     */
    private function increase_card_type_validity($card_type_id, $validity) {
        if($this->card_types_validity_ar[$card_type_id]<$validity) {
            $this->card_types_validity_ar[$card_type_id]=$validity;
        }
    }


    /**
     * @param $subscription_type_id
     * @param $price
     */
    private function increase_subscription_type_price($subscription_type_id, $price) {
        if($this->subscription_types_price_ar[$subscription_type_id]<$price) {
            $this->subscription_types_price_ar[$subscription_type_id]=$price;
        }
    }

    //duration_days

    /**
     * @param $subscription_type_id
     * @param $validity
     */
    private function increase_subscription_type_validity($subscription_type_id, $validity) {
        if($this->subscription_types_validity_ar[$subscription_type_id]<$validity) {
            $this->subscription_types_validity_ar[$subscription_type_id]=$validity;
        }
    }

    /**
     * @param $subscription_type_id
     * @param $classes_included
     */
    private function increase_subscription_type_classes_included($subscription_type_id, $classes_included) {
        if($this->subscription_types_classes_included_ar[$subscription_type_id]<$classes_included) {
            $this->subscription_types_classes_included_ar[$subscription_type_id]=$classes_included;
        }
    }

    /**
     * @param $card_type_id
     * @param $card_number
     * @param $valid_thru
     * @param $start_date
     * @param $client_id
     * @param int $site_id
     */
    private function create_client_card($card_type_id, $card_number, $valid_thru, $start_date, $client_id, $site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo('obooking')->prepare('INSERT INTO
            clients_cards (
            card_type_id,
            card_number,
            valid_thru,
            start_date,
            client_id,
            site_id
            ) VALUES (
            :card_type_id,
            :card_number,
            :valid_thru,
            :start_date,
            :client_id,
            :site_id                                                      
            )
            ');
            $stm->bindParam(':card_type_id', $card_type_id,PDO::PARAM_INT);
            $stm->bindParam(':card_number', $card_number,PDO::PARAM_STR);
            $stm->bindParam(':valid_thru', $valid_thru,PDO::PARAM_INT);
            $stm->bindParam(':start_date', $start_date,PDO::PARAM_INT);
            $stm->bindParam(':client_id', $client_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1587418281'.$e->getMessage());}
    }

    /**
     * @param $subscription_type_id
     * @param $valid_thru
     * @param $start_date
     * @param $visits_left
     * @param $client_id
     * @param int $site_id
     */
    private function create_client_subscription($subscription_type_id, $valid_thru, $start_date, $visits_left, $client_id, $site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo('obooking')->prepare('INSERT INTO
            clients_subscriptions (
            subscription_type_id,
            valid_thru,
            start_date,
            visits_left,
            client_id,
            site_id
            ) VALUES (
            :subscription_type_id,
            :valid_thru,
            :start_date,
            :visits_left,
            :client_id,
            :site_id                                                      
            )
            ');
            $stm->bindParam(':subscription_type_id', $subscription_type_id,PDO::PARAM_INT);
            $stm->bindParam(':valid_thru', $valid_thru,PDO::PARAM_INT);
            $stm->bindParam(':start_date', $start_date,PDO::PARAM_INT);
            $stm->bindParam(':visits_left', $visits_left,PDO::PARAM_INT);
            $stm->bindParam(':client_id', $client_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1587418285'.$e->getMessage());}
    }

    private $get_client_card_no_ar;

    /**
     * @param $client_id
     * @param int $site_id
     * @return mixed
     */
    private function get_client_card_no($client_id, $site_id=site_id) {
        if(!isset($this->get_client_card_no_ar)) {
            $this->get_client_card_no_ar = [];
        }
        if(!isset($this->get_client_card_no_ar[$client_id])) {
            try {
                $stm = $this->uFunc->pdo('obooking')->prepare('SELECT
                tmp_talanto_card_no
                FROM
                clients
                WHERE
                client_id=:client_id AND
                site_id=:site_id
                ');
                $stm->bindParam(':client_id', $client_id, PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                $stm->execute();

                if ($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                    $this->get_client_card_no_ar[$client_id]=$qr->tmp_talanto_card_no;
                }
                else {
                    $this->get_client_card_no_ar[$client_id] = '';
                }
            } catch (PDOException $e) {
                $this->uFunc->error('1587418289'.$e->getMessage());
            }
        }
        return $this->get_client_card_no_ar[$client_id];
    }

    /**
     * @param string $client
     * @param int $site_id
     * @return bool|int
     */
    private function get_client_id($client, $site_id=site_id) {
        try {
            $stm = $this->uFunc->pdo('obooking')->prepare("SELECT
            client_id
            FROM
            clients
            WHERE 
            (
                concat(client_lastname,' ',client_name)=:client OR 
                concat(client_lastname,client_name)=:client
                ) AND
            site_id=:site_id
            ");
            $stm->bindParam(':client', $client, PDO::PARAM_STR);
            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            $stm->execute();

            if ($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                return (int)$qr->client_id;
            }
        } catch (PDOException $e) {
            $this->uFunc->error('1587418293'.$e->getMessage());
        }

        return false;
    }

    /**
     * @param int $site_id
     * @return bool|PDOStatement
     */
    private function get_all_card_types($site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo('obooking')->prepare('SELECT
            card_type_id
            FROM
            card_types
            WHERE
            site_id=:site_id
            ');
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('1587419497'.$e->getMessage());}
        return false;
    }

    /**
     * @param int $site_id
     * @return bool|PDOStatement
     */
    private function get_all_subscription_types($site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo('obooking')->prepare('SELECT
            subscription_type_id
            FROM
            subscription_types
            WHERE
            site_id=:site_id
            ');
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('1587419435'.$e->getMessage());}
        return false;
    }


    /**
     * @param $card_type_id
     * @param int $site_id
     * @return bool
     */
    private function update_card_type_info($card_type_id, $site_id=site_id) {
        if(!isset($this->card_types_price_ar[$card_type_id])) {
            return false;
        }
        $price=$this->card_types_price_ar[$card_type_id];

        if(!isset($this->card_types_validity_ar[$card_type_id])) {
            return false;
        }
        $validity=$this->card_types_validity_ar[$card_type_id];

        try {
            $stm=$this->uFunc->pdo('obooking')->prepare('UPDATE
            card_types
            SET
            price=:price,
            validity=:validity
            WHERE
            card_type_id=:card_type_id AND
            site_id=:site_id
            ');
            $stm->bindParam(':price', $price,PDO::PARAM_INT);
            $stm->bindParam(':validity', $validity,PDO::PARAM_INT);
            $stm->bindParam(':card_type_id', $card_type_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1587419500'.$e->getMessage());}
        return true;
    }

    /**
     * @param $subscription_type_id
     * @param int $site_id
     * @return bool
     */
    private function update_subscription_type_info($subscription_type_id, $site_id=site_id) {
        if(!isset($this->subscription_types_price_ar[$subscription_type_id])) {
            return false;
        }
        $price=$this->subscription_types_price_ar[$subscription_type_id];

        if(!isset($this->subscription_types_validity_ar[$subscription_type_id])) {
            return false;
        }
        $validity=$this->subscription_types_validity_ar[$subscription_type_id];

        if(!isset($this->subscription_types_classes_included_ar[$subscription_type_id])) {
            return false;
        }
        $classes_included=$this->subscription_types_classes_included_ar[$subscription_type_id];

        try {
            $stm=$this->uFunc->pdo('obooking')->prepare('UPDATE
            subscription_types
            SET
            price=:price,
            validity=:validity,
            classes_included=:classes_included
            WHERE
            subscription_type_id=:subscription_type_id AND
            site_id=:site_id
            ');
            $stm->bindParam(':price', $price,PDO::PARAM_INT);
            $stm->bindParam(':validity', $validity,PDO::PARAM_INT);
            $stm->bindParam(':classes_included', $classes_included,PDO::PARAM_INT);
            $stm->bindParam(':subscription_type_id', $subscription_type_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1587419402'.$e->getMessage());}
        return true;
    }

    /**
     * create_cards constructor.
     * @param $uCore
     */
    public function __construct (&$uCore) {
        $this->uFunc=new uFunc($uCore);

        echo '<h1>create_cards. START</h1>';

        if(!$abonements_stm=$this->get_all_abonements()) {
            exit('No clients found in database');
        }
        while($abonement=$abonements_stm->fetch(PDO::FETCH_OBJ)) {
            $abonement_title=trim($abonement->abonement_title);
            $validity=(int)$abonement->duration_days;

            $start_date_formatted=$abonement->start_date;
            $dateAr=explode('.',$start_date_formatted);
            $dd=$dateAr[0];
            $mm=$dateAr[1];
            $yyyy=$dateAr[2];
            $start_date=strtotime($mm.'/'.$dd.'/'.$yyyy.' 00:00:00');

            $end_date_formatted=$abonement->end_date;
            $dateAr=explode('.',$end_date_formatted);
            $dd=$dateAr[0];
            $mm=$dateAr[1];
            $yyyy=$dateAr[2];
            $valid_thru=strtotime($mm.'/'.$dd.'/'.$yyyy.' 00:00:00');

            $price=(int)$abonement->price;
            $classes_included=(int)$abonement->visits_amount;
            $visits_left=(int)$abonement->visits_left;

            $client=$abonement->client;
            if(!$client_id=$this->get_client_id($client)) {
                print "<p>Client is not found $client</p>";
                continue;
            }

            if($visits_left) {
                $subscription_type_id = $this->create_subscription_type($abonement_title);//Увеличивать price и validity
                $this->increase_subscription_type_price($subscription_type_id,$price);
                $this->increase_subscription_type_classes_included($subscription_type_id,$classes_included);
                $this->increase_subscription_type_validity($subscription_type_id,$validity);

                $this->create_client_subscription($subscription_type_id,$valid_thru,$start_date,$visits_left,$client_id);
            }
            else {
                $card_type_id = $this->create_card_type($abonement_title);//Увеличивать price и validity
                $this->increase_card_type_price($card_type_id,$price);
                $this->increase_card_type_validity($card_type_id,$validity);
                $card_number=$this->get_client_card_no($client_id);

                $this->create_client_card($card_type_id,$card_number,$valid_thru,$start_date,$client_id);
            }
        }

        $subscription_types_stm=$this->get_all_subscription_types();
        while($subscription_type=$subscription_types_stm->fetch(PDO::FETCH_OBJ)) {
            $subscription_type_id=(int)$subscription_type->subscription_type_id;

            $this->update_subscription_type_info($subscription_type_id);
        }

        $card_types_stm=$this->get_all_card_types();
        while($card_type=$card_types_stm->fetch(PDO::FETCH_OBJ)) {
            $card_type_id=(int)$card_type->card_type_id;

            $this->update_card_type_info($card_type_id);
        }

        echo '<h1>create_cards. FINISHED</h1>';
    }
}
new create_cards($this);
