<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\CustomerGroup */

$this->title = 'Tạo nhóm khách hàng';
$this->params['breadcrumbs'][] = ['label' => 'Nhóm khách hàng', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="customer-group-create">

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