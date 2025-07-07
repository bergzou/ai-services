/*
 Navicat Premium Data Transfer

 Source Server         : 测试环境-改
 Source Server Type    : MySQL
 Source Server Version : 80018 (8.0.18)
 Source Host           : 120.31.71.194:23306
 Source Schema         : xh_return
I
 Target Server Type    : MySQL
 Target Server Version : 80018 (8.0.18)
 File Encoding         : 65001

 Date: 08/04/2025 16:18:51
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for returned_claim_detail
-- ----------------------------
DROP TABLE IF EXISTS `returned_claim_detail`;
CREATE TABLE `returned_claim_detail`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sku` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '系统SKU',
  `customer_sku` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '卖家SKU ',
  `receive_quantity` int(11) NOT NULL DEFAULT 0 COMMENT '退件数量/退件数量',
  `receive_defective_quantity` int(11) NULL DEFAULT NULL COMMENT '实收不良品数量',
  `claim_order_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '认领单号',
  `identification_mark` tinyint(3) NOT NULL DEFAULT 2 COMMENT '无法识别SKU标识 1是 2：否',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `new_sku` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '转换后SKU',
  `new_customer_sku` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '转换后SKU编码',
  `seller_sku` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '销售SKU',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_code`(`claim_order_code` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 832 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '认领详情' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for returned_claim_log
-- ----------------------------
DROP TABLE IF EXISTS `returned_claim_log`;
CREATE TABLE `returned_claim_log`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `claim_order_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '' COMMENT '认领单号',
  `content` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '' COMMENT '操作内容',
  `opeator_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '' COMMENT '操作人',
  `opeator_uid` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '' COMMENT '操作人名称',
  `operation_at` datetime NULL DEFAULT CURRENT_TIMESTAMP COMMENT '操作时间',
  `claim_log_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '' COMMENT '退件日志雪花ID',
  `log_type` tinyint(3) NULL DEFAULT NULL COMMENT '日志类型 1：前台  2：中台',
  `seller_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '' COMMENT '卖家代码',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_claim_log_id`(`claim_log_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 205 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '退件单日志' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for returned_claim_order
-- ----------------------------
DROP TABLE IF EXISTS `returned_claim_order`;
CREATE TABLE `returned_claim_order`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `claim_order_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '认领单号 ',
  `tracking_number` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '跟踪单号',
  `returned_desc` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '退件描述',
  `seller_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '卖家代码',
  `claim_status` tinyint(3) NOT NULL COMMENT '认领状态 ：10 待认领  2已认领 3已弃货',
  `claim_type` tinyint(3) NOT NULL COMMENT '认领类型 1我的退件  2：未知退件 ',
  `warehouse_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '仓库编码',
  `region_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '区域编码',
  `handling_method` tinyint(3) NOT NULL DEFAULT 0 COMMENT '处理方式 1：重新上架  2:销毁',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `receiving_at` datetime NULL DEFAULT NULL COMMENT '收货时间',
  `claim_at` datetime NULL DEFAULT NULL COMMENT '认领时间',
  `manage_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '项目编码',
  `manage_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '项目名称',
  `returned_order_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '关联退件单号',
  `tenant_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '' COMMENT '租户编码',
  `claim_order_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '雪花id',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_claim_order_code`(`claim_order_code` ASC) USING BTREE,
  UNIQUE INDEX `uk_claim_order_id`(`claim_order_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 319 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '认领单号' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for returned_detail
-- ----------------------------
DROP TABLE IF EXISTS `returned_detail`;
CREATE TABLE `returned_detail`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `returned_detail_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '雪花ID 退件详情ID',
  `sku` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '系统SKU',
  `new_sku` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT 'new系统SKU',
  `customer_sku` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'SKU编码',
  `actual_received_quantity` int(11) NULL DEFAULT NULL COMMENT '出库单数量',
  `returned_quantity` int(64) NULL DEFAULT NULL COMMENT '可退数量',
  `new_seller_sku` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '' COMMENT '新平台销售SKU',
  `receive_quantity` int(11) NULL DEFAULT NULL COMMENT '实收数量',
  `forecast_quantity` int(11) NULL DEFAULT NULL COMMENT '预报退件数量',
  `receive_defective_quantity` int(11) NULL DEFAULT NULL COMMENT '实收不良品数量',
  `putaway_quantity` int(11) NULL DEFAULT 0 COMMENT '上架数量',
  `destruction_quantity` int(11) NULL DEFAULT 0 COMMENT '销毁数量',
  `returned_order_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '退件单号',
  `seller_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '' COMMENT '卖家代码',
  `identification_mark` tinyint(64) NULL DEFAULT 2 COMMENT '无法识别SKU标识 1是 2：否',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `defective_putaway` int(11) NULL DEFAULT 0 COMMENT '不良品上架',
  `good_putaway` int(11) NULL DEFAULT 0 COMMENT '良品上架',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_return_detail_id`(`returned_detail_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2628 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '退件详情' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for returned_detail_attach
-- ----------------------------
DROP TABLE IF EXISTS `returned_detail_attach`;
CREATE TABLE `returned_detail_attach`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `returned_attach_detail_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '雪花附件id',
  `sku` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '0' COMMENT 'sku',
  `attach_url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '图片地址',
  `attach_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '图片名称',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `returned_order_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '退件单号',
  `claim_order_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '认领单号',
  `box_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '箱唛号',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_return_attach_detail_id`(`returned_attach_detail_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1862 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '退件单详情附件表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for returned_log
-- ----------------------------
DROP TABLE IF EXISTS `returned_log`;
CREATE TABLE `returned_log`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `returned_order_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '' COMMENT '退件单号',
  `content` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '' COMMENT '操作内容',
  `opeator_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '' COMMENT '操作人',
  `opeator_uid` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '' COMMENT '操作人名称',
  `operation_at` datetime NULL DEFAULT CURRENT_TIMESTAMP COMMENT '操作时间',
  `returned_log_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '' COMMENT '退件日志雪花ID',
  `log_type` tinyint(3) NULL DEFAULT NULL COMMENT '日志类型 1：前台  2：中台',
  `seller_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '' COMMENT '卖家代码',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_return_log_id`(`returned_log_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1586 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '退件单日志' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for returned_operate
-- ----------------------------
DROP TABLE IF EXISTS `returned_operate`;
CREATE TABLE `returned_operate`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `returned_order_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '' COMMENT '退件单号',
  `content` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '' COMMENT '操作内容',
  `operate_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '' COMMENT '操作人',
  `operate_at` datetime NULL DEFAULT CURRENT_TIMESTAMP COMMENT '操作时间',
  `content_num` int(10) NULL DEFAULT NULL COMMENT '序号',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1952 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '退件单操作流程' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for returned_order
-- ----------------------------
DROP TABLE IF EXISTS `returned_order`;
CREATE TABLE `returned_order`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `returned_order_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '雪花ID',
  `returned_order_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '退件单号',
  `tracking_number` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '跟踪号',
  `returned_reference_no` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '退件参考号',
  `manage_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '' COMMENT '项目名称',
  `manage_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '项目编码',
  `returned_sign` tinyint(4) NULL DEFAULT 3 COMMENT '退件标识 1:芯宏发货退件 2：非芯宏发货退件',
  `warehouse_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '退件仓库',
  `returned_type` tinyint(3) NOT NULL COMMENT '退件类型 1：买家退件 2：物流退件 3：退件认领',
  `handling_method` tinyint(3) NOT NULL DEFAULT 0 COMMENT '处理方式 1：重新上架  2:销毁',
  `returned_illustrate` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '' COMMENT '退件说明',
  `created_at` datetime NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `submit_at` datetime NULL DEFAULT NULL COMMENT '提交时间',
  `receiving_at` datetime NULL DEFAULT NULL COMMENT '收货时间',
  `completion_at` datetime NULL DEFAULT NULL COMMENT '完成时间',
  `updator_uid` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '更新人UID',
  `updator_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '更新人名称',
  `outbound_order_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '' COMMENT '出库单号',
  `seller_order_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '' COMMENT '卖家订单号',
  `expected_delivery_time` datetime NULL DEFAULT NULL COMMENT '预计到货时间',
  `region_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '' COMMENT '区域仓代码',
  `seller_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '' COMMENT '卖家代码',
  `tenant_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '' COMMENT '租户编码',
  `claim_order_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '' COMMENT '退件认领单号',
  `returned_status` tinyint(3) NOT NULL COMMENT '退件状态：10：草稿 20:待确认 30:待签收 40：处理中  50：已完成 60：已取消',
  `outbound_warehouse_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT '' COMMENT '发货仓库编码',
  `outbound_warehouse_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '发货仓库名称',
  `warehouse_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '仓库名称',
  `document_type` int(4) NOT NULL DEFAULT 1 COMMENT '单据类型: 1：代发退件单 2：整箱中转退件单',
  `create_type` int(4) NOT NULL DEFAULT 1 COMMENT '创建类型: 1：客户创建 2：仓库创建',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_return_order_id`(`returned_order_id` ASC) USING BTREE,
  UNIQUE INDEX `uk_return_order_code`(`returned_order_code` ASC) USING BTREE,
  INDEX `idx_outbound_code`(`outbound_order_code` ASC) USING BTREE,
  INDEX `idx_ref_no`(`returned_reference_no` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1088 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '退件单' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for returned_order_box
-- ----------------------------
DROP TABLE IF EXISTS `returned_order_box`;
CREATE TABLE `returned_order_box`  (
  `id` bigint(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `returned_box_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '雪花ID',
  `box_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '箱唛单号',
  `tracking_number` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '跟踪号',
  `outer_box_length` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '预报外箱长',
  `outer_box_width` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '预报外箱宽',
  `outer_box_height` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '预报外箱高',
  `outer_box_weight` decimal(10, 3) NOT NULL DEFAULT 0.000 COMMENT '预报外箱重量',
  `sku_types` int(11) NOT NULL DEFAULT 0 COMMENT 'SKU种类数',
  `sku_pieces` int(11) NOT NULL DEFAULT 0 COMMENT 'SKU数量',
  `actual_outer_box_length` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '实收外箱长',
  `actual_outer_box_width` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '实收外箱宽',
  `actual_outer_box_height` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '实收外箱高',
  `actual_outer_box_weight` decimal(10, 3) NOT NULL DEFAULT 0.000 COMMENT '实收外箱重量',
  `returned_order_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '退件单号',
  `putaway_at` datetime NULL DEFAULT NULL COMMENT '上架时间',
  `remarks` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_returned_box_id`(`returned_box_id` ASC) USING BTREE,
  INDEX `idx_returned_order_code`(`returned_order_code` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 355 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '退件单箱信息' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for returned_order_box_detail
-- ----------------------------
DROP TABLE IF EXISTS `returned_order_box_detail`;
CREATE TABLE `returned_order_box_detail`  (
  `id` bigint(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `returned_order_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '退件单号',
  `returned_order_box_detail_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '箱明细ID',
  `sku` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'SKU编码',
  `customer_sku` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '卖家SKU',
  `sku_weight` decimal(10, 3) UNSIGNED NOT NULL DEFAULT 0.000 COMMENT '重量',
  `sku_length` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '长',
  `sku_width` decimal(10, 2) NOT NULL DEFAULT 0.00 COMMENT '宽',
  `sku_height` decimal(10, 2) NOT NULL COMMENT '高',
  `shipment_quantity` int(11) NOT NULL DEFAULT 0 COMMENT '数量',
  `box_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '箱唛号',
  `returned_box_id` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'returned_order_box.returned_box_id主键ID',
  `seller_code` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '卖家代码',
  `packing_quantity` int(11) NOT NULL DEFAULT 0 COMMENT '装箱数量',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_returned_order_box_detail_id`(`returned_order_box_detail_id` ASC) USING BTREE,
  INDEX `idx_returned_box_id`(`returned_box_id` ASC) USING BTREE,
  INDEX `idx_return_box_code`(`returned_order_code` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 538 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = '退件单箱明细' ROW_FORMAT = DYNAMIC;

SET FOREIGN_KEY_CHECKS = 1;
