<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\User */
/* @var $allRoles array */
/* @var $userRoles array */

$this->title = 'Phân quyền cho người dùng: ' . $model->full_name;
$this->params['breadcrumbs'][] = ['label' => 'Quản lý người dùng', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->full_name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Phân quyền';
?>
<div class="user-assign-role">

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
        </div>
        <div class="card-body">
            <?php $form = ActiveForm::begin(); ?>

            <div class="form-group">
                <label>Chọn vai trò cho người dùng</label>
                <div class="role-list">
                    <?php foreach ($allRoles as $role): ?>
                        <div class="form-check">
                            <input type="checkbox" 
                                   class="form-check-input" 
                                   id="role-<?= $role->name ?>" 
                                   name="roles[]" 
                                   value="<?= $role->name ?>"
                                   <?= isset($userRoles[$role->name]) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="role-<?= $role->name ?>">
                                <strong><?= $role->name ?></strong>
                                <?php if (!empty($role->description)): ?>
                                    <br><small class="text-muted"><?= $role->description ?></small>
                                <?php endif; ?>
                            </label>
                        </div>
                        <hr>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group">
                <?= Html::submitButton('<i class="fas fa-save"></i> Lưu thay đổi', ['class' => 'btn btn-success']) ?>
                <?= Html::a('<i class="fas fa-times"></i> Hủy', ['view', 'id' => $model->id], ['class' => 'btn btn-secondary']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>

</div>