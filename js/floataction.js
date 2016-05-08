function AdjustFloatAction() {
   var ep = $('#endfiles').position();
   var h = $(document).height();
   if ( ep.top < h ) {
     $('#floatactions').css('top',ep.top);   
   }
}
