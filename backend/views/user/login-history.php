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

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
            <div class="card-tools">
                <?= Html::a('<i class="fas fa-arrow-left"></i> Quay lại', ['view', 'id' => $user->id], ['class' => 'btn btn-default btn-sm']) ?>
            </div>
        </div>
        <div class="card-body p-0">
            <?php Pjax::begin(); ?>

            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'layout' => "{summary}\n{items}\n{pager}",
                'tableOptions' => ['class' => 'table table-striped table-bordered'],
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    [
                        'attribute' => 'login_time',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return Yii::$app->formatter->asDatetime($model->login_time, 'php:d/m/Y H:i:s');
                        }
                    ],
                    [
                        'attribute' => 'logout_time',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return $model->logout_time
                                ? Yii::$app->formatter->asDatetime($model->logout_time, 'php:d/m/Y H:i:s')
                                : '<span class="badge badge-warning">Chưa đăng xuất</span>';
                        }
                    ],
                    'ip_address',
                    [
                        'attribute' => 'user_agent',
                        'format' => 'ntext',
                        'contentOptions' => ['style' => 'max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;'],
                    ],
                    [
                        'attribute' => 'success',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return $model->success
                                ? '<span class="badge badge-success">Thành công</span>'
                                : '<span class="badge badge-danger">Thất bại</span>';
                        },
                        'contentOptions' => function ($model) {
                            return ['class' => $model->success ? 'text-success' : 'text-danger'];
                        }
                    ],
                    [
                        'attribute' => 'failure_reason',
                        'visible' => function ($model, $key, $index, $column) {
                            return !$model->success;
                        }
                    ],
                    [
                        'attribute' => 'duration',
                        'label' => 'Thời gian phiên',
                        'format' => 'raw',
                        'value' => function ($model) {
                            if (!$model->logout_time || !$model->success) {
                                return '-';
                            }
                            
                            $loginTime = strtotime($model->login_time);
                            $logoutTime = strtotime($model->logout_time);
                            $duration = $logoutTime - $loginTime;
                            
                            if ($duration < 60) {
                                return $duration . ' giây';
                            } elseif ($duration < 3600) {
                                return floor($duration / 60) . ' phút ' . ($duration % 60) . ' giây';
                            } else {
                                $hours = floor($duration / 3600);
                                $minutes = floor(($duration % 3600) / 60);
                                return $hours . ' giờ ' . $minutes . ' phút';
                            }
                        }
                    ],
                ],
            ]); ?>

            <?php Pjax::end(); ?>
        </div>
    </div>

</div>