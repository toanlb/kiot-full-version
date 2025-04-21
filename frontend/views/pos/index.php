<?php
/* @var $this yii\web\View */
/* @var $currentShift app\models\Shift */
/* @var $warehouse app\models\Warehouse */
/* @var $categories app\models\ProductCategory[] */
/* @var $paymentMethods app\models\PaymentMethod[] */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'POS - Bán hàng';
$this->params['breadcrumbs'][] = $this->title;

// Register CSS & JS
$this->registerCssFile('@web/css/modern-pos.css');
$this->registerJsFile('@web/js/pos.js', ['depends' => [\yii\web\JqueryAsset::class]]);
?>

<div class="modern-pos-container">
    <!-- Header -->
    <div class="pos-header shadow-sm">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <div class="d-flex align-items-center">
                        <div class="pos-cashier-info">
                            <div class="d-flex align-items-center">
                                <div class="cashier-avatar mr-2">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <h6 class="cashier-name mb-0"><?= Yii::$app->user->identity->full_name ?></h6>
                                    <small class="text-muted">Nhân viên bán hàng</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="shift-info text-center">
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="mr-3">
                                <div class="shift-icon bg-primary text-white">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                            <div class="text-left">
                                <h6 class="mb-0">Ca làm việc: #<?= $currentShift->id ?></h6>
                                <div class="d-flex align-items-center">
                                    <span class="badge badge-soft-success mr-2">
                                        <i class="far fa-calendar-alt"></i> 
                                        <?= Yii::$app->formatter->asDate($currentShift->start_time) ?>
                                    </span>
                                    <span class="badge badge-soft-info">
                                        <i class="far fa-clock"></i> 
                                        <?= Yii::$app->formatter->asTime($currentShift->start_time) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="pos-actions text-right">
                        <div class="header-info d-inline-block text-left mr-3">
                            <div class="current-time">
                                <i class="far fa-clock"></i> <span id="current-time"><?= date('H:i:s') ?></span>
                            </div>
                            <div class="current-date">
                                <i class="far fa-calendar-alt"></i> <span id="current-date"><?= date('d/m/Y') ?></span>
                            </div>
                        </div>
                        <button class="btn btn-warning btn-sm" id="endShiftBtn">
                            <i class="fas fa-sign-out-alt"></i> Kết ca
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="pos-content">
        <div class="container-fluid">
            <div class="row">
                <!-- Left Sidebar - Product Categories and Filters -->
                <div class="col-md-3">
                    <div class="card card-custom h-100">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-warehouse text-primary mr-2"></i> 
                                <?= $warehouse->name ?>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="search-wrapper p-3">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0">
                                            <i class="fas fa-search text-muted"></i>
                                        </span>
                                    </div>
                                    <input type="text" id="productSearch" class="form-control border-left-0" placeholder="Tìm sản phẩm...">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="button" id="barcodeBtn">
                                            <i class="fas fa-barcode"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="category-list">
                                <div class="list-group list-group-flush">
                                    <a href="#" class="list-group-item list-group-item-action active" data-category="">
                                        <i class="fas fa-th-large mr-2"></i> Tất cả sản phẩm
                                    </a>
                                    <?php foreach ($categories as $category): ?>
                                    <a href="#" class="list-group-item list-group-item-action" data-category="<?= $category->id ?>">
                                        <i class="<?= $category->icon ?? 'fas fa-folder' ?> mr-2"></i> <?= $category->name ?>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Center - Products Display -->
                <div class="col-md-5">
                    <div class="card card-custom h-100">
                        <div class="card-header bg-white">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-shopping-basket text-success mr-2"></i> Sản phẩm
                                    </h5>
                                </div>
                                <div class="col-auto">
                                    <select id="productSort" class="form-control form-control-sm">
                                        <option value="name">Sắp xếp theo tên</option>
                                        <option value="price_asc">Giá tăng dần</option>
                                        <option value="price_desc">Giá giảm dần</option>
                                        <option value="popular">Phổ biến nhất</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="product-grid" id="product-grid">
                                <!-- Products will be loaded here via AJAX -->
                                <div class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only">Đang tải...</span>
                                    </div>
                                    <p class="mt-2">Đang tải sản phẩm...</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="row align-items-center">
                                <div class="col">
                                    <div class="pagination-info">
                                        Hiển thị <span id="showing-products">0</span> / <span id="total-products">0</span> sản phẩm
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <nav>
                                        <ul class="pagination pagination-sm mb-0" id="product-pagination">
                                            <!-- Pagination will be generated by JS -->
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Sidebar - Shopping Cart -->
                <div class="col-md-4">
                    <div class="card card-custom h-100">
                        <div class="card-header bg-white">
                            <div class="d-flex align-items-center">
                                <div class="cart-icon-wrapper bg-soft-primary text-primary mr-3">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <h5 class="card-title mb-0">Giỏ hàng</h5>
                            </div>
                            <div class="customer-selector mt-3">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0">
                                            <i class="fas fa-user text-muted"></i>
                                        </span>
                                    </div>
                                    <input type="text" id="customerSearch" class="form-control border-left-0 border-right-0" placeholder="Tìm khách hàng...">
                                    <div class="input-group-append">
                                        <button class="btn btn-light border-left-0" type="button" id="customerSearchBtn">
                                            <i class="fas fa-search"></i>
                                        </button>
                                        <button class="btn btn-primary" type="button" id="addCustomerBtn">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                                <div id="customerInfo" class="mt-2 d-none bg-soft-success p-2 rounded">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0"><span id="customerName">Nguyễn Văn A</span></h6>
                                            <div class="d-flex align-items-center text-muted">
                                                <i class="fas fa-phone-alt mr-1"></i>
                                                <span id="customerPhone" class="small">0912345678</span>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="small">
                                                <i class="fas fa-coins text-warning"></i> <span id="customerPoints">0</span> điểm
                                            </div>
                                            <div class="small">
                                                <i class="fas fa-money-bill-wave text-success"></i> Nợ: <span id="customerDebt">0₫</span>
                                            </div>
                                        </div>
                                        <button class="btn btn-icon btn-sm btn-light" id="removeCustomerBtn">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-body p-0">
                            <div class="cart-items">
                                <table class="table table-sm mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Sản phẩm</th>
                                            <th class="text-center" width="60">SL</th>
                                            <th class="text-right" width="100">Thành tiền</th>
                                            <th width="40"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="cartItems">
                                        <!-- Cart items will be added here by JS -->
                                        <tr class="empty-cart">
                                            <td colspan="4" class="text-center py-4">
                                                <div class="empty-cart-icon">
                                                    <i class="fas fa-shopping-cart fa-3x text-muted"></i>
                                                </div>
                                                <p class="mt-2">Giỏ hàng trống</p>
                                                <p class="text-muted small">Vui lòng chọn sản phẩm để thêm vào giỏ hàng</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <div class="card-footer p-0">
                            <div class="discount-section p-3 border-bottom bg-soft-light">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-white border-right-0">
                                                    <i class="fas fa-ticket-alt text-primary"></i>
                                                </span>
                                            </div>
                                            <input type="text" id="discountCode" class="form-control border-left-0" placeholder="Mã giảm giá...">
                                            <div class="input-group-append">
                                                <button class="btn btn-primary" type="button" id="applyDiscountBtn">Áp dụng</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-white border-right-0">
                                                    <i class="fas fa-percent text-success"></i>
                                                </span>
                                            </div>
                                            <input type="number" id="discountPercent" class="form-control border-left-0" min="0" max="100" value="0" placeholder="Giảm giá %">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="order-summary p-3 bg-soft-light">
                                <div class="order-summary-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Tổng tiền hàng:</span>
                                    <span id="subtotal" class="font-weight-medium">0₫</span>
                                </div>
                                <div class="order-summary-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Giảm giá:</span>
                                    <span id="discount" class="font-weight-medium text-danger">0₫</span>
                                </div>
                                <div class="order-summary-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Thuế VAT (10%):</span>
                                    <span id="tax" class="font-weight-medium">0₫</span>
                                </div>
                                <div class="order-summary-item d-flex justify-content-between align-items-center pt-2 border-top">
                                    <span class="h6 mb-0">Thành tiền:</span>
                                    <span id="total" class="h5 mb-0 text-primary">0₫</span>
                                </div>
                            </div>
                            
                            <div class="payment-section p-3 bg-white">
                                <div class="payment-methods mb-3">
                                    <div class="payment-method-tabs">
                                        <ul class="nav nav-pills nav-justified" id="paymentTab" role="tablist">
                                            <?php foreach ($paymentMethods as $index => $method): ?>
                                            <li class="nav-item">
                                                <a class="nav-link<?= $index === 0 ? ' active' : '' ?>" 
                                                   id="payment-tab-<?= $method->id ?>" 
                                                   data-toggle="pill" 
                                                   href="#payment-content-<?= $method->id ?>" 
                                                   role="tab" 
                                                   aria-controls="payment-content-<?= $method->id ?>" 
                                                   aria-selected="<?= $index === 0 ? 'true' : 'false' ?>">
                                                    <i class="<?= $method->icon ?? 'fas fa-money-bill-wave' ?> mr-1"></i> <?= $method->name ?>
                                                </a>
                                            </li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <div class="tab-content mt-2" id="paymentTabContent">
                                            <?php foreach ($paymentMethods as $index => $method): ?>
                                            <div class="tab-pane fade<?= $index === 0 ? ' show active' : '' ?>" 
                                                 id="payment-content-<?= $method->id ?>" 
                                                 role="tabpanel" 
                                                 aria-labelledby="payment-tab-<?= $method->id ?>">
                                                 
                                                <?php if ($index === 0): // Assuming first method is cash ?>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group mb-0">
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text">Khách đưa</span>
                                                                </div>
                                                                <input type="number" id="amountTendered" class="form-control" min="0" value="0">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group mb-0">
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text">Tiền thừa</span>
                                                                </div>
                                                                <input type="text" id="changeAmount" class="form-control" readonly value="0₫">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php else: ?>
                                                <div class="form-group mb-0">
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">Mã giao dịch</span>
                                                        </div>
                                                        <input type="text" id="transactionReference<?= $method->id ?>" class="transaction-reference form-control">
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                                
                                                <input type="hidden" name="paymentMethod" class="payment-method-input" value="<?= $method->id ?>" <?= $index === 0 ? 'checked' : '' ?>>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="action-buttons">
                                    <div class="row">
                                        <div class="col-4">
                                            <button class="btn btn-outline-danger btn-block" id="cancelOrderBtn">
                                                <i class="fas fa-times"></i> Hủy
                                            </button>
                                        </div>
                                        <div class="col-4">
                                            <button class="btn btn-outline-info btn-block" id="holdOrderBtn">
                                                <i class="fas fa-pause"></i> Tạm giữ
                                            </button>
                                        </div>
                                        <div class="col-4">
                                            <button class="btn btn-success btn-block pulse-button" id="completeOrderBtn">
                                                <i class="fas fa-check"></i> Thanh toán
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Function Bar -->
    <div class="pos-functions shadow-sm">
        <div class="container-fluid">
            <div class="function-buttons">
                <button class="function-btn" id="openCashDrawerBtn">
                    <div class="function-icon">
                        <i class="fas fa-cash-register"></i>
                    </div>
                    <div class="function-text">Mở ngăn kéo</div>
                </button>
                <button class="function-btn" id="recentOrdersBtn">
                    <div class="function-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <div class="function-text">Đơn gần đây</div>
                </button>
                <button class="function-btn" id="holdOrdersBtn">
                    <div class="function-icon">
                        <i class="fas fa-pause-circle"></i>
                    </div>
                    <div class="function-text">Đơn tạm giữ</div>
                </button>
                <button class="function-btn" id="quickProductBtn">
                    <div class="function-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div class="function-text">SP nhanh</div>
                </button>
                <button class="function-btn" id="helpBtn">
                    <div class="function-icon">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <div class="function-text">Trợ giúp</div>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<!-- End Shift Modal -->
<div class="modal fade" id="endShiftModal" tabindex="-1" role="dialog" aria-labelledby="endShiftModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="endShiftModalLabel">
                    <i class="fas fa-user-clock text-primary mr-2"></i> Kết ca làm việc
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-custom mb-3">
                            <div class="card-header bg-soft-primary">
                                <h6 class="card-title mb-0">Thông tin ca làm việc</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td class="text-muted">Mã ca:</td>
                                        <td class="font-weight-medium"><?= $currentShift->id ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Bắt đầu:</td>
                                        <td class="font-weight-medium"><?= Yii::$app->formatter->asDatetime($currentShift->start_time) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Tiền đầu ca:</td>
                                        <td class="font-weight-medium"><?= Yii::$app->formatter->asCurrency($currentShift->opening_amount) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Tổng bán:</td>
                                        <td class="font-weight-medium text-success" id="shiftTotalSales"><?= Yii::$app->formatter->asCurrency($currentShift->total_sales) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Tổng trả:</td>
                                        <td class="font-weight-medium text-danger" id="shiftTotalReturns"><?= Yii::$app->formatter->asCurrency($currentShift->total_returns) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Số dư dự kiến:</td>
                                        <td class="font-weight-medium" id="shiftExpectedAmount"><?= Yii::$app->formatter->asCurrency($currentShift->expected_amount) ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-custom mb-3">
                            <div class="card-header bg-soft-info">
                                <h6 class="card-title mb-0">Chi tiết phương thức thanh toán</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless" id="shiftPaymentDetails">
                                    <!-- Payment method details will be loaded via AJAX -->
                                    <tr>
                                        <td class="text-muted">Tiền mặt:</td>
                                        <td class="font-weight-medium">0₫</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Chuyển khoản:</td>
                                        <td class="font-weight-medium">0₫</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Thẻ tín dụng:</td>
                                        <td class="font-weight-medium">0₫</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="actualAmount">Số tiền thực tế:</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white">
                                        <i class="fas fa-money-bill-wave text-success"></i>
                                    </span>
                                </div>
                                <input type="number" id="actualAmount" class="form-control" min="0">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="amountDifference">Chênh lệch:</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white">
                                        <i class="fas fa-balance-scale"></i>
                                    </span>
                                </div>
                                <input type="text" id="amountDifference" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="shiftExplanation">Giải thích (nếu có):</label>
                            <textarea id="shiftExplanation" class="form-control" rows="2" placeholder="Lý do chênh lệch..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="confirmEndShiftBtn">
                    <i class="fas fa-check mr-1"></i> Xác nhận kết ca
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" role="dialog" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCustomerModalLabel">
                    <i class="fas fa-user-plus text-primary mr-2"></i> Thêm khách hàng mới
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="customerNameInput">Họ tên khách hàng <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white">
                                <i class="fas fa-user text-primary"></i>
                            </span>
                        </div>
                        <input type="text" id="customerNameInput" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="customerPhoneInput">Số điện thoại <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white">
                                <i class="fas fa-phone-alt text-primary"></i>
                            </span>
                        </div>
                        <input type="text" id="customerPhoneInput" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="customerEmailInput">Email</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white">
                                <i class="fas fa-envelope text-primary"></i>
                            </span>
                        </div>
                        <input type="email" id="customerEmailInput" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label for="customerAddressInput">Địa chỉ</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white">
                                <i class="fas fa-map-marker-alt text-primary"></i>
                            </span>
                        </div>
                        <textarea id="customerAddressInput" class="form-control" rows="2"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="saveCustomerBtn">
                    <i class="fas fa-save mr-1"></i> Lưu khách hàng
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders Modal -->
<div class="modal fade" id="recentOrdersModal" tabindex="-1" role="dialog" aria-labelledby="recentOrdersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="recentOrdersModalLabel">
                    <i class="fas fa-history text-primary mr-2"></i> Đơn hàng gần đây
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Mã đơn</th>
                                <th>Thời gian</th>
                                <th>Khách hàng</th>
                                <th class="text-right">Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="recentOrdersList">
                            <!-- Recent orders will be loaded via AJAX -->
                            <tr>
                                <td colspan="6" class="text-center py-3">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only">Đang tải...</span>
                                    </div>
                                    <p class="mt-2 mb-0">Đang tải đơn hàng...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Hold Orders Modal -->
<div class="modal fade" id="holdOrdersModal" tabindex="-1" role="dialog" aria-labelledby="holdOrdersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="holdOrdersModalLabel">
                    <i class="fas fa-pause-circle text-info mr-2"></i> Đơn hàng tạm giữ
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Mã đơn</th>
                                <th>Thời gian</th>
                                <th>Khách hàng</th>
                                <th class="text-right">Tổng tiền</th>
                                <th>Ghi chú</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="holdOrdersList">
                            <!-- Hold orders will be loaded via AJAX -->
                            <tr>
                                <td colspan="6" class="text-center py-3">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only">Đang tải...</span>
                                    </div>
                                    <p class="mt-2 mb-0">Đang tải đơn hàng...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Order Complete Modal -->
<div class="modal fade" id="orderCompleteModal" tabindex="-1" role="dialog" aria-labelledby="orderCompleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="orderCompleteModalLabel">
                    <i class="fas fa-check-circle mr-2"></i> Đơn hàng thành công
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="success-animation">
                    <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                        <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
                        <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                    </svg>
                </div>
                <h4 class="mt-3">Đơn hàng đã được tạo thành công!</h4>
                <div class="order-complete-info">
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="card bg-light my-3">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="text-muted">Mã đơn hàng:</div>
                                        <div class="h5 mb-0 font-weight-bold text-primary" id="completedOrderCode">ORD00001</div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <div class="text-muted">Tổng tiền:</div>
                                        <div class="h5 mb-0 font-weight-bold text-success" id="completedOrderTotal">0₫</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-outline-secondary" id="printReceiptBtn">
                    <i class="fas fa-print mr-1"></i> In hóa đơn
                </button>
                <button type="button" class="btn btn-outline-primary" id="printWarrantyBtn">
                    <i class="fas fa-shield-alt mr-1"></i> In phiếu bảo hành
                </button>
                <button type="button" class="btn btn-success" data-dismiss="modal">
                    <i class="fas fa-shopping-cart mr-1"></i> Đơn hàng mới
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Loading overlay -->
<div class="loading-overlay d-none">
    <div class="spinner-wrapper">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Đang xử lý...</span>
        </div>
        <div class="mt-2">Đang xử lý...</div>
    </div>
</div>

<!-- Template for product items -->
<template id="product-item-template">
    <div class="product-card" data-id="${id}">
        <div class="product-card-inner">
            <div class="product-info">
                <h6 class="product-name">${name}</h6>
                <div class="product-code">${code}</div>
                <div class="product-price">${formatted_price}</div>
                <div class="product-stock ${stockClass}">
                    Tồn: ${quantity} ${unit}
                </div>
            </div>
            <div class="product-actions">
                <button class="btn btn-sm btn-primary add-to-cart-btn" ${outOfStock ? 'disabled' : ''}>
                    <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                </button>
            </div>
        </div>
    </div>
</template>

<!-- Template for cart items -->
<template id="cart-item-template">
    <tr class="cart-item" data-id="${product_id}">
        <td>
            <div class="d-flex align-items-center">

                <div>
                    <div class="cart-item-name">${name}</div>
                    <div class="cart-item-price">${formatted_price}</div>
                </div>
            </div>
        </td>
        <td>
            <div class="quantity-control">
                <button class="btn btn-sm btn-icon btn-light decrease-qty" data-id="${product_id}">
                    <i class="fas fa-minus"></i>
                </button>
                <input type="text" class="form-control form-control-sm text-center item-qty" value="${quantity}" min="1" data-id="${product_id}">
                <button class="btn btn-sm btn-icon btn-light increase-qty" data-id="${product_id}">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </td>
        <td class="text-right">${formatted_total}</td>
        <td class="text-center">
            <button class="btn btn-sm btn-icon btn-light remove-item" data-id="${product_id}">
                <i class="fas fa-times"></i>
            </button>
        </td>
    </tr>
</template>