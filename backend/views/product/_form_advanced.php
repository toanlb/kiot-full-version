<?php
use yii\helpers\Html;
use kartik\number\NumberControl;

/* @var $this yii\web\View */
/* @var $model common\models\Product */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="product-form-advanced">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Thông tin bổ sung</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-outline card-secondary">
                                <div class="card-header">
                                    <h5 class="card-title">Thông tin sản phẩm</h5>
                                </div>
                                <div class="card-body">
                                    <?= $form->field($model, 'weight')->widget(NumberControl::classname(), [
                                        'maskedInputOptions' => [
                                            'suffix' => ' kg',
                                            'allowMinus' => false,
                                            'digits' => 3,
                                            'groupSeparator' => '.',
                                            'radixPoint' => ','
                                        ],
                                        'displayOptions' => ['class' => 'form-control'],
                                        'saveInputContainer' => ['class' => 'kv-saved-cont']
                                    ]) ?>

                                    <?= $form->field($model, 'dimension')->textInput(['maxlength' => true, 'placeholder' => 'VD: 10x20x30 cm']) ?>

                                    <?= $form->field($model, 'warranty_period')->textInput(['type' => 'number', 'min' => '0', 'placeholder' => 'Nhập số tháng bảo hành']) ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card card-outline card-secondary">
                                <div class="card-header">
                                    <h5 class="card-title">Thông tin lưu trữ</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (!$model->isNewRecord): ?>
                                        <div class="form-group">
                                            <label>Ngày tạo</label>
                                            <input type="text" class="form-control" value="<?= Yii::$app->formatter->asDatetime($model->created_at) ?>" readonly>
                                        </div>

                                        <div class="form-group">
                                            <label>Cập nhật lần cuối</label>
                                            <input type="text" class="form-control" value="<?= Yii::$app->formatter->asDatetime($model->updated_at) ?>" readonly>
                                        </div>

                                        <?php if ($model->created_by): ?>
                                            <div class="form-group">
                                                <label>Người tạo</label>
                                                <input type="text" class="form-control" value="<?= $model->createdBy ? $model->createdBy->username : 'N/A' ?>" readonly>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($model->updated_by): ?>
                                            <div class="form-group">
                                                <label>Người cập nhật</label>
                                                <input type="text" class="form-control" value="<?= $model->updatedBy ? $model->updatedBy->username : 'N/A' ?>" readonly>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i> Thông tin lưu trữ sẽ được cập nhật sau khi tạo sản phẩm.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="card card-outline card-secondary">
                                <div class="card-header">
                                    <h5 class="card-title">Thông tin khác</h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> Bạn có thể thêm ghi chú hoặc thông tin bổ sung khác về sản phẩm ở đây.
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="product-notes">Ghi chú nội bộ</label>
                                        <textarea id="product-notes" class="form-control" name="Product[notes]" rows="3" placeholder="Nhập ghi chú nội bộ về sản phẩm"><?= Html::encode($model->notes ?? '') ?></textarea>
                                        <small class="form-text text-muted">Ghi chú này chỉ hiển thị trong nội bộ, không hiển thị cho khách hàng.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>