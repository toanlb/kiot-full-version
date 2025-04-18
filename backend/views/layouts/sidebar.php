<?php
use hail812\adminlte\widgets\Menu;
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index3.html" class="brand-link">
        <img src="<?=$assetDir?>/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">Zplus Kiot</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="<?=$assetDir?>/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block"><?= Yii::$app->user->identity->username ?? 'Guest' ?></a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <?php
            echo Menu::widget([
                'items' => [
                    ['label' => 'Dashboard', 'icon' => 'tachometer-alt', 'url' => ['/site/index']],
                    ['label' => 'Quản lý sản phẩm', 'header' => true],
                    ['label' => 'Sản phẩm', 'icon' => 'box', 'url' => ['/product/index']],
                    ['label' => 'Danh mục', 'icon' => 'list', 'url' => ['/product-category/index']],
                    ['label' => 'Đơn vị tính', 'icon' => 'balance-scale', 'url' => ['/product-unit/index']],
                    ['label' => 'Thuộc tính', 'icon' => 'tags', 'url' => ['/product-attribute/index']],
                    
                    ['label' => 'Quản lý kho', 'header' => true],
                    ['label' => 'Kho hàng', 'icon' => 'warehouse', 'url' => ['/warehouse/index']],
                    ['label' => 'Nhập kho', 'icon' => 'truck-loading', 'url' => ['/stock-in/index']],
                    ['label' => 'Xuất kho', 'icon' => 'truck', 'url' => ['/stock-out/index']],
                    ['label' => 'Chuyển kho', 'icon' => 'exchange-alt', 'url' => ['/stock-transfer/index']],
                    ['label' => 'Kiểm kho', 'icon' => 'clipboard-check', 'url' => ['/stock-check/index']],
                    ['label' => 'Tồn kho', 'icon' => 'inventory', 'url' => ['/stock/index']],
                    
                    ['label' => 'Bán hàng', 'header' => true],
                    ['label' => 'Đơn hàng', 'icon' => 'shopping-cart', 'url' => ['/order/index']],
                    ['label' => 'Trả hàng', 'icon' => 'undo', 'url' => ['/return/index']],
                    ['label' => 'Khuyến mãi', 'icon' => 'percent', 'url' => ['/discount/index']],
                    
                    ['label' => 'Đối tác', 'header' => true],
                    ['label' => 'Khách hàng', 'icon' => 'users', 'url' => ['/customer/index']],
                    ['label' => 'Nhóm khách hàng', 'icon' => 'user-friends', 'url' => ['/customer-group/index']],
                    ['label' => 'Nhà cung cấp', 'icon' => 'industry', 'url' => ['/supplier/index']],
                    
                    ['label' => 'Bảo hành', 'header' => true],
                    ['label' => 'Phiếu bảo hành', 'icon' => 'shield-alt', 'url' => ['/warranty/index']],
                    ['label' => 'Trạng thái bảo hành', 'icon' => 'tasks', 'url' => ['/warranty-status/index']],
                    
                    ['label' => 'Tài chính', 'header' => true],
                    ['label' => 'Ca làm việc', 'icon' => 'user-clock', 'url' => ['/shift/index']],
                    ['label' => 'Phiếu thu', 'icon' => 'hand-holding-usd', 'url' => ['/receipt/index']],
                    ['label' => 'Phiếu chi', 'icon' => 'money-bill-wave', 'url' => ['/payment/index']],
                    ['label' => 'Sổ quỹ', 'icon' => 'book', 'url' => ['/cash-book/index']],
                    
                    ['label' => 'Báo cáo', 'header' => true],
                    ['label' => 'Báo cáo doanh thu', 'icon' => 'chart-line', 'url' => ['/report/sales']],
                    ['label' => 'Báo cáo tồn kho', 'icon' => 'chart-bar', 'url' => ['/report/inventory']],
                    ['label' => 'Báo cáo tài chính', 'icon' => 'chart-pie', 'url' => ['/report/finance']],
                    ['label' => 'Báo cáo khách hàng', 'icon' => 'chart-area', 'url' => ['/report/customer']],
                    
                    ['label' => 'Hệ thống', 'header' => true],
                    ['label' => 'Người dùng', 'icon' => 'user', 'url' => ['/user/index']],
                    ['label' => 'Vai trò & Quyền', 'icon' => 'user-shield', 'url' => ['/rbac/index']],
                    ['label' => 'Cài đặt', 'icon' => 'cog', 'url' => ['/setting/index']],
                    ['label' => 'Nhật ký', 'icon' => 'history', 'url' => ['/log/index']],
                ],
            ]);
            ?>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>