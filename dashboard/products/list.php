<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';

$type = $_GET['type'] ?? '';

if (!isset($PRODUCT_TYPES[$type])) {
    header('Location: /dashboard/products/');
    exit;
}

$product_config = $PRODUCT_TYPES[$type];

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<main class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900"><?php echo $product_config['name']; ?> 옵션 관리</h1>
                <p class="mt-2 text-sm text-gray-600">테이블: <?php echo $product_config['table']; ?> | 단위: <?php echo $product_config['unit']; ?></p>
            </div>
            <a href="/dashboard/products/" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                제품 목록
            </a>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">스타일</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">섹션</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">수량</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">가격</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">관리</th>
                        </tr>
                    </thead>
                    <tbody id="productsTableBody" class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">로딩 중...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="pagination" class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    총 <span id="totalItems">0</span>개
                </div>
                <div id="paginationButtons" class="flex gap-2">
                </div>
            </div>
        </div>
    </div>
</main>

<script>
const productType = '<?php echo $type; ?>';
let currentPage = 1;

async function loadProducts(page = 1) {
    currentPage = page;
    
    const params = new URLSearchParams({
        action: 'list',
        type: productType,
        page: page
    });
    
    try {
        const response = await fetch(`/dashboard/api/products.php?${params}`);
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message);
        }
        
        const tbody = document.getElementById('productsTableBody');
        const products = result.data.data;
        
        if (products.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">제품 옵션이 없습니다.</td></tr>';
            return;
        }
        
        tbody.innerHTML = products.map(product => `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${product.no}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${product.style || '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">${product.Section || '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">${product.quantity || 0}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">${product.money ? parseInt(product.money).toLocaleString() + '원' : '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                    <button onclick="editProduct(${product.no}, '${product.money || ''}')" class="text-blue-600 hover:text-blue-800">수정</button>
                </td>
            </tr>
        `).join('');
        
        document.getElementById('totalItems').textContent = result.data.pagination.total_items;
        
        renderPagination(result.data.pagination);
        
    } catch (error) {
        console.error('Failed to load products:', error);
        document.getElementById('productsTableBody').innerHTML = 
            '<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">제품 목록을 불러오는데 실패했습니다.</td></tr>';
    }
}

function renderPagination(pagination) {
    const container = document.getElementById('paginationButtons');
    const buttons = [];
    
    if (pagination.current_page > 1) {
        buttons.push(`<button onclick="loadProducts(${pagination.current_page - 1})" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50">이전</button>`);
    }
    
    for (let i = Math.max(1, pagination.current_page - 2); i <= Math.min(pagination.total_pages, pagination.current_page + 2); i++) {
        const active = i === pagination.current_page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50';
        buttons.push(`<button onclick="loadProducts(${i})" class="px-3 py-1 border border-gray-300 rounded ${active}">${i}</button>`);
    }
    
    if (pagination.current_page < pagination.total_pages) {
        buttons.push(`<button onclick="loadProducts(${pagination.current_page + 1})" class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50">다음</button>`);
    }
    
    container.innerHTML = buttons.join('');
}

async function editProduct(id, currentMoney) {
    const newMoney = prompt('새 가격을 입력하세요:', currentMoney);
    
    if (newMoney === null || newMoney === currentMoney) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('action', 'update');
        formData.append('type', productType);
        formData.append('id', id);
        formData.append('money', newMoney);
        
        const response = await fetch('/dashboard/api/products.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('가격이 수정되었습니다.');
            loadProducts(currentPage);
        } else {
            alert('수정 실패: ' + result.message);
        }
    } catch (error) {
        alert('수정 중 오류가 발생했습니다.');
    }
}

loadProducts(1);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
