<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\CustomerGroupSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Nhóm khách hàng';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="customer-group-index">

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
                    <div class="card-tools">
                        <?= Html::a('<i class="fas fa-plus"></i> Tạo nhóm khách hàng', ['create'], ['class' => 'btn btn-success btn-sm']) ?>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <?php Pjax::begin(['id' => 'customer-group-pjax', 'timeout' => false, 'enablePushState' => false]); ?>
                    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
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

                            'name',
                            [
                                'attribute' => 'discount_rate',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    return $model->discount_rate . ' %';
                                },
                                'headerOptions' => ['class' => 'text-center', 'style' => 'width: 150px'],
                                'contentOptions' => ['class' => 'text-center'],
                            ],
                            [
                                'attribute' => 'description',
                                'format' => 'ntext',
                                'value' => function ($model) {
                                    return Yii::$app->formatter->asNtext(Yii::$app->formatter->asText($model->description, true));
                                },
                            ],
                            [
                                'attribute' => 'created_at',
                                'format' => 'date',
                                'filter' => \yii\jui\DatePicker::widget([
                                    'model' => $searchModel,
                                    'attribute' => 'created_at',
                                    'dateFormat' => 'dd-MM-yyyy',
                                    'options' => ['class' => 'form-control'],
                                ]),
                                'headerOptions' => ['class' => 'text-center', 'style' => 'width: 150px'],
                                'contentOptions' => ['class' => 'text-center'],
                            ],
                            [
                                'header' => 'Số lượng khách',
                                'value' => function ($model) {
                                    return $model->getCustomerCount();
                                },
                                'headerOptions' => ['class' => 'text-center', 'style' => 'width: 150px'],
                                'contentOptions' => ['class' => 'text-center'],
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template' => '{customer-list} {view} {update} {delete}',
                                'buttons' => [
                                    'customer-list' => function ($url, $model, $key) {
                                        return Html::a('<i class="fas fa-users"></i>', $url, [
                                            'title' => 'Danh sách khách hàng',
                                            'class' => 'btn btn-info btn-sm',
                                            'data-pjax' => '0',
                                        ]);
                                    },
                                    'view' => function ($url, $model, $key) {
                                        return Html::a('<i class="fas fa-eye"></i>', $url, [
                                            'title' => 'Xem chi tiết',
                                            'class' => 'btn btn-primary btn-sm ml-1',
                                            'data-pjax' => '0',
                                        ]);
                                    },
                                    'update' => function ($url, $model, $key) {
                                        return Html::a('<i class="fas fa-edit"></i>', $url, [
                                            'title' => 'Cập nhật',
                                            'class' => 'btn btn-warning btn-sm ml-1',
                                            'data-pjax' => '0',
                                        ]);
                                    },
                                    'delete' => function ($url, $model, $key) {
                                        return Html::a('<i class="fas fa-trash"></i>', $url, [
                                            'title' => 'Xóa',
                                            'class' => 'btn btn-danger btn-sm ml-1',
                                            'data-confirm' => 'Bạn có chắc chắn muốn xóa nhóm khách hàng này?',
                                            'data-method' => 'post',
                                            'data-pjax' => '0',
                                        ]);
                                    },
                                ],
                                'headerOptions' => ['class' => 'text-center', 'style' => 'width: 200px'],
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