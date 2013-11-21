<?php
$page_title = "API";
require_once('./header.php');
?>

<div class='frame'>
	<b>API Use</b>
	<p>
		<a href='http://legitimize.me/api/?e=test@legitimize.me&o=both' title='Test it'>http://legitimize.me/api/?e=test@legitimize.me&o=(both|rfc|blacklist)</a>
	</p>
	<hr />
	<b>O Verieables</b>
	<table class='chr'>
		<tbody>
			<tr>
				<td>both</td>
				<td>Check both RFC and blacklists.</td>
			</tr>
			<tr>
				<td>rfc</td>
				<td>Check to see if the email address is RFC compliant.</td>
			</tr>
			<tr>
				<td>blacklist</td>
				<td>Check to see if the email address is listed on any of the blacklists.</td>
			</tr>
		<tbody>
	</table>
	<hr />
	<b>API Output</b>
	<div class='code'>&lt;?xml version='1.0' encoding='UTF-8'?&gt;
&lt;rfc standard='5322'&gt;1&lt;/rfc&gt;
&lt;blacklist&gt;
	&lt;list name='Spamhaus' link='http://www.spamhaus.org/query/bl?ip=174.120.2.34'&gt;0&lt;/list&gt;
	&lt;list name='SpamCannibal' link='http://spamcannibal.org/cannibal.cgi'&gt;0&lt;/list&gt;
	&lt;list name='CBL' link='http://cbl.abuseat.org/lookup.cgi?ip=174.120.2.34'&gt;0&lt;/list&gt;
	&lt;list name='Sorbs DNSBL' link='http://www.au.sorbs.net/lookup.shtml'&gt;0&lt;/list&gt;
	&lt;list name='Uceprotech - Level 1' link='http://www.uceprotect.net'&gt;0&lt;/list&gt;
	&lt;list name='Uceprotech - Level 2' link='http://www.uceprotect.net'&gt;0&lt;/list&gt;
	&lt;list name='Uceprotech - Level 3' link='http://www.uceprotect.net'&gt;0&lt;/list&gt;
	&lt;list name='Weighted Private Black List' link='http://www.wpbl.info/cgi-bin/detail.cgi?ip=174.120.2.34'&gt;0&lt;/list&gt;
	&lt;list name='JunkEmailFilter' link='http://www.junkemailfilter.com'&gt;0&lt;/list&gt;
	&lt;list name='SpamCop' link='http://www.spamcop.net'&gt;0&lt;/list&gt;
&lt;/blacklist&gt;</div>
	<hr />
	<b>XML Reference</b>
	<table class='chr'>
		<tbody>
			<tr>
				<td>rfc</td>
				<td><b>0 (Zero)</b>: does note meet the standard, <b>1 (One)</b>: meets the standard</td>
			</tr>
			<tr>
				<td>rfc-&gt;standard</td>
				<td>The RFC standard being checked.</td>
			</tr>
			<tr>
				<td>blacklist-&gt;list</td>
				<td><b>0 (Zero)</b>: IP is not found in blacklist,<br /><b>1 (One)</b>: IP has a "warn" status or is marked as an IP from a shared web host account and should not be consider bad,<br /><b>Any other Number</b>: The IP is marked as being blacklisted.</td>
			</tr>
			<tr>
				<td>blacklist-&gt;list-&gt;name</td>
				<td>The name of the blacklist being checked.</td>
			</tr>
			<tr>
				<td>blacklist-&gt;list-&gt;link</td>
				<td>Link to the blacklist ip check tool, or directly to the blacklist.</td>
			</tr>
		</tbody>
	</table>
</div>

<?php
/*************************** FOOTER ***************************/
include_once('./footer.php');
?>