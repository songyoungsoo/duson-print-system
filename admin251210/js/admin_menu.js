function hideLayer(){
	if (overdiv == "0")
	{
		if(navigator.family =="nn4") {eval(document.object1.top="-500");}
		else if(navigator.family =="ie4"){object1.innerHTML="";}
	}
}

var isNav = (navigator.appName.indexOf("Netscape") !=-1);

function handlerMM(e){
	x = (isNav) ? e.pageX : event.clientX + document.body.scrollLeft;
	y = (isNav) ? e.pageY : event.clientY + document.body.scrollTop;
}

if (isNav){document.captureEvents(Event.MOUSEMOVE);}
document.onmousemove = handlerMM;


function TopMenuVisible(visID)
{
	var obj1 = document.getElementById("TopMenus1_pnlMyDirect");
	var obj2 = document.getElementById("TopMenus1_pnlBill");
	var obj3 = document.getElementById("TopMenus1_pnlService");
	var obj4 = document.getElementById("TopMenus1_pnlAddService");
	var obj5 = document.getElementById("TopMenus1_pnlAS9");
	var obj6 = document.getElementById("TopMenus1_pnlCom9");
	var obj7 = document.getElementById("TopMenus1_pnlTech9");
	
	var objVis = document.getElementById(visID);
	
	obj1.style.display = "none";
	obj2.style.display = "none";
	obj3.style.display = "none";
	obj4.style.display = "none";
	obj5.style.display = "none";
	obj6.style.display = "none";
	obj7.style.display = "none";
	
	objVis.style.display = "";
}

function TopMenuHidden()
{	
	var obj1 = document.getElementById("TopMenus1_pnlMyDirect");
	var obj2 = document.getElementById("TopMenus1_pnlBill");
	var obj3 = document.getElementById("TopMenus1_pnlService");
	var obj4 = document.getElementById("TopMenus1_pnlAddService");
	var obj5 = document.getElementById("TopMenus1_pnlAS9");
	var obj6 = document.getElementById("TopMenus1_pnlCom9");
	var obj7 = document.getElementById("TopMenus1_pnlTech9");
	
	obj1.style.display = "none";
	obj2.style.display = "none";
	obj3.style.display = "none";
	obj4.style.display = "none";
	obj5.style.display = "none";
	obj6.style.display = "none";
	obj7.style.display = "none";
}