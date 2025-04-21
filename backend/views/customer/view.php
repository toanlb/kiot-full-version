<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap4\Modal;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Customer */
/* @var $debtProvider yii\data\ActiveDataProvider */
/* @var $pointProvider yii\data\ActiveDataProvider */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Khách hàng', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="customer-view">

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
                    <div class="card-tools">
                        <?= Html::a('<i class="fas fa-edit"></i> Cập nhật', ['update', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm']) ?>
                        <?= Html::a('<i class="fas fa-trash"></i> Xóa', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-danger btn-sm',
                            'data' => [
                                'confirm' => 'Bạn có chắc chắn muốn xóa khách hàng này?',
                                'method' => 'post',
                            ],
                        ]) ?>
                        <?= Html::a('<i class="fas fa-undo"></i> Quay lại', ['index'], ['class' => 'btn btn-default btn-sm']) ?>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-primary card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">Thông tin cơ bản</h3>
                                </div>
                                <div class="card-body">
                                    <?= DetailView::widget([
                                        'model' => $model,
                                        'attributes' => [
                                            'id',
                                            'code',
                                            'name',
                                            'phone',
                                            'email:email',
                                            [
                                                'attribute' => 'customer_group_id',
                                                'value' => $model->customerGroup ? $model->customerGroup->name : null,
                                                'format' => 'raw',
                                                'value' => function ($model) {
                                                    if ($model->customerGroup) {
                                                        return $model->customerGroup->name . ' ' . 
                                                               Html::a('<i class="fas fa-exchange-alt"></i>', ['change-group', 'id' => $model->id], [
                                                                   'class' => 'btn btn-xs btn-info',
                                                                   'title' => 'Đổi nhóm',
                                                                   'data-toggle' => 'tooltip',
                                                               ]);
                                                    }
                                                    return null;
                                                }
                                            ],
                                            [
                                                'attribute' => 'gender',
                                                'value' => $model->getGenderLabel(),
                                            ],
                                            [
                                                'attribute' => 'status',
                                                'format' => 'raw',
                                                'value' => function ($model) {
                                                    $class = $model->status == $model::STATUS_ACTIVE ? 'success' : 'danger';
                                                    return '<span class="badge badge-' . $class . '">' . $model->getStatusLabel() . '</span>';
                                                },
                                            ],
                                            'birthday:date',
                                            'tax_code',
                                            'company_name',
                                        ],
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-success card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">Thông tin địa chỉ</h3>
                                </div>
                                <div class="card-body">
                                    <?= DetailView::widget([
                                        'model' => $model,
                                        'attributes' => [
                                            'address',
                                            [
                                                'attribute' => 'province_id',
                                                'value' => $model->province ? $model->province->name : null,
                                            ],
                                            [
                                                'attribute' => 'district_id',
                                                'value' => $model->district ? $model->district->name : null,
                                            ],
                                            [
                                                'attribute' => 'ward_id',
                                                'value' => $model->ward ? $model->ward->name : null,
                                            ],
                                            [
                                                'label' => 'Địa chỉ đầy đủ',
                                                'value' => $model->getFullAddress(),
                                            ],
                                            'created_at:datetime',
                                            'updated_at:datetime',
                                            [
                                                'attribute' => 'created_by',
                                                'value' => $model->createdBy ? $model->createdBy->username : null,
                                            ],
                                        ],
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-warning card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">Công nợ</h3>
                                    <div class="card-tools">
                                        <?= Html::a('<i class="fas fa-plus"></i> Thêm nợ', '#', [
                                            'class' => 'btn btn-danger btn-xs',
                                            'data-toggle' => 'modal',
                                            'data-target' => '#add-debt-modal',
                                        ]) ?>
                                        <?php if ($model->debt_amount > 0): ?>
                                            <?= Html::a('<i class="fas fa-money-bill"></i> Thanh toán', '#', [
                                                'class' => 'btn btn-success btn-xs',
                                                'data-toggle' => 'modal',
                                                'data-target' => '#pay-debt-modal',
                                            ]) ?>
                                        <?php endif; ?>
                                        <?= Html::a('<i class="fas fa-history"></i> Lịch sử', ['debt-history', 'id' => $model->id], [
                                            'class' => 'btn btn-info btn-xs',
                                            'title' => 'Xem lịch sử công nợ',
                                        ]) ?>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-warning"><i class="fas fa-credit-card"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Hạn mức tín dụng</span>
                                                    <span class="info-box-number"><?= Yii::$app->formatter->asCurrency($model->credit_limit) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-danger"><i class="fas fa-money-bill"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Dư nợ hiện tại</span>
                                                    <span class="info-box-number"><?= Yii::$app->formatter->asCurrency($model->debt_amount) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <?php Pjax::begin(['id' => 'debt-history-pjax']); ?>
                                    <?= GridView::widget([
                                        'dataProvider' => $debtProvider,
                                        'summary' => '',
                                        'columns' => [
                                            [
                                                'attribute' => 'transaction_date',
                                                'format' => 'datetime',
                                                'headerOptions' => ['class' => 'text-center'],
                                                'contentOptions' => ['class' => 'text-center'],
                                            ],
                                            [
                                                'attribute' => 'type',
                                                'value' => function ($model) {
                                                    return $model->getTypeLabel();
                                                },
                                                'contentOptions' => function ($model) {
                                                    return ['class' => $model->type == 1 ? 'text-danger' : 'text-success'];
                                                },
                                                'headerOptions' => ['class' => 'text-center'],
                                                'contentOptions' => ['class' => 'text-center'],
                                            ],
                                            [
                                                'attribute' => 'amount',
                                                'value' => function ($model) {
                                                    return $model->getFormattedAmount();
                                                },
                                                'headerOptions' => ['class' => 'text-right'],
                                                'contentOptions' => function ($model) {
                                                    return [
                                                        'class' => 'text-right ' . ($model->type == 1 ? 'text-danger' : 'text-success')
                                                    ];
                                                },
                                            ],
                                            [
                                                'attribute' => 'balance',
                                                'value' => function ($model) {
                                                    return Yii::$app->formatter->asCurrency($model->balance);
                                                },
                                                'headerOptions' => ['class' => 'text-right'],
                                                'contentOptions' => ['class' => 'text-right'],
                                            ],
                                        ],
                                    ]); ?>
                                    <?php Pjax::end(); ?>
                                </div>
                                <div class="card-footer text-center">
                                    <?= Html::a('Xem thêm', ['debt-history', 'id' => $model->id], ['class' => 'btn btn-sm btn-default']) ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card card-info card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">Điểm tích lũy</h3>
                                    <div class="card-tools">
                                        <?= Html::a('<i class="fas fa-plus"></i> Cộng điểm', '#', [
                                            'class' => 'btn btn-success btn-xs',
                                            'data-toggle' => 'modal',
                                            'data-target' => '#add-points-modal',
                                        ]) ?>
                                        <?php if ($model->getPoints() > 0): ?>
                                            <?= Html::a('<i class="fas fa-minus"></i> Trừ điểm', '#', [
                                                'class' => 'btn btn-danger btn-xs',
                                                'data-toggle' => 'modal',
                                                'data-target' => '#use-points-modal',
                                            ]) ?>
                                        <?php endif; ?>
                                        <?= Html::a('<i class="fas fa-history"></i> Lịch sử', ['point-history', 'id' => $model->id], [
                                            'class' => 'btn btn-info btn-xs',
                                            'title' => 'Xem lịch sử điểm',
                                        ]) ?>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-info"><i class="fas fa-star"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Điểm hiện tại</span>
                                                    <span class="info-box-number"><?= number_format($model->getPoints()) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-success"><i class="fas fa-plus"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Tổng cộng điểm</span>
                                                    <span class="info-box-number"><?= number_format($model->customerPoint ? $model->customerPoint->total_points_earned : 0) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box">
                                                <span class="info-box-icon bg-danger"><i class="fas fa-minus"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Tổng trừ điểm</span>
                                                    <span class="info-box-number"><?= number_format($model->customerPoint ? $model->customerPoint->total_points_used : 0) ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <?php Pjax::begin(['id' => 'point-history-pjax']); ?>
                                    <?= GridView::widget([
                                        'dataProvider' => $pointProvider,
                                        'summary' => '',
                                        'columns' => [
                                            [
                                                'attribute' => 'created_at',
                                                'format' => 'datetime',
                                                'headerOptions' => ['class' => 'text-center'],
                                                'contentOptions' => ['class' => 'text-center'],
                                            ],
                                            [
                                                'attribute' => 'type',
                                                'value' => function ($model) {
                                                    return $model->getTypeLabel();
                                                },
                                                'contentOptions' => function ($model) {
                                                    return ['class' => $model->type == 1 ? 'text-success' : 'text-danger'];
                                                },
                                                'headerOptions' => ['class' => 'text-center'],
                                                'contentOptions' => ['class' => 'text-center'],
                                            ],
                                            [
                                                'attribute' => 'points',
                                                'value' => function ($model) {
                                                    return $model->getFormattedPoints();
                                                },
                                                'headerOptions' => ['class' => 'text-right'],
                                                'contentOptions' => function ($model) {
                                                    return [
                                                        'class' => 'text-right ' . ($model->type == 1 ? 'text-success' : 'text-danger')
                                                    ];
                                                },
                                            ],
                                            [
                                                'attribute' => 'balance',
                                                'value' => function ($model) {
                                                    return number_format($model->balance);
                                                },
                                                'headerOptions' => ['class' => 'text-right'],
                                                'contentOptions' => ['class' => 'text-right'],
                                            ],
                                        ],
                                    ]); ?>
                                    <?php Pjax::end(); ?>
                                </div>
                                <div class="card-footer text-center">
                                    <?= Html::a('Xem thêm', ['point-history', 'id' => $model->id], ['class' => 'btn btn-sm btn-default']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
</div>

<!-- Modal thêm nợ -->
<div class="modal fade" id="add-debt-modal" tabindex="-1" role="dialog" aria-labelledby="add-debt-modal-label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="add-debt-modal-label"><i class="fas fa-plus"></i> Thêm khoản nợ</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php $form = ActiveForm::begin(['action' => ['add-debt', 'id' => $model->id], 'options' => ['data-pjax' => '0']]); ?>
                
                <div class="form-group">
                    <label for="amount">Số tiền</label>
                    <input type="number" id="amount" name="amount" class="form-control" min="1" step="1000" required>
                    <div class="help-block">Nhập số tiền cần thêm nợ</div>
                </div>
                
                <div class="form-group">
                    <label for="description">Mô tả</label>
                    <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                    <div class="help-block">Nhập lý do thêm nợ</div>
                </div>
                
                <div class="form-group text-right">
                    <?= Html::submitButton('<i class="fas fa-save"></i> Lưu', ['class' => 'btn btn-success']) ?>
                    <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fas fa-times"></i> Đóng</button>
                </div>
                
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal thanh toán nợ -->
<div class="modal fade" id="pay-debt-modal" tabindex="-1" role="dialog" aria-labelledby="pay-debt-modal-label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pay-debt-modal-label"><i class="fas fa-money-bill"></i> Thanh toán nợ</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php $form = ActiveForm::begin(['action' => ['pay-debt', 'id' => $model->id], 'options' => ['data-pjax' => '0']]); ?>
                
                <div class="form-group">
                    <label for="amount">Số tiền</label>
                    <input type="number" id="amount" name="amount" class="form-control" min="1" max="<?= $model->debt_amount ?>" step="1000" required value="<?= $model->debt_amount ?>">
                    <div class="help-block">Số tiền thanh toán (tối đa: <?= Yii::$app->formatter->asCurrency($model->debt_amount) ?>)</div>
                </div>
                
                <div class="form-group">
                    <label for="description">Mô tả</label>
                    <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                    <div class="help-block">Nhập thông tin thanh toán</div>
                </div>
                
                <div class="form-group text-right">
                    <?= Html::submitButton('<i class="fas fa-save"></i> Lưu', ['class' => 'btn btn-success']) ?>
                    <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fas fa-times"></i> Đóng</button>
                </div>
                
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal cộng điểm -->
<div class="modal fade" id="add-points-modal" tabindex="-1" role="dialog" aria-labelledby="add-points-modal-label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="add-points-modal-label"><i class="fas fa-plus"></i> Cộng điểm</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php $form = ActiveForm::begin(['action' => ['add-points', 'id' => $model->id], 'options' => ['data-pjax' => '0']]); ?>
                
                <div class="form-group">
                    <label for="points">Số điểm</label>
                    <input type="number" id="points" name="points" class="form-control" min="1" step="1" required>
                    <div class="help-block">Nhập số điểm cần cộng</div>
                </div>
                
                <div class="form-group">
                    <label for="note">Ghi chú</label>
                    <textarea id="note" name="note" class="form-control" rows="3"></textarea>
                    <div class="help-block">Nhập lý do cộng điểm</div>
                </div>
                
                <div class="form-group text-right">
                    <?= Html::submitButton('<i class="fas fa-save"></i> Lưu', ['class' => 'btn btn-success']) ?>
                    <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fas fa-times"></i> Đóng</button>
                </div>
                
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal trừ điểm -->
<div class="modal fade" id="use-points-modal" tabindex="-1" role="dialog" aria-labelledby="use-points-modal-label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="use-points-modal-label"><i class="fas fa-minus"></i> Trừ điểm</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php $form = ActiveForm::begin(['action' => ['use-points', 'id' => $model->id], 'options' => ['data-pjax' => '0']]); ?>
                
                <div class="form-group">
                    <label for="points">Số điểm</label>
                    <input type="number" id="points" name="points" class="form-control" min="1" max="<?= $model->getPoints() ?>" step="1" required value="<?= $model->getPoints() ?>">
                    <div class="help-block">Số điểm cần trừ (tối đa: <?= number_format($model->getPoints()) ?>)</div>
                </div>
                
                <div class="form-group">
                    <label for="note">Ghi chú</label>
                    <textarea id="note" name="note" class="form-control" rows="3"></textarea>
                    <div class="help-block">Nhập lý do trừ điểm</div>
                </div>
                
                <div class="form-group text-right">
                    <?= Html::submitButton('<i class="fas fa-save"></i> Lưu', ['class' => 'btn btn-success']) ?>
                    <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fas fa-times"></i> Đóng</button>
                </div>
                
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>

<?php
$script = <<<JS
$(function() {
    // Khởi tạo tooltip
    $('[data-toggle="tooltip"]').tooltip();
});
JS;
$this->registerJs($script);
?>