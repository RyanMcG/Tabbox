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
		//Prepare tb-selector-piece elements to be clicked on
		$(".tb-selector-piece").click(function ()
		{
			numtabs = $(this).siblings().length - 1;
			tab_selectPiece(this);
		});
		tab_initAll();
	}
});

function tab_initAll() //Clicks the default elements to be displayed
{
	$j(".tb").each(function ()
	{
		
		tab_init(this, true);
	});
}

function tab_init(tb, next)
{
	$j(tb).data('action', 'getmoretabs');
	$j(tb).css('border'); 
	$j(tb).children("form.tb-data").find("input:hidden").each(function ()
	{
		$j(tb).data($j(this).attr("name"), $j(this).val());
	});
	if(next == false)	//Select the the correct piece of this tabbed box.
	{
		$j(tb).find(".tb-selector-piece").eq(numtabs).click();
	}
	else
	{
		$j(tb).find(".tb-selector-piece:eq(1)").click();	
	}
}

function tab_selectPiece(obj) //Selects the correct tab by making its contents visible and making the selected tab "active".
{
	var i = $j(obj).index();
	if(i == 0 || i == numtabs+1)
	{
		var active_piece = $j(obj).siblings(".tb-selector-piece-active");
		if($j(active_piece).index() != numtabs && i == numtabs+1)
		{
			$j(active_piece).next().click();
		}
		else if($j(active_piece).index() != 1 && i == 0)
		{
			$j(active_piece).prev().click();
		}
		else
		{
			var next = true;
			if(i == 0)
			{
				next = false;
			}
			tab_getMore(next, obj);
		}
	}
	else
	{
		$j(obj).siblings(".tb-selector-piece").removeClass("tb-selector-piece-active");
		$j(obj).addClass("tb-selector-piece-active");
		$j(obj).parent().siblings(".tb-content").hide();
		$j(obj).parent().siblings(".tb-content").eq(i-1).show();
	}
}

function tab_getMore(next, obj)
{
	var tb = $j(obj).parents(".tb").first();
	var data = $j(tb).data();
	data['next'] = next;
	$j.post(tabbox_stuff.ajaxurl, data, function (response)
	{
		if(response != "-1" && response != "-2" && response != "-3")
		{
			$j(tb).html(response);
			$j(".tb-selector-piece").click(function ()
			{
				numtabs = $j(this).siblings().length - 1;
				tab_selectPiece(this);
			});
			tab_init(tb, next);
		}
	});
}