<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $user common\models\User */

$this->title = 'Lịch sử đăng nhập: ' . $user->full_name;
$this->params['breadcrumbs'][] = ['label' => 'Quản lý người dùng', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $user->full_name, 'url' => ['view', 'id' => $user->id]];
$this->params['breadcrumbs'][] = 'Lịch sử đăng nhập';
?>
<div class="login-history-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Quay lại', ['view', 'id' => $user->id], ['class' => 'btn btn-default']) ?>
    </p>

    <?php Pjax::begin(); ?>

    <div class="box">
        <div class="box-body table-responsive">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    [
                        'attribute' => 'login_time',
                        'format' => 'datetime',
                    ],
                    [
                        'attribute' => 'logout_time',
                        'format' => 'datetime',
                        'value' => function ($model) {
                            return $model->logout_time ?: '(Chưa đăng xuất)';
                        }
                    ],
                    'ip_address',
                    [
                        'attribute' => 'user_agent',
                        'format' => 'ntext',
                        'contentOptions' => ['style' => 'max-width: 300px; overflow: hidden; text-overflow: ellipsis;'],
                    ],
                    [
                        'attribute' => 'success',
                        'format' => 'boolean',
                        'contentOptions' => function ($model) {
                            return ['class' => $model->success ? 'success' : 'danger'];
                        }
                    ],
                    [
                        'attribute' => 'failure_reason',
                        'visible' => function ($model, $key, $index, $column) {
                            return !$model->success;
                        }
                    ],
                ],
            ]); ?>
        </div>
    </div>

    <?php Pjax::end(); ?>

</div>