<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use app\models\User;

/**
 * Controller để thiết lập phân quyền RBAC
 */
class RbacController extends Controller
{
    /**
     * Khởi tạo quyền và vai trò
     */
    public function actionInit()
    {
        $auth = Yii::$app->authManager;
        $auth->removeAll(); // Xóa tất cả quyền, vai trò hiện có

        // Tạo các quyền cho từng module

        // 1. Quản lý sản phẩm
        $viewProducts = $auth->createPermission('viewProducts');
        $viewProducts->description = 'Xem danh sách sản phẩm';
        $auth->add($viewProducts);

        $createProduct = $auth->createPermission('createProduct');
        $createProduct->description = 'Thêm sản phẩm mới';
        $auth->add($createProduct);

        $updateProduct = $auth->createPermission('updateProduct');
        $updateProduct->description = 'Cập nhật sản phẩm';
        $auth->add($updateProduct);

        $deleteProduct = $auth->createPermission('deleteProduct');
        $deleteProduct->description = 'Xóa sản phẩm';
        $auth->add($deleteProduct);

        $manageProductCategories = $auth->createPermission('manageProductCategories');
        $manageProductCategories->description = 'Quản lý danh mục sản phẩm';
        $auth->add($manageProductCategories);

        $manageProductAttributes = $auth->createPermission('manageProductAttributes');
        $manageProductAttributes->description = 'Quản lý thuộc tính sản phẩm';
        $auth->add($manageProductAttributes);

        $manageProductUnits = $auth->createPermission('manageProductUnits');
        $manageProductUnits->description = 'Quản lý đơn vị tính';
        $auth->add($manageProductUnits);

        $manageProductPrices = $auth->createPermission('manageProductPrices');
        $manageProductPrices->description = 'Quản lý giá sản phẩm';
        $auth->add($manageProductPrices);

        // 2. Quản lý kho hàng
        $viewInventory = $auth->createPermission('viewInventory');
        $viewInventory->description = 'Xem tồn kho';
        $auth->add($viewInventory);

        $manageWarehouses = $auth->createPermission('manageWarehouses');
        $manageWarehouses->description = 'Quản lý kho hàng';
        $auth->add($manageWarehouses);

        $createStockIn = $auth->createPermission('createStockIn');
        $createStockIn->description = 'Tạo phiếu nhập kho';
        $auth->add($createStockIn);

        $approveStockIn = $auth->createPermission('approveStockIn');
        $approveStockIn->description = 'Duyệt phiếu nhập kho';
        $auth->add($approveStockIn);

        $createStockOut = $auth->createPermission('createStockOut');
        $createStockOut->description = 'Tạo phiếu xuất kho';
        $auth->add($createStockOut);
        
        $approveStockOut = $auth->createPermission('approveStockOut');
        $approveStockOut->description = 'Duyệt phiếu xuất kho';
        $auth->add($approveStockOut);

        $createStockTransfer = $auth->createPermission('createStockTransfer');
        $createStockTransfer->description = 'Tạo phiếu chuyển kho';
        $auth->add($createStockTransfer);

        $approveStockTransfer = $auth->createPermission('approveStockTransfer');
        $approveStockTransfer->description = 'Duyệt phiếu chuyển kho';
        $auth->add($approveStockTransfer);

        $receiveStockTransfer = $auth->createPermission('receiveStockTransfer');
        $receiveStockTransfer->description = 'Nhận chuyển kho';
        $auth->add($receiveStockTransfer);

        $createStockCheck = $auth->createPermission('createStockCheck');
        $createStockCheck->description = 'Tạo phiếu kiểm kê';
        $auth->add($createStockCheck);

        $approveStockCheck = $auth->createPermission('approveStockCheck');
        $approveStockCheck->description = 'Duyệt phiếu kiểm kê';
        $auth->add($approveStockCheck);

        // 3. Quản lý bán hàng
        $accessPOS = $auth->createPermission('accessPOS');
        $accessPOS->description = 'Truy cập màn hình bán hàng POS';
        $auth->add($accessPOS);

        $createOrder = $auth->createPermission('createOrder');
        $createOrder->description = 'Tạo đơn hàng';
        $auth->add($createOrder);

        $viewOrders = $auth->createPermission('viewOrders');
        $viewOrders->description = 'Xem danh sách đơn hàng';
        $auth->add($viewOrders);

        $updateOrder = $auth->createPermission('updateOrder');
        $updateOrder->description = 'Cập nhật đơn hàng';
        $auth->add($updateOrder);

        $cancelOrder = $auth->createPermission('cancelOrder');
        $cancelOrder->description = 'Hủy đơn hàng';
        $auth->add($cancelOrder);

        $processReturn = $auth->createPermission('processReturn');
        $processReturn->description = 'Xử lý đơn trả hàng';
        $auth->add($processReturn);
        
        $manageDiscounts = $auth->createPermission('manageDiscounts');
        $manageDiscounts->description = 'Quản lý chương trình giảm giá';
        $auth->add($manageDiscounts);

        // 4. Quản lý khách hàng
        $viewCustomers = $auth->createPermission('viewCustomers');
        $viewCustomers->description = 'Xem danh sách khách hàng';
        $auth->add($viewCustomers);

        $createCustomer = $auth->createPermission('createCustomer');
        $createCustomer->description = 'Thêm khách hàng mới';
        $auth->add($createCustomer);

        $updateCustomer = $auth->createPermission('updateCustomer');
        $updateCustomer->description = 'Cập nhật thông tin khách hàng';
        $auth->add($updateCustomer);

        $deleteCustomer = $auth->createPermission('deleteCustomer');
        $deleteCustomer->description = 'Xóa khách hàng';
        $auth->add($deleteCustomer);

        $manageCustomerGroups = $auth->createPermission('manageCustomerGroups');
        $manageCustomerGroups->description = 'Quản lý nhóm khách hàng';
        $auth->add($manageCustomerGroups);

        $manageCustomerPoints = $auth->createPermission('manageCustomerPoints');
        $manageCustomerPoints->description = 'Quản lý điểm tích lũy khách hàng';
        $auth->add($manageCustomerPoints);

        // 5. Quản lý nhà cung cấp
        $viewSuppliers = $auth->createPermission('viewSuppliers');
        $viewSuppliers->description = 'Xem danh sách nhà cung cấp';
        $auth->add($viewSuppliers);

        $createSupplier = $auth->createPermission('createSupplier');
        $createSupplier->description = 'Thêm nhà cung cấp mới';
        $auth->add($createSupplier);

        $updateSupplier = $auth->createPermission('updateSupplier');
        $updateSupplier->description = 'Cập nhật thông tin nhà cung cấp';
        $auth->add($updateSupplier);

        $deleteSupplier = $auth->createPermission('deleteSupplier');
        $deleteSupplier->description = 'Xóa nhà cung cấp';
        $auth->add($deleteSupplier);

        $manageSupplierProducts = $auth->createPermission('manageSupplierProducts');
        $manageSupplierProducts->description = 'Quản lý sản phẩm từ nhà cung cấp';
        $auth->add($manageSupplierProducts);

        // 6. Quản lý bảo hành
        $viewWarranties = $auth->createPermission('viewWarranties');
        $viewWarranties->description = 'Xem danh sách bảo hành';
        $auth->add($viewWarranties);

        $createWarranty = $auth->createPermission('createWarranty');
        $createWarranty->description = 'Tạo phiếu bảo hành';
        $auth->add($createWarranty);

        $updateWarranty = $auth->createPermission('updateWarranty');
        $updateWarranty->description = 'Cập nhật phiếu bảo hành';
        $auth->add($updateWarranty);

        $manageWarrantyStatus = $auth->createPermission('manageWarrantyStatus');
        $manageWarrantyStatus->description = 'Quản lý trạng thái bảo hành';
        $auth->add($manageWarrantyStatus);

        // 7. Quản lý ca làm việc
        $manageShifts = $auth->createPermission('manageShifts');
        $manageShifts->description = 'Quản lý ca làm việc';
        $auth->add($manageShifts);

        $openShift = $auth->createPermission('openShift');
        $openShift->description = 'Mở ca làm việc';
        $auth->add($openShift);

        $closeShift = $auth->createPermission('closeShift');
        $closeShift->description = 'Đóng ca làm việc';
        $auth->add($closeShift);

        // 8. Quản lý tài chính
        $manageReceipts = $auth->createPermission('manageReceipts');
        $manageReceipts->description = 'Quản lý phiếu thu';
        $auth->add($manageReceipts);

        $managePayments = $auth->createPermission('managePayments');
        $managePayments->description = 'Quản lý phiếu chi';
        $auth->add($managePayments);

        $viewCashBook = $auth->createPermission('viewCashBook');
        $viewCashBook->description = 'Xem sổ quỹ';
        $auth->add($viewCashBook);
        
        $manageCustomerDebt = $auth->createPermission('manageCustomerDebt');
        $manageCustomerDebt->description = 'Quản lý công nợ khách hàng';
        $auth->add($manageCustomerDebt);
        
        $manageSupplierDebt = $auth->createPermission('manageSupplierDebt');
        $manageSupplierDebt->description = 'Quản lý công nợ nhà cung cấp';
        $auth->add($manageSupplierDebt);

        // 9. Báo cáo và thống kê
        $viewSalesReports = $auth->createPermission('viewSalesReports');
        $viewSalesReports->description = 'Xem báo cáo bán hàng';
        $auth->add($viewSalesReports);

        $viewInventoryReports = $auth->createPermission('viewInventoryReports');
        $viewInventoryReports->description = 'Xem báo cáo kho hàng';
        $auth->add($viewInventoryReports);
        
        $viewFinancialReports = $auth->createPermission('viewFinancialReports');
        $viewFinancialReports->description = 'Xem báo cáo tài chính';
        $auth->add($viewFinancialReports);

        $viewCustomerReports = $auth->createPermission('viewCustomerReports');
        $viewCustomerReports->description = 'Xem báo cáo khách hàng';
        $auth->add($viewCustomerReports);

        $viewDashboard = $auth->createPermission('viewDashboard');
        $viewDashboard->description = 'Xem dashboard tổng quan';
        $auth->add($viewDashboard);

        // 10. Quản lý hệ thống
        $manageUsers = $auth->createPermission('manageUsers');
        $manageUsers->description = 'Quản lý người dùng';
        $auth->add($manageUsers);

        $manageRoles = $auth->createPermission('manageRoles');
        $manageRoles->description = 'Quản lý vai trò và phân quyền';
        $auth->add($manageRoles);

        $manageSettings = $auth->createPermission('manageSettings');
        $manageSettings->description = 'Quản lý cài đặt hệ thống';
        $auth->add($manageSettings);
        
        $viewLogs = $auth->createPermission('viewLogs');
        $viewLogs->description = 'Xem nhật ký hệ thống';
        $auth->add($viewLogs);
        
        $managePaymentMethods = $auth->createPermission('managePaymentMethods');
        $managePaymentMethods->description = 'Quản lý phương thức thanh toán';
        $auth->add($managePaymentMethods);

        // Tạo các vai trò (roles)

        // 1. Admin - có toàn quyền trên hệ thống
        $admin = $auth->createRole('admin');
        $admin->description = 'Quản trị viên - có toàn quyền trên hệ thống';
        $auth->add($admin);
        
        // Gán tất cả quyền cho admin
        $permissions = $auth->getPermissions();
        foreach ($permissions as $permission) {
            $auth->addChild($admin, $permission);
        }

        // 2. Quản lý cửa hàng (Store Manager)
        $storeManager = $auth->createRole('storeManager');
        $storeManager->description = 'Quản lý cửa hàng';
        $auth->add($storeManager);

        // Phân quyền cho quản lý cửa hàng
        // Quản lý sản phẩm
        $auth->addChild($storeManager, $viewProducts);
        $auth->addChild($storeManager, $createProduct);
        $auth->addChild($storeManager, $updateProduct);
        $auth->addChild($storeManager, $manageProductCategories);
        $auth->addChild($storeManager, $manageProductAttributes);
        $auth->addChild($storeManager, $manageProductUnits);
        $auth->addChild($storeManager, $manageProductPrices);
        
        // Quản lý kho
        $auth->addChild($storeManager, $viewInventory);
        $auth->addChild($storeManager, $manageWarehouses);
        $auth->addChild($storeManager, $createStockIn);
        $auth->addChild($storeManager, $approveStockIn);
        $auth->addChild($storeManager, $createStockOut);
        $auth->addChild($storeManager, $approveStockOut);
        $auth->addChild($storeManager, $createStockTransfer);
        $auth->addChild($storeManager, $approveStockTransfer);
        $auth->addChild($storeManager, $receiveStockTransfer);
        $auth->addChild($storeManager, $createStockCheck);
        $auth->addChild($storeManager, $approveStockCheck);
        
        // Quản lý bán hàng
        $auth->addChild($storeManager, $accessPOS);
        $auth->addChild($storeManager, $createOrder);
        $auth->addChild($storeManager, $viewOrders);
        $auth->addChild($storeManager, $updateOrder);
        $auth->addChild($storeManager, $cancelOrder);
        $auth->addChild($storeManager, $processReturn);
        $auth->addChild($storeManager, $manageDiscounts);
        
        // Quản lý khách hàng và nhà cung cấp
        $auth->addChild($storeManager, $viewCustomers);
        $auth->addChild($storeManager, $createCustomer);
        $auth->addChild($storeManager, $updateCustomer);
        $auth->addChild($storeManager, $manageCustomerGroups);
        $auth->addChild($storeManager, $manageCustomerPoints);
        $auth->addChild($storeManager, $viewSuppliers);
        $auth->addChild($storeManager, $createSupplier);
        $auth->addChild($storeManager, $updateSupplier);
        $auth->addChild($storeManager, $manageSupplierProducts);
        
        // Quản lý bảo hành
        $auth->addChild($storeManager, $viewWarranties);
        $auth->addChild($storeManager, $createWarranty);
        $auth->addChild($storeManager, $updateWarranty);
        $auth->addChild($storeManager, $manageWarrantyStatus);
        
        // Quản lý ca làm việc
        $auth->addChild($storeManager, $manageShifts);
        $auth->addChild($storeManager, $openShift);
        $auth->addChild($storeManager, $closeShift);
        
        // Xem báo cáo
        $auth->addChild($storeManager, $viewSalesReports);
        $auth->addChild($storeManager, $viewInventoryReports);
        $auth->addChild($storeManager, $viewCustomerReports);
        $auth->addChild($storeManager, $viewDashboard);
        
        // Quản lý người dùng cơ bản
        $auth->addChild($storeManager, $manageUsers);

        // 3. Nhân viên bán hàng (Sales Staff)
        $salesStaff = $auth->createRole('salesStaff');
        $salesStaff->description = 'Nhân viên bán hàng';
        $auth->add($salesStaff);

        // Phân quyền cho nhân viên bán hàng
        $auth->addChild($salesStaff, $viewProducts);
        $auth->addChild($salesStaff, $accessPOS);
        $auth->addChild($salesStaff, $createOrder);
        $auth->addChild($salesStaff, $viewOrders);
        $auth->addChild($salesStaff, $processReturn);
        $auth->addChild($salesStaff, $viewCustomers);
        $auth->addChild($salesStaff, $createCustomer);
        $auth->addChild($salesStaff, $updateCustomer);
        $auth->addChild($salesStaff, $viewWarranties);
        $auth->addChild($salesStaff, $createWarranty);
        $auth->addChild($salesStaff, $updateWarranty);
        $auth->addChild($salesStaff, $openShift);
        $auth->addChild($salesStaff, $closeShift);
        $auth->addChild($salesStaff, $viewInventory);

        // 4. Nhân viên kho (Inventory Staff)
        $inventoryStaff = $auth->createRole('inventoryStaff');
        $inventoryStaff->description = 'Nhân viên kho hàng';
        $auth->add($inventoryStaff);

        // Phân quyền cho nhân viên kho
        $auth->addChild($inventoryStaff, $viewProducts);
        $auth->addChild($inventoryStaff, $viewInventory);
        $auth->addChild($inventoryStaff, $createStockIn);
        $auth->addChild($inventoryStaff, $createStockOut);
        $auth->addChild($inventoryStaff, $createStockTransfer);
        $auth->addChild($inventoryStaff, $receiveStockTransfer);
        $auth->addChild($inventoryStaff, $createStockCheck);
        $auth->addChild($inventoryStaff, $viewSuppliers);
        $auth->addChild($inventoryStaff, $viewInventoryReports);

        // 5. Kế toán (Accountant)
        $accountant = $auth->createRole('accountant');
        $accountant->description = 'Kế toán';
        $auth->add($accountant);

        // Phân quyền cho kế toán
        $auth->addChild($accountant, $viewOrders);
        $auth->addChild($accountant, $viewCustomers);
        $auth->addChild($accountant, $viewSuppliers);
        $auth->addChild($accountant, $manageReceipts);
        $auth->addChild($accountant, $managePayments);
        $auth->addChild($accountant, $viewCashBook);
        $auth->addChild($accountant, $manageCustomerDebt);
        $auth->addChild($accountant, $manageSupplierDebt);
        $auth->addChild($accountant, $viewSalesReports);
        $auth->addChild($accountant, $viewFinancialReports);
        $auth->addChild($accountant, $viewDashboard);

        // 6. Nhân viên bảo hành (Warranty Staff)
        $warrantyStaff = $auth->createRole('warrantyStaff');
        $warrantyStaff->description = 'Nhân viên bảo hành';
        $auth->add($warrantyStaff);

        // Phân quyền cho nhân viên bảo hành
        $auth->addChild($warrantyStaff, $viewProducts);
        $auth->addChild($warrantyStaff, $viewCustomers);
        $auth->addChild($warrantyStaff, $viewWarranties);
        $auth->addChild($warrantyStaff, $createWarranty);
        $auth->addChild($warrantyStaff, $updateWarranty);

        // Gán admin cho user có ID = 1 (thường là admin mặc định)
        $auth->assign($admin, 1);

        Console::output('Đã khởi tạo RBAC thành công!');
    }
}