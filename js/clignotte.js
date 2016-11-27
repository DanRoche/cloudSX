function switchlight(cnt) {
    if ( cnt % 2  == 0 ) {
	$('#attchbtn').css('background-color','#acc1d7');
    } else {
	$('#attchbtn').css('background-color','');
    }
    cnt -= 1;
    if (cnt > 0 ) {
	setTimeout(switchlight, 250, cnt);
    }
}

function clignotte() {
    /* must be even, otherwize do not return to original color ! */
    switchlight(16);
}
