<?php
$M123 = isset($M123) ? $M123 : '..';
$MenuSubIcon="&nbsp;<img src=".$M123."/img/left_icon123.gif width=3 height=5 border=0 align=absmiddle>&nbsp;";
$TTame=date("H");
?>

<?php if($TTame=="10"){?>oCMenu.level[0].filter="progid:DXImageTransform.Microsoft.Wheel(duration=0.5,spokes=5)"
<?php } else if($TTame=="11"){?>oCMenu.level[0].filter="progid:DXImageTransform.Microsoft.Barn(duration=0.5,orientation=horizontal)"
<?php } else if($TTame=="12"){?>oCMenu.level[0].filter="progid:DXImageTransform.Microsoft.Blinds(duration=0.5,bands=5)"
<?php } else if($TTame=="13"){?>oCMenu.level[0].filter="progid:DXImageTransform.Microsoft.CheckerBoard(duration=0.5)"
<?php } else if($TTame=="14"){?>oCMenu.level[0].filter="progid:DXImageTransform.Microsoft.Fade(duration=0.5)"
<?php } else if($TTame=="15"){?>oCMenu.level[0].filter="progid:DXImageTransform.Microsoft.GradientWipe(duration=0.5,wipeStyle=0)"
<?php } else if($TTame=="16"){?>oCMenu.level[0].filter="progid:DXImageTransform.Microsoft.Iris(duration=0.5,irisStyle=STAR)"
<?php } else if($TTame=="17"){?>oCMenu.level[0].filter="progid:DXImageTransform.Microsoft.Iris(duration=0.5,irisStyle=CIRCLE)"
<?php } else if($TTame=="18"){?>oCMenu.level[0].filter="progid:DXImageTransform.Microsoft.Pixelate(duration=0.5,maxSquare=40)"
<?php } else if($TTame=="19"){?>oCMenu.level[0].filter="progid:DXImageTransform.Microsoft.Wheel(duration=0.5,spokes=5)"
<?php } else if($TTame=="20"){?>oCMenu.level[0].filter="progid:DXImageTransform.Microsoft.RandomDissolve(duration=0.5)"
<?php } else if($TTame=="21"){?>oCMenu.level[0].filter="progid:DXImageTransform.Microsoft.Spiral(duration=0.5)"
<?php } else if($TTame=="22"){?>oCMenu.level[0].filter="progid:DXImageTransform.Microsoft.Stretch(duration=0.5,stretchStyle=push)"
<?php } else{?>oCMenu.level[0].filter="progid:DXImageTransform.Microsoft.Strips(duration=0.5,motion=rightdown)"<?php } ?>

oCMenu.makeMenu('top0','','HELP')

	oCMenu.makeMenu('sub0_1','top0','<?php echo $MenuSubIcon?>업데이트사항','<?php echo $M123; ?>/../HELP/SoftUpgrade.php','_blank')
	oCMenu.makeMenu('sub0_2','top0','<?php echo $MenuSubIcon?>WEBSIL바로가기','http://www.websil.net','_blank')

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

oCMenu.makeMenu('top1','','관리자환경')

	oCMenu.makeMenu('sub1_1','top1','<?php echo $MenuSubIcon?>비밀번호 변경','<?php echo $M123; ?>/AdminConfig.php?mode=modify','Mlang')

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

oCMenu.makeMenu('top2','','게시판관리')

	oCMenu.makeMenu('sub2_1','top2','<?php echo $MenuSubIcon?>생성/관리/삭제','<?php echo $M123; ?>/bbs_admin.php?mode=list')
	oCMenu.makeMenu('sub2_2','top2','<?php echo $MenuSubIcon?>자료신고함','<?php echo $M123; ?>/BBSSinGo/index.php')
	oCMenu.makeMenu('sub2_3','top2','<?php echo $MenuSubIcon?>실적물 관리','<?php echo $M123; ?>/results/admin.php?mode=list')

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

oCMenu.makeMenu('top3','','회원관리')

	oCMenu.makeMenu('sub3_1','top3','<?php echo $MenuSubIcon?>LIST/검색/관리','<?php echo $M123; ?>/member/index.php')
    oCMenu.makeMenu('sub3_2','top3','<?php echo $MenuSubIcon?>메일관리')

	    oCMenu.makeMenu('sub3_2_1','sub3_2','<?php echo $MenuSubIcon?>가입완료메일','<?php echo $M123; ?>/member/JoinAdmin.php')
	    oCMenu.makeMenu('sub3_2_2','sub3_2','<?php echo $MenuSubIcon?>전체메일 관리','<?php echo $M123; ?>/member/MaillingJoinAdmin.php')
		oCMenu.makeMenu('sub3_2_3','sub3_2','<?php echo $MenuSubIcon?>전체 메일발송')

		  oCMenu.makeMenu('sub3_2_3_1','sub3_2_3','<?php echo $MenuSubIcon?>YES만 발송','<?php echo $M123; ?>/mailing/form.php?FFF=ok')
		  oCMenu.makeMenu('sub3_2_3_2','sub3_2_3','<?php echo $MenuSubIcon?>전체다 발송','<?php echo $M123; ?>/mailing/form.php')

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
oCMenu.makeMenu('top4','','인쇄업무프로그램')

    oCMenu.makeMenu('sub4_1','top4','<?php echo $MenuSubIcon?>견적안내프로그램')
	     oCMenu.makeMenu('sub4_1_1','sub4_1','<?php echo $MenuSubIcon?>전단지 관리','<?php echo $M123; ?>/MlangPrintAuto/inserted_List.php')
	     oCMenu.makeMenu('sub4_1_2','sub4_1','<?php echo $MenuSubIcon?>스티카 관리','<?php echo $M123; ?>/MlangPrintAuto/sticker_List.php')
	     oCMenu.makeMenu('sub4_1_3','sub4_1','<?php echo $MenuSubIcon?>명함 관리','<?php echo $M123; ?>/MlangPrintAuto/NameCard_List.php')
	     oCMenu.makeMenu('sub4_1_4','sub4_1','<?php echo $MenuSubIcon?>상품권 관리','<?php echo $M123; ?>/MlangPrintAuto/MerchandiseBond_List.php')
	     oCMenu.makeMenu('sub4_1_5','sub4_1','<?php echo $MenuSubIcon?>봉투 관리','<?php echo $M123; ?>/MlangPrintAuto/envelope_List.php')
	     oCMenu.makeMenu('sub4_1_6','sub4_1','<?php echo $MenuSubIcon?>양식지 관리','<?php echo $M123; ?>/MlangPrintAuto/NcrFlambeau_List.php')
	     oCMenu.makeMenu('sub4_1_7','sub4_1','<?php echo $MenuSubIcon?>카다로그 관리','<?php echo $M123; ?>/MlangPrintAuto/cadarok_List.php')
		 //oCMenu.makeMenu('sub4_1_8','sub4_1','<?php echo $MenuSubIcon?>카다로그 관리','<?php echo $M123; ?>/MlangPrintAuto/cadarokTwo_List.php')
	     oCMenu.makeMenu('sub4_1_9','sub4_1','<?php echo $MenuSubIcon?>소량인쇄 관리','<?php echo $M123; ?>/MlangPrintAuto/LittlePrint_List.php')
	     oCMenu.makeMenu('sub4_1_10','sub4_1','<?php echo $MenuSubIcon?>견적안내 주문','<?php echo $M123; ?>/MlangPrintAuto/OrderList.php')
	     oCMenu.makeMenu('sub4_1_11','sub4_1','<?php echo $MenuSubIcon?>시안직접올리기','<?php echo $M123; ?>/MlangPrintAuto/admin.php?mode=AdminMlangOrdert','Mlang')
	     oCMenu.makeMenu('sub4_1_12','sub4_1','<?php echo $MenuSubIcon?>견적안내  통합관리','<?php echo $M123; ?>/MlangPrintAuto/admin.php?mode=BankForm&code=Text','Mlang')

    oCMenu.makeMenu('sub4_2','top4','<?php echo $MenuSubIcon?>수동견적프로그램')
	     oCMenu.makeMenu('sub4_2_10','sub4_2','<?php echo $MenuSubIcon?>수동견적 주문','<?php echo $M123; ?>/MlangPrintAuto/OfferOrder.php')

	oCMenu.makeMenu('sub4_3','top4','<?php echo $MenuSubIcon?>인쇄관련 업무')
	     oCMenu.makeMenu('sub4_3_1','sub4_3','<?php echo $MenuSubIcon?>주문자 접수일보','<?php echo $M123; ?>/MlangPrintAuto/MemberOrderOfficeList.php')

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


//menu activation
oCMenu.makeStyle(); oCMenu.construct()		

//check scrolling routine
function cm_checkScrolled(obj){
	if(bw.ns4 || bw.ns6) obj.scrolledY=obj.win.pageYOffset
	else obj.scrolledY=obj.win.document.body.scrollTop
	if(obj.scrolledY!=obj.lastScrolled){
		if(!obj.useframes){
			self.status=obj.scrolledY
			if(obj.scrolledY>119){
				for(i=0;i<obj.l[0].num;i++){var sobj=obj.l[0].o[i].oBorder; sobj.moveY(obj.scrolledY)}
				if(obj.usebar) obj.oBar.moveY(obj.scrolledY)
			}else{
				for(i=0;i<obj.l[0].num;i++){var sobj=obj.l[0].o[i].oBorder; sobj.moveY(obj.fromtop)}
				if(obj.usebar) obj.oBar.moveY(obj.fromtop)
			}

		}
		obj.lastScrolled=obj.scrolledY; page.y=obj.scrolledY; page.y2=page.y2orig+obj.scrolledY
		if(!obj.useframes || bw.ie){ clearTimeout(obj.tim); obj.isover=0; obj.hideSubs(1,0)}
	}
	if((bw.ns4 || bw.ns6) && !obj.useframes) setTimeout("cm_checkScrolled("+obj.name+")",200)
}