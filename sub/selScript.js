	  
	  
//++++++++++++++++++������ ����������..
function selPaper(){
f=document.ordform;
	
	//������ �޴��� ������ ��� �޴����� ���� �κ��� ����
	if(f.paper.value == "1" || f.paper.value==""){
		document.all["coatingA"].style.visibility = "visible"; 
		document.all["coatingA"].style.position = "";
	}else{
		document.all["coatingA"].style.visibility = "hidden";
		document.all["coatingA"].style.position = "absolute";
	}

	//������ ������ ���� ������ ��Ÿ��
	nc = f.paper.value;  //������ Value

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
	
	//���� ����
	f.price.value ="0";
	f.total.value="0";
//	f.op.value="0";

// ��� �����θ�� �������� �ʱ�
	for (i=0;i<=2;i++)
	{
		f.design[i].checked=false;
	}
	
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
size=f.size.value;

//����
coating=f.coating.value;

//�ܰ�
//dan=f.dan.value;
dan=0;


//======= ����ǥ : P+������+�԰ݰ�+����
	p111=new Array(0,7000,12000,24000,32000,42000,50000,70000,120000,200000,350000);  //������/���ÿ���_�ܸ�_������ ����
	p112=new Array(0,10000,18000,34000,44000,50000,55000,75000,140000,340000,500000);	//������/���ÿ���_�ܸ�_���� ����
	p121=new Array(0,10000,20000,34000,44000,50000,55000,75000,140000,340000,500000);	//������/���ÿ���_���_������ ����
	p122=new Array(0,15000,25000,42000,54000,60000,65000,85000,150000,380000,600000);	//������/���ÿ���_���_���� ����
	p21	=new Array(0,15000,28000,42000,55000,65000,250000,450000,1150000);	//�ֶ�/�ݴ������_�ܸ� ����
	p22	=new Array(0,18000,35000,52000,68000,80000,290000,550000,1300000);	//�ֶ�/�ݴ������_��� ����
	p31	=new Array(0,13000,24000,32000,44000,52000,100000,250000,450000,1150000);	//�׷��̽�_�ܸ� ����
	p32	=new Array(0,16000,32000,42000,54000,64000,120000,280000,550000,1300000);	//�׷��̽�_���_����
	p41	=new Array(0,16000,32000,44000,54000,60000,102000,255000,460000,1180000)	//������/��Ÿ��_�ܸ� ����
	p42	=new Array(0,20000,40000,54000,64000,70000,122000,290000,570000,1330000)	//������/��Ÿ��_��� ����
	p51	=new Array(0,60000,112000,150000,180000,210000,850000)	//�÷�Ƽ��_�ܸ� ����
	p52	=new Array(0,70000,128000,180000,220000,260000,950000)	//�÷�Ƽ��_��� ����

	//������ �ɼǿ� ���� ����ǥ�� �� �迭���� �����´�.
	if (paper=="1")	{	
		obj = eval("p1"+size+coating);
	} 
	else {
		obj = eval("p"+paper+size);
	}

	idx = eval("f.cnt"+paper).selectedIndex;
	dan = obj[idx];		//���õ� ������ �ܰ�
	
	f.price.value = number_format(String(dan));
	num= parseInt(f.num.value);		//�ο���

	if (f.designClick.value != "N")	{
		DC = parseInt(f.designClick.value);
	}
	else {
		DC = 0;
	}

	f.total.value = number_format(String(dan*num+DC));		//�Ѱ��� = �ܰ�*�ο���

	f.cntchk.value= eval("f.cnt"+paper).selectedIndex;		//������ ������ üũ�Ǿ� �ִ��� Ȯ���ϱ� ����.
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