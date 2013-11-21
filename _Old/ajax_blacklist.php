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



------------------------------------------------------------------------
Black list info
JunkEmailFilter.com
   * 127.0.0.1 - whilelist - trusted nonspam
   * 127.0.0.2 - blacklist - block spam
   * 127.0.0.3 - yellowlist - mix of spam and nonspam
   * 127.0.0.4 - brownlist - all spam - but not yet enough to blacklist
   * 127.0.0.5 - NOBL - This IP is not a spam only source and no blacklists need to be tested 
*/


/*--------------------------------------------------------------------
		FUNCTIONS AND CODE TO CHECK IF THE IP IS BLACKLISTED
--------------------------------------------------------------------*/	
$suspect_email = strtolower(urldecode(stripslashes($_REQUEST['e']))); // Email address
$blacklist = strtolower(urldecode(stripslashes($_REQUEST['b']))); // Blacklist to check

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
/*
	$code = '/^127\.0\.0\.\d{1,2,3}$/';
	if(preg_match($code, $ip))
	{
		return true;
	} else {
		return false;
	}
*/
	
	switch($ip)
	{
		case '127.0.0.2':
			return "2";
			break;
		case '127.0.0.4':
			return "4";
			break;
		default:
			return "1";
	}
}


/*--------------------------------------------------------------------
	THIS SECTION RUNS THE BLACKLIST CHECK AND SPITS OUT THE INFO
--------------------------------------------------------------------*/
$suspect_domain = get_email_domain($suspect_email);
$suspect_ip = gethostbyname($suspect_domain);
if(ip_good($suspect_ip))
{
	$r_ip = reverse_ip($suspect_ip);
	# Returns a 1 if blacklisted and a 0 if not.
	$return = gethostbyname($r_ip.'.'.$blacklist);
	$black_check = is_black_listed($return);
	switch($black_check)
	{
		case 1:
			echo "1";
			break;
		case 2:
			echo "2";
			break;
		case 4:
			echo "4";
			break;
		default:
			echo "99";
	}
	/*if($black_check)
	{
		echo "1"; //Blacklisted
	} else {
		echo "0"; //Not blacklisted
	} else {
		echo "99";
	}*/
}
?>