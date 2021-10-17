<?php
namespace uConf;

use uSes;

require_once 'processors/uSes.php';

/**
 * Class update
 * @package uConf
 */
class update {

    /**
     * update constructor.
     * @param $uCore
     */
    public function __construct (&$uCore) {
        $uSes=new uSes($uCore);
        if(!$uSes->access(17)) {
            print 'forbidden';
            exit;
        }

//        require_once "uConf/updates/migrate_texts2pages.php";
    }
}
new update($this);

//require_once 'uConf/updates/import from talanto/clean_client_phones.php';
//require_once 'uConf/updates/import from talanto/create_client_statuses.php';
//require_once 'uConf/updates/import from talanto/create_cards.php';
