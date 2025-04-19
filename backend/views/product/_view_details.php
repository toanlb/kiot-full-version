<?php
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Product */
?>

<div class="product-details mt-4">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Thông tin cơ bản</h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-striped table-bordered detail-view'],
                        'attributes' => [
                            'id',
                            'code',
                            'barcode',
                            'name',
                            'slug',
                            [
                                'attribute' => 'category_id',
                                'value' => $model->category ? $model->category->name : null,
                            ],
                            [
                                'attribute' => 'unit_id',
                                'value' => $model->unit ? $model->unit->name : null,
                            ],
                            [
                                'attribute' => 'status',
                                'format' => 'raw',
                                'value' => $model->status == 1 ? 
                                    '<span class="badge badge-success">Kích hoạt</span>' : 
                                    '<span class="badge badge-danger">Vô hiệu hóa</span>',
                            ],
                            [
                                'attribute' => 'is_combo',
                                'format' => 'raw',
                                'value' => $model->is_combo == 1 ? 
                                    '<span class="badge badge-info">Có</span>' : 
                                    '<span class="badge badge-secondary">Không</span>',
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Thông tin bổ sung</h5>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-striped table-bordered detail-view'],
                        'attributes' => [
                            [
                                'attribute' => 'cost_price',
                                'format' => 'currency',
                            ],
                            [
                                'attribute' => 'selling_price',
                                'format' => 'currency',
                            ],
                            'min_stock',
                            [
                                'attribute' => 'weight',
                                'value' => $model->weight ? $model->weight . ' kg' : null,
                            ],
                            'dimension',
                            [
                                'attribute' => 'warranty_period',
                                'value' => $model->warranty_period ? $model->warranty_period . ' tháng' : 'Không có bảo hành',
                            ],
                            [
                                'attribute' => 'created_at',
                                'format' => 'datetime',
                            ],
                            [
                                'attribute' => 'updated_at',
                                'format' => 'datetime',
                            ],
                            [
                                'attribute' => 'created_by',
                                'value' => $model->createdBy ? $model->createdBy->username : null,
                            ],
                            [
                                'attribute' => 'updated_by',
                                'value' => $model->updatedBy ? $model->updatedBy->username : null,
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Mô tả sản phẩm</h5>
                </div>
                <div class="card-body">
                    <?php if ($model->short_description): ?>
                        <div class="short-description mb-3">
                            <h6>Mô tả ngắn:</h6>
                            <p><?= Html::encode($model->short_description) ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($model->description): ?>
                        <div class="full-description">
                            <h6>Mô tả đầy đủ:</h6>
                            <div class="description-content">
                                <?= $model->description ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Sản phẩm chưa có mô tả chi tiết.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>