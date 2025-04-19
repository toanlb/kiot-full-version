<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ProductAttribute */

$this->title = 'Tạo mới thuộc tính sản phẩm';
$this->params['breadcrumbs'][] = ['label' => 'Thuộc tính sản phẩm', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-attribute-create">
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