<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use common\models\Warehouse;
use common\models\User;

/* @var $this yii\web\View */
/* @var $model common\models\User */
/* @var $profile common\models\UserProfile */
/* @var $warehouses common\models\Warehouse[] */
/* @var $availableRoles array */
/* @var $assignedWarehouses array */
/* @var $form yii\widgets\ActiveForm */

// Get available warehouses as array for dropdown
$warehousesList = ArrayHelper::map($warehouses, 'id', 'name');

// Get available roles as array for dropdown
$rolesList = ArrayHelper::map($availableRoles, 'name', function($role) {
    return $role->name . ' - ' . ($role->description ?: $role->name);
});
?>

<div class="user-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Thông tin tài khoản</h3>
                </div>
                <div class="card-body">
                    <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'email')->textInput(['maxlength' => true, 'type' => 'email']) ?>

                    <?= $form->field($model, 'full_name')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'password')->passwordInput(['maxlength' => true])
                        ->label($model->isNewRecord ? 'Mật khẩu' : 'Mật khẩu mới (để trống nếu không đổi)') ?>

                    <?= $form->field($model, 'status')->dropDownList([
                        User::STATUS_INACTIVE => 'Chưa kích hoạt',
                        User::STATUS_ACTIVE => 'Đã kích hoạt',
                    ]) ?>

                    <?= $form->field($model, 'warehouse_id')->dropDownList($warehousesList, [
                        'prompt' => '-- Chọn kho mặc định --',
                        'class' => 'form-control select2',
                    ])->hint('Kho mặc định là kho làm việc chính của người dùng') ?>

                    <?= $form->field($model, 'warehouses')->checkboxList($warehousesList, [
                        'item' => function ($index, $label, $name, $checked, $value) use ($assignedWarehouses) {
                            $checked = in_array($value, $assignedWarehouses) ? 'checked' : '';
                            return "<div class='checkbox'><label><input type='checkbox' name='{$name}[]' value='{$value}' {$checked}> {$label}</label></div>";
                        },
                    ])->label('Kho được phép truy cập')->hint('Chọn các kho mà người dùng có quyền truy cập') ?>

                    <?= $form->field($model, 'roles')->checkboxList($rolesList, [
                        'item' => function ($index, $label, $name, $checked, $value) use ($model) {
                            $checked = in_array($value, $model->roles ?? []) ? 'checked' : '';
                            return "<div class='checkbox'><label><input type='checkbox' name='{$name}[]' value='{$value}' {$checked}> {$label}</label></div>";
                        },
                    ])->label('Vai trò người dùng')->hint('Chọn các vai trò của người dùng này') ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Thông tin cá nhân</h3>
                </div>
                <div class="card-body">
                    <?php if (!$model->isNewRecord && $model->avatar): ?>
                        <div class="form-group">
                            <label>Ảnh đại diện hiện tại</label>
                            <div>
                                <img src="<?= Yii::getAlias('@web/' . $model->avatar) ?>" style="max-width: 150px; max-height: 150px; border-radius: 50%;" alt="Avatar" class="img-thumbnail">
                            </div>
                        </div>
                    <?php endif; ?>

                    <?= $form->field($model, 'avatar')->fileInput(['accept' => 'image/*']) ?>

                    <?= $form->field($profile, 'address')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($profile, 'city')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($profile, 'country')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($profile, 'birthday')->input('date') ?>

                    <?= $form->field($profile, 'position')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($profile, 'department')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($profile, 'hire_date')->input('date') ?>

                    <?= $form->field($profile, 'identity_card')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($profile, 'notes')->textarea(['rows' => 3]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group mt-3">
        <?= Html::submitButton('<i class="fas fa-save"></i> Lưu', ['class' => 'btn btn-success']) ?>
        <?= Html::a('<i class="fas fa-times"></i> Hủy', ['index'], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
// Register script for Select2
$this->registerJs("
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
");
?>