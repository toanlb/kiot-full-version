<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\search\WarehouseSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Quản lý Kho hàng';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="warehouse-index">

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
            <div class="card-tools">
                <?= Html::a('<i class="fas fa-plus"></i> Thêm mới kho hàng', ['create'], ['class' => 'btn btn-success btn-sm']) ?>
            </div>
        </div>
        <div class="card-body">
            <?php Pjax::begin(); ?>
            <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                    'code',
                    'name',
                    'address',
                    'phone',
                    [
                        'attribute' => 'manager_id',
                        'value' => function ($model) {
                            return $model->manager ? $model->manager->full_name : 'Chưa phân công';
                        },
                        'filter' => \yii\helpers\ArrayHelper::map(\common\models\User::find()->all(), 'id', 'full_name'),
                    ],
                    [
                        'attribute' => 'is_default',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return $model->is_default ? '<span class="badge badge-success">Có</span>' : '<span class="badge badge-secondary">Không</span>';
                        },
                        'filter' => [1 => 'Có', 0 => 'Không'],
                    ],
                    [
                        'attribute' => 'is_active',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return $model->is_active ? '<span class="badge badge-success">Kích hoạt</span>' : '<span class="badge badge-danger">Tạm khóa</span>';
                        },
                        'filter' => [1 => 'Kích hoạt', 0 => 'Tạm khóa'],
                    ],
                    [
                        'attribute' => 'created_at',
                        'format' => ['date', 'php:d/m/Y H:i'],
                        'filter' => false,
                    ],

                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view} {update} {delete}',
                        'buttons' => [
                            'view' => function ($url, $model) {
                                return Html::a('<i class="fas fa-eye"></i>', $url, [
                                    'title' => 'Xem chi tiết',
                                    'class' => 'btn btn-primary btn-sm',
                                ]);
                            },
                            'update' => function ($url, $model) {
                                return Html::a('<i class="fas fa-edit"></i>', $url, [
                                    'title' => 'Cập nhật',
                                    'class' => 'btn btn-info btn-sm',
                                ]);
                            },
                            'delete' => function ($url, $model) {
                                return Html::a('<i class="fas fa-trash"></i>', $url, [
                                    'title' => 'Xóa',
                                    'class' => 'btn btn-danger btn-sm',
                                    'data' => [
                                        'confirm' => 'Bạn có chắc chắn muốn xóa kho hàng này?',
                                        'method' => 'post',
                                    ],
                                ]);
                            },
                        ],
                    ],
                ],
            ]); ?>

            <?php Pjax::end(); ?>
        </div>
    </div>
</div>