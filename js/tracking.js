/*------------------------------------------------------------*/
$(function() {
	trackingPaintRows(document);
	/*	$(".imgToolTip").imgToolTip();	*/
	$(".showImage").showImage();
});
/*------------------------------------------------------------*/
function trackingPaintRows(context)
{
	$(".mRow", context).hoverClass("hilite");
	$(".trackingRow", context).hoverClass("hilite");
	$(".mFormRow", context).hoverClass("hilite");
	$(".mHeaderRow", context).addClass("trackingZebra0");
	$(".trackingHeaderRow", context).addClass("trackingZebra0");
	$(".mFormRow:nth-child(odd)", context).addClass("trackingZebra1");
	$(".mFormRow:nth-child(even)", context).addClass("trackingZebra2");
	$(".mRow:nth-child(odd)", context).addClass("trackingZebra1");
	$(".mRow:nth-child(even)", context).addClass("trackingZebra2");
	$(".trackingRow:nth-child(odd)", context).addClass("trackingZebra2");
	$(".trackingRow:nth-child(even)", context).addClass("trackingZebra1"); // first row is 1
	$(".trackingFormRow:nth-child(odd)", context).addClass("trackingZebra2");
	$(".trackingFormRow:nth-child(even)", context).addClass("trackingZebra1"); // first row is 1

	$(".today:nth-child(odd)", context).addClass("trackingZebra3");
	$(".today:nth-child(even)", context).addClass("trackingZebra4");
	$(".yesterday:nth-child(odd)", context).addClass("trackingZebra5");
	$(".yesterday:nth-child(even)", context).addClass("trackingZebra6");

}
/*------------------------------------------------------------*/
