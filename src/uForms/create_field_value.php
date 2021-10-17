<?php
ignore_user_abort(true);

require_once "processors/classes/uFunc.php";
if(!isset($this->uCore)) $uCore=&$this;
else $uCore=$this->uCore;

if(!isset($uFunc)) $uFunc=new \processors\uFunc($uCore);

try {
    $search_is_checked = $uFunc->pdo("uForms")->prepare("SELECT
    field_id,is_checked,u235_rows.site_id,form_id
    FROM
    u235_fields
    JOIN
    u235_columns
      ON
        u235_fields.col_id=u235_columns.col_id AND
        u235_fields.site_id=u235_columns.site_id
    JOIN
    u235_rows
      ON
        u235_columns.row_id=u235_rows.row_id AND
        u235_columns.site_id=u235_rows.site_id
    WHERE
    is_checked != 1
    ");

    /** @noinspection PhpUndefinedMethodInspection */
    $search_is_checked->execute();

    $time=time();

    while($row_field_id = $search_is_checked->fetch(PDO::FETCH_ASSOC)) {
        $is_checked = $row_field_id["is_checked"];
        $form_id = $row_field_id["form_id"];
        $site_id = $row_field_id["site_id"];
        $field_id = $row_field_id["field_id"];
        $search_rec_id = $uFunc->pdo("uForms")->prepare("SELECT
    rec_id
    FROM
    u235_records
    WHERE
    rec_id < :is_checked AND 
    site_id=:site_id AND 
    form_id=:form_id
    ORDER BY rec_id desc 
    limit 2000
    ");

        /** @noinspection PhpUndefinedMethodInspection */
        $search_rec_id->bindValue(':is_checked', $is_checked, PDO::PARAM_INT);
        $search_rec_id->bindValue(':form_id', $form_id, PDO::PARAM_INT);
        $search_rec_id->bindValue(':site_id', $site_id, PDO::PARAM_INT);
        $search_rec_id->execute();
        $row_rec_id = $search_rec_id->fetchAll(PDO::FETCH_ASSOC);
        $count = count($row_rec_id);
        if ($count) {
            for($i=0 ; $i<$count; $i++) {
                $rec_id=$row_rec_id[$i]["rec_id"];
                $field_value = "";
                $insert_null_val = $uFunc->pdo("uForms")->prepare("INSERT
              IGNORE INTO
              u235_form_results
              SET 
              field_id=:field_id, 
              rec_id=:rec_id, 
              field_value=:field_value, 
              site_id=:site_id
              ");
                $insert_null_val->bindValue(':rec_id', $rec_id, PDO::PARAM_INT);
                $insert_null_val->bindValue(':field_id', $field_id, PDO::PARAM_INT);
                $insert_null_val->bindValue(':field_value', $field_value, PDO::PARAM_INT);
                $insert_null_val->bindValue(':site_id', $site_id, PDO::PARAM_INT);
                $insert_null_val->execute();
                $thistime = time();
                $timer = $thistime-$time;
                if ($timer > 25) {
                    break;
                }
            }
            $update_is_checked = $uFunc->pdo("uForms")->prepare("UPDATE 
                u235_fields 
                SET 
                is_checked=:is_checked
                WHERE 
                field_id=:field_id AND 
                site_id=:site_id
                ");
            $update_is_checked->bindValue(':site_id', $site_id, PDO::PARAM_INT);
            $update_is_checked->bindValue(':is_checked', $rec_id, PDO::PARAM_INT);
            $update_is_checked->bindValue(':field_id', $field_id, PDO::PARAM_INT);
            $update_is_checked->execute();
            $thistime = time();
            $timer = $thistime-$time;
            if ($timer > 25) {
                break;
            }
        }
        else {
            $is_checked_val = 1;
            $update_is_checked = $uFunc->pdo("uForms")->prepare("UPDATE 
                u235_fields 
                SET 
                is_checked=:is_checked
                WHERE 
                field_id=:field_id
                ");
            $update_is_checked->bindValue(':is_checked', $is_checked_val, PDO::PARAM_INT);
            $update_is_checked->bindValue(':field_id', $field_id, PDO::PARAM_INT);
            $update_is_checked->execute();
        }
    }

}
catch(PDOException $e) {$this->uFunc->error('10'.$e->getMessage());}