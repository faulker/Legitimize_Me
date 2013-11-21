#!/usr/bin/perl								
#- Copyright (C) 2003 Marcin Gondek <drixter@e-utp.net>
#-
#- This program is free software; you can redistribute it and/or modify
#- it under the terms of the GNU General Public License as published by
#- the Free Software Foundation; either version 2, or (at your option)
#- any later version.
#-
#- This program is distributed in the hope that it will be useful,
#- but WITHOUT ANY WARRANTY; without even the implied warranty of
#- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#- GNU General Public License for more details.
#-
#- You should have received a copy of the GNU General Public License
#- along with this program; if not, write to the Free Software
#- Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.

# Setting output buffer

$| = 1; 

# Loading libraries.

use Net::DNS;
use Term::ANSIColor qw(:constants);

# About

 my $ver="0.0.1.1";
 my $verbose="no";
 print "RBL Lookup v.$ver\n";
 print "Copyright (c) 2003 Marcin Gondek <drixter\@e-utp.net>\n";
 print "\n";

# Sorting IP/DNS

 @iaddr = gethostbyname($ARGV[0]);
 if ( ! defined @iaddr ) {die "Network Error / Wrong IP/HOST";}
 if ( defined @iaddr ) {($a,$b,$c,$d) = unpack('C4', @iaddr[4]);}

 if ($ARGV[1] eq "-v") {$verbose="yes";}
 
 print "Checking $a.$b.$c.$d...\n";

# Main

# Numbers of servers

my @servers_no=(0,75,15,13,3);

# RBL servers

my @serversA = ("sbl.spamhaus.org","blacklist.spambag.org","blackholes.five-ten-sg.com","blackholes.intersil.net","block.blars.org","bl.spamcop.net","blackholes.easynet.nl","wpb.bl.reynolds.net.au","mail-abuse.blacklist.jippg.org","blackhole.compu.net","spamguard.leadmon.net","3y.spam.mrs.kithrup.com","dnsbl.njabl.org","xbl.selwerd.cx","spam.wytnij.to","t1.bl.reynolds.net.au","ricn.bl.reynolds.net.au","rmst.bl.reynolds.net.au","ksi.bl.reynolds.net.au ","rbl.rope.net","rbl.ntvinet.net","no-more-funn.moensted.dk","list.dsbl.org","unconfirmed.dsbl.org","ipwhois.rfc-ignorant.org","in.dnsbl.org","spam.dnsrbl.net","blackholes.uceb.org","sbbl.they.com","rsbl.aupads.org","hil.habeas.com","bl.deadbeef.com","intruders.docs.uu.se","bl.technovision.dk","spam.exsilia.net","mail.people.it","blocklist.squawk.com","blocklist2.squawk.com","rbl.fnidder.dk","bl.borderworlds.dk","dnsbl.delink.net","blocked.hilli.dk","blacklist.sci.kun.nl","rbl.schulte.org","forbidden.icm.edu.pl","msgid.bl.gweep.ca","dnsbl.sorbs.net","spam.dnsbl.sorbs.net","vox.schpider.com","query.trustic.com","dnsbl.isoc.bg","satos.rbl.cluecentral.net","spamsources.dnsbl.info","blacklist.woody.ch","all.spamblock.unit.liu.se","lbl.lagengymnastik.dk","rbl.firstbase.com","bl.tolkien.dk","reject.the-carrot-and-the-stick.com","ip.rbl.kropka.net","all.rbl.kropka.net","psbl.surriel.com","dnsbl.antispam.or.id","map.spam-rbl.com","probes.bl.reynolds.net.au","cbl.abuseat.org","dnsbl.solid.net","will-spam-for-food.eu.org","dnsbl.jammconsulting.com","spamsources.yamta.org","rbl-plus.mail-abuse.org","fresh.dict.rbl.arix.com","stale.dict.rbl.arix.com","fresh.sa_slip.rbl.arix.com","blackholes.alphanet.ch");

# Open Relay servers

my @serversB = ("relays.mail-abuse.org","relays.ordb.org","dev.null.dk","omrs.bl.reynolds.net.au","osrs.bl.reynolds.net.au","multihop.dsbl.org","orvedb.aupads.org","relays.nether.net","unsure.nether.net","relays.bl.gweep.ca","smtp.dnsbl.sorbs.net","or.rbl.kropka.net","relays.bl.kundenserver.de","relays.visi.com","relaywatcher.n13mbl.com");

# Open Proxy servers

my @serversC = ("proxies.relays.monkeys.com","proxies.exsilia.net","proxy.bl.gweep.ca","proxies.blackholes.easynet.nl","op.rbl.kropka.net","opm.blitzed.org","owps.bl.reynolds.net.au","ohps.bl.reynolds.net.au","osps.bl.reynolds.net.au","http.dnsbl.sorbs.net","socks.dnsbl.sorbs.net","misc.dnsbl.sorbs.net","pss.spambusters.org.ar");

# Open FormMail servers

my @serversD = ("web.dnsbl.sorbs.net","formmail.relays.monkeys.com","form.rbl.kropka.net");

# Setting results

my @result_ok = (0,0,0,0,0);
my @result_fail = (0,0,0,0,0);
my @result_total = (0,0,0,0,0);

# Initializing main variables

my $total_server_list=5;
my $current=0;
my $ok=0;
my $fail=0;
my $collection=1;

# DNS Timeouts

$tcp_timeout=10;
$udp_timeout=10;

# Query All by one connect (1=true, 0=false)

$persistent_tcp=1;

# Show status

 my $dns  = Net::DNS::Resolver->new;
 @nameservers = $dns->nameservers;
 print "Name server    : ",$nameservers[0],"\n";
 print "TCP timeout    : ",$tcp_timeout, "\n";
 print "UDP timeout    : ",$udp_timeout, "\n";
 if ($persistent_tcp=="1")
 {
  print "Persistent mode: True\n";
 } 
 if ($persistent_tcp=="0")
 {
  print "Persistent mode: False\n";
 } 


while ($total_server_list>$collection)
{
  if ($collection==1){print "\nRBL Scan...\n";}
  if ($collection==2){print "\nOpen Relay Scan...\n";}
  if ($collection==3){print "\nOpen Proxy Scan...\n";}
  if ($collection==4){print "\nOpen FormMail Scan...\n";}
 while ($current<$servers_no[$collection])
 { 
   if ($verbose eq "yes")
   {
    if ($collection==1){print $serversA[$current],"...";}
    if ($collection==2){print $serversB[$current],"...";}
    if ($collection==3){print $serversC[$current],"...";}
    if ($collection==4){print $serversD[$current],"...";}
   }
   if ($verbose eq "no"){print ".";} 
   my $res  = Net::DNS::Resolver->new;
   $res->tcp_timeout($tcp_timeout);
   $res->udp_timeout($udp_timeout);
   $res->persistent_tcp($persistent_tcp);
   if ($collection==1)
   {$query = $res->query("$d.$c.$b.$a.@serversA[$current]", "A");}
   if ($collection==2)
   {$query = $res->query("$d.$c.$b.$a.@serversB[$current]", "A");}
   if ($collection==3)
   {$query = $res->query("$d.$c.$b.$a.@serversC[$current]", "A");}
   if ($collection==4)
   {$query = $res->query("$d.$c.$b.$a.@serversD[$current]", "A");}
   if ($query)
   {
          foreach $rr (grep { $_->type eq 'A' } $query->answer)
	  {
	   if ($verbose eq "yes")
	     {
	      print "[",BOLD, RED, "LISTED", CLEAR, "]\n";
	     }
           $fail++
	  }
   }
   else 
   {
      if ($verbose eq "yes"){print "[", BOLD, GREEN, "clean", CLEAR,"]\n";}
      $ok++
   }
  $current++
 }

# Saving results

@result_ok[$collection]=$ok;
@result_fail[$collection]=$fail;
@result_total[$collection]=$current;

# Seting variables

$collection++;
$ok=0;
$fail=0;
$current=0;
}

# Printing results

print "\nRBL status:  ( OK / Listed / Total )\n";
print $result_ok[1], " ", $result_fail[1], " ", $result_total[1], "\n";
print "\nOpen Relay status:  ( OK / Listed / Total ) \n";
print $result_ok[2], " ", $result_fail[2], " ", $result_total[2], "\n";
print "\nOpen Proxy status: ( OK / Listed / Total )\n";
print $result_ok[3], " ", $result_fail[3], " ", $result_total[3], "\n";
print "\nOpen FormMail status: ( OK / Listed / Total )\n";
print $result_ok[4], " ", $result_fail[4], " ", $result_total[4], "\n";

# END 