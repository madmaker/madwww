<!-- Follow Us -->
<script type="text/javascript" src="https://apis.google.com/js/plusone.js">
	 {lang: 'ru'}
</script>
<div id="share42">
	<a href="" title="Поделиться в Facebook" target="_blank" id="sb_facebook"></a>
	<a href="" title="Опубликовать в LiveJournal" target="_blank" id="sb_livejournal"></a>
	<a href="" title="Поделиться в Моем Мире@Mail.Ru" target="_blank" id="sb_mailru"></a>
	<a href="" title="Добавить в Одноклассники" target="_blank" id="sb_odnoklassniki"></a>
	<a href="" title="Добавить в Twitter" target="_blank" id="sb_twitter"></a>

	<a href="" title="Поделиться В Контакте" target="_blank" id="sb_vkontakte"></a>
    <div id="sb_google"><g:plusone size="standard" annotation="none"></g:plusone></div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function($){
        var sb_title = $('h1:first').text();
        var sb_title_array = sb_title.split('/');
        if (sb_title_array.length >= 2) {
            sb_title = sb_title_array[sb_title_array.length - 1];
        }
        if (!sb_title) {
            sb_title = $('title').text();
        }
        var sb_url = document.location.href;
	    var sb_facebook = encodeURI("http://www.facebook.com/sharer.php?u="+sb_url+"&t="+sb_title);
        var sb_livejournal = encodeURI("http://www.livejournal.com/update.bml?event="+sb_url+"&subject="+sb_title);
	    var sb_mailru = encodeURI("http://connect.mail.ru/share?url="+sb_url+"&title="+sb_title);
	    var sb_odnoklassniki = encodeURI("http://www.odnoklassniki.ru/dk?st.cmd=addShare&st._surl="+sb_url+"&title="+sb_title);
	    var sb_twitter = encodeURI("http://twitter.com/share?text="+sb_title);
	    var sb_vkontakte = encodeURI("http://vkontakte.ru/share.php?url="+sb_url+"&title="+sb_title);
	    $('#sb_facebook').attr('href', sb_facebook);
            $('#sb_livejournal').attr('href', sb_livejournal);
	    $('#sb_mailru').attr('href', sb_mailru);
	    $('#sb_odnoklassniki').attr('href', sb_odnoklassniki);
	    $('#sb_twitter').attr('href', sb_twitter);
	    $('#sb_vkontakte').attr('href', sb_vkontakte);
	});
</script>
<!--Follow US  -->