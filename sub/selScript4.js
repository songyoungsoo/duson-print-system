

//++++++++++++++++++ num select : ������ ����������..
function selPaper(){

c1 = new Array('50��','100��','200��','300��','500��');
c2 = new Array('10��','20��','30��','40��','60��','80��');
c3 = new Array('10��','20��','30��','40��','50��');

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
	f.cnt.options[0] =  new Option("�� ���� ��", "notset", true, false); 
	for (var i = 1; i <= cObj.length; i++) { 
        f.cnt.options[i] = new Option(cObj[i-1]); 
    } 

//	f.sizechk.value=f.size.selectedIndex;	//�԰ݿ��� ���õ� �ε������� ����Ѵ�.

	//��������Ʈ�� �ʱ�ȭ�Ѵ�.
	f.cnt.options[0].selected=true;

	//���� ����
	f.price.value ="0";
	f.total.value="0";

	// ��� �����θ�� �������� �ʱ�
	f.design.checked=false;
  
}



//+++++++���� ��� : ���� ���ο� ���� ������ �޶���.
function calc(which){

	f=document.ordform;

	//����
	paper=f.paper.value;

	//����
	pb=f.price.value;
	pb = no_comma(String(pb));

	//�հ�
	t=f.total.value;
	t = no_comma(String(t));

	//�԰�
	size=f.size.selectedIndex;

	//�ܰ�
	//dan=f.dan.value;
	dan=0;


//======= ������ ���� ����ǥ : P+������+�԰ݰ�
	p10=new Array(0,34000,66000,100000,154000,200000);//������_48��_1��
	p11=new Array(0,50000,82000,110000,183000,231000);//������_48��_2��
	p20=new Array(0,56000,80000,133000,198000,289000);//������_32��_1��
	p21=new Array(0,72000,96000,163000,226000,316000);//������_32��_2��
	p30=new Array(0,72000,108000,190000,277000,400000);//������_A5_1��
	p31=new Array(0,90000,132000,235000,334000,489000);//������_A5_2��
	p40=new Array(0,28000,50000,66000,72000,102000,128000);//������_A4_1��
	p41=new Array(0,42000,66000,81000,86000,129000,153000);//������_A4_2��
	p50=new Array(0,24000,46000,66000,72000,82000,98000);//������_16��_1��
	p51=new Array(0,40000,62000,81000,86000,95000,110000);//������_16��_2��

	p60=new Array(0,64000,106000,171000,257000,340000);//NCR(������)_48��_1��
	p61=new Array(0,80000,122000,200000,286000,367000);//NCR(������)_48��_2��
	p70=new Array(0,74000,130000,210000,315000,476000);//NCR(������)_32��_1��
	p71=new Array(0,90000,146000,239000,343000,510000);//NCR(������)_32��_2��
	p80=new Array(0,100000,176000,283000,420000,600000);//NCR(������)_A5_1��
	p81=new Array(0,116000,208000,328000,460000,690000);//NCR(������)_A5_2��
	p90=new Array(0,50000,90000,114000,126000,150000,224000);//NCR(������)_A4_1��
	p91=new Array(0,66000,106000,129000,140000,177000,250000);//NCR(������)_A4_2��
	p100=new Array(0,44000,80000,114000,135000,147000,152000);//NCR(������)_16��_1��
	p101=new Array(0,60000,96000,129000,153000,160000,164000);//NCR(������)_16��_2��

	p110=new Array(0,84000,144000,256000,372000,510000);//NCR(��������)_48��_1��
	p111=new Array(0,100000,160000,285000,414000,550000);//NCR(��������)_48��_2��
	p120=new Array(0,110000,200000,370000,530000,714000);//NCR(��������)_32��_1��
	p121=new Array(0,126000,216000,400000,588000,748000);//NCR(��������)_32��_2��
	p130=new Array(0,148000,286000,501000,732000,1030000);//NCR(��������)_A5_1��
	p131=new Array(0,164000,308000,558000,804000,1167000);//NCR(��������)_A5_2��
	p140=new Array(0,70000,130000,190000,216000,255000);//NCR(��������)_A4_1��
	p141=new Array(0,86000,146000,220000,244000,282000);//NCR(��������)_A4_2��
	p150=new Array(0,60000,100000,171000,198000,230000);//NCR(��������)_16��_1��
	p151=new Array(0,76000,116000,186000,212000,256000);//NCR(��������)_16��_2��



	//������ �ɼǿ� ���� ����ǥ�� �� �迭���� �����´�.
		obj = eval("p"+paper+size);

	idx = f.cnt.selectedIndex;
	dan = obj[idx];		//���õ� ������ �ܰ�
	
	f.price.value = number_format(String(dan));
	num= 1;		//�ο�������

	if (f.design.checked==true)	{
		DC = parseInt(f.design.value);
	}
	else {
		DC = 0;
	}

	f.total.value = number_format(String(dan*num+DC));		//�Ѱ��� = �ܰ�*�ο���

	f.cntchk.value= f.cnt.selectedIndex;		//�������� ���õ� �ε������� ����Ѵ�.
}



//+++++++++++ ������ �Ƿڿ� ���� �߰� ���
function addCalc(pay){
	f = document.ordform;
	t = no_comma(String(f.total.value));

	tt = parseInt(t) + parseInt(pay);
	f.total.value=number_format(String(tt));

	if (f.design.checked)	//������ ����� ����� ��� ���� ���� �������.
	{
		tt = parseInt(t) + parseInt(pay);
		f.total.value=number_format(String(tt));
	}
	else{
		tt = parseInt(t) - parseInt(pay);
		f.total.value=number_format(String(tt));
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