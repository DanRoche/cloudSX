function changeContent(url) {
  //alert('in change content');

  $.get(url).done(function(data) {
    $('#pgcont').html(data);
  }).fail(function(data) {
    $('#pgcont').html("<h1> ERROR </h1>");
  })

}

function adjustContentLeft() {
   // fetch pgcont margin left 
   // to eventually inject in conthead left css
   var pl = $('#pgcont').css('margin-left');
   var i1 = pl.indexOf("px");
   var pn = parseInt(pl.substr(0,i1))
   $('#conthead').css('left',pn+5);   
   $('#prvbutn').css('left',pn+25);   
}


function contentRename() {
    $('#menuFileAction').val("REN");
    contentAction();
}

function contentDelete() {
    $('#menuFileAction').val("DEL");
    contentAction();
}

function contentGetZip() {
    $('#menuFileAction').val("ZIP");
    contentAction();
}

function contentAction() {

    $('#menuFileForm').submit(function( event ) {
 
	// Stop form from submitting normally
	event.preventDefault();
 
	var url = $(this).attr( "action" );
	var formData = new FormData($(this)[0]);
	//formData.append("PLOUC", "plic");

	$.ajax({
	    url: url,
	    type: 'POST',
	    data: formData,
	    async: false,
	    cache: false,
	    contentType: false,
	    processData: false,
	    success: function (returndata) {
		$('#pgcont').html(returndata);
	    },
	    error: function (returndata) {
		$('#pgcont').html("<h1> POST-ERROR </h1>");
	    }
	});
	
    });

    $('#menuFileForm').submit();    
}

function contentMailSend() {
    //alert("Sending Mail ");

    $('#melShareForm').submit(function( event ) {
 
	// Stop form from submitting normally
	event.preventDefault();
 
	var url = $(this).attr( "action" );
	var formData = new FormData($(this)[0]);

	$.ajax({
	    url: url,
	    type: 'POST',
	    data: formData,
	    async: false,
	    cache: false,
	    contentType: false,
	    processData: false,
	    success: function (returndata) {
		$('#pgcont').html(returndata);
	    },
	    error: function (returndata) {
		$('#pgcont').html("<h1> MAIL-SEND-ERROR </h1>");
	    }
	});
	
    });

    $('#melShareForm').submit();    
}

