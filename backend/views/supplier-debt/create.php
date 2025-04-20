<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\date\DatePicker;
use yii\helpers\ArrayHelper;
use common\models\Supplier;

/* @var $this yii\web\View */
/* @var $model common\models\SupplierDebt */

$this->title = 'Thêm công nợ nhà cung cấp';
$this->params['breadcrumbs'][] = ['label' => 'Quản lý công nợ nhà cung cấp', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="supplier-debt-create card">
    <div class="card-header">
        <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
    </div>
    <div class="card-body">
        <?php $form = ActiveForm::begin(); ?>
        
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'supplier_id')->widget(Select2::classname(), [
                    'data' => ArrayHelper::map(Supplier::find()->orderBy(['name' => SORT_ASC])->all(), 'id', function($model) {
                        return $model->code . ' - ' . $model->name;
                    }),
                    'options' => ['placeholder' => 'Chọn nhà cung cấp'],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                ]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'type')->dropDownList([
                    1 => 'Nợ',
                    2 => 'Thanh toán',
                ]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'amount')->textInput(['type' => 'number', 'step' => '0.01', 'min' => '0']) ?>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <?= $form->field($model, 'description')->textarea(['rows' => 3]) ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'transaction_date')->widget(DatePicker::classname(), [
                    'options' => ['placeholder' => 'Chọn ngày giao dịch'],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy-mm-dd',
                        'todayHighlight' => true
                    ]
                ]) ?>
            </div>
        </div>
        
        <div class="form-group">
            <?= Html::submitButton('<i class="fas fa-save"></i> Lưu', ['class' => 'btn btn-success']) ?>
            <?= Html::a('<i class="fas fa-ban"></i> Hủy', ['index'], ['class' => 'btn btn-secondary']) ?>
        </div>
        
        <?php ActiveForm::end(); ?>
    </div>
</div>