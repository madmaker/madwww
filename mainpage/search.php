<?php
namespace mainpage;

use PDO;
use PDOException;
use processors\uFunc;
use uSes;
use uString;

require_once 'processors/classes/uFunc.php';

class search
{
    /**
     * @var uFunc
     */
    public $uFunc;
    /**
     * @var uSes
     */
    public $uSes;
    private $uCore;
    public $f_word, $q_pages;

    public function text($str)
    {
        return $this->uCore->text(['mainpage', 'search'], $str);
    }

    private function error(/** @noinspection PhpUnusedParameterInspection */ $num) {
        //die($num);
        header('Location: ' . u_sroot);
        exit();
    }
    private function checkData()
    {
        if (!isset($_GET['search'])) {
            $this->error(1);
        }
        $this->f_word = uString::replace4sqlLike(
            uString::text2sql(trim(urldecode($_GET['search'])))
        );
    }
    public function getPages($pagesNum)
    {
        $f_sql = explode(' ', $this->f_word);

        $f_page_word_title_sql = "page_title LIKE '%" . $this->f_word . "%' OR";

        $f_text_word_sql =
            "page_short_text LIKE '%" .
            $this->f_word .
            "%' OR
        page_text LIKE '%" .
            $this->f_word .
            "%' OR ";

        $f_page_word_sql = "preview_text LIKE '%" . $this->f_word . "%' OR ";

        //Фраза целиком в заголовке страницы
        try {
            $stm = $this->uFunc->pdo('uPage')->prepare("SELECT
            page_id,
            preview_text,
            page_url,
            page_title
            FROM 
            u235_pages
            WHERE
            ($f_page_word_title_sql 1=0) AND
            site_id=:site_id
            ORDER BY page_id DESC
            LIMIT $pagesNum"
            );
            $site_id = site_id;
            $stm->bindParam(':site_id',$site_id,PDO::PARAM_INT);
            $stm->execute();
        } catch (PDOException $e) {
            $this->uFunc->error('10' /*.$e->getMessage()*/);
        }

        /** @noinspection PhpUndefinedVariableInspection */
        $qr = $stm->fetchAll(PDO::FETCH_OBJ);
        $qr_count = count($qr);

        $f_page_word_title1_sql = "page_title LIKE '%";
        foreach ($f_sql as $iValue) {
            $word = trim($iValue);
            if (strlen($word) < 3) {
                continue;
            }
            $f_page_word_title1_sql .= $word . '%';
        }
        $f_page_word_title1_sql .= "'";

        $no_page_id = '';

        foreach ($qr as $iValue) {
            $no_page_id .= ' AND u235_pages.page_id!=' . $iValue->page_id . ' ';
        }
        $f_page_word_title1_sql .= $no_page_id;

        if ($pagesNum - $qr_count > 0) {
            $pagesNum -= $qr_count;
        } else {
            $pagesNum = 0;
        }

        /** *******************
         *******************/
        //Все слова фразы в заголовке страницы
        try {
            $stm = $this->uFunc->pdo('uPage')->prepare("SELECT
            page_id,
            preview_text,
            page_title,
            page_url
            FROM 
            u235_pages
            WHERE
            ($f_page_word_title1_sql) AND
            site_id=:site_id
            ORDER BY 
            page_id DESC
            LIMIT " . $pagesNum
            );
            $site_id = site_id;
            $stm->bindParam(
                ':site_id',
                $site_id,
                PDO::PARAM_INT
            );
            $stm->execute();
        } catch (PDOException $e) {
            $this->uFunc->error('20' /*.$e->getMessage()*/);
        }

        $qr1 = $stm->fetchAll(PDO::FETCH_OBJ);
        $qr1_count = count($qr1);

        $f_page_word_title_sql = '(';
        foreach ($f_sql as $iValue) {
            $word = trim($iValue);
            if (strlen($word) < 3) {
                continue;
            }
            $f_page_word_title_sql .=
                "
            page_title LIKE '%" .
                $word .
                "%' OR";
        }
        $f_page_word_title_sql .= ' 1=0)';

        foreach ($qr1 as $iValue) {
            $no_page_id .= ' AND u235_pages.page_id!=' . $iValue->page_id . ' ';
        }
        $f_page_word_title_sql .= $no_page_id;

        if ($pagesNum - $qr_count - $qr1_count > 0) {
            $pagesNum = $pagesNum - $qr_count - $qr1_count;
        } else {
            $pagesNum = 0;
        }

        /** *******************
         *******************/
        //Любое из слов в заголовке страницы
        try {
            $stm = $this->uFunc->pdo('uPage')->prepare("SELECT
            page_id,
            preview_text,
            page_title,
            page_url
            FROM 
            u235_pages
            WHERE
            ($f_page_word_title_sql) AND
            site_id=:site_id
            ORDER BY page_id DESC
            LIMIT $pagesNum
            ");
            $site_id = site_id;
            $stm->bindParam(
                ':site_id',
                $site_id,
                PDO::PARAM_INT
            );
            $stm->execute();
        } catch (PDOException $e) {
            $this->uFunc->error('30' /*.$e->getMessage()*/);
        }

        $qr2 = $stm->fetchAll(PDO::FETCH_OBJ);
        $qr2_count = count($qr2);

        $f_page_word_sql = "( $f_page_word_sql 1=0)";
        foreach ($qr2 as $iValue) {
            $page_id=$iValue->page_id;
            $no_page_id .=
                " AND u235_pages.page_id!=$page_id ";
        }
        $f_page_word_sql .= $no_page_id;

        if ($pagesNum - $qr_count - $qr1_count - $qr2_count > 0) {
            $pagesNum = $pagesNum - $qr_count - $qr1_count - $qr2_count;
        } else {
            $pagesNum = 0;
        }

        /** *******************
         *******************/
        //Фраза целиком в preview_text_страницы
        try {
            $stm = $this->uFunc->pdo('uPage')->prepare("SELECT
            page_id,
            preview_text,
            page_title,
            page_url
            FROM 
            u235_pages
            WHERE
            ($f_page_word_sql) AND
            site_id=:site_id
            ORDER BY page_id DESC
            LIMIT $pagesNum
            ");
            $site_id = site_id;
            $stm->bindParam(
                ':site_id',
                $site_id,
                PDO::PARAM_INT
            );
            $stm->execute();
        } catch (PDOException $e) {
            $this->uFunc->error('40' /*.$e->getMessage()*/);
        }

        $qr3 = $stm->fetchAll(PDO::FETCH_OBJ);
        $qr3_count = count($qr3);

        $f_page_word_sql = '(';
        foreach ($f_sql as $iValue) {
            $word = trim($iValue);
            if (strlen($word) < 3) {
                continue;
            }
            $f_page_word_sql .= "preview_text LIKE '%" . $word . "%' OR ";
        }
        $f_page_word_sql .= ' 1=0)';

        foreach ($qr3 as $iValue) {
            $page_id=$iValue->page_id;
            $no_page_id .=" AND u235_pages.page_id!=$page_id ";
        }
        $f_page_word_sql .= $no_page_id;

        if ($pagesNum - $qr_count - $qr1_count - $qr2_count - $qr3_count > 0) {
            $pagesNum =
                $pagesNum - $qr_count - $qr1_count - $qr2_count - $qr3_count;
        } else {
            $pagesNum = 0;
        }

        /** *******************
         *******************/
        //Любое из слов в preview_text страницы
        try {
            $stm = $this->uFunc->pdo('uPage')->prepare("SELECT
            page_id,
            preview_text,
            page_title,
            page_url
            FROM 
            u235_pages
            WHERE
            ($f_page_word_sql) AND
            site_id=:site_id
            ORDER BY page_id DESC
            LIMIT $pagesNum
            ");
            $site_id = site_id;
            $stm->bindParam(
                ':site_id',
                $site_id,
                PDO::PARAM_INT
            );
            $stm->execute();
            $qr4 = $stm->fetchAll(PDO::FETCH_OBJ);
            $qr4_count = count($qr4);
        } catch (PDOException $e) {
            $this->uFunc->error('50' /*.$e->getMessage()*/);
        }

        $f_text_word_sql = "($f_text_word_sql 1=0)";

        /** @noinspection PhpUndefinedVariableInspection */
        foreach ($qr4 as $iValue) {
            $page_id=$iValue->page_id;
            $no_page_id .=
                " AND u235_pages.page_id!=$page_id ";
        }
        $f_text_word_sql .= $no_page_id;

        /** @noinspection PhpUndefinedVariableInspection */
        if (
            $pagesNum -
                $qr_count -
                $qr1_count -
                $qr2_count -
                $qr3_count -
                $qr4_count >
            0
        ) {
            $pagesNum =
                $pagesNum -
                $qr_count -
                $qr1_count -
                $qr2_count -
                $qr3_count -
                $qr4_count;
        } else {
            $pagesNum = 0;
        }

        /** *******************
         *******************/
//        $no_text_id = '';
        //Фраза целиком в short_text или page_text текста
        try {
            $stm = $this->uFunc->pdo('pages')->prepare("SELECT DISTINCT
            u235_pages.page_id,
            u235_pages_html.page_id AS text_id,
            u235_pages_html.page_text,
            u235_pages_html.page_short_text,
            u235_pages.page_title,
            u235_pages.page_url
            FROM 
            u235_pages_html
            JOIN
            madmakers_uPage.u235_cols_els
            ON
            u235_cols_els.el_id=u235_pages_html.page_id AND
            u235_cols_els.site_id=u235_pages_html.site_id AND
            u235_cols_els. el_type='art'
            JOIN 
            madmakers_uPage.u235_cols
            ON
            u235_cols.col_id=u235_cols_els.col_id AND
            u235_cols.site_id=u235_cols_els.site_id
            JOIN
            madmakers_uPage.u235_rows
            ON
            u235_rows.row_id=u235_cols.row_id AND
            u235_rows.site_id=u235_cols.site_id
            JOIN
            madmakers_uPage.u235_pages
            ON
            u235_pages.page_id=u235_rows.page_id AND
            u235_pages.site_id=u235_rows.site_id            
            WHERE
            ($f_text_word_sql) AND
            u235_pages_html.site_id=:site_id
            ORDER BY page_id DESC
            LIMIT $pagesNum
            ");
            $site_id = site_id;
            $stm->bindParam(
                ':site_id',
                $site_id,
                PDO::PARAM_INT
            );
            $stm->execute();
        } catch (PDOException $e) {
            $this->uFunc->error('60' /*.$e->getMessage()*/);
        }

        $qr5 = $stm->fetchAll(PDO::FETCH_OBJ);
        $qr5_count = count($qr5);

        $f_text_word_sql = '(';
        foreach ($f_sql as $iValue) {
            $word = trim($iValue);
            if (strlen($word) < 3) {
                continue;
            }
            $f_text_word_sql .=
                "
            page_text LIKE '%" .
                $word .
                "%' OR 
            page_short_text LIKE '%" .
                $word .
                "%' OR ";
        }
        $f_text_word_sql .= ' 1=0)';

        foreach ($qr5 as $iValue) {
            $page_id=$iValue->page_id;
//            $text_id=$iValue->text_id;
            $no_page_id .=" AND u235_pages.page_id!=$page_id ";
//            $no_text_id .=" AND u235_pages_html.page_id!=$text_id ";
        }
        $f_text_word_sql .= $no_page_id;

        if (
            $pagesNum -
                $qr_count -
                $qr1_count -
                $qr2_count -
                $qr3_count -
                $qr4_count -
                $qr5_count >
            0
        ) {
            $pagesNum =
                $pagesNum -
                $qr_count -
                $qr1_count -
                $qr2_count -
                $qr3_count -
                $qr4_count -
                $qr5_count;
        } else {
            $pagesNum = 0;
        }

        /** *******************
         *******************/
        //Любое из слов в page_text или page_short_text текста
        try {
            /** @noinspection SqlResolve */
            $stm = $this->uFunc->pdo('pages')->prepare("SELECT DISTINCT
            u235_pages.page_id,
            u235_pages_html.page_id AS text_id,
            u235_pages_html.page_text,
            u235_pages_html.page_short_text,
            u235_pages.page_title,
            u235_pages.page_url
            FROM 
            u235_pages_html
            JOIN
            madmakers_uPage.u235_cols_els
            ON
            u235_cols_els.el_id=u235_pages_html.page_id AND
            u235_cols_els.site_id=u235_pages_html.site_id AND
            u235_cols_els. el_type='art'
            JOIN 
            madmakers_uPage.u235_cols
            ON
            u235_cols.col_id=u235_cols_els.col_id AND
            u235_cols.site_id=u235_cols_els.site_id
            JOIN
            madmakers_uPage.u235_rows
            ON
            u235_rows.row_id=u235_cols.row_id AND
            u235_rows.site_id=u235_cols.site_id
            JOIN
            madmakers_uPage.u235_pages
            ON
            u235_pages.page_id=u235_rows.page_id AND
            u235_pages.site_id=u235_rows.site_id            
            WHERE
            ($f_text_word_sql) AND
            u235_pages_html.site_id=:site_id
            $no_page_id
            ORDER BY page_id DESC
            LIMIT $pagesNum
            ");
            $site_id = site_id;
            $stm->bindParam(
                ':site_id',
                $site_id,
                PDO::PARAM_INT
            );
            $stm->execute();
        } catch (PDOException $e) {
            $this->uFunc->error('70' /*.$e->getMessage()*/);
        }

        $qr6 = $stm->fetchAll(PDO::FETCH_OBJ);
        $qr6_count = count($qr6);

        return [
            $qr,
            $qr1,
            $qr2,
            $qr3,
            $qr4,
            $qr5,
            $qr6,
            $qr_count,
            $qr1_count,
            $qr2_count,
            $qr3_count,
            $qr4_count,
            $qr5_count,
            $qr6_count,
        ];
    }
    public function processText($text)
    {
        $text = uString::sql2text($text, true);
        $text = strip_tags($text);
        @$filterPos = mb_strpos($text, $this->f_word, 0, 'utf-8');
        $text = mb_substr(
            $text,
            $filterPos - 100 < 0 ? 0 : $filterPos - 100,
            1000,
            'utf-8'
        );

        $txt_ar = explode(' ', $this->f_word);
        foreach ($txt_ar as $iValue) {
            if (trim($iValue) === '') {
                continue;
            }
            $text = preg_replace(
                "/$iValue/iu",
                '<span class="bg-success">' . $iValue . '</span>',
                $text
            );
        }
        return $text;
    }
    public function __construct(&$uCore)
    {
        $this->uCore = &$uCore;
        if (!isset($this->uCore)) {
            /** @noinspection PhpFullyQualifiedNameUsageInspection */ $this->uCore = new \uCore();
        }

        $this->uFunc = new uFunc($this->uCore);
        $this->uSes = new uSes($this->uCore);

        $this->uCore->page['page_title'] = $this->text('Page name' );

        $this->checkData();
    }
}
$mainpage = new search($this);

ob_start();
?>

<!--suppress SpellCheckingInspection -->
    <div class="mpage_search">
    <h1><?= $mainpage->text('Search - page header') ?> "<?= $mainpage->f_word ?>"</h1>
    <div class="found"></div>

        <?php
    $qr_pages_ar=$mainpage->getPages(50);
    $fond=0;

    for($i=0;$i<$qr_pages_ar[7];$i++) {
        $data=$qr_pages_ar[0][$i];
                $fond=1;?>
            <div class="item">
                <h3><a href="<?php
                    print u_sroot;
                    if($data->page_url=== '') {
                        print 'uPage/page/';
                        print $data->page_id;
                    }
                    else {
                        print $data->page_url;
                    }
                    ?>"><?= uString::sql2text($data->page_title, 1) ?></a></h3>
                <div><?= $mainpage->processText($data->preview_text) ?></div>
            </div>
        <?php
    }

    for($i=0;$i<$qr_pages_ar[8];$i++) {
        $data=$qr_pages_ar[1][$i];
                $fond=1;?>
            <div class="item">
                <h3><a href="<?php
                    print u_sroot;
                    if($data->page_url=== '') {
                        print 'uPage/page/';
                        print $data->page_id;
                    }
                    else {
                        print $data->page_url;
                    }
                    ?>"><?= uString::sql2text($data->page_title, 1) ?></a></h3>
                <div><?= $mainpage->processText($data->preview_text) ?></div>
            </div>
        <?php
    }

    for($i=0;$i<$qr_pages_ar[9];$i++) {
        $data=$qr_pages_ar[2][$i];
                $fond=1;?>
            <div class="item">
                <h3><a href="<?php
                    print u_sroot;
                    if($data->page_url=== '') {
                        print 'uPage/page/';
                        print $data->page_id;
                    }
                    else {
                        print $data->page_url;
                    }
                    ?>"><?= uString::sql2text($data->page_title, 1) ?></a></h3>
                <div><?= $mainpage->processText($data->preview_text) ?></div>
            </div>
        <?php
    }

    for($i=0;$i<$qr_pages_ar[10];$i++) {
        $data=$qr_pages_ar[3][$i];
                $fond=1;?>
            <div class="item">
                <h3><a href="<?php
                    print u_sroot;
                    if($data->page_url=== '') {
                        print 'uPage/page/';
                        print $data->page_id;
                    }
                    else {
                        print $data->page_url;
                    }
                    ?>"><?= uString::sql2text($data->page_title, 1) ?></a></h3>
                <div><?= $mainpage->processText($data->preview_text) ?></div>
            </div>
        <?php
    }

    for($i=0;$i<$qr_pages_ar[11];$i++) {
        $data=$qr_pages_ar[4][$i];
                $fond=1;?>
            <div class="item">
                <h3><a href="<?php
                    print u_sroot;
                    if($data->page_url=== '') {
                        print 'uPage/page/';
                        print $data->page_id;
                    }
                    else {
                        print $data->page_url;
                    }
                    ?>"><?= uString::sql2text($data->page_title, 1) ?></a></h3>
                <div><?= $mainpage->processText($data->preview_text) ?></div>
            </div>
        <?php
    }

    for($i=0;$i<$qr_pages_ar[12];$i++) {
        $data=$qr_pages_ar[5][$i];
                $fond=1;?>
            <div class="item">
                <h3><a href="<?php
                    print u_sroot;
                    if($data->page_url=== '') {
                        print 'uPage/page/';
                        print $data->page_id;
                    }
                    else {
                        print $data->page_url;
                    }
                    ?>"><?= uString::sql2text($data->page_title, 1) ?></a></h3>
                <div><?= $mainpage->processText($data->page_text) ?></div>
            </div>
        <?php
    }

    for($i=0;$i<$qr_pages_ar[13];$i++) {
        $data=$qr_pages_ar[6][$i];
                $fond=1;?>
            <div class="item">
                <h3><a href="<?php
                    print u_sroot;
                    if($data->page_url=== '') {
                        print 'uPage/page/';
                        print $data->page_id;
                    }
                    else {
                        print $data->page_url;
                    }
                    ?>"><?= uString::sql2text($data->page_title, 1) ?></a></h3>
                <div><?= $mainpage->processText($data->page_text) ?></div>
            </div>
        <?php
    }

    if(!$fond) {?>
    <div class="jumbotron">
        <h3><?= $mainpage->text('Nothing found - results header') ?></h3>
        <?= $mainpage->text('Nothing found - hint') ?>
    </div>
    <?}?>
</div>

<?php
$this->page_content=ob_get_clean();

include 'templates/template.php';
