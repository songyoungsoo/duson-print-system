function formChk(){
	f = document.ordform;
	
	if (f.content.value=="")
		{
			alert('�����Ƿ� ������ �ۼ����ּ���.');
			f.content.focus();
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
/*	else if (f.cel1.value=="" || f.cel2.value=="" || f.cel3.value=="")
		{
			alert('�ֹ����� �ڵ��� ��ȣ�� �����ּ���');
			f.cel1.focus();
			return false;
		}  */
/*	else if (f.email.value=="")
		{
			alert('�ֹ����� �̸����� �����ּ���.');
			f.email.focus();
			return false;
		}  */
	else if (f.atch1.value=="")
		{
			result=confirm("÷�������� �����ϴ�. ����÷�ξ��� ��� �ۼ��Ͻðڽ��ϱ�?");
			if(result==false){
				f.atch1.focus();
				return false;
			}
		}
}