<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ProductAttribute */

$this->title = 'Cập nhật thuộc tính: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Thuộc tính sản phẩm', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Cập nhật';
?>
<div class="product-attribute-update">
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