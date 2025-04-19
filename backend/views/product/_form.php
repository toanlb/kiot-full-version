<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model common\models\Product */
/* @var $comboItems array|common\models\ProductCombo[] */
/* @var $form yii\widgets\ActiveForm */
/* @var $categories array */
/* @var $units array */

// Đảm bảo biến mảng comboItems luôn tồn tại
$comboItems = isset($comboItems) ? $comboItems : [];

// Khởi tạo mảng sản phẩm có sẵn để hiển thị trong dropdown
$availableProducts = [];
if (!empty($existingProductList)) {
    $availableProducts = $existingProductList;
}
?>

<div class="product-combo-form">

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Thông tin cơ bản</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'code')->textInput(['maxlength' => true, 'placeholder' => 'Nhập mã sản phẩm']) ?>

                    <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Nhập tên sản phẩm']) ?>

                    <?= $form->field($model, 'category_id')->dropDownList($categories, [
                        'prompt' => 'Chọn danh mục',
                    ]) ?>

                    <?= $form->field($model, 'unit_id')->dropDownList($units, [
                        'prompt' => 'Chọn đơn vị tính',
                    ]) ?>

                    <?= $form->field($model, 'status')->dropDownList([
                        1 => 'Đang kinh doanh',
                        0 => 'Ngừng kinh doanh',
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'cost_price')->textInput(['type' => 'number', 'step' => '0.01']) ?>

                    <?= $form->field($model, 'selling_price')->textInput(['type' => 'number', 'step' => '0.01']) ?>

                    <?= $form->field($model, 'short_description')->textarea(['rows' => 3, 'placeholder' => 'Mô tả ngắn về combo']) ?>

                    <?= $form->field($model, 'description')->textarea(['rows' => 5, 'placeholder' => 'Mô tả chi tiết về combo']) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">Thành phần combo</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Thêm các sản phẩm vào combo. Bạn có thể thêm nhiều sản phẩm khác nhau.
            </div>

            <table class="table table-bordered table-striped" id="combo-items-table">
                <thead>
                    <tr>
                        <th width="5%">#</th>
                        <th width="45%">Sản phẩm</th>
                        <th width="15%">Số lượng</th>
                        <th width="30%">Đơn vị tính</th>
                        <th width="5%"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Hiển thị các sản phẩm đã có trong trường hợp cập nhật
                    $itemCount = 0;
                    if (!empty($comboItems)) {
                        foreach ($comboItems as $index => $item) {
                            $itemCount++;
                            echo '<tr class="combo-item-row">';
                            echo '<td>' . $itemCount . '</td>';
                            
                            // Dropdown sản phẩm
                            echo '<td>';
                            echo Html::dropDownList(
                                "ComboItems[$index][product_id]",
                                $item->product_id,
                                ArrayHelper::map(
                                    \common\models\Product::find()
                                        ->where(['status' => 1, 'is_combo' => 0])
                                        ->andWhere(['!=', 'id', $model->id])
                                        ->all(),
                                    'id',
                                    function ($model) {
                                        return $model->code . ' - ' . $model->name;
                                    }
                                ),
                                ['class' => 'form-control', 'prompt' => 'Chọn sản phẩm']
                            );
                            echo '</td>';
                            
                            // Số lượng
                            echo '<td>';
                            echo Html::textInput(
                                "ComboItems[$index][quantity]",
                                $item->quantity,
                                ['type' => 'number', 'min' => '1', 'class' => 'form-control', 'required' => true]
                            );
                            echo '</td>';
                            
                            // Đơn vị tính
                            echo '<td>';
                            echo Html::dropDownList(
                                "ComboItems[$index][unit_id]",
                                $item->unit_id,
                                $units,
                                ['class' => 'form-control', 'prompt' => 'Chọn đơn vị tính']
                            );
                            echo '</td>';
                            
                            // Nút xóa
                            echo '<td>';
                            echo Html::button('<i class="fas fa-trash"></i>', [
                                'class' => 'btn btn-danger btn-sm btn-delete-row',
                                'onclick' => 'this.closest("tr").remove(); updateRowNumbers();'
                            ]);
                            echo '</td>';
                            
                            echo '</tr>';
                        }
                    }
                    
                    // Dòng mẫu để thêm mới
                    if ($itemCount == 0) {
                        $index = 0;
                        echo '<tr class="combo-item-row">';
                        echo '<td>1</td>';
                        
                        // Dropdown sản phẩm
                        echo '<td>';
                        echo Html::dropDownList(
                            "ComboItems[$index][product_id]",
                            '',
                            ArrayHelper::map(
                                \common\models\Product::find()
                                    ->where(['status' => 1, 'is_combo' => 0])
                                    ->andWhere(['!=', 'id', $model->id])
                                    ->all(),
                                'id',
                                function ($model) {
                                    return $model->code . ' - ' . $model->name;
                                }
                            ),
                            ['class' => 'form-control', 'prompt' => 'Chọn sản phẩm']
                        );
                        echo '</td>';
                        
                        // Số lượng
                        echo '<td>';
                        echo Html::textInput(
                            "ComboItems[$index][quantity]",
                            '1',
                            ['type' => 'number', 'min' => '1', 'class' => 'form-control', 'required' => true]
                        );
                        echo '</td>';
                        
                        // Đơn vị tính
                        echo '<td>';
                        echo Html::dropDownList(
                            "ComboItems[$index][unit_id]",
                            '',
                            $units,
                            ['class' => 'form-control', 'prompt' => 'Chọn đơn vị tính']
                        );
                        echo '</td>';
                        
                        // Nút xóa
                        echo '<td>';
                        echo Html::button('<i class="fas fa-trash"></i>', [
                            'class' => 'btn btn-danger btn-sm btn-delete-row',
                            'onclick' => 'this.closest("tr").remove(); updateRowNumbers();'
                        ]);
                        echo '</td>';
                        
                        echo '</tr>';
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5">
                            <?= Html::button('<i class="fas fa-plus"></i> Thêm sản phẩm', [
                                'class' => 'btn btn-success',
                                'id' => 'btn-add-row',
                                'onclick' => 'addNewRow()'
                            ]) ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="form-group mt-4">
        <?= Html::submitButton('<i class="fas fa-save"></i> Lưu', ['class' => 'btn btn-success']) ?>
        <?= Html::a('<i class="fas fa-arrow-left"></i> Quay lại', ['index'], ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<script>
// Script đơn giản để thêm dòng và cập nhật số thứ tự
function addNewRow() {
    var tbody = document.querySelector('#combo-items-table tbody');
    var rowCount = tbody.querySelectorAll('tr').length;
    
    // Lấy template từ dòng đầu tiên
    var template = tbody.querySelector('tr').cloneNode(true);
    
    // Cập nhật các ID và name
    template.querySelector('td:first-child').textContent = rowCount + 1;
    
    var inputs = template.querySelectorAll('select, input');
    inputs.forEach(function(input) {
        var name = input.getAttribute('name');
        if (name) {
            // Cập nhật index trong tên
            input.setAttribute('name', name.replace(/\[\d+\]/, '[' + rowCount + ']'));
            
            // Xóa giá trị đã chọn
            if (input.tagName === 'SELECT') {
                input.selectedIndex = 0;
            } else if (input.type === 'number') {
                input.value = '1';
            }
        }
    });
    
    // Thêm dòng vào bảng
    tbody.appendChild(template);
}

function updateRowNumbers() {
    var rows = document.querySelectorAll('#combo-items-table tbody tr');
    
    rows.forEach(function(row, index) {
        // Cập nhật số thứ tự
        row.querySelector('td:first-child').textContent = index + 1;
        
        // Cập nhật các index trong name attribute
        var inputs = row.querySelectorAll('select, input');
        inputs.forEach(function(input) {
            var name = input.getAttribute('name');
            if (name) {
                input.setAttribute('name', name.replace(/\[\d+\]/, '[' + index + ']'));
            }
        });
    });
}
</script>