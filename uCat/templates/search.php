<form method="GET" action="<?=u_sroot?>uCat/search" id="uCat_search_form">
    <div class="input-group" id="uCat_search_group">
        <input
            name="search"
            <?
            if(isset($_GET['search'])) {
                $_GET['search']=trim($_GET['search']);
                if(!empty($_GET['search'])) {?> value="<?=htmlspecialchars(strip_tags($_GET['search']))?>" <?}
            }?>
            type="text"
            placeholder="<?=$uCat->uFunc->getConf("search_field_label","uCat")?>"
            class="form-control">
        <div class="input-group-btn">
            <button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-search"></span></button>
            <!--<input type="submit" value=" " style="position: absolute; z-index:-100; width:0; height:0; overflow:hidden; display:block; background:transparent; border:none;">-->
        </div>
    </div>
</form>