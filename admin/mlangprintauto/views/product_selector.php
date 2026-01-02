<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>제품 관리 - 두손기획인쇄</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Malgun Gothic', sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .product-card {
            background: #fff;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }

        .product-card:hover {
            border-color: #4CAF50;
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.2);
            transform: translateY(-2px);
        }

        .product-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .product-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #333;
        }

        .product-category {
            font-size: 12px;
            color: #999;
            padding: 4px 12px;
            background: #f0f0f0;
            border-radius: 12px;
            display: inline-block;
        }

        .category-standard { background: #E3F2FD; color: #1976D2; }
        .category-complex { background: #FFF3E0; color: #F57C00; }
        .category-individual { background: #F3E5F5; color: #7B1FA2; }
        .category-special { background: #FFEBEE; color: #C62828; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📦 제품 관리</h1>
        <p class="subtitle">관리할 제품을 선택하세요</p>

        <div class="product-grid">
            <?php
            $products = ProductConfig::getAllProducts();
            $icons = [
                'inserted' => '📄',
                'namecard' => '👤',
                'envelope' => '✉️',
                'sticker' => '🏷️',
                'msticker' => '🧲',
                'cadarok' => '📖',
                'littleprint' => '🖼️',
                'merchandisebond' => '🎫',
                'ncrflambeau' => '📋'
            ];

            foreach ($products as $product) {
                $icon = $icons[$product['key']] ?? '📦';
                $category_class = 'category-' . $product['category'];
                $category_label = [
                    'standard' => '표준형',
                    'complex' => '복잡형',
                    'individual' => '개별형',
                    'special' => '특수형'
                ][$product['category']] ?? '';

                echo "<a href='product_manager.php?product={$product['key']}' class='product-card'>";
                echo "  <div class='product-icon'>{$icon}</div>";
                echo "  <div class='product-name'>{$product['name']}</div>";
                echo "  <div class='product-category {$category_class}'>{$category_label}</div>";
                echo "</a>";
            }
            ?>
        </div>
    </div>
</body>
</html>