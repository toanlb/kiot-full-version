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

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
            <div class="card-tools">
                <?= Html::a('<i class="fas fa-plus"></i> Thêm người dùng', ['create'], ['class' => 'btn btn-success btn-sm']) ?>
            </div>
        </div>
        <div class="card-body p-0">
            <?php Pjax::begin(); ?>

            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'layout' => "{items}\n{pager}",
                'tableOptions' => ['class' => 'table table-striped table-bordered'],
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'username',
                    'email:email',
                    'full_name',
                    'phone',
                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'value' => function ($model) {
                            $statusLabels = [
                                User::STATUS_INACTIVE => '<span class="badge badge-warning">Chưa kích hoạt</span>',
                                User::STATUS_ACTIVE => '<span class="badge badge-success">Đã kích hoạt</span>',
                                User::STATUS_DELETED => '<span class="badge badge-danger">Đã xóa</span>',
                            ];
                            return $statusLabels[$model->status] ?? '<span class="badge badge-secondary">Unknown</span>';
                        },
                    ],
                    [
                        'attribute' => 'last_login_at',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return $model->last_login_at 
                                ? Yii::$app->formatter->asDatetime(strtotime($model->last_login_at), 'php:d/m/Y H:i:s') 
                                : '<span class="text-muted">Chưa đăng nhập</span>';
                        },
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view} {update} {assign-role} {login-history} {delete}',
                        'buttons' => [
                            'view' => function ($url, $model) {
                                return Html::a('<i class="fas fa-eye"></i>', $url, [
                                    'title' => 'Xem chi tiết',
                                    'class' => 'btn btn-sm btn-primary',
                                    'data-pjax' => '0',
                                ]);
                            },
                            'update' => function ($url, $model) {
                                return Html::a('<i class="fas fa-edit"></i>', $url, [
                                    'title' => 'Cập nhật',
                                    'class' => 'btn btn-sm btn-info',
                                    'data-pjax' => '0',
                                ]);
                            },
                            'assign-role' => function ($url, $model) {
                                return Html::a('<i class="fas fa-user-tag"></i>', ['assign-role', 'id' => $model->id], [
                                    'title' => 'Phân quyền',
                                    'class' => 'btn btn-sm btn-warning',
                                    'data-pjax' => '0',
                                ]);
                            },
                            'login-history' => function ($url, $model) {
                                return Html::a('<i class="fas fa-history"></i>', ['login-history', 'id' => $model->id], [
                                    'title' => 'Lịch sử đăng nhập',
                                    'class' => 'btn btn-sm btn-secondary',
                                    'data-pjax' => '0',
                                ]);
                            },
                            'delete' => function ($url, $model) {
                                return $model->id != Yii::$app->user->id ? Html::a('<i class="fas fa-trash"></i>', $url, [
                                    'title' => 'Xóa',
                                    'class' => 'btn btn-sm btn-danger',
                                    'data-confirm' => 'Bạn có chắc muốn xóa người dùng này?',
                                    'data-method' => 'post',
                                    'data-pjax' => '0',
                                ]) : '';
                            },
                        ],
                        'contentOptions' => ['style' => 'width: 220px; text-align: center'],
                    ],
                ],
            ]); ?>

            <?php Pjax::end(); ?>
        </div>
    </div>
</div>