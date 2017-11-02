$(function() {

    /* Add Magic Line markup via JavaScript, because it ain't gonna work without */
    $("#nav_ul").append("<li id='magic-line'></li>");
    
    /* Cache it */
    var $magicLine = $("#magic-line");
    
    $magicLine
        .width($(".current_page_item").width())
		.css("left", $(".current_page_item a").position().left);
   
    $("#nav li").find("a").hover(function() {
        $el = $(this);
        leftPos = $el.position().left;
        newWidth = $el.parent().width();
        
        $magicLine.stop().animate({
            left: leftPos,
            width: newWidth
        });
    }, function() {
        $magicLine.stop().animate({
            left: $magicLine.data("origLeft"),
            width: $magicLine.data("origWidth")
        });    
    });
    
	$("#search_close").attr("style","display:none");
	$("#search_close").click(function(){
		$("#search_box").val("");
		$("#search_close").attr("style","display:none");
		});
	
});
/*根据输入框的内容来判断是否显示x号*/
function search_box(){
	var search_box = $("#search_box").val();
	if(search_box){$("#search_close").attr("style","");} else{$("#search_close").attr("style","display:none");};
	}