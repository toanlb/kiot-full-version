<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\Customer;
use common\models\CustomerGroup;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\CustomerSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Khách hàng';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="customer-index">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
                    <div class="card-tools">
                        <?= Html::a('<i class="fas fa-plus"></i> Tạo khách hàng', ['create'], ['class' => 'btn btn-success btn-sm']) ?>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <?php Pjax::begin(['id' => 'customer-pjax', 'timeout' => false, 'enablePushState' => false]); ?>
                    
                    <div class="mb-3">
                        <a class="btn btn-default btn-sm" data-toggle="collapse" href="#searchForm" role="button" aria-expanded="false">
                            <i class="fas fa-search"></i> Tìm kiếm nâng cao
                        </a>
                        
                        <div class="collapse mt-3" id="searchForm">
                            <div class="card card-body">
                                <?php 
                                $form = \yii\widgets\ActiveForm::begin([
                                    'action' => ['index'],
                                    'method' => 'get',
                                    'options' => ['data-pjax' => 1]
                                ]); 
                                ?>
                                
                                <div class="row">
                                    <div class="col-md-3">
                                        <?= $form->field($searchModel, 'name')->textInput(['placeholder' => 'Tên khách hàng']) ?>
                                    </div>
                                    <div class="col-md-3">
                                        <?= $form->field($searchModel, 'phone')->textInput(['placeholder' => 'Số điện thoại']) ?>
                                    </div>
                                    <div class="col-md-3">
                                        <?= $form->field($searchModel, 'email')->textInput(['placeholder' => 'Email']) ?>
                                    </div>
                                    <div class="col-md-3">
                                        <?= $form->field($searchModel, 'customer_group_id')->dropDownList(
                                            ArrayHelper::map(CustomerGroup::find()->orderBy('name')->all(), 'id', 'name'),
                                            ['prompt' => 'Tất cả nhóm']
                                        ) ?>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-3">
                                        <?= $form->field($searchModel, 'gender')->dropDownList(Customer::getGenders(), ['prompt' => 'Tất cả']) ?>
                                    </div>
                                    <div class="col-md-3">
                                        <?= $form->field($searchModel, 'status')->dropDownList(Customer::getStatuses(), ['prompt' => 'Tất cả']) ?>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Thời gian tạo</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <i class="far fa-calendar-alt"></i>
                                                    </span>
                                                </div>
                                                <input type="text" class="form-control float-right" id="dateRangePicker" name="CustomerSearch[date_range]" value="<?= $searchModel->date_range ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <?= Html::submitButton('<i class="fas fa-search"></i> Tìm kiếm', ['class' => 'btn btn-primary']) ?>
                                            <?= Html::resetButton('<i class="fas fa-redo"></i> Đặt lại', ['class' => 'btn btn-default']) ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php \yii\widgets\ActiveForm::end(); ?>
                            </div>
                        </div>
                    </div>

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

                            'code',
                            'name',
                            'phone',
                            'email:email',
                            [
                                'attribute' => 'customer_group_name',
                                'value' => 'customerGroup.name',
                                'filter' => ArrayHelper::map(CustomerGroup::find()->orderBy('name')->all(), 'id', 'name'),
                                'filterInputOptions' => ['class' => 'form-control form-control-sm'],
                            ],
                            [
                                'attribute' => 'gender',
                                'value' => function ($model) {
                                    return $model->getGenderLabel();
                                },
                                'filter' => Html::activeDropDownList($searchModel, 'gender', Customer::getGenders(), [
                                    'class' => 'form-control form-control-sm',
                                    'prompt' => 'Tất cả'
                                ]),
                                'headerOptions' => ['class' => 'text-center', 'style' => 'width: 100px'],
                                'contentOptions' => ['class' => 'text-center'],
                            ],
                            [
                                'attribute' => 'status',
                                'value' => function ($model) {
                                    return $model->getStatusLabel();
                                },
                                'filter' => Html::activeDropDownList($searchModel, 'status', Customer::getStatuses(), [
                                    'class' => 'form-control form-control-sm',
                                    'prompt' => 'Tất cả'
                                ]),
                                'contentOptions' => function ($model) {
                                    return ['class' => $model->status == Customer::STATUS_ACTIVE ? 'text-success' : 'text-danger'];
                                },
                                'headerOptions' => ['class' => 'text-center', 'style' => 'width: 120px'],
                                'contentOptions' => ['class' => 'text-center'],
                            ],
                            [
                                'attribute' => 'debt_amount',
                                'value' => function ($model) {
                                    return Yii::$app->formatter->asCurrency($model->debt_amount);
                                },
                                'headerOptions' => ['class' => 'text-right', 'style' => 'width: 120px'],
                                'contentOptions' => ['class' => 'text-right'],
                            ],
                            [
                                'attribute' => 'created_at',
                                'format' => 'date',
                                'headerOptions' => ['class' => 'text-center', 'style' => 'width: 150px'],
                                'contentOptions' => ['class' => 'text-center'],
                            ],
                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template' => '{view} {update} {delete}',
                                'buttons' => [
                                    'view' => function ($url, $model, $key) {
                                        return Html::a('<i class="fas fa-eye"></i>', $url, [
                                            'title' => 'Xem chi tiết',
                                            'class' => 'btn btn-primary btn-sm',
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
                                            'data-confirm' => 'Bạn có chắc chắn muốn xóa khách hàng này?',
                                            'data-method' => 'post',
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
</div>

<?php
$script = <<<JS
$(function() {
    // Daterangepicker với JS thuần
    $('#dateRangePicker').daterangepicker({
        locale: {
            format: 'DD-MM-YYYY',
            separator: ' - ',
            applyLabel: 'Áp dụng',
            cancelLabel: 'Hủy',
            fromLabel: 'Từ',
            toLabel: 'Đến',
            customRangeLabel: 'Tùy chỉnh',
            weekLabel: 'T',
            daysOfWeek: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
            monthNames: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6',
                        'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
            firstDay: 1
        },
        autoUpdateInput: false,
        opens: 'left'
    });
    
    $('#dateRangePicker').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
    });
    
    $('#dateRangePicker').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });
});
JS;
$this->registerJs($script);
?>