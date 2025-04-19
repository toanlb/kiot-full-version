-- 1. QUẢN LÝ NGƯỜI DÙNG VÀ PHÂN QUYỀN (RBAC)

-- 1.1. Bảng user
CREATE TABLE `user` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`username` varchar(255) NOT NULL,
`auth_key` varchar(32) NOT NULL,
`password_hash` varchar(255) NOT NULL,
`password_reset_token` varchar(255) DEFAULT NULL,
`email` varchar(255) NOT NULL,
`status` smallint(6) NOT NULL DEFAULT '10',
`created_at` int(11) NOT NULL,
`updated_at` int(11) NOT NULL,
`verification_token` varchar(255) DEFAULT NULL,
`full_name` varchar(255) NOT NULL,
`phone` varchar(20) DEFAULT NULL,
`avatar` varchar(255) DEFAULT NULL,
`warehouse_id` int(11) DEFAULT NULL,
`last_login_at` datetime DEFAULT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `username` (`username`),
UNIQUE KEY `email` (`email`),
UNIQUE KEY `password_reset_token` (`password_reset_token`),
KEY `idx-user-warehouse_id` (`warehouse_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.2. Bảng auth_assignment
CREATE TABLE `auth_assignment` (
`item_name` varchar(64) NOT NULL,
`user_id` varchar(64) NOT NULL,
`created_at` int(11) DEFAULT NULL,
PRIMARY KEY (`item_name`,`user_id`),
KEY `idx-auth_assignment-user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.3. Bảng auth_item
CREATE TABLE `auth_item` (
`name` varchar(64) NOT NULL,
`type` smallint(6) NOT NULL,
`description` text,
`rule_name` varchar(64) DEFAULT NULL,
`data` blob,
`created_at` int(11) DEFAULT NULL,
`updated_at` int(11) DEFAULT NULL,
PRIMARY KEY (`name`),
KEY `rule_name` (`rule_name`),
KEY `idx-auth_item-type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.4. Bảng auth_item_child
CREATE TABLE `auth_item_child` (
`parent` varchar(64) NOT NULL,
`child` varchar(64) NOT NULL,
PRIMARY KEY (`parent`,`child`),
KEY `child` (`child`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.5. Bảng auth_rule
CREATE TABLE `auth_rule` (
`name` varchar(64) NOT NULL,
`data` blob,
`created_at` int(11) DEFAULT NULL,
`updated_at` int(11) DEFAULT NULL,
PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.6. Bảng user_profile
CREATE TABLE `user_profile` (
`user_id` int(11) NOT NULL,
`address` varchar(255) DEFAULT NULL,
`city` varchar(100) DEFAULT NULL,
`country` varchar(100) DEFAULT NULL,
`birthday` date DEFAULT NULL,
`position` varchar(100) DEFAULT NULL,
`department` varchar(100) DEFAULT NULL,
`hire_date` date DEFAULT NULL,
`identity_card` varchar(20) DEFAULT NULL,
`notes` text,
PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.7. Bảng user_warehouse
CREATE TABLE `user_warehouse` (
`user_id` int(11) NOT NULL,
`warehouse_id` int(11) NOT NULL,
`created_at` datetime NOT NULL,
PRIMARY KEY (`user_id`,`warehouse_id`),
KEY `idx-user_warehouse-warehouse_id` (`warehouse_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.8. Bảng login_history
CREATE TABLE `login_history` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`user_id` int(11) NOT NULL,
`login_time` datetime NOT NULL,
`logout_time` datetime DEFAULT NULL,
`ip_address` varchar(50) DEFAULT NULL,
`user_agent` varchar(255) DEFAULT NULL,
`success` tinyint(1) NOT NULL DEFAULT '1',
`failure_reason` varchar(255) DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `idx-login_history-user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. QUẢN LÝ SẢN PHẨM VÀ DANH MỤC

-- 2.1. Bảng product_category
CREATE TABLE `product_category` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`parent_id` int(11) DEFAULT NULL,
`name` varchar(255) NOT NULL,
`slug` varchar(255) NOT NULL,
`description` text,
`image` varchar(255) DEFAULT NULL,
`status` tinyint(1) NOT NULL DEFAULT '1',
`sort_order` int(11) DEFAULT '0',
`created_at` datetime NOT NULL,
`updated_at` datetime NOT NULL,
`created_by` int(11) DEFAULT NULL,
`updated_by` int(11) DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `idx-product_category-parent_id` (`parent_id`),
KEY `idx-product_category-slug` (`slug`),
KEY `idx-product_category-status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.2. Bảng product
CREATE TABLE `product` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`category_id` int(11) DEFAULT NULL,
`code` varchar(50) NOT NULL,
`barcode` varchar(50) DEFAULT NULL,
`name` varchar(255) NOT NULL,
`slug` varchar(255) NOT NULL,
`description` text,
`short_description` varchar(500) DEFAULT NULL,
`unit_id` int(11) NOT NULL,
`cost_price` decimal(15,2) NOT NULL DEFAULT '0.00',
`selling_price` decimal(15,2) NOT NULL DEFAULT '0.00',
`min_stock` int(11) DEFAULT '0',
`status` tinyint(1) NOT NULL DEFAULT '1',
`is_combo` tinyint(1) NOT NULL DEFAULT '0',
`weight` decimal(10,3) DEFAULT NULL,
`dimension` varchar(50) DEFAULT NULL,
`warranty_period` int(11) DEFAULT '0',
`created_at` datetime NOT NULL,
`updated_at` datetime NOT NULL,
`created_by` int(11) DEFAULT NULL,
`updated_by` int(11) DEFAULT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `code` (`code`),
UNIQUE KEY `slug` (`slug`),
KEY `idx-product-category_id` (`category_id`),
KEY `idx-product-unit_id` (`unit_id`),
KEY `idx-product-status` (`status`),
KEY `idx-product-barcode` (`barcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.3. Bảng product_image
CREATE TABLE `product_image` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`product_id` int(11) NOT NULL,
`image` varchar(255) NOT NULL,
`sort_order` int(11) DEFAULT '0',
`is_main` tinyint(1) NOT NULL DEFAULT '0',
`created_at` datetime NOT NULL,
PRIMARY KEY (`id`),
KEY `idx-product_image-product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.4. Bảng product_attribute
CREATE TABLE `product_attribute` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`name` varchar(255) NOT NULL,
`sort_order` int(11) DEFAULT '0',
`is_filterable` tinyint(1) NOT NULL DEFAULT '0',
`created_at` datetime NOT NULL,
`updated_at` datetime NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.5. Bảng product_attribute_value
CREATE TABLE `product_attribute_value` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`product_id` int(11) NOT NULL,
`attribute_id` int(11) NOT NULL,
`value` varchar(255) NOT NULL,
`created_at` datetime NOT NULL,
`updated_at` datetime NOT NULL,
PRIMARY KEY (`id`),
KEY `idx-product_attribute_value-product_id` (`product_id`),
KEY `idx-product_attribute_value-attribute_id` (`attribute_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.6. Bảng product_unit
CREATE TABLE `product_unit` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`name` varchar(255) NOT NULL,
`abbreviation` varchar(10) DEFAULT NULL,
`is_default` tinyint(1) NOT NULL DEFAULT '0',
`created_at` datetime NOT NULL,
`updated_at` datetime NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.7. Bảng product_unit_conversion
CREATE TABLE `product_unit_conversion` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`product_id` int(11) NOT NULL,
`from_unit_id` int(11) NOT NULL,
`to_unit_id` int(11) NOT NULL,
`conversion_factor` decimal(15,5) NOT NULL,
`created_at` datetime NOT NULL,
`updated_at` datetime NOT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `product_from_to` (`product_id`,`from_unit_id`,`to_unit_id`),
KEY `idx-product_unit_conversion-from_unit_id` (`from_unit_id`),
KEY `idx-product_unit_conversion-to_unit_id` (`to_unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.8. Bảng product_combo
CREATE TABLE `product_combo` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`combo_id` int(11) NOT NULL,
`product_id` int(11) NOT NULL,
`quantity` int(11) NOT NULL,
`unit_id` int(11) NOT NULL,
`created_at` datetime NOT NULL,
`updated_at` datetime NOT NULL,
PRIMARY KEY (`id`),
KEY `idx-product_combo-combo_id` (`combo_id`),
KEY `idx-product_combo-product_id` (`product_id`),
KEY `idx-product_combo-unit_id` (`unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.9. Bảng product_price_history
CREATE TABLE `product_price_history` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`product_id` int(11) NOT NULL,
`cost_price` decimal(15,2) NOT NULL DEFAULT '0.00',
`selling_price` decimal(15,2) NOT NULL DEFAULT '0.00',
`effective_date` datetime NOT NULL,
`created_at` datetime NOT NULL,
`created_by` int(11) DEFAULT NULL,
`note` text,
PRIMARY KEY (`id`),
KEY `idx-product_price_history-product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. QUẢN LÝ KHO HÀNG

-- 3.1. Bảng warehouse
CREATE TABLE `warehouse` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`code` varchar(50) NOT NULL,
`name` varchar(255) NOT NULL,
`address` varchar(500) DEFAULT NULL,
`phone` varchar(20) DEFAULT NULL,
`manager_id` int(11) DEFAULT NULL,
`is_default` tinyint(1) NOT NULL DEFAULT '0',
`is_active` tinyint(1) NOT NULL DEFAULT '1',
`description` text,
`created_at` datetime NOT NULL,
`updated_at` datetime NOT NULL,
`created_by` int(11) DEFAULT NULL,
`updated_by` int(11) DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `idx-warehouse-manager_id` (`manager_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.2. Bảng stock
CREATE TABLE `stock` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`product_id` int(11) NOT NULL,
`warehouse_id` int(11) NOT NULL,
`quantity` int(11) NOT NULL DEFAULT '0',
`min_stock` int(11) DEFAULT NULL,
`updated_at` datetime NOT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `product_warehouse` (`product_id`,`warehouse_id`),
KEY `idx-stock-warehouse_id` (`warehouse_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.3. Bảng stock_movement
CREATE TABLE `stock_movement` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`product_id` int(11) NOT NULL,
`source_warehouse_id` int(11) DEFAULT NULL,
`destination_warehouse_id` int(11) DEFAULT NULL,
`reference_id` int(11) DEFAULT NULL,
`reference_type` varchar(50) DEFAULT NULL,
`quantity` int(11) NOT NULL,
`balance` int(11) NOT NULL,
`unit_id` int(11) NOT NULL,
`movement_type` tinyint(1) NOT NULL COMMENT '1: in, 2: out, 3: transfer, 4: check',
`movement_date` datetime NOT NULL,
`note` text,
`created_at` datetime NOT NULL,
`created_by` int(11) DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `idx-stock_movement-product_id` (`product_id`),
KEY `idx-stock_movement-source_warehouse_id` (`source_warehouse_id`),
KEY `idx-stock_movement-destination_warehouse_id` (`destination_warehouse_id`),
KEY `idx-stock_movement-reference_id-reference_type` (`reference_id`,`reference_type`),
KEY `idx-stock_movement-unit_id` (`unit_id`),
KEY `idx-stock_movement-movement_type` (`movement_type`),
KEY `idx-stock_movement-movement_date` (`movement_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.4. Bảng stock_in
CREATE TABLE `stock_in` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`code` varchar(50) NOT NULL,
`warehouse_id` int(11) NOT NULL,
`supplier_id` int(11) DEFAULT NULL,
`stock_in_date` datetime NOT NULL,
`reference_number` varchar(100) DEFAULT NULL,
`total_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
`discount_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
`tax_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
`final_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
`paid_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
`payment_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0: unpaid, 1: partially, 2: paid',
`status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0: draft, 1: confirmed, 2: completed, 3: canceled',
`note` text,
`created_at` datetime NOT NULL,
`updated_at` datetime NOT NULL,
`created_by` int(11) DEFAULT NULL,
`approved_by` int(11) DEFAULT NULL,
`approved_at` datetime DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `idx-stock_in-warehouse_id` (`warehouse_id`),
KEY `idx-stock_in-supplier_id` (`supplier_id`),
KEY `idx-stock_in-stock_in_date` (`stock_in_date`),
KEY `idx-stock_in-status` (`status`),
KEY `idx-stock_in-payment_status` (`payment_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.5. Bảng stock_in_detail
CREATE TABLE `stock_in_detail` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`stock_in_id` int(11) NOT NULL,
`product_id` int(11) NOT NULL,
`batch_number` varchar(100) DEFAULT NULL,
`expiry_date` date DEFAULT NULL,
`quantity` int(11) NOT NULL,
`unit_id` int(11) NOT NULL,
`unit_price` decimal(15,2) NOT NULL DEFAULT '0.00',
`discount_percent` decimal(5,2) DEFAULT '0.00',
`discount_amount` decimal(15,2) DEFAULT '0.00',
`tax_percent` decimal(5,2) DEFAULT '0.00',
`tax_amount` decimal(15,2) DEFAULT '0.00',
`total_price` decimal(15,2) NOT NULL DEFAULT '0.00',
`note` text,
PRIMARY KEY (`id`),
KEY `idx-stock_in_detail-stock_in_id` (`stock_in_id`),
KEY `idx-stock_in_detail-product_id` (`product_id`),
KEY `idx-stock_in_detail-unit_id` (`unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.6. Bảng stock_out
CREATE TABLE `stock_out` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`code` varchar(50) NOT NULL,
`warehouse_id` int(11) NOT NULL,
`reference_id` int(11) DEFAULT NULL,
`reference_type` varchar(50) DEFAULT NULL,
`recipient` varchar(255) DEFAULT NULL,
`stock_out_date` datetime NOT NULL,
`total_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
`status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0: draft, 1: confirmed, 2: completed, 3: canceled',
`note` text,
`created_at` datetime NOT NULL,
`updated_at` datetime NOT NULL,
`created_by` int(11) DEFAULT NULL,
`approved_by` int(11) DEFAULT NULL,
`approved_at` datetime DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `idx-stock_out-warehouse_id` (`warehouse_id`),
KEY `idx-stock_out-reference_id-reference_type` (`reference_id`,`reference_type`),
KEY `idx-stock_out-stock_out_date` (`stock_out_date`),
KEY `idx-stock_out-status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.7. Bảng stock_out_detail
CREATE TABLE `stock_out_detail` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`stock_out_id` int(11) NOT NULL,
`product_id` int(11) NOT NULL,
`batch_number` varchar(100) DEFAULT NULL,
`quantity` int(11) NOT NULL,
`unit_id` int(11) NOT NULL,
`unit_price` decimal(15,2) NOT NULL DEFAULT '0.00',
`total_price` decimal(15,2) NOT NULL DEFAULT '0.00',
`note` text,
PRIMARY KEY (`id`),
KEY `idx-stock_out_detail-stock_out_id` (`stock_out_id`),
KEY `idx-stock_out_detail-product_id` (`product_id`),
KEY `idx-stock_out_detail-unit_id` (`unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.8. Bảng stock_transfer
CREATE TABLE `stock_transfer` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`code` varchar(50) NOT NULL,
`source_warehouse_id` int(11) NOT NULL,
`destination_warehouse_id` int(11) NOT NULL,
`transfer_date` datetime NOT NULL,
`status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0: draft, 1: confirmed, 2: in progress, 3: received, 4: canceled',
`note` text,
`created_at` datetime NOT NULL,
`updated_at` datetime NOT NULL,
`created_by` int(11) DEFAULT NULL,
`approved_by` int(11) DEFAULT NULL,
`approved_at` datetime DEFAULT NULL,
`received_by` int(11) DEFAULT NULL,
`received_at` datetime DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `idx-stock_transfer-source_warehouse_id` (`source_warehouse_id`),
KEY `idx-stock_transfer-destination_warehouse_id` (`destination_warehouse_id`),
KEY `idx-stock_transfer-transfer_date` (`transfer_date`),
KEY `idx-stock_transfer-status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.9. Bảng stock_transfer_detail
CREATE TABLE `stock_transfer_detail` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`stock_transfer_id` int(11) NOT NULL,
`product_id` int(11) NOT NULL,
`batch_number` varchar(100) DEFAULT NULL,
`quantity` int(11) NOT NULL,
`received_quantity` int(11) DEFAULT NULL,
`unit_id` int(11) NOT NULL,
`note` text,
PRIMARY KEY (`id`),
KEY `idx-stock_transfer_detail-stock_transfer_id` (`stock_transfer_id`),
KEY `idx-stock_transfer_detail-product_id` (`product_id`),
KEY `idx-stock_transfer_detail-unit_id` (`unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.10. Bảng stock_check
CREATE TABLE `stock_check` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`code` varchar(50) NOT NULL,
`warehouse_id` int(11) NOT NULL,
`check_date` datetime NOT NULL,
`status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0: draft, 1: confirmed, 2: adjusted, 3: canceled',
`note` text,
`created_at` datetime NOT NULL,
`updated_at` datetime NOT NULL,
`created_by` int(11) DEFAULT NULL,
`approved_by` int(11) DEFAULT NULL,
`approved_at` datetime DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `idx-stock_check-warehouse_id` (`warehouse_id`),
KEY `idx-stock_check-check_date` (`check_date`),
KEY `idx-stock_check-status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.11. Bảng stock_check_detail
CREATE TABLE `stock_check_detail` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`stock_check_id` int(11) NOT NULL,
`product_id` int(11) NOT NULL,
`batch_number` varchar(100) DEFAULT NULL,
`system_quantity` int(11) NOT NULL,
`actual_quantity` int(11) NOT NULL,
`difference` int(11) NOT NULL,
`unit_id` int(11) NOT NULL,
`note` text,
`adjustment_approved` tinyint(1) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`),
KEY `idx-stock_check_detail-stock_check_id` (`stock_check_id`),
KEY `idx-stock_check_detail-product_id` (`product_id`),
KEY `idx-stock_check_detail-unit_id` (`unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.12. Bảng product_batch
CREATE TABLE `product_batch` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`product_id` int(11) NOT NULL,
`warehouse_id` int(11) NOT NULL,
`batch_number` varchar(100) NOT NULL,
`manufacturing_date` date DEFAULT NULL,
`expiry_date` date DEFAULT NULL,
`quantity` int(11) NOT NULL DEFAULT '0',
`cost_price` decimal(15,2) NOT NULL DEFAULT '0.00',
`stock_in_id` int(11) DEFAULT NULL,
`created_at` datetime NOT NULL,
`updated_at` datetime NOT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `product_warehouse_batch` (`product_id`,`warehouse_id`,`batch_number`),
KEY `idx-product_batch-warehouse_id` (`warehouse_id`),
KEY `idx-product_batch-expiry_date` (`expiry_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. QUẢN LÝ KHÁCH HÀNG VÀ NHÀ CUNG CẤP

-- 4.1. Bảng customer
CREATE TABLE `customer` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`code` varchar(50) NOT NULL,
`name` varchar(255) NOT NULL,
`phone` varchar(20) DEFAULT NULL,
`email` varchar(255) DEFAULT NULL,
`address` varchar(500) DEFAULT NULL,
`customer_group_id` int(11) DEFAULT NULL,
`tax_code` varchar(50) DEFAULT NULL,
`birthday` date DEFAULT NULL,
`gender` tinyint(1) DEFAULT '0' COMMENT '0: other, 1: male, 2: female',
`status` tinyint(1) NOT NULL DEFAULT '1',
`credit_limit` decimal(15,2) DEFAULT '0.00',
`debt_amount` decimal(15,2) DEFAULT '0.00',
`company_name` varchar(255) DEFAULT NULL,
`province_id` int(11) DEFAULT NULL,
`district_id` int(11) DEFAULT NULL,
`ward_id` int(11) DEFAULT NULL,
`created_at` datetime NOT NULL,
`updated_at` datetime NOT NULL,
`created_by` int(11) DEFAULT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `code` (`code`),
KEY `idx-customer-customer_group_id` (`customer_group_id`),
KEY `idx-customer-phone` (`phone`),
KEY `idx-customer-email` (`email`),
KEY `idx-customer-status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4.2. Bảng customer_group
CREATE TABLE `customer_group` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`name` varchar(255) NOT NULL,
`discount_rate` decimal(5,2) DEFAULT '0.00',
`description` text,
`created_at` datetime NOT NULL,
`updated_at` datetime NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4.3. Bảng customer_point
CREATE TABLE `customer_point` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`customer_id` int(11) NOT NULL,
`points` int(11) NOT NULL DEFAULT '0',
`total_points_earned` int(11) NOT NULL DEFAULT '0',
`total_points_used` int(11) NOT NULL DEFAULT '0',
`updated_at` datetime NOT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4.4. Bảng customer_point_history
CREATE TABLE `customer_point_history` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`customer_id` int(11) NOT NULL,
`reference_id` int(11) DEFAULT NULL,
`reference_type` varchar(50) DEFAULT NULL,
`points` int(11) NOT NULL,
`balance` int(11) NOT NULL,
`type` tinyint(1) NOT NULL COMMENT '1: add, 2: deduct',
`note` text,
`created_at` datetime NOT NULL,
`created_by` int(11) DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `idx-customer_point_history-customer_id` (`customer_id`),
KEY `idx-customer_point_history-reference_id-reference_type` (`reference_id`,`reference_type`),
KEY `idx-customer_point_history-type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4.5. Bảng customer_debt
CREATE TABLE `customer_debt` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`customer_id` int(11) NOT NULL,
`reference_id` int(11) DEFAULT NULL,
`reference_type` varchar(50) DEFAULT NULL,
`amount` decimal(15,2) NOT NULL,
`balance` decimal(15,2) NOT NULL,
`type` tinyint(1) NOT NULL COMMENT '1: debt, 2: payment',
`description` text,
`transaction_date` datetime NOT NULL,
`created_at` datetime NOT NULL,
`created_by` int(11) DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `idx-customer_debt-customer_id` (`customer_id`),
KEY `idx-customer_debt-reference_id-reference_type` (`reference_id`,`reference_type`),
KEY `idx-customer_debt-type` (`type`),
KEY `idx-customer_debt-transaction_date` (`transaction_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4.6. Bảng supplier
CREATE TABLE `supplier` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`code` varchar(50) NOT NULL,
`name` varchar(255) NOT NULL,
`phone` varchar(20) DEFAULT NULL,
`email` varchar(255) DEFAULT NULL,
`address` varchar(500) DEFAULT NULL,
`tax_code` varchar(50) DEFAULT NULL,
`contact_person` varchar(255) DEFAULT NULL,
`contact_phone` varchar(20) DEFAULT NULL,
`website` varchar(255) DEFAULT NULL,
`bank_name` varchar(255) DEFAULT NULL,
`bank_account` varchar(50) DEFAULT NULL,
`bank_account_name` varchar(255) DEFAULT NULL,
`debt_amount` decimal(15,2) DEFAULT '0.00',
`payment_term` int(11) DEFAULT NULL,
`credit_limit` decimal(15,2) DEFAULT '0.00',
`status` tinyint(1) NOT NULL DEFAULT '1',
`province_id` int(11) DEFAULT NULL,
`district_id` int(11) DEFAULT NULL,
`ward_id` int(11) DEFAULT NULL,
`created_at` datetime NOT NULL,
`updated_at` datetime NOT NULL,
`created_by` int(11) DEFAULT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `code` (`code`),
KEY `idx-supplier-phone` (`phone`),
KEY `idx-supplier-status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4.7. Bảng supplier_product
CREATE TABLE `supplier_product` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`supplier_id` int(11) NOT NULL,
`product_id` int(11) NOT NULL,
`supplier_product_code` varchar(50) DEFAULT NULL,
`supplier_product_name` varchar(255) DEFAULT NULL,
`unit_price` decimal(15,2) NOT NULL DEFAULT '0.00',
`min_order_quantity` int(11) DEFAULT NULL,
`lead_time` int(11) DEFAULT NULL,
`is_primary_supplier` tinyint(1) NOT NULL DEFAULT '0',
`last_purchase_date` datetime DEFAULT NULL,
`last_purchase_price` decimal(15,2) DEFAULT NULL,
`created_at` datetime NOT NULL,
`updated_at` datetime NOT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `supplier_product` (`supplier_id`,`product_id`),
KEY `idx-supplier_product-product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4.8. Bảng supplier_debt
CREATE TABLE `supplier_debt` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`supplier_id` int(11) NOT NULL,
`reference_id` int(11) DEFAULT NULL,
`reference_type` varchar(50) DEFAULT NULL,
`amount` decimal(15,2) NOT NULL,
`balance` decimal(15,2) NOT NULL,
`type` tinyint(1) NOT NULL COMMENT '1: debt, 2: payment',
`description` text,
`transaction_date` datetime NOT NULL,
`created_at` datetime NOT NULL,
`created_by` int(11) DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `idx-supplier_debt-supplier_id` (`supplier_id`),
KEY `idx-supplier_debt-reference_id-reference_type` (`reference_id`,`reference_type`),
KEY `idx-supplier_debt-type` (`type`),
KEY `idx-supplier_debt-transaction_date` (`transaction_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. QUẢN LÝ BÁN HÀNG VÀ THANH TOÁN

-- 5.1. Bảng order
CREATE TABLE `order` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`code` varchar(50) NOT NULL,
`customer_id` int(11) DEFAULT NULL,
`user_id` int(11) NOT NULL,
`shift_id` int(11) DEFAULT NULL,
`warehouse_id` int(11) NOT NULL,
`order_date` datetime NOT NULL,
`total_quantity` int(11) NOT NULL DEFAULT '0',
`subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
`discount_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
`tax_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
`total_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
`paid_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
`change_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
`points_earned` int(11) DEFAULT '0',
`points_used` int(11) DEFAULT '0',
`points_amount` decimal(15,2) DEFAULT '0.00',
`payment_method_id` int(11) DEFAULT NULL,
`payment_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0: unpaid, 1: partially, 2: paid',
`shipping_address` varchar(500) DEFAULT NULL,
`shipping_fee` decimal(15,2) DEFAULT '0.00',
`shipping_status` tinyint(1) DEFAULT '0' COMMENT '0: not shipped, 1: shipping, 2: delivered',
`delivery_date` datetime DEFAULT NULL,
`status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0: draft, 1: confirmed, 2: paid, 3: shipped, 4: completed, 5: canceled',
`note` text,
`created_at` datetime NOT NULL,
`updated_at` datetime NOT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `code` (`code`),
KEY `idx-order-customer_id` (`customer_id`),
KEY `idx-order-user_id` (`user_id`),
KEY `idx-order-shift_id` (`shift_id`),
KEY `idx-order-warehouse_id` (`warehouse_id`),
KEY `idx-order-order_date` (`order_date`),
KEY `idx-order-payment_method_id` (`payment_method_id`),
KEY `idx-order-payment_status` (`payment_status`),
KEY `idx-order-status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5.2. Bảng order_detail
CREATE TABLE `order_detail` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`order_id` int(11) NOT NULL,
`product_id` int(11) NOT NULL,
`quantity` int(11) NOT NULL,
`unit_id` int(11) NOT NULL,
`unit_price` decimal(15,2) NOT NULL DEFAULT '0.00',
`discount_percent` decimal(5,2) DEFAULT '0.00',
`discount_amount` decimal(15,2) DEFAULT '0.00',
`tax_percent` decimal(5,2) DEFAULT '0.00',
`tax_amount` decimal(15,2) DEFAULT '0.00',
`total_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
`batch_number` varchar(100) DEFAULT NULL,
`note` text,
`cost_price` decimal(15,2) DEFAULT '0.00',
PRIMARY KEY (`id`),
KEY `idx-order_detail-order_id` (`order_id`),
KEY `idx-order_detail-product_id` (`product_id`),
KEY `idx-order_detail-unit_id` (`unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5.3. Bảng order_payment
CREATE TABLE `order_payment` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`order_id` int(11) NOT NULL,
`payment_method_id` int(11) NOT NULL,
`amount` decimal(15,2) NOT NULL DEFAULT '0.00',
`payment_date` datetime NOT NULL,
`reference_number` varchar(100) DEFAULT NULL,
`status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0: pending, 1: success, 2: failed',
`note` text,
`created_at` datetime NOT NULL,
`created_by` int(11) DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `idx-order_payment-order_id` (`order_id`),
KEY `idx-order_payment-payment_method_id` (`payment_method_id`),
KEY `idx-order_payment-payment_date` (`payment_date`),
KEY `idx-order_payment-status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5.4. Bảng payment_method
CREATE TABLE `payment_method` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`name` varchar(255) NOT NULL,
`code` varchar(50) NOT NULL,
`description` text,
`is_default` tinyint(1) NOT NULL DEFAULT '0',
`is_active` tinyint(1) NOT NULL DEFAULT '1',
`require_reference` tinyint(1) NOT NULL DEFAULT '0',
`icon` varchar(255) DEFAULT NULL,
`sort_order` int(11) DEFAULT '0',
`created_at` datetime NOT NULL,
`updated_at` datetime NOT NULL,
PRIMARY KEY (`id`),
KEY `idx-payment_method-is_active` (`is_active`),
KEY `idx-payment_method-sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5.5. Bảng return
CREATE TABLE `return` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`code` varchar(50) NOT NULL,
`order_id` int(11) DEFAULT NULL,
`customer_id` int(11) DEFAULT NULL,
`user_id` int(11) NOT NULL,
`warehouse_id` int(11) NOT NULL,
`return_date` datetime NOT NULL,
`total_quantity` int(11) NOT NULL DEFAULT '0',
`subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
`tax_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
`total_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
`refund_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
`points_adjusted` int(11) DEFAULT '0',
`payment_method_id` int(11) DEFAULT NULL,
`status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0: draft, 1: confirmed, 2: refunded, 3: completed, 4: canceled',
`reason` text,
`note` text,
`created_at` datetime NOT NULL,
`updated_at` datetime NOT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `code` (`code`),
KEY `idx-return-order_id` (`order_id`),
KEY `idx-return-customer_id` (`customer_id`),
KEY `idx-return-user_id` (`user_id`),
KEY `idx-return-warehouse_id` (`warehouse_id`),
KEY `idx-return-return_date` (`return_date`),
KEY `idx-return-payment_method_id` (`payment_method_id`),
KEY `idx-return-status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5.6. Bảng return_detail
CREATE TABLE `return_detail` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`return_id` int(11) NOT NULL,
`order_detail_id` int(11) DEFAULT NULL,
`product_id` int(11) NOT NULL,
`quantity` int(11) NOT NULL,
`unit_id` int(11) NOT NULL,
`unit_price` decimal(15,2) NOT NULL DEFAULT '0.00',
`tax_amount` decimal(15,2) DEFAULT '0.00',
`total_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
`batch_number` varchar(100) DEFAULT NULL,
`reason` text,
`condition` tinyint(1) DEFAULT '1' COMMENT '1: good, 2: defective, 3: damaged',
`restocking` tinyint(1) NOT NULL DEFAULT '1',
PRIMARY KEY (`id`),
KEY `idx-return_detail-return_id` (`return_id`),
KEY `idx-return_detail-order_detail_id` (`order_detail_id`),
KEY `idx-return_detail-product_id` (`product_id`),
KEY `idx-return_detail-unit_id` (`unit_id`),
KEY `idx-return_detail-condition` (`condition`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5.7. Bảng discount
CREATE TABLE `discount` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`name` varchar(255) NOT NULL,
`code` varchar(50) DEFAULT NULL,
`discount_type` tinyint(1) NOT NULL COMMENT '1: percentage, 2: amount, 3: order discount',
`value` decimal(15,2) NOT NULL DEFAULT '0.00',
`min_order_amount` decimal(15,2) DEFAULT NULL,
`max_discount_amount` decimal(15,2) DEFAULT NULL,
`start_date` datetime DEFAULT NULL,
`end_date` datetime DEFAULT NULL,
`usage_limit` int(11) DEFAULT NULL,
`usage_count` int(11) NOT NULL DEFAULT '0',
`is_active` tinyint(1) NOT NULL DEFAULT '1',
`created_at` datetime NOT NULL,
`updated_at` datetime NOT NULL,
`created_by` int(11) DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `idx-discount-code` (`code`),
KEY `idx-discount-is_active` (`is_active`),
KEY `idx-discount-start_date` (`start_date`),
KEY `idx-discount-end_date` (`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5.8. Bảng product_discount
CREATE TABLE `product_discount` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`discount_id` int(11) NOT NULL,
`product_id` int(11) DEFAULT NULL,
`product_category_id` int(11) DEFAULT NULL,
`created_at` datetime NOT NULL,
PRIMARY KEY (`id`),
KEY `idx-product_discount-discount_id` (`discount_id`),
KEY `idx-product_discount-product_id` (`product_id`),
KEY `idx-product_discount-product_category_id` (`product_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. QUẢN LÝ BẢO HÀNH

-- 6.1. Bảng warranty
CREATE TABLE `warranty` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`code` varchar(50) NOT NULL,
`order_id` int(11) DEFAULT NULL,
`order_detail_id` int(11) DEFAULT NULL,
`product_id` int(11) NOT NULL,
`customer_id` int(11) DEFAULT NULL,
`serial_number` varchar(100) DEFAULT NULL,
`start_date` date NOT NULL,
`end_date` date NOT NULL,
`status_id` int(11) NOT NULL,
`active` tinyint(1) NOT NULL DEFAULT '1',
`note` text,
`created_at` datetime NOT NULL,
`updated_at` datetime NOT NULL,
`created_by` int(11) DEFAULT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `code` (`code`),
KEY `idx-warranty-order_id` (`order_id`),
KEY `idx-warranty-order_detail_id` (`order_detail_id`),
KEY `idx-warranty-product_id` (`product_id`),
KEY `idx-warranty-customer_id` (`customer_id`),
KEY `idx-warranty-serial_number` (`serial_number`),
KEY `idx-warranty-status_id` (`status_id`),
KEY `idx-warranty-start_date` (`start_date`),
KEY `idx-warranty-end_date` (`end_date`),
KEY `idx-warranty-active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6.2. Bảng warranty_detail
CREATE TABLE `warranty_detail` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`warranty_id` int(11) NOT NULL,
`service_date` datetime NOT NULL,
`status_id` int(11) NOT NULL,
`description` text,
`diagnosis` text,
`solution` text,
`replacement_product_id` int(11) DEFAULT NULL,
`replacement_cost` decimal(15,2) DEFAULT '0.00',
`service_cost` decimal(15,2) DEFAULT '0.00',
`total_cost` decimal(15,2) DEFAULT '0.00',
`is_charged` tinyint(1) NOT NULL DEFAULT '0',
`handled_by` int(11) DEFAULT NULL,
`created_at` datetime NOT NULL,
PRIMARY KEY (`id`),
KEY `idx-warranty_detail-warranty_id` (`warranty_id`),
KEY `idx-warranty_detail-status_id` (`status_id`),
KEY `idx-warranty_detail-replacement_product_id` (`replacement_product_id`),
KEY `idx-warranty_detail-handled_by` (`handled_by`),
KEY `idx-warranty_detail-service_date` (`service_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6.3. Bảng warranty_status
CREATE TABLE `warranty_status` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`name` varchar(255) NOT NULL,
`description` text,
`color` varchar(20) DEFAULT NULL,
`sort_order` int(11) DEFAULT '0',
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. QUẢN LÝ CA LÀM VIỆC VÀ TÀI CHÍNH

-- 7.1. Bảng shift
CREATE TABLE `shift` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`user_id` int(11) NOT NULL,
`warehouse_id` int(11) NOT NULL,
`cashier_id` int(11) DEFAULT NULL,
`start_time` datetime NOT NULL,
`end_time` datetime DEFAULT NULL,
`opening_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
`total_sales` decimal(15,2) NOT NULL DEFAULT '0.00',
`total_returns` decimal(15,2) NOT NULL DEFAULT '0.00',
`total_receipts` decimal(15,2) NOT NULL DEFAULT '0.00',
`total_payments` decimal(15,2) NOT NULL DEFAULT '0.00',
`expected_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
`actual_amount` decimal(15,2) DEFAULT NULL,
`difference` decimal(15,2) DEFAULT '0.00',
`explanation` text,
`status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0: open, 1: closed',
`note` text,
`created_at` datetime NOT NULL,
`updated_at` datetime NOT NULL,
PRIMARY KEY (`id`),
KEY `idx-shift-user_id` (`user_id`),
KEY `idx-shift-warehouse_id` (`warehouse_id`),
KEY `idx-shift-cashier_id` (`cashier_id`),
KEY `idx-shift-start_time` (`start_time`),
KEY `idx-shift-end_time` (`end_time`),
KEY `idx-shift-status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7.2. Bảng shift_detail
CREATE TABLE `shift_detail` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`shift_id` int(11) NOT NULL,
`payment_method_id` int(11) NOT NULL,
`transaction_type` tinyint(1) NOT NULL COMMENT '1: sales, 2: returns, 3: receipts, 4: payments',
`total_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
`transaction_count` int(11) NOT NULL DEFAULT '0',
`note` text,
PRIMARY KEY (`id`),
KEY `idx-shift_detail-shift_id` (`shift_id`),
KEY `idx-shift_detail-payment_method_id` (`payment_method_id`),
KEY `idx-shift_detail-transaction_type` (`transaction_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7.3. Bảng receipt
CREATE TABLE `receipt` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`code` varchar(50) NOT NULL,
`receipt_type` tinyint(1) NOT NULL COMMENT '1: sales, 2: debt payment, 3: supplier refund, 4: other',
`reference_id` int(11) DEFAULT NULL,
`reference_type` varchar(50) DEFAULT NULL,
`customer_id` int(11) DEFAULT NULL,
`supplier_id` int(11) DEFAULT NULL,
`amount` decimal(15,2) NOT NULL DEFAULT '0.00',
`payment_method_id` int(11) NOT NULL,
`receipt_date` datetime NOT NULL,
`received_from` varchar(255) DEFAULT NULL,
`account_number` varchar(50) DEFAULT NULL,
`bank_name` varchar(255) DEFAULT NULL,
`transaction_code` varchar(100) DEFAULT NULL,
`description` text,
`status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0: draft, 1: confirmed, 2: canceled',
`created_at` datetime NOT NULL,
`updated_at` datetime NOT NULL,
`created_by` int(11) DEFAULT NULL,
`approved_by` int(11) DEFAULT NULL,
`approved_at` datetime DEFAULT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `code` (`code`),
KEY `idx-receipt-reference_id-reference_type` (`reference_id`,`reference_type`),
KEY `idx-receipt-customer_id` (`customer_id`),
KEY `idx-receipt-supplier_id` (`supplier_id`),
KEY `idx-receipt-payment_method_id` (`payment_method_id`),
KEY `idx-receipt-receipt_date` (`receipt_date`),
KEY `idx-receipt-status` (`status`),
KEY `idx-receipt-receipt_type` (`receipt_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7.4. Bảng payment
CREATE TABLE `payment` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`code` varchar(50) NOT NULL,
`payment_type` tinyint(1) NOT NULL COMMENT '1: purchase, 2: supplier debt, 3: customer refund, 4: other',
`reference_id` int(11) DEFAULT NULL,
`reference_type` varchar(50) DEFAULT NULL,
`supplier_id` int(11) DEFAULT NULL,
`customer_id` int(11) DEFAULT NULL,
`amount` decimal(15,2) NOT NULL DEFAULT '0.00',
`payment_method_id` int(11) NOT NULL,
`payment_date` datetime NOT NULL,
`paid_to` varchar(255) DEFAULT NULL,
`account_number` varchar(50) DEFAULT NULL,
`bank_name` varchar(255) DEFAULT NULL,
`transaction_code` varchar(100) DEFAULT NULL,
`description` text,
`status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0: draft, 1: confirmed, 2: canceled',
`created_at` datetime NOT NULL,
`updated_at` datetime NOT NULL,
`created_by` int(11) DEFAULT NULL,
`approved_by` int(11) DEFAULT NULL,
`approved_at` datetime DEFAULT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `code` (`code`),
KEY `idx-payment-reference_id-reference_type` (`reference_id`,`reference_type`),
KEY `idx-payment-supplier_id` (`supplier_id`),
KEY `idx-payment-customer_id` (`customer_id`),
KEY `idx-payment-payment_method_id` (`payment_method_id`),
KEY `idx-payment-payment_date` (`payment_date`),
KEY `idx-payment-status` (`status`),
KEY `idx-payment-payment_type` (`payment_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7.5. Bảng cash_book
CREATE TABLE `cash_book` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`transaction_date` datetime NOT NULL,
`reference_id` int(11) DEFAULT NULL,
`reference_type` varchar(50) DEFAULT NULL,
`payment_method_id` int(11) NOT NULL,
`shift_id` int(11) DEFAULT NULL,
`warehouse_id` int(11) DEFAULT NULL,
`amount` decimal(15,2) NOT NULL,
`type` tinyint(1) NOT NULL COMMENT '1: in, 2: out',
`balance` decimal(15,2) NOT NULL,
`description` text,
`created_at` datetime NOT NULL,
`created_by` int(11) DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `idx-cash_book-reference_id-reference_type` (`reference_id`,`reference_type`),
KEY `idx-cash_book-payment_method_id` (`payment_method_id`),
KEY `idx-cash_book-shift_id` (`shift_id`),
KEY `idx-cash_book-warehouse_id` (`warehouse_id`),
KEY `idx-cash_book-type` (`type`),
KEY `idx-cash_book-transaction_date` (`transaction_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. CÀI ĐẶT HỆ THỐNG

-- 8.1. Bảng setting
CREATE TABLE `setting` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`category` varchar(64) NOT NULL,
`key` varchar(255) NOT NULL,
`value` text,
`description` text,
`is_public` tinyint(1) NOT NULL DEFAULT '1',
`updated_at` datetime NOT NULL,
`updated_by` int(11) DEFAULT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `category_key` (`category`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8.2. Bảng province
CREATE TABLE `province` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`name` varchar(255) NOT NULL,
`code` varchar(20) DEFAULT NULL,
`region` varchar(100) DEFAULT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8.3. Bảng district
CREATE TABLE `district` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`province_id` int(11) NOT NULL,
`name` varchar(255) NOT NULL,
`code` varchar(20) DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `idx-district-province_id` (`province_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8.4. Bảng ward
CREATE TABLE `ward` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`district_id` int(11) NOT NULL,
`name` varchar(255) NOT NULL,
`code` varchar(20) DEFAULT NULL,
PRIMARY KEY (`id`),
KEY `idx-ward-district_id` (`district_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8.5. Bảng log
CREATE TABLE `log` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`user_id` int(11) DEFAULT NULL,
`action` varchar(100) NOT NULL,
`model` varchar(100) DEFAULT NULL,
`model_id` int(11) DEFAULT NULL,
`old_data` text,
`new_data` text,
`ip_address` varchar(50) DEFAULT NULL,
`user_agent` varchar(255) DEFAULT NULL,
`created_at` datetime NOT NULL,
PRIMARY KEY (`id`),
KEY `idx-log-user_id` (`user_id`),
KEY `idx-log-action` (`action`),
KEY `idx-log-model-model_id` (`model`,`model_id`),
KEY `idx-log-created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. KHÓA NGOẠI VÀ RÀNG BUỘC

-- Ràng buộc cho bảng user
ALTER TABLE `user`
ADD CONSTRAINT `fk-user-warehouse_id` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouse` (`id`) ON DELETE SET NULL;

-- Ràng buộc cho bảng auth_item
ALTER TABLE `auth_item`
ADD CONSTRAINT `fk-auth_item-rule_name` FOREIGN KEY (`rule_name`) REFERENCES `auth_rule` (`name`) ON DELETE SET NULL;

-- Ràng buộc cho bảng auth_item_child
ALTER TABLE `auth_item_child`
ADD CONSTRAINT `fk-auth_item_child-parent` FOREIGN KEY (`parent`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-auth_item_child-child` FOREIGN KEY (`child`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE;

-- Ràng buộc cho bảng user_profile
ALTER TABLE `user_profile`
ADD CONSTRAINT `fk-user_profile-user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

-- Ràng buộc cho bảng user_warehouse
ALTER TABLE `user_warehouse`
ADD CONSTRAINT `fk-user_warehouse-user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-user_warehouse-warehouse_id` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouse` (`id`) ON DELETE CASCADE;

-- Ràng buộc cho bảng login_history
ALTER TABLE `login_history`
ADD CONSTRAINT `fk-login_history-user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

-- Ràng buộc cho bảng product_category
ALTER TABLE `product_category`
ADD CONSTRAINT `fk-product_category-parent_id` FOREIGN KEY (`parent_id`) REFERENCES `product_category` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-product_category-created_by` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-product_category-updated_by` FOREIGN KEY (`updated_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;

-- Ràng buộc cho bảng product
ALTER TABLE `product`
ADD CONSTRAINT `fk-product-category_id` FOREIGN KEY (`category_id`) REFERENCES `product_category` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-product-unit_id` FOREIGN KEY (`unit_id`) REFERENCES `product_unit` (`id`),
ADD CONSTRAINT `fk-product-created_by` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-product-updated_by` FOREIGN KEY (`updated_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;

-- Ràng buộc cho bảng product_image
ALTER TABLE `product_image`
ADD CONSTRAINT `fk-product_image-product_id` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE;

-- Ràng buộc cho bảng product_attribute_value
ALTER TABLE `product_attribute_value`
ADD CONSTRAINT `fk-product_attribute_value-product_id` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-product_attribute_value-attribute_id` FOREIGN KEY (`attribute_id`) REFERENCES `product_attribute` (`id`) ON DELETE CASCADE;

-- Ràng buộc cho bảng product_unit_conversion
ALTER TABLE `product_unit_conversion`
ADD CONSTRAINT `fk-product_unit_conversion-product_id` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-product_unit_conversion-from_unit_id` FOREIGN KEY (`from_unit_id`) REFERENCES `product_unit` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-product_unit_conversion-to_unit_id` FOREIGN KEY (`to_unit_id`) REFERENCES `product_unit` (`id`) ON DELETE CASCADE;

-- Ràng buộc cho bảng product_combo
ALTER TABLE `product_combo`
ADD CONSTRAINT `fk-product_combo-combo_id` FOREIGN KEY (`combo_id`) REFERENCES `product` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-product_combo-product_id` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-product_combo-unit_id` FOREIGN KEY (`unit_id`) REFERENCES `product_unit` (`id`) ON DELETE CASCADE;

-- Ràng buộc cho bảng product_price_history
ALTER TABLE `product_price_history`
ADD CONSTRAINT `fk-product_price_history-product_id` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-product_price_history-created_by` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;

-- Ràng buộc cho bảng warehouse
ALTER TABLE `warehouse`
ADD CONSTRAINT `fk-warehouse-manager_id` FOREIGN KEY (`manager_id`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-warehouse-created_by` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-warehouse-updated_by` FOREIGN KEY (`updated_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;

-- Ràng buộc cho bảng stock
ALTER TABLE `stock`
ADD CONSTRAINT `fk-stock-product_id` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-stock-warehouse_id` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouse` (`id`) ON DELETE CASCADE;

-- Ràng buộc cho bảng stock_movement
ALTER TABLE `stock_movement`
ADD CONSTRAINT `fk-stock_movement-product_id` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-stock_movement-source_warehouse_id` FOREIGN KEY (`source_warehouse_id`) REFERENCES `warehouse` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-stock_movement-destination_warehouse_id` FOREIGN KEY (`destination_warehouse_id`) REFERENCES `warehouse` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-stock_movement-unit_id` FOREIGN KEY (`unit_id`) REFERENCES `product_unit` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-stock_movement-created_by` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;

-- Ràng buộc cho bảng stock_in
ALTER TABLE `stock_in`
ADD CONSTRAINT `fk-stock_in-warehouse_id` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouse` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-stock_in-supplier_id` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-stock_in-created_by` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-stock_in-approved_by` FOREIGN KEY (`approved_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;

-- Ràng buộc cho bảng stock_in_detail
ALTER TABLE `stock_in_detail`
ADD CONSTRAINT `fk-stock_in_detail-stock_in_id` FOREIGN KEY (`stock_in_id`) REFERENCES `stock_in` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-stock_in_detail-product_id` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-stock_in_detail-unit_id` FOREIGN KEY (`unit_id`) REFERENCES `product_unit` (`id`) ON DELETE CASCADE;

-- Ràng buộc cho bảng stock_out
ALTER TABLE `stock_out`
ADD CONSTRAINT `fk-stock_out-warehouse_id` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouse` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-stock_out-created_by` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-stock_out-approved_by` FOREIGN KEY (`approved_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;

-- Ràng buộc cho bảng stock_out_detail
ALTER TABLE `stock_out_detail`
ADD CONSTRAINT `fk-stock_out_detail-stock_out_id` FOREIGN KEY (`stock_out_id`) REFERENCES `stock_out` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-stock_out_detail-product_id` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-stock_out_detail-unit_id` FOREIGN KEY (`unit_id`) REFERENCES `product_unit` (`id`) ON DELETE CASCADE;

-- Ràng buộc cho bảng stock_transfer
ALTER TABLE `stock_transfer`
ADD CONSTRAINT `fk-stock_transfer-source_warehouse_id` FOREIGN KEY (`source_warehouse_id`) REFERENCES `warehouse` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-stock_transfer-destination_warehouse_id` FOREIGN KEY (`destination_warehouse_id`) REFERENCES `warehouse` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-stock_transfer-created_by` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-stock_transfer-approved_by` FOREIGN KEY (`approved_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-stock_transfer-received_by` FOREIGN KEY (`received_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;

-- Ràng buộc cho bảng stock_transfer_detail
ALTER TABLE `stock_transfer_detail`
ADD CONSTRAINT `fk-stock_transfer_detail-stock_transfer_id` FOREIGN KEY (`stock_transfer_id`) REFERENCES `stock_transfer` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-stock_transfer_detail-product_id` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-stock_transfer_detail-unit_id` FOREIGN KEY (`unit_id`) REFERENCES `product_unit` (`id`) ON DELETE CASCADE;

-- Ràng buộc cho bảng stock_check
ALTER TABLE `stock_check`
ADD CONSTRAINT `fk-stock_check-warehouse_id` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouse` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-stock_check-created_by` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-stock_check-approved_by` FOREIGN KEY (`approved_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;

-- Ràng buộc cho bảng stock_check_detail
ALTER TABLE `stock_check_detail`
ADD CONSTRAINT `fk-stock_check_detail-stock_check_id` FOREIGN KEY (`stock_check_id`) REFERENCES `stock_check` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-stock_check_detail-product_id` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-stock_check_detail-unit_id` FOREIGN KEY (`unit_id`) REFERENCES `product_unit` (`id`) ON DELETE CASCADE;

-- Ràng buộc cho bảng product_batch
ALTER TABLE `product_batch`
ADD CONSTRAINT `fk-product_batch-product_id` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-product_batch-warehouse_id` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouse` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-product_batch-stock_in_id` FOREIGN KEY (`stock_in_id`) REFERENCES `stock_in` (`id`) ON DELETE SET NULL;

-- Ràng buộc cho bảng customer
ALTER TABLE `customer`
ADD CONSTRAINT `fk-customer-customer_group_id` FOREIGN KEY (`customer_group_id`) REFERENCES `customer_group` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-customer-province_id` FOREIGN KEY (`province_id`) REFERENCES `province` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-customer-district_id` FOREIGN KEY (`district_id`) REFERENCES `district` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-customer-ward_id` FOREIGN KEY (`ward_id`) REFERENCES `ward` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-customer-created_by` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;

-- Ràng buộc cho bảng customer_point
ALTER TABLE `customer_point`
ADD CONSTRAINT `fk-customer_point-customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE CASCADE;

-- Ràng buộc cho bảng customer_point_history
ALTER TABLE `customer_point_history`
ADD CONSTRAINT `fk-customer_point_history-customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-customer_point_history-created_by` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;

-- Ràng buộc cho bảng customer_debt
ALTER TABLE `customer_debt`
ADD CONSTRAINT `fk-customer_debt-customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-customer_debt-created_by` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;

-- Ràng buộc cho bảng supplier
ALTER TABLE `supplier`
ADD CONSTRAINT `fk-supplier-province_id` FOREIGN KEY (`province_id`) REFERENCES `province` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-supplier-district_id` FOREIGN KEY (`district_id`) REFERENCES `district` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-supplier-ward_id` FOREIGN KEY (`ward_id`) REFERENCES `ward` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-supplier-created_by` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;

-- Ràng buộc cho bảng supplier_product
ALTER TABLE `supplier_product`
ADD CONSTRAINT `fk-supplier_product-supplier_id` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-supplier_product-product_id` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE;

-- Ràng buộc cho bảng supplier_debt
ALTER TABLE `supplier_debt`
ADD CONSTRAINT `fk-supplier_debt-supplier_id` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-supplier_debt-created_by` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;

-- Ràng buộc cho bảng order
ALTER TABLE `order`
ADD CONSTRAINT `fk-order-customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-order-user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-order-shift_id` FOREIGN KEY (`shift_id`) REFERENCES `shift` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-order-warehouse_id` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouse` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-order-payment_method_id` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_method` (`id`) ON DELETE SET NULL;

-- Ràng buộc cho bảng order_detail
ALTER TABLE `order_detail`
ADD CONSTRAINT `fk-order_detail-order_id` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-order_detail-product_id` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-order_detail-unit_id` FOREIGN KEY (`unit_id`) REFERENCES `product_unit` (`id`) ON DELETE CASCADE;

-- Ràng buộc cho bảng order_payment
ALTER TABLE `order_payment`
ADD CONSTRAINT `fk-order_payment-order_id` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-order_payment-payment_method_id` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_method` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-order_payment-created_by` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;

-- Ràng buộc cho bảng return
ALTER TABLE `return`
ADD CONSTRAINT `fk-return-order_id` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-return-customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-return-user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-return-warehouse_id` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouse` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-return-payment_method_id` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_method` (`id`) ON DELETE SET NULL;

-- Ràng buộc cho bảng return_detail
ALTER TABLE `return_detail`
ADD CONSTRAINT `fk-return_detail-return_id` FOREIGN KEY (`return_id`) REFERENCES `return` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-return_detail-order_detail_id` FOREIGN KEY (`order_detail_id`) REFERENCES `order_detail` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-return_detail-product_id` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-return_detail-unit_id` FOREIGN KEY (`unit_id`) REFERENCES `product_unit` (`id`) ON DELETE CASCADE;

-- Ràng buộc cho bảng discount
ALTER TABLE `discount`
ADD CONSTRAINT `fk-discount-created_by` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;

-- Ràng buộc cho bảng product_discount
ALTER TABLE `product_discount`
ADD CONSTRAINT `fk-product_discount-discount_id` FOREIGN KEY (`discount_id`) REFERENCES `discount` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-product_discount-product_id` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-product_discount-product_category_id` FOREIGN KEY (`product_category_id`) REFERENCES `product_category` (`id`) ON DELETE CASCADE;

-- Ràng buộc cho bảng warranty
ALTER TABLE `warranty`
ADD CONSTRAINT `fk-warranty-order_id` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-warranty-order_detail_id` FOREIGN KEY (`order_detail_id`) REFERENCES `order_detail` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-warranty-product_id` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-warranty-customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-warranty-status_id` FOREIGN KEY (`status_id`) REFERENCES `warranty_status` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-warranty-created_by` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;

-- Ràng buộc cho bảng warranty_detail
ALTER TABLE `warranty_detail`
ADD CONSTRAINT `fk-warranty_detail-warranty_id` FOREIGN KEY (`warranty_id`) REFERENCES `warranty` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-warranty_detail-status_id` FOREIGN KEY (`status_id`) REFERENCES `warranty_status` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-warranty_detail-replacement_product_id` FOREIGN KEY (`replacement_product_id`) REFERENCES `product` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-warranty_detail-handled_by` FOREIGN KEY (`handled_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;

-- Ràng buộc cho bảng shift
ALTER TABLE `shift`
ADD CONSTRAINT `fk-shift-user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-shift-warehouse_id` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouse` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-shift-cashier_id` FOREIGN KEY (`cashier_id`) REFERENCES `user` (`id`) ON DELETE SET NULL;

-- Ràng buộc cho bảng shift_detail
ALTER TABLE `shift_detail`
ADD CONSTRAINT `fk-shift_detail-shift_id` FOREIGN KEY (`shift_id`) REFERENCES `shift` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-shift_detail-payment_method_id` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_method` (`id`) ON DELETE CASCADE;

-- Ràng buộc cho bảng receipt
ALTER TABLE `receipt`
ADD CONSTRAINT `fk-receipt-customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-receipt-supplier_id` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-receipt-payment_method_id` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_method` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-receipt-created_by` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-receipt-approved_by` FOREIGN KEY (`approved_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;

-- Ràng buộc cho bảng payment
ALTER TABLE `payment`
ADD CONSTRAINT `fk-payment-supplier_id` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-payment-customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-payment-payment_method_id` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_method` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-payment-created_by` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-payment-approved_by` FOREIGN KEY (`approved_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;

-- Ràng buộc cho bảng cash_book
ALTER TABLE `cash_book`
ADD CONSTRAINT `fk-cash_book-payment_method_id` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_method` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `fk-cash_book-shift_id` FOREIGN KEY (`shift_id`) REFERENCES `shift` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-cash_book-warehouse_id` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouse` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk-cash_book-created_by` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;

-- Ràng buộc cho bảng setting
ALTER TABLE `setting`
ADD CONSTRAINT `fk-setting-updated_by` FOREIGN KEY (`updated_by`) REFERENCES `user` (`id`) ON DELETE SET NULL;

-- Ràng buộc cho bảng district
ALTER TABLE `district`
ADD CONSTRAINT `fk-district-province_id` FOREIGN KEY (`province_id`) REFERENCES `province` (`id`) ON DELETE CASCADE;

-- Ràng buộc cho bảng ward
ALTER TABLE `ward`
ADD CONSTRAINT `fk-ward-district_id` FOREIGN KEY (`district_id`) REFERENCES `district` (`id`) ON DELETE CASCADE;

-- Ràng buộc cho bảng log
ALTER TABLE `log`
ADD CONSTRAINT `fk-log-user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL;

-- 10. DỮ LIỆU MẪU

-- Dữ liệu cho bảng user
INSERT INTO `user` (`username`, `auth_key`, `password_hash`, `email`, `status`, `created_at`, `updated_at`, `full_name`) VALUES
('admin', '...', '$2y$13$jDYx/jDJCLM0U6vYBJMvcu0jQ9jj3sDQKR.d9.DR2LgOGjOxQH0BC', 'admin@example.com', 10, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'Admin User'),
('manager', '...', '$2y$13$jDYx/jDJCLM0U6vYBJMvcu0jQ9jj3sDQKR.d9.DR2LgOGjOxQH0BC', 'manager@example.com', 10, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'Manager User'),
('cashier', '...', '$2y$13$jDYx/jDJCLM0U6vYBJMvcu0jQ9jj3sDQKR.d9.DR2LgOGjOxQH0BC', 'cashier@example.com', 10, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'Cashier User');

-- Dữ liệu cho bảng RBAC
INSERT INTO `auth_item` (`name`, `type`, `description`, `created_at`, `updated_at`) VALUES
('admin', 1, 'Administrator', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('manager', 1, 'Store Manager', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('cashier', 1, 'Cashier', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('viewDashboard', 2, 'View dashboard', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('manageProducts', 2, 'Manage products', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('manageOrders', 2, 'Manage orders', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('manageCustomers', 2, 'Manage customers', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('manageSettings', 2, 'Manage settings', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

INSERT INTO `auth_item_child` (`parent`, `child`) VALUES
('admin', 'manager'),
('manager', 'cashier'),
('cashier', 'viewDashboard'),
('manager', 'manageProducts'),
('manager', 'manageOrders'),
('manager', 'manageCustomers'),
('admin', 'manageSettings');

-- Dữ liệu cho warehouse và các bảng khác
INSERT INTO `warehouse` (`code`, `name`, `is_default`, `is_active`, `created_at`, `updated_at`) VALUES
('WH001', 'Kho chính', 1, 1, NOW(), NOW()),
('WH002', 'Kho phụ', 0, 1, NOW(), NOW());

INSERT INTO `product_unit` (`name`, `abbreviation`, `is_default`, `created_at`, `updated_at`) VALUES
('Cái', 'cái', 1, NOW(), NOW()),
('Hộp', 'hộp', 0, NOW(), NOW()),
('Kg', 'kg', 0, NOW(), NOW()),
('Bộ', 'bộ', 0, NOW(), NOW());

INSERT INTO `product_category` (`name`, `slug`, `status`, `created_at`, `updated_at`) VALUES
('Điện thoại', 'dien-thoai', 1, NOW(), NOW()),
('Máy tính', 'may-tinh', 1, NOW(), NOW()),
('Phụ kiện', 'phu-kien', 1, NOW(), NOW());

INSERT INTO `payment_method` (`name`, `code`, `is_default`, `is_active`, `created_at`, `updated_at`) VALUES
('Tiền mặt', 'CASH', 1, 1, NOW(), NOW()),
('Chuyển khoản', 'BANK', 0, 1, NOW(), NOW()),
('Thẻ tín dụng', 'CREDIT', 0, 1, NOW(), NOW());

INSERT INTO `warranty_status` (`name`, `color`, `sort_order`) VALUES
('Chờ xử lý', '#FFB900', 1),
('Đang xử lý', '#0078D7', 2),
('Hoàn thành', '#107C10', 3),
('Từ chối', '#E81123', 4);

-- Cập nhật mật khẩu của user 'admin' thành '123456'
UPDATE `user` SET `password_hash` = '$2y$13$Nuo.7hViwXGXc6ZzUl49p.ZHjHDx/6JFKnrF0A8qZXJHzLlxuL99u' WHERE `username` = 'admin';

