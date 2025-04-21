<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\RbacAssignment */
/* @var $role yii\rbac\Role */

$this->title = 'Update Role: ' . $role->name;
$this->params['breadcrumbs'][] = ['label' => 'Role Management', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $role->name, 'url' => ['assign', 'name' => $role->name]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="rbac-update-role">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Role Information</h3>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin(); ?>
                    
                    <div class="form-group">
                        <label class="control-label">Name</label>
                        <input type="text" class="form-control" value="<?= Html::encode($role->name) ?>" disabled>
                        <small class="form-text text-muted">Role name cannot be changed after creation.</small>
                    </div>
                    
                    <div class="form-group required">
                        <label class="control-label">Description</label>
                        <input type="text" class="form-control" name="description" required placeholder="Enter role description" value="<?= Html::encode($role->description) ?>">
                    </div>
                    
                    <div class="form-group">
                        <?= Html::submitButton('<i class="fa fa-save"></i> Update Role', ['class' => 'btn btn-success']) ?>
                        <?= Html::a('<i class="fa fa-times"></i> Cancel', ['index'], ['class' => 'btn btn-secondary']) ?>
                        <?= Html::a('<i class="fa fa-key"></i> Manage Permissions', ['assign', 'name' => $role->name], ['class' => 'btn btn-primary']) ?>
                    </div>
                    
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Role Details</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th>Name</th>
                                <td><?= Html::encode($role->name) ?></td>
                            </tr>
                            <tr>
                                <th>Description</th>
                                <td><?= Html::encode($role->description) ?></td>
                            </tr>
                            <tr>
                                <th>Created At</th>
                                <td><?= Yii::$app->formatter->asDatetime($role->createdAt) ?></td>
                            </tr>
                            <tr>
                                <th>Updated At</th>
                                <td><?= Yii::$app->formatter->asDatetime($role->updatedAt) ?></td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <div class="mt-3">
                        <h5>Assigned Permissions</h5>
                        <?php 
                            $permissions = Yii::$app->authManager->getPermissionsByRole($role->name);
                            if (!empty($permissions)) {
                                echo '<div class="table-responsive"><table class="table table-bordered table-sm">';
                                echo '<thead><tr><th>Permission</th><th>Description</th></tr></thead><tbody>';
                                foreach ($permissions as $permission) {
                                    echo '<tr>';
                                    echo '<td><code>' . Html::encode($permission->name) . '</code></td>';
                                    echo '<td>' . Html::encode($permission->description) . '</td>';
                                    echo '</tr>';
                                }
                                echo '</tbody></table></div>';
                            } else {
                                echo '<div class="alert alert-warning">No permissions assigned to this role.</div>';
                            }
                        ?>
                        
                        <?= Html::a('<i class="fa fa-key"></i> Manage Permissions', ['assign', 'name' => $role->name], ['class' => 'btn btn-primary btn-sm mt-2']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>