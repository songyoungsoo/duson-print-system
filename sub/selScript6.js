	  
	  
//++++++++++++++++++용지를 선택했을때..
function selPaper(){
	f=document.ordform;
	
	//구분상태를 나타냄
	s1=new Array('8p','12P','16P','20P','24P');
	s2=new Array('120g','150g','180g','200g');
	
	paper = f.paper.selectedIndex;

	if (paper=="7")	{
		sObj = s2;
		document.all["sizeA"].style.visibility = "visible";
		document.all["sizeA"].style.position = "";
	}
	else if (paper=="5" || paper=="6"){
		sObj = s2;
		document.all["sizeA"].style.visibility = "hidden";
		document.all["sizeA"].style.position = "absolute";
	}
	else {
		sObj = s1;
		document.all["sizeA"].style.visibility = "hidden";
		document.all["sizeA"].style.position = "absolute";
	}

	f.gubun.options.length = sObj.length + 1; 
    f.gubun.options[0] = new Option("▒ 구분 ▒", "notset", true, false); 
    for (var i = 1; i <= sObj.length; i++) { 
        f.gubun.options[i] = new Option(sObj[i-1]); 
    }


	//가격 비우기
	f.price.value ="0";
	f.total.value="0";
	

// 모든 디자인모드 선택하지 않기
	for (i=0;i<f.design.lenght;i++)
	{
		f.design[i].checked=false;
	}
	
}



function viewCnt(){
	//선택한 용지에 따라 수량을 나타냄
	c1=new Array('1,000부','2,000부');
	c2=new Array('2,000부');
	c3=new Array('1,000장');

	f=document.ordform;
	paper = f.paper.selectedIndex;
	coating = f.coating.selectedIndex;

	if(paper=="7"){
		cObj=c3;
	}
	else if(paper=="6"){
		cObj=c2;
	}
	else{
		cObj=c1;
	}

	f.cnt.options.length = cObj.length+1;
	f.cnt.options[0] =  new Option("▒ 수량 ▒", "notset", true, false); 
	for (var i = 1; i <= cObj.length; i++) { 
        f.cnt.options[i] = new Option(cObj[i-1]); 
    } 
}


//+++++++가격 계산 : 코팅 여부에 따라 가격이 달라짐.
function calc(which){

f=document.ordform;

	//용지
	paper=f.paper.selectedIndex;

	//가격
	pb=f.price.value;
	pb = no_comma(String(pb));

	//합계
	t=f.total.value;
	t = no_comma(String(t));

	//규격
	size=f.size.selectedIndex;

	//코팅
	coating=f.coating.selectedIndex;

	//구분
	gubun=f.gubun.selectedIndex;

	//단가
	//dan=f.dan.value;
	dan=0;


//======= 가격표 : P+용지값+구분값(+규격)

	
	p11=new Array(0,590000,780000);//A4/120g_8P
	p12=new Array(0,900000,1150000);//A4/120g_12P
	p13=new Array(0,1100000,1400000);//A4/120g_16P
	p14=new Array(0,1400000,1880000);//A4/120g_20P
	p15=new Array(0,1560000,2000000);//A4/120g_24P

	p21=new Array(0,620000,840000);//A4/150g_8P
	p22=new Array(0,940000,1240000);//A4/150g_12P
	p23=new Array(0,1140000,1500000);//A4/150g_16P
	p24=new Array(0,1440000,1920000);//A4/150g_20P
	p25=new Array(0,1600000,2200000);//A4/150g_24P

	p31=new Array(0,640000,900000);//A4/180g_8P
	p32=new Array(0,1000000,1300000);//A4/180g_12P
	p33=new Array(0,1200000,1640000);//A4/180g_16P
	p34=new Array(0,1500000,2100000);//A4/180g_20P
	p35=new Array(0,1660000,2340000);//A4/180g_24P

	p41=new Array(0,680000,1070000);//A4/200g_8P
	p42=new Array(0,1060000,1360000);//A4/200g_12P
	p43=new Array(0,1240000,1720000);//A4/200g_16P
	p44=new Array(0,1520000,2240000);//A4/200g_20P
	p45=new Array(0,1720000,2440000);//A4/200g_24P

	p51=new Array(0,390000,500000);//리플렛-A4/4p-2단_120g
	p52=new Array(0,410000,530000);//리플렛-A4/4p-2단_150g
	p53=new Array(0,430000,560000);//리플렛-A4/4p-2단_180g
	p54=new Array(0,450000,580000);//리플렛-A4/4p-2단_200g

	p61=new Array(0,630000);//리플렛-A4/6p-3단_120g
	p62=new Array(0,660000);//리플렛-A4/6p-3단_150g
	p63=new Array(0,690000);//리플렛-A4/6p-3단_180g
	p64=new Array(0,720000);//리플렛-A4/6p-3단_200g

	p710=new Array(0,265000);//포스터_120g_국2절
	p711=new Array(0,295000);//포스터_120g_4*6
	p720=new Array(0,280000);//포스터_150g_국2절
	p721=new Array(0,330000);//포스터_150g_4*6
	p730=new Array(0,295000);//포스터_180g_국2절
	p731=new Array(0,365000);//포스터_180g_4*6
	p740=new Array(0,310000);//포스터_200g_국2절
	p741=new Array(0,400000);//포스터_200g_4*6

	p000=new Array(0,0);//아트모조를 선택하지 않았을때


	//선택한 옵션에 따라 가격표를 위 배열에서 가져온다.
	if (gubun==0){
		obj=p000;
	}
	else if (paper=="7")	{
		obj = eval("p"+paper+gubun+size);
	}
	else{
		obj = eval("p"+paper+gubun);
	}

	idx = f.cnt.selectedIndex;
	dan = obj[idx];		//선택된 가격의 단가
	
	f.price.value = number_format(String(dan));
	num= 1;		//인원수

	if (f.designClick.value != "N")	{
		DC = parseInt(f.designClick.value);
	}
	else {
		DC = 0;
	}

	f.total.value = number_format(String(dan*num+DC));		//총가격 = 단가*인원수

	f.cntchk.value= f.cnt.selectedIndex;		//수량에 무엇이 체크되어 있는지 확인하기 위해.
	f.gubunchk.value = gubun;
}


//+++++++++++ 디자인 의뢰에 대한 추가 비용
function addCalc(pay){
	f = document.ordform;
	DC = f.designClick.value;	//디자인 비용을 한번 선택했는지 여부
	t = no_comma(String(f.total.value));
	
	if (DC=="N")	//디자인 비용이 변경될 경우 이전 값을 빼줘야함.
	{
		tt = parseInt(t) + parseInt(pay);
		f.total.value=number_format(String(tt));
		f.designClick.value=pay;
	}
	else{
		tt = parseInt(t) + parseInt(pay) - parseInt(DC);
		f.total.value=number_format(String(tt));
		f.designClick.value=pay;
	}
}



// 자바스크립트로 PHP의 number_format 흉내를 냄
// 숫자에 , 를 출력
function number_format(data) 
{
	
    var tmp = '';
    var number = '';
    var cutlen = 3;
    var comma = ',';
    var i;
   
    len = data.length;
    mod = (len % cutlen);
    k = cutlen - mod;
    for (i=0; i<data.length; i++) 
	{
        number = number + data.charAt(i);
		
        if (i < data.length - 1) 
		{
            k++;
            if ((k % cutlen) == 0) 
			{
                number = number + comma;
                k = 0;
			}
        }
    }

    return number;
}



// , 를 없앤다.
function no_comma(data)
{
	var tmp = '';
    var comma = ',';
    var i;

	for (i=0; i<data.length; i++)
	{
		if (data.charAt(i) != comma)
		    tmp += data.charAt(i);
	}
	return tmp;
}