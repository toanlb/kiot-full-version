<footer class="main-footer">
    <div class="row">
        <div class="col-md-8">
            <strong>Bản quyền &copy; <?= date('Y') ?> <a href="#">ZPlus Kiot</a>.</strong>
            Đã đăng ký bản quyền.
        </div>
        <div class="col-md-4 text-right">
            <div class="d-inline-block mr-3">
                <b>Phiên bản</b> 1.0.0
            </div>
            <div class="d-inline-block">
                <a href="#" class="text-secondary mr-2"><i class="fab fa-facebook"></i></a>
                <a href="#" class="text-secondary mr-2"><i class="fab fa-twitter"></i></a>
                <a href="#" class="text-secondary"><i class="fab fa-github"></i></a>
            </div>
        </div>
    </div>
</footer>

<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
    <div class="p-3">
        <h5>Cài đặt giao diện</h5>
        <hr class="mb-2">
        
        <div class="mb-4">
            <h6>Màu chủ đề</h6>
            <div class="d-flex">
                <div class="custom-control custom-radio mr-2">
                    <input class="custom-control-input" type="radio" id="themeLight" name="themeOptions" checked>
                    <label for="themeLight" class="custom-control-label">Sáng</label>
                </div>
                <div class="custom-control custom-radio mr-2">
                    <input class="custom-control-input" type="radio" id="themeDark" name="themeOptions">
                    <label for="themeDark" class="custom-control-label">Tối</label>
                </div>
            </div>
        </div>
        
        <div class="mb-4">
            <h6>Menu bên</h6>
            <div class="mb-1">
                <div class="custom-control custom-switch">
                    <input class="custom-control-input" type="checkbox" id="sidebarCollapsed">
                    <label class="custom-control-label" for="sidebarCollapsed">Thu gọn</label>
                </div>
            </div>
            <div class="mb-1">
                <div class="custom-control custom-switch">
                    <input class="custom-control-input" type="checkbox" id="sidebarMini">
                    <label class="custom-control-label" for="sidebarMini">Mini</label>
                </div>
            </div>
        </div>
        
        <div class="mb-4">
            <h6>Thanh điều hướng</h6>
            <div class="custom-control custom-switch">
                <input class="custom-control-input" type="checkbox" id="navbarFixed">
                <label class="custom-control-label" for="navbarFixed">Cố định</label>
            </div>
        </div>
        
        <a href="#" class="btn btn-primary btn-sm btn-block">Khôi phục mặc định</a>
    </div>
</aside>
<!-- /.control-sidebar -->