<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\jui\DatePicker;
use yii\helpers\ArrayHelper;
use common\models\Province;

/* @var $this yii\web\View */
/* @var $model common\models\SupplierSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="supplier-search mb-4">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1,
            'class' => 'form-horizontal',
        ],
    ]); ?>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'name')->textInput(['placeholder' => 'Tên nhà cung cấp']) ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'code')->textInput(['placeholder' => 'Mã nhà cung cấp']) ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'phone')->textInput(['placeholder' => 'Số điện thoại']) ?>
        </div>
        <div class="col-md-2">
            <?= $form->field($model, 'status')->dropDownList([
                '' => 'Tất cả trạng thái',
                '1' => 'Hoạt động',
                '0' => 'Không hoạt động',
            ]) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'province_id')->dropDownList(
                ArrayHelper::map(Province::find()->all(), 'id', 'name'),
                [
                    'prompt' => 'Chọn tỉnh/thành',
                    'class' => 'form-control'
                ]
            ) ?>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-3">
            <label>Khoảng công nợ</label>
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'debt_from')->textInput(['placeholder' => 'Từ'])->label(false) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'debt_to')->textInput(['placeholder' => 'Đến'])->label(false) ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <label>Ngày tạo</label>
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'created_date_from')->widget(DatePicker::className(), [
                        'language' => 'vi',
                        'dateFormat' => 'yyyy-MM-dd',
                        'options' => [
                            'class' => 'form-control',
                            'placeholder' => 'Từ ngày'
                        ],
                        'clientOptions' => [
                            'changeMonth' => true,
                            'changeYear' => true,
                            'showButtonPanel' => true,
                        ],
                    ])->label(false) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'created_date_to')->widget(DatePicker::className(), [
                        'language' => 'vi',
                        'dateFormat' => 'yyyy-MM-dd',
                        'options' => [
                            'class' => 'form-control',
                            'placeholder' => 'Đến ngày'
                        ],
                        'clientOptions' => [
                            'changeMonth' => true,
                            'changeYear' => true,
                            'showButtonPanel' => true,
                        ],
                    ])->label(false) ?>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="form-group mt-4">
                <?= Html::submitButton('<i class="fas fa-search"></i> Tìm kiếm', ['class' => 'btn btn-primary']) ?>
                <?= Html::a('<i class="fas fa-redo"></i> Đặt lại', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>