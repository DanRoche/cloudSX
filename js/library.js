function UserAgent() {
 if ( navigator.userAgent.indexOf("Gecko") == -1 ) {
 return "ie";
 } else {
  if ( navigator.userAgent.indexOf("Safari") == -1 ) {
   return "moz";
  } else {
   return "saf"
  }
 }
}

function GetElemById(elmid) {
 if ( UserAgent() == "ie" ) {
  return eval("document.all."+elmid)
 } else {
  return document.getElementById(elmid)
 } 
}

function ChangeElementClass(elmid, newclass) {
 if ( UserAgent() == "ie" ) {
  eval("document.all."+elmid+".className = '"+newclass+"'")
 } else {
  document.getElementById(elmid).className = newclass;
 } 
}

function ChangeElementSrc(elmid, newsrc) {
 if ( UserAgent() == "ie" ) {
  eval("document.all."+elmid+".src = '"+newsrc+"'")
 } else {
  document.getElementById(elmid).src = newsrc;
 } 
}

function ChangeElementValue(elmid, newval) {
 if ( UserAgent() == "ie" ) {
  eval("document.all."+elmid+".value = '"+newval+"'")
 } else {
  document.getElementById(elmid).value = newval;
 } 
}

function ChangeElementPosition(elmid, posx, posy) {
 elem = GetElemById(elmid)
 elem.style.top = posy
 elem.style.left = posx
}
