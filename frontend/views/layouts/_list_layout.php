<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $title string */
/* @var $createButton array|null */
/* @var $extraButtons array|null */
/* @var $columns array */
/* @var $filterModel model|null */
?>

<div class="list-view">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list mr-2"></i> <?= Html::encode($title) ?>
            </h3>
            <div class="card-tools">
                <?php if (isset($extraButtons) && is_array($extraButtons)): ?>
                    <?php foreach ($extraButtons as $button): ?>
                        <?= $button ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <?php if (isset($createButton)): ?>
                    <?= Html::a('<i class="fas fa-plus"></i> ' . ($createButton['label'] ?? 'Tạo mới'), 
                        $createButton['url'], 
                        ['class' => 'btn btn-success btn-sm']) ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <?php Pjax::begin(['id' => 'pjax-grid']); ?>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $filterModel ?? null,
                'columns' => $columns,
                'summary' => 'Hiển thị <b>{begin}-{end}</b> trong tổng số <b>{totalCount}</b> bản ghi',
                'tableOptions' => ['class' => 'table table-striped table-bordered table-hover'],
                'pager' => [
                    'options' => ['class' => 'pagination pagination-sm'],
                    'linkContainerOptions' => ['class' => 'page-item'],
                    'linkOptions' => ['class' => 'page-link'],
                    'disabledListItemSubTagOptions' => ['tag' => 'a', 'class' => 'page-link']
                ],
            ]); ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
</div>