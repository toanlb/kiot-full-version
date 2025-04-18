<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $title string */
/* @var $tabs array */
/* @var $buttons array|null */
/* @var $activeTab string */
?>

<div class="tab-view">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-window-restore mr-2"></i> <?= Html::encode($title) ?>
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
            <ul class="nav nav-tabs" id="customTab" role="tablist">
                <?php foreach ($tabs as $id => $tab): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= ($activeTab == $id) ? 'active' : '' ?>" 
                           id="<?= $id ?>-tab" 
                           data-toggle="tab" 
                           href="#<?= $id ?>" 
                           role="tab" 
                           aria-controls="<?= $id ?>" 
                           aria-selected="<?= ($activeTab == $id) ? 'true' : 'false' ?>">
                            <?php if (isset($tab['icon'])): ?>
                                <i class="<?= $tab['icon'] ?> mr-1"></i>
                            <?php endif; ?>
                            <?= $tab['label'] ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="tab-content p-3 border-left border-right border-bottom" id="customTabContent">
                <?php foreach ($tabs as $id => $tab): ?>
                    <div class="tab-pane fade <?= ($activeTab == $id) ? 'show active' : '' ?>" 
                         id="<?= $id ?>" 
                         role="tabpanel" 
                         aria-labelledby="<?= $id ?>-tab">
                        <?= $tab['content'] ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>