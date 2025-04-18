$(function () {
    // AdminLTE sidebar toggle sẽ tự hoạt động, không cần thêm code
    
    $(document).ready(function() {
        // Xử lý dropdown menu
        $('.navbar-nav .dropdown').on('mouseenter', function() {
            if ($(window).width() >= 768) {
                $(this).addClass('show');
                $(this).find('.dropdown-menu').addClass('show');
            }
        });
    
        $('.navbar-nav .dropdown').on('mouseleave', function() {
            if ($(window).width() >= 768) {
                $(this).removeClass('show');
                $(this).find('.dropdown-menu').removeClass('show');
            }
        });
    
        // Xử lý click trên thiết bị di động
        $('.navbar-nav .dropdown > a').on('click', function(e) {
            if ($(window).width() < 768) {
                e.preventDefault();
                e.stopPropagation();
                
                // Toggle dropdown
                $(this).parent().toggleClass('show');
                $(this).next('.dropdown-menu').toggleClass('show');
                
                // Đóng các dropdown khác
                $('.navbar-nav .dropdown').not($(this).parent()).removeClass('show');
                $('.navbar-nav .dropdown-menu').not($(this).next()).removeClass('show');
            }
        });
    
        // Đóng dropdown khi click ra ngoài
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.dropdown').length) {
                $('.navbar-nav .dropdown').removeClass('show');
                $('.navbar-nav .dropdown-menu').removeClass('show');
            }
        });
        
        // Xử lý khi thay đổi kích thước màn hình
        $(window).resize(function() {
            if ($(window).width() >= 768) {
                $('.navbar-nav .dropdown').removeClass('show');
                $('.navbar-nav .dropdown-menu').removeClass('show');
            }
        });
    });
    
    // Ví dụ: Ẩn thông báo alert sau 5 giây
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});