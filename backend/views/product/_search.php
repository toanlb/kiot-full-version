<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\daterange\DateRangePicker;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model common\models\ProductSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="product-search">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1,
            'class' => 'search-form'
        ],
    ]); ?>

    <div class="card card-primary card-outline card-tabs mb-3">
        <div class="card-header p-0 pt-1 border-bottom-0">
            <ul class="nav nav-tabs nav-justified" id="search-tab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="search-basic-tab" data-toggle="pill" href="#search-basic" role="tab" aria-controls="search-basic" aria-selected="true">Tìm kiếm cơ bản</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="search-advanced-tab" data-toggle="pill" href="#search-advanced" role="tab" aria-controls="search-advanced" aria-selected="false">Tìm kiếm nâng cao</a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="search-tabContent">
                <div class="tab-pane fade show active" id="search-basic" role="tabpanel" aria-labelledby="search-basic-tab">
                    <div class="row">
                        <div class="col-md-4">
                            <?= $form->field($model, 'name')->textInput(['placeholder' => 'Nhập tên sản phẩm']) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($model, 'code')->textInput(['placeholder' => 'Nhập mã sản phẩm']) ?>
                        </div>
                        <div class="col-md-3">
                            <?= $form->field($model, 'barcode')->textInput(['placeholder' => 'Nhập mã vạch']) ?>
                        </div>
                        <div class="col-md-2">
                            <?= $form->field($model, 'status')->dropDownList([
                                '' => 'Tất cả',
                                '1' => 'Kích hoạt', 
                                '0' => 'Vô hiệu hóa'
                            ], ['class' => 'form-control']) ?>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="search-advanced" role="tabpanel" aria-labelledby="search-advanced-tab">
                    <div class="row">
                        <div class="col-md-4">
                            <?= $form->field($model, 'category_id')->widget(Select2::classname(), [
                                'data' => $categories,
                                'options' => ['placeholder' => 'Chọn danh mục'],
                                'pluginOptions' => [
                                    'allowClear' => true
                                ],
                            ]); ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'unit_id')->widget(Select2::classname(), [
                                'data' => \yii\helpers\ArrayHelper::map(\common\models\ProductUnit::find()->all(), 'id', 'name'),
                                'options' => ['placeholder' => 'Chọn đơn vị tính'],
                                'pluginOptions' => [
                                    'allowClear' => true
                                ],
                            ]); ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'is_combo')->dropDownList([
                                '' => 'Tất cả',
                                '1' => 'Combo/Bộ', 
                                '0' => 'Sản phẩm đơn'
                            ], ['class' => 'form-control']) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'has_stock')->dropDownList([
                                '' => 'Tất cả',
                                '1' => 'Còn hàng', 
                                '0' => 'Hết hàng'
                            ], ['class' => 'form-control']) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($model, 'created_at')->widget(DateRangePicker::classname(), [
                                'convertFormat' => true,
                                'pluginOptions' => [
                                    'locale' => [
                                        'format' => 'Y-m-d',
                                        'separator' => ' - ',
                                    ],
                                    'opens' => 'left'
                                ]
                            ]); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 text-center">
                    <?= Html::submitButton('<i class="fa fa-search"></i> Tìm kiếm', ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('<i class="fa fa-redo"></i> Làm mới', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
$js = <<<JS
// Auto submit search form when selecting dropdowns
$('.search-form select').change(function() {
    $('.search-form').submit();
});
JS;
$this->registerJs($js);
?>