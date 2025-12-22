function formChk(){
	f = document.ordform;
	if (f.paper.value=="")
		{
			alert('상품의 용지를 선택해주세요');
			f.paper.focus();
			return false;
		}
	else if (f.coatingchk.value=="")
		{
			alert('코팅 여부를 선택해주세요');
			f.coating.focus();
			return false;
		}
	else if (f.cntchk.value=="" || f.cntchk.value=="0")
		{
			alert('상품의 수량을 선택해주세요');
			f.cnt.focus();
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
	else if (f.cel1.value=="" || f.cel2.value=="" || f.cel3.value=="")
		{
			alert('주문자의 핸드폰 번호를 적어주세요');
			f.cel1.focus();
			return false;
		}
	else if (f.email.value=="")
		{
			alert('주문자의 이메일을 적어주세요.');
			f.email.focus();
			return false;
		}
	else if (f.memo.value=="")
		{
			alert('간단하게 남기실 메모를 작성해주세요. 전화 가능 시간 등');
			f.memo.focus();
			return false;
		}
	else if (f.atch1.value=="")
		{
			result=confirm("첨부파일이 없습니다. 파일첨부없이 계속 작성하시겠습니까?");
			if(result==false){
				f.atch1.focus();
				return false;
			}
		}
}