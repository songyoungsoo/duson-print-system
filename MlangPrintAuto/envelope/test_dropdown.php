<?php
// 드롭다운 테스트 페이지
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>봉투 드롭다운 테스트</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        select { padding: 10px; margin: 10px; width: 300px; }
        .result { margin: 20px 0; padding: 10px; background: #f0f0f0; }
    </style>
</head>
<body>
    <h1>봉투 드롭다운 테스트</h1>
    
    <div>
        <label>봉투 구분:</label>
        <select id="category" onchange="loadTypes()">
            <option value="">선택하세요</option>
            <?php
            include "../../db.php";
            $connect = $db;
            mysqli_set_charset($connect, "utf8");
            
            $query = "SELECT no, title FROM MlangPrintAuto_transactionCate WHERE Ttable = 'envelope' AND BigNo = 0 ORDER BY no ASC";
            $result = mysqli_query($connect, $query);
            
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<option value='{$row['no']}'>{$row['title']}</option>";
            }
            
            mysqli_close($connect);
            ?>
        </select>
    </div>
    
    <div>
        <label>봉투 종류:</label>
        <select id="types">
            <option value="">구분을 먼저 선택하세요</option>
        </select>
    </div>
    
    <div class="result" id="result">
        결과가 여기에 표시됩니다.
    </div>
    
    <script>
        function loadTypes() {
            var categorySelect = document.getElementById('category');
            var typesSelect = document.getElementById('types');
            var resultDiv = document.getElementById('result');
            
            var categoryValue = categorySelect.value;
            
            if (!categoryValue) {
                typesSelect.innerHTML = '<option value="">구분을 먼저 선택하세요</option>';
                resultDiv.innerHTML = '구분을 선택하세요.';
                return;
            }
            
            // 로딩 표시
            typesSelect.innerHTML = '<option value="">로딩중...</option>';
            resultDiv.innerHTML = '데이터를 불러오는 중...';
            
            // AJAX 요청
            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        try {
                            var response = xhr.responseText.trim();
                            console.log('서버 응답:', response);
                            
                            var options = JSON.parse(response);
                            
                            typesSelect.innerHTML = '';
                            
                            if (options && options.length > 0) {
                                for (var i = 0; i < options.length; i++) {
                                    var option = document.createElement('option');
                                    option.value = options[i].no;
                                    option.text = options[i].title;
                                    typesSelect.appendChild(option);
                                }
                                resultDiv.innerHTML = '성공: ' + options.length + '개의 종류를 불러왔습니다.';
                            } else {
                                typesSelect.innerHTML = '<option value="">해당 구분에 종류가 없습니다</option>';
                                resultDiv.innerHTML = '해당 구분에 종류가 없습니다.';
                            }
                        } catch (e) {
                            console.error('JSON 파싱 오류:', e);
                            typesSelect.innerHTML = '<option value="">오류 발생</option>';
                            resultDiv.innerHTML = '오류: ' + e.message + '<br>응답: ' + xhr.responseText;
                        }
                    } else {
                        console.error('HTTP 오류:', xhr.status);
                        typesSelect.innerHTML = '<option value="">로딩 실패</option>';
                        resultDiv.innerHTML = 'HTTP 오류: ' + xhr.status + ' ' + xhr.statusText;
                    }
                }
            };
            
            xhr.open('GET', 'get_envelope_types.php?category_type=' + encodeURIComponent(categoryValue), true);
            xhr.send();
        }
    </script>
</body>
</html>