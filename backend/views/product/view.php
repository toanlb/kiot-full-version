<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use yii\data\ArrayDataProvider;
use kartik\tabs\TabsX;


/* @var $this yii\web\View */
/* @var $model common\models\Product */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Quản lý Sản phẩm', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="product-view">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
            <div class="card-tools">
                <?= Html::a('<i class="fas fa-edit"></i> Cập nhật', ['update', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm']) ?>
                <?= Html::a('<i class="fas fa-trash"></i> Xóa', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger btn-sm',
                    'data' => [
                        'confirm' => 'Bạn có chắc muốn xóa sản phẩm này?',
                        'method' => 'post',
                    ],
                ]) ?>
                <?= Html::a('<i class="fas fa-arrow-left"></i> Quay lại', ['index'], ['class' => 'btn btn-default btn-sm']) ?>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="product-info">
                        <h1 class="product-title">
                            <?= Html::encode($model->name) ?>
                            <?php if ($model->status == 0): ?>
                                <span class="badge badge-danger">Vô hiệu hóa</span>
                            <?php endif; ?>
                            <?php if ($model->is_combo): ?>
                                <span class="badge badge-info">Combo/Bộ</span>
                            <?php endif; ?>
                        </h1>
                        <p class="product-code text-muted">
                            Mã sản phẩm: <?= Html::encode($model->code) ?>
                            <?php if ($model->barcode): ?>
                                | Mã vạch: <?= Html::encode($model->barcode) ?>
                            <?php endif; ?>
                        </p>
                        
                        <div class="product-pricing mt-3">
                            <div class="row">
                                <div class="col-sm-6">
                                    <h5>Giá nhập:</h5>
                                    <h4 class="text-primary"><?= Yii::$app->formatter->asCurrency($model->cost_price) ?></h4>
                                </div>
                                <div class="col-sm-6">
                                    <h5>Giá bán:</h5>
                                    <h4 class="text-success"><?= Yii::$app->formatter->asCurrency($model->selling_price) ?></h4>
                                </div>
                            </div>
                        </div>

                        <div class="product-stock mt-3">
                            <h5>Tồn kho:</h5>
                            <div class="row">
                                <?php
                                $totalStock = $model->getTotalStock();
                                $stockClass = $totalStock <= 0 ? 'danger' : ($totalStock <= $model->min_stock ? 'warning' : 'success');
                                ?>
                                <div class="col-sm-6">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-<?= $stockClass ?>"><i class="fas fa-boxes"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Tổng tồn kho</span>
                                            <span class="info-box-number"><?= $totalStock ?> <?= $model->unit->name ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-info"><i class="fas fa-exclamation-triangle"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Tồn kho tối thiểu</span>
                                            <span class="info-box-number"><?= $model->min_stock ?> <?= $model->unit->name ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="product-image text-center">
                        <?php
                        $mainImage = $model->getMainImage()->one();
                        $mainImage = $mainImage ? $mainImage : null;
                        if ($model->is_combo) {
                            $mainImage = $model->getComboImage()->one();
                        }
                        if ($mainImage) {
                            echo Html::img(Yii::$app->urlManager->createUrl('/' . $mainImage->image), [
                                'class' => 'img-fluid',
                                'alt' => $model->name,
                                'style' => 'max-height: 300px; border-radius: 5px;'
                            ]);
                        } else {
                            echo Html::img('@web/img/no-image.png', [
                                'class' => 'img-fluid',
                                'alt' => 'No Image',
                                'style' => 'max-height: 300px; border-radius: 5px;'
                            ]);
                        }
                        ?>
                    </div>
                    
                    <?php if (count($model->productImages) > 1): ?>
                    <div class="product-thumbnails d-flex justify-content-center flex-wrap mt-2">
                        <?php foreach ($model->productImages as $image): ?>
                            <?= Html::img(Yii::$app->urlManager->createUrl('/' . $image->image), [
                                'class' => 'img-thumbnail m-1',
                                'style' => 'width: 60px; height: 60px; object-fit: cover;' . ($image->is_main ? 'border-color: #007bff;' : '')
                            ]) ?>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php
            // Product details tabs
            $items = [
                [
                    'label' => '<i class="fas fa-info-circle"></i> Thông tin chi tiết',
                    'content' => $this->render('_view_details', ['model' => $model]),
                    'active' => true
                ],
                [
                    'label' => '<i class="fas fa-list-ul"></i> Thuộc tính sản phẩm',
                    'content' => $this->render('_view_attributes', ['model' => $model]),
                    'headerOptions' => ['class' => 'product-attributes-tab'],
                    'visible' => !empty($model->productAttributeValues),
                ],
                [
                    'label' => '<i class="fas fa-cubes"></i> Thành phần combo',
                    'content' => $this->render('_view_combo', ['model' => $model]),
                    'headerOptions' => ['class' => 'product-combo-tab'],
                    'visible' => $model->is_combo,
                ],
                [
                    'label' => '<i class="fas fa-dollar-sign"></i> Lịch sử giá',
                    'content' => $this->render('_view_price_history', ['model' => $model]),
                ],
                [
                    'label' => '<i class="fas fa-exchange-alt"></i> Lịch sử kho',
                  
                ],
            ];
            
            echo TabsX::widget([
                'items' => $items,
                'position' => TabsX::POS_ABOVE,
                'encodeLabels' => false,
                'pluginOptions' => [
                    'enableCache' => false,
                ],
                'bsVersion' => '4.x', // 👈 thêm dòng này để tương thích Bootstrap 4
            ]);
            ?>
        </div>
    </div>
</div>