<?php
namespace uConf;

use PDO;
use PDOException;
use PDOStatement;
use processors\uFunc;
use uString;

require_once 'processors/classes/uFunc.php';

/**
 * Cleans client phones. Deletes white spaces, -, _
 * Checks phones and prints out wrong phone numbers
 * @package uConf
 */
class clean_client_phones {
    /**
     * @var uFunc
     */
    private $uFunc;

    /**
     * @param int $site_id
     * @return bool|PDOStatement
     */
    private function get_all_site_clients($site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo('obooking')->prepare('SELECT
            client_id,
            client_phone,
            client_phone2
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
     * @param string $phone1
     * @param string $phone2
     * @param int $client_id
     * @param int $site_id
     */
    private function update_phones($phone1, $phone2, $client_id, $site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo('obooking')->prepare('UPDATE
            madmakers_obooking.clients
            SET
            client_phone=:phone1,
            client_phone2=:phone2
            WHERE
            client_id=:client_id AND
            site_id=:site_id
            ');
            $stm->bindParam(':phone1', $phone1,PDO::PARAM_STR);
            $stm->bindParam(':phone2', $phone2,PDO::PARAM_STR);
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

        echo '<h1>clean_client_phones. START</h1>';

        if(!$clients_stm=$this->get_all_site_clients()) {
            exit('No clients found in database');
        }
        while($client=$clients_stm->fetch(PDO::FETCH_OBJ)) {
            $client_id=(int)$client->client_id;
            $phone1=$client->client_phone;
            $phone2=$client->client_phone2;

            $phone1=trim($phone1);
            $phone1=str_replace('-','',$phone1);
            $phone1=str_replace('_','',$phone1);
            $phone1=str_replace(' ','',$phone1);

            if(!uString::isPhone($phone1)) {
                print "<p>Wrong phone 1 $phone1 in client record#$client_id</p>";
            }

            $phone2=trim($phone2);
            $phone2=str_replace('-','',$phone2);
            $phone2=str_replace('_','',$phone2);
            $phone2=str_replace(' ','',$phone2);

            if(!uString::isPhone($phone2)) {
                print "<p>Wrong phone 2 $phone2 in client record#$client_id</p>";
            }

            if($phone1!==$client->client_phone||$phone2!==$client->client_phone2) {
                $this->update_phones($phone1,$phone2,$client_id);
            }

        }
        echo '<h1>clean_client_phones. FINISHED</h1>';
    }
}
new clean_client_phones($this);
