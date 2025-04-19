<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ProductUnit */

$this->title = 'Tạo mới đơn vị tính';
$this->params['breadcrumbs'][] = ['label' => 'Đơn vị tính', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-unit-create">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
        </div>
        <div class="card-body">
            <?= $this->render('_form', [
                'model' => $model,
            ]) ?>
        </div>
    </div>
</div>