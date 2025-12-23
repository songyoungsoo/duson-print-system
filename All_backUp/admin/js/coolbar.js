var coolbutton_crLeft   = "buttonhighlight";
var coolbutton_crRight  = "buttonshadow";
var coolbutton_crTop    = "buttonhighlight";
var coolbutton_crBottom = "buttonshadow";
var coolbutton_cpLeft   = "buttonshadow";
var coolbutton_cpRight  = "buttonhighlight";
var coolbutton_cpTop    = "buttonshadow";
var coolbutton_cpBottom = "buttonhighlight";

document.onmouseover = doOver;
document.onmouseout  = doOut;
document.onmousedown = doDown;
document.onmouseup   = doUp;

function changeRaisedColor (left, right, top, bottom)
  {
    if ("" != left)   coolbutton_crLeft   = left;
    if ("" != right)  coolbutton_crRight  = right;
    if ("" != top)    coolbutton_crTop    = top;
    if ("" != bottom) coolbutton_crBottom = bottom;
  }

function changePressedColor (left, right, top, bottom)
  {
    if ("" != left)   coolbutton_cpLeft   = left;
    if ("" != right)  coolbutton_cpRight  = right;
    if ("" != top)    coolbutton_cpTop    = top;
    if ("" != bottom) coolbutton_cpBottom = bottom;
  }

function doOver ()
  {
    var toEl = getReal(window.event.toElement, "className", "coolButton");
    var fromEl = getReal(window.event.fromElement, "className", "coolButton");
    if (toEl == fromEl) return;
    var el = toEl;
    
    var cDisabled = el.cDisabled;
    cDisabled = (cDisabled != null);
    
    if ( (el.className == "coolButton") || (el.className == "coolButton2") )
      el.onselectstart = new Function("return false");
    
    if ( ( (el.className == "coolButton") || (el.className == "coolButton2") ) && !cDisabled)
      {
        makeRaised(el);
        makeGray(el,false);
      }
  }

function doOut ()
  {
    var toEl = getReal(window.event.toElement, "className", "coolButton");
    var fromEl = getReal(window.event.fromElement, "className", "coolButton");
    if (toEl == fromEl) return;
    var el = fromEl;

    var cDisabled = el.getAttribute("cDisabled");
    var cDisabled = el.cDisabled;
    cDisabled = (cDisabled != null); // If CDISABLED atribute is present

    var cToggle = el.cToggle;
    toggle_disabled = (cToggle != null); // If CTOGGLE atribute is present

    if (cToggle && el.value)
      {
        makePressed(el);
        makeGray(el,true);
      }
    else if ( ( (el.className == "coolButton") || (el.className == "coolButton2") ) && !cDisabled)
      {
        makeFlat(el);
        makeGray(el,true);
      }
  }

function doDown ()
  {
    el = getReal(window.event.srcElement, "className", "coolButton");
    
    var cDisabled = el.cDisabled;
    cDisabled = (cDisabled != null); // If CDISABLED atribute is present
    
    if ( ( (el.className == "coolButton") || (el.className == "coolButton2") ) && !cDisabled)
      makePressed(el)
  }

function doUp ()
  {
    el = getReal(window.event.srcElement, "className", "coolButton");
    
    var cDisabled = el.cDisabled;
    cDisabled = (cDisabled != null); // If CDISABLED atribute is present
    
    if ( ( (el.className == "coolButton") || (el.className == "coolButton2") ) && !cDisabled)
      makeRaised(el);
  }


function getReal (el, type, value)
  {
    temp = el;
    while ((temp != null) && (temp.tagName != "BODY"))
      {
        eval_value = eval("temp." + type);
        if ( ( eval_value == "coolButton" ) || ( eval_value == "coolButton2" ) )
          {
            el = temp;
            return el;
          }
        temp = temp.parentElement;
      }
    return el;
  }

function findChildren (el, type, value)
  {
    var children = el.children;
    var tmp = new Array();
    var j=0;
    
    for (var i=0; i<children.length; i++)
      {
        if (eval("children[i]." + type + "==\"" + value + "\""))
          tmp[tmp.length] = children[i];
        tmp = tmp.concat(findChildren(children[i], type, value));
      }
    
    return tmp;
  }

function disable (el)
  {
    if (document.readyState != "complete")
      {
        window.setTimeout("disable(" + el.id + ")", 100);  // If document not finished rendered try later.
        return;
      }
    
    var cDisabled = el.cDisabled;
    
    cDisabled = (cDisabled != null); // If CDISABLED atribute is present

    if (!cDisabled)
      {
        el.cDisabled = true;
        
        el.innerHTML = '<span style="background: buttonshadow; width: 100%; height: 100%; text-align: center;">' +
                '<span style="filter:Mask(Color=buttonface) DropShadow(Color=buttonhighlight, OffX=1, OffY=1, Positive=0); height: 100%; width: 100%%; text-align: center;">' +
                el.innerHTML +
                '</span>' +
                '</span>';

        if (el.onclick != null)
          {
            el.cDisabled_onclick = el.onclick;
            el.onclick = null;
          }
      }
  }

function enable (el)
  {
    var cDisabled = el.cDisabled;
    
    cDisabled = (cDisabled != null); // If CDISABLED atribute is present
    
    if (cDisabled)
      {
        el.cDisabled = null;
        el.innerHTML = el.children[0].children[0].innerHTML;

        if (el.cDisabled_onclick != null)
          {
            el.onclick = el.cDisabled_onclick;
            el.cDisabled_onclick = null;
          }
      }
  }

function addToggle (el)
  {
    var cDisabled = el.cDisabled;
    
    cDisabled = (cDisabled != null); // If CDISABLED atribute is present
    
    var cToggle = el.cToggle;
    
    cToggle = (cToggle != null); // If CTOGGLE atribute is present

    if (!cToggle && !cDisabled)
      {
        el.cToggle = true;
        
        if (el.value == null)
          el.value = 0;    // Start as not pressed down
        
        if (el.onclick != null)
          el.cToggle_onclick = el.onclick;  // Backup the onclick
        else 
          el.cToggle_onclick = "";

        el.onclick = new Function("toggle(" + el.id +"); " + el.id + ".cToggle_onclick();");
      }
  }

function removeToggle (el)
  {
    var cDisabled = el.cDisabled;
    
    cDisabled = (cDisabled != null); // If CDISABLED atribute is present
    
    var cToggle = el.cToggle;
    
    cToggle = (cToggle != null); // If CTOGGLE atribute is present
    
    if (cToggle && !cDisabled)
      {
        el.cToggle = null;

        if (el.value)
          toggle(el);

        makeFlat(el);
        
        if (el.cToggle_onclick != null)
          {
            el.onclick = el.cToggle_onclick;
            el.cToggle_onclick = null;
          }
      }
  }

function toggle (el)
  {
    el.value = !el.value;
    
    if (el.value)
      el.style.background = "URL(/webfx/images/tileback.gif)";
    else
      el.style.backgroundImage = "";

    // doOut(el);  
  }


function makeFlat (el)
  {
    with (el.style)
      {
        background    = "";
        border        = "";
        padding       = "2px";
      }
  }

function makeRaised (el)
  {
    with (el.style)
      {
        borderLeft    = "1px solid "+coolbutton_crLeft;
        borderRight   = "1px solid "+coolbutton_crRight;
        borderTop     = "1px solid "+coolbutton_crTop;
        borderBottom  = "1px solid "+coolbutton_crBottom;
        padding       = "1px";
      }
  }

function makePressed (el)
  {
    with (el.style)
      {
        borderLeft    = "1px solid "+coolbutton_cpLeft;
        borderRight   = "1px solid "+coolbutton_cpRight;
        borderTop     = "1px solid "+coolbutton_cpTop;
        borderBottom  = "1px solid "+coolbutton_cpBottom;
        paddingTop    = "1px";
        paddingLeft   = "1px";
        paddingBottom = "0px";
        paddingRight  = "0px";
      }
  }

function makeGray (el,b)
  {
    var filtval;
    
    if (b && el.className == "coolButton" )
      filtval = "gray()";
    else
      filtval = "";

    var imgs = findChildren(el, "tagName", "IMG");
      
    for (var i=0; i<imgs.length; i++)
      imgs[i].style.filter = filtval;
  }
  
document.write("<style>");
document.write(".coolBar  {background: buttonface;border-top: 1px solid buttonhighlight;  border-left: 1px solid buttonhighlight;  border-bottom: 1px solid buttonshadow; border-right: 1px solid buttonshadow; padding: 1px; font: menu;}");
document.write(".coolButton, .coolButton2 {padding: 1px; text-align: center; cursor: hand;}");
document.write(".coolButton IMG  {filter: gray();}");
document.write("</style>");
