<?php
/*
	local regex check = /^((?:\.|\+|\!|\$|\&|\*|\-|\=|\^|\`|\||\~|\#|\%|\'|\/|\?|\_|\{|\}|\s|\@|\w)*)/i
	domain regex check = /(?:[\w-]+\.){1,255}/i
*/
$email = strtolower(urldecode(stripslashes($_REQUEST['e']))); // Email address

#######################################################
##
##  Build Top-Level Domain list
##
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
##
#######################################################



#######################################################
##
##  Email validation section
##
$regex = strtolower('/^((?:(\\\")|\.|\+|\!|\$|&|\*|\-|\=|\^|\`|\||\~|\#|\%|\'|\/|\?|\_|\{|\}|\s|\@|\w)*)@(?:[\w-]+\.){1,255}'.$regex_tld.'$/');
preg_match($regex, $email, $match);
if($match[0] === $email)
{
	echo "<span class='is_valid'>- RFC VALID -</span>\n";
} else {
	echo "<span class='not_valid'>- NOT RFC VALID -</span>\n";
}
echo "<hr />\n";
##
#######################################################
?>