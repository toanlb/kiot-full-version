<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use kartik\select2\Select2;
use kartik\date\DatePicker;
use wbraganca\dynamicform\DynamicFormWidget;

$this->title = 'Tạo phiếu nhập kho';
$this->params['breadcrumbs'][] = ['label' => 'Phiếu nhập kho', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$js = '
var products = ' . json_encode($products) . ';
var units = ' . json_encode($units) . ';

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
    
    $(".dynamicform_wrapper .row").find("input, select").trigger("change");
    recalculateTotal();
});

$(".dynamicform_wrapper").on("afterDelete", function(e) {
    recalculateTotal();
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

// Sự kiện khi nhập số lượng, giá, chiết khấu, thuế
$(document).on("change keyup", ".quantity, .unit-price, .discount-percent, .tax-percent", function() {
    var row = $(this).closest(".row");
    calculateRowTotal(row);
    recalculateTotal();
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
                row.find(".unit-price").val(product.cost_price);
                row.find(".unit-select").val(product.unit_id).trigger("change");
                calculateRowTotal(row);
                recalculateTotal();
            }
        }
    });
}

// Hàm tính tổng tiền cho từng dòng
function calculateRowTotal(row) {
    var quantity = parseFloat(row.find(".quantity").val()) || 0;
    var unitPrice = parseFloat(row.find(".unit-price").val()) || 0;
    var discountPercent = parseFloat(row.find(".discount-percent").val()) || 0;
    var taxPercent = parseFloat(row.find(".tax-percent").val()) || 0;
    
    var subtotal = quantity * unitPrice;
    var discountAmount = subtotal * (discountPercent / 100);
    var taxAmount = (subtotal - discountAmount) * (taxPercent / 100);
    var total = subtotal - discountAmount + taxAmount;
    
    row.find(".discount-amount").val(discountAmount.toFixed(2));
    row.find(".tax-amount").val(taxAmount.toFixed(2));
    row.find(".total-price").val(total.toFixed(2));
}

// Hàm tính tổng tiền cho cả phiếu
function recalculateTotal() {
    var totalAmount = 0;
    var totalDiscount = 0;
    var totalTax = 0;
    
    $(".dynamicform_wrapper .row").each(function() {
        var quantity = parseFloat($(this).find(".quantity").val()) || 0;
        var unitPrice = parseFloat($(this).find(".unit-price").val()) || 0;
        var discountAmount = parseFloat($(this).find(".discount-amount").val()) || 0;
        var taxAmount = parseFloat($(this).find(".tax-amount").val()) || 0;
        
        totalAmount += quantity * unitPrice;
        totalDiscount += discountAmount;
        totalTax += taxAmount;
    });
    
    var finalAmount = totalAmount - totalDiscount + totalTax;
    
    $("#total-amount").text(totalAmount.toFixed(2));
    $("#total-discount").text(totalDiscount.toFixed(2));
    $("#total-tax").text(totalTax.toFixed(2));
    $("#final-amount").text(finalAmount.toFixed(2));
}
';

$this->registerJs($js);
?>

<div class="stock-in-create">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
        </div>
        
        <?php $form = ActiveForm::begin(['id' => 'stock-in-form']); ?>
        
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'code')->textInput(['maxlength' => true, 'readonly' => true]) ?>
                    
                    <?= $form->field($model, 'warehouse_id')->widget(Select2::classname(), [
                        'data' => $warehouses,
                        'options' => ['placeholder' => 'Chọn kho hàng...'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ]) ?>
                    
                    <?= $form->field($model, 'supplier_id')->widget(Select2::classname(), [
                        'data' => $suppliers,
                        'options' => ['placeholder' => 'Chọn nhà cung cấp...'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ]) ?>
                </div>
                
                <div class="col-md-6">
                    <?= $form->field($model, 'stock_in_date')->widget(DatePicker::classname(), [
                        'options' => ['placeholder' => 'Chọn ngày nhập kho...'],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => 'yyyy-mm-dd'
                        ]
                    ]) ?>
                    
                    <?= $form->field($model, 'reference_number')->textInput(['maxlength' => true]) ?>
                    
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
                'model' => new \common\models\StockInDetail(),
                'formId' => 'stock-in-form',
                'formFields' => [
                    'product_id',
                    'batch_number',
                    'expiry_date',
                    'quantity',
                    'unit_id',
                    'unit_price',
                    'discount_percent',
                    'discount_amount',
                    'tax_percent',
                    'tax_amount',
                    'total_price',
                ],
            ]); ?>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Chi tiết nhập kho</h3>
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
                                <div class="col-md-3">
                                    <?= Html::dropDownList(
                                        'StockInDetail[0][product_id]',
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
                                    <?= Html::textInput('StockInDetail[0][batch_number]', null, [
                                        'class' => 'form-control',
                                        'placeholder' => 'Số lô'
                                    ]) ?>
                                </div>
                                <div class="col-md-2">
                                    <?= DatePicker::widget([
                                        'name' => 'StockInDetail[0][expiry_date]',
                                        'options' => ['placeholder' => 'Hạn sử dụng'],
                                        'pluginOptions' => [
                                            'autoclose' => true,
                                            'format' => 'yyyy-mm-dd'
                                        ]
                                    ]) ?>
                                </div>
                                <div class="col-md-1">
                                    <?= Html::textInput('StockInDetail[0][quantity]', null, [
                                        'class' => 'form-control quantity',
                                        'placeholder' => 'SL',
                                        'type' => 'number',
                                        'min' => '1',
                                        'required' => true
                                    ]) ?>
                                </div>
                                <div class="col-md-1">
                                    <?= Html::dropDownList(
                                        'StockInDetail[0][unit_id]',
                                        null,
                                        $units,
                                        [
                                            'class' => 'form-control unit-select',
                                            'prompt' => 'ĐVT',
                                            'required' => true
                                        ]
                                    ) ?>
                                </div>
                                <div class="col-md-3">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <?= Html::textInput('StockInDetail[0][unit_price]', null, [
                                                'class' => 'form-control unit-price',
                                                'placeholder' => 'Đơn giá',
                                                'required' => true
                                            ]) ?>
                                        </div>
                                        <div class="col-md-2">
                                            <?= Html::textInput('StockInDetail[0][discount_percent]', '0', [
                                                'class' => 'form-control discount-percent',
                                                'placeholder' => 'CK%'
                                            ]) ?>
                                        </div>
                                        <div class="col-md-2">
                                            <?= Html::textInput('StockInDetail[0][discount_amount]', '0', [
                                                'class' => 'form-control discount-amount',
                                                'placeholder' => 'CK',
                                                'readonly' => true
                                            ]) ?>
                                        </div>
                                        <div class="col-md-2">
                                            <?= Html::textInput('StockInDetail[0][tax_percent]', '0', [
                                                'class' => 'form-control tax-percent',
                                                'placeholder' => 'VAT%'
                                            ]) ?>
                                        </div>
                                        <div class="col-md-2">
                                            <?= Html::textInput('StockInDetail[0][tax_amount]', '0', [
                                                'class' => 'form-control tax-amount',
                                                'placeholder' => 'VAT',
                                                'readonly' => true
                                            ]) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-2">
                                <div class="col-md-11">
                                    <?= Html::textarea('StockInDetail[0][note]', null, [
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
                    <div class="row">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-success btn-sm add-item">
                                <i class="fas fa-plus"></i> Thêm sản phẩm
                            </button>
                        </div>
                        <div class="col-md-6">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <th>Tổng tiền hàng:</th>
                                        <td class="text-right"><span id="total-amount">0.00</span></td>
                                    </tr>
                                    <tr>
                                        <th>Tổng chiết khấu:</th>
                                        <td class="text-right"><span id="total-discount">0.00</span></td>
                                    </tr>
                                    <tr>
                                        <th>Tổng VAT:</th>
                                        <td class="text-right"><span id="total-tax">0.00</span></td>
                                    </tr>
                                    <tr>
                                        <th>Tổng cộng:</th>
                                        <td class="text-right"><strong><span id="final-amount">0.00</span></strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
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