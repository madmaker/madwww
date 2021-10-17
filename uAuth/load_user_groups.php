<?php
namespace uAuth;

use uCore;
use uSes;
use uString;

require_once 'processors/uSes.php';
require_once 'uAuth/classes/common.php';

/**
 * uAuth/load_user_groups
 * ===
 * Get all site\'s user groups. Marks groups as assigned if they are connected to user
 * ---
 *
 * ## ACL - 31
 * ---
 *
 * #Request
 * ## POST JSON
 * - int user_id
 *
 * ## COOKIES
 * - user_id `int` - Authorized user id
 * - ses_id `string` - Access token given after signing in
 *
 * # Response JSON
 * - status `string`
 *     - success
 *     - forbidden
 *     - error
 * - msg `string` - in case of error status
 *     - wrong request - when request has no user_id
 *     - wrong user_id - when request has wrong user_id
 *     - json groups - json array with user's groups
 * - groups `array` - array of groups
 *      - group_id
 *      - assigned - if this group is assigned to user_id passed in request. If assigned - user_id, if not assigned - null
 *      - module - module id for this group_id
 * - assigned `int|null` - if group is assigned  value is user_id. If group is not assigned  value is null
 *
 * # Example
 * ## Request
 * ~~~~json
 * {
 * "user_id": 25
 * }
 * ~~~~
 *
 * ## Response
 * ### Successfully executed
 * ~~~~json
 * {
 * "status": "success",
 * "groups": {
 *      'group_id':32,
 *      'assigned':30,
 *      'module':'15
 *  },
 * {
 *      'group_id':33,
 *      'assigned':null,
 *      'module':'16
 *  },
 * }
 * ~~~~
 *
 * ### Error occurred
 * ~~~~json
 * {
 * "status": "error",
 * "msg": 'wrong request'
 * }
 * ~~~~
 *
 *
 * @package uAuth
 * @api
 */
class load_user_groups {
    /**
     * @var uCore
     */
    public $uCore;

    /**
     * Checks received parameters and validates permissions
     * @return int $user_id
     */
    private function checkData() {
        $uSes=new uSes($uCore);
        if(!$uSes->access(31)) {
            print json_encode([
                'status'=>'forbidden'
            ]);
            exit;
        }

        if(!isset($_POST['user_id'])) {
            print json_encode([
                'status'=>'error',
                'msg'=>'wrong request'
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
        return (int)$_POST['user_id'];
    }

    /**
     * load_user_groups constructor.
     *      Permissions Only use with ACL 31
     *
     * @param $uCore
     * @Rest/api
     * @uses \uAuth\common to work with user data and groups
     * @uses uSes to validate permissions
     * @api
     */
    public function __construct (&$uCore) {
        if(!isset($uCore)) {$uCore = new uCore();}
        $this->uCore=$uCore;
        $uAuth=new common($uCore);

        $user_id=$this->checkData();
        $groups=$uAuth->get_groups_with_assigned_to_user($user_id);
        print json_encode([
           'status'=>'success',
           'groups'=>$groups
        ]);
    }
}
new load_user_groups($this);
