var IE = document.all?true:false;
function eid(idNamed)
{
	var setId;
	var browser = navigator.appName;
	if(browser == "Microsoft Internet Explorer"){
		
		setId = document.all[idNamed];
	}else{
		setId = document.getElementById(idNamed);
	}
	return setId;
}

function createRequestObject(handler)
{
	var xmlHttp;
	try
	{
		// Firefox, Opera 8.0+, Safari
		var xmlHttp = new XMLHttpRequest();
		xmlHttp.onload=handler;
		xmlHttp.onerror=handler;
		return xmlHttp;
	}
	catch (e)
	{
		// Internet Explorer
		try
		{
			xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
			return xmlHttp;
		}
		catch (e)
		{
			try
			{
				xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
				return xmlHttp;
			}
			catch (e)
			{
				alert("Your browser does not support AJAX!");
				return false;
			}
		}
	}
}
/*********************************************************************************************************************************/

function check_rfc(id)
{
	var valid = '';
	var email = escape(eid(id).value);
	var page = 'ajax_rfc.php';
	var pass = 'e='+email;

	var http = createRequestObject();
	http.onreadystatechange = function()
	{
		if((http.readyState == 4) && (http.status == 200))
		{
			var id = eid('rfc_valid');
			remove_all_chilren(id);
			
			var msg = http.responseText;
			if(msg == 1)
			{
				var span = document.createElement("span");
					if(IE)
					{
						span.className = 'is_valid';
					} else {
						span.setAttribute('class', "is_valid");	
					}
						span.appendChild(document.createTextNode('RFC VALID'));
			} else {
				var span = document.createElement("span");
					if(IE)
					{
						span.className = 'not_valid';
					} else {
						span.setAttribute('class', "not_valid");
					}
						span.appendChild(document.createTextNode('NOT RFC VALID'));
			}
			id.appendChild(span);
			id.appendChild(document.createElement("hr"));
		}
	}
	http.open('POST', page, true);
	http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	http.setRequestHeader("Content-length", pass.length);
	http.setRequestHeader("Connection", "close");
	http.send(pass);
}

function check_blacklist(id, blacklist)
{
	eid('blacklist_loading').style.display = "inline";
	var email = escape(eid(id).value);
	var page = 'ajax_blacklist.php';
	var pass = 'e='+email+'&b='+blacklist;

	var http = createRequestObject();
	http.onreadystatechange = function()
	{
		if((http.readyState == 4) && (http.status == 200))
		{
			var output = "";
			var msg = http.responseText;
			if(msg == "99") // IP address error
			{
				add_element("blacklist_valid", "./warning.png", "Error, please check your email address and try again.")
			}
			else if(msg == "2" || msg == "4") // Blacklisted
			{
				add_element("blacklist_valid", "./power_off.png", blacklist+" has this email's domain's IP address marked as being blacklisted.")
			} else { // Other or not blacklisted.
				add_element("blacklist_valid", "./power_on.png", blacklist+" is not blacklisting this email's domain's IP address.")
			}
			eid('blacklist_loading').style.display = "none";
		}
	}
	http.open('POST', page, true);
	http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	http.setRequestHeader("Content-length", pass.length);
	http.setRequestHeader("Connection", "close");
	http.send(pass);
}

function add_element(id, data1, data2)
{
	var tr = document.createElement('tr');
		if(IE)
		{
			tr.className = 'result_blacklist';
		} else {
			tr.setAttribute('class','result_blacklist');
		}

	var td1 = document.createElement('td');
		if(IE)
		{
			td1.className = 'img_blacklist';
		} else {
			td1.setAttribute('class','img_blacklist');
		}
	
	var img = document.createElement('img');
		img.setAttribute('src',data1);
		img.setAttribute('alt', '*');
	
	var td2 = document.createElement('td');

	var append_list = eid(id);
		append_list.appendChild(tr);
		tr.appendChild(td1);
		td1.appendChild(img);
		tr.appendChild(td2);
		td2.appendChild(document.createTextNode(data2));
}

function check_email(id)
{
	remove_all_chilren(eid('blacklist_valid'));
	var ip_blacklists = new Array();
		ip_blacklists[0] = 'zen.spamhaus.org';
		ip_blacklists[1] = 'bl.spamcannibal.org';
		ip_blacklists[2] = 'cbl.abuseat.org';
		ip_blacklists[3] = 'dnsbl.sorbs.net';
		ip_blacklists[4] = 'dnsbl-1.uceprotect.net';
		ip_blacklists[5] = 'dnsbl-2.uceprotect.net';
		ip_blacklists[6] = 'dnsbl-3.uceprotect.net';
		ip_blacklists[7] = 'db.wpbl.info';
		ip_blacklists[8] = 'hostkarma.junkemailfilter.com';
		ip_blacklists[9] = 'bl.spamcop.net';
	
	check_rfc(id);
	for(var i=0;i<ip_blacklists.length;i++)
	{
		check_blacklist(id, ip_blacklists[i]);
	}
}

function check_key(id, e)
{
	var charCode;

	if(e && e.which)
	{
		charCode = e.which;
	}
	else if(window.event)
	{
		e = window.event;
		charCode = e.keyCode;
	}

	if(charCode == 13)
	{
		check_email(id);
	}
}

function remove_all_chilren(id)
{
	if(id.hasChildNodes())
	{
		while(id.childNodes.length >= 1)
		{
			id.removeChild(id.firstChild);
		} 
	}
}