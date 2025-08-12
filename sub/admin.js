	f = document.ordform;
	//상품종류
	for (i=0;i<f.kind.length;i++){
		if (f.kind[i].value == "<?=$kind?>"){
			f.kind[i].checked = true;
		}
	}
	// 용지	
	for (i=0;i<f.paper.length;i++){
		if (f.paper.options[i].value=="<?=$paper?>"){
			f.paper.options[i].selected = true;
		}
	}
	//규격
	for (i=0;i<f.size.length;i++ ){
		if (f.size.options[i].value=="<?=$size?>"){
			f.size.options[i].selected = true;
		}
	}
	//코팅
	for (i=0;i<f.coating.length;i++ ){
		if(f.coating.options[i].value=="<?=$coating?>"){
			f.coating.options[i].selected = true;
		}
	}
	//수량
	for (i=0;i <= 5 ;i++)
	{
		num="cntA"+i;
		cnt=eval("document.ordform.cnt"+i);
		if(i == <?=$paper?>){
		document.all[num].style.visibility = "visible";
		document.all[num].style.position = "";
			for (i=0;i<cnt.length ;i++ ){
			if(cnt.options[i].index=="<?=$cnt?>"){
				cnt.options[i].selected=true;
			}
		}
		}else{
		document.all[num].style.visibility = "hidden";
		document.all[num].style.position = "absolute";
		}
	}
	//가격
	f.price.value="<?=$price?>";
	//인원
	for (i=0;i<f.num.length ;i++ ){
		if (f.num.options[i].value=="<?=$num?>"){
			f.num.options[i].selected = true;
		}
	}

	if("<?=$reorder?>"=="Y") f.reorder.checked = true;
	if("<?=$printer?>"=="Y") f.printer.checked = true;
	for (i=0;i<f.design.length ;i++ ){
		if(f.design[i].value=="<?=$design?>"){
			f.design[i].checked = true;
		}
	}
	f.total.value="<?=$total?>";

	f.ordername.value="<?=$ordername?>";
	f.company.value="<?=$company?>";
	f.tel1.value="<?=$tel[0]?>";f.tel2.value="<?=$tel[1]?>";f.tel3.value="<?=$tel[2]?>";
	f.fax1.value="<?=$fax[0]?>";f.fax2.value="<?=$fax[1]?>";f.fax3.value="<?=$fax[2]?>";
	f.cel1.value="<?=$cel[0]?>";f.cel2.value="<?=$cel[1]?>";f.cel3.value="<?=$cel[2]?>";
	f.email.value="<?=$email?>";
	f.memo.value="<?=$memo?>";

	function del(){
		result=confirm("정말로 삭제하시겠습니까?");
		if(result)	location.href="estimate_auto_PC.php?del=<?=$no?>";
	}
