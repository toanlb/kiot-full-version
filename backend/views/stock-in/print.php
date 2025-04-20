<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\StockIn */
/* @var $details common\models\StockInDetail[] */

$this->title = $model->code;
$this->registerCss('
    body {
        font-size: 14px;
        font-family: "Roboto", "Helvetica Neue", Helvetica, Arial, sans-serif;
    }
    .print-header, .print-footer {
        text-align: center;
        margin-bottom: 20px;
    }
    .print-header h1 {
        font-size: 22px;
        font-weight: bold;
        margin: 5px 0;
    }
    .print-header p {
        margin: 2px 0;
    }
    .company-info {
        text-align: center;
        margin-bottom: 20px;
    }
    .stock-in-info {
        width: 100%;
        margin-bottom: 20px;
    }
    .stock-in-info th {
        text-align: left;
        padding: 5px 10px 5px 0;
        font-weight: normal;
        width: 200px;
    }
    .stock-in-info td {
        padding: 5px 0;
    }
    .items-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    .items-table th {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: center;
        background-color: #f4f4f4;
    }
    .items-table td {
        border: 1px solid #ddd;
        padding: 8px;
    }
    .text-right {
        text-align: right;
    }
    .text-center {
        text-align: center;
    }
    .signature-block {
        width: 100%;
        margin-top: 30px;
    }
    .signature-block td {
        width: 33%;
        text-align: center;
        vertical-align: top;
        padding: 0 15px;
    }
    .signature-line {
        margin-top: 50px;
    }
    @media print {
        .btn-print {
            display: none;
        }
    }
');
?>

<div class="btn-print text-right">
    <?= Html::a('<i class="fas fa-print"></i> In phiếu', 'javascript:window.print();', ['class' => 'btn btn-primary']) ?>
    <?= Html::a('<i class="fas fa-times"></i> Đóng', 'javascript:window.close();', ['class' => 'btn btn-default']) ?>
</div>

<div class="stock-in-print">
    <div class="company-info">
        <h3>CÔNG TY ZPLUS KIOT</h3>
        <p>Địa chỉ: 123 Đường Lê Lợi, Quận 1, TP. Hồ Chí Minh</p>
        <p>SĐT: 0903 123 456 | Email: info@zpluskiot.com</p>
    </div>

    <div class="print-header">
        <h1>PHIẾU NHẬP KHO</h1>
        <p><strong>Mã phiếu: <?= $model->code ?></strong></p>
        <p>Ngày: <?= Yii::$app->formatter->asDate($model->stock_in_date, 'dd/MM/yyyy') ?></p>
    </div>

    <table class="stock-in-info">
        <tr>
            <th>Nhà cung cấp:</th>
            <td><?= $model->supplier ? $model->supplier->name : 'N/A' ?></td>
        </tr>
        <tr>
            <th>Địa chỉ:</th>
            <td><?= $model->supplier ? $model->supplier->address : '' ?></td>
        </tr>
        <tr>
            <th>Điện thoại:</th>
            <td><?= $model->supplier ? $model->supplier->phone : '' ?></td>
        </tr>
        <tr>
            <th>Kho nhập:</th>
            <td><?= $model->warehouse->name ?></td>
        </tr>
        <?php if ($model->reference_number): ?>
        <tr>
            <th>Số tham chiếu:</th>
            <td><?= $model->reference_number ?></td>
        </tr>
        <?php endif; ?>
        <?php if ($model->note): ?>
        <tr>
            <th>Ghi chú:</th>
            <td><?= $model->note ?></td>
        </tr>
        <?php endif; ?>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 50px;">STT</th>
                <th>Mã SP</th>
                <th>Tên sản phẩm</th>
                <th>Số lô</th>
                <th>Hạn sử dụng</th>
                <th style="width: 80px;">Số lượng</th>
                <th>Đơn vị</th>
                <th style="width: 100px;">Đơn giá</th>
                <th style="width: 80px;">Chiết khấu</th>
                <th style="width: 80px;">Thuế</th>
                <th style="width: 120px;">Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            <?php $i = 1; foreach ($details as $detail): ?>
            <tr>
                <td class="text-center"><?= $i++ ?></td>
                <td><?= $detail->product->code ?></td>
                <td><?= $detail->product->name ?></td>
                <td><?= $detail->batch_number ?></td>
                <td class="text-center"><?= $detail->expiry_date ? Yii::$app->formatter->asDate($detail->expiry_date, 'dd/MM/yyyy') : '' ?></td>
                <td class="text-right"><?= Yii::$app->formatter->asDecimal($detail->quantity, 0) ?></td>
                <td><?= $detail->unit->name ?></td>
                <td class="text-right"><?= Yii::$app->formatter->asDecimal($detail->unit_price, 0) ?></td>
                <td class="text-right"><?= $detail->discount_percent ? $detail->discount_percent . '%' : '' ?></td>
                <td class="text-right"><?= $detail->tax_percent ? $detail->tax_percent . '%' : '' ?></td>
                <td class="text-right"><?= Yii::$app->formatter->asDecimal($detail->total_price, 0) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="9" class="text-right">Tổng tiền hàng:</th>
                <th colspan="2" class="text-right"><?= Yii::$app->formatter->asDecimal($model->total_amount, 0) ?></th>
            </tr>
            <tr>
                <th colspan="9" class="text-right">Chiết khấu:</th>
                <th colspan="2" class="text-right"><?= Yii::$app->formatter->asDecimal($model->discount_amount, 0) ?></th>
            </tr>
            <tr>
                <th colspan="9" class="text-right">Thuế:</th>
                <th colspan="2" class="text-right"><?= Yii::$app->formatter->asDecimal($model->tax_amount, 0) ?></th>
            </tr>
            <tr>
                <th colspan="9" class="text-right">Tổng cộng:</th>
                <th colspan="2" class="text-right"><?= Yii::$app->formatter->asDecimal($model->final_amount, 0) ?></th>
            </tr>
        </tfoot>
    </table>

    <table class="signature-block">
        <tr>
            <td>
                <p><strong>Người giao hàng</strong></p>
                <p class="signature-line">(Ký và ghi rõ họ tên)</p>
            </td>
            <td>
                <p><strong>Người nhận hàng</strong></p>
                <p class="signature-line">(Ký và ghi rõ họ tên)</p>
            </td>
            <td>
                <p><strong>Thủ kho</strong></p>
                <p class="signature-line">(Ký và ghi rõ họ tên)</p>
            </td>
        </tr>
    </table>

    <div class="print-footer">
        <p>Ngày in: <?= date('d/m/Y H:i:s') ?></p>
    </div>
</div>

<script>
    window.onload = function() {
        // Tự động mở cửa sổ in khi trang đã tải xong
        // window.print();
    }
</script>