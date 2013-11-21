<?php
$page_title = "Email Check";
require_once('./header.php');
?>

<div id='about_check' class='frame'>
	<h2>About</h2>
	Check an EMail address to see if its RFC5322 valid and if it is blacklisted by a number of online IP and domain blacklists.
</div>

<div class='frame'>
	<h2>Email Check</h2>
	<hr />
	<div id='email_check'>
		<input type='text' id='email' />
		<img id='blacklist_loading' src='./_img/loading.gif' alt='loading...' />
		<br />
		<a href='#' id='check_email' title='Check Email'>Check Email</a>
		<hr />
		<div id='rfc_valid'></div>
		<div id='blacklist_list'></div>
	</div>
	<div class='legend'>
		<img src='./_img/power_on.png' alt=':&#41;' /><small>: Not black listed.&nbsp;-&nbsp;</small><img src='./_img/power_off.png' alt=':&#40;' /><small>: Black listed.</small>&nbsp;-&nbsp;<img src='./_img/information.png' alt=':&#40;' /><small>: Brown/Yellow listed.</small>&nbsp;-&nbsp;<img src='./_img/warning.png' alt=':&#40;' /><small>: Error.</small>
	</div>
</div>

<?php
/*************************** FOOTER ***************************/
include_once('./footer.php');
?>