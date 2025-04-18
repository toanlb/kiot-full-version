<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\User */
/* @var $profile common\models\UserProfile */
/* @var $warehouses common\models\Warehouse[] */
/* @var $availableRoles array */
/* @var $assignedWarehouses array */

$this->title = 'Thêm người dùng mới';
$this->params['breadcrumbs'][] = ['label' => 'Quản lý người dùng', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-create">

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
        </div>
        <div class="card-body">
            <?= $this->render('_form', [
                'model' => $model,
                'profile' => $profile,
                'warehouses' => $warehouses,
                'availableRoles' => $availableRoles,
                'assignedWarehouses' => $assignedWarehouses,
            ]) ?>
        </div>
    </div>

</div>