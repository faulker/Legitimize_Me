$(document).ready(function()
{
	$("#email").live('keypress', function(e){ if(e.which==13){ load_blacklist(); }});
	$("#check_email").live('click', function(){ load_blacklist(); });
	
	var load_blacklist = function()
	{
		$("#blacklist_loading").show();
		$("#blacklist_list").load("./_inc/blacklist_ajax.php", {"e":$("#email").val()});
		$("#rfc_valid").load("./_inc/rfc_ajax.php", {"e":$("#email").val()});
		$("#blacklist_loading").hide();
	}
});