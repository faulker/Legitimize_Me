/*
 * $Id: //devel/tools/main/arblcheck/getopt.i#1 $
 */

/*------------------------------------------------------------------------
 * UNICODE SUPPORT
 *
 * For a few Win32 programs we need to use the generic text mappings to
 * give us Unicode or not, so we provide a small translation layer for
 * the machines that don't need it. See <tchar.h> for info on what these
 * are.
 *
 * However, if UNICODE is defined the TCHAR type is wchar_t, but if _T is
 * NOT defined, then the user forgot to include <tchar.h>. This is bad!
 */

#if ! defined(_T) && defined(_WIN32) && (defined(UNICODE)||defined(_UNICODE))
# error "missing <tchar.h> defined for unicode support"
#endif

#ifndef _T
#  define TCHAR		char
#  define _T(x)		x
#  define _tcschr(a,b)	strchr(a,b)
#  define _fputts(x,fp)	fputs(x,fp)
#  define _fputtc(x,fp)	fputc(x,fp)
#endif

/*
 * get option letter from argument vector
 */
int	optind = 1,		/* index into parent argv vector */
	optopt;			/* character checked for validity */
TCHAR	*optarg;		/* argument associated with option */

static TCHAR	EMSG[] = _T("");

#define BADCH	(int)'?'
#define tell(s)	_fputts(*nargv, stderr);\
		_fputts(s, stderr); \
		_fputtc(optopt,stderr);\
		_fputtc('\n',stderr);\
		return(BADCH)


int
getopt(int nargc, TCHAR **nargv, const TCHAR *ostr)
{
static TCHAR	*place = EMSG;		/* option letter processing */
register TCHAR	*oli;			/* option letter list index */

	if (!*place)			/* update scanning pointer */
	{
		if (optind >= nargc || *(place = nargv[optind]) != '-'
		  || !*++place) return(EOF);
		if (*place == '-')		/* found "--" */
		{
			++optind;
			return(EOF);
		}
	}				/* option letter okay? */
	if ((optopt = (int)*place++) == (int)':'
	 || (oli = _tcschr(ostr,optopt)) == 0)
	{
		if (!*place) ++optind;
		tell(_T(": illegal option -- "));
	}
	if (*++oli != ':')		/* don't need argument */
	{
		optarg = NULL;
		if (!*place) ++optind;
	}
	else				/* need an argument */
	{
		if (*place)
			optarg = place;	/* no white space */
		else if (nargc <= ++optind)	/* no arg */
		{
			place = EMSG;
			tell(_T(": option requires an argument -- "));
		}
	 	else optarg = nargv[optind];	/* white space */
		place = EMSG;
		++optind;
	}
	return(optopt);			/* dump back option letter */
}
