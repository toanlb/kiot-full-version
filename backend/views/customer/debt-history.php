<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Customer */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Lịch sử công nợ: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Khách hàng', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Lịch sử công nợ';
?>
<div class="customer-debt-history">

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
                    <div class="card-tools">
                        <?= Html::a('<i class="fas fa-plus"></i> Thêm nợ', '#', [
                            'class' => 'btn btn-danger btn-sm',
                            'data-toggle' => 'modal',
                            'data-target' => '#add-debt-modal',
                        ]) ?>
                        <?php if ($model->debt_amount > 0): ?>
                            <?= Html::a('<i class="fas fa-money-bill"></i> Thanh toán', '#', [
                                'class' => 'btn btn-success btn-sm',
                                'data-toggle' => 'modal',
                                'data-target' => '#pay-debt-modal',
                            ]) ?>
                        <?php endif; ?>
                        <?= Html::a('<i class="fas fa-user"></i> Xem thông tin khách hàng', ['view', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm']) ?>
                        <?= Html::a('<i class="fas fa-undo"></i> Quay lại', ['view', 'id' => $model->id], ['class' => 'btn btn-default btn-sm']) ?>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-user"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Khách hàng</span>
                                    <span class="info-box-number"><?= Html::encode($model->name) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-phone"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Điện thoại</span>
                                    <span class="info-box-number"><?= Html::encode($model->phone) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-credit-card"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Hạn mức tín dụng</span>
                                    <span class="info-box-number"><?= Yii::$app->formatter->asCurrency($model->credit_limit) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
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
                        'dataProvider' => $dataProvider,
                        'layout' => "{summary}\n{items}\n<div class='card-footer clearfix'>{pager}</div>",
                        'pager' => [
                            'class' => 'yii\bootstrap4\LinkPager',
                            'options' => ['class' => 'pagination float-right'],
                            'maxButtonCount' => 5,
                            'firstPageLabel' => '<i class="fas fa-angle-double-left"></i>',
                            'lastPageLabel' => '<i class="fas fa-angle-double-right"></i>',
                            'prevPageLabel' => '<i class="fas fa-angle-left"></i>',
                            'nextPageLabel' => '<i class="fas fa-angle-right"></i>',
                        ],
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
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
                            [
                                'attribute' => 'reference_type',
                                'value' => function ($model) {
                                    return $model->getReferenceLabel();
                                },
                                'headerOptions' => ['class' => 'text-center'],
                                'contentOptions' => ['class' => 'text-center'],
                            ],
                            'description:ntext',
                            [
                                'attribute' => 'created_at',
                                'format' => 'datetime',
                                'headerOptions' => ['class' => 'text-center'],
                                'contentOptions' => ['class' => 'text-center'],
                            ],
                            [
                                'attribute' => 'created_by',
                                'value' => function ($model) {
                                    return $model->createdBy ? $model->createdBy->username : null;
                                },
                                'headerOptions' => ['class' => 'text-center'],
                                'contentOptions' => ['class' => 'text-center'],
                            ],
                        ],
                    ]); ?>
                    <?php Pjax::end(); ?>
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