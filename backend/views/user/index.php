<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use common\models\User;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Quản lý người dùng';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Thêm người dùng', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'username',
            'email:email',
            'full_name',
            'phone',
            [
                'attribute' => 'status',
                'value' => function ($model) {
                    $statuses = [
                        User::STATUS_INACTIVE => 'Chưa kích hoạt',
                        User::STATUS_ACTIVE => 'Đã kích hoạt',
                    ];
                    return $statuses[$model->status] ?? 'Unknown';
                },
                'filter' => [
                    User::STATUS_INACTIVE => 'Chưa kích hoạt',
                    User::STATUS_ACTIVE => 'Đã kích hoạt',
                ],
            ],
            [
                'attribute' => 'created_at',
                'value' => function ($model) {
                    return date('Y-m-d H:i:s', $model->created_at);
                },
            ],
            [
                'attribute' => 'last_login_at',
                'value' => function ($model) {
                    return $model->last_login_at ? date('Y-m-d H:i:s', strtotime($model->last_login_at)) : 'Chưa đăng nhập';
                },
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{view} {update} {delete} {login-history}',
                'buttons' => [
                    'login-history' => function ($url, $model) {
                        return Html::a('<span class="glyphicon glyphicon-time"></span>', ['login-history', 'id' => $model->id], [
                            'title' => 'Lịch sử đăng nhập',
                            'data-pjax' => '0',
                        ]);
                    }
                ]
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>
</div>