
// ++++++++ ������ �԰� ����� ����/����ũ�� ���� : ������ ����������
function selPaper(f) { 
	f = document.ordform;

	//������ ����/����ũ�� ��
	c1 = new Array('100����','����ũ��','120����');
	c2 = new Array('100����','����ũ��','120����');
	c3 = new Array('����ũ��','120����');
	c4 = new Array('����ũ��');
	c5 = new Array('100����','����ũ��','120����','ũ����Ʈ��(Ȳ����)');
	c6 = new Array('����ũ��');

	var sn = f.paper.selectedIndex;
		
	cObj = eval("c"+sn);
    f.coating.options.length = cObj.length + 1; 
    f.coating.options[0] = new Option("�� ����/����ũ�� ��", "notset", true, false); 
    for (var i = 1; i <= cObj.length; i++) { 
        f.coating.options[i] = new Option(cObj[i-1]); 
    } 


	//��������Ʈ�� �ʱ�ȭ�Ѵ�.
	f.cnt.options[0].selected=true;

	//���� ����
	f.price.value ="0";
	f.total.value="0";

	// ��� �����θ�� �������� �ʱ�
	f.design.checked=false;
}


	  
//++++++++++++++++++ ����/����ũ ���ÿ��ο� ���� Į�� �ɼ� ����

function viewSize(){
	f=document.ordform;
	p = f.paper.selectedIndex;
	c = f.coating.selectedIndex;
	
	if((p=="1" || p=="5") && (c=="2"||c=="3") ){		
		f.size.options[1] = new Option("Į��"); 
		f.size.options[1].value = new Option("2");
	}

	f.coatingchk.value=f.coating.selectedIndex;	//����/����ũ���� ���õ� �ε������� ����Ѵ�.
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

	//����
	coating=f.coating.selectedIndex;

	//�ܰ�
	//dan=f.dan.value;
	dan=0;


//======= ������ ���� ����ǥ : P+����/����ũ+�԰ݰ�
	p110 = new Array(0,40000,80000,120000,160000,200000,240000,280000,320000,360000,400000);//A4�Һ���_100����_1��
	p120 = new Array(0,70000,130000,170000,230000,280000,335000,380000,425000,465000,578000);//A4�Һ���_����ũ��_1��
	p121 = new Array(0,110000,200000,290000,340000,390000,440000,490000,540000,590000,640000);//A4�Һ���_����ũ��_Į��
	p130 = new Array(0,45000,90000,135000,180000,225000,270000,315000,360000,405000,450000);//A4�Һ���_120����_1��
	p131 = new Array(0,110000,200000,290000,340000,390000,440000,490000,540000,590000,640000);//A4�Һ���_120����_Į��

	p210 = new Array(0,40000,80000,120000,160000,200000,240000,280000,320000,360000,400000);//16���Һ���_100����_1��
	p220 = new Array(0,60000,120000,165000,210000,255000,300000,345000,390000,435000,480000);//16���Һ���_����ũ��_1��
	p230 = new Array(0,45000,90000,135000,180000,225000,270000,315000,360000,405000,450000);//16���Һ���_120����_1��

	p310 = new Array(0,60000,120000,165000,210000,255000,300000,345000,390000,435000,480000);//Ƽ�Ϻ���_����ũ��_1��

	p410 = new Array(0,70000,140000,200000,260000,320000,375000,430000,485000,540000,595000);//���������_����ũ��_1��

	p510 = new Array(0,90000,178000,218000,294000,368000,442000,516000,558000,588000,664000);//�����_100����_1��
	p520 = new Array(0,110000,200000,280000,360000,480000,560000,640000,720000,800000,880000);//�����_����ũ��_1��
	p521 = new Array(0,190000,190000,420000,510000,630000,720000,810000,910000,1050000,1100000);//�����_����ũ��_Į��
	p530 = new Array(0,110000,200000,280000,360000,480000,560000,640000,720000,800000,880000);//�����_120����_1��
	p531 = new Array(0,190000,190000,420000,510000,630000,720000,810000,910000,910000,1100000);//�����_120����-Į��
	p540 = new Array(0,70000,140000,210000,280000,350000,410000,470000,530000,600000,650000);//�����_ũ����Ʈ��_1��

	p610 = new Array(0,140000,270000,360000,450000,540000,630000,720000,810000,900000,990000);//Ư�����_����ũ��_1��


	//������ �ɼǿ� ���� ����ǥ�� �� �迭���� �����´�.
		obj = eval("p"+paper+coating+size);

	idx = f.cnt.selectedIndex;
	dan = obj[idx];		//���õ� ������ �ܰ�
	
	f.price.value = number_format(String(dan));
	num= 1;	//�ο���

	if (f.design.checked==true)	{
		DC = parseInt(f.design.value);
	}
	else {
		DC = 0;
	}

	f.total.value = number_format(String(dan*num+DC));		//�Ѱ��� = �ܰ�*�ο���

	f.cntchk.value= f.cnt.selectedIndex;		//�������� ���õ� �ε������� ����Ѵ�.
	f.sizechk.value=size;					//�԰ݰ����� Į���� �ε������� ����Ѵ�.;
	
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