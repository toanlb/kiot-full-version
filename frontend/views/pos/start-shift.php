<?php
/* @var $this yii\web\View */
/* @var $warehouses app\models\Warehouse[] */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Bắt đầu ca làm việc';
$this->params['breadcrumbs'][] = $this->title;

// Register CSS & JS
$this->registerCssFile('@web/css/pos.css');
?>

<div class="start-shift-container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user-clock mr-2"></i> Bắt đầu ca làm việc</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <img src="<?= Yii::getAlias('@web/images/shift.png') ?>" alt="Shift Icon" style="max-width: 150px;">
                        <h4 class="mt-3">Bạn cần bắt đầu ca làm việc trước khi bán hàng</h4>
                        <p class="text-muted">Vui lòng chọn kho hàng và nhập số tiền đầu ca</p>
                    </div>
                    
                    <form id="startShiftForm">
                        <div class="form-group">
                            <label for="warehouseSelect">Chọn kho hàng <span class="text-danger">*</span></label>
                            <select id="warehouseSelect" name="warehouse_id" class="form-control" required>
                                <option value="">-- Chọn kho hàng --</option>
                                <?php foreach ($warehouses as $warehouse): ?>
                                <option value="<?= $warehouse->id ?>"><?= $warehouse->name ?> - <?= $warehouse->code ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="openingAmount">Số tiền đầu ca <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-money-bill"></i></span>
                                </div>
                                <input type="number" id="openingAmount" name="opening_amount" class="form-control" min="0" value="0" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">VNĐ</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="shiftNote">Ghi chú</label>
                            <textarea id="shiftNote" name="note" class="form-control" rows="2"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <div class="current-time">
                                <i class="far fa-clock"></i> <span id="current-time"><?= date('H:i:s') ?></span>
                            </div>
                            <div class="current-date">
                                <i class="far fa-calendar-alt"></i> <span id="current-date"><?= date('d/m/Y') ?></span>
                            </div>
                        </div>
                        
                        <div class="form-group mb-0">
                            <button type="submit" id="startShiftBtn" class="btn btn-primary btn-block">
                                <i class="fas fa-play-circle mr-1"></i> Bắt đầu ca làm việc
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay d-none">
    <div class="spinner-border text-primary" role="status">
        <span class="sr-only">Đang tải...</span>
    </div>
</div>

<?php
$script = <<<JS
$(document).ready(function() {
    // Update clock
    function updateClock() {
        const now = new Date();
        const timeString = now.toLocaleTimeString();
        const dateString = now.toLocaleDateString('vi-VN');
        
        $('#current-time').text(timeString);
        $('#current-date').text(dateString);
        
        setTimeout(updateClock, 1000);
    }
    
    updateClock();
    
    // Start shift form submit
    $('#startShiftForm').on('submit', function(e) {
        e.preventDefault();
        
        const warehouseId = $('#warehouseSelect').val();
        const openingAmount = $('#openingAmount').val();
        const note = $('#shiftNote').val();
        
        if (!warehouseId) {
            alert('Vui lòng chọn kho hàng.');
            return;
        }
        
        if (openingAmount < 0) {
            alert('Số tiền đầu ca không hợp lệ.');
            return;
        }
        
        // Show loading
        $('.loading-overlay').removeClass('d-none');
        
        $.ajax({
            url: baseUrl + '/pos/start-shift',
            type: 'POST',
            data: {
                warehouse_id: warehouseId,
                opening_amount: openingAmount,
                note: note
            },
            dataType: 'json',
            success: function(response) {
                $('.loading-overlay').addClass('d-none');
                
                if (response.success) {
                    alert('Ca làm việc đã được bắt đầu.');
                    window.location.href = baseUrl + '/pos/index';
                } else {
                    alert(response.message || 'Có lỗi xảy ra khi bắt đầu ca làm việc.');
                }
            },
            error: function() {
                $('.loading-overlay').addClass('d-none');
                alert('Không thể kết nối đến máy chủ.');
            }
        });
    });
});
JS;
$this->registerJs($script);
?>