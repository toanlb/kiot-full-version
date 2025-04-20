<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\jui\DatePicker;
use yii\web\JsExpression;
use yii\web\View;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\StockIn */
/* @var $form yii\widgets\ActiveForm */
/* @var $warehouses array */
/* @var $suppliers array */
/* @var $products array */
/* @var $units array */
/* @var $details common\models\StockInDetail[] */

$details = $details ?? [];
// Đăng ký các file CSS và JS từ CDN


?>

<div class="stock-in-form">
    <?php $form = ActiveForm::begin(['id' => 'stock-in-form']); ?>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Thông tin phiếu nhập</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'code')->textInput(['maxlength' => true, 'readonly' => true]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'stock_in_date')->widget(DatePicker::class, [
                                'dateFormat' => 'php:Y-m-d',
                                'options' => ['class' => 'form-control'],
                                'clientOptions' => [
                                    'changeMonth' => true,
                                    'changeYear' => true,
                                ],
                            ]) ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'warehouse_id')->dropDownList($warehouses, ['prompt' => 'Chọn kho hàng']) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'supplier_id')->dropDownList($suppliers, ['prompt' => 'Chọn nhà cung cấp']) ?>
                        </div>
                    </div>
                    
                    <?= $form->field($model, 'reference_number')->textInput(['maxlength' => true]) ?>
                    
                    <?= $form->field($model, 'note')->textarea(['rows' => 3]) ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Thông tin thanh toán</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'total_amount')->textInput([
                                'readonly' => true, 
                                'class' => 'form-control text-right'
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'discount_amount')->textInput([
                                'readonly' => true, 
                                'class' => 'form-control text-right'
                            ]) ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'tax_amount')->textInput([
                                'readonly' => true, 
                                'class' => 'form-control text-right'
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'final_amount')->textInput([
                                'readonly' => true, 
                                'class' => 'form-control text-right font-weight-bold'
                            ]) ?>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'paid_amount')->textInput([
                                'class' => 'form-control text-right',
                                'value' => $model->isNewRecord ? 0 : $model->paid_amount
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Số tiền còn nợ</label>
                                <input type="text" id="remaining-amount" class="form-control text-right text-danger font-weight-bold" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mt-3">
        <div class="card-header">
            <h5 class="card-title">Chi tiết phiếu nhập</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="stock-in-items">
                    <thead>
                        <tr>
                            <th style="width: 50px">#</th>
                            <th>Sản phẩm</th>
                            <th style="width: 120px">Số lô</th>
                            <th style="width: 120px">Hạn sử dụng</th>
                            <th style="width: 100px">Số lượng</th>
                            <th style="width: 100px">Đơn vị</th>
                            <th style="width: 120px">Đơn giá</th>
                            <th style="width: 100px">Chiết khấu %</th>
                            <th style="width: 120px">Thuế %</th>
                            <th style="width: 130px">Thành tiền</th>
                            <th style="width: 50px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($details)): ?>
                        <tr class="item-row">
                            <td class="text-center row-index">1</td>
                            <td>
                                <?= Html::dropDownList('StockInDetail[0][product_id]', null, $products, [
                                    'class' => 'form-control select2 select-product',
                                    'prompt' => 'Chọn sản phẩm',
                                    'required' => true
                                ]) ?>
                            </td>
                            <td>
                                <?= Html::textInput('StockInDetail[0][batch_number]', null, [
                                    'class' => 'form-control',
                                    'placeholder' => 'Số lô'
                                ]) ?>
                            </td>
                            <td>
                                <?= Html::textInput('StockInDetail[0][expiry_date]', null, [
                                    'class' => 'form-control expiry-date',
                                    'placeholder' => 'dd/mm/yyyy'
                                ]) ?>
                            </td>
                            <td>
                                <?= Html::textInput('StockInDetail[0][quantity]', 1, [
                                    'class' => 'form-control text-right item-quantity',
                                    'required' => true,
                                    'type' => 'number',
                                    'min' => 1,
                                    'step' => 1
                                ]) ?>
                            </td>
                            <td>
                                <?= Html::dropDownList('StockInDetail[0][unit_id]', null, $units, [
                                    'class' => 'form-control item-unit',
                                    'required' => true
                                ]) ?>
                            </td>
                            <td>
                                <?= Html::textInput('StockInDetail[0][unit_price]', 0, [
                                    'class' => 'form-control text-right item-price',
                                    'required' => true,
                                    'type' => 'number',
                                    'min' => 0,
                                    'step' => 0.01
                                ]) ?>
                            </td>
                            <td>
                                <?= Html::textInput('StockInDetail[0][discount_percent]', 0, [
                                    'class' => 'form-control text-right item-discount',
                                    'type' => 'number',
                                    'min' => 0,
                                    'max' => 100,
                                    'step' => 0.01
                                ]) ?>
                            </td>
                            <td>
                                <?= Html::textInput('StockInDetail[0][tax_percent]', 0, [
                                    'class' => 'form-control text-right item-tax',
                                    'type' => 'number',
                                    'min' => 0,
                                    'max' => 100,
                                    'step' => 0.01
                                ]) ?>
                            </td>
                            <td>
                                <?= Html::textInput('StockInDetail[0][total_price]', 0, [
                                    'class' => 'form-control text-right item-total',
                                    'readonly' => true
                                ]) ?>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-danger btn-sm btn-remove-row">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($details as $index => $detail): ?>
                            <tr class="item-row">
                                <td class="text-center row-index"><?= $index + 1 ?></td>
                                <td>
                                    <?= Html::dropDownList("StockInDetail[$index][product_id]", $detail->product_id, $products, [
                                        'class' => 'form-control select2 select-product',
                                        'prompt' => 'Chọn sản phẩm',
                                        'required' => true
                                    ]) ?>
                                </td>
                                <td>
                                    <?= Html::textInput("StockInDetail[$index][batch_number]", $detail->batch_number, [
                                        'class' => 'form-control',
                                        'placeholder' => 'Số lô'
                                    ]) ?>
                                </td>
                                <td>
                                    <?= Html::textInput("StockInDetail[$index][expiry_date]", $detail->expiry_date ? date('Y-m-d', strtotime($detail->expiry_date)) : null, [
                                        'class' => 'form-control expiry-date',
                                        'placeholder' => 'dd/mm/yyyy'
                                    ]) ?>
                                </td>
                                <td>
                                    <?= Html::textInput("StockInDetail[$index][quantity]", $detail->quantity, [
                                        'class' => 'form-control text-right item-quantity',
                                        'required' => true,
                                        'type' => 'number',
                                        'min' => 1,
                                        'step' => 1
                                    ]) ?>
                                </td>
                                <td>
                                    <?= Html::dropDownList("StockInDetail[$index][unit_id]", $detail->unit_id, $units, [
                                        'class' => 'form-control item-unit',
                                        'required' => true
                                    ]) ?>
                                </td>
                                <td>
                                    <?= Html::textInput("StockInDetail[$index][unit_price]", $detail->unit_price, [
                                        'class' => 'form-control text-right item-price',
                                        'required' => true,
                                        'type' => 'number',
                                        'min' => 0,
                                        'step' => 0.01
                                    ]) ?>
                                </td>
                                <td>
                                    <?= Html::textInput("StockInDetail[$index][discount_percent]", $detail->discount_percent, [
                                        'class' => 'form-control text-right item-discount',
                                        'type' => 'number',
                                        'min' => 0,
                                        'max' => 100,
                                        'step' => 0.01
                                    ]) ?>
                                </td>
                                <td>
                                    <?= Html::textInput("StockInDetail[$index][tax_percent]", $detail->tax_percent, [
                                        'class' => 'form-control text-right item-tax',
                                        'type' => 'number',
                                        'min' => 0,
                                        'max' => 100,
                                        'step' => 0.01
                                    ]) ?>
                                </td>
                                <td>
                                    <?= Html::textInput("StockInDetail[$index][total_price]", $detail->total_price, [
                                        'class' => 'form-control text-right item-total',
                                        'readonly' => true
                                    ]) ?>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-danger btn-sm btn-remove-row">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="11">
                                <button type="button" class="btn btn-success btn-sm" id="btn-add-row">
                                    <i class="fas fa-plus"></i> Thêm sản phẩm
                                </button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="form-group text-center mt-3">
        <?= Html::submitButton('<i class="fas fa-save"></i> Lưu phiếu nhập kho', ['class' => 'btn btn-success']) ?>
        <?= Html::a('<i class="fas fa-arrow-left"></i> Quay lại', ['index'], ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
$productUrl = Url::to(['get-product']);
$js = <<<JS
    // Hàm format số tiền
    function formatMoney(amount) {
        return parseFloat(amount).toLocaleString('vi-VN');
    }
    
    // Khởi tạo datepicker cho ngày hết hạn
    $('.expiry-date').datepicker({
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true
    });
    
    // Khởi tạo select2 cho dropdown sản phẩm
    $('.select2').select2();
    
    // Cập nhật tính toán khi thay đổi chi tiết
    function updateDetails() {
        let totalAmount = 0;
        let totalDiscount = 0;
        let totalTax = 0;
        
        $('.item-row').each(function(index) {
            updateRowCalculation($(this));
            
            // Cập nhật STT
            $(this).find('.row-index').text(index + 1);
            
            // Cập nhật name attribute cho các input fields
            $(this).find('select, input').each(function() {
                let name = $(this).attr('name');
                if (name) {
                    let newName = name.replace(/\[\d+\]/, '[' + index + ']');
                    $(this).attr('name', newName);
                }
            });
            
            // Cộng dồn tổng
            let total = parseFloat($(this).find('.item-total').val()) || 0;
            let quantity = parseFloat($(this).find('.item-quantity').val()) || 0;
            let price = parseFloat($(this).find('.item-price').val()) || 0;
            let discountPercent = parseFloat($(this).find('.item-discount').val()) || 0;
            let taxPercent = parseFloat($(this).find('.item-tax').val()) || 0;
            
            let subtotal = quantity * price;
            let discount = subtotal * (discountPercent / 100);
            let tax = (subtotal - discount) * (taxPercent / 100);
            
            totalAmount += subtotal;
            totalDiscount += discount;
            totalTax += tax;
        });
        
        // Cập nhật tổng
        $('#stockin-total_amount').val(totalAmount.toFixed(2));
        $('#stockin-discount_amount').val(totalDiscount.toFixed(2));
        $('#stockin-tax_amount').val(totalTax.toFixed(2));
        
        let finalAmount = totalAmount - totalDiscount + totalTax;
        $('#stockin-final_amount').val(finalAmount.toFixed(2));
        
        // Cập nhật số tiền còn lại
        updateRemainingAmount();
    }
    
    // Tính toán cho một dòng
    function updateRowCalculation(row) {
        let quantity = parseFloat(row.find('.item-quantity').val()) || 0;
        let price = parseFloat(row.find('.item-price').val()) || 0;
        let discountPercent = parseFloat(row.find('.item-discount').val()) || 0;
        let taxPercent = parseFloat(row.find('.item-tax').val()) || 0;
        
        let subtotal = quantity * price;
        let discount = subtotal * (discountPercent / 100);
        let tax = (subtotal - discount) * (taxPercent / 100);
        let total = subtotal - discount + tax;
        
        row.find('.item-total').val(total.toFixed(2));
    }
    
    // Cập nhật số tiền còn lại
    function updateRemainingAmount() {
        let finalAmount = parseFloat($('#stockin-final_amount').val()) || 0;
        let paidAmount = parseFloat($('#stockin-paid_amount').val()) || 0;
        let remainingAmount = Math.max(0, finalAmount - paidAmount);
        
        $('#remaining-amount').val(remainingAmount.toFixed(2));
    }
    
    // Xử lý khi chọn sản phẩm
    $(document).on('change', '.select-product', function() {
        let productId = $(this).val();
        let row = $(this).closest('tr');
        
        if (productId) {
            $.get('{$productUrl}', {id: productId}, function(data) {
                let product = JSON.parse(data);
                row.find('.item-price').val(product.cost_price);
                row.find('.item-unit').val(product.unit_id);
                updateDetails();
            });
        }
    });
    
    // Sự kiện khi thay đổi số lượng hoặc giá
    $(document).on('change', '.item-quantity, .item-price, .item-discount, .item-tax', function() {
        updateDetails();
    });
    
    // Sự kiện khi thay đổi tiền đã thanh toán
    $(document).on('change', '#stockin-paid_amount', function() {
        updateRemainingAmount();
    });
    
    // Thêm dòng mới
    $('#btn-add-row').click(function() {
        let lastRow = $('.item-row:last');
        let newRow = lastRow.clone();
        
        // Làm trống các giá trị
        newRow.find('select').val('');
        newRow.find('input').not('.item-discount, .item-tax').val('');
        newRow.find('.item-discount, .item-tax').val('0');
        newRow.find('.item-quantity').val('1');
        
        // Thêm dòng mới vào bảng
        lastRow.after(newRow);
        
        // Khởi tạo lại select2 và datepicker
        newRow.find('.select2').select2();
        newRow.find('.expiry-date').datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth: true,
            changeYear: true
        });
        
        // Cập nhật tính toán
        updateDetails();
    });
    
    // Xóa dòng
    $(document).on('click', '.btn-remove-row', function() {
        if ($('.item-row').length > 1) {
            $(this).closest('tr').remove();
            updateDetails();
        } else {
            alert('Không thể xóa dòng duy nhất. Vui lòng thêm dòng khác trước khi xóa.');
        }
    });
    
    // Khởi tạo tính toán khi tải trang
    updateDetails();
    
    // Validate form trước khi submit
    $('#stock-in-form').submit(function(e) {
        if ($('.item-row').length === 0) {
            alert('Vui lòng thêm ít nhất một sản phẩm.');
            e.preventDefault();
            return false;
        }
        
        let valid = true;
        $('.item-row').each(function() {
            if (!$(this).find('.select-product').val()) {
                alert('Vui lòng chọn sản phẩm cho tất cả các dòng.');
                valid = false;
                return false;
            }
            
            if (parseFloat($(this).find('.item-quantity').val()) <= 0) {
                alert('Số lượng phải lớn hơn 0.');
                valid = false;
                return false;
            }
        });
        
        if (!valid) {
            e.preventDefault();
            return false;
        }
    });
JS;

$this->registerJs($js, View::POS_READY);
?>