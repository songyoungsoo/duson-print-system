function formChk(){
	f = document.ordform;
	if (f.paper.value=="")
		{
			alert('��ǰ�� ������ �������ּ���');
			f.paper.focus();
			return false;
		}
	else if (f.size.value=="")
		{
			alert('�԰��� �����ϼ���');
			f.size.focus();
			return false;
		}
	else if (f.cntchk.value=="" || f.cntchk.value=="0")
		{
			alert('��ǰ�� ������ �������ּ���');
			f.cnt.focus();
			return false;
		}
	else if (f.ordername.value=="")
		{
			alert('�ֹ����� �̸��� �����ּ���');
			f.ordername.focus();
			return false;
		}
/*	else if (f.company.value=="")
		{
			result=confirm('�ͻ��� �̸��� �����ϴ�. ����Ͻðڽ��ϱ�?');
			if (result==false){
				f.company.focus();
				return false;
			}	
		} */
	else if (f.tel1.value=="" || f.tel2.value=="" || f.tel3.value=="")
		{
			alert('�ֹ����� ��ȭ��ȣ�� �����ּ���');
			f.tel1.focus();
			return false;
		}
/*	else if (f.fax1.value=="" || f.fax2.value=="" || f.fax3.value=="")
		{
			alert('�ֹ����� �ѽ���ȣ�� �����ּ���');
			f.fax1.focus();
			return false;
		} */
	else if (f.cel1.value=="" || f.cel2.value=="" || f.cel3.value=="")
		{
			alert('�ֹ����� �ڵ��� ��ȣ�� �����ּ���');
			f.cel1.focus();
			return false;
		}
	else if (f.email.value=="")
		{
			alert('�ֹ����� �̸����� �����ּ���.');
			f.email.focus();
			return false;
		}
	else if (f.memo.value=="")
		{
			alert('�����ϰ� ����� �޸� �ۼ����ּ���. ��ȭ ���� �ð� ��');
			f.memo.focus();
			return false;
		}
	else if (f.atch1.value=="")
		{
			result=confirm("÷�������� �����ϴ�. ����÷�ξ��� ��� �ۼ��Ͻðڽ��ϱ�?");
			if(result==false){
				f.atch1.focus();
				return false;
			}
		}
}