<?php
// 게시판의 필드들.... Mlang_{$table}_bbs //////////////////////////////////////////////////////////////////////////////////////
if(!$DbDir){$DbDir="..";}
include "$DbDir/db.php";

// $no 변수가 정의되어 있는지 확인하고, 없으면 요청 파라미터에서 가져옴
$no = isset($no) ? $no : (isset($_REQUEST['no']) ? $_REQUEST['no'] : 0);

$result= mysqli_query($db, "select * from Mlang_{$table}_bbs where Mlang_bbs_no='$no'");
$rows=mysqli_num_rows($result);
if($rows){

while($row= mysqli_fetch_array($result)) 
{ 
//Mlang_bbs_no mediumint(12) unsigned NOT NULL auto_increment, // 게시글 번호
//Mlang_bbs_member varchar(100) NOT NULL default '',                     // 등록인
//Mlang_bbs_title text,                                                                          // 제목
//Mlang_bbs_style varchar(100) NOT NULL default 'br',                       // 문서형식
//Mlang_bbs_connent text,                                                                  // 내용
//Mlang_bbs_link text,                                                                          // 링크 파일
//Mlang_bbs_file text,                                                                           // 업로드 파일
//Mlang_bbs_pass varchar(100) NOT NULL default '',                          // 비밀번호
//Mlang_bbs_count int(12) NOT NULL default '0',                                  // 카운터
//Mlang_bbs_rec int(12) NOT NULL default '0',                                     // 추천                        
//Mlang_bbs_secret varchar(100) NOT NULL default 'yes',                  // 글의 공개, 비공개
//Mlang_bbs_reply int(12) NOT NULL default '0',                                  // 몇번글인가 (답변글일경우) 의 여부 0 은 본글을의미           
//Mlang_date date NOT NULL default '0000-00-00',                              // 등록날짜


$BbsViewMlang_bbs_no=$row['Mlang_bbs_no'];
$BbsViewMlang_bbs_member=htmlspecialchars($row['Mlang_bbs_member']);
$BbsViewMlang_bbs_title=htmlspecialchars($row['Mlang_bbs_title']);
$BbsViewMlang_bbs_style=$row['Mlang_bbs_style'];
$BbsViewMlang_bbs_connent=$row['Mlang_bbs_connent'];
$BbsViewMlang_bbs_link=htmlspecialchars($row['Mlang_bbs_link']);
$BbsViewMlang_bbs_file=htmlspecialchars($row['Mlang_bbs_file']);
$BbsViewMlang_bbs_pass=$row['Mlang_bbs_pass'];
$BbsViewMlang_bbs_count=$row['Mlang_bbs_count'];
$BbsViewMlang_bbs_rec=$row['Mlang_bbs_rec'];                     
$BbsViewMlang_bbs_secret=$row['Mlang_bbs_secret'];
$BbsViewMlang_bbs_reply=$row['Mlang_bbs_reply'];
$BbsViewMlang_date=$row['Mlang_date'];
$BbsViewMlang_CATEGORY=$row['CATEGORY'];
$BbsViewMlang_bbs_NoticeStyle=$row['NoticeSelect'];

}

}else{
		echo "
			<script>
				window.alert('게시판의  자료가 없습니다.\\n\\n삭제된 자료일수 있으니 확인 해주세요..!!');
		        opener.parent.location.reload();
                window.self.close();
			</script>";
		exit;
}

mysqli_close($db); 
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>