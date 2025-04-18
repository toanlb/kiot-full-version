<?php
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model model */
/* @var $title string */
/* @var $attributes array */
/* @var $buttons array|null */
/* @var $extraContent string|null */
?>

<div class="detail-view">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-eye mr-2"></i> <?= Html::encode($title) ?>
            </h3>
            <div class="card-tools">
                <?php if (isset($buttons) && is_array($buttons)): ?>
                    <?php foreach ($buttons as $button): ?>
                        <?= $button ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="<?= isset($secondAttributes) ? 'col-md-6' : 'col-md-12' ?>">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => $attributes,
                        'options' => ['class' => 'table table-striped table-bordered detail-view'],
                    ]) ?>
                </div>
                
                <?php if (isset($secondAttributes)): ?>
                <div class="col-md-6">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => $secondAttributes,
                        'options' => ['class' => 'table table-striped table-bordered detail-view'],
                    ]) ?>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if (isset($extraContent)): ?>
                <div class="mt-4">
                    <?= $extraContent ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>