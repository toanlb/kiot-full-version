<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Customer */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Lịch sử điểm thưởng: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Khách hàng', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Lịch sử điểm thưởng';
?>
<div class="customer-point-history">

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
                    <div class="card-tools">
                        <?= Html::a('<i class="fas fa-plus"></i> Cộng điểm', '#', [
                            'class' => 'btn btn-success btn-sm',
                            'data-toggle' => 'modal',
                            'data-target' => '#add-points-modal',
                        ]) ?>
                        <?php if ($model->getPoints() > 0): ?>
                            <?= Html::a('<i class="fas fa-minus"></i> Trừ điểm', '#', [
                                'class' => 'btn btn-danger btn-sm',
                                'data-toggle' => 'modal',
                                'data-target' => '#use-points-modal',
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
                                <span class="info-box-icon bg-info"><i class="fas fa-user"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Khách hàng</span>
                                    <span class="info-box-number"><?= Html::encode($model->name) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-star"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Điểm hiện tại</span>
                                    <span class="info-box-number"><?= number_format($model->getPoints()) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-plus"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Tổng cộng điểm</span>
                                    <span class="info-box-number"><?= number_format($model->customerPoint ? $model->customerPoint->total_points_earned : 0) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
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
                            [
                                'attribute' => 'reference_type',
                                'value' => function ($model) {
                                    return $model->getReferenceLabel();
                                },
                                'headerOptions' => ['class' => 'text-center'],
                                'contentOptions' => ['class' => 'text-center'],
                            ],
                            'note:ntext',
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