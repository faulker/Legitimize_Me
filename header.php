<?php
	$starttimer = time()+microtime();
	$email		= 'admin [ at ] legitimize dot me';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--
// |******************************************************************
// | Copyright(c) 2004-2007. Winter Faulk <?=$email?> 
// |******************************************************************
-->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
	<title>Legitimize :: <?=$page_title;?></title>
	<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1' />
	<meta name='keywords' content='valid email, email, validation, RFC5322, compliant, RFC2822' />
	<meta name='description' content='Check to see if an email address is RFC5322/2822 compliant.' />
	<meta http-equiv='X-UA-Compatible' content='IE=7' />
	<link rel='stylesheet' type='text/css' href='./local.css' />
</head>
<body>
<!-- <a href="http://legitimize.me/_inc/dolphins.php">raspy-musical</a> -->
<div id='content'>
	<div id='heading'>
		<a id='logo' href='http://legitimize.me/' title='legitimize.me'>Legitimize Me</a><br />
		Email Blacklist Check
		<div id='menu'>
			&#123;&nbsp;<a href='http://legitimize.me/' title='Email Check'>Email Check</a>&nbsp;&#125;&#123;&nbsp;<a href='about.php' title='About'>About</a>&nbsp;&#125;&#123;&nbsp;<a href='api.php' title='API'>API</a>&nbsp;&#125;
		</div>
	</div>
	<div id='main_body'>