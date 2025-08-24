var editor;

function Editor()
{
    this.iframe;
    this.doc;
    this.selection;
    this.range;

    this.colorset = "white";

    this.init = initEditor;
    this.isIE = isBrowserIE;
    this.focus = focusEditor;
    this.setColorset = setColorset;
    this.getEditorSource = getEditorSource;
    this.getFrame = getFrame;
    this.getContent = getContent;
    this.setContent = setContent;
    this.getSelection = getSelection;
    this.createRange = createRange;
    this.setFontColorBoxPosition = setFontColorBoxPosition;
    this.setBGColorBoxPosition = setBGColorBoxPosition;
}

function initEditor(width, height)
{
    this.frame = this.getFrame(width, height);    
    this.doc = this.frame.document;
    this.doc.designMode = "on";

    this.doc.open("text/html");
    this.doc.write(this.getEditorSource());
    this.doc.close();

    this.doc.domain = "naver.com";
    document.domain = "naver.com";

    this.doc.body.style.fontSize = "9pt";
    this.doc.body.style.fontFamily = "돋움";

    this.focus();
}

function isBrowserIE()
{
    var ua = navigator.userAgent.toLowerCase();

    return (ua.indexOf("msie") != -1) && (ua.indexOf("opera") == -1) && (ua.indexOf("webtv") == -1);
}

function focusEditor()
{
    this.frame.focus();
}

function setColorset(colorset)
{
    this.colorset = colorset;
}

function getEditorSource()
{
    var source = "";
    var style = "";
    var body = "";

    if (this.colorset == "silver")
    {
        style = "<style>.Background {background-color:#EFEFEF} P {margin-top:2px;margin-bottom:2px;} table {border:1 solid C6C3C6}</style>";
        body = "<body class=\"Background\"></body>";
    }
    else if (this.colorset == "gray")
    {
        style = "<style>P {margin-top:2px;margin-bottom:2px;} table {border:1 solid C6C3C6}</style>";
        body = "<body bgcolor=#545454 text=#C1C0C1 link=#6F93FF vlink=#6F93FF alink=#6F93FF></body>";
    }
    else if (this.colorset == "black")
    {
        style = "<style>P {margin-top:2px;margin-bottom:2px;} table {border:1 solid C6C3C6}</style>";
        body = "<body bgcolor=#292929 text=#C1C0C1 link=#6F93FF vlink=#6F93FF alink=#6F93FF></body>";
    }
    else
    {
        style = "<style>.Background {background-color:#FFFFFF} P {margin-top:2px;margin-bottom:2px;} table {border:1 solid C6C3C6}</style>";
        body = "<body class=\"Background\"></body>";
    }

    source = "<html><head>" + style + "<script>function resizeImage(num){};function popview(){};</script></head>" + body + "</html>";

    return source;
}

function setFontColorBoxPosition(top, left)
{
    document.getElementById("TableFontColor").style.top = top;
    document.getElementById("TableFontColor").style.left = left;
}

function setBGColorBoxPosition(top, left)
{
    document.getElementById("TableBGColor").style.top = top;
    document.getElementById("TableBGColor").style.left = left;
}

function getFrame(width, height)
{
    if (this.isIE())
    {
        document.getElementById("content").style.display = "none";

        this.frame = EDITOR;
    }
    else
    {
        var textarea = document.getElementById("content");
        var htmlarea = document.createElement("div");
        textarea.parentNode.insertBefore(htmlarea, textarea);
        textarea.style.display = "none";
        document.getElementById("EDITOR").style.display = "none";

        iframe = document.createElement("iframe");
        htmlarea.appendChild(iframe);
        
        iframe.style.width = width + "px";
        iframe.style.height = height + "px";
        iframe.className = document.getElementById('EDITOR').className;
        
        this.frame = iframe.contentWindow;
    }

    return this.frame;
}


function getContent()
{
    if (this.isIE())
    {
        this.doc.body.createTextRange().execCommand("copy");
    }

    return this.doc.body.innerHTML;
}

function setContent(html)
{
    this.doc.body.innerHTML = html;
}

function getSelection()
{
    if (this.isIE()) 
        return this.doc.selection;
    else
        return this.frame.getSelection();
}

function createRange(sel) 
{
    if (this.isIE()) 
    {
        return sel.createRange();
    } 
    else 
    {
        this.focus();
        if (typeof sel != "undefined") 
        {
            try {
                return sel.getRangeAt(0);
            } catch(e) {
                return this.doc.createRange();
            }
        } 
        else 
        {
            return this.doc.createRange();
        }
    }
}

// reply, modify content setting
function decodeContent(str)
{
    return str.replace(/&lt;/gi,"<").replace(/&gt;/gi,">").replace(/&amp;/gi,"&");
}

// edit wyswyg html 
function editContent(act)
{
    var selection = editor.getSelection();
    var range = editor.createRange(selection);
    editor.range = range;

    switch (act)
    {
        case "Bold" :
        case "Italic" :
        case "Underline" :
        case "StrikeThrough" :
        case "JustifyLeft" :
        case "JustifyCenter" :
        case "JustifyRight" :
            editor.doc.execCommand(act, false, null);
            break;
        case "fontcolor" :
        case "bgcolor" :
            showFontColorBox(act);
            break;
        case "link" :
            showLinkBox();
    }
}

function execSelect(act, el)
{
    if (el.options[el.selectedIndex].value != '')
        editor.doc.execCommand(act, null, el.options[el.selectedIndex].value);

    el.selectedIndex = 0;
}

function setFont(oSelect)
{
    execSelect("FontName", oSelect);
}

function setFontSize(oSelect)
{
    execSelect("FontSize", oSelect);
}

function setFontColor(color)
{
    if (editor.range != null && editor.isIE())
    {
        editor.range.select();
    }
    editor.doc.execCommand("ForeColor", false, color);

    displayFontColorBox(false);
}

function setBGColor(color, ftcolor)
{
    if (editor.range != null && editor.isIE())
    {
            editor.range.select();
    }
    editor.doc.execCommand("BackColor", null, color);
    if (ftcolor != "") editor.doc.execCommand("ForeColor", null, ftcolor);

    displayBGColorBox(false);
}

function showLinkBox()
{
    editor.focus();
    editor.doc.execCommand("CreateLink", true, null);
}

function showFontColorBox(act)
{
    if (act == "fontcolor")
    {
        displayFontColorBox(true);
        displayBGColorBox(false);
    }
    if (act == "bgcolor")
    {
        displayFontColorBox(false);
        displayBGColorBox(true);
    }
}

function displayFontColorBox(flag)
{
    if (flag)
        document.getElementById("TableFontColor").style.display = "";
    else
        document.getElementById("TableFontColor").style.display = "none";
}

function displayBGColorBox(flag)
{
    if (flag)
        document.getElementById("TableBGColor").style.display = "";
    else
        document.getElementById("TableBGColor").style.display = "none";
}

var sepFile = "||";
var setField = "|";

function popFile(cluburl)
{
    open_wnd("FileUp.php?cluburl=" + cluburl, "addfile", 330, 200);
}

function addLink(attachURL, comment, alignPos, chk)
{
    try
    {
        var attachTag = getAttachTag(attachURL, comment, alignPos, chk);

        if (alignPos=='top')
        {
            editor.doc.body.innerHTML =  attachTag+ "<br>" + editor.doc.body.innerHTML;
        }
        else if (alignPos=='bottom')
        {
            editor.doc.body.innerHTML =  editor.doc.body.innerHTML + "<br>" + attachTag ;
        }
        else
        {
            editor.doc.body.innerHTML = "<P>" + attachTag + editor.doc.body.innerHTML + "</P>";
        }
    }
    catch(e)
    {
        editor.doc.body.innerHTML = "<P>" + attachTag + editor.doc.body.innerHTML + "</P>";
    }

    editor.focus();
}

function getAttachTag(attachURL, comment, alignPos, chk)
{
    var attachTag = "";

    if (chk == "1")
    {
         attachURL = "http://cafefiles.naver.net" + attachURL;
    }

    attachURL = attachURL.replace(/'/g, "\'");

    if  (attachURL.match(/.jpg|.jpeg|.gif|.png$/i))
    {
        var imgid = parseInt((Math.random()*10000000));

        if (alignPos == 'top' || alignPos == 'bottom')
            attachTag = "<center><img src='" + attachURL + "' align='" + alignPos + "' id='userImg" + imgid + "' onload='setTimeout(\"resizeImage(" + imgid + ")\",200)' style='cursor:hand' onclick='popview(\"" + attachURL + "\")'>" + comment + "</center>" ;
        else
            attachTag = "<img src='" + attachURL + "' align='" + alignPos + "' id='userImg" + imgid + "' onload='setTimeout(\"resizeImage(" + imgid + ")\",200)' style='cursor:hand' onclick='popview(\"" + attachURL + "\")'>" + comment ;
    }
    else
    {
        attachTag = "<embed autostart='false' src='" + attachURL + "' >" + comment;
    }

    return attachTag;
}


////////////////////////////////////////////////
function addList(dirname, filename, filesize)
{
    // 폴 첨부
    if (filename == 'poll@nhn')
    {
        document.getElementById("attachfilelist").options.add(new Option('폴이 첨부되었습니다' , filename));
    }
    // 일반 파일 첨부
    else
    {
        if (filesize)
        {
            calcFileSize(filesize, 1);
            document.getElementById("attachfilelist").options.add(new Option(filename + "   " + parseInt(parseInt(filesize)/1024) + "KB", dirname +"/" + filename));
        }
        else
        {
            document.getElementById("attachfilelist").options.add(new Option(filename, dirname +"/" + filename));
        }
    }
}

function calcFileSize(filesize, oper)
{
    var objRealSum = document.getElementById("attachsizerealsum");

    if (oper == 1)
    {
        objRealSum.value = parseInt(objRealSum.value) + parseInt(filesize);
    }
    else
    {
        objRealSum.value = parseInt(objRealSum.value) - parseInt(filesize);
    }
    
    document.getElementById("attachsizesum").value = parseInt(objRealSum.value/1024);    
}

function addPoll(attachpolls)
{
    document.getElementById("attachpollyn").value = "Y";
    document.getElementById("attachpolls").value = attachpolls;
    document.getElementById("imgPoll").src = "http://cafeimgs.naver.com/img/colorset/white/btn_poll_edit.gif";
}

function removePoll()
{
    document.getElementById("attachpollyn").value = "";
    document.getElementById("attachpolls").value = "";
    document.getElementById("imgPoll").src = "http://cafeimgs.naver.com/img/btn_poll.gif";
}


//////////////////////
function addFile(dirname, filename, filesize, filetype)
{
    if (filetype == "F") document.getElementById("attachfileyn").value = "Y";
    if (filetype == "I") document.getElementById("attachimageyn").value = "Y";
    document.getElementById("attachfiles").value = document.getElementById("attachfiles").value + dirname +"/" + filename + "|" + filetype + "||";
    document.getElementById("attachsizes").value = document.getElementById("attachsizes").value + filesize + "||";
}
//////////////////////



function removeFile(index)
{
    arrAttachfile = document.getElementById("attachfiles").value.split("||");
    arrAttachsize = document.getElementById("attachsizes").value.split("||");

    calcFileSize(arrAttachsize[index], -1);    
    
    arrAttachfile.remove(index);
    arrAttachsize.remove(index);
    document.getElementById("attachfiles").value = arrAttachfile.join("||");
    document.getElementById("attachsizes").value = arrAttachsize.join("||");
    
    
    document.getElementById("attachfileyn").value = "";
    document.getElementById("attachimageyn").value = "";
    for (var i = 0; i<arrAttachfile.length; i++)
    {
        if (arrAttachfile[i].match(/F$/))
        {
            document.getElementById("attachfileyn").value = "Y";
        }
        if (arrAttachfile[i].match(/I$/))
        {
            document.getElementById("attachfileyn").value = "Y";
        }
    }
}

function removeAttach()
{
    var attachlist = document.getElementById("attachfilelist");
    
    if (attachlist.selectedIndex <= 0)
        return;

    if (attachlist.options[attachlist.selectedIndex].value == "poll@nhn")
        removePoll();
    else
        removeFile(attachlist.selectedIndex-1);
    
    attachlist.remove(attachlist.selectedIndex);    
}

document.write("" 
  + "<div id='TableFontColor' style='position:absolute;top:50;left:240;display:none;'>"
  + "<table width='200' border='0' cellspacing='1' cellpadding='0' bgcolor='#CCCCCC'>"
  + "<tr>" 
  + "<td align='center' bgcolor='#FFFFFF' width='85'>" 
  + "<table width='111' border='0' cellspacing='4' cellpadding='0' height='88' style='cursor:hand'>"
  + "<tr>" 
  + "<td bgcolor='#008000' onclick='setFontColor(\"#008000\");'>&nbsp;</td>"
  + "<td bgcolor='#009966' onclick='setFontColor(\"#009966\");'>&nbsp;</td>"
  + "<td bgcolor='#99CC66' onclick='setFontColor(\"#99CC66\");'>&nbsp;</td>"
  + "<td bgcolor='#999966' onclick='setFontColor(\"#999966\");'>&nbsp;</td>"
  + "<td bgcolor='#CC9900' onclick='setFontColor(\"#CC9900\");'>&nbsp;</td>"
  + "</tr>"
  + "<tr>" 
  + "<td bgcolor='#D41A01' onclick='setFontColor(\"#D41A01\");'>&nbsp;</td>"
  + "<td bgcolor='#FF0000' onclick='setFontColor(\"#FF0000\");'>&nbsp;</td>"
  + "<td bgcolor='#FF7635' onclick='setFontColor(\"#FF7635\");'>&nbsp;</td>"
  + "<td bgcolor='#FF9900' onclick='setFontColor(\"#FF9900\");'>&nbsp;</td>"
  + "<td bgcolor='#FF3399' onclick='setFontColor(\"#FF3399\");'>&nbsp;</td>"
  + "</tr>"
  + "<tr>" 
  + "<td bgcolor='#9B18C1' onclick='setFontColor(\"#9B18C1\");'>&nbsp;</td>"
  + "<td bgcolor='#993366' onclick='setFontColor(\"#993366\");'>&nbsp;</td>"
  + "<td bgcolor='#666699' onclick='setFontColor(\"#666699\");'>&nbsp;</td>"
  + "<td bgcolor='#0000FF' onclick='setFontColor(\"#0000FF\");'>&nbsp;</td>"
  + "<td bgcolor='#177FCD' onclick='setFontColor(\"#177FCD\");'>&nbsp;</td>"
  + "</tr>"
  + "<tr>" 
  + "<td bgcolor='#006699' onclick='setFontColor(\"#006699\");'>&nbsp;</td>"
  + "<td bgcolor='#003366' onclick='setFontColor(\"#003366\");'>&nbsp;</td>"
  + "<td bgcolor='#333333' onclick='setFontColor(\"#333333\");'>&nbsp;</td>"
  + "<td bgcolor='#8E8E8E' onclick='setFontColor(\"#8E8E8E\");'>&nbsp;</td>"
  + "<td bgcolor='#C1C1C1' onclick='setFontColor(\"#C1C1C1\");'>&nbsp;</td>"
  + "</tr>"
  + "</table>"
  + "</td>"
  + "<td align='center' bgcolor='#FFFFFF' width='162' valign='top'>"
  + "<br>"
  + "직접입력"
  + "<font face='Verdana' size='1'>" 
  + "<input type='text' name='FontBox' id='FontBox' style='width:60px' maxlength='7' class='box' value=''>"
  + "</font>"
  + "<br>"
  + "<table width='5' border='0' cellspacing='0' cellpadding='0' height='10'>"
  + "<tr>" 
  + "<td>&nbsp;</td>"
  + "</tr>"
  + "</table>"
  + "<a href='javascript:setFontColor(document.getElementById(\"FontBox\").value);'><img src='http://cafeimgs.naver.com/img/btn_ok01.gif' width='32' height='19' align='absmiddle'></a>"
  + "<br>"
  + "</td>"
  + "</tr>"
  + "</table>"
  + "</div>");

document.write(""
  + "<div id='TableBGColor' style='position:absolute;top:0;left:260;display:none;'>"
  + "<table width='200' border='0' cellspacing='1' cellpadding='0' bgcolor='#CCCCCC'>"
  + "  <tr>" 
  + "    <td align='center' bgcolor='#FFFFFF' width='85'>"
  + "<table width='111' border='0' cellspacing='5' cellpadding='0' height='88' style='cursor:hand'>"
  + "<tr valign='top'>"
  + "<td bgcolor='#FFDAED' width='53' onclick='setBGColor(\"#FFDAED\",\"\");'>바탕색</td>"
  + "<td bgcolor='#FF0000' width='53' onclick='setBGColor(\"#FF0000\",\"#FFFFFF\");'><font color='#FFFFFF'>바탕색</font></td>"
  + "</tr>"
  + "<tr valign='top'>"
  + "<td bgcolor='#99DCFF' width='53' onclick='setBGColor(\"#99DCFF\",\"\");'>바탕색</td>"
  + "<td bgcolor='#0000FF' width='53' onclick='setBGColor(\"#0000FF\",\"#FFFFFF\");'><font color='#FFFFFF'>바탕색</font></td>"
  + "</tr>"
  + "<tr valign='top'>"
  + "<td bgcolor='#A6FF4D' width='53' onclick='setBGColor(\"#A6FF4D\",\"\");'>바탕색</td>"
  + "<td bgcolor='#009966' width='53' onclick='setBGColor(\"#009966\",\"#FFFFFF\");'><font color='#FFFFFF'>바탕색</font></td>"
  + "</tr>"
  + "<tr valign='top'>"
  + "<td bgcolor='#E4FF75' width='53' onclick='setBGColor(\"#E4FF75\",\"\");'>바탕색</td>"
  + "<td bgcolor='#E4E4E4' width='53' onclick='setBGColor(\"#E4E4E4\",\"\");'>바탕색</td>"
  + "</tr>"
  + "<tr valign='top'>"
  + "<td bgcolor='#333333' width='53' onclick='setBGColor(\"#333333\",\"#FFFFFF\");'><font color='#FFFFFF'>바탕색</font></td>"
  + "<td bgcolor='#333333' width='53' onclick='setBGColor(\"#333333\",\"#FFFF00\");'><font color='#FFFF00'>바탕색</font></td>"
  + "</tr>"
  + "<tr valign='top'>"
  + "<td bgcolor='#8E8E8E' width='53' onclick='setBGColor(\"#8E8E8E\",\"#FFFFFF\");'><font color='#FFFFFF'>바탕색</font></td>"
  + "<td bgcolor='#CDCDCD' width='53' onclick='setBGColor(\"#CDCDCD\",\"#FFFFFF\");'><font color='#FFFFFF'>바탕색</font></td>"
  + "</tr>"
  + "<tr valign='top'>"
  + "<td width='53' onclick='setBGColor(\"\",\"\");'>바탕색</td>"
  + "<td width='53'>&nbsp;</td>"
  + "</tr>"
  + "</table>"
  + "</td>"
  + "<td align='center' bgcolor='#FFFFFF' width='162'>"
  + "<br>"
  + "직접입력"
  + "<br>"
  + "<input type='text' name='FontBox2' id='FontBox2' style='width:60px' maxlength='7' class='box' value='' align='absmiddle'>"
  + "<br>"
  + "<table width='5' border='0' cellspacing='0' cellpadding='0' height='10'>"
  + "<tr>"
  + "<td>&nbsp;</td>"
  + "</tr>"
  + "</table>"
  + "<a href='javascript:setBGColor(document.getElementById(\"FontBox2\").value,\"\");'><img src='http://cafeimgs.naver.com/img/btn_ok01.gif' width='32' height='19' align='absmiddle'></a>"
  + "<br>"
  + "<br>"
  + "</td>"
  + "</tr>"
  + "</table>"
  + "</div>");
