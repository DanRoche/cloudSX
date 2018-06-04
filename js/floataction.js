function AdjustFloatAction() {
    var ep = $('#endfiles').position();
    var h1 = $(document).height();
    var g1 = h1 - ep.top;
    // valeur 200 prise au pif,  le calcul avec la hauteur de floataction n'est pas viable !!
    if ( g1 > 200 ) {
	$('#floatactions').css('top',ep.top);   
    }
}
