	  
	  
//++++++++++++++++++������ ����������..
function selPaper(){
	f=document.ordform;
	
	//���л��¸� ��Ÿ��
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
    f.gubun.options[0] = new Option("�� ���� ��", "notset", true, false); 
    for (var i = 1; i <= sObj.length; i++) { 
        f.gubun.options[i] = new Option(sObj[i-1]); 
    }


	//���� ����
	f.price.value ="0";
	f.total.value="0";
	

// ��� �����θ�� �������� �ʱ�
	for (i=0;i<f.design.lenght;i++)
	{
		f.design[i].checked=false;
	}
	
}



function viewCnt(){
	//������ ������ ���� ������ ��Ÿ��
	c1=new Array('1,000��','2,000��');
	c2=new Array('2,000��');
	c3=new Array('1,000��');

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
	f.cnt.options[0] =  new Option("�� ���� ��", "notset", true, false); 
	for (var i = 1; i <= cObj.length; i++) { 
        f.cnt.options[i] = new Option(cObj[i-1]); 
    } 
}


//+++++++���� ��� : ���� ���ο� ���� ������ �޶���.
function calc(which){

f=document.ordform;

	//����
	paper=f.paper.selectedIndex;

	//����
	pb=f.price.value;
	pb = no_comma(String(pb));

	//�հ�
	t=f.total.value;
	t = no_comma(String(t));

	//�԰�
	size=f.size.selectedIndex;

	//����
	coating=f.coating.selectedIndex;

	//����
	gubun=f.gubun.selectedIndex;

	//�ܰ�
	//dan=f.dan.value;
	dan=0;


//======= ����ǥ : P+������+���а�(+�԰�)

	
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

	p51=new Array(0,390000,500000);//���÷�-A4/4p-2��_120g
	p52=new Array(0,410000,530000);//���÷�-A4/4p-2��_150g
	p53=new Array(0,430000,560000);//���÷�-A4/4p-2��_180g
	p54=new Array(0,450000,580000);//���÷�-A4/4p-2��_200g

	p61=new Array(0,630000);//���÷�-A4/6p-3��_120g
	p62=new Array(0,660000);//���÷�-A4/6p-3��_150g
	p63=new Array(0,690000);//���÷�-A4/6p-3��_180g
	p64=new Array(0,720000);//���÷�-A4/6p-3��_200g

	p710=new Array(0,265000);//������_120g_��2��
	p711=new Array(0,295000);//������_120g_4*6
	p720=new Array(0,280000);//������_150g_��2��
	p721=new Array(0,330000);//������_150g_4*6
	p730=new Array(0,295000);//������_180g_��2��
	p731=new Array(0,365000);//������_180g_4*6
	p740=new Array(0,310000);//������_200g_��2��
	p741=new Array(0,400000);//������_200g_4*6

	p000=new Array(0,0);//��Ʈ������ �������� �ʾ�����


	//������ �ɼǿ� ���� ����ǥ�� �� �迭���� �����´�.
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
	dan = obj[idx];		//���õ� ������ �ܰ�
	
	f.price.value = number_format(String(dan));
	num= 1;		//�ο���

	if (f.designClick.value != "N")	{
		DC = parseInt(f.designClick.value);
	}
	else {
		DC = 0;
	}

	f.total.value = number_format(String(dan*num+DC));		//�Ѱ��� = �ܰ�*�ο���

	f.cntchk.value= f.cnt.selectedIndex;		//������ ������ üũ�Ǿ� �ִ��� Ȯ���ϱ� ����.
	f.gubunchk.value = gubun;
}


//+++++++++++ ������ �Ƿڿ� ���� �߰� ���
function addCalc(pay){
	f = document.ordform;
	DC = f.designClick.value;	//������ ����� �ѹ� �����ߴ��� ����
	t = no_comma(String(f.total.value));
	
	if (DC=="N")	//������ ����� ����� ��� ���� ���� �������.
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



// �ڹٽ�ũ��Ʈ�� PHP�� number_format �䳻�� ��
// ���ڿ� , �� ���
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



// , �� ���ش�.
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