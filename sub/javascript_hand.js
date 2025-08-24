function formChk(){
	f = document.ordform;
	
	if (f.content.value=="")
		{
			alert('견적의뢰 내용을 작성해주세요.');
			f.content.focus();
			return false;
		}
	else if (f.ordername.value=="")
		{
			alert('주문자의 이름을 적어주세요');
			f.ordername.focus();
			return false;
		}
/*	else if (f.company.value=="")
		{
			result=confirm('귀사의 이름이 없습니다. 계속하시겠습니까?');
			if (result==false){
				f.company.focus();
				return false;
			}	
		} */
	else if (f.tel1.value=="" || f.tel2.value=="" || f.tel3.value=="")
		{
			alert('주문자의 전화번호를 적어주세요');
			f.tel1.focus();
			return false;
		}
/*	else if (f.fax1.value=="" || f.fax2.value=="" || f.fax3.value=="")
		{
			alert('주문자의 팩스번호를 적어주세요');
			f.fax1.focus();
			return false;
		} */
/*	else if (f.cel1.value=="" || f.cel2.value=="" || f.cel3.value=="")
		{
			alert('주문자의 핸드폰 번호를 적어주세요');
			f.cel1.focus();
			return false;
		}  */
/*	else if (f.email.value=="")
		{
			alert('주문자의 이메일을 적어주세요.');
			f.email.focus();
			return false;
		}  */
	else if (f.atch1.value=="")
		{
			result=confirm("첨부파일이 없습니다. 파일첨부없이 계속 작성하시겠습니까?");
			if(result==false){
				f.atch1.focus();
				return false;
			}
		}
}