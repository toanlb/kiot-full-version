<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use kartik\select2\Select2;
use kartik\date\DatePicker;
use wbraganca\dynamicform\DynamicFormWidget;

$this->title = 'Tạo phiếu chuyển kho';
$this->params['breadcrumbs'][] = ['label' => 'Phiếu chuyển kho', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$js = '
var products = ' . json_encode($products) . ';
var units = ' . json_encode($units) . ';

// Ngăn không cho chọn cùng kho nguồn và đích
$("#stocktransfer-source_warehouse_id, #stocktransfer-destination_warehouse_id").on("change", function() {
    var sourceId = $("#stocktransfer-source_warehouse_id").val();
    var destId = $("#stocktransfer-destination_warehouse_id").val();
    
    if (sourceId && destId && sourceId === destId) {
        alert("Kho nguồn và kho đích không thể giống nhau!");
        $(this).val("").trigger("change");
    }
});

$(".dynamicform_wrapper").on("afterInsert", function(e, item) {
    $(".product-select").select2({
        theme: "krajee",
        placeholder: "Chọn sản phẩm...",
        allowClear: true
    });
    
    $(".unit-select").select2({
        theme: "krajee",
        placeholder: "Đơn vị tính...",
        allowClear: true
    });
    
    $(".dynamicform_wrapper .row").last().find(".product-select").on("change", function() {
        var productId = $(this).val();
        var row = $(this).closest(".row");
        getProductInfo(productId, row);
    });
});

$(".dynamicform_wrapper").on("beforeDelete", function(e, item) {
    if (! confirm("Bạn có chắc chắn muốn xóa sản phẩm này?")) {
        return false;
    }
    return true;
});

// Sự kiện khi thay đổi sản phẩm
$(".product-select").on("change", function() {
    var productId = $(this).val();
    var row = $(this).closest(".row");
    getProductInfo(productId, row);
});

// Sự kiện khi thay đổi kho nguồn
$("#stocktransfer-source_warehouse_id").on("change", function() {
    var warehouseId = $(this).val();
    $(".product-select").each(function() {
        var productId = $(this).val();
        if (productId) {
            var row = $(this).closest(".row");
            getProductStock(productId, warehouseId, row);
        }
    });
});

// Hàm lấy thông tin sản phẩm
function getProductInfo(productId, row) {
    if (!productId) return;
    
    $.ajax({
        url: "' . Url::to(['get-product']) . '",
        type: "GET",
        data: {id: productId},
        success: function(data) {
            var product = JSON.parse(data);
            if (product) {
                row.find(".unit-select").val(product.unit_id).trigger("change");
                
                // Kiểm tra tồn kho nếu đã chọn kho nguồn
                var warehouseId = $("#stocktransfer-source_warehouse_id").val();
                if (warehouseId) {
                    getProductStock(productId, warehouseId, row);
                }
            }
        }
    });
}

// Hàm lấy thông tin tồn kho của sản phẩm tại kho nguồn
function getProductStock(productId, warehouseId, row) {
    if (!productId || !warehouseId) return;
    
    $.ajax({
        url: "' . Url::to(['get-product-stock']) . '",
        type: "GET",
        data: {product_id: productId, warehouse_id: warehouseId},
        success: function(data) {
            var stock = JSON.parse(data);
            if (stock) {
                var infoSpan = row.find(".stock-info");
                if (!infoSpan.length) {
                    infoSpan = $("<span class=\"stock-info text-muted ml-2\"></span>");
                    row.find(".product-select").parent().append(infoSpan);
                }
                
                infoSpan.html("Tồn kho: " + stock.quantity + " " + stock.unit_name);
                
                // Cập nhật max quantity input
                var qtyInput = row.find(".quantity");
                qtyInput.attr("max", stock.quantity);
                qtyInput.attr("data-max", stock.quantity);
                
                // Kiểm tra nếu số lượng hiện tại vượt quá tồn kho
                var currentQty = parseInt(qtyInput.val()) || 0;
                if (currentQty > stock.quantity) {
                    qtyInput.val(stock.quantity);
                    alert("Số lượng đã được điều chỉnh theo tồn kho hiện có!");
                }
            } else {
                var infoSpan = row.find(".stock-info");
                if (!infoSpan.length) {
                    infoSpan = $("<span class=\"stock-info text-danger ml-2\"></span>");
                    row.find(".product-select").parent().append(infoSpan);
                }
                
                infoSpan.html("Không có tồn kho!");
                
                // Reset quantity input
                var qtyInput = row.find(".quantity");
                qtyInput.val(0);
                qtyInput.attr("max", 0);
                qtyInput.attr("data-max", 0);
            }
        }
    });
}

// Kiểm tra số lượng nhập không vượt quá tồn kho
$(document).on("change keyup", ".quantity", function() {
    var max = parseInt($(this).attr("data-max")) || 0;
    var val = parseInt($(this).val()) || 0;
    
    if (val > max) {
        $(this).val(max);
        alert("Số lượng không thể vượt quá tồn kho!");
    }
});
';

$this->registerJs($js);
?>

<div class="stock-transfer-create">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
        </div>
        
        <?php $form = ActiveForm::begin(['id' => 'stock-transfer-form']); ?>
        
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'code')->textInput(['maxlength' => true, 'readonly' => true]) ?>
                    
                    <?= $form->field($model, 'source_warehouse_id')->widget(Select2::classname(), [
                        'data' => $warehouses,
                        'options' => ['placeholder' => 'Chọn kho nguồn...'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ]) ?>
                    
                    <?= $form->field($model, 'destination_warehouse_id')->widget(Select2::classname(), [
                        'data' => $warehouses,
                        'options' => ['placeholder' => 'Chọn kho đích...'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ]) ?>
                </div>
                
                <div class="col-md-6">
                    <?= $form->field($model, 'transfer_date')->widget(DatePicker::classname(), [
                        'options' => ['placeholder' => 'Chọn ngày chuyển kho...'],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd'
                        ]
                    ]) ?>
                    
                    <?= $form->field($model, 'note')->textarea(['rows' => 3]) ?>
                </div>
            </div>
            
            <hr>
            
            <?php DynamicFormWidget::begin([
                'widgetContainer' => 'dynamicform_wrapper',
                'widgetBody' => '.container-items',
                'widgetItem' => '.item',
                'limit' => 50,
                'min' => 1,
                'insertButton' => '.add-item',
                'deleteButton' => '.remove-item',
                'model' => new \common\models\StockTransferDetail(),
                'formId' => 'stock-transfer-form',
                'formFields' => [
                    'product_id',
                    'batch_number',
                    'quantity',
                    'unit_id',
                    'note',
                ],
            ]); ?>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Chi tiết chuyển kho</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success btn-sm add-item">
                            <i class="fas fa-plus"></i> Thêm sản phẩm
                        </button>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="container-items">
                        <div class="item">
                            <div class="row">
                                <div class="col-md-6">
                                    <?= Html::dropDownList(
                                        'StockTransferDetail[0][product_id]',
                                        null,
                                        $products,
                                        [
                                            'class' => 'form-control product-select',
                                            'prompt' => 'Chọn sản phẩm...',
                                            'required' => true
                                        ]
                                    ) ?>
                                </div>
                                <div class="col-md-2">
                                    <?= Html::textInput('StockTransferDetail[0][batch_number]', null, [
                                        'class' => 'form-control',
                                        'placeholder' => 'Số lô (nếu có)'
                                    ]) ?>
                                </div>
                                <div class="col-md-2">
                                    <?= Html::textInput('StockTransferDetail[0][quantity]', null, [
                                        'class' => 'form-control quantity',
                                        'placeholder' => 'Số lượng',
                                        'type' => 'number',
                                        'min' => '1',
                                        'required' => true
                                    ]) ?>
                                </div>
                                <div class="col-md-2">
                                    <?= Html::dropDownList(
                                        'StockTransferDetail[0][unit_id]',
                                        null,
                                        $units,
                                        [
                                            'class' => 'form-control unit-select',
                                            'prompt' => 'Đơn vị tính',
                                            'required' => true
                                        ]
                                    ) ?>
                                </div>
                            </div>
                            
                            <div class="row mt-2">
                                <div class="col-md-11">
                                    <?= Html::textarea('StockTransferDetail[0][note]', null, [
                                        'class' => 'form-control',
                                        'placeholder' => 'Ghi chú',
                                        'rows' => 1
                                    ]) ?>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-danger btn-sm remove-item">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <hr>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="button" class="btn btn-success btn-sm add-item">
                        <i class="fas fa-plus"></i> Thêm sản phẩm
                    </button>
                </div>
            </div>
            
            <?php DynamicFormWidget::end(); ?>
        </div>
        
        <div class="card-footer">
            <?= Html::submitButton('<i class="fas fa-save"></i> Lưu', ['class' => 'btn btn-success']) ?>
            <?= Html::a('<i class="fas fa-times"></i> Hủy', ['index'], ['class' => 'btn btn-default']) ?>
        </div>
        
        <?php ActiveForm::end(); ?>
    </div>
</div>