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

	if ( score == 3 ) {
	    thetr.show();
	} else {
	    thetr.hide();
	}

    })

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

	if ( score == 4 ) {
	    thetr.show();
	} else {
	    thetr.hide();
	}

    })

}

