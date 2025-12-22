/**
 * 품목 관리 시스템 - 프론트엔드 로직
 * 두손기획인쇄
 */

// 전역 상태
let currentProduct = null;
let productConfig = null;
let currentFilters = {
    selector1: '',
    selector2: '',
    selector3: ''
};
let currentPage = 1;
let itemsPerPage = 30;
let totalItems = 0;
let allData = [];
let currentColumns = [];

/**
 * 품목 선택 핸들러
 */
function selectProduct(productKey) {
    console.log('품목 선택:', productKey);
    currentProduct = productKey;

    // 모든 버튼 비활성화
    document.querySelectorAll('.product-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    // 현재 버튼 활성화 (클릭된 버튼 찾기)
    const clickedBtn = document.querySelector(`[data-product="${productKey}"]`);
    if (clickedBtn) {
        clickedBtn.classList.add('active');

        // 설정 로드 및 필터 렌더링
        const selectorsCount = parseInt(clickedBtn.dataset.selectors);
        console.log('셀렉터 개수:', selectorsCount);
        renderFilterSelectors(productKey, selectorsCount);
    }

    // 필터 섹션 표시
    document.getElementById('filterSection').style.display = 'block';

    // 가격표 섹션 숨김
    document.getElementById('priceTableSection').style.display = 'none';

    // 필터 초기화
    currentFilters = {selector1: '', selector2: '', selector3: ''};
}

/**
 * 필터 셀렉터 렌더링
 */
async function renderFilterSelectors(productKey, selectorsCount) {
    const container = document.getElementById('filterSelectors');
    container.innerHTML = '';

    // 설정 가져오기 (라벨 정보 포함)
    const configResponse = await fetch(`api/get_product_config.php?product=${productKey}`)
        .then(res => res.json());

    if (!configResponse.success) {
        alert('설정을 불러올 수 없습니다');
        return;
    }

    // 전역 변수에 저장
    productConfig = configResponse;
    const selectorLabels = configResponse.selector_labels || [];

    for (let i = 1; i <= selectorsCount; i++) {
        const wrapper = document.createElement('div');
        wrapper.className = 'filter-item';

        const label = document.createElement('label');
        label.textContent = selectorLabels[i-1];
        label.className = 'filter-label';

        const select = document.createElement('select');
        select.id = `selector${i}`;
        select.className = 'filter-select';
        select.innerHTML = '<option value="">전체</option>';

        // 1단계는 즉시 로드
        if (i === 1) {
            await loadSelectorOptions(productKey, i, 0, select);
        }

        // 상위 셀렉터 변경 시 하위 셀렉터 로드
        select.addEventListener('change', async function() {
            console.log(`🔄 ${selectorLabels[i-1]} 변경됨: ${this.value} (이전 값에서 변경)`);
            currentFilters[`selector${i}`] = this.value;

            // 하위 셀렉터 리셋 및 로드
            if (i < selectorsCount) {
                // 카다록/전단지 특수 처리: 2단계 선택 시 3단계를 건드리지 않음
                // (3단계는 1단계 값을 parent로 사용하므로 2단계와 독립적)
                // 카다록: 2=규격(BigNo), 3=종이종류(TreeNo)
                // 전단지: 2=종이종류(TreeNo), 3=종이규격(BigNo)
                const independentProducts = ['cadarok', 'inserted'];
                const skipNextLoad = (independentProducts.includes(productKey) && i === 2);

                console.log(`📂 셀렉터 ${i} 변경 로직:`, {
                    product: productKey,
                    isIndependentProduct: independentProducts.includes(productKey),
                    level: i,
                    skipNextLoad: skipNextLoad
                });

                if (this.value) {
                    console.log(`📂 하위 드롭다운 초기화 시작`);
                }

                if (!skipNextLoad) {
                    // 다음 단계 로드
                    const nextSelect = document.getElementById(`selector${i+1}`);
                    if (nextSelect) {
                        const prevValue = nextSelect.value;
                        nextSelect.innerHTML = '<option value="">전체</option>';
                        console.log(`  ✓ ${selectorLabels[i]} 초기화 완료 (이전 값: "${prevValue}" → 현재 값: "전체")`);

                        if (this.value) {
                            console.log(`📏 ${selectorLabels[i]} 옵션 로드 시작`);
                            await loadSelectorOptions(productKey, i+1, this.value, nextSelect);
                        }
                    }
                }

                // 1단계 선택 시 2단계와 3단계를 동시에 자동 로드하는 제품들
                // - 전단지: 혼합 (1=BigNo, 2=TreeNo, 3=BigNo) - 2단계(종이종류)와 3단계(종이규격) 모두 1단계 값 참조
                // - 카다록: 혼합 (1=BigNo, 2=BigNo, 3=TreeNo) - 2단계(규격)와 3단계(종이종류) 모두 1단계 값 참조
                // - 양식지/포스터: BigNo 기반 (1→2→3 모두 BigNo 사용)
                const autoLoad3rdProducts = ['inserted', 'ncrflambeau', 'littleprint', 'cadarok'];
                if (i === 1 && selectorsCount === 3 && autoLoad3rdProducts.includes(productKey)) {
                    const thirdSelect = document.getElementById('selector3');
                    if (thirdSelect) {
                        const prevValue3rd = thirdSelect.value;
                        thirdSelect.innerHTML = '<option value="">전체</option>';
                        console.log(`  ✓ ${selectorLabels[2]} 초기화 완료 (이전 값: "${prevValue3rd}" → 현재 값: "전체")`);

                        if (this.value) {
                            console.log(`📏 ${selectorLabels[2]} 옵션 로드 시작 (1단계 기준)`);
                            await loadSelectorOptions(productKey, 3, this.value, thirdSelect);
                        }
                    }
                }


                // 하위의 하위 셀렉터도 리셋
                for (let j = i + 2; j <= selectorsCount; j++) {
                    const resetSelect = document.getElementById(`selector${j}`);
                    if (resetSelect && j !== 3) { // 3단계는 위에서 처리했으므로 제외
                        const prevValueReset = resetSelect.value;
                        resetSelect.innerHTML = '<option value="">전체</option>';
                        console.log(`  ✓ 셀렉터 ${j} 초기화 완료 (이전 값: "${prevValueReset}" → 현재 값: "전체")`);
                    }
                }

                console.log(`✅ 모든 하위 드롭다운 초기화 완료`);
            }
        });

        wrapper.appendChild(label);
        wrapper.appendChild(select);
        container.appendChild(wrapper);
    }
}

/**
 * 셀렉터 옵션 로드
 */
async function loadSelectorOptions(productKey, level, parentId, selectElement) {
    try {
        const url = `api/get_categories.php?product=${productKey}&level=${level}&parent_id=${parentId}`;
        console.log(`📏 레벨 ${level} 옵션 로드 시작:`, {product: productKey, parentId});

        const response = await fetch(url);
        const data = await response.json();

        console.log(`📏 레벨 ${level} 응답:`, {success: data.success, count: data.categories?.length});

        if (data.success) {
            selectElement.innerHTML = '<option value="">전체</option>';
            data.categories.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat.no;
                option.textContent = cat.title;
                selectElement.appendChild(option);
            });
            console.log(`✅ 레벨 ${level} 옵션 ${data.categories.length}개 로드 완료`);
        } else {
            console.error(`❌ 셀렉터 로드 실패 (레벨 ${level}):`, data.message);
        }
    } catch (error) {
        console.error(`❌ 레벨 ${level} 옵션 로드 오류:`, error);
    }
}

/**
 * 가격표 조회
 */
async function loadPriceTable() {
    console.log('가격표 조회 시작');
    console.log('현재 품목:', currentProduct);

    if (!currentProduct) {
        alert('먼저 품목을 선택하세요');
        return;
    }

    // 로딩 표시
    showLoading(true);

    // 필터 값 가져오기
    const selector1 = document.getElementById('selector1')?.value || '';
    const selector2 = document.getElementById('selector2')?.value || '';
    const selector3 = document.getElementById('selector3')?.value || '';

    console.log('필터 값:', {selector1, selector2, selector3});

    try {
        const params = new URLSearchParams({
            product: currentProduct,
            selector1,
            selector2,
            selector3
        });

        const url = `api/get_price_table.php?${params}`;
        console.log('API 호출:', url);

        const response = await fetch(url);
        const data = await response.json();

        console.log('API 응답:', data);

        if (data.success) {
            allData = data.data;
            totalItems = data.total;
            currentPage = 1;
            currentColumns = data.columns; // 컬럼 정보 저장
            renderPriceTable(data.columns);
            renderPagination();
            document.getElementById('priceTableSection').style.display = 'block';
        } else {
            alert('오류: ' + data.message);
        }
    } catch (error) {
        console.error('가격표 로드 오류:', error);
        alert('가격표를 불러오는 중 오류가 발생했습니다');
    } finally {
        showLoading(false);
    }
}

/**
 * 가격표 렌더링 (페이지네이션 적용)
 */
function renderPriceTable(columns) {
    const thead = document.getElementById('tableHead');
    const tbody = document.getElementById('tableBody');

    // 현재 페이지 데이터 계산
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const rows = allData.slice(startIndex, endIndex);

    // 헤더
    thead.innerHTML = `
        <tr>
            ${columns.map(col => `<th>${col}</th>`).join('')}
            <th>작업</th>
        </tr>
    `;

    // 데이터 행
    if (rows.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="${columns.length + 1}" style="text-align:center; padding:40px; color:#999;">
                    조회된 데이터가 없습니다
                </td>
            </tr>
        `;
    } else {
        tbody.innerHTML = rows.map(row => `
            <tr>
                <td>${row.selector1_name}</td>
                <td>${row.selector2_name}</td>
                ${row.selector3_name ? `<td>${row.selector3_name}</td>` : ''}
                <td>${row.quantity}</td>
                <td class="price">${formatPrice(row.price_single)}</td>
                <td class="price">${formatPrice(row.price_double)}</td>
                <td class="action-buttons">
                    <button class="btn-edit" onclick="editRow(${row.id})">수정</button>
                    <button class="btn-delete" onclick="deleteRow(${row.id})">삭제</button>
                </td>
            </tr>
        `).join('');
    }

    // 총 개수 표시
    document.getElementById('totalCount').textContent = totalItems;
}

/**
 * 페이지네이션 렌더링
 */
function renderPagination() {
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    const paginationWrapper = document.getElementById('paginationWrapper');
    const pagination = document.getElementById('pagination');

    if (totalPages <= 1) {
        paginationWrapper.style.display = 'none';
        return;
    }

    paginationWrapper.style.display = 'block';

    let html = '';

    // 이전 버튼
    html += `<button onclick="goToPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>이전</button>`;

    // 페이지 번호
    const pageRange = 5; // 표시할 페이지 개수
    let startPage = Math.max(1, currentPage - Math.floor(pageRange / 2));
    let endPage = Math.min(totalPages, startPage + pageRange - 1);

    // 시작 페이지 조정
    if (endPage - startPage + 1 < pageRange) {
        startPage = Math.max(1, endPage - pageRange + 1);
    }

    // 첫 페이지
    if (startPage > 1) {
        html += `<button onclick="goToPage(1)">1</button>`;
        if (startPage > 2) {
            html += `<span class="dots">...</span>`;
        }
    }

    // 페이지 번호들
    for (let i = startPage; i <= endPage; i++) {
        html += `<button onclick="goToPage(${i})" class="${i === currentPage ? 'active' : ''}">${i}</button>`;
    }

    // 마지막 페이지
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            html += `<span class="dots">...</span>`;
        }
        html += `<button onclick="goToPage(${totalPages})">${totalPages}</button>`;
    }

    // 다음 버튼
    html += `<button onclick="goToPage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>다음</button>`;

    pagination.innerHTML = html;
}

/**
 * 페이지 이동
 */
function goToPage(page) {
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    if (page < 1 || page > totalPages) return;

    currentPage = page;

    // 저장된 컬럼 정보로 다시 렌더링
    renderPriceTable(currentColumns);
    renderPagination();

    // 스크롤을 테이블 상단으로 이동
    document.getElementById('priceTableSection').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

/**
 * 새 가격 추가 모달 열기
 */
async function openCreateModal() {
    if (!currentProduct) {
        alert('먼저 품목을 선택하세요');
        return;
    }

    // 모달 초기화
    document.getElementById('modalTitle').textContent = '새 가격 추가';
    document.getElementById('formAction').value = 'create';
    document.getElementById('formId').value = '0';
    document.getElementById('crudForm').reset();

    // 1단계 셀렉터 로드
    const selector1 = document.getElementById('formSelector1');
    await loadSelectorOptions(currentProduct, 1, 0, selector1);

    // 2단계 셀렉터 리셋
    document.getElementById('formSelector2').innerHTML = '<option value="">먼저 1단계를 선택하세요</option>';

    // 3단계 셀렉터 표시 여부
    const selectorsCount = parseInt(document.querySelector('.product-btn.active').dataset.selectors);
    document.getElementById('formSelector3Group').style.display = selectorsCount === 3 ? 'block' : 'none';

    if (selectorsCount === 3) {
        document.getElementById('formSelector3').innerHTML = '<option value="">먼저 2단계를 선택하세요</option>';
    }

    // 모달 표시
    document.getElementById('crudModal').style.display = 'flex';

    // 1단계 변경 시 2단계 로드
    selector1.onchange = async function() {
        const selector2 = document.getElementById('formSelector2');
        await loadSelectorOptions(currentProduct, 2, this.value, selector2);
    };

    // 2단계 변경 시 3단계 로드 (3셀렉터인 경우)
    // 단, 카다록/전단지는 2단계와 3단계가 독립적이므로 제외
    if (selectorsCount === 3) {
        const independentProducts = ['cadarok', 'inserted'];
        if (!independentProducts.includes(currentProduct)) {
            // 양식지, 포스터 등: 2단계 선택 시 3단계 로드
            document.getElementById('formSelector2').onchange = async function() {
                const selector3 = document.getElementById('formSelector3');
                await loadSelectorOptions(currentProduct, 3, this.value, selector3);
            };
        }
    }
}

/**
 * 수정 모달 열기
 */
async function editRow(id) {
    try {
        if (!currentProduct) {
            alert('먼저 품목을 선택하세요');
            return;
        }

        // 데이터 가져오기
        const response = await fetch('api/product_crud.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'get',
                product: currentProduct,
                id: id
            })
        });

        const result = await response.json();

        if (result.success) {
            const data = result.data;

            // 모달 초기화 (openCreateModal 대신 직접 설정)
            document.getElementById('modalTitle').textContent = '가격 수정';
            document.getElementById('formAction').value = 'update';
            document.getElementById('formId').value = id;
            document.getElementById('crudForm').reset();

            // 1단계 셀렉터 로드
            const selector1 = document.getElementById('formSelector1');
            await loadSelectorOptions(currentProduct, 1, 0, selector1);

            // 2단계 셀렉터 초기화
            document.getElementById('formSelector2').innerHTML = '<option value="">먼저 1단계를 선택하세요</option>';

            // 3단계 셀렉터 표시 여부
            const selectorsCount = parseInt(document.querySelector('.product-btn.active').dataset.selectors);
            document.getElementById('formSelector3Group').style.display = selectorsCount === 3 ? 'block' : 'none';

            if (selectorsCount === 3) {
                document.getElementById('formSelector3').innerHTML = '<option value="">먼저 2단계를 선택하세요</option>';
            }

            // 모달 표시
            document.getElementById('crudModal').style.display = 'flex';

            // 값 채우기
            setTimeout(async () => {
                // 1단계 값 설정 (API에서 selector1로 통일하여 반환)
                document.getElementById('formSelector1').value = data.selector1;

                // 2단계 로드 및 값 설정
                await loadSelectorOptions(currentProduct, 2, data.selector1, document.getElementById('formSelector2'));
                document.getElementById('formSelector2').value = data.selector2;

                // 3단계가 있는 경우
                if (selectorsCount === 3 && data.selector3) {
                    await loadSelectorOptions(currentProduct, 3, data.selector2, document.getElementById('formSelector3'));
                    document.getElementById('formSelector3').value = data.selector3;
                }

                // 수량 및 가격 설정
                document.getElementById('formQuantity').value = data.quantity;
                document.getElementById('formPriceSingle').value = data.price_single;
                document.getElementById('formPriceDouble').value = data.price_double;
            }, 300);

            // 1단계 변경 이벤트 (수정 모드에서는 불필요하지만 일관성 유지)
            selector1.onchange = async function() {
                const selector2 = document.getElementById('formSelector2');
                await loadSelectorOptions(currentProduct, 2, this.value, selector2);
            };

            // 2단계 변경 이벤트
            if (selectorsCount === 3) {
                const independentProducts = ['cadarok', 'inserted'];
                if (!independentProducts.includes(currentProduct)) {
                    document.getElementById('formSelector2').onchange = async function() {
                        const selector3 = document.getElementById('formSelector3');
                        await loadSelectorOptions(currentProduct, 3, this.value, selector3);
                    };
                }
            }
        } else {
            alert('데이터를 불러올 수 없습니다');
        }
    } catch (error) {
        console.error('수정 모달 오류:', error);
        alert('오류가 발생했습니다');
    }
}

/**
 * 삭제
 */
async function deleteRow(id) {
    if (!confirm('정말 삭제하시겠습니까?')) {
        return;
    }

    try {
        const response = await fetch('api/product_crud.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'delete',
                product: currentProduct,
                id: id
            })
        });

        const result = await response.json();

        if (result.success) {
            alert('삭제되었습니다');
            loadPriceTable();
        } else {
            alert('오류: ' + result.message);
        }
    } catch (error) {
        console.error('삭제 오류:', error);
        alert('오류가 발생했습니다');
    }
}

/**
 * 폼 제출
 */
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('crudForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const action = document.getElementById('formAction').value;
        const id = parseInt(document.getElementById('formId').value);

        const data = {
            selector1: document.getElementById('formSelector1').value,
            selector2: document.getElementById('formSelector2').value,
            quantity: document.getElementById('formQuantity').value,
            price_single: document.getElementById('formPriceSingle').value,
            price_double: document.getElementById('formPriceDouble').value
        };

        // 3단계 셀렉터가 있는 경우
        const selector3 = document.getElementById('formSelector3');
        if (selector3.offsetParent !== null) {
            data.selector3 = selector3.value;
        }

        try {
            const response = await fetch('api/product_crud.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    action: action,
                    product: currentProduct,
                    id: id,
                    data: data
                })
            });

            const result = await response.json();

            if (result.success) {
                alert(result.message);
                closeModal();
                loadPriceTable();
            } else {
                alert('오류: ' + result.message);
            }
        } catch (error) {
            console.error('저장 오류:', error);
            alert('오류가 발생했습니다');
        }
    });
});

/**
 * 모달 닫기
 */
function closeModal() {
    document.getElementById('crudModal').style.display = 'none';
}

/**
 * 필터 초기화
 */
function resetFilters() {
    document.querySelectorAll('.filter-select').forEach(select => {
        select.value = '';
    });
    currentFilters = {selector1: '', selector2: '', selector3: ''};
}

/**
 * 로딩 표시
 */
function showLoading(show) {
    const loading = document.getElementById('loading');
    if (show) {
        loading.classList.add('active');
    } else {
        loading.classList.remove('active');
    }
}

/**
 * 가격 포맷
 */
function formatPrice(price) {
    return new Intl.NumberFormat('ko-KR').format(price) + '원';
}

// 모달 외부 클릭 시 닫기
window.onclick = function(event) {
    const modal = document.getElementById('crudModal');
    if (event.target === modal) {
        closeModal();
    }
}
