<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model common\models\ProductUnit */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Đơn vị tính', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-unit-view">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
            <div class="card-tools">
                <?= Html::a('<i class="fas fa-edit"></i> Cập nhật', ['update', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm']) ?>
                <?= Html::a('<i class="fas fa-trash"></i> Xóa', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger btn-sm',
                    'data' => [
                        'confirm' => 'Bạn có chắc muốn xóa đơn vị tính này?',
                        'method' => 'post',
                    ],
                ]) ?>
                <?= Html::a('<i class="fas fa-arrow-left"></i> Quay lại', ['index'], ['class' => 'btn btn-default btn-sm']) ?>
            </div>
        </div>
        <div class="card-body">
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    'name',
                    'abbreviation',
                    [
                        'attribute' => 'is_default',
                        'format' => 'raw',
                        'value' => $model->is_default ? 
                            '<span class="badge badge-primary">Đơn vị mặc định</span>' : 
                            '<span class="badge badge-secondary">Không</span>',
                    ],
                    [
                        'attribute' => 'created_at',
                        'format' => 'datetime',
                    ],
                    [
                        'attribute' => 'updated_at',
                        'format' => 'datetime',
                    ],
                ],
            ]) ?>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title">Sản phẩm sử dụng đơn vị tính này</h5>
                </div>
                <div class="card-body">
                    <?= GridView::widget([
                        'dataProvider' => $productsProvider,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            [
                                'attribute' => 'image',
                                'label' => 'Hình ảnh',
                                'format' => 'html',
                                'value' => function ($model) {
                                    $mainImage = $model->getMainImage()->one();
                                    if ($mainImage) {
                                        return Html::img('@web/' . $mainImage->image, ['width' => '50px', 'class' => 'img-thumbnail']);
                                    }
                                    return Html::img('@web/img/no-image.png', ['width' => '50px', 'class' => 'img-thumbnail']);
                                },
                            ],
                            'code',
                            'name',
                            [
                                'attribute' => 'selling_price',
                                'format' => 'currency',
                                'contentOptions' => ['class' => 'text-right'],
                            ],
                            [
                                'attribute' => 'status',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    return $model->status ? 
                                        '<span class="badge badge-success">Kích hoạt</span>' : 
                                        '<span class="badge badge-danger">Vô hiệu hóa</span>';
                                },
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template' => '{view}',
                                'buttons' => [
                                    'view' => function ($url, $model) {
                                        return Html::a('<i class="fas fa-eye"></i>', ['product/view', 'id' => $model->id], [
                                            'title' => 'Xem sản phẩm',
                                            'class' => 'btn btn-sm btn-info',
                                        ]);
                                    },
                                ],
                            ],
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>