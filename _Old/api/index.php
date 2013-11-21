<?php
$email = strtolower(urldecode(stripslashes($_REQUEST['e']))); // Email address
$option = strtolower($_REQUEST['o']); // Option rfc, blacklist, 

/*--------------------------------------------------------------------
	BLACKLISTS, IP, DNS, EMAIL
--------------------------------------------------------------------*/

# Array of blacklist ip servers.
$ip_blacklists = Array(
		Array('Spamhaus', 'zen.spamhaus.org', 'http://www.spamhaus.org/query/bl?ip=', 1),
		Array('SpamCannibal', 'bl.spamcannibal.org', 'http://spamcannibal.org/cannibal.cgi', 0),
		Array('CBL', 'cbl.abuseat.org', 'http://cbl.abuseat.org/lookup.cgi?ip=', 1),
		Array('Sorbs DNSBL', 'dnsbl.sorbs.net', 'http://www.au.sorbs.net/lookup.shtml', 0),
		Array('Uceprotech - Level 1', 'dnsbl-1.uceprotect.net', 'http://www.uceprotect.net', 0),
		Array('Uceprotech - Level 2', 'dnsbl-2.uceprotect.net', 'http://www.uceprotect.net', 0),
		Array('Uceprotech - Level 3', 'dnsbl-3.uceprotect.net', 'http://www.uceprotect.net', 0),
		Array('Weighted Private Black List', 'db.wpbl.info', 'http://www.wpbl.info/cgi-bin/detail.cgi?ip=', 1),
		Array('JunkEmailFilter', 'hostkarma.junkemailfilter.com', 'http://www.junkemailfilter.com', 0),
		Array('SpamCop', 'bl.spamcop.net ', 'http://www.spamcop.net', 0)
	);


/*--------------------------------------------------------------------
	RFC CHECK
--------------------------------------------------------------------*/
function rfc_check($email)
{
	$email_okay = 0;
	##  Build Top-Level Domain list
	require_once('./tld.php');
	$tld_num = count($tld);
	$regex_tld = '(';
	for($i=0;$i<$tld_num;$i++) //--> Start
	{
		if($i == $tld_num-1)
		{
			$regex_tld .= $tld[$i].')';
		} else {
			$regex_tld .= $tld[$i].'|';
		}
	} //--> End

	##  Email validation section
	$regex = strtolower('/^((?:(\\\")|\.|\+|\!|\$|&|\*|\-|\=|\^|\`|\||\~|\#|\%|\'|\/|\?|\_|\{|\}|\s|\@|\w)*)@(?:[\w-]+\.){1,255}'.$regex_tld.'$/');
	preg_match($regex, $email, $match);
	if($match[0] === $email)
	{
		$email_okay = 1;
	}
	return $email_okay;
}


/*--------------------------------------------------------------------
		FUNCTIONS AND CODE TO CHECK IF THE IP IS BLACKLISTED
--------------------------------------------------------------------*/	
$suspect_email = strtolower(urldecode(stripslashes($_REQUEST['e']))); // Email address

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

echo "<?xml version='1.0' encoding='UTF-8'?>\n";
if($option == 'rfc' || $option == 'both')
{
	echo "<rfc standard='5322'>".rfc_check($email)."</rfc>\n";
}

if($option == 'blacklist' || $option == 'both')
{
	echo "<blacklist>\n";

	/*--------------------------------------------------------------------
		THIS SECTION RUNS THE BLACKLIST CHECK AND SPITS OUT THE INFO
	--------------------------------------------------------------------*/
	$suspect_domain = get_email_domain($suspect_email);
	$suspect_ip = gethostbyname($suspect_domain);
	if(ip_good($suspect_ip))
	{
		$r_ip = reverse_ip($suspect_ip);
		foreach($ip_blacklists as &$b)
		{
			# If site has a ip lookup function else just send your to the blacklist site.
			if($b[3] === 1)
			{
				$link = $b[2].$suspect_ip;
			} else {
				$link = $b[2];
			}
			
			# Returns a 1 if blacklisted and a 0 if not.
			if(is_black_listed(gethostbyname($r_ip.'.'.$b[1])))
			{
				echo "	<list name='".$b[0]."' link='".$link."'>1</list>\n";
			} else {
				echo "	<list name='".$b[0]."' link='".$link."'>0</list>\n";
			}
		}
	} else {
		echo "<error code='99'>Bad email domain/ip address.</error>\n"; // Bad IP
	}
	echo "</blacklist>\n";
}
?>