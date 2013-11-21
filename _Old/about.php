<?php
$page_title = "About";
require_once('./header.php');
?>
<div class='frame'>
	<ul>
		<li><a href='#info' title='About this project.'>Why? What?</a></li>
		<li><a href='#chr' title='RFC valid characters'>Valid RFC Characters</a></li>
		<li><a href='#regex' title='Regular Expression'>Regular Expression</a></li>
		<li><a href='#blacklist' title='Blacklists used by legitimize.me'>Blacklists used</a></li>
	</ul>
</div>
<div class='frame'>
	<a name='info'></a>
	<b>Why?</b>
	<p>
	Over the years I have found more and more sites running the email section of there registration forums through an email validation script.
	I understand the need for this but what I have found is that most sites are using a validation script that is not RFC 5322 compliant.
	Also most forums and sites down check the email address to see if its domain is being blacklisted.
	</p>
	<hr />
	<b>What?</b>
	<p>
	A RFC compliant validation script would allow for a number of different
	symbols/characters in the "local" section of the email. The local section of
	the email is the part before the '@' when the '@' is being fallowed by the domain.
	<br />
	<small>Exp. foo@bar.com - foo is the local section and bar.com is the domain.</small>
	</p>
</div>
<div class='frame'>
	<a name='chr'></a>
	<b>Valid RFC Characters:</b>
	<table class='chr'>
		<tbody>
			<tr>
				<td>&#33;</td>
				<td>exclamation mark</td>
			</tr>
			<tr>
				<td>&#36;</td>
				<td>dollar sign</td>
			</tr>
			<tr>
				<td>&#38;</td>
				<td>ampersand</td>
			</tr>
			<tr>
				<td>&#42;</td>
				<td>asterisk</td>
			</tr>
			<tr>
				<td>&#45;</td>
				<td>hyphen</td>
			</tr>
			<tr>
				<td>&#61;</td>
				<td>equals-to</td>
			</tr>
			<tr>
				<td>&#94;</td>
				<td>caret</td>
			</tr>
			<tr>
				<td>&#96;</td>
				<td>grave accent</td>
			</tr>
			<tr>
				<td>&#124;</td>
				<td>vertical bar/pipe</td>
			</tr>
			<tr>
				<td>&#126;</td>
				<td>tilde</td>
			</tr>
			<tr>
				<td>&#35;</td>
				<td>number sign</td>
			</tr>
			<tr>
				<td>&#37;</td>
				<td>percent sign</td>
			</tr>
			<tr>
				<td>&#39;</td>
				<td>apostrophe</td>
			</tr>
			<tr>
				<td>&#43;</td>
				<td>plus sign</td>
			</tr>
			<tr>
				<td>&#47;</td>
				<td>slash</td>
			</tr>
			<tr>
				<td>&#63;</td>
				<td>question mark</td>
			</tr>
			<tr>
				<td>&#95;</td>
				<td>underscore</td>
			</tr>
			<tr>
				<td>&#123;</td>
				<td>left curly brace</td>
			</tr>
			<tr>
				<td>&#125;</td>
				<td>right curly brace</td>
			</tr>
			<tr>
				<td colspan='2'>Quote’s are also allowed but must be paired with a backslash. <small>Exp. \”foo\”@bar.com</small></td>
			</tr>
		</tbody>
	</table>
	<hr />
	<a name='regex'></a>
	<b>Regular Expression</b>
	<p>
		The fallowing Regular Express is what legitimize.me uses to check to see if the email address is RFC compliant.
		<br />
		<small>This regex will change as we find ways to improve it. Last Updated: 05/28/2009</small>
		<br />
		<textarea id='regex' rows='10' cols='10'>^((?:(\\")|\.|\+|\!|\$|&amp;|\*|\-|\=|\^|\`|\||\~|\#|\%|'|\/|\?|\_|\{|\}|\s|\@|\w)*)@(?:[\w-]+\.){1,255}(ac|ad|ae|aero|af|ag|ai|al|am|an|ao|aq|ar|arpa|as|asia|at|au|aw|ax|az|ba|bb|bd|be|bf|bg|bh|bi|biz|bj|bm|bn|bo|br|bs|bt|bv|bw|by|bz|ca|cat|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|com|coop|cr|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gg|gh|gi|gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|hu|id|ie|il|im|in|info|int|io|iq|ir|is|it|je|jm|jo|jobs|jp|ke|kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|lt|lu|lv|ly|ma|mc|md|me|mg|mh|mil|mk|ml|mm|mn|mo|mobi|mp|mq|mr|ms|mt|mu|museum|mv|mw|mx|my|mz|na|name|nc|ne|net|nf|ng|ni|nl|no|np|nr|nu|nz|om|org|pa|pe|pf|pg|ph|pk|pl|pm|pn|pr|pro|ps|pt|pw|py|qa|re|ro|rs|ru|rw|sa|sb|sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|sz|tc|td|tel|tf|tg|th|tj|tk|tl|tm|tn|to|tp|tr|travel|tt|tv|tw|tz|ua|ug|uk|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|xn|ye|yt|yu|za|zm|zw)$</textarea>
	</p>
</div>
<div class='frame'>
	<a name='blacklist'></a>
	<b>Blacklists Checked</b>
	<p>The fallowing is a list of blacklists that legitimize.me uses to check your email’s domain to see if it is blacklisted.</p>
	<table class='about_blacklist'>
		<tbody>
			<tr>
				<td><a href='http://www.spamhaus.org' title='Spamhaus'>http://www.spamhaus.org</a></td>
			</tr>
			<tr>
				<td><a href='http://spamcannibal.org' title='SpamCannibal'>http://spamcannibal.org</a></td>
			</tr>
			<tr>
				<td><a href='http://cbl.abuseat.org' title='Abuseat'>http://cbl.abuseat.org</a></td>
			</tr>
			<tr>
				<td><a href='http://www.au.sorbs.net' title='Sorbs'>http://www.au.sorbs.net</a></td>
			</tr>
			<tr>
				<td><a href='http://www.uceprotect.net' title='UCEProTect'>http://www.uceprotect.net</a></td>
			</tr>
			<tr>
				<td><a href='http://www.wpbl.info' title='WPBL'>http://www.wpbl.info</a></td>
			</tr>
			<tr>
				<td><a href='http://www.junkemailfilter.com' title='JunkEmailFilter'>http://www.junkemailfilter.com</a></td>
			</tr>
			<tr>
				<td><a href='http://www.spamcop.net' title='SpamCop'>http://bl.spamcop.net</a></td>
			</tr>
		</tbody>
	</table>
</div>

<?php
/*************************** FOOTER ***************************/
include_once('./footer.php');
?>