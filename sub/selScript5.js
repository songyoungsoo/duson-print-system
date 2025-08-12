
// ++++++++ 용지별 규격 내용과 모조/레자크지 내용 : 용지를 선택했을때
function selPaper(f) { 
	f = document.ordform;

	//용지별 모조/레쟈크지 값
	c1 = new Array('100모조','레쟈크지','120모조');
	c2 = new Array('100모조','레쟈크지','120모조');
	c3 = new Array('레쟈크지','120모조');
	c4 = new Array('레쟈크지');
	c5 = new Array('100모조','레쟈크지','120모조','크라프트지(황봉투)');
	c6 = new Array('레쟈크지');

	var sn = f.paper.selectedIndex;
		
	cObj = eval("c"+sn);
    f.coating.options.length = cObj.length + 1; 
    f.coating.options[0] = new Option("▒ 모조/레쟈크지 ▒", "notset", true, false); 
    for (var i = 1; i <= cObj.length; i++) { 
        f.coating.options[i] = new Option(cObj[i-1]); 
    } 


	//수량셀렉트도 초기화한다.
	f.cnt.options[0].selected=true;

	//가격 비우기
	f.price.value ="0";
	f.total.value="0";

	// 모든 디자인모드 선택하지 않기
	f.design.checked=false;
}


	  
//++++++++++++++++++ 모조/레쟈크 선택여부에 따라 칼라 옵션 포함

function viewSize(){
	f=document.ordform;
	p = f.paper.selectedIndex;
	c = f.coating.selectedIndex;
	
	if((p=="1" || p=="5") && (c=="2"||c=="3") ){		
		f.size.options[1] = new Option("칼라"); 
		f.size.options[1].value = new Option("2");
	}

	f.coatingchk.value=f.coating.selectedIndex;	//모조/레쟈크에서 선택된 인덱스값을 기억한다.
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
	size=f.size.selectedIndex;

	//코팅
	coating=f.coating.selectedIndex;

	//단가
	//dan=f.dan.value;
	dan=0;


//======= 수량에 따른 가격표 : P+모조/레자크+규격값
	p110 = new Array(0,40000,80000,120000,160000,200000,240000,280000,320000,360000,400000);//A4소봉투_100모조_1도
	p120 = new Array(0,70000,130000,170000,230000,280000,335000,380000,425000,465000,578000);//A4소봉투_레쟈크지_1도
	p121 = new Array(0,110000,200000,290000,340000,390000,440000,490000,540000,590000,640000);//A4소봉투_레쟈크지_칼라
	p130 = new Array(0,45000,90000,135000,180000,225000,270000,315000,360000,405000,450000);//A4소봉투_120모조_1도
	p131 = new Array(0,110000,200000,290000,340000,390000,440000,490000,540000,590000,640000);//A4소봉투_120모조_칼라

	p210 = new Array(0,40000,80000,120000,160000,200000,240000,280000,320000,360000,400000);//16절소봉투_100모조_1도
	p220 = new Array(0,60000,120000,165000,210000,255000,300000,345000,390000,435000,480000);//16절소봉투_레쟈크지_1도
	p230 = new Array(0,45000,90000,135000,180000,225000,270000,315000,360000,405000,450000);//16절소봉투_120모조_1도

	p310 = new Array(0,60000,120000,165000,210000,255000,300000,345000,390000,435000,480000);//티켓봉투_레쟈크지_1도

	p410 = new Array(0,70000,140000,200000,260000,320000,375000,430000,485000,540000,595000);//수강료봉투_레쟈크지_1도

	p510 = new Array(0,90000,178000,218000,294000,368000,442000,516000,558000,588000,664000);//대봉투_100모조_1도
	p520 = new Array(0,110000,200000,280000,360000,480000,560000,640000,720000,800000,880000);//대봉투_레쟈크지_1도
	p521 = new Array(0,190000,190000,420000,510000,630000,720000,810000,910000,1050000,1100000);//대봉투_레쟈크지_칼라
	p530 = new Array(0,110000,200000,280000,360000,480000,560000,640000,720000,800000,880000);//대봉투_120모조_1도
	p531 = new Array(0,190000,190000,420000,510000,630000,720000,810000,910000,910000,1100000);//대봉투_120모조-칼라
	p540 = new Array(0,70000,140000,210000,280000,350000,410000,470000,530000,600000,650000);//대봉투_크라프트지_1도

	p610 = new Array(0,140000,270000,360000,450000,540000,630000,720000,810000,900000,990000);//특대봉투_레쟈크지_1도


	//선택한 옵션에 따라 가격표를 위 배열에서 가져온다.
		obj = eval("p"+paper+coating+size);

	idx = f.cnt.selectedIndex;
	dan = obj[idx];		//선택된 가격의 단가
	
	f.price.value = number_format(String(dan));
	num= 1;	//인원수

	if (f.design.checked==true)	{
		DC = parseInt(f.design.value);
	}
	else {
		DC = 0;
	}

	f.total.value = number_format(String(dan*num+DC));		//총가격 = 단가*인원수

	f.cntchk.value= f.cnt.selectedIndex;		//수량에서 선택된 인덱스값을 기억한다.
	f.sizechk.value=size;					//규격값에서 칼라선택 인덱스값을 기억한다.;
	
}



//+++++++++++ 디자인 의뢰에 대한 추가 비용
function addCalc(pay){
	f = document.ordform;
	t = no_comma(String(f.total.value));

	tt = parseInt(t) + parseInt(pay);
	f.total.value=number_format(String(tt));

	if (f.design.checked)	//디자인 비용이 변경될 경우 이전 값을 빼줘야함.
	{
		tt = parseInt(t) + parseInt(pay);
		f.total.value=number_format(String(tt));
	}
	else{
		tt = parseInt(t) - parseInt(pay);
		f.total.value=number_format(String(tt));
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