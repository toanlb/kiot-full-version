<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $model common\models\CustomerGroup */
/* @var $customersDataProvider yii\data\ActiveDataProvider */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Nhóm khách hàng', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="customer-group-view">

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
                    <div class="card-tools">
                        <?= Html::a('<i class="fas fa-edit"></i> Cập nhật', ['update', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm']) ?>
                        <?= Html::a('<i class="fas fa-trash"></i> Xóa', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-danger btn-sm',
                            'data' => [
                                'confirm' => 'Bạn có chắc chắn muốn xóa nhóm khách hàng này?',
                                'method' => 'post',
                            ],
                        ]) ?>
                        <?= Html::a('<i class="fas fa-users"></i> Danh sách khách hàng', ['customer-list', 'id' => $model->id], ['class' => 'btn btn-info btn-sm']) ?>
                        <?= Html::a('<i class="fas fa-undo"></i> Quay lại', ['index'], ['class' => 'btn btn-default btn-sm']) ?>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            'name',
                            [
                                'attribute' => 'discount_rate',
                                'value' => $model->discount_rate . ' %',
                            ],
                            'description:ntext',
                            'created_at:datetime',
                            'updated_at:datetime',
                            [
                                'label' => 'Số lượng khách hàng',
                                'value' => $model->getCustomerCount(),
                                'format' => 'raw',
                            ],
                        ],
                    ]) ?>
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
                    <h3 class="card-title">Danh sách khách hàng trong nhóm</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <?php Pjax::begin(['id' => 'customers-pjax']); ?>
                    
                    <?= GridView::widget([
                        'dataProvider' => $customersDataProvider,
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
                                'template' => '{view}',
                                'buttons' => [
                                    'view' => function ($url, $model, $key) {
                                        return Html::a('<i class="fas fa-eye"></i>', ['/customer/view', 'id' => $model->id], [
                                            'title' => 'Xem chi tiết',
                                            'class' => 'btn btn-primary btn-sm',
                                            'data-pjax' => '0',
                                        ]);
                                    },
                                ],
                                'headerOptions' => ['class' => 'text-center', 'style' => 'width: 100px'],
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