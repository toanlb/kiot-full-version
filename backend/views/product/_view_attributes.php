<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Product */

$attributeValues = $model->attributeValues;
?>

<div class="product-attributes mt-4">
    <?php if (empty($attributeValues)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Sản phẩm này chưa có thuộc tính nào.
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 30%">Thuộc tính</th>
                                <th>Giá trị</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attributeValues as $attributeValue): ?>
                                <tr>
                                    <td><?= Html::encode($attributeValue->attribute->name) ?></td>
                                    <td><?= Html::encode($attributeValue->value) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>