<?
$result= mysql_query("select * from $table where no='$ModifyCode'",$db);
$rows=mysql_num_rows($result);
if($rows){

while($row= mysql_fetch_array($result)) 
{  
$GF_cate="$row[cate]";  
$GF_bizname="$row[bizname]"; 
$GF_name="$row[name]";  
$GF_tel="$row[tel]";  
$GF_fax="$row[fax]"; 
$GF_zip="$row[zip]";  
$GF_upfile="$row[photo]";  
$GF_upfile1="$row[photo1]";  
$GF_upfile2="$row[photo2]";  
$GF_upfile3="$row[photo3]";  
$GF_style="$row[cont_style]";  
$GF_cont="$row[cont]";  
}

}else{echo("<p align=center><b>DB 에 $ModifyCode 의 등록 자료가 없음.</b></p>"); exit;}
?>