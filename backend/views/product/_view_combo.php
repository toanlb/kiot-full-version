<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\Product */

$comboItems = $model->productCombos;
?>

<div class="product-combo mt-4">
    <?php if (empty($comboItems)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Sản phẩm combo này chưa có thành phần nào.
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 60px">STT</th>
                                <th style="width: 100px">Hình ảnh</th>
                                <th>Sản phẩm</th>
                                <th style="width: 150px">Số lượng</th>
                                <th style="width: 150px">Đơn giá</th>
                                <th style="width: 150px">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalPrice = 0;
                            foreach ($comboItems as $index => $item): 
                                $product = $item->product;
                                $itemPrice = $product->selling_price * $item->quantity;
                                $totalPrice += $itemPrice;
                            ?>
                                <tr>
                                    <td class="text-center"><?= $index + 1 ?></td>
                                    <td class="text-center">
                                        <?php
                                        $mainImage = $product->getMainImage()->one();
                                        $mainImage = $mainImage ? $mainImage : null;
                                        if ($mainImage) {
                                            echo Html::img(Yii::$app->urlManager->createUrl('/' . $mainImage->image), [
                                                'class' => 'img-thumbnail',
                                                'style' => 'width: 50px; height: 50px; object-fit: cover;',
                                                'alt' => $product->name
                                            ]);
                                        } else {
                                            echo Html::img('@web/img/no-image.png', [
                                                'class' => 'img-thumbnail',
                                                'style' => 'width: 50px; height: 50px; object-fit: cover;',
                                                'alt' => 'No Image'
                                            ]);
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?= Html::a(Html::encode($product->name), ['view', 'id' => $product->id], ['target' => '_blank']) ?>
                                        <div class="small text-muted">Mã: <?= Html::encode($product->code) ?></div>
                                    </td>
                                    <td class="text-center">
                                        <?= $item->quantity ?> <?= $item->unit->name ?>
                                    </td>
                                    <td class="text-right">
                                        <?= Yii::$app->formatter->asCurrency($product->selling_price) ?>
                                    </td>
                                    <td class="text-right">
                                        <?= Yii::$app->formatter->asCurrency($itemPrice) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-right">Tổng giá trị combo:</th>
                                <th class="text-right"><?= Yii::$app->formatter->asCurrency($totalPrice) ?></th>
                            </tr>
                            <tr>
                                <th colspan="5" class="text-right">Giá bán combo:</th>
                                <th class="text-right"><?= Yii::$app->formatter->asCurrency($model->selling_price) ?></th>
                            </tr>
                            <?php if ($model->selling_price < $totalPrice): ?>
                                <tr>
                                    <th colspan="5" class="text-right">Tiết kiệm:</th>
                                    <th class="text-right text-success">
                                        <?= Yii::$app->formatter->asCurrency($totalPrice - $model->selling_price) ?>
                                        (<?= round((($totalPrice - $model->selling_price) / $totalPrice) * 100, 2) ?>%)
                                    </th>
                                </tr>
                            <?php endif; ?>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>