/*
 * Tab selected box
 * Author: Ryan McGowan (StudioCT)
 */
 
 var $j = jQuery;
 var numtabs;
 
$j(document).ready(function ($)
{
	if($(".tb").length > 0)
	{
		$(".tb-content").hide();
		var minheight = $j(".tb .tb-selector-bar").height() - 22;
		$j(".tb .tb-content").css({minHeight: minheight});
		//Prepare tb-selector-piece elements to be clicked on
		$(".tb-selector-piece").click(function ()
		{
			tab_selectPiece(this);
		});
		$j(".tb").find(".tb-selector-piece:first").click();
	}
});

function tab_selectPiece(obj) //Selects the correct tab by making its contents visible and making the selected tab "active".
{
	var i = $j(obj).index();
	$j(obj).siblings(".tb-selector-piece").removeClass("active");
	$j(obj).addClass("active");
	$j(obj).parent().siblings(".tb-content").hide();
	$j(obj).parent().siblings(".tb-content").eq(i).show();
}