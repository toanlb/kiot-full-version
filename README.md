TÀI LIỆU THIẾT KẾ DỰ ÁN PHẦN MỀM QUẢN LÝ KIOT
1. TỔNG QUAN DỰ ÁN
1.1. Giới thiệu
Phần mềm Quản lý Kiot là một hệ thống toàn diện được phát triển bằng PHP và framework Yii2, nhằm hỗ trợ việc quản lý cửa hàng bán lẻ, bao gồm quản lý hàng tồn kho, bán hàng, và các hoạt động kinh doanh khác.
1.2. Mục tiêu
•	Tự động hóa quy trình bán hàng và quản lý kho
•	Cung cấp hệ thống POS (Point of Sale) hiệu quả cho nhân viên bán hàng
•	Theo dõi hàng tồn kho và cảnh báo khi hàng sắp hết
•	Quản lý thông tin khách hàng và lịch sử mua hàng
•	Theo dõi bảo hành sản phẩm
•	Cung cấp báo cáo chi tiết về doanh số, lợi nhuận
1.3. Công nghệ sử dụng
•	Ngôn ngữ lập trình: PHP 7.4+
•	Framework: Yii2 Advanced
•	Cơ sở dữ liệu: MySQL 8.0+
•	Frontend: HTML5, CSS3, JavaScript, Bootstrap 4/5
•	Giao diện người dùng: AdminLTE cho backend, Responsive design
•	Báo cáo: TCPDF/MPDF
•	Phân quyền: RBAC (Role-Based Access Control) của Yii2
2. YÊU CẦU CHỨC NĂNG
2.1. Giao diện nhân viên (Frontend)
2.1.1. Hệ thống POS (Point of Sale)
•	Màn hình bán hàng:
o	Hiển thị danh sách sản phẩm theo danh mục
o	Tìm kiếm sản phẩm theo mã, tên, mã vạch
o	Thêm sản phẩm vào giỏ hàng
o	Áp dụng giảm giá (theo sản phẩm, tổng đơn hàng)
o	Chọn khách hàng hoặc thêm khách hàng mới
o	Thanh toán nhiều hình thức (tiền mặt, chuyển khoản, thẻ)
o	Hỗ trợ trả góp và ghi nợ
•	Quản lý ca làm việc:
o	Đăng nhập/đăng xuất ca
o	Khai báo tiền đầu ca
o	Kết ca và kiểm đếm tiền cuối ca
o	Báo cáo doanh số theo ca
•	In đơn hàng:
o	In hóa đơn bán hàng
o	In phiếu bảo hành
o	Hỗ trợ in nhiều định dạng (A4, A5, hóa đơn nhỏ)
o	Tùy chỉnh mẫu in
2.1.2. Quản lý bảo hành
•	Tạo phiếu bảo hành cho từng sản phẩm trong đơn hàng
•	Theo dõi thời hạn bảo hành
•	Cập nhật trạng thái bảo hành
•	Lịch sử bảo hành sản phẩm
2.1.3. Quản lý hóa đơn bán hàng
•	Danh sách hóa đơn đã bán
•	Tìm kiếm hóa đơn theo nhiều tiêu chí
•	Chi tiết hóa đơn
•	In lại hóa đơn
•	Xử lý đổi/trả hàng
2.2. Giao diện quản trị (Backend)
2.2.1. Quản lý sản phẩm
•	Danh mục sản phẩm:
o	Thêm/sửa/xóa danh mục
o	Hỗ trợ danh mục đa cấp
o	Sắp xếp thứ tự hiển thị
•	Sản phẩm:
o	Thêm/sửa/xóa sản phẩm
o	Quản lý thuộc tính sản phẩm (màu sắc, kích thước...)
o	Quản lý hình ảnh sản phẩm
o	Quản lý giá bán, giá nhập
o	Mã vạch sản phẩm
o	Cài đặt số lượng tồn kho tối thiểu
o	Quản lý sản phẩm combo/bộ
•	Đơn vị tính:
o	Thêm/sửa/xóa đơn vị tính
o	Quản lý đơn vị tính cơ bản và quy đổi
2.2.2. Quản lý khách hàng
•	Thêm/sửa/xóa thông tin khách hàng
•	Phân loại khách hàng
•	Lịch sử mua hàng của khách
•	Công nợ khách hàng
•	Quản lý điểm thưởng/tích lũy
2.2.3. Quản lý đơn hàng
•	Danh sách đơn hàng
•	Lọc đơn hàng theo trạng thái, thời gian, nhân viên
•	Chi tiết đơn hàng
•	Xử lý đơn hàng (hủy, hoàn thành, đổi trả)
•	Lịch sử thanh toán
2.2.4. Quản lý kho hàng
•	Quản lý danh sách kho:
o	Thêm/sửa/xóa kho hàng
o	Thiết lập kho mặc định
o	Phân quyền quản lý kho theo người dùng
o	Thiết lập thông tin kho (tên, địa chỉ, người phụ trách)
o	Kích hoạt/vô hiệu hóa kho
•	Tồn kho:
o	Xem số lượng tồn kho thực tế theo từng kho
o	Lọc tồn kho theo kho, danh mục, sản phẩm
o	Báo cáo hàng sắp hết theo từng kho
o	Báo cáo hàng tồn kho theo thời gian
o	Cảnh báo mức tồn kho tối thiểu theo từng kho
o	Kiểm kê kho riêng biệt cho từng kho
•	Nhập kho:
o	Tạo phiếu nhập kho cho kho cụ thể
o	Nhập từ nhà cung cấp vào kho được chọn
o	Nhập kho nội bộ
o	In phiếu nhập kho
o	Lịch sử nhập kho theo từng kho
•	Xuất kho:
o	Tạo phiếu xuất kho từ kho cụ thể
o	Xuất cho đơn hàng từ kho được chọn
o	Xuất hủy, xuất trả nhà cung cấp
o	In phiếu xuất kho
o	Lịch sử xuất kho theo từng kho
•	Chuyển kho:
o	Tạo phiếu chuyển kho giữa các kho hàng
o	Chọn kho nguồn và kho đích
o	Chọn sản phẩm và số lượng cần chuyển
o	Theo dõi trạng thái chuyển kho (đang chuyển, đã nhận, đã hủy)
o	Xác nhận nhận hàng chuyển kho
o	In phiếu chuyển kho
o	Báo cáo lịch sử chuyển kho
2.2.5. Quản lý nhà cung cấp
•	Thêm/sửa/xóa nhà cung cấp
•	Lịch sử nhập hàng từ nhà cung cấp
•	Công nợ nhà cung cấp
•	Đánh giá nhà cung cấp
2.2.6. Quản lý bảo hành
•	Danh sách phiếu bảo hành
•	Tìm kiếm theo mã bảo hành, sản phẩm, khách hàng
•	Cập nhật trạng thái bảo hành
•	Báo cáo bảo hành
2.2.7. Quản lý người dùng và phân quyền
•	Thêm/sửa/xóa tài khoản nhân viên
•	Phân quyền người dùng theo vai trò (RBAC)
•	Quản lý vai trò và quyền hạn
•	Phân quyền theo chức năng và dữ liệu
•	Lịch sử đăng nhập
•	Khóa/mở khóa tài khoản
2.2.8. Báo cáo bán hàng
•	Báo cáo doanh thu theo ngày/tuần/tháng/năm
•	Báo cáo theo nhân viên
•	Báo cáo theo sản phẩm
•	Báo cáo lợi nhuận
•	Báo cáo khách hàng
•	Xuất báo cáo (Excel, PDF)
2.2.9. Quản lý tài chính
•	Phiếu thu:
o	Tạo phiếu thu
o	Phiếu thu từ khách hàng
o	Phiếu thu khác
o	In phiếu thu
•	Phiếu chi:
o	Tạo phiếu chi
o	Phiếu chi cho nhà cung cấp
o	Phiếu chi khác
o	In phiếu chi
•	Sổ quỹ:
o	Theo dõi thu chi
o	Báo cáo tồn quỹ
o	Đối chiếu quỹ
3. THIẾT KẾ HỆ THỐNG
3.1. Kiến trúc tổng thể
Hệ thống được xây dựng theo mô hình MVC (Model-View-Controller) của Yii2 Advanced với ba ứng dụng chính:
•	Backend: Giao diện quản trị dành cho quản lý cửa hàng
•	Frontend: Giao diện POS dành cho nhân viên bán hàng
•	API: RESTful API cho tích hợp với các ứng dụng khác
3.2. Cấu trúc cơ sở dữ liệu
Cơ sở dữ liệu được thiết kế với các nhóm bảng chính:
•	Quản lý sản phẩm: product, product_category, product_attribute, product_image, product_unit...
•	Quản lý kho: warehouse, stock, stock_movement, stock_in, stock_out, stock_transfer...
•	Bán hàng: order, order_detail, payment_method, discount, return...
•	Khách hàng & Nhà cung cấp: customer, customer_group, supplier...
•	Bảo hành: warranty, warranty_detail, warranty_status...
•	Người dùng & Phân quyền: user, auth_assignment, auth_item, auth_item_child...
•	Tài chính: receipt, payment, cash_book...
3.3. Giao diện người dùng
3.3.1. Giao diện POS
•	Thiết kế giao diện đơn giản, dễ sử dụng
•	Tối ưu cho màn hình cảm ứng
•	Bố cục: 
o	Bên trái: Danh sách sản phẩm, tìm kiếm
o	Bên phải: Giỏ hàng, thanh toán
o	Phía trên: Thông tin ca làm việc, nhân viên
o	Phía dưới: Các chức năng nhanh
3.3.2. Giao diện Backend
•	Sử dụng AdminLTE template
•	Responsive design
•	Navigation menu bên trái
•	Header chứa thông tin người dùng, thông báo
•	Nội dung chính ở giữa
•	Dashboard tổng quan
4. QUY TRÌNH NGHIỆP VỤ
4.1. Quy trình bán hàng
1.	Nhân viên đăng nhập và mở ca làm việc
2.	Thêm sản phẩm vào giỏ hàng
3.	Chọn khách hàng hoặc thêm khách hàng mới
4.	Áp dụng giảm giá (nếu có)
5.	Chọn phương thức thanh toán
6.	Hoàn tất đơn hàng
7.	In hóa đơn và phiếu bảo hành
8.	Cập nhật tồn kho
4.2. Quy trình nhập hàng
1.	Tạo phiếu nhập kho
2.	Chọn kho hàng đích để nhập
3.	Chọn nhà cung cấp
4.	Thêm sản phẩm vào phiếu nhập
5.	Nhập số lượng, giá nhập
6.	Hoàn tất phiếu nhập
7.	Cập nhật tồn kho của kho hàng đã chọn
8.	Tạo phiếu chi (nếu thanh toán ngay)
4.3. Quy trình chuyển kho
1.	Tạo phiếu chuyển kho
2.	Chọn kho nguồn và kho đích
3.	Thêm sản phẩm vào phiếu chuyển kho
4.	Nhập số lượng cần chuyển
5.	Hoàn tất phiếu chuyển kho và ghi chú vận chuyển
6.	Cập nhật trạng thái "Đang chuyển"
7.	Kho đích xác nhận nhận hàng
8.	Cập nhật trạng thái "Đã nhận"
9.	Hệ thống tự động cập nhật tồn kho ở cả hai kho
4.4. Quy trình bảo hành
1.	Tìm kiếm thông tin bảo hành theo mã đơn hàng/sản phẩm
2.	Tạo phiếu bảo hành
3.	Cập nhật trạng thái bảo hành
4.	Thông báo cho khách hàng
5.	Hoàn tất bảo hành
4.5. Quy trình kiểm kê kho
1.	Tạo phiếu kiểm kê cho kho cụ thể
2.	Chọn danh sách sản phẩm cần kiểm kê hoặc kiểm kê toàn bộ kho
3.	Nhập số lượng thực tế đếm được
4.	Hệ thống tính toán chênh lệch giữa số lượng thực tế và số lượng trong hệ thống
5.	Xác nhận điều chỉnh
6.	Cập nhật tồn kho
7.	Lưu lịch sử kiểm kê kho
5. BÁO CÁO VÀ THỐNG KÊ
5.1. Báo cáo bán hàng
•	Báo cáo doanh thu theo ngày/tuần/tháng/năm
•	Báo cáo bán hàng theo sản phẩm
•	Báo cáo bán hàng theo nhân viên
•	Báo cáo bán hàng theo khách hàng
•	Biểu đồ doanh số
5.2. Báo cáo kho hàng
•	Báo cáo tồn kho theo từng kho
•	Báo cáo nhập xuất tồn theo từng kho
•	Báo cáo hàng sắp hết theo từng kho
•	Báo cáo giá trị hàng tồn kho theo từng kho
•	Báo cáo chuyển kho
•	Báo cáo so sánh tồn kho giữa các kho
5.3. Báo cáo tài chính
•	Báo cáo thu chi
•	Báo cáo lợi nhuận
•	Báo cáo công nợ khách hàng
•	Báo cáo công nợ nhà cung cấp
5.4. Báo cáo khách hàng
•	Phân tích khách hàng thường xuyên
•	Giá trị đơn hàng trung bình
•	Tần suất mua hàng
•	Phân tích theo nhóm khách hàng
5.5. Dashboard tổng quan
•	Tổng quan doanh thu, lợi nhuận
•	Chỉ số KPI chính
•	Cảnh báo và thông báo quan trọng
•	Biểu đồ xu hướng
6. BẢO MẬT VÀ PHÂN QUYỀN
6.1. Phân quyền người dùng
•	Sử dụng RBAC (Role-Based Access Control) của Yii2
•	Phân quyền theo vai trò (Admin, Quản lý, Nhân viên bán hàng, Kế toán...)
•	Phân quyền theo chức năng (xem, thêm, sửa, xóa)
•	Phân quyền theo dữ liệu (kho hàng, chi nhánh)
6.2. Bảo mật hệ thống
•	Xác thực người dùng an toàn
•	Quản lý phiên làm việc
•	Nhật ký hoạt động người dùng
•	Sao lưu dữ liệu định kỳ
7. TÍCH HỢP VÀ MỞ RỘNG
7.1. Tích hợp với thiết bị ngoại vi
•	Máy in hóa đơn
•	Máy quét mã vạch
•	Máy đọc thẻ
•	Ngăn kéo đựng tiền
7.2. API cho ứng dụng di động
•	RESTful API cho frontend app
•	Xác thực và bảo mật API
•	Endpoints cho quản lý đơn hàng, sản phẩm, kho
7.3. Mở rộng chức năng
•	Mô-đun CRM (quản lý khách hàng nâng cao)
•	Mô-đun bán hàng online
•	Mô-đun báo cáo nâng cao

