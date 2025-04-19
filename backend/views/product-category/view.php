<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model common\models\ProductCategory */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Danh mục sản phẩm', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-category-view">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
            <div class="card-tools">
                <?= Html::a('<i class="fas fa-edit"></i> Cập nhật', ['update', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm']) ?>
                <?= Html::a('<i class="fas fa-trash"></i> Xóa', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger btn-sm',
                    'data' => [
                        'confirm' => 'Bạn có chắc muốn xóa danh mục này?',
                        'method' => 'post',
                    ],
                ]) ?>
                <?= Html::a('<i class="fas fa-arrow-left"></i> Quay lại', ['index'], ['class' => 'btn btn-default btn-sm']) ?>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            'name',
                            'slug',
                            [
                                'attribute' => 'parent_id',
                                'value' => $model->parent ? $model->parent->name : 'Không có',
                            ],
                            [
                                'attribute' => 'description',
                                'format' => 'html',
                            ],
                            [
                                'attribute' => 'status',
                                'format' => 'raw',
                                'value' => $model->status ? 
                                    '<span class="badge badge-success">Kích hoạt</span>' : 
                                    '<span class="badge badge-danger">Vô hiệu hóa</span>',
                            ],
                            'sort_order',
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
                                'value' => $model->createdBy ? $model->createdBy->username : '',
                            ],
                            [
                                'attribute' => 'updated_by',
                                'value' => $model->updatedBy ? $model->updatedBy->username : '',
                            ],
                        ],
                    ]) ?>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Hình ảnh</h5>
                        </div>
                        <div class="card-body text-center">
                            <?php if ($model->image): ?>
                                <?= Html::img('@web/' . $model->image, ['class' => 'img-fluid', 'style' => 'max-height: 200px;']) ?>
                            <?php else: ?>
                                <?= Html::img('@web/img/no-image.png', ['class' => 'img-fluid', 'style' => 'max-height: 200px;']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title">Sản phẩm thuộc danh mục</h5>
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
                                'attribute' => 'unit_id',
                                'value' => function ($model) {
                                    return $model->unit ? $model->unit->name : '';
                                },
                            ],
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