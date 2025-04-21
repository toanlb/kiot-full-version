<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $roles yii\rbac\Role[] */

$this->title = 'Role Management';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="rbac-index">

    <div class="row mb-4">
        <div class="col-md-6">
            <h1><?= Html::encode($this->title) ?></h1>
        </div>
        <div class="col-md-6 text-right">
            <?= Html::a('<i class="fa fa-plus"></i> Create Role', ['create-role'], ['class' => 'btn btn-success']) ?>
            <?= Html::a('<i class="fa fa-search"></i> Scan Permissions', ['scan'], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('<i class="fa fa-list"></i> View Permissions', ['permissions'], ['class' => 'btn btn-info']) ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Available Roles</h3>
        </div>
        <div class="card-body">
            <?php Pjax::begin(); ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roles as $role): ?>
                        <tr>
                            <td><?= Html::encode($role->name) ?></td>
                            <td><?= Html::encode($role->description) ?></td>
                            <td><?= Yii::$app->formatter->asDatetime($role->createdAt) ?></td>
                            <td><?= Yii::$app->formatter->asDatetime($role->updatedAt) ?></td>
                            <td>
                                <?= Html::a('<i class="fa fa-edit"></i>', ['update-role', 'name' => $role->name], ['class' => 'btn btn-sm btn-primary', 'title' => 'Update Role']) ?>
                                <?= Html::a('<i class="fa fa-key"></i>', ['assign', 'name' => $role->name], ['class' => 'btn btn-sm btn-info', 'title' => 'Assign Permissions']) ?>
                                <?= Html::a('<i class="fa fa-trash"></i>', ['delete-role', 'name' => $role->name], [
                                    'class' => 'btn btn-sm btn-danger',
                                    'title' => 'Delete Role',
                                    'data' => [
                                        'confirm' => 'Are you sure you want to delete this role?',
                                        'method' => 'post',
                                    ],
                                ]) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php Pjax::end(); ?>
        </div>
    </div>
</div>