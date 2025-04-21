<?php

use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $permissions yii\rbac\Permission[] */

$this->title = 'Permissions';
$this->params['breadcrumbs'][] = ['label' => 'Role Management', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerJs("
    $('#search-permissions').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#permissions-table tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
");

?>
<div class="rbac-permissions">

    <div class="row mb-4">
        <div class="col-md-6">
            <h1><?= Html::encode($this->title) ?></h1>
        </div>
        <div class="col-md-6 text-right">
            <?= Html::a('<i class="fa fa-arrow-left"></i> Back to Roles', ['index'], ['class' => 'btn btn-secondary']) ?>
            <?= Html::a('<i class="fa fa-search"></i> Scan New Permissions', ['scan'], ['class' => 'btn btn-primary']) ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6">
                    <h3 class="card-title">Available Permissions</h3>
                </div>
                <div class="col-md-6">
                    <input type="text" id="search-permissions" class="form-control" placeholder="Search permissions...">
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php Pjax::begin(); ?>
            
            <div class="table-responsive">
                <table class="table table-striped table-bordered" id="permissions-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Type</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($permissions as $permission): ?>
                        <tr>
                            <td><code><?= Html::encode($permission->name) ?></code></td>
                            <td><?= Html::encode($permission->description) ?></td>
                            <td>
                                <?php if (strpos($permission->name, '/*') !== false): ?>
                                    <span class="badge badge-primary">Controller</span>
                                <?php else: ?>
                                    <span class="badge badge-info">Action</span>
                                <?php endif; ?>
                            </td>
                            <td><?= Yii::$app->formatter->asDatetime($permission->createdAt) ?></td>
                            <td><?= Yii::$app->formatter->asDatetime($permission->updatedAt) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php Pjax::end(); ?>
        </div>
    </div>
</div>