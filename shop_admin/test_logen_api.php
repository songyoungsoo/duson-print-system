<?php
/**
 * 로젠 API 테스트 페이지
 *
 * 실행: http://localhost/shop_admin/test_logen_api.php
 */

include "lib.php";  // 관리자 인증
require_once __DIR__ . '/../db.php';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>로젠 API 테스트</title>
    <style>
        body {
            font-family: 'Malgun Gothic', sans-serif;
            margin: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #28a745;
            padding-bottom: 10px;
        }
        .section {
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .section h2 {
            color: #28a745;
            margin-top: 0;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input[type="text"], select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background: #28a745;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }
        button:hover {
            background: #218838;
        }
        button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        #result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            display: none;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .order-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ddd;
            margin-bottom: 15px;
        }
        .order-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        .order-item:hover {
            background: #f0f0f0;
        }
        .order-item input[type="checkbox"] {
            margin-right: 10px;
        }
        pre {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 로젠택배 API 테스트</h1>

        <!-- Section 1: 최근 주문 목록 -->
        <div class="section">
            <h2>1️⃣ 최근 주문 선택 (송장번호 없는 주문)</h2>
            <div class="order-list" id="orderList">
                <p style="text-align:center; color:#999;">로딩 중...</p>
            </div>
            <button onclick="testAutoRegister()" id="btnAutoRegister">선택한 주문 자동 접수</button>
        </div>

        <!-- Section 2: 수동 주문번호 입력 -->
        <div class="section">
            <h2>2️⃣ 수동 주문번호 입력</h2>
            <label>주문번호 (쉼표로 구분):</label>
            <input type="text" id="manualOrderNos" placeholder="예: 103700, 103701, 103702">
            <button onclick="testManualRegister()">수동 접수</button>
        </div>

        <!-- 결과 표시 -->
        <div id="result"></div>
    </div>

    <script>
        // 페이지 로드 시 최근 주문 목록 가져오기
        window.onload = function() {
            loadRecentOrders();
        };

        // 최근 주문 목록 로드
        function loadRecentOrders() {
            fetch('get_recent_orders.php')
                .then(response => response.json())
                .then(data => {
                    const orderList = document.getElementById('orderList');
                    if (data.success && data.orders.length > 0) {
                        let html = '';
                        data.orders.forEach(order => {
                            html += `
                                <div class="order-item">
                                    <label>
                                        <input type="checkbox" value="${order.no}" class="order-checkbox">
                                        주문 #${order.no} - ${order.name} - ${order.Type} - ${order.date}
                                    </label>
                                </div>
                            `;
                        });
                        orderList.innerHTML = html;
                    } else {
                        orderList.innerHTML = '<p style="text-align:center; color:#999;">송장번호가 없는 주문이 없습니다.</p>';
                    }
                })
                .catch(error => {
                    document.getElementById('orderList').innerHTML = '<p style="color:red;">주문 목록 로드 실패: ' + error + '</p>';
                });
        }

        // 선택한 주문 자동 접수
        function testAutoRegister() {
            const checkboxes = document.querySelectorAll('.order-checkbox:checked');
            const orderNos = Array.from(checkboxes).map(cb => parseInt(cb.value));

            if (orderNos.length === 0) {
                alert('주문을 선택해주세요.');
                return;
            }

            callAPI(orderNos);
        }

        // 수동 주문번호 접수
        function testManualRegister() {
            const input = document.getElementById('manualOrderNos').value;
            const orderNos = input.split(',').map(no => parseInt(no.trim())).filter(no => !isNaN(no));

            if (orderNos.length === 0) {
                alert('유효한 주문번호를 입력해주세요.');
                return;
            }

            callAPI(orderNos);
        }

        // API 호출
        function callAPI(orderNos) {
            const resultDiv = document.getElementById('result');
            const btnAutoRegister = document.getElementById('btnAutoRegister');

            resultDiv.innerHTML = '⏳ 처리 중...';
            resultDiv.className = '';
            resultDiv.style.display = 'block';
            btnAutoRegister.disabled = true;

            fetch('logen_auto_register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ order_nos: orderNos })
            })
            .then(response => response.json())
            .then(data => {
                btnAutoRegister.disabled = false;

                if (data.success) {
                    resultDiv.className = 'success';
                    let html = `<h3>✅ ${data.message}</h3>`;
                    html += '<h4>상세 결과:</h4>';
                    html += '<pre>' + JSON.stringify(data.details, null, 2) + '</pre>';
                    resultDiv.innerHTML = html;

                    // 주문 목록 새로고침
                    setTimeout(loadRecentOrders, 1000);
                } else {
                    resultDiv.className = 'error';
                    let html = `<h3>❌ ${data.message}</h3>`;
                    if (data.details) {
                        html += '<h4>상세 결과:</h4>';
                        html += '<pre>' + JSON.stringify(data.details, null, 2) + '</pre>';
                    }
                    resultDiv.innerHTML = html;
                }
            })
            .catch(error => {
                btnAutoRegister.disabled = false;
                resultDiv.className = 'error';
                resultDiv.innerHTML = '<h3>❌ API 호출 실패</h3><p>' + error + '</p>';
            });
        }
    </script>
</body>
</html>
