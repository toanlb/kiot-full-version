<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\RbacAssignment */

$this->title = 'Create Role';
$this->params['breadcrumbs'][] = ['label' => 'Role Management', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="rbac-create-role">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Role Information</h3>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin(); ?>
                    
                    <div class="form-group required">
                        <label class="control-label">Name</label>
                        <input type="text" class="form-control" name="name" required placeholder="Enter role name">
                        <small class="form-text text-muted">Role name should be unique and use lowercase alphanumeric characters only.</small>
                    </div>
                    
                    <div class="form-group required">
                        <label class="control-label">Description</label>
                        <input type="text" class="form-control" name="description" required placeholder="Enter role description">
                        <small class="form-text text-muted">Provide a clear description of what this role represents.</small>
                    </div>
                    
                    <div class="form-group">
                        <?= Html::submitButton('<i class="fa fa-plus"></i> Create Role', ['class' => 'btn btn-success']) ?>
                        <?= Html::a('<i class="fa fa-times"></i> Cancel', ['index'], ['class' => 'btn btn-secondary']) ?>
                    </div>
                    
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">About Roles</h3>
                </div>
                <div class="card-body">
                    <p>
                        Roles represent a collection of permissions that define what actions a user can perform 
                        in the system. A user can be assigned one or more roles.
                    </p>
                    <p>
                        After creating a role, you can assign specific permissions to it from the 
                        <strong>Assign Permissions</strong> page.
                    </p>
                    <p>
                        <strong>Examples of common roles:</strong>
                    </p>
                    <ul>
                        <li><strong>admin</strong> - Has full access to all system functions</li>
                        <li><strong>manager</strong> - Can manage products, orders, and staff</li>
                        <li><strong>cashier</strong> - Can create orders and manage customer information</li>
                        <li><strong>warehouse</strong> - Can manage inventory and stock</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>