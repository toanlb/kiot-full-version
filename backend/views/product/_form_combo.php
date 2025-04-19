<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model common\models\Product */
/* @var $form yii\widgets\ActiveForm */
/* @var $comboItems array */
/* @var $units array */
?>

<div class="product-form-combo">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Thành phần combo sản phẩm</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Nếu sản phẩm này là combo, hãy thêm các sản phẩm con vào combo.
                        Giá bán combo sẽ được tính tự động dựa trên tổng giá trị các sản phẩm con.
                    </div>

                    <div class="mb-3">
                        <button type="button" class="btn btn-success" id="add-combo-item-btn">
                            <i class="fas fa-plus"></i> Thêm sản phẩm vào combo
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="combo-items-table">
                            <thead>
                                <tr>
                                    <th style="width: 40%">Sản phẩm</th>
                                    <th style="width: 20%">Số lượng</th>
                                    <th style="width: 20%">Đơn vị tính</th>
                                    <th style="width: 10%">Đơn giá</th>
                                    <th style="width: 10%">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($comboItems)): ?>
                                <tr class="empty-row">
                                    <td colspan="5" class="text-center">Chưa có sản phẩm nào trong combo</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($comboItems as $index => $item): ?>
                                <tr class="combo-item-row">
                                    <td>
                                        <input type="hidden" name="ComboItems[<?= $index ?>][product_id]" value="<?= $item['product_id'] ?>" class="product-id">
                                        <div class="product-info">
                                            <div class="product-name"><?= Html::encode($item['product_name']) ?></div>
                                            <div class="product-code text-muted"><?= Html::encode($item['product_code']) ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" name="ComboItems[<?= $index ?>][quantity]" value="<?= $item['quantity'] ?>" min="1" class="form-control quantity-input">
                                    </td>
                                    <td>
                                        <input type="hidden" name="ComboItems[<?= $index ?>][unit_id]" value="<?= $item['unit_id'] ?>" class="unit-id">
                                        <span class="unit-name"><?= Html::encode($item['unit_name']) ?></span>
                                    </td>
                                    <td class="text-right">
                                        <span class="item-price"><?= Yii::$app->formatter->asCurrency($item['product']->selling_price) ?></span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm remove-combo-item">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-right">Tổng giá trị:</th>
                                    <th class="text-right" id="total-combo-price">0 đ</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Chọn Sản Phẩm -->
<div class="modal fade" id="select-product-modal" tabindex="-1" role="dialog" aria-labelledby="select-product-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="select-product-modal-label">Chọn sản phẩm cho combo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <input type="text" class="form-control" id="search-product-input" placeholder="Tìm kiếm sản phẩm...">
                </div>
                <div class="table-responsive mt-3">
                    <table class="table table-bordered table-striped" id="product-list-table">
                        <thead>
                            <tr>
                                <th style="width: 10%">Mã</th>
                                <th style="width: 50%">Tên sản phẩm</th>
                                <th style="width: 15%">Giá bán</th>
                                <th style="width: 15%">Đơn vị</th>
                                <th style="width: 10%">Chọn</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Danh sách sản phẩm sẽ được tải dynamically -->
                            <tr>
                                <td colspan="5" class="text-center">Đang tải dữ liệu...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<?php
$productListUrl = Url::to(['product/index', 'ajax' => 1, 'exclude' => $model->id]);
$js = <<<JS
// Counter for combo items
var comboItemIndex = {$model->isNewRecord ? 0 : count($comboItems)};

// Load products for the modal
function loadProducts() {
    var searchTerm = $('#search-product-input').val();
    
    $.ajax({
        url: '$productListUrl',
        type: 'GET',
        data: {search: searchTerm},
        dataType: 'json',
        success: function(response) {
            var html = '';
            
            if (response.data.length === 0) {
                html = '<tr><td colspan="5" class="text-center">Không tìm thấy sản phẩm</td></tr>';
            } else {
                $.each(response.data, function(index, product) {
                    html += '<tr>';
                    html += '<td>' + product.code + '</td>';
                    html += '<td>' + product.name + '</td>';
                    html += '<td class="text-right">' + product.selling_price_formatted + '</td>';
                    html += '<td>' + product.unit_name + '</td>';
                    html += '<td><button type="button" class="btn btn-sm btn-primary select-product-btn" ' +
                           'data-id="' + product.id + '" ' +
                           'data-name="' + product.name + '" ' +
                           'data-code="' + product.code + '" ' +
                           'data-price="' + product.selling_price + '" ' +
                           'data-unit-id="' + product.unit_id + '" ' +
                           'data-unit-name="' + product.unit_name + '"><i class="fas fa-plus"></i></button></td>';
                    html += '</tr>';
                });
            }
            
            $('#product-list-table tbody').html(html);
        },
        error: function() {
            alert('Đã xảy ra lỗi khi tải danh sách sản phẩm.');
        }
    });
}

// Open modal to add combo item
$('#add-combo-item-btn').click(function() {
    $('#search-product-input').val('');
    loadProducts();
    $('#select-product-modal').modal('show');
});

// Search products in modal
$('#search-product-input').on('input', function() {
    loadProducts();
});

// Select product from modal
$(document).on('click', '.select-product-btn', function() {
    var productId = $(this).data('id');
    var productName = $(this).data('name');
    var productCode = $(this).data('code');
    var productPrice = $(this).data('price');
    var unitId = $(this).data('unit-id');
    var unitName = $(this).data('unit-name');
    
    // Check if product already exists in combo
    var exists = false;
    $('.product-id').each(function() {
        if ($(this).val() == productId) {
            exists = true;
            return false;
        }
    });
    
    if (exists) {
        alert('Sản phẩm này đã có trong combo!');
        return;
    }
    
    // Add product to combo table
    addComboItem(productId, productName, productCode, productPrice, unitId, unitName);
    
    // Close modal
    $('#select-product-modal').modal('hide');
});

// Add combo item to table
function addComboItem(productId, productName, productCode, productPrice, unitId, unitName) {
    // Remove empty row if exists
    $('.empty-row').remove();
    
    var html = '<tr class="combo-item-row">';
    html += '<td>';
    html += '<input type="hidden" name="ComboItems[' + comboItemIndex + '][product_id]" value="' + productId + '" class="product-id">';
    html += '<div class="product-info">';
    html += '<div class="product-name">' + productName + '</div>';
    html += '<div class="product-code text-muted">' + productCode + '</div>';
    html += '</div>';
    html += '</td>';
    html += '<td>';
    html += '<input type="number" name="ComboItems[' + comboItemIndex + '][quantity]" value="1" min="1" class="form-control quantity-input">';
    html += '</td>';
    html += '<td>';
    html += '<input type="hidden" name="ComboItems[' + comboItemIndex + '][unit_id]" value="' + unitId + '" class="unit-id">';
    html += '<span class="unit-name">' + unitName + '</span>';
    html += '</td>';
    html += '<td class="text-right">';
    html += '<span class="item-price">' + formatCurrency(productPrice) + '</span>';
    html += '<input type="hidden" class="item-price-value" value="' + productPrice + '">';
    html += '</td>';
    html += '<td>';
    html += '<button type="button" class="btn btn-danger btn-sm remove-combo-item"><i class="fas fa-trash"></i></button>';
    html += '</td>';
    html += '</tr>';
    
    $('#combo-items-table tbody').append(html);
    comboItemIndex++;
    
    // Update total price
    updateTotalPrice();
}

// Remove combo item
$(document).on('click', '.remove-combo-item', function() {
    $(this).closest('tr').remove();
    
    // Add empty row if no items left
    if ($('.combo-item-row').length === 0) {
        var emptyHtml = '<tr class="empty-row">';
        emptyHtml += '<td colspan="5" class="text-center">Chưa có sản phẩm nào trong combo</td>';
        emptyHtml += '</tr>';
        $('#combo-items-table tbody').html(emptyHtml);
    }
    
    // Update total price
    updateTotalPrice();
});

// Update quantity
$(document).on('change', '.quantity-input', function() {
    updateTotalPrice();
});

// Update total price
function updateTotalPrice() {
    var total = 0;
    
    $('.combo-item-row').each(function() {
        var price = parseFloat($(this).find('.item-price-value').val() || 0);
        var quantity = parseInt($(this).find('.quantity-input').val() || 0);
        total += price * quantity;
    });
    
    $('#total-combo-price').text(formatCurrency(total));
    
    // Also update the main selling price field if empty or auto-update is enabled
    if ($('#product-selling_price').val() === '' || $('#auto-update-price').is(':checked')) {
        $('#product-selling_price').val(total);
    }
}

// Format currency
function formatCurrency(value) {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
}

// Initialize on load
$(document).ready(function() {
    updateTotalPrice();
});
JS;
$this->registerJs($js);
?>