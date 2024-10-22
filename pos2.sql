-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 04, 2023 at 06:49 PM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pos2`
--

-- --------------------------------------------------------

--
-- Table structure for table `attributes`
--

CREATE TABLE `attributes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `branch_id`, `name`, `address`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(9, 'BRCH-TRK-NYD4S8NAVQ', 'leventist', NULL, NULL, '2023-12-04 09:53:34', '2023-12-04 09:53:34', NULL),
(10, 'BRCH-TRK-CBBAI8L2VN', 'UI', NULL, NULL, '2023-12-04 09:53:34', '2023-12-04 09:53:34', NULL),
(11, 'BRCH-TRK-ZSMEFFHAJL', 'Lead city', NULL, NULL, '2023-12-04 09:53:34', '2023-12-04 09:53:34', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES
(26, 'MOBILE', 'mobile', '2023-12-04 09:47:50', '2023-12-04 09:47:50'),
(27, 'LAPTOPS', 'laptops', '2023-12-04 09:47:50', '2023-12-04 09:47:50'),
(28, 'DESKTOP', 'desktop', '2023-12-04 09:47:50', '2023-12-04 09:47:50');

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `name`, `lastname`, `company_name`, `email`, `phone`, `address`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1211, 'Tawa barakat', NULL, NULL, NULL, NULL, NULL, '2023-12-04 10:00:27', '2023-12-04 10:00:27', NULL),
(1212, 'tawa', NULL, NULL, 'tawatope@gmail.com', '09023345188', 'Apata Ibadan', '2023-12-04 10:01:02', '2023-12-04 10:01:02', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `company_settings`
--

CREATE TABLE `company_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cashier_daily_filter` int(11) DEFAULT NULL,
  `sell_by_serial_no` int(11) NOT NULL DEFAULT 0,
  `logo_url` varchar(255) DEFAULT NULL,
  `currency` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone_one` varchar(255) DEFAULT NULL,
  `phone_two` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `invoice_header` varchar(255) NOT NULL,
  `invoice_footer_one` varchar(255) DEFAULT NULL,
  `invoice_footer_two` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `company_settings`
--

INSERT INTO `company_settings` (`id`, `cashier_daily_filter`, `sell_by_serial_no`, `logo_url`, `currency`, `country`, `website`, `city`, `name`, `email`, `phone_one`, `phone_two`, `address`, `invoice_header`, `invoice_footer_one`, `invoice_footer_two`, `created_at`, `updated_at`, `deleted_at`) VALUES
(5, 0, 0, 'http://127.0.0.1:8001/storage/logo/FXQgHnjo0D4dnVEVOUc8BSZ7fcRr.jpeg', 'NGN', 'Nigeria Naira', 'amiayosolutions.com', 'Ibadan', 'Amiayo solutions', 'amiayo@gmail.com', '09023345177', '08123345178', 'Shop 5, Leventist Ibadan', 'Sales of Laptops, Phones and Accessories!', 'Thanks for your Patronage!', 'Goods bought in good condition are not refundable', '2023-12-04 06:53:52', '2023-12-04 06:57:46', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `creditors`
--

CREATE TABLE `creditors` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `amount` bigint(20) NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `purchase_order_id` bigint(20) UNSIGNED NOT NULL,
  `supplier_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `creditors`
--

INSERT INTO `creditors` (`id`, `amount`, `product_id`, `purchase_order_id`, `supplier_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(14, 1500000, 51, 91, 12, '2023-12-04 09:52:16', '2023-12-04 09:52:16', NULL),
(15, 4000000, 50, 92, 13, '2023-12-04 14:12:43', '2023-12-04 14:12:43', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `creditors_payments`
--

CREATE TABLE `creditors_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `creditor_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `receiver` varchar(255) DEFAULT NULL,
  `amount` bigint(20) NOT NULL,
  `amount_paid` bigint(20) NOT NULL,
  `balance` bigint(20) NOT NULL,
  `payment_type` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `payment_mode` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `creditors_payments`
--

INSERT INTO `creditors_payments` (`id`, `creditor_id`, `branch_id`, `created_by`, `updated_by`, `receiver`, `amount`, `amount_paid`, `balance`, `payment_type`, `description`, `payment_mode`, `created_at`, `updated_at`, `deleted_at`) VALUES
(21, 14, NULL, NULL, NULL, NULL, 1500000, 0, 1500000, 'CREDITOR', NULL, NULL, '2023-12-04 09:52:16', '2023-12-04 09:52:16', NULL),
(22, 15, NULL, NULL, NULL, NULL, 4000000, 0, 4000000, 'CREDITOR', NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43', NULL),
(23, NULL, 6, 1, 1, 'ade', 100000, 100000, 0, 'EXPENSE', 'hello world', 'cash', '2023-12-04 17:14:44', '2023-12-04 17:14:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoiceitems`
--

CREATE TABLE `invoiceitems` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `rate` int(11) DEFAULT NULL,
  `unit` int(11) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  `discount` int(11) DEFAULT NULL,
  `document` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_order_no` varchar(255) DEFAULT NULL,
  `invoice_no` varchar(255) DEFAULT NULL,
  `currency` varchar(255) DEFAULT NULL,
  `cashier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `issued_date` varchar(255) DEFAULT NULL,
  `due_date` varchar(255) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  `amount_paid` int(11) DEFAULT NULL,
  `balance` int(11) DEFAULT NULL,
  `payment_status` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `payment_type` varchar(255) DEFAULT 'MANUAL',
  `discount` int(11) DEFAULT NULL,
  `invoice_type` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `purchase_order_no`, `invoice_no`, `currency`, `cashier_id`, `client_id`, `issued_date`, `due_date`, `amount`, `amount_paid`, `balance`, `payment_status`, `description`, `created_at`, `updated_at`, `deleted_at`, `transaction_id`, `payment_type`, `discount`, `invoice_type`) VALUES
(1406, NULL, 'INV-1', 'NGN', 11, 1211, '2023-12-04 11:01:51', '2023-12-03T23:00:00.000Z', 110000, 160000, 0, NULL, 'Sales from POS Menu', '2023-12-04 10:01:51', '2023-12-04 12:22:39', NULL, 'TRANSAC-NEA2B8YC5CTEWUS', 'POS', NULL, NULL),
(1407, NULL, 'INV-1407', 'NGN', 1, 1211, '2023-12-04 13:38:37', '2023-11-30T23:00:00.000Z', 110000, 110000, 0, NULL, 'Sales from POS Menu', '2023-12-04 12:38:37', '2023-12-04 12:38:37', NULL, 'TRANSAC-2YBPNTTSR8HM2OK', 'POS', NULL, NULL),
(1408, NULL, 'INV-1408', 'NGN', 11, 1212, '2023-12-04 15:27:39', '2023-11-30T23:00:00.000Z', 230000, 230000, 0, NULL, 'Sales from POS Menu', '2023-12-04 14:27:39', '2023-12-04 14:30:28', NULL, 'TRANSAC-VOQC7LNAUAPDQHF', 'POS', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(13, '2014_10_12_000000_create_users_table', 1),
(14, '2014_10_12_100000_create_password_resets_table', 1),
(15, '2019_08_19_000000_create_failed_jobs_table', 1),
(22, '2021_08_03_130135_create_categories_table', 2),
(23, '2021_08_03_130208_create_products_table', 2),
(24, '2021_08_03_130228_create_stocks_table', 2),
(25, '2021_08_03_130251_create_attributes_table', 2),
(26, '2021_08_03_130318_create_product_attributes_table', 2),
(27, '2021_08_05_140942_create_purchase_order_table', 2),
(28, '2021_08_05_154559_create_brands_table', 3),
(29, '2021_08_05_155233_create_supplier_table', 3),
(30, '2021_08_18_123948_create_table_branches', 4),
(34, '2021_08_25_232349_create_table_pos', 5),
(36, '2021_08_29_001434_create_table_productimages', 6),
(37, '2021_12_06_183106_create_table_phones', 7),
(38, '2022_03_08_114006_create_clients_table', 8),
(39, '2022_03_08_114029_create_invoices_table', 8),
(40, '2022_03_08_114044_create_invoiceitems_table', 8),
(41, '2022_03_08_115930_create_payments_table', 8),
(42, '2022_03_28_023920_create_company_settings_table', 9),
(43, '2022_04_04_044707_create_stocks_table', 10),
(44, '2022_04_05_173941_create_stock_serial_no_table', 11),
(45, '2022_04_29_095147_create_purchase_order_serials_table', 12),
(46, '2022_05_25_133142_create_creditors_table', 13),
(47, '2022_05_25_155329_create_creditors_payments_table', 13);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `invoice_no` varchar(255) DEFAULT NULL,
  `dues` int(11) DEFAULT NULL,
  `amount_paid` int(11) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  `balance` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `invoice_id`, `invoice_no`, `dues`, `amount_paid`, `amount`, `balance`, `created_at`, `updated_at`, `deleted_at`) VALUES
(120, 1406, NULL, NULL, 110000, 110000, 0, '2023-12-04 10:01:51', '2023-12-04 10:01:51', NULL),
(121, 1406, NULL, NULL, 0, 110000, 0, '2023-12-04 12:22:22', '2023-12-04 12:22:39', NULL),
(122, 1407, NULL, NULL, 110000, 110000, 0, '2023-12-04 12:38:37', '2023-12-04 12:38:37', NULL),
(123, 1408, NULL, NULL, 220000, 230000, 10000, '2023-12-04 14:27:39', '2023-12-04 14:27:39', NULL),
(124, 1408, NULL, NULL, 10000, 230000, 0, '2023-12-04 14:30:28', '2023-12-04 14:30:28', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `phones`
--

CREATE TABLE `phones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `phone` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `phones`
--

INSERT INTO `phones` (`id`, `user_id`, `phone`, `created_at`, `updated_at`) VALUES
(1, 2, '08023345167', '2021-12-07 04:25:31', '2021-12-07 04:50:35'),
(2, 1, '08023345167', '2021-12-07 04:30:16', '2021-12-07 04:30:16');

-- --------------------------------------------------------

--
-- Table structure for table `pos`
--

CREATE TABLE `pos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_order_id` int(255) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `stock_id` int(11) DEFAULT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `cashier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `serials` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`serials`)),
  `channel` varchar(255) DEFAULT NULL,
  `qty_sold` bigint(20) DEFAULT NULL,
  `payment_mode` varchar(255) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pos`
--

INSERT INTO `pos` (`id`, `purchase_order_id`, `supplier_id`, `stock_id`, `invoice_id`, `cashier_id`, `product_id`, `transaction_id`, `serials`, `channel`, `qty_sold`, `payment_mode`, `customer_name`, `created_at`, `updated_at`, `deleted_at`) VALUES
(290, 91, 12, 52, 1406, 11, 51, 'TRANSAC-NEA2B8YC5CTEWUS', NULL, 'pos_order', 1, 'cash', NULL, '2023-12-04 10:01:51', '2023-12-04 10:01:51', NULL),
(291, 91, 12, 53, 1407, 1, 51, 'TRANSAC-2YBPNTTSR8HM2OK', NULL, 'pos_order', 1, 'card', NULL, '2023-12-04 12:38:37', '2023-12-04 12:38:37', NULL),
(292, 92, 13, 55, 1408, 11, 50, 'TRANSAC-VOQC7LNAUAPDQHF', NULL, 'pos_order', 1, 'transfer', NULL, '2023-12-04 14:27:39', '2023-12-04 14:27:39', NULL),
(293, 91, 12, 52, 1408, 11, 51, 'TRANSAC-VOQC7LNAUAPDQHF', NULL, 'pos_order', 1, 'transfer', NULL, '2023-12-04 14:27:39', '2023-12-04 14:27:39', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `productimages`
--

CREATE TABLE `productimages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `deleted` int(11) DEFAULT NULL,
  `brand_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `slug`, `category_id`, `deleted`, `brand_id`, `supplier_id`, `status`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(49, 'HP 15', 'hp-15', 27, 0, NULL, NULL, NULL, 'mini laptops', '2023-12-04 09:48:20', '2023-12-04 09:48:20', NULL),
(50, 'dell xperion', 'dell-xperion', 27, 0, NULL, NULL, NULL, 'dell gadgets', '2023-12-04 09:48:46', '2023-12-04 09:48:46', NULL),
(51, 'Iphone 15', '', 26, 0, NULL, NULL, 1, 'Apple products', '2023-12-04 09:49:11', '2023-12-04 09:50:33', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_attributes`
--

CREATE TABLE `product_attributes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `attribute_id` int(11) DEFAULT NULL,
  `attribute_value` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order`
--

CREATE TABLE `purchase_order` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_attributes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`product_attributes`)),
  `product_attributes_keys` varchar(255) DEFAULT NULL,
  `product_id` bigint(20) DEFAULT NULL,
  `stock_quantity` bigint(20) DEFAULT 0,
  `payment_reference` varchar(255) DEFAULT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `quantity_sold` int(11) DEFAULT 0,
  `quantity_moved` int(11) DEFAULT 0,
  `quantity_returned` int(11) DEFAULT 0,
  `currency` varchar(255) DEFAULT NULL,
  `unit_price` bigint(20) DEFAULT NULL,
  `unit_selling_price` bigint(20) DEFAULT NULL,
  `amount` bigint(20) DEFAULT 0,
  `initiator_id` bigint(20) UNSIGNED DEFAULT NULL,
  `verifier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `receiver_id` bigint(20) UNSIGNED DEFAULT NULL,
  `supplier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `warehouse_id` bigint(20) UNSIGNED DEFAULT NULL,
  `billing_id` bigint(20) UNSIGNED DEFAULT NULL,
  `tracking_id` varchar(255) DEFAULT NULL,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `received_at` timestamp NULL DEFAULT NULL,
  `sold_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_order`
--

INSERT INTO `purchase_order` (`id`, `product_attributes`, `product_attributes_keys`, `product_id`, `stock_quantity`, `payment_reference`, `barcode`, `quantity`, `quantity_sold`, `quantity_moved`, `quantity_returned`, `currency`, `unit_price`, `unit_selling_price`, `amount`, `initiator_id`, `verifier_id`, `receiver_id`, `supplier_id`, `warehouse_id`, `billing_id`, `tracking_id`, `confirmed_at`, `cancelled_at`, `rejected_at`, `received_at`, `sold_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(91, NULL, NULL, 51, 15, NULL, NULL, 0, 0, 15, 0, NULL, 100000, 110000, 0, NULL, NULL, NULL, 12, NULL, NULL, 'TRK-5LFZZ', '2023-12-04 09:52:16', NULL, NULL, '2023-12-04 22:00:00', NULL, '2023-12-04 09:51:29', '2023-12-04 09:55:17', NULL),
(92, NULL, NULL, 50, 40, NULL, NULL, 0, 0, 10, 0, NULL, 100000, 120000, 0, NULL, NULL, NULL, 13, NULL, NULL, 'TRK-BRWN5', '2023-12-04 14:12:42', NULL, NULL, '2023-12-04 22:00:00', NULL, '2023-12-04 14:11:53', '2023-12-04 14:26:03', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_serials`
--

CREATE TABLE `purchase_order_serials` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_order_id` bigint(20) DEFAULT NULL,
  `serial_no` varchar(255) DEFAULT NULL,
  `moved_at` varchar(255) DEFAULT NULL,
  `returned_at` varchar(255) DEFAULT NULL,
  `branch_moved_to` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_order_serials`
--

INSERT INTO `purchase_order_serials` (`id`, `purchase_order_id`, `serial_no`, `moved_at`, `returned_at`, `branch_moved_to`, `created_at`, `updated_at`) VALUES
(884, 91, NULL, NULL, NULL, NULL, '2023-12-04 09:52:16', '2023-12-04 09:52:16'),
(885, 91, NULL, NULL, NULL, NULL, '2023-12-04 09:52:16', '2023-12-04 09:52:16'),
(886, 91, NULL, NULL, NULL, NULL, '2023-12-04 09:52:16', '2023-12-04 09:52:16'),
(887, 91, NULL, NULL, NULL, NULL, '2023-12-04 09:52:16', '2023-12-04 09:52:16'),
(888, 91, NULL, NULL, NULL, NULL, '2023-12-04 09:52:16', '2023-12-04 09:52:16'),
(889, 91, NULL, NULL, NULL, NULL, '2023-12-04 09:52:16', '2023-12-04 09:52:16'),
(890, 91, NULL, NULL, NULL, NULL, '2023-12-04 09:52:16', '2023-12-04 09:52:16'),
(891, 91, NULL, NULL, NULL, NULL, '2023-12-04 09:52:16', '2023-12-04 09:52:16'),
(892, 91, NULL, NULL, NULL, NULL, '2023-12-04 09:52:16', '2023-12-04 09:52:16'),
(893, 91, NULL, NULL, NULL, NULL, '2023-12-04 09:52:16', '2023-12-04 09:52:16'),
(894, 91, NULL, NULL, NULL, NULL, '2023-12-04 09:52:16', '2023-12-04 09:52:16'),
(895, 91, NULL, NULL, NULL, NULL, '2023-12-04 09:52:16', '2023-12-04 09:52:16'),
(896, 91, NULL, NULL, NULL, NULL, '2023-12-04 09:52:16', '2023-12-04 09:52:16'),
(897, 91, NULL, NULL, NULL, NULL, '2023-12-04 09:52:16', '2023-12-04 09:52:16'),
(898, 91, NULL, NULL, NULL, NULL, '2023-12-04 09:52:16', '2023-12-04 09:52:16'),
(899, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(900, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(901, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(902, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(903, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(904, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(905, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(906, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(907, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(908, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(909, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(910, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(911, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(912, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(913, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(914, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(915, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(916, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(917, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(918, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(919, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(920, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(921, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(922, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(923, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(924, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(925, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(926, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(927, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(928, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(929, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(930, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(931, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(932, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(933, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(934, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(935, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(936, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(937, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43'),
(938, 92, NULL, NULL, NULL, NULL, '2023-12-04 14:12:43', '2023-12-04 14:12:43');

-- --------------------------------------------------------

--
-- Table structure for table `stocks`
--

CREATE TABLE `stocks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `purchase_order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `stock_quantity` bigint(20) NOT NULL DEFAULT 0,
  `quantity_sold` int(11) NOT NULL DEFAULT 0,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `quantity_returned` bigint(20) NOT NULL DEFAULT 0,
  `status` varchar(255) DEFAULT NULL,
  `receiver_id` bigint(20) UNSIGNED DEFAULT NULL,
  `verifier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `received_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stocks`
--

INSERT INTO `stocks` (`id`, `branch_id`, `product_id`, `supplier_id`, `purchase_order_id`, `stock_quantity`, `quantity_sold`, `quantity`, `quantity_returned`, `status`, `receiver_id`, `verifier_id`, `confirmed_at`, `cancelled_at`, `rejected_at`, `received_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(52, 9, 51, 12, 91, 12, 2, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-12-04 09:54:13', '2023-12-04 14:27:39', NULL),
(53, 10, 51, 12, 91, 3, 1, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-12-04 09:55:07', '2023-12-04 12:38:37', NULL),
(54, 10, 50, 13, 92, 5, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-12-04 14:20:27', '2023-12-04 14:20:27', NULL),
(55, 9, 50, 13, 92, 5, 1, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2023-12-04 14:26:03', '2023-12-04 14:27:39', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `stock_serial_no`
--

CREATE TABLE `stock_serial_no` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `stock_id` bigint(20) DEFAULT NULL,
  `serial_no` varchar(255) DEFAULT NULL,
  `sold_at` timestamp NULL DEFAULT NULL,
  `status` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `street_address` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `country_code` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `zip` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `supplier_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`id`, `name`, `street_address`, `city`, `state`, `country`, `country_code`, `phone`, `email`, `zip`, `description`, `supplier_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(12, 'idea konsult', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'SUP-TRK-QZODS', '2023-12-04 09:47:01', '2023-12-04 09:47:01', NULL),
(13, 'NKG', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'SUP-TRK-TXHYO', '2023-12-04 09:47:01', '2023-12-04 09:47:01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `admin` int(11) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(255) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstname`, `lastname`, `admin`, `phone`, `email`, `branch_id`, `password`, `role`, `status`, `address`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'showole', 'oladayo', 1, '08023345166', 'show4ril@gmail.com', 6, '$2y$10$OToB11CWR8z8y6tdL6aJYeRh76kRS20BKCUfGk1VxJ4azSiW5qsky', '1', 1, NULL, NULL, '2021-08-03 18:40:59', '2021-08-03 18:40:59', NULL),
(11, 'Tope', 'Azeez', 0, '09023345188', 'azeeztope@gmail.com', 9, '$2y$10$xkltH.PdrteUGbkBjuDD8..4fjTSqc66cyU65tpCvD.mdyntgCnW2', NULL, 1, '3 Sango Ibadan', NULL, '2023-12-04 09:58:57', '2023-12-04 09:58:57', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attributes`
--
ALTER TABLE `attributes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `company_settings`
--
ALTER TABLE `company_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `creditors`
--
ALTER TABLE `creditors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `creditors_payments`
--
ALTER TABLE `creditors_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoiceitems`
--
ALTER TABLE `invoiceitems`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `phones`
--
ALTER TABLE `phones`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pos`
--
ALTER TABLE `pos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `productimages`
--
ALTER TABLE `productimages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_attributes`
--
ALTER TABLE `product_attributes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchase_order`
--
ALTER TABLE `purchase_order`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchase_order_serials`
--
ALTER TABLE `purchase_order_serials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stocks`
--
ALTER TABLE `stocks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock_serial_no`
--
ALTER TABLE `stock_serial_no`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attributes`
--
ALTER TABLE `attributes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1213;

--
-- AUTO_INCREMENT for table `company_settings`
--
ALTER TABLE `company_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `creditors`
--
ALTER TABLE `creditors`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `creditors_payments`
--
ALTER TABLE `creditors_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoiceitems`
--
ALTER TABLE `invoiceitems`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1462;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1409;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=125;

--
-- AUTO_INCREMENT for table `phones`
--
ALTER TABLE `phones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pos`
--
ALTER TABLE `pos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=294;

--
-- AUTO_INCREMENT for table `productimages`
--
ALTER TABLE `productimages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `product_attributes`
--
ALTER TABLE `product_attributes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `purchase_order`
--
ALTER TABLE `purchase_order`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `purchase_order_serials`
--
ALTER TABLE `purchase_order_serials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=939;

--
-- AUTO_INCREMENT for table `stocks`
--
ALTER TABLE `stocks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `stock_serial_no`
--
ALTER TABLE `stock_serial_no`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=308;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
