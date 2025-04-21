<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model common\models\CustomerGroup */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Danh sách khách hàng: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Nhóm khách hàng', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Danh sách khách hàng';
?>
<div class="customer-list">

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
                    <div class="card-tools">
                        <?= Html::a('<i class="fas fa-eye"></i> Xem nhóm', ['view', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm']) ?>
                        <?= Html::a('<i class="fas fa-plus"></i> Thêm khách hàng vào nhóm', ['/customer/create', 'group_id' => $model->id], ['class' => 'btn btn-success btn-sm']) ?>
                        <?= Html::a('<i class="fas fa-undo"></i> Quay lại', ['index'], ['class' => 'btn btn-default btn-sm']) ?>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <?php Pjax::begin(['id' => 'customer-group-customers-pjax', 'timeout' => false, 'enablePushState' => false]); ?>
                    
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
                            'code',
                            'name',
                            'phone',
                            'email:email',
                            [
                                'attribute' => 'debt_amount',
                                'value' => function ($model) {
                                    return Yii::$app->formatter->asCurrency($model->debt_amount);
                                },
                                'headerOptions' => ['class' => 'text-right'],
                                'contentOptions' => ['class' => 'text-right'],
                            ],
                            [
                                'attribute' => 'status',
                                'value' => function ($model) {
                                    return $model->status == 1 ? 'Hoạt động' : 'Không hoạt động';
                                },
                                'contentOptions' => function ($model) {
                                    return ['class' => $model->status == 1 ? 'text-success' : 'text-danger'];
                                },
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template' => '{view} {update} {change-group}',
                                'buttons' => [
                                    'view' => function ($url, $model, $key) {
                                        return Html::a('<i class="fas fa-eye"></i>', ['/customer/view', 'id' => $model->id], [
                                            'title' => 'Xem chi tiết',
                                            'class' => 'btn btn-primary btn-sm',
                                            'data-pjax' => '0',
                                        ]);
                                    },
                                    'update' => function ($url, $model, $key) {
                                        return Html::a('<i class="fas fa-edit"></i>', ['/customer/update', 'id' => $model->id], [
                                            'title' => 'Cập nhật',
                                            'class' => 'btn btn-warning btn-sm ml-1',
                                            'data-pjax' => '0',
                                        ]);
                                    },
                                    'change-group' => function ($url, $model, $key) {
                                        return Html::a('<i class="fas fa-exchange-alt"></i>', ['/customer/change-group', 'id' => $model->id], [
                                            'title' => 'Đổi nhóm',
                                            'class' => 'btn btn-info btn-sm ml-1',
                                            'data-pjax' => '0',
                                        ]);
                                    },
                                ],
                                'headerOptions' => ['class' => 'text-center', 'style' => 'width: 150px'],
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
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Thông tin nhóm khách hàng</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 30%">Tên nhóm</th>
                                    <td><?= Html::encode($model->name) ?></td>
                                </tr>
                                <tr>
                                    <th>Tỷ lệ chiết khấu</th>
                                    <td><?= Html::encode($model->discount_rate) ?> %</td>
                                </tr>
                                <tr>
                                    <th>Số lượng khách hàng</th>
                                    <td><?= Html::encode($model->getCustomerCount()) ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 30%">Mô tả</th>
                                    <td><?= Yii::$app->formatter->asNtext($model->description) ?></td>
                                </tr>
                                <tr>
                                    <th>Ngày tạo</th>
                                    <td><?= Yii::$app->formatter->asDatetime($model->created_at) ?></td>
                                </tr>
                                <tr>
                                    <th>Cập nhật lần cuối</th>
                                    <td><?= Yii::$app->formatter->asDatetime($model->updated_at) ?></td>
                                </tr>
                            </table>
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