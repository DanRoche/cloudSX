function DoResize(event) {
    //$('#debug1').text(event.pageX + ", " + event.pageY);
    ResizeMC(event.pageX);
}

function StopResize(event) {
    //$('#debug1').text("STOP");

    $('#pgmenu').removeClass('no-selection');
    $('#pgtop').removeClass('no-selection');
    $('#pgcont').removeClass('no-selection');

    $('#onepage').off("mousemove");

    RegisterPosition();
}

function StartResize(event) {
    //$('#debug1').text("START");
    
    $('#pgmenu').addClass('no-selection');
    $('#pgtop').addClass('no-selection');
    $('#pgcont').addClass('no-selection');

    $('#onepage').mousemove(DoResize);
    $('#onepage').mouseup(StopResize);


}

function ResizeMCInit() {

 $('#pgresizer').mousedown(StartResize);

}

function ResizeMC(x) {
    
    //$('#debug1').text("resizeMC: " + x );

    $('#pgmenu').css('width',x);   
    $('#menu_docs').css('width',x-14);
    $('#pgresizer').css('left',x);   
    $('#pgcont').css('margin-left',x+5);   
    $('#conthead').css('left',x+10);   

    if ( x < 25 ) {
	$('#pgmenu').css('display','none');
    } else {
	$('#pgmenu').css('display','block');
    }   
    
    $('#prvbutn').css('left', x+30); 
}

function RegisterPosition() {
    var lft = $('#pgresizer').css('left');
    var jid = $('#did').val();
    Cookies.set("CSXLEFT_"+jid, lft.replace("px",""));
    //$('#debug1').text("JP=" + jid + "_" + lft );
}


function RecallPosition() {
    var jid = $('#did').val();
    var lpos = Cookies.get("CSXLEFT_"+jid);

    if ( lpos === undefined ) {
	return;
    }
    var xpos = parseInt(lpos);
    ResizeMC(xpos);
}

