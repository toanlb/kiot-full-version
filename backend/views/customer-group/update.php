<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\CustomerGroup */

$this->title = 'Cập nhật nhóm khách hàng: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Nhóm khách hàng', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Cập nhật';
?>
<div class="customer-group-update">

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
                </div>
                <!-- /.card-header -->
                
                <?= $this->render('_form', [
                    'model' => $model,
                ]) ?>
                
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->

</div>