<?php
use yii\helpers\Html;
use common\models\Shift;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $activeShift common\models\Shift */

$this->title = 'Quản lý ca làm việc';
$this->params['breadcrumbs'][] = $this->title;

// Chuẩn bị nút extra
$extraButtons = [];
if ($activeShift) {
    $extraButtons[] = '<span class="badge badge-success mr-2">Ca #' . $activeShift->id . ' đang mở</span>';
    $extraButtons[] = Html::a('<i class="fas fa-lock"></i> Đóng ca', ['close', 'id' => $activeShift->id], ['class' => 'btn btn-warning btn-sm mr-2']);
} else {
    $extraButtons[] = Html::a('<i class="fas fa-play"></i> Mở ca làm việc', ['open'], ['class' => 'btn btn-success btn-sm']);
}

// Chuẩn bị cấu hình cột
$columns = [
    ['class' => 'yii\grid\SerialColumn'],
    [
        'attribute' => 'start_time',
        'format' => 'datetime',
    ],
    [
        'attribute' => 'end_time',
        'format' => 'raw',
        'value' => function ($model) {
            return $model->end_time ? Yii::$app->formatter->asDatetime($model->end_time) :
                '<span class="badge badge-info">Đang mở</span>';
        },
    ],
    [
        'attribute' => 'status',
        'format' => 'raw',
        'value' => function ($model) {
            return $model->status == Shift::STATUS_OPEN ? 
                '<span class="badge badge-success">Mở</span>' : 
                '<span class="badge badge-secondary">Đóng</span>';
        },
    ],
    [
        'attribute' => 'total_sales',
        'format' => 'currency',
    ],
    [
        'attribute' => 'expected_amount',
        'format' => 'currency',
    ],
    [
        'class' => 'yii\grid\ActionColumn',
        'template' => '{view} {close} {detail} {report}',
        'buttons' => [
            'view' => function ($url, $model) {
                return Html::a('<i class="fas fa-eye"></i>', $url, [
                    'title' => 'Xem',
                    'class' => 'btn btn-primary btn-sm mr-1',
                    'data-toggle' => 'tooltip',
                ]);
            },
            'close' => function ($url, $model) {
                if ($model->status == Shift::STATUS_OPEN) {
                    return Html::a('<i class="fas fa-lock"></i>', ['close', 'id' => $model->id], [
                        'title' => 'Đóng ca',
                        'class' => 'btn btn-warning btn-sm mr-1',
                        'data-toggle' => 'tooltip',
                    ]);
                }
                return '';
            },
            'detail' => function ($url, $model) {
                return Html::a('<i class="fas fa-list"></i>', ['detail', 'id' => $model->id], [
                    'title' => 'Chi tiết',
                    'class' => 'btn btn-info btn-sm mr-1',
                    'data-toggle' => 'tooltip',
                ]);
            },
            'report' => function ($url, $model) {
                if ($model->status == Shift::STATUS_CLOSED) {
                    return Html::a('<i class="fas fa-chart-bar"></i>', ['report', 'id' => $model->id], [
                        'title' => 'Báo cáo',
                        'class' => 'btn btn-success btn-sm',
                        'data-toggle' => 'tooltip',
                    ]);
                }
                return '';
            },
        ],
    ],
];

// Render view với layout chung
echo $this->render('/layouts/_list_layout', [
    'title' => $this->title,
    'dataProvider' => $dataProvider,
    'extraButtons' => $extraButtons,
    'columns' => $columns,
]);

// Thêm JavaScript để khởi tạo tooltip
$this->registerJs("
    $(function () {
        $('[data-toggle=\"tooltip\"]').tooltip();
    });
");
?>