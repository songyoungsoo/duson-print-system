	  
	  
//++++++++++++++++++용지를 선택했을때..
function selPaper(){
f=document.ordform;
	
	//코팅지 메뉴를 제외한 모든 메뉴에서 코팅 부분을 숨김
	if(f.paper.value == "1" || f.paper.value==""){
		document.all["coatingA"].style.visibility = "visible"; 
		document.all["coatingA"].style.position = "";
	}else{
		document.all["coatingA"].style.visibility = "hidden";
		document.all["coatingA"].style.position = "absolute";
	}

	//선택한 용지에 따라 수량을 나타냄
	nc = f.paper.value;  //용지의 Value

	for (i=0;i <= 5 ;i++)
	{
		num="cntA"+i;
		if(nc == i){
			document.all[num].style.visibility = "visible";
			document.all[num].style.position = "";
			eval("f.cnt"+i).options[0].selected=true;
		}else{
			document.all[num].style.visibility = "hidden";
			document.all[num].style.position = "absolute";
		}
	}
	
	//가격 비우기
	f.price.value ="0";
	f.total.value="0";
//	f.op.value="0";

// 모든 디자인모드 선택하지 않기
	for (i=0;i<=2;i++)
	{
		f.design[i].checked=false;
	}
	
}


//+++++++가격 계산 : 코팅 여부에 따라 가격이 달라짐.
function calc(which){

f=document.ordform;

//용지
paper=f.paper.value;

//가격
pb=f.price.value;
pb = no_comma(String(pb));

//합계
t=f.total.value;
t = no_comma(String(t));

//규격
size=f.size.value;

//코팅
coating=f.coating.value;

//단가
//dan=f.dan.value;
dan=0;


//======= 가격표 : P+용지값+규격값+코팅
	p111=new Array(0,7000,12000,24000,32000,42000,50000,70000,120000,200000,350000);  //무코팅/코팅용지_단면_무코팅 가격
	p112=new Array(0,10000,18000,34000,44000,50000,55000,75000,140000,340000,500000);	//무코팅/코팅용지_단면_코팅 가격
	p121=new Array(0,10000,20000,34000,44000,50000,55000,75000,140000,340000,500000);	//무코팅/코팅용지_양면_무코팅 가격
	p122=new Array(0,15000,25000,42000,54000,60000,65000,85000,150000,380000,600000);	//무코팅/코팅용지_양면_코팅 가격
	p21	=new Array(0,15000,28000,42000,55000,65000,250000,450000,1150000);	//휘라레/반누브용지_단면 가격
	p22	=new Array(0,18000,35000,52000,68000,80000,290000,550000,1300000);	//휘라레/반누브용지_양면 가격
	p31	=new Array(0,13000,24000,32000,44000,52000,100000,250000,450000,1150000);	//그레이스_단면 가격
	p32	=new Array(0,16000,32000,42000,54000,64000,120000,280000,550000,1300000);	//그레이스_양면_가격
	p41	=new Array(0,16000,32000,44000,54000,60000,102000,255000,460000,1180000)	//빌리지/스타지_단면 가격
	p42	=new Array(0,20000,40000,54000,64000,70000,122000,290000,570000,1330000)	//빌리지/스타지_양면 가격
	p51	=new Array(0,60000,112000,150000,180000,210000,850000)	//플래티넘_단면 가격
	p52	=new Array(0,70000,128000,180000,220000,260000,950000)	//플래티넘_양면 가격

	//선택한 옵션에 따라 가격표를 위 배열에서 가져온다.
	if (paper=="1")	{	
		obj = eval("p1"+size+coating);
	} 
	else {
		obj = eval("p"+paper+size);
	}

	idx = eval("f.cnt"+paper).selectedIndex;
	dan = obj[idx];		//선택된 가격의 단가
	
	f.price.value = number_format(String(dan));
	num= parseInt(f.num.value);		//인원수

	if (f.designClick.value != "N")	{
		DC = parseInt(f.designClick.value);
	}
	else {
		DC = 0;
	}

	f.total.value = number_format(String(dan*num+DC));		//총가격 = 단가*인원수

	f.cntchk.value= eval("f.cnt"+paper).selectedIndex;		//수량에 무엇이 체크되어 있는지 확인하기 위해.
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