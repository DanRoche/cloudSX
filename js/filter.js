function GlobalFilter() {
    var filt_doc = $("#searchDoc").val();
    var filt_mod = $("#searchMod").val();
    var filt_eol = $("#searchEOL").val();

    if (typeof filt_doc == 'undefined') {
	filt_doc = ""
    }
    if (typeof filt_mod == 'undefined') {
	filt_mod = ""
    }
    if (typeof filt_eol == 'undefined') {
	filt_eol = ""
    }

    if (window.XMLHttpRequest) {
        // code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp=new XMLHttpRequest();
    } else {
        // code for IE6, IE5
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }
    var xurl = "FilterSave?FD="+filt_doc+"&FM="+filt_mod+"&FE="+filt_eol;
    xmlhttp.open("GET",xurl,true);
    xmlhttp.send();

    var cnt1sel = 0;
    var cnt1tot = 0;

    $('#dostable').find('.datarow').each(function(){
	var thetr = $(this);
 
	var thedoc = thetr.find('.dnam').text();
	var themod = thetr.find('.dmod').text();
	var theeol = thetr.find('.deol').text();

	var score=0
	if ( filt_doc == "" || thedoc.indexOf(filt_doc) != -1 ) {
	    score = score + 1;
	}
	if ( filt_mod == "" || themod.indexOf(filt_mod) != -1 ) {
	    score = score + 1;
	}
	if ( filt_eol == "" || theeol.indexOf(filt_eol) != -1 ) {
	    score = score + 1;
	}

	cnt1tot += 1;
	if ( score == 3 ) {
	    thetr.show();
	    cnt1sel += 1;
	} else {
	    thetr.hide();
	}

    })

    if (  cnt1sel == cnt1tot ) {
	$("#DosCnt").text(cnt1sel);
    } else {
	$("#DosCnt").text(cnt1sel+"/"+cnt1tot);
    }

}

function GlobalUzFilter() {
    var filt_mel = $("#searchMel").val();
    var filt_nam = $("#searchNam").val();
    var filt_cre = $("#searchCre").val();
    var filt_pay = $("#searchPay").val();

    if (typeof filt_mel == 'undefined') {
	filt_mel = ""
    }
    if (typeof filt_nam == 'undefined') {
	filt_nam = ""
    }
    if (typeof filt_cre == 'undefined') {
	filt_cre = ""
    }
    if (typeof filt_pay == 'undefined') {
	filt_pay = ""
    }

    if (window.XMLHttpRequest) {
        // code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp=new XMLHttpRequest();
    } else {
        // code for IE6, IE5
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }
    var xurl = "FilturSave?F1="+filt_mel+"&F2="+filt_nam+"&F3="+filt_cre+"&F4="+filt_pay;
    xmlhttp.open("GET",xurl,true);
    xmlhttp.send();

    var cnt2sel = 0;
    var cnt2tot = 0;

    $('#uzrtable').find('.datarow').each(function(){
	var thetr = $(this);
 
	var themel = thetr.find('.umel').text();
	var thenam = thetr.find('.unam').text();
	var thecre = thetr.find('.ucre').text();
	var thepay = thetr.find('.upay').text();

	var score=0
	if ( filt_mel == "" || themel.indexOf(filt_mel) != -1 ) {
	    score = score + 1;
	}
	if ( filt_nam == "" || thenam.indexOf(filt_nam) != -1 ) {
	    score = score + 1;
	}
	if ( filt_cre == "" || thecre.indexOf(filt_cre) != -1 ) {
	    score = score + 1;
	}
	if ( filt_pay == "" || thepay.indexOf(filt_pay) != -1 ) {
	    score = score + 1;
	}

	cnt2tot += 1;
	if ( score == 4 ) {
	    thetr.show();
	    cnt2sel += 1;
	} else {
	    thetr.hide();
	}

    })

    if (  cnt2sel == cnt2tot ) {
	$("#UzrCnt").text(cnt2sel);
    } else {
	$("#UzrCnt").text(cnt2sel+"/"+cnt2tot);
    }

}

