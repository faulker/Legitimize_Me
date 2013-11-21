/*
 * $Id: //devel/tools/main/arblcheck/arblcheck.c#2 $
 *
 * This program contains portions of code from:
 * rblcheck 1.4, Copyright (C) 1997, 1998 Edward S. Marshall
 *
 * Yiorgos Adamopoulos (adamo@ieee.org) added ADNS support.
 *
 * Steve Friedl (steve@unixwiz.net) did extensive cleanups. Steve's
 * work is in the public domain, but the underlying copyrights remain.
 */

/*
 * This is a reimplementation of rblcheck using ADNS.  Why?  Just becasue I
 * wanted to learn how to program with ADNS.
 *
 * Yiorgos Adamopoulos, adamo@ieee.org
 *
 * arblcheck: http://www.unixwiz.net/tools/arblcheck.html
 *
 * Additional code and documention by Steve Friedl (steve@unixwiz.net)
 *
 *	The ADNS library is delightful: easy to use, well documented,
 *	straightforward.  Seems to be a lot easier to use than the usual
 *	resolver library.
 *
 *	ADNS Home: http://www.chiark.greenend.org.uk/~ian/adns/
 *
 *	We've taken some additional steps here.
 *
 *	1) all RBL hosts are validated by querying for 127.0.0.2: there
 *	   seems to be a convention that this address always returns "in
 *	   the list", and it allows us to insure that this RBL host is
 *	   actually valid. Since we have to do the DNS queries to get to
 *	   that server anyway, this has the additional benefit of
 *	   "preloading" the cache with those nameserver entries.
 *
 *	   Those that are not found to be valid are not considered during
 *	   the "real" checks.
 *
 *	2) Multiple "targets" are allowed, so one can query several mail
 *	   server IPs or hostnames at a time. If a hostname is given, it
 *	   MUST resolve to at least one IP address: if more than one A
 *	   record is found, a warning is given and only the first of the
 *	   bunch is used.
 *
 *	3) Lots of general cleanups to the code. It compiles cleanly with
 *	   all the warnings turned on.
 *
 * COMMAND LINE
 * ------------
 *
 *	-h      print this brief help message, then exit.
 *
 *	-v      print the program's version information
 *
 *	-l      list the current set of DNSBL sites supported by the
 *	        program. This list is reported right when the option
 *	        is processed: subsequent behavior by "-c" and "-s" is
 *	        not reflected here.
 *
 *	-c      clear the list of DNSBL sites. This is usually done if
 *	        one wants to give a full list of DNSBL sites on the cmd
 *	        line, ignoring anything that might be built in.
 *
 *	-sSITE  toggle SITE in the RBL list: add it if not found, or
 *	        remove it if it's there now.
 *
 * EXIT CODES
 * ----------
 *
 *	0       no sites filtered, no program failures
 *
 *	1..254  # of DNSBL-filtered entries
 *
 *	255     program error of some kind
 *
 * VERSION INFORMATION
 * -------------------
 *
 * 1.4.2 - 2004-08-15
 *
 *	Updated the list of dnsbls
 *
 * 1.4.1 - 2003-10-21
 *
 *	Initial release by S. Friedl
 */
#ifdef _WIN32
# define WIN32
# define STRICT
# define _STRICT
  /* ADNS.H includes everything - bah */
#else
# include <sys/types.h>
# include <sys/time.h>
# include <arpa/inet.h>
# include <netinet/in.h>
# include <unistd.h>
# include <assert.h>
# include <string.h>
# include <stdarg.h>
# include <stdlib.h>
# include <stdio.h>
# include <ctype.h>
# include <errno.h>
#endif

#include <adns.h>

#ifdef _WIN32
# include "getopt.i"
# define snprintf _snprintf
#else
extern char *optarg;
extern int optind;
#endif

/* NOTE: this version is in a standard form */
static const char Version[] =
	"arblcheck 1.4.2 - 2004/08/15 - http://www.unixwiz.net/tools/";


/*------------------------------------------------------------------------
 * This is the list of RBL sites that we're going to check. Feel free to
 * add or subtract from this list. They need not be in any particular
 * order.
 */
static const char *rbllist[] = {

	"relays.ordb.org",

	"list.dsbl.org",
	"multihop.dsbl.org",
	"unconfirmed.dsbl.org",

	"bl.spamcop.net",

	"ipwhois.rfc-ignorant.org",

	"sbl.spamhaus.org",	/* http://www.spamhaus.org/SBL/           */

	"cbl.abuseat.org",	/* composite list */

	"l1.spews.dnsbl.sorbs.net",
	"l2.spews.dnsbl.sorbs.net",

	/*----------------------------------------------------------------
	 * This is a bizarre blacklist that seems to include *everybody*.
	 * Nobody should actually use this list, but it's decent for testing
	 * when looking for an "in the list" response.
	 */

	0		/* ENDMARKER - must be here */
};


/*------------------------------------------------------------------------
 * PORTABILITY STUFF
 *
 * We want our code to work with as little external setup as possible,
 * so we do lots of macros up front.
 *
 * __attribute__ is GNU attributes: see
 *
 * 	http://www.unixwiz.net/techtips/gnu-c-attributes.html
 */
#ifndef __GNU__
#  define __attribute__(x)	/* NOTHING */
#endif

#ifndef _WIN32
#  define __cdecl	/* NOTHING */
#  define __stdcall	/* NOTHING */
#endif


#define EXIT_OK   0
#define EXIT_FAIL 255

#ifndef MAXHOSTNAMELEN
# define MAXHOSTNAMELEN 256
#endif

#ifndef TRUE
#  define TRUE   1
#  define FALSE  0
#endif

/*------------------------------------------------------------------------
 * LIST OF RBLS
 *
 * The list of RBLs that we're using is maintained in a singly-linked list,
 * and for each one we maintain the domain name (of course), and this name
 * is validated before it's used: those DNSBL names that are not longer
 * valid (either temporarily unreachable, or permanently out of service),
 * are marked "skip" so they won't bog down the "real" search.
 */
struct rbl {
	char       *site;	/* doman name of the DNSBL    */
	int         skip;	/* invalid: skip this name!   */
	struct rbl *next;
};

static const char *ProgName = 0;

static void * chkmalloc(int size);
static char * chkstrdup(const char *s);
static void   die(const char *format, ...)
			__attribute__((noreturn))
			__attribute__((format(printf, 1, 2)));

static unsigned long query_dnsbl( adns_state adns,
                                  unsigned long addr,
                                  const char *dnsname );

static void
usage(void)
{
const char *const *pp;
static const char *const textlist[] = {
	Version,
	"",
	"usage: arblcheck [-f] [-l|v|h] | [-c -s [site [site...]]]",
	"  -h    print this message",
	"  -v    report version information",
	"  -l    list RBL sites",
	"  -c    clear list of RBL sites",
	"  -s    add a site to the list of RBL sites",
	"  -f    list filtered sites only",

	"",
	"Portions copyright Edward S. Marshall & Yiorgos Adamopoulos",
	"",

	0
};

	for (pp = textlist; *pp; pp++)
	{
		fprintf(stderr, "%s\n", *pp);
	}
}

/* This function is stolen from rblcheck-1.4 */

/* togglesite()
 * This function takes the name of the site, and either adds it to the
 * list of sites to check, or removes it if it already exists.
 */
static struct rbl *
togglesite( const char * sitename, struct rbl * sites )
{
        struct rbl * ptr;
        struct rbl * last = NULL;

	assert(sitename != 0);

        for( ptr = sites; ptr != NULL; last = ptr, ptr = ptr->next )
        {
                if( ( strlen( ptr->site ) == strlen( sitename ) ) &&
                  ( ! strcmp( ptr->site, sitename ) ) )
                {
                        if( last )
                                last->next = ptr->next;
                        else
                                sites = ptr->next;
                        free( ptr->site );
                        free( ptr );
                        return sites;
                }
        }
        ptr = ( struct rbl * )chkmalloc( sizeof( struct rbl ) );

        if( last )
                last->next = ptr;
        else
                sites = ptr;

        ptr->site = chkstrdup( sitename );
        ptr->next = NULL;
	ptr->skip = 0;

        return sites;
}

int __cdecl
main(int argc, char **argv)
{
	int c;
	unsigned long	testaddr;
	struct rbl *rblsites = NULL;
	struct rbl *ptr;
        adns_state adns;
	const char **pp;
	int        nrbl, nfiltered;
	int	filteredonly = FALSE;

	ProgName = argv[0];

	/*----------------------------------------------------------------
	 * INITIALIZE DNSBL LIST
	 *
	 * Populate the list of RBLs from the text list above.
	 */
	for (pp = rbllist; *pp; pp++ )
	{
		rblsites = togglesite( *pp, rblsites );
	}

	/*----------------------------------------------------------------
	 * PROCESS COMMAND LINE
	 *
	 * This is the usual getopt
	 */
	while ((c = getopt(argc, argv, "fvhls:c")) != EOF)
	{
		switch (c)
		{
		  case 'f':
			filteredonly = TRUE;
			break;

		  case 'v':
			puts(Version);
			exit(EXIT_OK);

		  case 'h':
			usage();
			exit(EXIT_OK);

		  case 's':
			rblsites = togglesite( optarg, rblsites );
			break;

		  case 'l':
			for (ptr = rblsites; ptr != NULL; ptr = ptr->next)
			{
				printf( "%s\n", ptr->site );
			}
			exit(EXIT_OK);

		  case 'c':
			while ( rblsites )
			{
				struct rbl *tmp = rblsites;

				rblsites = rblsites->next;

				free( tmp->site );
				free( tmp );
			}
			break;

		  default:
			usage();
			exit(EXIT_FAIL);
		}
	}

	if (optind >= argc)
	{
		usage();

		die("ERROR: no IP address/hostname specified.");
	}

	adns_init(&adns, adns_if_noenv, 0);

	/*----------------------------------------------------------------
	 * VERIFY ALL DNSBLs
	 *
	 * Check all the RBL site entries to make sure that they are even
	 * valid in the first place. There seems to be a convention that
	 * a query of 127.0.0.2 returns "filtered" for *all* DNSbls, so
	 * clearly this was put in for exactly this kind of testing.
	 *
	 * It's not fatal if we can't reach any *one* DNSBL, because an
	 * internet connectivity issue between us and them could make
	 * it temporarily unreachable.
	 *
	 * But if we can't get *any* DNSBL entries, then there is nothing
	 * we can do.
	 */
	testaddr = inet_addr("127.0.0.2");

	for (nrbl = 0, ptr = rblsites; ptr != NULL; ptr = ptr->next)
	{
	unsigned long	rc;

		rc = query_dnsbl(adns, testaddr, ptr->site);

		if ( rc == INADDR_NONE )
		{
			ptr->skip = TRUE;

			fprintf(stderr, "ERROR: %s is an invalid DNSBL\n",
				ptr->site);
		}
		else
		{
			ptr->skip = FALSE;
			nrbl++;
		}
	}

	if (nrbl == 0)
		die("ERROR: no valid DNSBL remaining");

	/*----------------------------------------------------------------
	 * CHECK ALL THE TARGETS!
	 *
	 * For all the remaining parameters on the command line, treat
	 * them as "targets" and look each one up in the list of DNSBLs.
	 * All DNSBL queries are done by IP address, so any hostnames
	 * must be looked up into IP addresses.
	 */
	for ( nfiltered = 0; optind < argc; optind++ )
	{
	char		*target = argv[optind];
	unsigned long	addr;
        adns_answer	*answer;

		/*--------------------------------------------------------
		 * FIND TARGET IP ADDRESS
		 *
		 * The user could have given us a target in dotted-quad
		 * format or in hostname format, and this section attempts
		 * to find out which is which. The result is the IP addres
		 * in the "addr" variable, or we exit with failure if we
		 * can't find it.
	 	 *
		 * Note that for hostname-based targets, we report the
		 * actual IP address used.
		 */
		adns_synchronous(adns,
			target,
			adns_r_a,
			adns_qf_search|adns_qf_owner,
			&answer);

		if (answer->status == adns_s_ok)
		{
			if ( answer->nrrs == 0 )
				die("ERROR: %s has no IP addresses!", target);

			addr = answer->rrs.inaddr[0].s_addr;

			if ( answer->nrrs > 1 )
			{
				printf("# %s has %d IP addresses (using %s)\n",
					target,
					answer->nrrs,
					inet_ntoa(answer->rrs.inaddr[0]));
			}
			else
			{
				printf("# %s has IP address %s\n",
					target,
					inet_ntoa(answer->rrs.inaddr[0]));
			}

		}
		else if ( (addr = inet_addr(target)) == INADDR_NONE )
		{
			fprintf(stderr, "%s: %s is not a valid target\n",
				argv[0], target);

			usage();

			exit(EXIT_FAIL);
		}

		/*--------------------------------------------------------
		 * CHECK ALL RBLs
		 *
		 * Run through the linked-list of all RBLs and see if this
		 * IP address is listed. We ignore any RBLs that are no
		 * longer valid, 
		 */
		for (ptr = rblsites; ptr != NULL; ptr = ptr->next)
		{
		int            filtered;
		struct in_addr rc;

			if (ptr->skip) continue;

			/*------------------------------------------------
			 * See if this address is filtered or not. It's
			 * only filtered if we get an actual answer from
			 * the DNSBL.
			 */
			rc.s_addr = query_dnsbl(adns, addr, ptr->site);

			filtered = (rc.s_addr != INADDR_NONE);

			if ( filtered )
				nfiltered++;

			/*------------------------------------------------
			 * If this is filtered *or* if we're reporting
			 * everything, send the output to the user. We
			 * build it up one line at a time.
			 */
			if ( filtered || ! filteredonly )
			{
			char    reportbuf[256],
			       *rbuf = reportbuf;

				rbuf += sprintf(rbuf, "%s %sRBL filtered by %s",
					target,
					filtered ? "" : "not ",
					ptr->site);

				if ( filtered )
				{
					rbuf += sprintf(rbuf, " (%s)",
					                inet_ntoa(rc));
				}

				puts(reportbuf);
			}
		}
	}

	adns_finish(adns);

	/*----------------------------------------------------------------
	 * Exit with the number of RBL'd filters we've seen, but limit the
	 * result to 254: 255 is a "generic failure" message. This also
	 * avoids the problem of exactly 256 failures being truncated to
	 * "0" (it's an eight-bit value only).
	 */
	if ( nfiltered > 254 )
		nfiltered = 254;

	return nfiltered;
}

/*
 * die()
 *
 *	Given a printf-style argument list, format it to the standard error
 *	stream, append a newline, and exit with error status.
 */
static void
die(const char *format, ...)
{
va_list	args;

	fprintf(stderr, "%s: ", ProgName);
	va_start(args, format);
	vfprintf(stderr, format, args);
	va_end(args);
	putc('\n', stderr);

	exit(EXIT_FAIL);
}

/*
 * chkstrdup()
 * chkmalloc()
 *
 *	These two simple wrapper functions allocate the requested memory and
 *	exit on any failures. This allows the callers to avoid having to
 *	think about memory errors.
 */

static char *
chkstrdup(const char *s)
{
	char *q = strdup(s);

	assert(s != 0);

	if ( q == 0 )
		die("ERROR: cannot duplicate string {%s}", s);

	return q;
}

static void *
chkmalloc(int size)
{
void	*p = malloc(size);

	if ( p == 0 )
		die("ERROR: cannot alloc %d bytes of mem", size);

	return p;
}

/*
 * query_dnsbl()
 *
 *	Given an ADNS state, an IP address in *network* word order, and the
 *	DNSBL full name, return the network word order result. Return is
 *	INADDR_NONE if the query was unsuccessful, though this usually means
 *	that the address in question is *not* blacklisted. This is not a
 *	"failure" case.
 */
static unsigned long query_dnsbl( adns_state    adns,
                                  unsigned long addr,
                                  const char    *dnsname)
{
char          querybuf[256];
adns_answer  *answer;

	assert(dnsname != 0);

	addr = ntohl(addr);	/* now in host word order */

	snprintf(querybuf, sizeof querybuf, "%d.%d.%d.%d.%s",
		(int)( (addr >>  0) & 0xFF ),
		(int)( (addr >>  8) & 0xFF ),
		(int)( (addr >> 16) & 0xFF ),
		(int)( (addr >> 24) & 0xFF ),
		dnsname );

	adns_synchronous(adns, querybuf, adns_r_a, adns_qf_owner, &answer);

	if ( answer->status != adns_s_ok
	  || answer->nrrs   == 0 )
	{
		return INADDR_NONE;
	}

	return answer->rrs.inaddr[0].s_addr;
}
