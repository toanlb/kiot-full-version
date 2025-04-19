<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model common\models\ProductAttribute */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Thuộc tính sản phẩm', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-attribute-view">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
            <div class="card-tools">
                <?= Html::a('<i class="fas fa-edit"></i> Cập nhật', ['update', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm']) ?>
                <?= Html::a('<i class="fas fa-trash"></i> Xóa', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger btn-sm',
                    'data' => [
                        'confirm' => 'Bạn có chắc muốn xóa thuộc tính này?',
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
                    'sort_order',
                    [
                        'attribute' => 'is_filterable',
                        'format' => 'raw',
                        'value' => $model->is_filterable ? 
                            '<span class="badge badge-success">Có</span>' : 
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
                    <h5 class="card-title">Sản phẩm sử dụng thuộc tính này</h5>
                </div>
                <div class="card-body">
                    <?= GridView::widget([
                        'dataProvider' => $attributeValuesProvider,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            [
                                'label' => 'Sản phẩm',
                                'value' => function ($model) {
                                    return $model->product ? $model->product->name : '';
                                },
                            ],
                            [
                                'label' => 'Mã sản phẩm',
                                'value' => function ($model) {
                                    return $model->product ? $model->product->code : '';
                                },
                            ],
                            [
                                'label' => 'Giá trị',
                                'value' => 'value',
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template' => '{view}',
                                'buttons' => [
                                    'view' => function ($url, $model) {
                                        return Html::a('<i class="fas fa-eye"></i>', ['product/view', 'id' => $model->product_id], [
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