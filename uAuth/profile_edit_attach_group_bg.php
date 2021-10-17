<?php
namespace uAuth;

use uSes;
use uString;

require_once 'processors/uSes.php';
require_once 'uAuth/classes/common.php';

/**
 * Class profile_edit_attach_group_bg
 *
 * Assigns|Removes group to|from user
 *
 * ---
 * # Request
 * ## JSON
 * - group_id : int
 * - user_id : int
 * - action : assign | remove
 *
 * ## COOKIE
 * - ses_id
 * - user_id
 *
 * ---
 *
 * # Response
 * - status
 *      - success
 *      - forbidden
 *      - error
 * - msg - in case of error
 *      - wrong request
 *      - wrong group id
 *      - wrong user_id
 * ---
 *
 * # Example
 *
 * ## Request 1
 * ~~~json
 * {
 * "group_id": 3,
 * "user_id": 25,
 * "action": "assign"
 * }
 * ~~~
 *
 * ## Response 1
 * ~~~json
 * {
 * "status": "success"
 * }
 * ~~~
 *
 * ## Request 1
 * ~~~json
 * {
 * "group_id": 99950,
 * "user_id": 25,
 * "action": "assign"
 * }
 * ~~~
 *
 * ## Response 1
 * ~~~json
 * {
 * "status": "error",
 * "msg": "wrong group id"
 * }
 * ~~~
 *
 * ---
 *
 * @api
 * @package uAuth
 */
class profile_edit_attach_group_bg {
    /**
     * Checks data from request
     *
     * @return array
     */
    private function checkData() {
        if(!isset(
            $_POST['action'],
            $_POST['group_id'],
            $_POST['user_id'])
        ) {
            print json_encode([
                'status'=>'error',
                'msg'=>'wrong request'
            ]);
            exit;
        }
        $action=$_POST['action'];

        if($action!=='assign') {
            $action='remove';
        }

        if(!uString::isDigits($_POST['group_id'])) {
            print json_encode([
                'status'=>'error',
                'msg'=>'wrong group id'
            ]);
            exit;
        }

        $group_id=(int)$_POST['group_id'];


        if($group_id===13) {
            print json_encode([
                'status'=>'success'
            ]);
            exit;
        }

        if(!uString::isDigits($_POST['user_id'])) {
            print json_encode([
                'status'=>'error',
                'msg'=>'wrong user_id'
            ]);
            exit;
        }

        $user_id=(int)$_POST['user_id'];

        return [
            'user_id'=>$user_id,
            'group_id'=>$group_id,
            'action'=>$action
        ];
    }


    /**
     * profile_edit_attach_group_bg constructor.
     * @param $uCore
     */
    public function __construct (&$uCore) {
        $uSes=new uSes($uCore);
        if(!$uSes->access(31)) {
            print json_encode([
                'status'=>'forbidden'
            ]);
            exit;
        }

        $result=$this->checkData();

        $uAuth=new common($uCore);

        $uAuth->assignOrRemoveUserToGroup($result['user_id'],$result['group_id'],$result['action']);

        $uSes->getUserACL();

        print json_encode([
            'status'=>'success'
        ]);
        exit;
    }
}
new profile_edit_attach_group_bg($this);
