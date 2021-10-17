<?php

require_once "processors/classes/uFunc.php";
$uFunc=new \processors\uFunc($this);

$site_id=6;

try {
    /** @noinspection PhpUndefinedMethodInspection */
    $stm=$uFunc->pdo("pages")->prepare("SELECT
       page_id,
    page_text,
       page_alias,
       page_name
    FROM 
    u235_pages_html 
    WHERE 
    site_id=:site_id
    ");
    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
}
catch(PDOException $e) {$uFunc->error('0'/*.$e->getMessage()*/);}

while($qr=$stm->fetch(PDO::FETCH_OBJ)) {
    $page_alias=trim(uString::sql2text($qr->page_alias,1));
    $page_name=trim(uString::sql2text($qr->page_name,1));
    $page_text=uString::sql2text($qr->page_text,1);
    $page_id=$qr->page_id;

    echo $page_name;
    echo "<br>";

//    echo $page_text;
    if(mb_strlen($page_alias)) {
        $page_text=str_replace('href="#','href="'.u_sroot.$page_alias.'#',$page_text);
    }
    else $page_text=str_replace('href="#','href="'.u_sroot.'page/'.$page_name.'#',$page_text);
    $page_text=uString::text2sql($page_text,1);
    try {
        /** @noinspection PhpUndefinedMethodInspection */
        $stm1=$uFunc->pdo("pages")->prepare("UPDATE
        u235_pages_html
        SET
        page_text=:page_text
        WHERE
        page_id=:page_id AND
        site_id=:site_id
    ");
    /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_text', $page_text,PDO::PARAM_STR);
    /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_id', $page_id,PDO::PARAM_INT);
    /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':site_id', $site_id,PDO::PARAM_INT);
        /** @noinspection PhpUndefinedMethodInspection */$stm1->execute();
        echo "|";
    }
    catch(PDOException $e) {$uFunc->error('0'/*.$e->getMessage()*/);}
    echo "---------";
}

echo "done";