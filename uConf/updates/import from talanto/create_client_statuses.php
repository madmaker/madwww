<?php
namespace uConf;

use PDO;
use PDOException;
use PDOStatement;
use processors\uFunc;

require_once 'processors/classes/uFunc.php';

/**
 * Class create_client_statuses
 * @package uConf
 */
class create_client_statuses {
    /**
     * @var uFunc
     */
    private $uFunc;

    /**
     * @param int $site_id
     * @return bool|PDOStatement
     */
    private $client_statuses_ar;

    /**
     * @param int $site_id
     * @return bool|PDOStatement
     */
    private function get_all_site_clients($site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo('obooking')->prepare('SELECT
            client_id,
            tmp_talanto_client_type
            FROM
            madmakers_obooking.clients
            WHERE
            site_id=:site_id
            ');
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('1587409996'/*.$e->getMessage()*/);}

        return false;
    }


    /**
     * @param string $client_status_name
     * @param int $site_id
     * @return int $client_status_id
     */
    private function create_client_status($client_status_name, $site_id=site_id) {
        if(!isset($this->client_statuses_ar)) {
            $this->client_statuses_ar = [];
        }

        if(!isset($this->client_statuses_ar[$client_status_name])) {
            try {
                $stm = $this->uFunc->pdo('obooking')->prepare('INSERT INTO
                madmakers_obooking.client_statuses (client_status_name, site_id) VALUES (
                :client_status_name,
                :site_id                                                                        
                )
                ');
                $stm->bindParam(':client_status_name', $client_status_name, PDO::PARAM_STR);
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                $stm->execute();

                $this->client_statuses_ar[$client_status_name]=(int)$this->uFunc->pdo('obooking')->lastInsertId();
            } catch (PDOException $e) {
                $this->uFunc->error('1587410489'/*.$e->getMessage()*/);
            }
        }
        return $this->client_statuses_ar[$client_status_name];
    }

    /**
     * @param int $status_id
     * @param int $client_id
     * @param int $site_id
     */
    private function update_client_statuses($status_id,$client_id, $site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo('obooking')->prepare('UPDATE
            madmakers_obooking.clients
            SET
            client_status=:client_status
            WHERE
            client_id=:client_id AND
            site_id=:site_id
            ');
            $stm->bindParam(':client_status', $status_id,PDO::PARAM_INT);
            $stm->bindParam(':client_id', $client_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1587410489'/*.$e->getMessage()*/);}
    }

    /**
     * clean_client_phones constructor.
     * @param $uCore
     */
    public function __construct (&$uCore) {
        $this->uFunc=new uFunc($uCore);

        echo '<h1>create_client_statuses. START</h1>';

        if(!$clients_stm=$this->get_all_site_clients()) {
            exit('No clients found in database');
        }
        while($client=$clients_stm->fetch(PDO::FETCH_OBJ)) {
            $client_id=(int)$client->client_id;
            $client_status_name=trim($client->tmp_talanto_client_type);

            $client_status_id=$this->create_client_status($client_status_name);
            $this->update_client_statuses($client_status_id,$client_id);

        }
        echo '<h1>clean_client_phones. FINISHED</h1>';
    }
}
new create_client_statuses($this);
