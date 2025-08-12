

//++++++++++++++++++ num select : 용지를 선택했을때..
function selPaper(){

c1 = new Array('50권','100권','200권','300권','500권');
c2 = new Array('10권','20권','30권','40권','60권','80권');
c3 = new Array('10권','20권','30권','40권','50권');

	f=document.ordform;
	paper = f.paper.selectedIndex;
	size = f.size.selectedIndex;
	
	if(paper=="14" || paper=="15"){
		cObj=c3;
	}
	else if(paper=="4" || paper=="5" || paper=="9" || paper=="10"){		
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

//	f.sizechk.value=f.size.selectedIndex;	//규격에서 선택된 인덱스값을 기억한다.

	//수량셀렉트도 초기화한다.
	f.cnt.options[0].selected=true;

	//가격 비우기
	f.price.value ="0";
	f.total.value="0";

	// 모든 디자인모드 선택하지 않기
	f.design.checked=false;
  
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

	//단가
	//dan=f.dan.value;
	dan=0;


//======= 수량에 따른 가격표 : P+용지값+규격값
	p10=new Array(0,34000,66000,100000,154000,200000);//서식지_48절_1도
	p11=new Array(0,50000,82000,110000,183000,231000);//서식지_48절_2도
	p20=new Array(0,56000,80000,133000,198000,289000);//서식지_32절_1도
	p21=new Array(0,72000,96000,163000,226000,316000);//서식지_32절_2도
	p30=new Array(0,72000,108000,190000,277000,400000);//서식지_A5_1도
	p31=new Array(0,90000,132000,235000,334000,489000);//서식지_A5_2도
	p40=new Array(0,28000,50000,66000,72000,102000,128000);//서식지_A4_1도
	p41=new Array(0,42000,66000,81000,86000,129000,153000);//서식지_A4_2도
	p50=new Array(0,24000,46000,66000,72000,82000,98000);//서식지_16절_1도
	p51=new Array(0,40000,62000,81000,86000,95000,110000);//서식지_16절_2도

	p60=new Array(0,64000,106000,171000,257000,340000);//NCR(상하지)_48절_1도
	p61=new Array(0,80000,122000,200000,286000,367000);//NCR(상하지)_48절_2도
	p70=new Array(0,74000,130000,210000,315000,476000);//NCR(상하지)_32절_1도
	p71=new Array(0,90000,146000,239000,343000,510000);//NCR(상하지)_32절_2도
	p80=new Array(0,100000,176000,283000,420000,600000);//NCR(상하지)_A5_1도
	p81=new Array(0,116000,208000,328000,460000,690000);//NCR(상하지)_A5_2도
	p90=new Array(0,50000,90000,114000,126000,150000,224000);//NCR(상하지)_A4_1도
	p91=new Array(0,66000,106000,129000,140000,177000,250000);//NCR(상하지)_A4_2도
	p100=new Array(0,44000,80000,114000,135000,147000,152000);//NCR(상하지)_16절_1도
	p101=new Array(0,60000,96000,129000,153000,160000,164000);//NCR(상하지)_16절_2도

	p110=new Array(0,84000,144000,256000,372000,510000);//NCR(상중하지)_48절_1도
	p111=new Array(0,100000,160000,285000,414000,550000);//NCR(상중하지)_48절_2도
	p120=new Array(0,110000,200000,370000,530000,714000);//NCR(상중하지)_32절_1도
	p121=new Array(0,126000,216000,400000,588000,748000);//NCR(상중하지)_32절_2도
	p130=new Array(0,148000,286000,501000,732000,1030000);//NCR(상중하지)_A5_1도
	p131=new Array(0,164000,308000,558000,804000,1167000);//NCR(상중하지)_A5_2도
	p140=new Array(0,70000,130000,190000,216000,255000);//NCR(상중하지)_A4_1도
	p141=new Array(0,86000,146000,220000,244000,282000);//NCR(상중하지)_A4_2도
	p150=new Array(0,60000,100000,171000,198000,230000);//NCR(상중하지)_16절_1도
	p151=new Array(0,76000,116000,186000,212000,256000);//NCR(상중하지)_16절_2도



	//선택한 옵션에 따라 가격표를 위 배열에서 가져온다.
		obj = eval("p"+paper+size);

	idx = f.cnt.selectedIndex;
	dan = obj[idx];		//선택된 가격의 단가
	
	f.price.value = number_format(String(dan));
	num= 1;		//인원수없음

	if (f.design.checked==true)	{
		DC = parseInt(f.design.value);
	}
	else {
		DC = 0;
	}

	f.total.value = number_format(String(dan*num+DC));		//총가격 = 단가*인원수

	f.cntchk.value= f.cnt.selectedIndex;		//수량에서 선택된 인덱스값을 기억한다.
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