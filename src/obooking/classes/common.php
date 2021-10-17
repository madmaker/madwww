<?php
namespace obooking;
use PDO;
use PDOException;
use processors\uFunc;
use stdClass;
use translator\translator;
use uSes;

require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';
require_once 'translator/translator.php';

class common {
    /**
     * @var translator
     */
    private $translator;
    /**
     * @var uSes
     */
    private $uSes;
    private $uFunc;

    //USERS COMMON
    /**
     * Updates cellphone for admin/manager/client assigned to UserId
     * @param int $UserId
     * @param string $NewPhone
     */
    public function update_user_cellphone($UserId,$NewPhone) {
        $UserTypesAr=[
            [
                'table'=>'administrators',
                'field'=>'administrator_phone'
            ],[
                'table'=>'managers',
                'field'=>'manager_phone'
            ],[
                'table'=>'clients',
                'field'=>'client_phone'
            ]
        ];

        foreach ($UserTypesAr AS $UserType) {
            $table=$UserType['table'];
            $field=$UserType['field'];
            try {
                $stm = $this->uFunc->pdo('obooking')->prepare("UPDATE
                $table
                SET
                $field=:$field
                WHERE
                user_id=:user_id
                ");
                $stm->bindParam(':user_id', $UserId, PDO::PARAM_INT);
                $stm->bindParam(":$field", $NewPhone, PDO::PARAM_STR);
                $stm->execute();
            } catch (PDOException $e) {
                $this->uFunc->error('1588451747'/*.$e->getMessage()*/);
            }
        }
    }

    /**
     * Updates email for admin/manager/client assigned to UserId
     * @param int $UserId
     * @param string $NewEmail
     */
    public function update_user_email($UserId,$NewEmail) {
        $UserTypesAr=[
            [
                'table'=>'administrators',
                'field'=>'administrator_email'
            ],[
                'table'=>'managers',
                'field'=>'manager_email'
            ],[
                'table'=>'clients',
                'field'=>'client_email'
            ]
        ];

        foreach ($UserTypesAr AS $UserType) {
            $table=$UserType['table'];
            $field=$UserType['field'];
            try {
                $stm = $this->uFunc->pdo('obooking')->prepare("UPDATE
                $table
                SET
                $field=:$field
                WHERE
                user_id=:user_id
                ");
                $stm->bindParam(':user_id', $UserId, PDO::PARAM_INT);
                $stm->bindParam(":$field", $NewEmail, PDO::PARAM_STR);
                $stm->execute();
            } catch (PDOException $e) {
                $this->uFunc->error('1588453921'/*.$e->getMessage()*/);
            }
        }
    }

    /**
     * Assigns user to admin, manager or client by email or phone
     * @param int $userId
     * @param string $userEmail
     * @param string $userPhone
     * @param int $site_id
     */
    public function assignUserToOwnerAdminManagerClient($userId,$userEmail,$userPhone,$site_id=site_id) {
        $stm_managers=$this->getManagerByUserIdOrEmailOrPhone($userId,$userEmail,$userPhone,$site_id);
        while($manager=$stm_managers->fetch(PDO::FETCH_OBJ)) {
            $manager->user_id=(int)$manager->user_id;
            if(!$manager->user_id) {
                $manager_id=(int)$manager->manager_id;
                $this->assignUserToManager($userId,$manager_id,$site_id);
            }
        }
        $stm_administrators=$this->getAdministratorByUserIdOrEmailOrPhone($userId,$userEmail,$userPhone,$site_id);
        while($administrator=$stm_administrators->fetch(PDO::FETCH_OBJ)) {
            $administrator->user_id=(int)$administrator->user_id;
            if(!$administrator->user_id) {
                $administrator_id=(int)$administrator->administrator_id;
                $this->assignUserToAdministrator($userId,$administrator_id,$site_id);
            }
        }
        $stm_clients=$this->getClientByUserIdOrEmailOrPhone($userId,$userEmail,$userPhone,$site_id);
        while($client=$stm_clients->fetch(PDO::FETCH_OBJ)) {
            $client->user_id=(int)$client->user_id;
            if(!$client->user_id) {
                $client_id=(int)$client->client_id;
                $this->assignUserToClient($userId,$client_id,$site_id);
            }
        }
    }

    //MANAGERS
    public function get_managers($q_select="manager_id",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            ".$q_select."
            FROM 
            managers 
            WHERE 
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('obooking common 10'/*.$e->getMessage()*/,1);}
        return 0;
    }

    private $get_manager_info_ar;
    /**
     * @param string $q_select : fields you want to be retrieved:
     * manager_id
     * site_id
     * manager_name
     * manager_lastname
     * manager_phone
     * manager_email
     * manager_status
     * manager_specialization
     * manager_birthdate
     * manager_balance
     * manager_comment
     * manager_vk_id
     * manager_bank_card_number
     * user_id
     * @param int $manager_id
     * @param int $site_id
     * @return object | boolean
     * Object of manager's fields you've requested | false if manager is not found
     */
    public function get_manager_info($q_select,$manager_id,$site_id=site_id) {
        if(!isset($this->get_manager_info_ar[$manager_id][$q_select])) {
            try {
                $stm = $this->uFunc->pdo('obooking')->prepare("SELECT 
                $q_select
                FROM 
                managers
                WHERE 
                      manager_id=:manager_id AND
                      site_id=:site_id
                ");
                $stm->bindParam(':manager_id', $manager_id, PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                $stm->execute();

                $this->get_manager_info_ar[$manager_id][$q_select]=$stm->fetch(PDO::FETCH_OBJ);
            } catch (PDOException $e) {$this->uFunc->error('1581948738'/*.$e->getMessage()*/,1);}
        }
        return $this->get_manager_info_ar[$manager_id][$q_select];
    }

    public function create_new_manager($manager_name,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("INSERT INTO 
            managers (
                      site_id, 
                      manager_name
                      ) 
            VALUES (
                    :site_id,
                    :manager_name
            )");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':manager_name', $manager_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return (int)$this->uFunc->pdo("obooking")->lastInsertId();
        }
        catch(PDOException $e) {$this->uFunc->error('obooking common 30'/*.$e->getMessage()*/,1);}
        return 0;
    }
    public function delete_manager($manager_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("DELETE FROM 
            managers 
            WHERE 
                  manager_id=:manager_id AND
                  site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':manager_id', $manager_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('obooking common 40'/*.$e->getMessage()*/,1);}
    }
    public function managers_list($show_select_btn=0,$site_id=site_id) {
        $managers_stm=$this->get_managers('manager_id,manager_name,manager_lastname,manager_phone,manager_email,user_id',$site_id);?>
        <div class="input-group">
            <!--suppress HtmlFormInputWithoutLabel -->
            <input type="text" id="obooking_managers_filter" class="form-control" placeholder="Фильтр" onkeyup="obooking_inline_edit.managers_filter()">
            <span class="input-group-btn">
                <button class="btn btn-default" type="button" onclick="obooking_inline_edit.managers_filter()"><span class="icon-search"></span></button>
            </span>
        </div>

        <table class="table table-striped table-condensed" id="obooking_managers_list">
            <?php
            while($manager=$managers_stm->fetch(PDO::FETCH_OBJ)) {
                $user_id=(int)$manager->user_id;?>
                <tr id="obooking_manager_row_<?=$manager->manager_id?>" class="obooking_manager_row">
                    <td class="manager_user_id">
                    <?if($user_id) {?>
                        <a target="_blank" href="/uAuth/profile/<?=$user_id?>"><span class="icon-user"></span></a>
                    <?}
                    else {?>
<!--                        <i class="icon-mail-alt" title="--><?//=$this->translator->txt('Invite to create account')?><!--"></i>-->
                    <?}?></td>
                    <td onclick="<?if($show_select_btn) {?>obooking_calendar.record_editor_select_manager(<?=$manager->manager_id?>);<?} else {?>obooking_inline_edit.edit_manager_init(<?=$manager->manager_id?>);<?}?>" class="manager_name"><?=$manager->manager_name?></td>
                    <td onclick="<?if($show_select_btn) {?>obooking_calendar.record_editor_select_manager(<?=$manager->manager_id?>);<?} else {?>obooking_inline_edit.edit_manager_init(<?=$manager->manager_id?>);<?}?>" class="manager_lastname"><?=$manager->manager_lastname?></td>
                    <td onclick="<?if($show_select_btn) {?>obooking_calendar.record_editor_select_manager(<?=$manager->manager_id?>);<?} else {?>obooking_inline_edit.edit_manager_init(<?=$manager->manager_id?>);<?}?>" class="manager_phone"><?=$manager->manager_phone?></td>
                    <td onclick="<?if($show_select_btn) {?>obooking_calendar.record_editor_select_manager(<?=$manager->manager_id?>);<?} else {?>obooking_inline_edit.edit_manager_init(<?=$manager->manager_id?>);<?}?>" class="manager_email"><?=$manager->manager_email?></td>
                    <?if($show_select_btn) {?>
                        <td><button class="btn btn-xs btn-default" onclick="obooking_inline_edit.edit_manager_init(<?=$manager->manager_id?>)"><span class="icon-pencil"></span> Изменить</button></td>
                    <?}?>
                    <td><div class="obooking_managers_buttons"><button type="button" class="btn btn-danger btn-xs obooking_managers_delete_btn" title="Удалить наставника" onclick="obooking_inline_edit.delete_manager_confirm(<?=$manager->manager_id?>)"><span class="icon-cancel"></span></button></div></td>
                </tr>
            <?}?>
        </table>
    <?}
    public function get_managers_of_class_for_day($class_id,$day_of_week/*0-mon,7-sun*/,$site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo('obooking')->prepare('SELECT DISTINCT 
            m.manager_id,
            m.manager_name,
            m.manager_lastname
            FROM 
            manager_schedule
            JOIN
            managers m 
            on manager_schedule.manager_id = m.manager_id
            WHERE
            day_of_week=:day_of_week AND
            class_id=:class_id AND
            manager_schedule.site_id=:site_id
            ');
            $stm->bindParam(':class_id', $class_id,PDO::PARAM_INT);
            $stm->bindParam(':day_of_week', $day_of_week,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('obooking common 50'/*.$e->getMessage()*/,1);}
        return 0;
    }
    public function get_managers_of_class_for_day_for_hour($class_id,$day_of_week/*0-mon,7-sun*/,$hour,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT DISTINCT 
            m.manager_id,
            m.manager_name,
            m.manager_lastname,
            m.manager_phone,
            m.manager_email
            FROM 
            manager_schedule
            JOIN
            managers m 
            on manager_schedule.manager_id = m.manager_id
            WHERE
            day_of_week=:day_of_week AND
            hour=:hour AND
            class_id=:class_id AND
            manager_schedule.site_id=:site_id
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':class_id', $class_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':day_of_week', $day_of_week,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':hour', $hour,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('obooking common 60'/*.$e->getMessage()*/,1);}
        return 0;
    }

    /**
     * Searches for manager by any field: user_id, email, phone
     * @param int $user_id
     * @param string $manager_email
     * @param string $manager_phone
     * @param int $site_id
     * @return bool|\PDOStatement
     */
    private function getManagerByUserIdOrEmailOrPhone($user_id,$manager_email,$manager_phone,$site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo('obooking')->prepare("SELECT
            manager_id,
            user_id
            FROM
            managers
            WHERE
            (
                (user_id=:user_id AND user_id!=0) OR
                (manager_email=:manager_email AND manager_email!='') OR
                (manager_phone=:manager_phone AND manager_phone!='')
            ) AND
            site_id=:site_id
            ");
            $stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            $stm->bindParam(':manager_email', $manager_email,PDO::PARAM_STR);
            $stm->bindParam(':manager_phone', $manager_phone,PDO::PARAM_STR);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1589231922'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedVariableInspection */
        return $stm;
    }

    /**
     * Assigns user to manager
     * @param int $user_id
     * @param int $manager_id
     * @param int $site_id
     */
    private function assignUserToManager($user_id,$manager_id,$site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo('obooking')->prepare('UPDATE
            managers
            SET
            user_id=:user_id
            WHERE
            manager_id=:manager_id AND
            site_id=:site_id
            ');
            $stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            $stm->bindParam(':manager_id', $manager_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1589211661'.$e->getMessage());}

    }

    //administrators
    /**
     * Checks if user belongs to group of administrators in OBOOKING
     * @param $user_id
     * @param int $site_id
     * @return bool
     * true if user belongs to admin group in OBOOKING, false if user not belongs
     */
    public function is_admin($user_id,$site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo('obooking')->prepare('SELECT
            administrator_id
            FROM
            administrators
            WHERE
            user_id=:user_id AND
            site_id=:site_id
            ');
            $stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('1588605157'/*.$e->getMessage()*/);}
        return false;
    }
    public function get_administrators($q_select= 'administrator_id', $site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo('obooking')->prepare("SELECT 
            $q_select
            FROM 
            administrators 
            WHERE 
            site_id=:site_id
            ");
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('1587008953'/*.$e->getMessage()*/,1);}
        return 0;
    }
    private $get_administrator_info_ar;

    /**
     * @param string $q_select : fields you want to be retrieved: administrator_id
    site_id
    administrator_name
    administrator_lastname
    administrator_phone
    administrator_email
    administrator_status
    administrator_birthday
    administrator_balance
    administrator_comment
    administrator_vk_id
    administrator_bank_card_number
    user_id
     * @param int $administrator_id
     * @param int $site_id
     * @return object | boolean
     * Object of administrator's fields you've requested | false if administrator is not found
     */
    public function get_administrator_info($q_select,$administrator_id,$site_id=site_id) {
        if(!isset($this->get_administrator_info_ar[$administrator_id][$q_select])) {
            try {
                $stm = $this->uFunc->pdo('obooking')->prepare("SELECT 
                $q_select
                FROM 
                administrators
                WHERE 
                administrator_id=:administrator_id AND
                site_id=:site_id
                ");
                $stm->bindParam(':administrator_id', $administrator_id, PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                $stm->execute();

                $this->get_administrator_info_ar[$administrator_id][$q_select]=$stm->fetch(PDO::FETCH_OBJ);
            } catch (PDOException $e) {$this->uFunc->error('1587008989'/*.$e->getMessage()*/,1);}
        }
        return $this->get_administrator_info_ar[$administrator_id][$q_select];
    }
    public function create_new_administrator($administrator_name,$site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo('obooking')->prepare('INSERT INTO 
            administrators (
            site_id, 
            administrator_name
            ) 
            VALUES (
            :site_id,
            :administrator_name
            )');
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->bindParam(':administrator_name', $administrator_name,PDO::PARAM_STR);
            $stm->execute();

            return (int)$this->uFunc->pdo('obooking')->lastInsertId();
        }
        catch(PDOException $e) {$this->uFunc->error('1587009025'.$e->getMessage(),1);}
        return 0;
    }
    public function delete_administrator($administrator_id,$site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo('obooking')->prepare('DELETE FROM 
            administrators 
            WHERE 
            administrator_id=:administrator_id AND
            site_id=:site_id
            ');
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->bindParam(':administrator_id', $administrator_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1587009048'/*.$e->getMessage()*/,1);}
    }
    public function administrators_list($site_id=site_id) {
        $administrators_stm=$this->get_administrators('administrator_id,administrator_name,administrator_lastname,administrator_phone,administrator_email,user_id',$site_id);?>
        <div class="input-group">
            <!--suppress HtmlFormInputWithoutLabel -->
            <input type="text" id="obooking_administrators_filter" class="form-control" placeholder="Фильтр" onkeyup="obooking_inline_edit.administrators_filter()">
            <span class="input-group-btn">
                <button class="btn btn-default" type="button" onclick="obooking_inline_edit.administrators_filter()"><span class="icon-search"></span></button>
            </span>
        </div>

        <table class="table table-striped table-condensed" id="obooking_administrators_list">
            <?php
            while($administrator=$administrators_stm->fetch(PDO::FETCH_OBJ)) {
                $user_id=(int)$administrator->user_id;?>
                <tr id="obooking_administrator_row_<?=$administrator->administrator_id?>" class="obooking_administrator_row">
                    <td class="administrator_user_id"><?php
                        if($user_id) {?><a target="_blank" href="/uAuth/profile/<?=$user_id?>"><span class="icon-user"></span></a><?}
                        ?></td>
                    <td onclick="obooking_inline_edit.edit_administrator_init(<?=$administrator->administrator_id?>);" class="administrator_name"><?=$administrator->administrator_name?></td>
                    <td onclick="obooking_inline_edit.edit_administrator_init(<?=$administrator->administrator_id?>);" class="administrator_lastname"><?=$administrator->administrator_lastname?></td>
                    <td onclick="obooking_inline_edit.edit_administrator_init(<?=$administrator->administrator_id?>);" class="administrator_phone"><?=$administrator->administrator_phone?></td>
                    <td onclick="obooking_inline_edit.edit_administrator_init(<?=$administrator->administrator_id?>);" class="administrator_email"><?=$administrator->administrator_email?></td>
                    <td><div class="obooking_administrators_buttons"><button type="button" class="btn btn-danger btn-xs obooking_administrators_delete_btn" title="Удалить наставника" onclick="obooking_inline_edit.delete_administrator_confirm(<?=$administrator->administrator_id?>)"><span class="icon-cancel"></span></button></div></td>
                </tr>
            <?}?>
        </table>
    <?}

    /**
     * @param int $administrator_id
     * @param int $site_id
     * @return int $user_id
     */
    public function checkIfAdministratorIsAssignedToUserProfile($administrator_id,$site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo('obooking')->prepare('SELECT
            user_id
            FROM
            administrators
            WHERE
            administrator_id=:administrator_id AND
            site_id=:site_id
            ');
            $stm->bindParam(':administrator_id', $administrator_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) {
                return 0;
            }
            return (int)$qr->user_id;

        }
        catch(PDOException $e) {$this->uFunc->error('1588074689'/*.$e->getMessage()*/);}
        return 0;
    }

    /**
     * Searches for administrator by any field: user_id, email, phone
     * @param int $user_id
     * @param string $administrator_email
     * @param string $administrator_phone
     * @param int $site_id
     * @return bool|\PDOStatement
     */
    private function getAdministratorByUserIdOrEmailOrPhone($user_id,$administrator_email,$administrator_phone,$site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo('obooking')->prepare("SELECT
            administrator_id,
            user_id
            FROM
            administrators
            WHERE
            (
                (user_id=:user_id AND user_id!=0) OR
                (administrator_email=:administrator_email AND administrator_email!='') OR
                (administrator_phone=:administrator_phone AND administrator_phone!='')
            ) AND
            site_id=:site_id
            ");
            $stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            $stm->bindParam(':administrator_email', $administrator_email,PDO::PARAM_STR);
            $stm->bindParam(':administrator_phone', $administrator_phone,PDO::PARAM_STR);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1589231927'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedVariableInspection */
        return $stm;
    }

    /**
     * Assigns user to administrator
     * @param int $user_id
     * @param int $administrator_id
     * @param int $site_id
     */
    private function assignUserToAdministrator($user_id,$administrator_id,$site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo('obooking')->prepare('UPDATE
            administrators
            SET
            user_id=:user_id
            WHERE
            administrator_id=:administrator_id AND
            site_id=:site_id
            ');
            $stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            $stm->bindParam(':administrator_id', $administrator_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1589211666'/*.$e->getMessage()*/);}

    }

    //CLIENTS
    public function get_clients($q_select="client_id",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            $q_select
            FROM 
            clients 
            WHERE 
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('obooking common 70'/*.$e->getMessage()*/,1);}
        return 0;
    }
    private $get_client_info_ar;

    /**
     * @param string $q_select client_id
        site_id
        client_name
        client_lastname
        client_birthdate
        client_phone
        client_phone2
        client_email
        client_balance
        client_bonus_balance
        client_status
        client_last_activity_timestamp
        client_comment
        creation_date
        edit_date
        talanto_uid
        money_spent
        money_filled
        source
        responsible_manager
        course
        responsible_admin
        gender
        tmp_talanto_card_no
        tmp_talanto_office
        tmp_talanto_client_type
        tmp_talanto_course_1
        tmp_talanto_course_2
        tmp_talanto_course_3
        tmp_talanto_manager
        tmp_talanto_has_card
     * @param int $client_id
     * @param int $site_id
     * @return object | bool
     * object with requested client information or false if client_id is not found
     */
    public function get_client_info($q_select,$client_id,$site_id=site_id) {
        if(!isset($this->get_client_info_ar[$client_id][$q_select])) {
            try {
                $stm = $this->uFunc->pdo('obooking')->prepare("SELECT 
                $q_select
                FROM 
                clients
                WHERE 
                client_id=:client_id AND
                site_id=:site_id
                ");
                $stm->bindParam(':client_id', $client_id, PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                $stm->execute();

                $this->get_client_info_ar[$client_id][$q_select]=$stm->fetch(PDO::FETCH_OBJ);
            } catch (PDOException $e) {$this->uFunc->error('1588158644'/*.$e->getMessage()*/,1);}
        }
        return $this->get_client_info_ar[$client_id][$q_select];
    }
    private $get_client_longest_card_ar;
    public function get_client_longest_card($client_id,$site_id=site_id) {
        if(!isset($this->get_client_longest_card_ar[$client_id])) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm = $this->uFunc->pdo("obooking")->prepare("SELECT
                rec_id,
                card_type_id,
                card_number,
                valid_thru,
                start_date
                FROM 
                clients_cards
                WHERE 
                client_id=:client_id AND
                site_id=:site_id
                ORDER BY valid_thru DESC 
                LIMIT 1
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':client_id', $client_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */$this->get_client_longest_card_ar[$client_id]=$stm->fetch(PDO::FETCH_OBJ);
            } catch (PDOException $e) {$this->uFunc->error('obooking common 90'/*.$e->getMessage()*/,1);}
        }
        return $this->get_client_longest_card_ar[$client_id];
    }
    public function get_rec_clients_info($q_select,$rec_id,$site_id=site_id) {
        try {
            $stm = $this->uFunc->pdo('obooking')->prepare("SELECT 
            $q_select
            FROM 
            clients
            JOIN
            records_clients
            ON
            clients.client_id=records_clients.client_id AND
            clients.site_id=records_clients.site_id
            JOIN
            records
            ON
            records_clients.rec_id=records.rec_id AND
            records_clients.site_id=records.site_id
            WHERE 
            records.rec_id=:rec_id AND
            clients.site_id=:site_id
            ");
            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            $stm->bindParam(':rec_id', $rec_id, PDO::PARAM_INT);
            $stm->execute();

            return $stm;
        } catch (PDOException $e) {$this->uFunc->error('1581928988'/*.$e->getMessage()*/,1);}
        return 0;
    }
    public function get_rec_clients($rec_id,$site_id=site_id) {
        try {
            $stm = $this->uFunc->pdo('obooking')->prepare('SELECT 
            client_id,
            status, 
            trial
            FROM 
            records_clients
            WHERE 
            rec_id=:rec_id AND
            site_id=:site_id
            ');
            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            $stm->bindParam(':rec_id', $rec_id, PDO::PARAM_INT);
            $stm->execute();

            return $stm;
        } catch (PDOException $e) {$this->uFunc->error('1586938048'/*.$e->getMessage()*/,1);}
        return false;
    }
    private $get_client_subscription_classes_left_ar;
    public function get_client_subscription_classes_left($client_id,$site_id=site_id) {
        if(!isset($this->get_client_subscription_classes_left_ar)) $this->get_client_subscription_classes_left_ar=array();
        if(!isset($this->get_client_subscription_classes_left_ar[$site_id])) $this->get_client_subscription_classes_left_ar[$site_id]=array();
        if(!isset($this->get_client_subscription_classes_left_ar[$site_id][$client_id])) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
                SUM(visits_left) AS number 
                FROM 
                clients_subscriptions 
                WHERE
                valid_thru>=:now AND
                client_id=:client_id AND
                site_id=:site_id
                ");
                $now=time();
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':now', $now,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':client_id', $client_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                /** @noinspection PhpUndefinedMethodInspection */
                $qr=$stm->fetch(PDO::FETCH_OBJ);
                $this->get_client_subscription_classes_left_ar[$site_id][$client_id]=(int)$qr->number;
            }
            catch(PDOException $e) {$this->uFunc->error('110'/*.$e->getMessage()*/);}
        }
        return $this->get_client_subscription_classes_left_ar[$site_id][$client_id];
    }

    /**
     * Creates new client with provided client name and returns new client_id
     * @param string $client_name
     * @param int $site_id
     * @return int
     * client_id
     */
    public function create_new_client($client_name,$site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("INSERT INTO 
            clients (
            site_id, 
            client_name
            ) 
            VALUES (
            :site_id,
            :client_name
            )");
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->bindParam(':client_name', $client_name,PDO::PARAM_STR);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1589230554'/*.$e->getMessage()*/,1);}

        return (int)$this->uFunc->pdo("obooking")->lastInsertId();
    }

    /**
     * Updates client information provided in client_data assoc array (at least one of following fields):

     * - $client_data=[
     *  - 'client_name'=>'client_name',
     *  - 'client_lastname'=>'client_lastname',
     *  - 'client_birthdate'=>'client_birthday',
     *  - 'client_phone'=>'client_phone',
     *  - 'client_phone2'=>'client_phone2',
     *  - 'client_email'=>'client_email',
     *  - 'client_status'=>'client_status',
     *  - 'client_comment'=>'client_comment',
     *  - 'user_id'=>'user_id',
     * - ];

     * @param int $client_id
     * @param array $client_data
     * @param int $site_id
     */
    public function updateClient($client_id,$client_data,$site_id=site_id) {
//        $client_data=[
//            'client_name'=>'client_name',
//            'client_lastname'=>'client_lastname',
//            'client_birthdate'=>'client_birthday',
//            'client_phone'=>'client_phone',
//            'client_phone2'=>'client_phone2',
//            'client_email'=>'client_email',
//            'client_status'=>'client_status',
//            'client_comment'=>'client_comment',
//            'user_id'=>'user_id',
//        ];
        $fieldNameToTypeAr=[
            'client_name'=>PDO::PARAM_STR,
            'client_lastname'=>PDO::PARAM_STR,
            'client_birthdate'=>PDO::PARAM_STR,
            'client_phone'=>PDO::PARAM_STR,
            'client_phone2'=>PDO::PARAM_STR,
            'client_email'=>PDO::PARAM_STR,
            'client_status'=>PDO::PARAM_INT,
            'client_comment'=>PDO::PARAM_STR,
            'user_id'=>PDO::PARAM_INT,
        ];

        /** @noinspection SqlWithoutWhere */
        $query='UPDATE
            madmakers_obooking.clients
            SET ';
        foreach ($client_data as $field=>$value) {
            $query.=" $field=:$field, ";
        }
        $query.=' 
        client_id=:client_id
        WHERE
            client_id=:client_id AND
            site_id=:site_id
            ';

        try {
            $stm=$this->uFunc->pdo('obooking')->prepare($query);

            $fieldsNameToValueAr=[];

            foreach ($client_data as $fieldName=>$fieldValue) {
                $fieldsNameToValueAr[$fieldName]=$fieldValue;
                $stm->bindParam(":$fieldName", $fieldsNameToValueAr[$fieldName],$fieldNameToTypeAr[$fieldName]);
            }

            $stm->bindParam(':client_id', $client_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1589231035'/*.$e->getMessage()*/);}
    }

    public function delete_client($client_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("DELETE FROM 
            clients 
            WHERE 
                  client_id=:client_id AND
                  site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':client_id', $client_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('obooking common 140'/*.$e->getMessage()*/,1);}
    }
    public function clients_list($show_select_btn=0,$site_id=site_id) {
        $clients_stm=$this->get_clients('client_id,client_name,client_lastname,client_phone,client_email,client_balance,user_id',$site_id);
        ?>
        <div class="input-group">
            <!--suppress HtmlFormInputWithoutLabel -->
            <input type="text" id="obooking_clients_filter" class="form-control" placeholder="Фильтр" onkeyup="obooking_inline_edit.clients_filter()">
            <span class="input-group-btn">
                <button class="btn btn-default" type="button" onclick="obooking_inline_edit.clients_filter()"><span class="icon-search"></span></button>
            </span>
        </div>
        <table class="table table-condensed table-hover">
            <?$cur_timestamp=time();
            while($client=$clients_stm->fetch(PDO::FETCH_OBJ)) {
                $user_id=(int)$client->user_id;
                $has_card=0;
                if($card_data=$this->get_client_longest_card($client->client_id,$site_id)) {
                    $has_card=1;
                    $card_type=$this->card_type_id2name($card_data->card_type_id);
                }
                else {
                    $card_data=new stdClass();
                    $card_data->card_number= '';
                    $card_type= '';
                    $card_data->start_date=0;
                    $card_data->valid_thru=0;
                }
                $classes_left=$this->get_client_subscription_classes_left($client->client_id,$site_id);

                if($show_select_btn) {
                    $onclickText = 'obooking_calendar.record_editor_add_client(' . $client->client_id . ',\'' . $client->client_balance . '\',\'' . $classes_left . '\',' . $has_card . ',\'' . $card_type . '\',\'' . $card_data->card_number . '\',' . $card_data->start_date . ',' . $card_data->valid_thru . ')';
                }
                else {
                    $onclickText = 'obooking_inline_edit.edit_client_init(' . $client->client_id . ')';
                }
                ?>
                <tr id="obooking_client_row_<?=$client->client_id?>" class="obooking_client_row">
                    <td class="client_user_id"><?php
                        if($user_id) {?><a target="_blank" href="/uAuth/profile/<?=$user_id?>"><span class="icon-user"></span></a><?}
                        ?></td>
                    <td onclick="<?=$onclickText?>" class="client_name"><?=$client->client_name?></td>
                    <td onclick="<?=$onclickText?>" class="client_lastname"><?=$client->client_lastname?></td>
                    <td onclick="<?=$onclickText?>" class="client_phone"><?=$client->client_phone?></td>
                    <td onclick="<?=$onclickText?>" class="client_email"><?=$client->client_email?></td>
                    <td onclick="<?=$onclickText?>" <?php
                    if($has_card) {
                        if($cur_timestamp>=$card_data->valid_thru) {
                            print ' class="bg-danger" ';
                        }
                        else {
                            print ' class="bg-success" ';
                        }
                    }
                    if(!$has_card&&$classes_left) {
                        print ' class="bg-success" ';
                    }

                    ?>><?if($has_card) {
                            print '<div style="display:inline" id="clients_list_card_'; print $card_data->rec_id; print '">';
                            if($cur_timestamp>=$card_data->valid_thru) {
                                print 'Карта просрочена ';
                            }
                            else {
                                print 'Карта действует до ';
                            }
                            print date('d.m.Y', $card_data->valid_thru);
                            print '</div>';
                        }
                        if($has_card&&$classes_left) {
                            print ' | ';
                        }
                        if($classes_left) {
                            print '<div style="display:inline" './*id="clients_list_subscription_'; print $card_data->rec_id; print '*/'">';
                            print 'Абонемент на '.$classes_left.' посещений';
                            print '</div>';
                        }

                        ?></td>
                    <?if($show_select_btn) {?>
                        <td><button class="btn btn-xs btn-default" onclick="obooking_inline_edit.edit_client_init(<?=$client->client_id?>)"><span class="icon-pencil"></span> Изменить</button></td>
                    <?}?>
                    <td><div class="obooking_clients_buttons"><button type="button" class="btn btn-danger btn-xs" title="Удалить ученика" onclick="obooking_inline_edit.delete_client_confirm(<?=$client->client_id?>)"><span class="icon-cancel"></span></button></div></td>
                </tr>
            <?}?>
        </table>
    <?}

    public function pay_with_subscription(
        $timestamp,
        $client_id,
        $office_id,
        $description,
        $site_id=site_id
    ) {

        //Get any available subscription
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            rec_id
            FROM
            clients_subscriptions
            WHERE
            visits_left>0 AND
            client_id=:client_id AND
            site_id=:site_id AND
            valid_thru>=:now
            ");
            $now=time();
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':now', $now,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':client_id', $client_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) $rec_id=$qr->rec_id;
            else $this->uFunc->error('obooking common 170');
        }
        catch(PDOException $e) {$this->uFunc->error('obooking common 180'/*.$e->getMessage()*/);}

        //reduce visits_lest
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("UPDATE 
            clients_subscriptions
            SET
            visits_left=visits_left-1
            WHERE
            rec_id=:rec_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */
            /** @noinspection PhpUndefinedVariableInspection */$stm->bindParam(':rec_id', $rec_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('obooking common 190'/*.$e->getMessage()*/);}


        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("INSERT INTO 
            clients_balance_history (
            timestamp, 
            client_id, 
            office_id,
            description, 
            amount,
            payment_method,
            site_id
            ) VALUES (
            :timestamp, 
            :client_id, 
            :office_id,
            :description, 
            0,
            100,
            :site_id
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':timestamp', $timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':client_id', $client_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':office_id', $office_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':description', $description,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1581948747'/*.$e->getMessage()*/);}
    }
    public function save_records_history(
        $timestamp,
        $client_id,
        $description,
        $site_id=site_id
    ) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("INSERT INTO 
            clients_records_history (
            timestamp, 
            client_id, 
            description, 
            site_id
            ) VALUES (
            :timestamp, 
            :client_id, 
            :description, 
            :site_id
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':timestamp', $timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':client_id', $client_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':description', $description,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1581929190'/*.$e->getMessage()*/);}

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("UPDATE 
            clients 
            SET
            client_last_activity_timestamp=:client_last_activity_timestamp
            WHERE
            client_id=:client_id AND
            site_id=:site_id
            ");
            $client_last_activity_timestamp=time();
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':client_last_activity_timestamp', $client_last_activity_timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':client_id', $client_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1581948753'/*.$e->getMessage()*/);}


    }

    //Client Balance
    public function save_balance_history(
        $timestamp,
        $client_id,
        $office_id,
        $description,
        $amount,
        $payment_method,
        $site_id=site_id
    ) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("INSERT INTO 
            clients_balance_history (
            timestamp, 
            client_id, 
            office_id,
            description, 
            amount,
            payment_method,
            site_id,
            user_id
            ) VALUES (
            :timestamp, 
            :client_id, 
            :office_id,
            :description, 
            :amount,
            :payment_method,
            :site_id,
            :user_id
            )
            ");
            $user_id=$this->uSes->get_val('user_id');
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':timestamp', $timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':client_id', $client_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':office_id', $office_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':description', $description,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':amount', $amount,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':payment_method', $payment_method,PDO::PARAM_INT);//0 - нал, 1 - карта, 2 - онлайн, 100 - списание со счета
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return (int)$this->uFunc->pdo("obooking")->lastInsertId();
        }
        catch(PDOException $e) {$this->uFunc->error('1581995268'/*.$e->getMessage()*/);}
        return 0;
    }
    public function update_client_balance($client_id,$deposit_amount) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("UPDATE 
            clients
            SET
            client_balance=client_balance+:balance_delta
            WHERE
            client_id=:client_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':balance_delta', $deposit_amount,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':client_id', $client_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('obooking common 150'/*.$e->getMessage()*/);}

        return $deposit_amount;
    }
    public function recalculate_client_balance($client_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            SUM(amount) AS balance
            FROM
            clients_balance_history
            WHERE
            client_id=:client_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':client_id', $client_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1581998529'/*.$e->getMessage()*/,1);}
        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(1581998519,1);
        $balance=$qr->balance;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("UPDATE 
            clients
            SET
            client_balance=:client_balance
            WHERE
            client_id=:client_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':client_balance', $balance,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':client_id', $client_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1581998513'/*.$e->getMessage()*/,1);}

        return $balance;
    }
    public function client_balance_operation_id2data($q_select,$operation_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            $q_select 
            FROM 
            clients_balance_history 
            WHERE 
            id=:id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':id', $operation_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('1581995638'/*.$e->getMessage()*/);}
        return 0;
    }

    /**
     * Searches for client by any field: user_id, email, phone
     *
     * If Client is found PDO statement is returned with following fields:
     * - client_id,
     * - user_id,
     * - client_phone,
     * - client_phone2,
     * - client_email
     *
     * @param int $user_id
     * @param string $client_email
     * @param string $client_phone
     * @param int $site_id
     * @return bool|\PDOStatement
     */
    public function getClientByUserIdOrEmailOrPhone($user_id, $client_email, $client_phone, $site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo('obooking')->prepare("SELECT
            client_id,
            user_id,
            client_name,
            client_lastname,
            client_phone,
            client_phone2,
            client_email
            FROM
            clients
            WHERE
            (
                (user_id=:user_id AND user_id!=0) OR
                (client_email=:client_email AND client_email!='') OR
                (client_phone2=:client_phone AND client_phone2!='') OR
                (client_phone=:client_phone AND client_phone!='')
            ) AND
            site_id=:site_id
            ");
            $stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            $stm->bindParam(':client_email', $client_email,PDO::PARAM_STR);
            $stm->bindParam(':client_phone', $client_phone,PDO::PARAM_STR);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1589231931'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedVariableInspection */
        return $stm;
    }

    /**
     * Assigns user to client
     * @param int $user_id
     * @param int $client_id
     * @param int $site_id
     */
    private function assignUserToClient($user_id, $client_id, $site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo('obooking')->prepare('UPDATE
            clients
            SET
            user_id=:user_id
            WHERE
            client_id=:client_id AND
            site_id=:site_id
            ');
            $stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            $stm->bindParam(':client_id', $client_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1589211672'/*.$e->getMessage()*/);}

    }

    //RECORDS IN CALENDAR
    public function get_rec_types($q_select="rec_type_id",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            $q_select
            FROM 
            rec_types 
            WHERE 
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('obooking common 230'/*.$e->getMessage()*/,1);}
        return 0;
    }
    private $get_rec_type_info_ar;
    public function get_rec_type_info($q_select,$rec_type_id,$site_id=site_id) {
        if(!isset($this->get_rec_type_info_ar[$rec_type_id][$q_select])) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm = $this->uFunc->pdo("obooking")->prepare("SELECT 
                $q_select
                FROM 
                rec_types
                WHERE 
                      rec_type_id=:rec_type_id AND
                      site_id=:site_id
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':rec_type_id', $rec_type_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */ $this->get_rec_type_info_ar[$rec_type_id][$q_select]=$stm->fetch(PDO::FETCH_OBJ);
            } catch (PDOException $e) {$this->uFunc->error('obooking common 240'/*.$e->getMessage()*/,1);}
        }
        return $this->get_rec_type_info_ar[$rec_type_id][$q_select];
    }
    private $get_record_info_ar;
    public function get_record_info($q_select,$rec_id,$site_id=site_id) {
        if(!isset($this->get_record_info_ar[$rec_id][$q_select])) {
            try {
                $stm = $this->uFunc->pdo('obooking')->prepare("SELECT 
                $q_select
                FROM 
                records
                WHERE 
                rec_id=:rec_id AND
                site_id=:site_id
                ");
                $stm->bindParam(':rec_id', $rec_id, PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                $stm->execute();

                $this->get_record_info_ar[$rec_id][$q_select]=$stm->fetch(PDO::FETCH_OBJ);
            } catch (PDOException $e) {$this->uFunc->error('1586901847'/*.$e->getMessage()*/,1);}
        }
        return $this->get_record_info_ar[$rec_id][$q_select];
    }
    public function create_new_rec_type($rec_type_name,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("INSERT INTO 
            rec_types (
                      site_id, 
                      rec_type_name
                      ) 
            VALUES (
                    :site_id,
                    :rec_type_name
            )");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':rec_type_name', $rec_type_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return (int)$this->uFunc->pdo("obooking")->lastInsertId();
        }
        catch(PDOException $e) {$this->uFunc->error('obooking common 260'/*.$e->getMessage()*/,1);}
        return 0;
    }
    public function delete_rec_type($rec_type_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("DELETE FROM 
            rec_types 
            WHERE 
                  rec_type_id=:rec_type_id AND
                  site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':rec_type_id', $rec_type_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('obooking common 270'/*.$e->getMessage()*/,1);}
    }
    public function delete_record($rec_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("DELETE FROM 
            records 
            WHERE 
            rec_id=:rec_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':rec_id', $rec_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('obooking common 280'/*.$e->getMessage()*/,1);}
    }
    public function rec_types_list($show_select_btn=0,$site_id=site_id) {
        $rec_types_stm=$this->get_rec_types("rec_type_name,rec_type_id,rec_type_price,rec_type_price_without_card,rec_type_duration",$site_id);?>
        <div class="input-group">
            <!--suppress HtmlFormInputWithoutLabel -->
            <input type="text" id="obooking_rec_types_filter" class="form-control" placeholder="Фильтр" onkeyup="obooking_inline_edit.rec_types_filter()">
            <span class="input-group-btn">
                <button class="btn btn-default" type="button" onclick="obooking_inline_edit.rec_types_filter()"><span class="icon-search"></span></button>
            </span>
        </div>
        <table class="table table-condensed table-hover">
            <tr>
                <th>Название</th>
                <th>Стоимость по карте</th>
                <th>Стоимость без карты</th>
                <th>Длительность</th>
            </tr>
            <?
            /** @noinspection PhpUndefinedMethodInspection */
            while($rec_type=$rec_types_stm->fetch(PDO::FETCH_OBJ)) {
                $duration_minutes=$rec_type->rec_type_duration/60;
                for($hours=0;$duration_minutes>60;$hours++) {
                    $duration_minutes-=60;
                }
                $rec_type_duration=$hours.":".(int)$duration_minutes;?>
                <tr id="obooking_rec_type_row_<?=$rec_type->rec_type_id?>" class="obooking_rec_type_row">
                    <td onclick="<?if($show_select_btn) {?>obooking_calendar.record_editor_select_rec_type(<?=$rec_type->rec_type_id?>,<?=$rec_type->rec_type_price?>,<?=$rec_type->rec_type_price_without_card?>,'<?=$rec_type_duration?>');<?} else {?>obooking_inline_edit.edit_rec_type_init(<?=$rec_type->rec_type_id?>);<?}?>" class="rec_type_name"><?=$rec_type->rec_type_name?></td>
                    <td onclick="<?if($show_select_btn) {?>obooking_calendar.record_editor_select_rec_type(<?=$rec_type->rec_type_id?>,<?=$rec_type->rec_type_price?>,<?=$rec_type->rec_type_price_without_card?>,'<?=$rec_type_duration?>');<?} else {?>obooking_inline_edit.edit_rec_type_init(<?=$rec_type->rec_type_id?>);<?}?>" class="rec_type_price"><?=$rec_type->rec_type_price?></td>
                    <td onclick="<?if($show_select_btn) {?>obooking_calendar.record_editor_select_rec_type(<?=$rec_type->rec_type_id?>,<?=$rec_type->rec_type_price?>,<?=$rec_type->rec_type_price_without_card?>,'<?=$rec_type_duration?>');<?} else {?>obooking_inline_edit.edit_rec_type_init(<?=$rec_type->rec_type_id?>);<?}?>" class="rec_type_price_without_card"><?=$rec_type->rec_type_price_without_card?></td>
                    <td onclick="<?if($show_select_btn) {?>obooking_calendar.record_editor_select_rec_type(<?=$rec_type->rec_type_id?>,<?=$rec_type->rec_type_price?>,<?=$rec_type->rec_type_price_without_card?>,'<?=$rec_type_duration?>');<?} else {?>obooking_inline_edit.edit_rec_type_init(<?=$rec_type->rec_type_id?>);<?}?>" class="rec_type_duration"><?=$rec_type_duration?>
                    </td>
                    <?if($show_select_btn) {?>
                        <td><button class="btn btn-xs btn-default" onclick="obooking_inline_edit.edit_rec_type_init(<?=$rec_type->rec_type_id?>)"><span class="icon-pencil"></span> Изменить</button></td>
                    <?}?>
                    <td><div class="obooking_rec_types_buttons"><button type="button" class="btn btn-danger btn-xs" title="Удалить тип занятия" onclick="obooking_inline_edit.delete_rec_type_confirm(<?=$rec_type->rec_type_id?>)"><span class="icon-cancel"></span></button></div></td>
                </tr>
            <?}?>
        </table>
    <?}
    public function time_is_free($class_id,$start_time,$duration,$cur_rec_id=0) {
        $end_time=$start_time+$duration;
        try {
            $stm=$this->uFunc->pdo('obooking')->prepare('SELECT
            rec_id,
            timestamp,
            duration
            FROM 
            records 
            WHERE 
            class_id=:class_id AND
            rec_id!=:rec_id AND (
                timestamp>=:start_time AND 
                timestamp<:end_time OR
                (timestamp+duration)>:start_time AND 
                (timestamp+duration)<=:end_time OR
                :start_time>=timestamp AND
                :start_time<(timestamp+duration) OR
                :end_time>timestamp AND
                :end_time<=(timestamp+duration)
            ) 
            LIMIT 1
            ');
            $stm->bindParam(':class_id', $class_id,PDO::PARAM_INT);
            $stm->bindParam(':rec_id', $cur_rec_id,PDO::PARAM_INT);
            $stm->bindParam(':start_time', $start_time,PDO::PARAM_INT);
            $stm->bindParam(':end_time', $end_time,PDO::PARAM_INT);
            $stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
//            if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
//                echo $qr->rec_id;
//                echo "\n";
//                echo date("d.m.Y H:i",$qr->timestamp);
//                echo "\n";
//                echo date("d.m.Y H:i",$qr->duration);
//                exit;
//            }
            return !$stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('obooking common 290'/*.$e->getMessage()*/);}
        return 1;
    }
    public function copy_record($srcRecId, $destTimestamp, $destClassId, $checkTimeOccupation=true, $copyAssignedClients=true, $siteId = site_id) {
        if (!($recInfo = $this->get_record_info('
            rec_type,
            office_id,
            class_id,
            manager_id,
            timestamp,
            duration,
            notes,
            price,
            price_without_card,
            payment_status',
            $srcRecId,
            $siteId
        ))) {
            return false;
        }
        if($checkTimeOccupation) {
            $recInfo=$this->get_record_info('duration',$srcRecId,$siteId);
            if(!$this->time_is_free($destClassId,$destTimestamp,$recInfo->duration,$srcRecId)) {
                return false;
            }
        }

        $fields=array(
            'rec_type'=>$recInfo->rec_type,
            'office_id'=>$recInfo->office_id,
            'class_id'=>$destClassId,
            'manager_id'=>$recInfo->manager_id,
            'timestamp'=>$destTimestamp,
            'duration'=>$recInfo->duration,
            'notes'=>$recInfo->notes,
            'price'=>$recInfo->price,
            'price_without_card'=>$recInfo->price_without_card,
            'payment_status'=>$recInfo->payment_status
        );
        $recId=$this->create_new_record($fields,$siteId);


        if($copyAssignedClients) {
            $clients_stm = $this->get_rec_clients($srcRecId,$siteId);

            if ($rec_type_info = $this->get_rec_type_info('rec_type_name',$recInfo->rec_type)) {
                $rec_type_name = $rec_type_info->rec_type_name;
            } else {
                $rec_type_name = '';
            }

            if ($office_info = $this->get_office_info('office_name',$recInfo->office_id)) {
                $office_name = $office_info->office_name;
            } else {
                $office_name = '';
            }

            if ($class_info = $this->get_class_info('class_name',$recInfo->class_id)) {
                $class_name = $class_info->class_name;
            } else {
                $class_name = '';
            }

            if (
            $manager_info = $this->get_manager_info('manager_name,manager_lastname',$recInfo->manager_id)) {
                $manager_name =
                    $manager_info->manager_name . ' ' .$manager_info->manager_lastname;
            } else {
                $manager_name = '';
            }

            $description_end = ': ' .$rec_type_name . '<br>Филиал: ' .$office_name . '<br>Класс: ' .$class_name . '<br>Наставник: ' .$manager_name . '<br>Время занятия: ' .date('d.m.Y H:i', $destTimestamp);

            while ($client = $clients_stm->fetch(PDO::FETCH_OBJ)) {
                $client_id = (int) $client->client_id;
                $status = (int) $client->status;
                $trial = (int) $client->trial;

                $this->assign_client_to_record($recId,$client_id,$status,$trial,$siteId);

                $this->save_records_history(
                    time(),
                    $client_id,
                    'Записан(а) на занятие' . $description_end
                );
            }
        }

        return $recId;
    }
    public function create_new_record($fields,$siteId=site_id) {
//        $fields=array(
//            'rec_type'=>$rec_type,
//            'office_id'=>$office_id,
//            'class_id'=>$class_id,
//            'manager_id'=>$manager_id,
//            'timestamp'=>$timestamp,
//            'duration'=>$duration,
//            'notes'=>$notes,
//            'price'=>$price,
//            'price_without_card'=>$price_without_card
//        );

        if(!array_key_exists('rec_type',$fields)) {return 0;}
        if(!array_key_exists('office_id',$fields)) {return 0;}
        if(!array_key_exists('class_id',$fields)) {return 0;}
        if(!array_key_exists('manager_id',$fields)) {return 0;}
        if(!array_key_exists('timestamp',$fields)) {return 0;}
        if(!array_key_exists('duration',$fields)) {return 0;}
        if(!array_key_exists('notes',$fields)) {return 0;}
        if(!array_key_exists('price',$fields)) {return 0;}
        if(!array_key_exists('price_without_card',$fields)) {return 0;}

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
            $stm->bindParam('site_id',$siteId,PDO::PARAM_INT);
            $stm->bindParam('rec_type',$fields['rec_type'],PDO::PARAM_INT);
            $stm->bindParam('office_id',$fields['office_id'],PDO::PARAM_INT);
            $stm->bindParam('class_id',$fields['class_id'],PDO::PARAM_INT);
            $stm->bindParam('manager_id',$fields['manager_id'],PDO::PARAM_INT);
            $stm->bindParam('timestamp',$fields['timestamp'],PDO::PARAM_INT);
            $stm->bindParam('duration',$fields['duration'],PDO::PARAM_INT);
            $stm->bindParam('notes',$fields['notes'],PDO::PARAM_STR);
            $stm->bindParam('price',$fields['price'],PDO::PARAM_STR);
            $stm->bindParam('price_without_card',$fields['price_without_card'],PDO::PARAM_STR);
            $stm->execute();
        } catch (PDOException $e) {$this->uFunc->error('1586935359' /*.$e->getMessage()*/, 1);}

        return $this->uFunc->pdo('obooking')->LastInsertId();
    }
    public function assign_client_to_record($rec_id,$client_id,$status=-1,$trial=0,$site_id=site_id) {
        try {
            $stm = $this->uFunc->pdo('obooking')->prepare('INSERT INTO 
            records_clients (
             rec_id, 
             client_id, 
             status, 
             trial,
             site_id
             ) VALUES (
             :rec_id, 
             :client_id, 
             :status, 
             :trial,
             :site_id
             )
            ');
            $stm->bindParam(':rec_id',$rec_id,PDO::PARAM_INT);
            $stm->bindParam(':client_id',$client_id,PDO::PARAM_INT);
            $stm->bindParam(':status',$status,PDO::PARAM_INT);
            $stm->bindParam(':trial',$trial,PDO::PARAM_INT);
            $stm->bindParam(':site_id',$site_id,PDO::PARAM_INT);
            $stm->execute();
        } catch (PDOException $e) {$this->uFunc->error('1586939173' /*.$e->getMessage()*/);}
    }


    //OFFICES
    public function get_offices($q_select="office_id,office_name",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            $q_select
            FROM 
            offices 
            WHERE 
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('obooking common 300'/*.$e->getMessage()*/,1);}
        return 0;
    }
    private $get_office_info_ar;
    public function get_office_info($q_select,$office_id,$site_id=site_id) {
        if(!isset($this->get_office_info_ar[$office_id][$q_select])) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm = $this->uFunc->pdo("obooking")->prepare("SELECT 
                $q_select
                FROM 
                offices
                WHERE 
                      office_id=:office_id AND
                      site_id=:site_id
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':office_id', $office_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */$this->get_office_info_ar[$office_id][$q_select]=$stm->fetch(PDO::FETCH_OBJ);
            } catch (PDOException $e) {$this->uFunc->error('obooking common 310'/*.$e->getMessage()*/,1);}
        }
        return $this->get_office_info_ar[$office_id][$q_select];
    }
    public function create_new_office($office_name,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("INSERT INTO 
            offices (
                      site_id, 
                      office_name
                      ) 
            VALUES (
                    :site_id,
                    :office_name
            )");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':office_name', $office_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return (int)$this->uFunc->pdo("obooking")->lastInsertId();
        }
        catch(PDOException $e) {$this->uFunc->error('obooking common 320'/*.$e->getMessage()*/,1);}
        return 0;
    }
    public function delete_office($office_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("DELETE FROM 
            offices 
            WHERE 
                  office_id=:office_id AND
                  site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':office_id', $office_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('obooking common 330'/*.$e->getMessage()*/,1);}
    }
    public function offices_list($site_id=site_id) {
        $offices_stm=$this->get_offices("office_id,office_name",$site_id);?>
        <div class="input-group">
            <!--suppress HtmlFormInputWithoutLabel -->
            <input type="text" id="obooking_offices_filter" class="form-control" placeholder="Фильтр" onkeyup="obooking_inline_edit.offices_filter()">
            <span class="input-group-btn">
                <button class="btn btn-default" type="button" onclick="obooking_inline_edit.offices_filter()"><span class="icon-search"></span></button>
            </span>
        </div>

        <table class="table table-striped table-condensed" id="obooking_offices_list">
            <?/** @noinspection PhpUndefinedMethodInspection */
            while($office=$offices_stm->fetch(PDO::FETCH_OBJ)) {?>
                <tr id="obooking_office_row_<?=$office->office_id?>" class="obooking_office_row">
                    <td onclick="obooking_inline_edit.edit_office_init(<?=$office->office_id?>)" class="office_name"><?=$office->office_name?></td>
                    <td><!--suppress HtmlUnknownTarget -->
                        <a class="schedule_link" href="obooking/calendar?office=<?=$office->office_id?>" target="_blank">Расписание</a></td>
                    <td><!--suppress HtmlUnknownTarget -->
                        <a class="schedule_link" href="javascript:void(0)" onclick="obooking_inline_edit.get_office_billing_history(<?=$office->office_id?>,1)">Финансы</a></td>
                    <td><div class="obooking_offices_buttons"><button type="button" class="btn btn-danger btn-xs obooking_offices_delete_btn" title="Удалить филиал" onclick="obooking_inline_edit.delete_office_confirm(<?=$office->office_id?>)"><span class="icon-cancel"></span></button></div></td>
                </tr>
            <?}?>
        </table>
    <?}
    public function get_first_office_id($site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            office_id 
            FROM 
            offices
            WHERE 
            site_id=:site_id
            ORDER BY
            office_id
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('obooking common 340'/*.$e->getMessage()*/,1);}
        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$office=$stm->fetch(PDO::FETCH_OBJ)) {
            $this->create_new_office("Office",$site_id);
            return $this->get_first_office_id($site_id);
        }
        return (int)$office->office_id;
    }
    private $office_id2office_name_ar;
    public function office_id2office_name($office_id,$site_id=site_id) {
        if(!isset($this->office_id2office_name_ar[$site_id]))$this->office_id2office_name_ar[$site_id]=[];
        if(!isset($this->office_id2office_name_ar[$site_id][$office_id])) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
                office_name 
                FROM 
                offices 
                WHERE
                office_id=:office_id AND
                site_id=:site_id
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':office_id', $office_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if($qr=$stm->fetch(PDO::FETCH_OBJ)) $this->office_id2office_name_ar[$site_id][$office_id]=$qr->office_name;
                else return $office_id;
            }
            catch(PDOException $e) {$this->uFunc->error('obooking common 350'/*.$e->getMessage()*/);}
        }
        return $this->office_id2office_name_ar[$site_id][$office_id];
    }


    //ORDERS
    private function get_new_order_id() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            order_id 
            FROM 
            orders 
            ORDER BY 
            order_id DESC
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr->order_id+1;
        }
        catch(PDOException $e) {$this->uFunc->error('obooking common 355'/*.$e->getMessage()*/);}
        return 1;
    }
    public function register_new_temp_order($site_id=site_id) {
        $order_id=$this->get_new_order_id();
        $timestamp=time();
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("INSERT INTO 
            orders
            (
            order_id,
            timestamp,
            site_id
            ) VALUES (
            :order_id,
            :timestamp,
            :site_id
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':order_id', $order_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':timestamp', $timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('obooking common 360'/*.$e->getMessage()*/);}

        return $order_id;
    }

    /**
     * Retrieves order with $order_id info passed in $query
     *
     * Available fields in query:
     * - order_id
     * - site_id
     * - office_id
     * - trial_date
     * - client_name
     * - phone
     * - email
     * - status_id
     * - manager_id
     * - comment
     * - source_id
     * - sys_status
     * - timestamp
     * - next_contact_date
     * - how_did_find_out_id
     * - user_id
     *
     * @param int $order_id
     * @param string $query
     * @param int $site_id
     * @return mixed
     */
    public function order_id2data($order_id,$query="order_id",$site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            $query 
            FROM 
            orders 
            WHERE
            order_id=:order_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':order_id', $order_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1580860541'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedVariableInspection */
        return $stm->fetch(PDO::FETCH_OBJ);
    }

    //ORDER SOURCES
    public function get_order_sources($q_select="source_id",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            $q_select
            FROM 
            order_sources 
            WHERE 
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('1581888869'/*.$e->getMessage()*/);}
        return 0;
    }
    private $source_id2source_name_ar;
    public function source_id2source_name($source_id,$site_id=site_id) {
        if(!isset($this->source_id2source_name_ar[$site_id]))$this->source_id2source_name_ar[$site_id]=[];
        if(!isset($this->source_id2source_name_ar[$site_id][$source_id])) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
                source_name 
                FROM 
                order_sources 
                WHERE
                source_id=:source_id AND
                site_id=:site_id
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':source_id', $source_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if($qr=$stm->fetch(PDO::FETCH_OBJ)) $this->source_id2source_name_ar[$site_id][$source_id]=$qr->source_name;
                else return $source_id;
            }
            catch(PDOException $e) {$this->uFunc->error('obooking common 1581888877'/*.$e->getMessage()*/);}
        }
        return $this->source_id2source_name_ar[$site_id][$source_id];
    }
    public function source_id2data($source_id,$query="source_id",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            $query 
            FROM 
            order_sources 
            WHERE
            source_id=:source_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':source_id', $source_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('obooking common 370'/*.$e->getMessage()*/);}
        return 0;
    }

    //ORDER how_did_find_outS
    /**
     * Gets all orders of selected files - retrieves only fields in $q_select
     * @param string $q_select
     * @param int $site_id
     * @return bool|int|\PDOStatement
     */
    public function get_order_how_did_find_outs($q_select="how_did_find_out_id",$site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            $q_select
            FROM 
            order_how_did_find_outs 
            WHERE 
            site_id=:site_id
            ");
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('1589201512'/*.$e->getMessage()*/);}
        return 0;
    }
    private $how_did_find_out_id2how_did_find_out_name_ar;

    /**
     * Converts Order's $how_did_find_out_id to $how_did_find_out_name
     * @param int $how_did_find_out_id
     * @param int $site_id
     * @return string
     */
    public function how_did_find_out_id2how_did_find_out_name($how_did_find_out_id,$site_id=site_id) {
        if(!isset($this->how_did_find_out_id2how_did_find_out_name_ar[$site_id])) {
            $this->how_did_find_out_id2how_did_find_out_name_ar[$site_id] = [];
        }
        if(!isset($this->how_did_find_out_id2how_did_find_out_name_ar[$site_id][$how_did_find_out_id])) {
            try {
                $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
                how_did_find_out_name 
                FROM 
                order_how_did_find_outs 
                WHERE
                how_did_find_out_id=:how_did_find_out_id AND
                site_id=:site_id
                ");
                $stm->bindParam(':how_did_find_out_id', $how_did_find_out_id,PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();

                if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                    $this->how_did_find_out_id2how_did_find_out_name_ar[$site_id][$how_did_find_out_id] = $qr->how_did_find_out_name;
                }
                else {
                    return $how_did_find_out_id;
                }
            }
            catch(PDOException $e) {$this->uFunc->error('1589201532'/*.$e->getMessage()*/);}
        }
        return $this->how_did_find_out_id2how_did_find_out_name_ar[$site_id][$how_did_find_out_id];
    }

    /**
     * Retrieves fields about order in $query from db
     * @param int $how_did_find_out_id
     * @param string $query
     * @param int $site_id
     * @return bool|object
     */
    public function how_did_find_out_id2data($how_did_find_out_id,$query="how_did_find_out_id",$site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            $query 
            FROM 
            order_how_did_find_outs 
            WHERE
            how_did_find_out_id=:how_did_find_out_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':how_did_find_out_id', $how_did_find_out_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('1589201560'/*.$e->getMessage()*/);}
        return false;
    }

    //ORDER STATUSESUSES
    public function status_id2data($status_id,$query="status_id",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            $query 
            FROM 
            order_statuses 
            WHERE
            status_id=:status_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':status_id', $status_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('obooking common 380'/*.$e->getMessage()*/);}
        return 0;
    }
    public function get_order_statuses($q_select="status_id",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            $q_select
            FROM 
            order_statuses 
            WHERE 
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('1581678759'/*.$e->getMessage()*/);}
        return 0;
    }
    private $status_id2status_name_ar;
    public function status_id2status_name($status_id,$site_id=site_id) {
        if(!isset($this->status_id2status_name_ar[$site_id]))$this->status_id2status_name_ar[$site_id]=[];
        if(!isset($this->status_id2status_name_ar[$site_id][$status_id])) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
                status_name 
                FROM 
                order_statuses 
                WHERE
                status_id=:status_id AND
                site_id=:site_id
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':status_id', $status_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if($qr=$stm->fetch(PDO::FETCH_OBJ)) $this->status_id2status_name_ar[$site_id][$status_id]=$qr->status_name;
                else return $status_id;
            }
            catch(PDOException $e) {$this->uFunc->error('1581891044'/*.$e->getMessage()*/);}
        }
        return $this->status_id2status_name_ar[$site_id][$status_id];
    }

    //CLASSES
    public function get_classes($office_id,$q_select="class_id,class_name",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            $q_select
            FROM 
            classes
            WHERE
            office_id=:office_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':office_id', $office_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('obooking common 410'/*.$e->getMessage()*/,1);}
        return 0;
    }
    private $get_class_info_ar;
    public function get_class_info($q_select,$class_id,$site_id=site_id) {
        if(!isset($this->get_class_info_ar[$class_id][$q_select])) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm = $this->uFunc->pdo("obooking")->prepare("SELECT 
                $q_select
                FROM 
                classes
                WHERE 
                      class_id=:class_id AND
                      site_id=:site_id
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':class_id', $class_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */$this->get_class_info_ar[$class_id][$q_select]=$stm->fetch(PDO::FETCH_OBJ);
            } catch (PDOException $e) {$this->uFunc->error('obooking common 420'/*.$e->getMessage()*/,1);}
        }
        return $this->get_class_info_ar[$class_id][$q_select];
    }
    public function create_new_class($office_id,$class_name,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("INSERT INTO 
            classes (
                      site_id,
                     office_id,
                      class_name
                      ) 
            VALUES (
                    :site_id,
                    :office_id,
                    :class_name
            )");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':office_id', $office_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':class_name', $class_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return (int)$this->uFunc->pdo("obooking")->lastInsertId();
        }
        catch(PDOException $e) {$this->uFunc->error('obooking common 430'/*.$e->getMessage()*/,1);}
        return 0;
    }
    public function delete_class($class_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("DELETE FROM 
        classes 
            WHERE 
                  class_id=:class_id AND
                  site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':class_id', $class_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('obooking common 440'/*.$e->getMessage()*/,1);}
    }
    public function classes_list($show_select_btn=0,$site_id=site_id) {
        $q_offices=$this->get_offices("office_id,office_name",$site_id);
        /** @noinspection PhpUndefinedMethodInspection */
        while($office=$q_offices->fetch(PDO::FETCH_OBJ)) {?>
            <h3><a href="javascript:void(0)" onclick="obooking_inline_edit.edit_office_init(<?=$office->office_id?>)"><?=$office->office_name?></a></h3>
            <?$q_classes=$this->get_classes($office->office_id,"class_id,class_name",$site_id);?>
            <table class="table table-striped"><?
                /** @noinspection PhpUndefinedMethodInspection */
                while($class=$q_classes->fetch(PDO::FETCH_OBJ)) {?>
                    <tr id="obooking_class_row_<?=$class->class_id?>" class="obooking_class_row">
                        <td class="hidden office_name"><?=$office->office_name?></td>
                        <td class="hidden office_id"><?=$office->office_id?></td>
                        <td onclick="<?if($show_select_btn) {?>obooking_calendar.record_editor_select_class(<?=$class->class_id?>);<?} else {?>obooking_inline_edit.edit_class_init(<?=$class->class_id?>);<?}?>" class="class_name"><?=$class->class_name?></td>
                        <?if($show_select_btn) {?>
                            <td style="width: 100px;"><button class="btn btn-xs btn-default" onclick="obooking_inline_edit.edit_class_init(<?=$class->class_id?>)"><span class="icon-pencil"></span> Изменить</button></td>
                        <?}?>
                        <td style="width: 100px"><div class="obooking_classes_buttons"><button type="button" class="btn btn-danger btn-xs" title="Удалить класс" onclick="obooking_inline_edit.delete_class_confirm(<?=$class->class_id?>)"><span class="icon-cancel"></span></button></div></td>
                    </tr>
                <?}?>
            </table>
        <?}
    }


    //COURSES
    public function course_id2data($course_id,$query="course_id",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            $query 
            FROM 
            courses 
            WHERE
            course_id=:course_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':course_id', $course_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('obooking common 460'/*.$e->getMessage()*/);}
        return 0;
    }
    public function get_courses($q_select="course_id",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            $q_select
            FROM 
            courses 
            WHERE 
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('1581678858'/*.$e->getMessage()*/);}
        return 0;
    }
    private $course_id2course_name_ar;
    public function course_id2course_name($course_id,$site_id=site_id) {
        if(!isset($this->course_id2course_name_ar[$site_id]))$this->course_id2course_name_ar[$site_id]=[];
        if(!isset($this->course_id2course_name_ar[$site_id][$course_id])) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
                course_name 
                FROM 
                courses 
                WHERE
                course_id=:course_id AND
                site_id=:site_id
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':course_id', $course_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if($qr=$stm->fetch(PDO::FETCH_OBJ)) $this->course_id2course_name_ar[$site_id][$course_id]=$qr->course_name;
                else return $course_id;
            }
            catch(PDOException $e) {$this->uFunc->error('obooking common 1581889578'/*.$e->getMessage()*/);}
        }
        return $this->course_id2course_name_ar[$site_id][$course_id];
    }


    //CARDS
    private $card_type_id2name_ar;
    public function card_type_id2name($card_type_id,$site_id=site_id) {
        $card_type_id=(int)$card_type_id;
        if(!isset($this->card_type_id2name_ar[$card_type_id])) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm = $this->uFunc->pdo("obooking")->prepare("SELECT 
                card_type_name 
                FROM 
                card_types 
                WHERE
                card_type_id=:card_type_id AND
                site_id=:site_id
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':card_type_id', $card_type_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if($qr=$stm->fetch(PDO::FETCH_OBJ)) $this->card_type_id2name_ar[$card_type_id]=$qr->card_type_name;
                else return "";

            } catch (PDOException $e) {$this->uFunc->error('obooking common 470'/*.$e->getMessage()*/);}
        }
        return $this->card_type_id2name_ar[$card_type_id];
    }

    /**
     * Retrieves card types existing on this site
     * @param string $q_select - fields to retrieve. card_type_id     card_type_name     validity    price
     * @param int $site_id
     * @return bool|array
     * Assoc Array
     */
    public function getCardTypes($q_select,$site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            $q_select
            FROM 
            card_types 
            WHERE 
            site_id=:site_id
            ");
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            return $stm->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e) {$this->uFunc->error('1588640916 '/*.$e->getMessage()*/);}
        return false;
    }

    /**
     * Retrieves information about client card by card rec_id
     * @param string $q_select - fields to retrieve. rec_id    card_type_id    card_number    valid_thru    start_date    client_id    site_id
     * @param int $rec_id - client card record id
     * @param int $site_id
     * @return int|mixed
     * Object of client card fields, like result->rec_id
     */
    public function getClientCardInfo($q_select,$rec_id,$site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            $q_select 
            FROM 
            clients_cards 
            WHERE 
            rec_id=:rec_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':rec_id', $rec_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('1588641736 '/*.$e->getMessage()*/);}
        return false;
    }

    private function getNewClientCardRecId() {
        try {
            $stm=$this->uFunc->pdo("obooking")->query("SELECT 
            rec_id 
            FROM 
            clients_cards 
            ORDER BY
            rec_id DESC 
            LIMIT 1
            ");

            if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                return $qr->rec_id + 1;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('1588643671 '/*.$e->getMessage()*/);}
        return 1;
    }

    /**
     * Creates new Client Card
     *
     * @param int $card_type_id
     * @param string $card_number
     * @param int $valid_thru - timestamp
     * @param int $start_date - timestamp
     * @param int $client_id
     * @param int $site_id
     * @return int
     * int rec_id of new Client Card
     */
    public function createNewClientCard($card_type_id,$card_number,$valid_thru,$start_date,$client_id,$site_id=site_id) {
        $rec_id=$this->getNewClientCardRecId();

        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("INSERT INTO clients_cards (
            rec_id,
            card_type_id,
            card_number,
            valid_thru,
            start_date,
            client_id,
            site_id               
            )
            VALUES (
            :rec_id,
            :card_type_id,
            :card_number,
            :valid_thru,
            :start_date,
            :client_id,
            :site_id              
            )
            ");
            $stm->bindParam(':rec_id', $rec_id,PDO::PARAM_INT);
            $stm->bindParam(':card_type_id', $card_type_id,PDO::PARAM_INT);
            $stm->bindParam(':card_number', $card_number,PDO::PARAM_STR);
            $stm->bindParam(':valid_thru', $valid_thru,PDO::PARAM_INT);
            $stm->bindParam(':start_date', $start_date,PDO::PARAM_INT);
            $stm->bindParam(':client_id', $client_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1588643810 '/*.$e->getMessage()*/);}

        return $rec_id;
    }

    /**
     * Saves new values for client's card. Client card record id (rec_id) and site_id should be passed. And key=>value array of fields to update.
     *
     * $fieldsToUpdateAr=[
     * 'card_type_id'=>$card_type_id,
     * 'card_number'=>$card_number,
     * 'valid_thru'=>$valid_thru,
     * 'start_date'=>$start_date
     * ];
     *
     * @param array $fieldsToUpdateAr
     * @param int $rec_id
     * @param int $site_id
     */
    public function updateClientCard($fieldsToUpdateAr,$rec_id,$site_id=site_id) {
        //$fieldsToUpdateAr=[
        //        'card_type_id'=>$card_type_id,
        //        'card_number'=>$card_number,
        //        'valid_thru'=>$valid_thru,
        //        'start_date'=>$start_date
        //];

        $querySetAr=[];
        foreach ($fieldsToUpdateAr as $fieldName=>$fieldValue) {
            $querySetAr[]="$fieldName=:$fieldName";
        }
        $querySetStr=implode(",\n",$querySetAr);

        $fieldNameToTypeAr=[
            'card_type_id'=>PDO::PARAM_INT,
            'card_number'=>PDO::PARAM_STR,
            'valid_thru'=>PDO::PARAM_INT,
            'start_date'=>PDO::PARAM_INT
        ];

        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("UPDATE 
            clients_cards
            SET
            $querySetStr
            WHERE
            rec_id=:rec_id AND
            site_id=:site_id
            ");

            $fieldsNameToValueAr=[];
            foreach ($fieldsToUpdateAr as $fieldName=>$fieldValue) {
                $fieldsNameToValueAr[$fieldName]=$fieldValue;
                $stm->bindParam(":$fieldName", $fieldsNameToValueAr[$fieldName],$fieldNameToTypeAr[$fieldName]);
            }
            $stm->bindParam(':rec_id', $rec_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('55'/*.$e->getMessage()*/);}
    }

    /**
     * Deletes selected client's card by record id
     * @param int $rec_id
     * @param int $site_id
     */
    public function deleteClientCard($rec_id,$site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("DELETE  
            FROM 
            clients_cards
            WHERE 
            rec_id=:rec_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':rec_id', $rec_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1588645854 '/*.$e->getMessage()*/);}
    }


    //SUBSCRIPTIONS
    private $subscription_type_id2name_ar;
    public function subscription_type_id2name($subscription_type_id,$site_id=site_id) {
        $subscription_type_id=(int)$subscription_type_id;
        if(!isset($this->subscription_type_id2name_ar[$subscription_type_id])) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm = $this->uFunc->pdo("obooking")->prepare("SELECT 
                subscription_type_name 
                FROM 
                subscription_types 
                WHERE
                subscription_type_id=:subscription_type_id AND
                site_id=:site_id
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':subscription_type_id', $subscription_type_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if($qr=$stm->fetch(PDO::FETCH_OBJ)) $this->subscription_type_id2name_ar[$subscription_type_id]=$qr->subscription_type_name;
                else return "";

            } catch (PDOException $e) {$this->uFunc->error('obooking common 480'/*.$e->getMessage()*/);}
        }
        return $this->subscription_type_id2name_ar[$subscription_type_id];
    }
    private $subscription_type_id2classes_included_ar;
    public function subscription_type_id2classes_included($subscription_type_id,$site_id=site_id) {
        $subscription_type_id=(int)$subscription_type_id;
        if(!isset($this->subscription_type_id2classes_included_ar[$subscription_type_id])) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm = $this->uFunc->pdo("obooking")->prepare("SELECT 
                classes_included 
                FROM 
                subscription_types 
                WHERE
                subscription_type_id=:subscription_type_id AND
                site_id=:site_id
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':subscription_type_id', $subscription_type_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if($qr=$stm->fetch(PDO::FETCH_OBJ)) $this->subscription_type_id2classes_included_ar[$subscription_type_id]=$qr->classes_included;
                else return "";

            } catch (PDOException $e) {$this->uFunc->error('obooking common 490'/*.$e->getMessage()*/);}
        }
        return $this->subscription_type_id2classes_included_ar[$subscription_type_id];
    }

    /**
     * Retrieves information about client subscription by subscription rec_id
     * @param string $q_select - fields to retrieve. rec_id subscription_type_id    valid_thru    start_date    visits_left    site_id    client_id
     * @param int $rec_id - client subscription record_id
     * @param int $site_id
     * @return int|mixed
     * Object of client subscription fields, like result->rec_id
     */
    public function getClientSubscriptionInfo($q_select,$rec_id,$site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            $q_select 
            FROM 
            clients_subscriptions 
            WHERE 
            rec_id=:rec_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':rec_id', $rec_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('1588626742 '/*.$e->getMessage()*/);}
        return 0;
    }

    /**
     * Retrieves subscription types exists on this site
     * @param string $q_select - fields to retrieve. subscription_type_id     subscription_type_name    validity    price
     * @param int $site_id
     * @return bool|array
     * Assoc Array
     */
    public function getSubscriptionTypes($q_select,$site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            $q_select
            FROM 
            subscription_types 
            WHERE 
            site_id=:site_id
            ");
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            return $stm->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e) {$this->uFunc->error('1588627123 '/*.$e->getMessage()*/);}
        return false;
    }

    /**
     * Save's new values for client's subscription. Client subscription record id (rec_id) and site_id should be passed. And key=>value array of fields to update.
     *
     * $fieldsToUpdateAr=[
     * 'subscription_type_id'=>$subscription_type_id,
     * 'valid_thru'=>$valid_thru,
     * 'start_date'=>$start_date,
     * 'visits_left'=>$visits_left,
     * 'client_id'=>$client_id
     * ];
     *
     * @param array $fieldsToUpdateAr
     * @param int $rec_id
     * @param int $site_id
     */
    public function updateClientSubscription($fieldsToUpdateAr,$rec_id,$site_id=site_id) {
//        $fieldsToUpdateAr=[
//                'subscription_type_id'=>$subscription_type_id,
//                'valid_thru'=>$valid_thru,
//                'start_date'=>$start_date,
//                'visits_left'=>$visits_left,
//                'client_id'=>$client_id
//        ];
        $querySetAr=[];
        foreach ($fieldsToUpdateAr as $fieldName=>$fieldValue) {
            $querySetAr[]="$fieldName=:$fieldName";
        }
        $querySetStr=implode(",\n",$querySetAr);

        $fieldNameToTypeAr=[
            'subscription_type_id'=>PDO::PARAM_INT,
            'valid_thru'=>PDO::PARAM_INT,
            'start_date'=>PDO::PARAM_INT,
            'visits_left'=>PDO::PARAM_INT
        ];

        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("UPDATE
            clients_subscriptions
            SET
            $querySetStr
            WHERE
            rec_id=:rec_id AND
            site_id=:site_id             
            ");
            $fieldsNameToValueAr=[];
            foreach ($fieldsToUpdateAr as $fieldName=>$fieldValue) {
                $fieldsNameToValueAr[$fieldName]=$fieldValue;
                $stm->bindParam(":$fieldName", $fieldsNameToValueAr[$fieldName],$fieldNameToTypeAr[$fieldName]);
            }
            $stm->bindParam(':rec_id', $rec_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1588629358 '.$e->getMessage());}
    }

    /**
     * Deletes selected client's subscription by record id
     * @param int $rec_id
     * @param int $site_id
     */
    public function deleteClientSubscription($rec_id,$site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("DELETE  
            FROM 
            clients_subscriptions
            WHERE 
            rec_id=:rec_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':rec_id', $rec_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1588631229 '/*.$e->getMessage()*/);}
    }

    private function getNewClientSubscriptionRecId() {
        try {
            $stm=$this->uFunc->pdo("obooking")->query("SELECT 
            rec_id 
            FROM 
            clients_subscriptions 
            ORDER BY
            rec_id DESC 
            LIMIT 1
            ");

            if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                return $qr->rec_id + 1;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('1588629925 '/*.$e->getMessage()*/);}
        return 1;
    }

    /**
     * Creates New Client Subscription
     * @param int $subscription_type_id
     * @param int $valid_thru - timestamp
     * @param int $start_date - timestamp
     * @param int $visits_left - number of classes included
     * @param int $client_id
     * @param int $site_id
     * @return int
     */
    public function createNewClientSubscription($subscription_type_id,$valid_thru,$start_date,$visits_left,$client_id,$site_id=site_id) {
        $rec_id=$this->getNewClientSubscriptionRecId();

        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("INSERT INTO clients_subscriptions (
            rec_id,
            subscription_type_id,
            valid_thru,
            start_date,
            visits_left,
            client_id,
            site_id               
            )
            VALUES (
            :rec_id,
            :subscription_type_id,
            :valid_thru,
            :start_date,
            :visits_left,
            :client_id,
            :site_id              
            )
            ");
            $stm->bindParam(':rec_id', $rec_id,PDO::PARAM_INT);
            $stm->bindParam(':subscription_type_id', $subscription_type_id,PDO::PARAM_INT);
            $stm->bindParam(':valid_thru', $valid_thru,PDO::PARAM_INT);
            $stm->bindParam(':start_date', $start_date,PDO::PARAM_INT);
            $stm->bindParam(':visits_left', $visits_left,PDO::PARAM_INT);
            $stm->bindParam(':client_id', $client_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1588629933'/*.$e->getMessage()*/);}

        return $rec_id;
    }

    //COMMON
    public function day_of_week_number2word($number) {
        $number=(int)$number;
        if($number===0) return "Вс";
        elseif($number===1) return "Пн";
        elseif($number===2) return "Вт";
        elseif($number===3) return "Ср";
        elseif($number===4) return "Чт";
        elseif($number===5) return "Пт";
        elseif($number===6) return "Сб";

        return $number;
    }


    public function __construct (&$uCore) {
        $this->uFunc=new uFunc($uCore);
        $this->uSes=new uSes($uCore);
        $this->translator=new translator(site_lang,'obooking/classes/common.php');
    }
}
