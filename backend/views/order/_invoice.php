<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Order */
/* @var $orderDetails array */
/* @var $orderPayments array */

// Dữ liệu công ty
$companyInfo = [
    'name' => 'KIOT POS',
    'address' => '123 Đường ABC, Quận XYZ, TP.HCM',
    'phone' => '0123456789',
    'email' => 'contact@example.com',
    'tax_code' => '0123456789'
];

/**
 * Hàm chuyển số thành chữ
 * @param float $number Số cần chuyển
 * @return string Chuỗi chữ đọc số tiền
 */
function convertNumberToWords($number) {
    $hyphen = ' ';
    $conjunction = ' và ';
    $separator = ' ';
    $negative = 'âm ';
    $decimal = ' phẩy ';
    $dictionary = array(
        0 => 'không',
        1 => 'một',
        2 => 'hai',
        3 => 'ba',
        4 => 'bốn',
        5 => 'năm',
        6 => 'sáu',
        7 => 'bảy',
        8 => 'tám',
        9 => 'chín',
        10 => 'mười',
        11 => 'mười một',
        12 => 'mười hai',
        13 => 'mười ba',
        14 => 'mười bốn',
        15 => 'mười lăm',
        16 => 'mười sáu',
        17 => 'mười bảy',
        18 => 'mười tám',
        19 => 'mười chín',
        20 => 'hai mươi',
        30 => 'ba mươi',
        40 => 'bốn mươi',
        50 => 'năm mươi',
        60 => 'sáu mươi',
        70 => 'bảy mươi',
        80 => 'tám mươi',
        90 => 'chín mươi',
        100 => 'trăm',
        1000 => 'nghìn',
        1000000 => 'triệu',
        1000000000 => 'tỷ',
        1000000000000 => 'nghìn tỷ',
        1000000000000000 => 'nghìn triệu triệu',
        1000000000000000000 => 'tỷ tỷ'
    );

    if (!is_numeric($number)) {
        return false;
    }

    if (($number >= 0 && (int)$number < 0) || (int)$number < 0 - PHP_INT_MAX) {
        // overflow
        trigger_error('convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX, E_USER_WARNING);
        return false;
    }

    if ($number < 0) {
        return $negative . convertNumberToWords(abs($number));
    }

    $string = $fraction = null;

    if (strpos($number, '.') !== false) {
        list($number, $fraction) = explode('.', $number);
    }

    switch (true) {
        case $number < 21:
            $string = $dictionary[$number];
            break;
        case $number < 100:
            $tens = ((int)($number / 10)) * 10;
            $units = $number % 10;
            $string = $dictionary[$tens];
            if ($units) {
                $string .= $hyphen . $dictionary[$units];
            }
            break;
        case $number < 1000:
            $hundreds = $number / 100;
            $remainder = $number % 100;
            $string = $dictionary[(int)$hundreds] . ' ' . $dictionary[100];
            if ($remainder) {
                $string .= $conjunction . convertNumberToWords($remainder);
            }
            break;
        default:
            $baseUnit = pow(1000, floor(log($number, 1000)));
            $numBaseUnits = (int)($number / $baseUnit);
            $remainder = $number % $baseUnit;
            $string = convertNumberToWords($numBaseUnits) . ' ' . $dictionary[$baseUnit];
            if ($remainder) {
                $string .= $remainder < 100 ? $conjunction : $separator;
                $string .= convertNumberToWords($remainder);
            }
            break;
    }

    if (null !== $fraction && is_numeric($fraction)) {
        $string .= $decimal;
        $words = array();
        foreach (str_split((string)$fraction) as $number) {
            $words[] = $dictionary[(int)$number];
        }
        $string .= implode(' ', $words);
    }

    return $string;
}
?>

<!-- Styles sử dụng inline CSS để đảm bảo hoạt động với mpdf -->
<div style="font-family: Arial, sans-serif; font-size: 14px; line-height: 1.5; color: #333;">
    <div style="max-width: 100%; margin: auto; padding: 30px;">
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 20px;">
            <h1 style="font-size: 24px; font-weight: bold; margin-bottom: 5px;"><?= Html::encode($companyInfo['name']) ?></h1>
            <p style="margin: 2px 0;"><?= Html::encode($companyInfo['address']) ?></p>
            <p style="margin: 2px 0;">Điện thoại: <?= Html::encode($companyInfo['phone']) ?> | Email: <?= Html::encode($companyInfo['email']) ?></p>
            <p style="margin: 2px 0;">Mã số thuế: <?= Html::encode($companyInfo['tax_code']) ?></p>
        </div>
        
        <!-- Title -->
        <div style="text-align: center; margin: 30px 0;">
            <h2 style="font-size: 20px; font-weight: bold; margin-bottom: 5px; text-transform: uppercase;">Hóa Đơn Bán Hàng</h2>
            <p style="margin: 2px 0;">Mã đơn hàng: <?= Html::encode($model->code) ?></p>
            <p style="margin: 2px 0;">Ngày: <?= Yii::$app->formatter->asDate($model->order_date) ?></p>
        </div>
        
        <!-- Customer Info -->
        <div style="margin-bottom: 20px;">
            <table style="width: 100%;">
                <tr>
                    <th style="text-align: left; padding: 5px 10px 5px 0; vertical-align: top; width: 150px;">Khách hàng:</th>
                    <td style="padding: 5px 0; vertical-align: top;"><?= $model->customer ? Html::encode($model->customer->name) : 'Khách lẻ' ?></td>
                </tr>
                <?php if ($model->customer && $model->customer->phone): ?>
                <tr>
                    <th style="text-align: left; padding: 5px 10px 5px 0; vertical-align: top; width: 150px;">Điện thoại:</th>
                    <td style="padding: 5px 0; vertical-align: top;"><?= Html::encode($model->customer->phone) ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($model->customer && $model->customer->address): ?>
                <tr>
                    <th style="text-align: left; padding: 5px 10px 5px 0; vertical-align: top; width: 150px;">Địa chỉ:</th>
                    <td style="padding: 5px 0; vertical-align: top;"><?= Html::encode($model->customer->address) ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($model->shipping_address): ?>
                <tr>
                    <th style="text-align: left; padding: 5px 10px 5px 0; vertical-align: top; width: 150px;">Địa chỉ giao hàng:</th>
                    <td style="padding: 5px 0; vertical-align: top;"><?= Html::encode($model->shipping_address) ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th style="text-align: left; padding: 5px 10px 5px 0; vertical-align: top; width: 150px;">Nhân viên bán hàng:</th>
                    <td style="padding: 5px 0; vertical-align: top;"><?= $model->user ? Html::encode($model->user->full_name) : 'N/A' ?></td>
                </tr>
            </table>
        </div>
        
        <!-- Order Items -->
        <div style="margin-bottom: 20px;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: left; padding: 10px; background-color: #f3f3f3; border-bottom: 1px solid #ddd;">STT</th>
                        <th style="text-align: left; padding: 10px; background-color: #f3f3f3; border-bottom: 1px solid #ddd;">Sản phẩm</th>
                        <th style="width: 80px; text-align: center; padding: 10px; background-color: #f3f3f3; border-bottom: 1px solid #ddd;">Số lượng</th>
                        <th style="width: 120px; text-align: right; padding: 10px; background-color: #f3f3f3; border-bottom: 1px solid #ddd;">Đơn giá</th>
                        <th style="width: 120px; text-align: right; padding: 10px; background-color: #f3f3f3; border-bottom: 1px solid #ddd;">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderDetails as $i => $detail): ?>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #ddd;"><?= $i + 1 ?></td>
                        <td style="padding: 10px; border-bottom: 1px solid #ddd;">
                            <?= $detail->product ? Html::encode($detail->product->name) : 'Sản phẩm không tồn tại' ?>
                            <?php if ($detail->note): ?>
                            <br><small><?= Html::encode($detail->note) ?></small>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 10px; border-bottom: 1px solid #ddd; text-align: center;">
                            <?= $detail->quantity ?> <?= $detail->unit ? Html::encode($detail->unit->abbreviation) : '' ?>
                        </td>
                        <td style="padding: 10px; border-bottom: 1px solid #ddd; text-align: right;">
                            <?= Yii::$app->formatter->asDecimal($detail->unit_price) ?>
                        </td>
                        <td style="padding: 10px; border-bottom: 1px solid #ddd; text-align: right;">
                            <?= Yii::$app->formatter->asDecimal($detail->total_amount) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" style="padding: 10px; text-align: right;">Tổng cộng:</td>
                        <td style="padding: 10px; text-align: right;"><?= Yii::$app->formatter->asDecimal($model->subtotal) ?></td>
                    </tr>
                    <?php if ($model->discount_amount > 0): ?>
                    <tr>
                        <td colspan="4" style="padding: 10px; text-align: right;">Giảm giá:</td>
                        <td style="padding: 10px; text-align: right;"><?= Yii::$app->formatter->asDecimal($model->discount_amount) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($model->tax_amount > 0): ?>
                    <tr>
                        <td colspan="4" style="padding: 10px; text-align: right;">Thuế:</td>
                        <td style="padding: 10px; text-align: right;"><?= Yii::$app->formatter->asDecimal($model->tax_amount) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($model->shipping_fee > 0): ?>
                    <tr>
                        <td colspan="4" style="padding: 10px; text-align: right;">Phí vận chuyển:</td>
                        <td style="padding: 10px; text-align: right;"><?= Yii::$app->formatter->asDecimal($model->shipping_fee) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td colspan="4" style="padding: 10px; text-align: right; font-weight: bold; border-top: 2px solid #ddd;">Tổng thanh toán:</td>
                        <td style="padding: 10px; text-align: right; font-weight: bold; border-top: 2px solid #ddd;"><?= Yii::$app->formatter->asDecimal($model->total_amount) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <!-- Summary -->
        <div style="margin-bottom: 20px;">
            <p><strong>Số tiền bằng chữ:</strong> <?= convertNumberToWords($model->total_amount) ?> đồng.</p>
            
            <?php if ($model->note): ?>
            <p><strong>Ghi chú:</strong> <?= Yii::$app->formatter->asNtext($model->note) ?></p>
            <?php endif; ?>
            
            <p><strong>Phương thức thanh toán:</strong> 
                <?= $model->paymentMethod ? Html::encode($model->paymentMethod->name) : 'Chưa thanh toán' ?>
            </p>
        </div>
        
        <!-- Signatures -->
        <div style="margin-top: 40px;">
            <table style="width: 100%;">
                <tr>
                    <td style="vertical-align: top; text-align: center; width: 33.33%; padding: 10px;">
                        <strong>Người mua hàng</strong>
                        <p style="margin-top: 80px;">(Ký, ghi rõ họ tên)</p>
                    </td>
                    <td style="vertical-align: top; text-align: center; width: 33.33%; padding: 10px;">
                        <strong>Người giao hàng</strong>
                        <p style="margin-top: 80px;">(Ký, ghi rõ họ tên)</p>
                    </td>
                    <td style="vertical-align: top; text-align: center; width: 33.33%; padding: 10px;">
                        <strong>Người bán hàng</strong>
                        <p style="margin-top: 80px;">(Ký, ghi rõ họ tên)</p>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>