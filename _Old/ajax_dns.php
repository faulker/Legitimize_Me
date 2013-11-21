<?php
/*
Need to reverse the IP address so that 127.0.0.1 would be 1.0.0.127

exp. of a bad IP: 87.184.34.64.zen.spamhaus.org
gethostbyname('87.184.34.64.zen.spamhaus.org') will return 127.0.0.2


# dnsbl.tqmcube.com
# blacklist.spambag.org
# rbl.maps.vix.com
# lbl.lagengymnastik.dk
# dynablock.njabl.org
# dnsbl.radparker.com
# relays.radparker.com
# relays.visi.com
# opm.blitzed.org
# relays.ordb.org
# block.blars.org
# relays.orbs.org


Add this to black lists

# http://www.stopforumspam.com/api?ip=91.186.18.61
# http://www.stopforumspam.com/api?email=g2fsehis5e@mail.ru
*/

# Array of blacklist dns servers.
$blacklists = Array(
		Array('spamhaus', 'zen.spamhaus.org', 'http://www.spamhaus.org/query/bl?ip=', 1),
		Array('SpamCannibal', 'bl.spamcannibal.org', 'http://spamcannibal.org/cannibal.cgi', 0),
		Array('CBL', 'cbl.abuseat.org', 'http://cbl.abuseat.org/lookup.cgi?ip=', 1),
		Array('Sorbs DNSBL', 'dnsbl.sorbs.net', 'http://www.au.sorbs.net/lookup.shtml', 0),
		Array('Uceprotech - Level 1', 'dnsbl-1.uceprotect.net', 'http://www.uceprotect.net', 0),
		Array('Uceprotech - Level 2', 'dnsbl-2.uceprotect.net', 'http://www.uceprotect.net', 0),
		Array('Uceprotech - Level 3', 'dnsbl-3.uceprotect.net', 'http://www.uceprotect.net', 0)
	);


$suspect_email = $_REQUEST['e'];

# Reverses the ip address
# 192.168.5.8 would be returned as 8.5.168.192
function reverse_ip($ip)
{
	$new_ip = array_reverse(explode('.', $ip));
	$new_ip = implode('.', $new_ip);
	return $new_ip;
}

# Returns the domain part of an email address
function get_email_domain($e)
{
	$email = explode('@', $e);
	$num = count($email);
	return $email[$num-1];
}

# Checks to see if the ip address is a good address.
# Also will return a file if the domain of the email is not a vaild domain.
function ip_good($ip)
{
	$bad_ip = true;
	
	# check the ip to see if it is a private ip
	# 192.168.x.x
	# 172.16-31.x.x
	# 10.x.x.x
	# 127.x.x.x
	$private_ip = '/^(172\.(1[6-9]|2[0-9]|3[0-1])\.\d{1,3}\.\d{1,3})|(192\.168\.\d{1,3}.\d{1,3})|(10\.\d{1,3}\.\d{1,3}\.\d{1,3})|(127\.\d{1,3}\.\d{1,3}\.\d{1,3})$/';
	if(preg_match($private_ip, $ip))
	{
		$bad_ip = true;
	} else {
		$bad_ip = false;
	}
	
	# Checks to see if the ip address is correctly formated
	# ie. It is between 0 and 255
	# exp. 206.13.28.12 would pass but 268.221.2.58 would fail.
	$patter = '/^\b(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b/';
	if(preg_match($patter, $ip) && $bad_ip === false)
	{
		return true;
	} else {
		return false;
	}
}

function is_black_listed($ip)
{
	$code = '/^127\.0\.0\.\d{1,3}$/';
	if(preg_match($code, $ip))
	{
		return true;
	} else {
		return false;
	}
}

$suspect_domain = get_email_domain($suspect_email);
$suspect_ip = gethostbyname($suspect_domain);
if(ip_good($suspect_ip))
{
	$r_ip = reverse_ip($suspect_ip);
	foreach($blacklists as &$b)
	{
		if(is_black_listed(gethostbyname($r_ip.'.'.$b[1])))
		{
			echo gethostbyname($r_ip.'.'.$b[1])."<br />\n";
			echo $b[0]." is blacklisting this email's domain.<br />\n";
		} else {
			echo gethostbyname($r_ip.'.'.$b[1])."<br />\n";
			echo $b[0]." is not blacklisting this email's domain.<br />\n";
		}
	}
} else {
	echo "IP/Host failed";
}


#echo gethostbyname("87.184.34.64.zen.spamhaus.org");

?>