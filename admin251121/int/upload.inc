<?
function func_multi_upload($f_tmp_array, $f_name_array, $f_size_array, $f_type_array, $upload_dir, $forbidden_array=array()) {
        
        // 다수의 파일을 업로드할수 있다
        // 
        
        // $f_tmp_array[] : 업로드시 임시파일(배열)
        // $f_name_array[] : 파일이름(배열)
        // $f_size_array[] : 파일사이즈(배열)
        // $f_type_array[] : 파일타입(배열)
        // $upload_dir : 업로드 디렉토리. 시스템에 반드시 있어야 하며 적절한 퍼미션이 주어져야 한다
        // $forbidden_array : 금지확장자(배열). 디폴트 인자로 공백이면 모든 확장자를 업로드할 수있다

        /////////////// Start ///////////////
        
        // 업로드 폼의 갯수를 파악
        $form_count=count($f_tmp_array);
        $cnt=0;

        // 올린 갯수만큼 루프를 돌린다
        for ($idx=0; $idx<$form_count; $idx++) {
                
                if (!$f_size_array[$idx] && !$f_name_array[$idx]) {                        // 업로드한 파일이 없으면 다음 루프로 점프
                        continue;
                } else {
                        
                        // 업로드하려는 파일이 설정용량을 초과하면
                        if ($f_size_array[$idx] == 0) {
                                echo "<script language='javascript'>
                                        alert('$f_name_array[$idx] 파일은 설정된 업로드 용량을 초과하였습니다\\n다른 파일들은 업로드작업이 계속 수행됩니다');
                                        </script>";
                                continue;
                        }
                                
                        // 업로드하는 시스템이 로컬시스템이면
                        if (!strcmp($HTTP_SERVER_VARS["HTTP_HOST"],"localhost")) {
                                $file_array=explode("/", $f_name_array[$idx]);
                                $f_name_array[$idx]=$file_array[sizeof($file_array)-1];
                        }
                        
                        // 확장자를 조사해서 업로드가능 파일인지 판별한다
                        $ext_array=explode(".", $f_name_array[$idx]);
                        $extension=$ext_array[sizeof($ext_array)-1];
                        if (in_array($extension, $forbidden_array)) {
                                echo "<script language='javascript'>
                                        alert('업로드가 불가능한 확장자입니다');
                                        history.go(-1);
                                        </script>";
                                exit;
                        }
                        
                        // 업로드 디렉토리가 시스템에 있는지 조사한다.
                        if (!is_dir($upload_dir)) {
                                echo "<script language='javascript'>
                                        alert('$upload_dir 디렉토리가 존재하지 않습니다');
                                        history.go(-1);
                                        </script>";
                                exit;
                        }
                
                        // 해당 디렉토리에 업로드하려는 파일과 같은이름의 파일이 있는지 조사한다
                        $dest=$upload_dir.$f_name_array[$idx];
                        $name_array=explode(".", $f_name_array[$idx]);
                        
                        // 서버상에 업로드하려는 파일이 존재하면
                        if (file_exists($dest)) {
                                $file_array=explode(".", $f_name_array[$idx]);
                                $tmp_name="";
                                for ($i=0; $i<sizeof($name_array)-1; $i++) {
                                        $tmp_name .= $name_array[$i].".";
                                }
                                $tmp_name=substr($tmp_name, 0, -1);

                                // 동명의 파일이 존재하면 "기존파일명_숫자" 로 새로운 파일명이 만들어진다
                                for ($j=1; ; $j++) {
                                        $f_name_array[$idx]=$tmp_name."_".$j.".".$extension;
                                        $dest=$upload_dir.$f_name_array[$idx];

                                        // 서버상에 같은 파일명이 없으면 빠져나간다
                                        if (!file_exists($dest)) break;
                                }
                        }

                        // 임시 디렉토리에 저장된 바이너리 파일을 해당 디렉토리에 복사한다
                        if (!@copy($f_tmp_array[$idx], $dest)) {
                                echo "<script language='javascript'>
                                        alert('원본 파일을 복사하는도중 에러가 발생했습니다.\\n\\n관리자에게 문의하여 주십시오');
                                        history.go(-1);
                                        </script>";
                                exit;
                        }

                        // 임시 디렉토리의 바이너리 파일을 삭제한다
                        if (!@unlink($f_tmp_array[$idx])) {
                                echo "<script language='javascript'>
                                        alert('원본 파일을 삭제하는도중 에러가 발생했습니다.\\n\\n업로드는 완료되었습니다');
                                        history.go(-1);
                                        </script>";
                                exit;
                        }
                        
                }                // else 이하 끝
                $cnt++;
        }                        // for 끝

        // 업로드가 수행되었으면 1을 리턴한다(에러없음)
        return $cnt;
}
?>