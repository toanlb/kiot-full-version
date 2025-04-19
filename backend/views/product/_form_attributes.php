<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\Product */
/* @var $form yii\widgets\ActiveForm */
/* @var $attributes common\models\ProductAttribute[] */
/* @var $attributeValues array */
?>

<div class="product-form-attributes">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Thuộc tính sản phẩm</h3>
                    <div class="card-tools">
                        <?= Html::a('<i class="fas fa-plus"></i> Thêm thuộc tính mới', ['product-attribute/create'], [
                            'class' => 'btn btn-sm btn-success',
                            'target' => '_blank',
                            'title' => 'Thêm thuộc tính mới vào hệ thống'
                        ]) ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($attributes)): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> Chưa có thuộc tính nào được định nghĩa. 
                            Hãy <?= Html::a('thêm thuộc tính mới', ['product-attribute/create'], ['target' => '_blank']) ?> trước khi gán thuộc tính cho sản phẩm.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Nhập giá trị cho các thuộc tính áp dụng cho sản phẩm này. Bỏ trống nếu thuộc tính không áp dụng.
                        </div>
                        
                        <div class="row">
                            <?php foreach ($attributes as $attribute): ?>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="form-group">
                                        <label for="attribute-<?= $attribute->id ?>"><?= Html::encode($attribute->name) ?></label>
                                        <?= Html::textInput(
                                            "ProductAttributeValue[{$attribute->id}]", 
                                            isset($attributeValues[$attribute->id]) ? $attributeValues[$attribute->id] : '', 
                                            [
                                                'class' => 'form-control',
                                                'id' => "attribute-{$attribute->id}",
                                                'placeholder' => "Nhập {$attribute->name}"
                                            ]
                                        ) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>