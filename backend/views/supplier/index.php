<!-- View index.php -->
<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap4\Modal;
use kartik\select2\Select2;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $searchModel common\models\SupplierSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Quản lý nhà cung cấp';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="supplier-index card">
    <div class="card-header">
        <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
        <div class="card-tools">
            <?= Html::a('<i class="fas fa-plus"></i> Thêm nhà cung cấp', ['create'], ['class' => 'btn btn-success btn-sm']) ?>
            <?= Html::a('<i class="fas fa-file-excel"></i> Xuất Excel', ['export'], ['class' => 'btn btn-primary btn-sm']) ?>
            <?= Html::a('<i class="fas fa-file-import"></i> Nhập Excel', ['import'], ['class' => 'btn btn-warning btn-sm', 'data-toggle' => 'modal', 'data-target' => '#importModal']) ?>
        </div>
    </div>

    <div class="card-body">
        <?php Pjax::begin(); ?>
        
        <div class="search-form">
            <?= $this->render('_search', ['model' => $searchModel]); ?>
        </div>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'layout' => "{summary}\n{items}\n<div class='card-footer clearfix'>{pager}</div>",
            'options' => ['class' => 'grid-view table-responsive'],
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],

                'code',
                'name',
                'phone',
                [
                    'attribute' => 'debt_amount',
                    'format' => 'currency',
                ],
                [
                    'attribute' => 'status',
                    'format' => 'raw',
                    'value' => function ($model) {
                        return $model->getStatusLabel();
                    }
                ],
                [
                    'attribute' => 'created_at',
                    'format' => 'datetime',
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{view} {update} {delete} {products}',
                    'buttons' => [
                        'products' => function ($url, $model, $key) {
                            return Html::a('<i class="fas fa-boxes"></i>', ['product', 'id' => $model->id], [
                                'title' => 'Quản lý sản phẩm',
                                'data-toggle' => 'tooltip',
                                'class' => 'btn btn-sm btn-info',
                            ]);
                        },
                        'view' => function ($url, $model, $key) {
                            return Html::a('<i class="fas fa-eye"></i>', ['view', 'id' => $model->id], [
                                'title' => 'Xem',
                                'data-toggle' => 'tooltip',
                                'class' => 'btn btn-sm btn-primary',
                            ]);
                        },
                        'update' => function ($url, $model, $key) {
                            return Html::a('<i class="fas fa-edit"></i>', ['update', 'id' => $model->id], [
                                'title' => 'Cập nhật',
                                'data-toggle' => 'tooltip',
                                'class' => 'btn btn-sm btn-warning',
                            ]);
                        },
                        'delete' => function ($url, $model, $key) {
                            return Html::a('<i class="fas fa-trash"></i>', ['delete', 'id' => $model->id], [
                                'title' => 'Xóa',
                                'data-toggle' => 'tooltip',
                                'class' => 'btn btn-sm btn-danger',
                                'data' => [
                                    'confirm' => 'Bạn có chắc chắn muốn xóa nhà cung cấp này?',
                                    'method' => 'post',
                                ],
                            ]);
                        },
                    ],
                    'contentOptions' => ['class' => 'text-center'],
                ],
            ],
        ]); ?>

        <?php Pjax::end(); ?>
    </div>
</div>

<!-- Modal Import -->
<?php Modal::begin([
    'title' => '<h4>Nhập danh sách nhà cung cấp từ Excel</h4>',
    'id' => 'importModal',
    'size' => Modal::SIZE_LARGE,
]); ?>

<form action="<?= Url::to(['import']) ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">
    
    <div class="form-group">
        <label for="excelFile">Chọn file Excel</label>
        <input type="file" name="excelFile" id="excelFile" class="form-control" required accept=".xlsx, .xls">
        <small class="form-text text-muted">Chỉ chấp nhận file Excel (.xlsx, .xls). <a href="<?= Url::to(['download-template']) ?>">Tải về file mẫu</a></small>
    </div>
    
    <div class="form-group">
        <button type="submit" class="btn btn-success">Nhập dữ liệu</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
    </div>
</form>

<?php Modal::end(); ?>