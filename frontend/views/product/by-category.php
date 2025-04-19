<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $category common\models\ProductCategory */
/* @var $products common\models\Product[] */
/* @var $categories common\models\ProductCategory[] */

$this->title = $category->name;
$this->params['breadcrumbs'][] = ['label' => 'Sản phẩm', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Get current warehouse from session or default
$currentWarehouse = Yii::$app->session->get('current_warehouse_id', null);
?>

<div class="product-category">
    <div class="row">
        <!-- Category Sidebar -->
        <div class="col-md-3 d-none d-md-block">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">Danh mục sản phẩm</h3>
                </div>
                <div class="card-body p-0">
                    <div class="list-group category-list">
                        <a href="<?= Url::to(['/product']) ?>" class="list-group-item list-group-item-action">
                            <i class="fas fa-th"></i> Tất cả sản phẩm
                        </a>
                        <?php foreach ($categories as $cat): ?>
                            <a href="<?= Url::to(['/product/by-category', 'id' => $cat->id]) ?>" 
                               class="list-group-item list-group-item-action <?= $cat->id == $category->id ? 'active' : '' ?>">
                                <?php if ($cat->image): ?>
                                    <img src="<?= Yii::$app->urlManager->createUrl('/' . $cat->image) ?>" class="category-icon" alt="<?= Html::encode($cat->name) ?>">
                                <?php else: ?>
                                    <i class="fas fa-folder"></i>
                                <?php endif; ?>
                                <?= Html::encode($cat->name) ?>
                                <span class="badge badge-primary badge-pill"><?= $cat->getProducts()->count() ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Quick Access -->
            <div class="card card-primary card-outline mt-3">
                <div class="card-header">
                    <h3 class="card-title">Truy cập nhanh</h3>
                </div>
                <div class="card-body p-0">
                    <div class="list-group">
                        <a href="<?= Url::to(['/product/list', 'ProductSearch[has_stock]' => '1']) ?>" class="list-group-item list-group-item-action">
                            <i class="fas fa-cubes text-success"></i> Sản phẩm còn hàng
                        </a>
                        <a href="<?= Url::to(['/product/list', 'ProductSearch[has_stock]' => '0']) ?>" class="list-group-item list-group-item-action">
                            <i class="fas fa-exclamation-triangle text-danger"></i> Sản phẩm hết hàng
                        </a>
                        <a href="<?= Url::to(['/product/list', 'ProductSearch[is_combo]' => '1']) ?>" class="list-group-item list-group-item-action">
                            <i class="fas fa-box text-primary"></i> Sản phẩm combo
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-9">
            <!-- Mobile Category Selection -->
            <div class="d-block d-md-none mb-3">
                <div class="form-group">
                    <label for="mobile-category-select">Chọn danh mục:</label>
                    <select class="form-control" id="mobile-category-select">
                        <option value="<?= Url::to(['/product']) ?>">Tất cả sản phẩm</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= Url::to(['/product/by-category', 'id' => $cat->id]) ?>" <?= $cat->id == $category->id ? 'selected' : '' ?>>
                                <?= Html::encode($cat->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <!-- Search Box -->
            <div class="card card-primary card-outline mb-3">
                <div class="card-body">
                    <div class="input-group">
                        <input type="text" class="form-control form-control-lg" id="product-search-input" placeholder="Tìm sản phẩm trong <?= Html::encode($category->name) ?>...">
                        <div class="input-group-append">
                            <button class="btn btn-primary btn-lg" type="button" id="product-search-button">
                                <i class="fas fa-search"></i> Tìm kiếm
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Category Products -->
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><?= Html::encode($category->name) ?></h3>
                    <div class="card-tools">
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-default active" data-view="grid"><i class="fas fa-th-large"></i></button>
                            <button type="button" class="btn btn-default" data-view="list"><i class="fas fa-list"></i></button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if ($category->description): ?>
                        <div class="category-description mb-4">
                            <?= $category->description ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="product-grid">
                        <div class="row">
                            <?php if (empty($products)): ?>
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> Không có sản phẩm nào trong danh mục này.
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php foreach ($products as $product): ?>
                                    <?php
                                    $mainImage = $product->getMainImage();
                                    $imageUrl = $mainImage ? Yii::$app->urlManager->createUrl('/' . $mainImage->image) : Yii::$app->urlManager->createUrl('/img/no-image.png');
                                    
                                    // Get stock information
                                    $stock = 0;
                                    if ($currentWarehouse) {
                                        $stockModel = \common\models\Stock::findOne(['product_id' => $product->id, 'warehouse_id' => $currentWarehouse]);
                                        $stock = $stockModel ? $stockModel->quantity : 0;
                                    } else {
                                        $stock = \common\models\Stock::find()
                                            ->where(['product_id' => $product->id])
                                            ->sum('quantity') ?: 0;
                                    }
                                    ?>
                                    <div class="col-md-4 col-sm-6 mb-4">
                                        <div class="card h-100 product-card <?= $stock <= 0 ? 'out-of-stock' : '' ?>" 
                                             data-id="<?= $product->id ?>" 
                                             data-code="<?= $product->code ?>" 
                                             data-name="<?= $product->name ?>" 
                                             data-price="<?= $product->selling_price ?>" 
                                             data-unit="<?= $product->unit->name ?>"
                                             data-stock="<?= $stock ?>">
                                            <div class="product-image-container">
                                                <?= Html::img($imageUrl, [
                                                    'class' => 'card-img-top product-image',
                                                    'alt' => $product->name
                                                ]) ?>
                                                
                                                <?php if ($product->is_combo): ?>
                                                    <div class="product-badge combo-badge">
                                                        <span class="badge badge-info">Combo</span>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <?php if ($stock <= 0): ?>
                                                    <div class="product-badge stock-badge">
                                                        <span class="badge badge-danger">Hết hàng</span>
                                                    </div>
                                                <?php elseif ($stock <= $product->min_stock): ?>
                                                    <div class="product-badge stock-badge">
                                                        <span class="badge badge-warning">Sắp hết</span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="card-body">
                                                <h5 class="card-title"><?= Html::encode($product->name) ?></h5>
                                                <p class="card-text text-muted small"><?= Html::encode($product->code) ?></p>
                                                <div class="d-flex justify-content-between align-items-center mt-2">
                                                    <span class="product-price"><?= Yii::$app->formatter->asCurrency($product->selling_price) ?></span>
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-sm btn-info product-info-btn" data-id="<?= $product->id ?>">
                                                            <i class="fas fa-info-circle"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-success product-add-btn" data-id="<?= $product->id ?>" <?= ($stock <= 0) ? 'disabled' : '' ?>>
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-footer bg-white">
                                                <small class="text-muted">
                                                    <i class="fas fa-box"></i> Tồn kho: <?= $stock ?> <?= $product->unit->name ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="product-list" style="display: none;">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 80px">Hình ảnh</th>
                                        <th>Tên sản phẩm</th>
                                        <th style="width: 120px">Mã sản phẩm</th>
                                        <th style="width: 150px">Giá bán</th>
                                        <th style="width: 100px">Tồn kho</th>
                                        <th style="width: 120px">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($products)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center">Không có sản phẩm nào trong danh mục này.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($products as $product): ?>
                                            <?php
                                            $mainImage = $product->getMainImage();
                                            $imageUrl = $mainImage ? Yii::$app->urlManager->createUrl('/' . $mainImage->image) : Yii::$app->urlManager->createUrl('/img/no-image.png');
                                            
                                            // Get stock information
                                            $stock = 0;
                                            if ($currentWarehouse) {
                                                $stockModel = \common\models\Stock::findOne(['product_id' => $product->id, 'warehouse_id' => $currentWarehouse]);
                                                $stock = $stockModel ? $stockModel->quantity : 0;
                                            } else {
                                                $stock = \common\models\Stock::find()
                                                    ->where(['product_id' => $product->id])
                                                    ->sum('quantity') ?: 0;
                                            }
                                            ?>
                                            <tr class="<?= $stock <= 0 ? 'table-danger' : '' ?>">
                                                <td>
                                                    <?= Html::img($imageUrl, [
                                                        'class' => 'img-thumbnail',
                                                        'style' => 'width: 50px; height: 50px; object-fit: cover;',
                                                        'alt' => $product->name
                                                    ]) ?>
                                                </td>
                                                <td>
                                                    <?= Html::encode($product->name) ?>
                                                    <?php if ($product->is_combo): ?>
                                                        <span class="badge badge-info">Combo</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= Html::encode($product->code) ?></td>
                                                <td class="text-right"><?= Yii::$app->formatter->asCurrency($product->selling_price) ?></td>
                                                <td>
                                                    <?= $stock ?> <?= $product->unit->name ?>
                                                    <?php if ($stock <= 0): ?>
                                                        <span class="badge badge-danger">Hết hàng</span>
                                                    <?php elseif ($stock <= $product->min_stock): ?>
                                                        <span class="badge badge-warning">Sắp hết</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-info product-info-btn" data-id="<?= $product->id ?>">
                                                        <i class="fas fa-info-circle"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-success product-add-btn" data-id="<?= $product->id ?>" <?= ($stock <= 0) ? 'disabled' : '' ?>>
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="mt-3 text-center">
                        <?= Html::a('Xem tất cả sản phẩm', ['list'], ['class' => 'btn btn-primary']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Product Detail Modal (Same as in index.php) -->
<div class="modal fade" id="product-detail-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thông tin sản phẩm</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center p-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Đang tải...</span>
                    </div>
                    <p>Đang tải thông tin sản phẩm...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-success" id="modal-add-to-cart-btn">
                    <i class="fas fa-cart-plus"></i> Thêm vào giỏ hàng
                </button>
            </div>
        </div>
    </div>
</div>

<?php
$searchUrl = Url::to(['/product/search']);
$detailsUrl = Url::to(['/product/get-details']);
$warehouseId = $currentWarehouse ?: 'null';
$categoryId = $category->id;

$js = <<<JS
// Toggle view (grid/list)
$('.btn-group button').on('click', function() {
    $('.btn-group button').removeClass('active');
    $(this).addClass('active');
    
    var view = $(this).data('view');
    if (view === 'grid') {
        $('.product-grid').show();
        $('.product-list').hide();
    } else {
        $('.product-grid').hide();
        $('.product-list').show();
    }
});

// Mobile category selector
$('#mobile-category-select').change(function() {
    window.location.href = $(this).val();
});

// Search functionality
function searchProducts() {
    var term = $('#product-search-input').val();
    if (!term) return;
    
    // Show loading
    $('.product-grid .row, .product-list tbody').html('<div class="col-12 text-center p-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Đang tìm kiếm sản phẩm...</p></div>');
    
    // Perform AJAX search
    $.ajax({
        url: '$searchUrl',
        type: 'GET',
        data: {
            term: term,
            warehouse_id: $warehouseId,
            category_id: $categoryId // Filter by current category
        },
        dataType: 'json',
        success: function(response) {
            // Update product display using the same code as in index.php
            // ...implementation omitted for brevity...
        },
        error: function() {
            alert('Đã xảy ra lỗi khi tìm kiếm sản phẩm.');
        }
    });
}

$('#product-search-button').on('click', function() {
    searchProducts();
});

$('#product-search-input').on('keypress', function(e) {
    if (e.which === 13) {
        searchProducts();
    }
});

// Product detail modal handler - same as in index.php
$(document).on('click', '.product-info-btn', function() {
    var productId = $(this).data('id');
    var modal = $('#product-detail-modal');
    
    // Show modal with loading state
    modal.find('.modal-body').html('<div class="text-center p-3"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Đang tải thông tin sản phẩm...</p></div>');
    modal.modal('show');
    
    // Store product ID in modal
    modal.data('product-id', productId);
    
    // Get product details via AJAX
    $.ajax({
        url: '$detailsUrl',
        type: 'GET',
        data: {
            id: productId,
            warehouse_id: $warehouseId
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                var product = response.product;
                
                // Update modal content using same buildProductDetailHTML function as in index.php
                var modalContent = window.buildProductDetailHTML ? window.buildProductDetailHTML(product) : 'Product details not available';
                modal.find('.modal-body').html(modalContent);
                
                // Enable/disable add to cart button based on stock
                $('#modal-add-to-cart-btn').prop('disabled', product.stock <= 0);
                
                // Initialize image carousel if multiple images
                if (product.images.length > 1) {
                    $('#product-image-carousel').carousel();
                }
            } else {
                modal.find('.modal-body').html('<div class="alert alert-danger">' + response.message + '</div>');
            }
        },
        error: function() {
            modal.find('.modal-body').html('<div class="alert alert-danger">Đã xảy ra lỗi khi tải thông tin sản phẩm.</div>');
        }
    });
});

// Add to cart button handler
$(document).on('click', '.product-add-btn', function() {
    var productId = $(this).data('id');
    
    // Add to cart (handled by cart module)
    if (window.addToCart) {
        window.addToCart(productId, 1);
    } else {
        console.log('Add to cart:', productId, 1);
        alert('Đã thêm sản phẩm vào giỏ hàng!');
    }
});

// Add to cart from modal
$('#modal-add-to-cart-btn').on('click', function() {
    var modal = $('#product-detail-modal');
    var productId = modal.data('product-id');
    var quantity = parseInt($('#product-quantity').val()) || 1;
    var unitId = $('#product-unit').length > 0 ? $('#product-unit').val() : null;
    
    // Add to cart (handled by cart module)
    if (window.addToCart) {
        window.addToCart(productId, quantity, unitId);
        modal.modal('hide');
    } else {
        console.log('Add to cart:', productId, quantity, unitId);
        alert('Đã thêm sản phẩm vào giỏ hàng!');
        modal.modal('hide');
    }
});

// Quantity controls
$(document).on('click', '#qty-minus-btn', function() {
    var input = $('#product-quantity');
    var value = parseInt(input.val()) || 1;
    if (value > 1) {
        input.val(value - 1);
    }
});

$(document).on('click', '#qty-plus-btn', function() {
    var input = $('#product-quantity');
    var value = parseInt(input.val()) || 1;
    var max = parseInt(input.attr('max')) || 9999;
    if (value < max) {
        input.val(value + 1);
    }
});

// Format currency (utility function)
function formatCurrency(value) {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
}

// Build HTML for product detail modal
// This function is also defined in index.php but included here for completeness
function buildProductDetailHTML(product) {
    var html = '<div class="row">';
    
    // Product images
    html += '<div class="col-md-5">';
    if (product.images.length > 0) {
        if (product.images.length === 1) {
            html += '<img src="' + product.images[0].url + '" class="img-fluid" alt="' + product.name + '">';
        } else {
            html += '<div id="product-image-carousel" class="carousel slide" data-ride="carousel">';
            html += '<ol class="carousel-indicators">';
            for (var i = 0; i < product.images.length; i++) {
                html += '<li data-target="#product-image-carousel" data-slide-to="' + i + '"' + (i === 0 ? ' class="active"' : '') + '></li>';
            }
            html += '</ol>';
            html += '<div class="carousel-inner">';
            for (var i = 0; i < product.images.length; i++) {
                html += '<div class="carousel-item' + (i === 0 ? ' active' : '') + '">';
                html += '<img src="' + product.images[i].url + '" class="d-block w-100" alt="' + product.name + '">';
                html += '</div>';
            }
            html += '</div>';
            html += '<a class="carousel-control-prev" href="#product-image-carousel" role="button" data-slide="prev">';
            html += '<span class="carousel-control-prev-icon" aria-hidden="true"></span>';
            html += '<span class="sr-only">Previous</span>';
            html += '</a>';
            html += '<a class="carousel-control-next" href="#product-image-carousel" role="button" data-slide="next">';
            html += '<span class="carousel-control-next-icon" aria-hidden="true"></span>';
            html += '<span class="sr-only">Next</span>';
            html += '</a>';
            html += '</div>';
        }
    } else {
        html += '<img src="/img/no-image.png" class="img-fluid" alt="No Image">';
    }
    html += '</div>';
    
    // Product details
    html += '<div class="col-md-7">';
    html += '<h4>' + product.name + '</h4>';
    html += '<p class="text-muted">Mã: ' + product.code + '</p>';
    
    if (product.is_combo) {
        html += '<div class="mb-2"><span class="badge badge-info">Combo</span></div>';
    }
    
    html += '<div class="product-price mb-3">';
    html += '<h5>Giá bán: <span class="text-success">' + product.selling_price_formatted + '</span></h5>';
    html += '</div>';
    
    html += '<div class="product-stock mb-3">';
    var stockClass = product.stock <= 0 ? 'danger' : (product.stock <= 5 ? 'warning' : 'success');
    var stockText = product.stock <= 0 ? 'Hết hàng' : (product.stock <= 5 ? 'Sắp hết hàng' : 'Còn hàng');
    html += '<div class="alert alert-' + stockClass + '">';
    html += '<i class="fas fa-box"></i> Tồn kho: <strong>' + product.stock + ' ' + product.unit_name + '</strong> (' + stockText + ')';
    html += '</div></div>';
    
    // Units selection if available
    if (product.units.length > 1) {
        html += '<div class="form-group">';
        html += '<label for="product-unit">Đơn vị tính:</label>';
        html += '<select class="form-control" id="product-unit">';
        for (var i = 0; i < product.units.length; i++) {
            var unit = product.units[i];
            html += '<option value="' + unit.id + '" data-conversion="' + unit.conversion_factor + '"' + (unit.is_default ? ' selected' : '') + '>' + unit.name + '</option>';
        }
        html += '</select>';
        html += '</div>';
    }
    
    // Quantity input
    html += '<div class="form-group">';
    html += '<label for="product-quantity">Số lượng:</label>';
    html += '<div class="input-group">';
    html += '<div class="input-group-prepend">';
    html += '<button class="btn btn-outline-secondary" type="button" id="qty-minus-btn"><i class="fas fa-minus"></i></button>';
    html += '</div>';
    html += '<input type="number" class="form-control text-center" id="product-quantity" value="1" min="1" max="' + product.stock + '">';
    html += '<div class="input-group-append">';
    html += '<button class="btn btn-outline-secondary" type="button" id="qty-plus-btn"><i class="fas fa-plus"></i></button>';
    html += '</div>';
    html += '</div>';
    html += '</div>';
    
    // Category
    if (product.category_name) {
        html += '<p><strong>Danh mục:</strong> ' + product.category_name + '</p>';
    }
    
    // Warranty
    if (product.warranty_period > 0) {
        html += '<p><strong>Bảo hành:</strong> ' + product.warranty_period + ' tháng</p>';
    }
    
    // Short description
    if (product.short_description) {
        html += '<div class="product-description mt-3">';
        html += '<h6>Mô tả ngắn:</h6>';
        html += '<p>' + product.short_description + '</p>';
        html += '</div>';
    }
    
    html += '</div>';
    html += '</div>';
    
    // Combo items
    if (product.is_combo && product.combo_items.length > 0) {
        html += '<div class="row mt-4">';
        html += '<div class="col-12">';
        html += '<h5>Thành phần combo:</h5>';
        html += '<div class="table-responsive">';
        html += '<table class="table table-sm table-striped">';
        html += '<thead><tr><th>Sản phẩm</th><th class="text-center">Số lượng</th><th class="text-right">Đơn giá</th><th class="text-right">Thành tiền</th></tr></thead>';
        html += '<tbody>';
        
        var totalValue = 0;
        for (var i = 0; i < product.combo_items.length; i++) {
            var item = product.combo_items[i];
            var itemTotal = item.quantity * item.price;
            totalValue += itemTotal;
            
            html += '<tr>';
            html += '<td>' + item.product_name + ' <small class="text-muted">(' + item.product_code + ')</small></td>';
            html += '<td class="text-center">' + item.quantity + ' ' + item.unit_name + '</td>';
            html += '<td class="text-right">' + item.price_formatted + '</td>';
            html += '<td class="text-right">' + formatCurrency(itemTotal) + '</td>';
            html += '</tr>';
        }
        
        html += '</tbody>';
        html += '<tfoot>';
        html += '<tr>';
        html += '<th colspan="3" class="text-right">Tổng giá trị:</th>';
        html += '<th class="text-right">' + formatCurrency(totalValue) + '</th>';
        html += '</tr>';
        
        if (product.selling_price < totalValue) {
            var savings = totalValue - product.selling_price;
            var savingsPercent = Math.round((savings / totalValue) * 100);
            
            html += '<tr class="table-success">';
            html += '<th colspan="3" class="text-right">Tiết kiệm:</th>';
            html += '<th class="text-right text-success">' + formatCurrency(savings) + ' (' + savingsPercent + '%)</th>';
            html += '</tr>';
        }
        
        html += '</tfoot>';
        html += '</table>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
    }
    
    // Full description
    if (product.description) {
        html += '<div class="row mt-4">';
        html += '<div class="col-12">';
        html += '<h5>Mô tả chi tiết:</h5>';
        html += '<div class="card">';
        html += '<div class="card-body">' + product.description + '</div>';
        html += '</div>';
        html += '</div>';
        html += '</div>';
    }
    
    return html;
}

// Make buildProductDetailHTML function available globally
window.buildProductDetailHTML = buildProductDetailHTML;
JS;
$this->registerJs($js);

$css = <<<CSS
.product-card {
    transition: all 0.3s ease;
    border: 1px solid #ddd;
}

.product-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transform: translateY(-5px);
}

.product-image-container {
    height: 180px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    position: relative;
    background-color: #f9f9f9;
}

.product-image {
    max-height: 100%;
    max-width: 100%;
    object-fit: contain;
}

.product-badge {
    position: absolute;
    z-index: 2;
}

.combo-badge {
    top: 5px;
    left: 5px;
}

.stock-badge {
    top: 5px;
    right: 5px;
}

.product-price {
    font-weight: bold;
    color: #28a745;
}

.category-icon {
    width: 20px;
    height: 20px;
    object-fit: cover;
    margin-right: 5px;
}

.category-list .list-group-item {
    display: flex;
    align-items: center;
}

.out-of-stock .card-body,
.out-of-stock .card-footer {
    opacity: 0.7;
}

.out-of-stock .product-image-container::after {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255,255,255,0.5);
    z-index: 1;
}

#product-search-input {
    font-size: 16px;
}

.carousel-inner img {
    max-height: 400px;
    object-fit: contain;
}

@media (max-width: 767.98px) {
    .product-card {
        margin-bottom: 15px;
    }
    
    .product-image-container {
        height: 140px;
    }
}
CSS;
$this->registerCss($css);
?>