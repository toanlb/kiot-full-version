<?php
use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use yii\widgets\DetailView;
use common\models\PaymentMethod;

/* @var $this yii\web\View */
/* @var $model common\models\Shift */
/* @var $paymentMethods array */
/* @var $paymentMethodAmounts array */

$this->title = 'Đóng ca làm việc #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Quản lý ca làm việc', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Ca làm việc #' . $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Đóng ca';
?>

<div class="shift-close">
    <div class="row">
        <!-- Thông tin ca làm việc -->
        <div class="col-md-5">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-2"></i> Thông tin ca làm việc
                    </h3>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-striped table-bordered detail-view'],
                        'attributes' => [
                            'id',
                            [
                                'attribute' => 'warehouse_id',
                                'value' => $model->warehouse->name,
                            ],
                            [
                                'attribute' => 'user_id',
                                'value' => $model->user->username,
                            ],
                            [
                                'attribute' => 'start_time',
                                'format' => 'datetime',
                            ],
                            [
                                'attribute' => 'opening_amount',
                                'format' => 'currency',
                            ],
                            [
                                'attribute' => 'total_sales',
                                'format' => 'currency',
                            ],
                            [
                                'attribute' => 'total_returns',
                                'format' => 'currency',
                            ],
                            [
                                'attribute' => 'expected_amount',
                                'format' => 'currency',
                                'contentOptions' => ['class' => 'font-weight-bold'],
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
        
        <!-- Form đóng ca -->
        <div class="col-md-7">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-lock mr-2"></i> Đóng ca làm việc
                    </h3>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin(['id' => 'close-shift-form']); ?>
                    
                    <h5 class="mb-3">Nhập số tiền thực tế theo phương thức thanh toán:</h5>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Phương thức thanh toán</th>
                                    <th width="40%">Số tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($paymentMethods as $method): ?>
                                <tr>
                                    <td>
                                        <label for="payment-method-<?= $method->id ?>"><?= Html::encode($method->name) ?></label>
                                    </td>
                                    <td>
                                        <input type="number" 
                                               id="payment-method-<?= $method->id ?>" 
                                               class="form-control payment-method-amount" 
                                               name="PaymentMethodAmount[<?= $method->id ?>]" 
                                               value="<?= isset($paymentMethodAmounts[$method->id]) ? $paymentMethodAmounts[$method->id] : 0 ?>"
                                               step="1000">
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <tr class="table-info">
                                    <th>Tổng cộng</th>
                                    <th>
                                        <span id="total-amount">0</span> đ
                                        <input type="hidden" name="Shift[actual_amount]" id="actual-amount-input" value="0">
                                    </th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <strong>Ghi chú:</strong> Số tiền mặt dự kiến là <strong><?= Yii::$app->formatter->asCurrency($model->expected_amount) ?></strong>. 
                        Vui lòng đếm kỹ tiền trước khi đóng ca.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <?= $form->field($model, 'note')->textarea(['rows' => 3, 'placeholder' => 'Ghi chú thêm (nếu có)']) ?>
                        </div>
                    </div>
                    
                    <div class="form-group text-right">
                        <?= Html::a('<i class="fas fa-times"></i> Hủy', ['view', 'id' => $model->id], ['class' => 'btn btn-default mr-2']) ?>
                        <?= Html::submitButton('<i class="fas fa-lock"></i> Đóng ca', ['class' => 'btn btn-warning']) ?>
                    </div>
                    
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$expectedAmount = $model->expected_amount;
$this->registerJs("
    $(function() {
        // Tính tổng số tiền khi trang tải
        calculateTotal();
        
        // Tính tổng số tiền khi thay đổi giá trị
        $('.payment-method-amount').on('change keyup', function() {
            calculateTotal();
        });
        
        // Hàm tính tổng số tiền
        function calculateTotal() {
            var total = 0;
            $('.payment-method-amount').each(function() {
                var value = parseFloat($(this).val()) || 0;
                total += value;
            });
            
            $('#total-amount').text(formatNumber(total));
            $('#actual-amount-input').val(total);
            
            var expectedAmount = {$expectedAmount};
            var difference = total - expectedAmount;
            
            if (difference != 0) {
                var message = difference > 0 ? 
                    'Thừa ' + formatNumber(difference) + ' đ' : 
                    'Thiếu ' + formatNumber(Math.abs(difference)) + ' đ';
                    
                var alertClass = difference > 0 ? 'alert-success' : 'alert-danger';
                
                // Kiểm tra xem đã có thông báo chưa
                if ($('#difference-alert').length) {
                    $('#difference-alert').attr('class', 'alert ' + alertClass).find('span').text(message);
                } else {
                    var alert = $('<div id=\"difference-alert\" class=\"alert ' + alertClass + ' mt-3\"><strong>Chênh lệch:</strong> <span>' + message + '</span></div>');
                    $('.alert-info').after(alert);
                }
            } else {
                $('#difference-alert').remove();
            }
        }
        
        // Hàm định dạng số
        function formatNumber(number) {
            return new Intl.NumberFormat('vi-VN').format(number);
        }
    });
");
?>