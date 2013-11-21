<?php
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


# List of blacklists
$ip_blacklists = array();
$ip_blacklists[] = 'zen.spamhaus.org';
$ip_blacklists[] = 'bl.spamcannibal.org';
$ip_blacklists[] = 'cbl.abuseat.org';
$ip_blacklists[] = 'dnsbl.sorbs.net';
$ip_blacklists[] = 'dnsbl-1.uceprotect.net';
$ip_blacklists[] = 'dnsbl-2.uceprotect.net';
$ip_blacklists[] = 'dnsbl-3.uceprotect.net';
$ip_blacklists[] = 'db.wpbl.info';
$ip_blacklists[] = 'hostkarma.junkemailfilter.com';
$ip_blacklists[] = 'bl.spamcop.net';



/*--------------------------------------------------------------------
	THIS SECTION RUNS THE BLACKLIST CHECK AND SPITS OUT THE INFO
--------------------------------------------------------------------*/
$suspect_email = strtolower(urldecode(stripslashes($_REQUEST['e']))); // Email address
#$blacklist = strtolower(urldecode(stripslashes($_REQUEST['b']))); // Blacklist to check

$suspect_domain = get_email_domain($suspect_email); // Extracts the domain from the email address.
$suspect_ip = gethostbyname($suspect_domain); // Get the IP address from the domain.


echo "<table id='blacklist_valid' class='t_full'>\n";
echo "<tbody>\n";
if(ip_good($suspect_ip))
{
	$r_ip = reverse_ip($suspect_ip);
	$b_num = count($ip_blacklists);
	
	foreach($ip_blacklists as $b)
	{
		echo "<tr class='result_blacklist'>\n";
		$return = gethostbyname($r_ip.'.'.$b);
		if(preg_match('(spamhaus)', $b))
		{
			if(preg_match("(\.([2-9]|10|11)$)", $return))
			{
				echo "<td class='img_blacklist'><img src='./_img/power_off.png' alt='X' /></td>\n";
				echo "<td class='list_title'>".$b." - IP address for the email's domain is blacklisted.</td>\n";
			} else {
				echo "<td class='img_blacklist'><img src='./_img/power_on.png' alt='0' /></td>\n";
				echo "<td class='list_title'>".$b." - This domain's email is NOT blacklisted.</td>\n";
			}
		} else {
			if(preg_match("(127\.0\.0\.3)", $return)) // yellowlist - mix of spam and nonspam 
			{
				echo "<td class='img_blacklist'><img src='./_img/information.png' alt='!' /></td>\n";
				echo "<td class='list_title'>".$b." - The domain's IP address is sending out a mix of spam and nonspam but is not blacklisted.</td>\n";
			}
			else if(preg_match("(127\.0\.0\.4)", $return)) // brownlist - all spam - but not yet enough to blacklist 
			{
				echo "<td class='img_blacklist'><img src='./_img/information.png' alt='!' /></td>\n";
				echo "<td class='list_title'>".$b." - Not blacklisted yet, but spam is coming from the domain's IP address.</td>\n";
			}
			else if(preg_match("(127\.0\.0\.[^1|3|4])", $return))
			{
				echo "<td class='img_blacklist'><img src='./_img/power_off.png' alt='X' /></td>\n";
				echo "<td class='list_title'>".$b." - IP address for the email's domain is blacklisted.</td>\n";
			} else {
				echo "<td class='img_blacklist'><img src='./_img/power_on.png' alt='0' /></td>\n";
				echo "<td class='list_title'>".$b." - This domain's email is NOT blacklisted.</td>\n";
			}
		}
		echo "</tr>\n";
	}
} else {
	echo "<tr>\n";
	echo "<td class='img_blacklist'><img src='./_img/warning.png' alt='X' /></td>\n";
	echo "<td>Error, please check your email address and try again.</td>\n";
	echo "</tr>\n";
}
echo "</tbody>\n";
echo "</table>\n";

?>