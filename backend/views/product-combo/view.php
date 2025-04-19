<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model common\models\Product */
/* @var $comboItemsProvider yii\data\ActiveDataProvider */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Sản phẩm Combo', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="product-combo-view">

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
            <div class="card-tools">
                <?= Html::a('<i class="fas fa-edit"></i> Cập nhật', ['update', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm']) ?>
                <?= Html::a('<i class="fas fa-trash"></i> Xóa', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger btn-sm',
                    'data' => [
                        'confirm' => 'Bạn có chắc chắn muốn xóa sản phẩm combo này?',
                        'method' => 'post',
                    ],
                ]) ?>
                <?= Html::a('<i class="fas fa-arrow-left"></i> Quay lại', ['index'], ['class' => 'btn btn-default btn-sm']) ?>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h4>Thông tin combo</h4>
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            'code',
                            'name',
                            [
                                'attribute' => 'category_id',
                                'value' => $model->category ? $model->category->name : 'Không có',
                                'label' => 'Danh mục'
                            ],
                            [
                                'attribute' => 'unit_id',
                                'value' => $model->unit ? $model->unit->name : 'Không có',
                                'label' => 'Đơn vị tính'
                            ],
                            [
                                'attribute' => 'cost_price',
                                'format' => ['currency'],
                                'label' => 'Giá vốn'
                            ],
                            [
                                'attribute' => 'selling_price',
                                'format' => ['currency'],
                                'label' => 'Giá bán'
                            ],
                            [
                                'attribute' => 'status',
                                'format' => 'raw',
                                'value' => $model->status == 1 ? 
                                    '<span class="badge badge-success">Đang kinh doanh</span>' : 
                                    '<span class="badge badge-danger">Ngừng kinh doanh</span>',
                                'label' => 'Trạng thái'
                            ],
                            'short_description:ntext',
                            [
                                'attribute' => 'created_at',
                                'format' => ['date', 'php:d/m/Y H:i'],
                                'label' => 'Ngày tạo'
                            ],
                            [
                                'attribute' => 'updated_at',
                                'format' => ['date', 'php:d/m/Y H:i'],
                                'label' => 'Ngày cập nhật'
                            ],
                            [
                                'attribute' => 'created_by',
                                'value' => $model->createdBy ? $model->createdBy->username : 'Không có',
                                'label' => 'Người tạo'
                            ],
                            [
                                'attribute' => 'updated_by',
                                'value' => $model->updatedBy ? $model->updatedBy->username : 'Không có',
                                'label' => 'Người cập nhật'
                            ],
                        ],
                    ]) ?>
                </div>
                
                <div class="col-md-6">
                    <?php if($model->description): ?>
                    <h4>Mô tả chi tiết</h4>
                    <div class="description-box border p-3 mb-4">
                        <?= $model->description ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <h4>Thành phần combo</h4>
                    <?= GridView::widget([
                        'dataProvider' => $comboItemsProvider,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            [
                                'attribute' => 'product_id',
                                'value' => function ($model) {
                                    return $model->product ? $model->product->code . ' - ' . $model->product->name : 'Không tìm thấy';
                                },
                                'label' => 'Sản phẩm'
                            ],
                            'quantity',
                            [
                                'attribute' => 'unit_id',
                                'value' => 'unit.name',
                                'label' => 'Đơn vị tính'
                            ],
                            [
                                'label' => 'Giá sản phẩm',
                                'value' => function ($model) {
                                    return $model->product ? Yii::$app->formatter->asCurrency($model->product->selling_price) : '0';
                                },
                            ],
                            [
                                'label' => 'Thành tiền',
                                'value' => function ($model) {
                                    if ($model->product) {
                                        return Yii::$app->formatter->asCurrency($model->product->selling_price * $model->quantity);
                                    }
                                    return '0';
                                },
                            ],
                        ],
                        'options' => [
                            'class' => 'table table-striped table-bordered',
                        ],
                        'showFooter' => true,
                    ]); ?>
                </div>
            </div>
        </div>
    </div>

</div>