-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1:3307
-- Üretim Zamanı: 18 May 2025, 00:02:27
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.2.12
CREATE DATABASE IF NOT EXISTS `dormitory_database`
  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `dormitory_database`;


SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `dormitory_database`
--

DELIMITER $$
--
-- Yordamlar
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_backfill_paid_invoices` ()   BEGIN
  DECLARE cur_month DATE;
  DECLARE stu_id   INT;
  DECLARE done     INT DEFAULT 0;
  
  -- Öğrenci listesi için cursor
  DECLARE student_cursor CURSOR FOR 
    SELECT student_id FROM students;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

  -- 2024-09-01’den başla
  SET cur_month = '2024-09-01';
  -- Nisan ayı faturası için en son 2025-04-01
  WHILE cur_month <= '2025-04-01' DO

    SET done = 0;
    OPEN student_cursor;
    read_loop: LOOP
      FETCH student_cursor INTO stu_id;
      IF done THEN 
        LEAVE read_loop;
      END IF;

      -- 1) Fatura (ödendi olarak)
      INSERT INTO invoices
        (student_id, total_amount, issue_date, due_date, status_id, method_id)
      VALUES
        (stu_id, 40000.00, cur_month, DATE_ADD(cur_month, INTERVAL 9 DAY), 2, 1);

      -- 2) Ödeme kaydı (ayın 15’i saat 12:00)
      INSERT INTO payments
        (student_id, amount, payment_date, status_id, method_id)
      VALUES
        (stu_id, 40000.00,
         DATE_ADD(cur_month, INTERVAL 14 DAY) + INTERVAL '12:00:00' HOUR_SECOND,
         2, 1);

      -- 3) Tahsis
      SET @last_pay = LAST_INSERT_ID();
      SET @last_inv = (
        SELECT invoice_id 
        FROM invoices 
        WHERE student_id=stu_id 
          AND issue_date=cur_month
        ORDER BY invoice_id DESC
        LIMIT 1
      );
      INSERT INTO payment_allocations
        (payment_id, invoice_id, alloc_amount)
      VALUES
        (@last_pay, @last_inv, 40000.00);

    END LOOP;
    CLOSE student_cursor;

    -- Sonraki aya geç
    SET cur_month = DATE_ADD(cur_month, INTERVAL 1 MONTH);
  END WHILE;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `beds`
--

CREATE TABLE IF NOT EXISTS `beds` (
  `bed_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `bed_no` tinyint(3) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `beds`
--

INSERT IGNORE INTO `beds` (`bed_id`, `room_id`, `bed_no`) VALUES
(1, 31, 1),
(2, 31, 2),
(3, 32, 1),
(4, 32, 2),
(5, 33, 1),
(6, 33, 2),
(7, 34, 1),
(8, 34, 2),
(9, 35, 1),
(10, 35, 2),
(11, 36, 1),
(12, 36, 2);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `invoices`
--

CREATE TABLE IF NOT EXISTS `invoices` (
  `invoice_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `issue_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `status_id` int(11) NOT NULL DEFAULT 1,
  `method_id` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `invoices`
--

INSERT IGNORE INTO `invoices` (`invoice_id`, `student_id`, `total_amount`, `issue_date`, `due_date`, `status_id`, `method_id`) VALUES
(356, 1, 40000.00, '2025-05-01', '2025-05-11', 2, 1),
(357, 2, 40000.00, '2025-05-01', '2025-05-11', 1, 1),
(358, 3, 40000.00, '2025-05-01', '2025-05-11', 1, 1),
(359, 4, 40000.00, '2025-05-01', '2025-05-11', 1, 1),
(360, 7, 40000.00, '2025-05-01', '2025-05-11', 1, 1),
(361, 9, 40000.00, '2025-05-01', '2025-05-11', 1, 1),
(362, 12, 40000.00, '2025-05-01', '2025-05-11', 1, 1),
(363, 11, 40000.00, '2025-05-01', '2025-05-11', 1, 1),
(365, 1, 40000.00, '2024-09-01', '2024-09-10', 2, 1),
(366, 2, 40000.00, '2024-09-01', '2024-09-10', 2, 1),
(367, 3, 40000.00, '2024-09-01', '2024-09-10', 2, 1),
(368, 4, 40000.00, '2024-09-01', '2024-09-10', 2, 1),
(369, 7, 40000.00, '2024-09-01', '2024-09-10', 2, 1),
(370, 9, 40000.00, '2024-09-01', '2024-09-10', 2, 1),
(371, 12, 40000.00, '2024-09-01', '2024-09-10', 2, 1),
(372, 11, 40000.00, '2024-09-01', '2024-09-10', 2, 1),
(373, 1, 40000.00, '2024-10-01', '2024-10-10', 2, 1),
(374, 2, 40000.00, '2024-10-01', '2024-10-10', 2, 1),
(375, 3, 40000.00, '2024-10-01', '2024-10-10', 2, 1),
(376, 4, 40000.00, '2024-10-01', '2024-10-10', 2, 1),
(377, 7, 40000.00, '2024-10-01', '2024-10-10', 2, 1),
(378, 9, 40000.00, '2024-10-01', '2024-10-10', 2, 1),
(379, 12, 40000.00, '2024-10-01', '2024-10-10', 2, 1),
(380, 11, 40000.00, '2024-10-01', '2024-10-10', 2, 1),
(381, 1, 40000.00, '2024-11-01', '2024-11-10', 2, 1),
(382, 2, 40000.00, '2024-11-01', '2024-11-10', 2, 1),
(383, 3, 40000.00, '2024-11-01', '2024-11-10', 2, 1),
(384, 4, 40000.00, '2024-11-01', '2024-11-10', 2, 1),
(385, 7, 40000.00, '2024-11-01', '2024-11-10', 2, 1),
(386, 9, 40000.00, '2024-11-01', '2024-11-10', 2, 1),
(387, 12, 40000.00, '2024-11-01', '2024-11-10', 2, 1),
(388, 11, 40000.00, '2024-11-01', '2024-11-10', 2, 1),
(389, 1, 40000.00, '2024-12-01', '2024-12-10', 2, 1),
(390, 2, 40000.00, '2024-12-01', '2024-12-10', 2, 1),
(391, 3, 40000.00, '2024-12-01', '2024-12-10', 2, 1),
(392, 4, 40000.00, '2024-12-01', '2024-12-10', 2, 1),
(393, 7, 40000.00, '2024-12-01', '2024-12-10', 2, 1),
(394, 9, 40000.00, '2024-12-01', '2024-12-10', 2, 1),
(395, 12, 40000.00, '2024-12-01', '2024-12-10', 2, 1),
(396, 11, 40000.00, '2024-12-01', '2024-12-10', 2, 1),
(397, 1, 40000.00, '2025-01-01', '2025-01-10', 2, 1),
(398, 2, 40000.00, '2025-01-01', '2025-01-10', 2, 1),
(399, 3, 40000.00, '2025-01-01', '2025-01-10', 2, 1),
(400, 4, 40000.00, '2025-01-01', '2025-01-10', 2, 1),
(401, 7, 40000.00, '2025-01-01', '2025-01-10', 2, 1),
(402, 9, 40000.00, '2025-01-01', '2025-01-10', 2, 1),
(403, 12, 40000.00, '2025-01-01', '2025-01-10', 2, 1),
(404, 11, 40000.00, '2025-01-01', '2025-01-10', 2, 1),
(405, 1, 40000.00, '2025-02-01', '2025-02-10', 2, 1),
(406, 2, 40000.00, '2025-02-01', '2025-02-10', 2, 1),
(407, 3, 40000.00, '2025-02-01', '2025-02-10', 2, 1),
(408, 4, 40000.00, '2025-02-01', '2025-02-10', 2, 1),
(409, 7, 40000.00, '2025-02-01', '2025-02-10', 2, 1),
(410, 9, 40000.00, '2025-02-01', '2025-02-10', 2, 1),
(411, 12, 40000.00, '2025-02-01', '2025-02-10', 2, 1),
(412, 11, 40000.00, '2025-02-01', '2025-02-10', 2, 1),
(413, 1, 40000.00, '2025-03-01', '2025-03-10', 2, 1),
(414, 2, 40000.00, '2025-03-01', '2025-03-10', 2, 1),
(415, 3, 40000.00, '2025-03-01', '2025-03-10', 2, 1),
(416, 4, 40000.00, '2025-03-01', '2025-03-10', 2, 1),
(417, 7, 40000.00, '2025-03-01', '2025-03-10', 2, 1),
(418, 9, 40000.00, '2025-03-01', '2025-03-10', 2, 1),
(419, 12, 40000.00, '2025-03-01', '2025-03-10', 2, 1),
(420, 11, 40000.00, '2025-03-01', '2025-03-10', 2, 1),
(421, 1, 40000.00, '2025-04-01', '2025-04-10', 2, 1),
(422, 2, 40000.00, '2025-04-01', '2025-04-10', 2, 1),
(423, 3, 40000.00, '2025-04-01', '2025-04-10', 2, 1),
(424, 4, 40000.00, '2025-04-01', '2025-04-10', 2, 1),
(425, 7, 40000.00, '2025-04-01', '2025-04-10', 2, 1),
(426, 9, 40000.00, '2025-04-01', '2025-04-10', 2, 1),
(427, 12, 40000.00, '2025-04-01', '2025-04-10', 2, 1),
(428, 11, 40000.00, '2025-04-01', '2025-04-10', 2, 1);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `payments`
--

CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` datetime DEFAULT current_timestamp(),
  `status_id` int(11) NOT NULL DEFAULT 1,
  `method_id` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `payments`
--

INSERT IGNORE INTO `payments` (`payment_id`, `student_id`, `amount`, `payment_date`, `status_id`, `method_id`) VALUES
(4, 1, 40000.00, '2024-09-15 12:00:00', 2, 1),
(5, 2, 40000.00, '2024-09-15 12:00:00', 2, 1),
(6, 3, 40000.00, '2024-09-15 12:00:00', 2, 1),
(7, 4, 40000.00, '2024-09-15 12:00:00', 2, 1),
(8, 7, 40000.00, '2024-09-15 12:00:00', 2, 1),
(9, 9, 40000.00, '2024-09-15 12:00:00', 2, 1),
(10, 12, 40000.00, '2024-09-15 12:00:00', 2, 1),
(11, 11, 40000.00, '2024-09-15 12:00:00', 2, 1),
(12, 1, 40000.00, '2024-10-15 12:00:00', 2, 1),
(13, 2, 40000.00, '2024-10-15 12:00:00', 2, 1),
(14, 3, 40000.00, '2024-10-15 12:00:00', 2, 1),
(15, 4, 40000.00, '2024-10-15 12:00:00', 2, 1),
(16, 7, 40000.00, '2024-10-15 12:00:00', 2, 1),
(17, 9, 40000.00, '2024-10-15 12:00:00', 2, 1),
(18, 12, 40000.00, '2024-10-15 12:00:00', 2, 1),
(19, 11, 40000.00, '2024-10-15 12:00:00', 2, 1),
(20, 1, 40000.00, '2024-11-15 12:00:00', 2, 1),
(21, 2, 40000.00, '2024-11-15 12:00:00', 2, 1),
(22, 3, 40000.00, '2024-11-15 12:00:00', 2, 1),
(23, 4, 40000.00, '2024-11-15 12:00:00', 2, 1),
(24, 7, 40000.00, '2024-11-15 12:00:00', 2, 1),
(25, 9, 40000.00, '2024-11-15 12:00:00', 2, 1),
(26, 12, 40000.00, '2024-11-15 12:00:00', 2, 1),
(27, 11, 40000.00, '2024-11-15 12:00:00', 2, 1),
(28, 1, 40000.00, '2024-12-15 12:00:00', 2, 1),
(29, 2, 40000.00, '2024-12-15 12:00:00', 2, 1),
(30, 3, 40000.00, '2024-12-15 12:00:00', 2, 1),
(31, 4, 40000.00, '2024-12-15 12:00:00', 2, 1),
(32, 7, 40000.00, '2024-12-15 12:00:00', 2, 1),
(33, 9, 40000.00, '2024-12-15 12:00:00', 2, 1),
(34, 12, 40000.00, '2024-12-15 12:00:00', 2, 1),
(35, 11, 40000.00, '2024-12-15 12:00:00', 2, 1),
(36, 1, 40000.00, '2025-01-15 12:00:00', 2, 1),
(37, 2, 40000.00, '2025-01-15 12:00:00', 2, 1),
(38, 3, 40000.00, '2025-01-15 12:00:00', 2, 1),
(39, 4, 40000.00, '2025-01-15 12:00:00', 2, 1),
(40, 7, 40000.00, '2025-01-15 12:00:00', 2, 1),
(41, 9, 40000.00, '2025-01-15 12:00:00', 2, 1),
(42, 12, 40000.00, '2025-01-15 12:00:00', 2, 1),
(43, 11, 40000.00, '2025-01-15 12:00:00', 2, 1),
(44, 1, 40000.00, '2025-02-15 12:00:00', 2, 1),
(45, 2, 40000.00, '2025-02-15 12:00:00', 2, 1),
(46, 3, 40000.00, '2025-02-15 12:00:00', 2, 1),
(47, 4, 40000.00, '2025-02-15 12:00:00', 2, 1),
(48, 7, 40000.00, '2025-02-15 12:00:00', 2, 1),
(49, 9, 40000.00, '2025-02-15 12:00:00', 2, 1),
(50, 12, 40000.00, '2025-02-15 12:00:00', 2, 1),
(51, 11, 40000.00, '2025-02-15 12:00:00', 2, 1),
(52, 1, 40000.00, '2025-03-15 12:00:00', 2, 1),
(53, 2, 40000.00, '2025-03-15 12:00:00', 2, 1),
(54, 3, 40000.00, '2025-03-15 12:00:00', 2, 1),
(55, 4, 40000.00, '2025-03-15 12:00:00', 2, 1),
(56, 7, 40000.00, '2025-03-15 12:00:00', 2, 1),
(57, 9, 40000.00, '2025-03-15 12:00:00', 2, 1),
(58, 12, 40000.00, '2025-03-15 12:00:00', 2, 1),
(59, 11, 40000.00, '2025-03-15 12:00:00', 2, 1),
(60, 1, 40000.00, '2025-04-15 12:00:00', 2, 1),
(61, 2, 40000.00, '2025-04-15 12:00:00', 2, 1),
(62, 3, 40000.00, '2025-04-15 12:00:00', 2, 1),
(63, 4, 40000.00, '2025-04-15 12:00:00', 2, 1),
(64, 7, 40000.00, '2025-04-15 12:00:00', 2, 1),
(65, 9, 40000.00, '2025-04-15 12:00:00', 2, 1),
(66, 12, 40000.00, '2025-04-15 12:00:00', 2, 1),
(67, 11, 40000.00, '2025-04-15 12:00:00', 2, 1),
(69, 1, 40000.00, '2025-05-17 23:42:33', 2, 3);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `payment_allocations`
--

CREATE TABLE IF NOT EXISTS `payment_allocations` (
  `payment_id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `alloc_amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `payment_allocations`
--

INSERT IGNORE INTO `payment_allocations` (`payment_id`, `invoice_id`, `alloc_amount`) VALUES
(4, 365, 40000.00),
(5, 366, 40000.00),
(6, 367, 40000.00),
(7, 368, 40000.00),
(8, 369, 40000.00),
(9, 370, 40000.00),
(10, 371, 40000.00),
(11, 372, 40000.00),
(12, 373, 40000.00),
(13, 374, 40000.00),
(14, 375, 40000.00),
(15, 376, 40000.00),
(16, 377, 40000.00),
(17, 378, 40000.00),
(18, 379, 40000.00),
(19, 380, 40000.00),
(20, 381, 40000.00),
(21, 382, 40000.00),
(22, 383, 40000.00),
(23, 384, 40000.00),
(24, 385, 40000.00),
(25, 386, 40000.00),
(26, 387, 40000.00),
(27, 388, 40000.00),
(28, 389, 40000.00),
(29, 390, 40000.00),
(30, 391, 40000.00),
(31, 392, 40000.00),
(32, 393, 40000.00),
(33, 394, 40000.00),
(34, 395, 40000.00),
(35, 396, 40000.00),
(36, 397, 40000.00),
(37, 398, 40000.00),
(38, 399, 40000.00),
(39, 400, 40000.00),
(40, 401, 40000.00),
(41, 402, 40000.00),
(42, 403, 40000.00),
(43, 404, 40000.00),
(44, 405, 40000.00),
(45, 406, 40000.00),
(46, 407, 40000.00),
(47, 408, 40000.00),
(48, 409, 40000.00),
(49, 410, 40000.00),
(50, 411, 40000.00),
(51, 412, 40000.00),
(52, 413, 40000.00),
(53, 414, 40000.00),
(54, 415, 40000.00),
(55, 416, 40000.00),
(56, 417, 40000.00),
(57, 418, 40000.00),
(58, 419, 40000.00),
(59, 420, 40000.00),
(60, 421, 40000.00),
(61, 422, 40000.00),
(62, 423, 40000.00),
(63, 424, 40000.00),
(64, 425, 40000.00),
(65, 426, 40000.00),
(66, 427, 40000.00),
(67, 428, 40000.00),
(69, 356, 40000.00);

--
-- Tetikleyiciler `payment_allocations`
--
DELIMITER $$
CREATE TRIGGER `trg_after_allocation_insert` AFTER INSERT ON `payment_allocations` FOR EACH ROW BEGIN
  DECLARE paid_so_far DECIMAL(10,2);

  -- Bu invoice_id'ye ait tüm tahsisleri topla
  SELECT COALESCE(SUM(pa.alloc_amount),0)
    INTO paid_so_far
  FROM payment_allocations AS pa
  WHERE pa.invoice_id = NEW.invoice_id;

  -- Eğer toplam tahsis, faturanın tutarını karşıladıysa
  IF paid_so_far >= (
       SELECT i.total_amount
       FROM invoices AS i
       WHERE i.invoice_id = NEW.invoice_id
     ) THEN
    -- Statüyü "Ödendi" (status_id = 2) yap
    UPDATE invoices
      SET status_id = 2
    WHERE invoice_id = NEW.invoice_id;
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `payment_method`
--

CREATE TABLE IF NOT EXISTS `payment_method` (
  `method_id` int(11) NOT NULL,
  `method_name` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `payment_method`
--

INSERT IGNORE INTO `payment_method` (`method_id`, `method_name`) VALUES
(3, 'Banka Havale'),
(4, 'Diğer'),
(2, 'Kredi Kartı'),
(1, 'Nakit');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `payment_status`
--

CREATE TABLE IF NOT EXISTS `payment_status` (
  `status_id` int(11) NOT NULL,
  `status_name` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `payment_status`
--

INSERT IGNORE INTO `payment_status` (`status_id`, `status_name`) VALUES
(1, 'Beklemede'),
(3, 'İptal'),
(2, 'Ödendi');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `permissions`
--

CREATE TABLE IF NOT EXISTS `permissions` (
  `permission_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `permissions_type` enum('Weekend','Holiday','Medical','Family','Another') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `permissions`
--

INSERT IGNORE INTO `permissions` (`permission_id`, `student_id`, `start_date`, `end_date`, `permissions_type`) VALUES
(4, 3, '2025-05-25', '2025-05-29', 'Family'),
(16, 4, '2025-05-14', '2025-05-22', 'Medical'),
(17, 7, '2025-05-14', '2025-05-24', 'Medical'),
(18, 1, '2025-05-13', '2025-05-16', 'Holiday'),
(20, 9, '2025-06-05', '2025-06-07', 'Another'),
(21, 1, '2025-06-03', '2025-06-05', 'Weekend'),
(23, 11, '2025-05-23', '2025-05-30', 'Holiday'),
(24, 9, '2025-05-15', '2025-05-24', 'Holiday');

--
-- Tetikleyiciler `permissions`
--
DELIMITER $$
CREATE TRIGGER `trg_before_insert_no_overlap` BEFORE INSERT ON `permissions` FOR EACH ROW BEGIN
  IF EXISTS (
    SELECT 1 FROM permissions p
    WHERE p.student_id = NEW.student_id
      AND NEW.start_date <= p.end_date
      AND NEW.end_date >= p.start_date
  ) THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Bu tarihler arasında zaten izin var.';
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_before_insert_permissions` BEFORE INSERT ON `permissions` FOR EACH ROW BEGIN
  IF NEW.start_date < CURDATE() THEN
    SIGNAL SQLSTATE '45000' 
      SET MESSAGE_TEXT = 'Başlangıç tarihi bugünden önce olamaz.';
  END IF;
  IF NEW.end_date < NEW.start_date THEN
    SIGNAL SQLSTATE '45000' 
      SET MESSAGE_TEXT = 'Bitiş tarihi, başlangıçtan önce olamaz.';
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_before_update_permissions` BEFORE UPDATE ON `permissions` FOR EACH ROW BEGIN
  -- Başlangıç tarihi bugünden önce olamaz
  IF NEW.start_date < CURDATE() THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Başlangıç tarihi bugünden önce olamaz.';
  END IF;
  
  -- Bitiş tarihi, başlangıçtan önce olamaz
  IF NEW.end_date < NEW.start_date THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Bitiş tarihi, başlangıç tarihinden önce olamaz.';
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `permission_approved_by`
--

CREATE TABLE IF NOT EXISTS `permission_approved_by` (
  `permission_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `approved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `permission_approved_by`
--

INSERT IGNORE INTO `permission_approved_by` (`permission_id`, `user_id`, `approved_at`) VALUES
(21, 2, '2025-05-17 05:19:12'),
(23, 2, '2025-05-17 04:14:02');

--
-- Tetikleyiciler `permission_approved_by`
--
DELIMITER $$
CREATE TRIGGER `trg_before_insert_permission_approved_by` BEFORE INSERT ON `permission_approved_by` FOR EACH ROW BEGIN
  DECLARE sd DATE;
  DECLARE ed DATE;
  SELECT start_date, end_date
    INTO sd, ed
    FROM permissions
   WHERE permission_id = NEW.permission_id;

  IF sd < CURDATE() THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Başlangıç tarihi bugünden önce olamaz, bu izin onaylanamaz.';
  END IF;

  IF ed < sd THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Bitiş tarihi, başlangıçtan önce olamaz, bu izin onaylanamaz.';
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `permission_created_by`
--

CREATE TABLE IF NOT EXISTS `permission_created_by` (
  `permission_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `permission_created_by`
--

INSERT IGNORE INTO `permission_created_by` (`permission_id`, `user_id`) VALUES
(20, 1),
(21, 1),
(23, 1),
(24, 1);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rooms`
--

CREATE TABLE IF NOT EXISTS `rooms` (
  `room_id` int(11) NOT NULL,
  `room_number` varchar(10) NOT NULL,
  `capacity` int(11) NOT NULL DEFAULT 2
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `rooms`
--

INSERT IGNORE INTO `rooms` (`room_id`, `room_number`, `capacity`) VALUES
(31, '101', 2),
(32, '102', 2),
(33, '201', 2),
(34, '202', 2),
(35, '301', 2),
(36, '302', 2);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `room_assignments`
--

CREATE TABLE IF NOT EXISTS `room_assignments` (
  `room_assignments_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `bed_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `room_assignments`
--

INSERT IGNORE INTO `room_assignments` (`room_assignments_id`, `student_id`, `bed_id`) VALUES
(2, 4, 5),
(3, 2, 1),
(4, 7, 2),
(5, 1, 10),
(6, 11, 11),
(7, 3, 8),
(8, 9, 7),
(9, 12, 6);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `students`
--

CREATE TABLE IF NOT EXISTS `students` (
  `student_id` int(11) NOT NULL,
  `TC_no` char(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `birth_date` date DEFAULT NULL,
  `register_date` datetime DEFAULT current_timestamp(),
  `stud_telNo` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `students`
--

INSERT IGNORE INTO `students` (`student_id`, `TC_no`, `first_name`, `last_name`, `birth_date`, `register_date`, `stud_telNo`) VALUES
(1, '11111111111', 'Elif', 'Güldal', '2006-08-02', '2025-05-12 04:34:26', '5014509900'),
(2, '11111111121', 'Ayşe', 'Arı', '2005-03-02', '2025-05-12 04:40:14', '5016705005'),
(3, '11111161121', 'Sena', 'Kır', '2002-06-04', '2025-05-12 04:40:50', '5019191220'),
(4, '11911161121', 'Zeynep', 'Aydın', '2003-09-17', '2025-05-12 04:41:31', '5014939689'),
(7, '15918161121', 'Tayyibe', 'Gazioğlu', '2010-01-25', '2025-05-12 04:43:19', '5019593386'),
(9, '15918163121', 'Saliha', 'Kır', '2009-12-25', '2025-05-12 04:44:15', '5018481607'),
(11, '98765432145', 'Pakize', 'Regaip', '2003-08-29', '2025-05-13 22:13:10', '5384154129'),
(12, '54986532145', 'Ayşe', 'Fatma', '2006-08-17', '2025-05-17 00:06:05', '5864125689');

--
-- Tetikleyiciler `students`
--
DELIMITER $$
CREATE TRIGGER `trg_after_insert_student` AFTER INSERT ON `students` FOR EACH ROW BEGIN
  DECLARE issue_dt DATE;
  SET issue_dt = DATE_FORMAT(NEW.register_date, '%Y-%m-01');

  INSERT INTO invoices (
    student_id, total_amount, issue_date, due_date, status_id, method_id
  ) VALUES (
    NEW.student_id,
    40000,
    issue_dt,
    DATE_ADD(issue_dt, INTERVAL 9 DAY),
    1, 
    1  
  );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `student_entry_logs`
--

CREATE TABLE IF NOT EXISTS `student_entry_logs` (
  `student_entry_log_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `security_id` int(11) NOT NULL,
  `action` enum('enter','leave') NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `student_entry_logs`
--

INSERT IGNORE INTO `student_entry_logs` (`student_entry_log_id`, `student_id`, `security_id`, `action`, `timestamp`) VALUES
(1, 1, 1, 'enter', '2025-05-12 04:35:15'),
(2, 1, 1, 'enter', '2025-05-12 04:41:37'),
(3, 1, 1, 'enter', '2025-05-12 04:43:22'),
(4, 1, 1, 'enter', '2025-05-12 04:44:19'),
(5, 1, 1, 'enter', '2025-05-12 04:45:36'),
(6, 1, 1, 'leave', '2025-05-12 06:54:10'),
(7, 1, 1, 'leave', '2025-05-12 06:56:00'),
(8, 1, 1, 'leave', '2025-05-12 06:57:13'),
(9, 1, 1, 'leave', '2025-05-12 06:57:55'),
(10, 1, 1, 'leave', '2025-05-12 06:58:24'),
(11, 2, 1, 'enter', '2025-05-12 07:40:39'),
(12, 2, 1, 'leave', '2025-05-12 07:41:41'),
(13, 2, 1, 'enter', '2025-05-12 07:41:43'),
(14, 2, 1, 'leave', '2025-05-12 07:41:46'),
(15, 2, 1, 'enter', '2025-05-12 07:41:47'),
(16, 9, 1, 'enter', '2025-05-12 07:42:21'),
(17, 9, 1, 'leave', '2025-05-12 07:42:22'),
(18, 9, 1, 'enter', '2025-05-12 07:42:23'),
(19, 2, 1, 'leave', '2025-05-12 07:42:48'),
(20, 2, 1, 'enter', '2025-05-12 07:42:48'),
(21, 2, 1, 'leave', '2025-05-12 07:42:50'),
(22, 2, 1, 'enter', '2025-05-12 07:42:52'),
(23, 2, 1, 'leave', '2025-05-12 07:42:53'),
(24, 2, 1, 'enter', '2025-05-12 07:44:26'),
(25, 2, 1, 'leave', '2025-05-12 07:46:44'),
(26, 2, 1, 'enter', '2025-05-12 07:55:48'),
(27, 1, 1, 'enter', '2025-05-13 17:51:42'),
(28, 1, 1, 'leave', '2025-05-13 17:51:45'),
(29, 1, 1, 'enter', '2025-05-17 01:35:32'),
(30, 12, 1, 'enter', '2025-05-17 02:44:52'),
(31, 12, 1, 'leave', '2025-05-17 02:44:54'),
(32, 1, 1, 'leave', '2025-05-17 05:18:31');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL,
  `TC_no` char(11) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `role` enum('students affair','security') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `users`
--

INSERT IGNORE INTO `users` (`user_id`, `TC_no`, `password`, `first_name`, `last_name`, `role`) VALUES
(1, '12345678901', '$2y$10$cSu3ZFrzwB4tfHEhLrlfeOphhI1oxYvX.k2zkYtOMcSOaumuYwf92', 'Ali', 'Güvenlik', 'security'),
(2, '12345678932', '$2y$10$v3PMAzlBTsi3S/U/NXBr/OO5Z/l3MH2pMGbCHJdAIwbdrWZv81TK.', 'Sudenaz', 'Güldal', 'students affair'),
(3, '15345678932', '$2y$10$h5g.dAO3qcEFSice9gRax.gPzBvZV6R3oKp20hyMYMunk9xNo6BvG', 'Merve', 'Gazioğlu', 'students affair'),
(4, '15345628932', '$2y$10$bSbiF7LnJe7egJ/OomrssOdokDwZaLH9tk7KNaCKzZfdhM/pr/OYW', 'Melike', 'Kır', 'students affair');

-- --------------------------------------------------------

--
-- Görünüm yapısı durumu `view_active_permissions`
-- (Asıl görünüm için aşağıya bakın)
--
CREATE TABLE IF NOT EXISTS `view_active_permissions` (
`permission_id` int(11)
,`student_id` int(11)
,`student_name` varchar(101)
,`start_date` date
,`end_date` date
,`permissions_type` enum('Weekend','Holiday','Medical','Family','Another')
);

-- --------------------------------------------------------

--
-- Görünüm yapısı durumu `view_pending_permissions`
-- (Asıl görünüm için aşağıya bakın)
--
CREATE TABLE IF NOT EXISTS `view_pending_permissions` (
`permission_id` int(11)
,`student_name` varchar(101)
,`start_date` date
,`end_date` date
,`permissions_type` enum('Weekend','Holiday','Medical','Family','Another')
);

-- --------------------------------------------------------

--
-- Görünüm yapısı durumu `view_recent_approvals`
-- (Asıl görünüm için aşağıya bakın)
--
CREATE TABLE IF NOT EXISTS `view_recent_approvals` (
`permission_id` int(11)
,`student_name` varchar(101)
,`start_date` date
,`end_date` date
,`permissions_type` enum('Weekend','Holiday','Medical','Family','Another')
,`approved_by` int(11)
,`approved_at` datetime
);

-- --------------------------------------------------------

--
-- Görünüm yapısı durumu `v_active_invoices`
-- (Asıl görünüm için aşağıya bakın)
--
CREATE TABLE IF NOT EXISTS `v_active_invoices` (
`invoice_id` int(11)
,`student_name` varchar(101)
,`total_amount` decimal(10,2)
,`issue_date` varchar(10)
,`due_date` varchar(10)
,`status_name` varchar(30)
);

-- --------------------------------------------------------

--
-- Görünüm yapısı durumu `v_invoice_details`
-- (Asıl görünüm için aşağıya bakın)
--
CREATE TABLE IF NOT EXISTS `v_invoice_details` (
`invoice_id` int(11)
,`student_id` int(11)
,`student_name` varchar(101)
,`total_amount` decimal(10,2)
,`issue_date` date
,`due_date` date
,`status_name` varchar(30)
,`paid_so_far` decimal(32,2)
,`remaining` decimal(33,2)
);

-- --------------------------------------------------------

--
-- Görünüm yapısı durumu `v_monthly_payments`
-- (Asıl görünüm için aşağıya bakın)
--
CREATE TABLE IF NOT EXISTS `v_monthly_payments` (
`month` varchar(7)
,`total_received` decimal(32,2)
,`payment_count` bigint(21)
);

-- --------------------------------------------------------

--
-- Görünüm yapısı durumu `v_recent_permissions`
-- (Asıl görünüm için aşağıya bakın)
--
CREATE TABLE IF NOT EXISTS `v_recent_permissions` (
`permission_id` int(11)
,`start_date` date
,`end_date` date
,`permissions_type` enum('Weekend','Holiday','Medical','Family','Another')
,`student_name` varchar(101)
);

-- --------------------------------------------------------

--
-- Görünüm yapısı `view_active_permissions`
--
DROP TABLE IF EXISTS `view_active_permissions`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_active_permissions`  AS SELECT `p`.`permission_id` AS `permission_id`, `s`.`student_id` AS `student_id`, concat(`s`.`first_name`,' ',`s`.`last_name`) AS `student_name`, `p`.`start_date` AS `start_date`, `p`.`end_date` AS `end_date`, `p`.`permissions_type` AS `permissions_type` FROM (`permissions` `p` join `students` `s` on(`s`.`student_id` = `p`.`student_id`)) WHERE curdate() between `p`.`start_date` and `p`.`end_date` ;

-- --------------------------------------------------------

--
-- Görünüm yapısı `view_pending_permissions`
--
DROP TABLE IF EXISTS `view_pending_permissions`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_pending_permissions`  AS SELECT `p`.`permission_id` AS `permission_id`, concat(`s`.`first_name`,' ',`s`.`last_name`) AS `student_name`, `p`.`start_date` AS `start_date`, `p`.`end_date` AS `end_date`, `p`.`permissions_type` AS `permissions_type` FROM ((`permissions` `p` join `students` `s` on(`s`.`student_id` = `p`.`student_id`)) left join `permission_approved_by` `a` on(`a`.`permission_id` = `p`.`permission_id`)) WHERE `a`.`permission_id` is null AND `p`.`permission_id` = (select max(`p2`.`permission_id`) from `permissions` `p2` where `p2`.`student_id` = `p`.`student_id`) ORDER BY `p`.`permission_id` DESC LIMIT 0, 5 ;

-- --------------------------------------------------------

--
-- Görünüm yapısı `view_recent_approvals`
--
DROP TABLE IF EXISTS `view_recent_approvals`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_recent_approvals`  AS SELECT `p`.`permission_id` AS `permission_id`, concat(`s`.`first_name`,' ',`s`.`last_name`) AS `student_name`, `p`.`start_date` AS `start_date`, `p`.`end_date` AS `end_date`, `p`.`permissions_type` AS `permissions_type`, `a`.`user_id` AS `approved_by`, `a`.`approved_at` AS `approved_at` FROM ((`permission_approved_by` `a` join `permissions` `p` on(`p`.`permission_id` = `a`.`permission_id`)) join `students` `s` on(`s`.`student_id` = `p`.`student_id`)) ORDER BY `a`.`approved_at` DESC LIMIT 0, 5 ;

-- --------------------------------------------------------

--
-- Görünüm yapısı `v_active_invoices`
--
DROP TABLE IF EXISTS `v_active_invoices`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_active_invoices`  AS SELECT `i`.`invoice_id` AS `invoice_id`, concat(`s`.`first_name`,' ',`s`.`last_name`) AS `student_name`, `i`.`total_amount` AS `total_amount`, date_format(`i`.`issue_date`,'%Y-%m-%d') AS `issue_date`, date_format(`i`.`due_date`,'%Y-%m-%d') AS `due_date`, `ps`.`status_name` AS `status_name` FROM ((`invoices` `i` join `students` `s` on(`i`.`student_id` = `s`.`student_id`)) join `payment_status` `ps` on(`i`.`status_id` = `ps`.`status_id`)) WHERE `i`.`status_id` = 1 ORDER BY `i`.`issue_date` DESC ;

-- --------------------------------------------------------

--
-- Görünüm yapısı `v_invoice_details`
--
DROP TABLE IF EXISTS `v_invoice_details`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_invoice_details`  AS SELECT `i`.`invoice_id` AS `invoice_id`, `i`.`student_id` AS `student_id`, concat(`s`.`first_name`,' ',`s`.`last_name`) AS `student_name`, `i`.`total_amount` AS `total_amount`, `i`.`issue_date` AS `issue_date`, `i`.`due_date` AS `due_date`, `ps`.`status_name` AS `status_name`, coalesce(sum(`pa`.`alloc_amount`),0) AS `paid_so_far`, `i`.`total_amount`- coalesce(sum(`pa`.`alloc_amount`),0) AS `remaining` FROM (((`invoices` `i` join `students` `s` on(`i`.`student_id` = `s`.`student_id`)) join `payment_status` `ps` on(`i`.`status_id` = `ps`.`status_id`)) left join `payment_allocations` `pa` on(`i`.`invoice_id` = `pa`.`invoice_id`)) GROUP BY `i`.`invoice_id` ;

-- --------------------------------------------------------

--
-- Görünüm yapısı `v_monthly_payments`
--
DROP TABLE IF EXISTS `v_monthly_payments`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_monthly_payments`  AS SELECT date_format(`payments`.`payment_date`,'%Y-%m') AS `month`, sum(`payments`.`amount`) AS `total_received`, count(0) AS `payment_count` FROM `payments` GROUP BY date_format(`payments`.`payment_date`,'%Y-%m') ORDER BY date_format(`payments`.`payment_date`,'%Y-%m') DESC ;

-- --------------------------------------------------------

--
-- Görünüm yapısı `v_recent_permissions`
--
DROP TABLE IF EXISTS `v_recent_permissions`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_recent_permissions`  AS SELECT `p`.`permission_id` AS `permission_id`, `p`.`start_date` AS `start_date`, `p`.`end_date` AS `end_date`, `p`.`permissions_type` AS `permissions_type`, concat(`s`.`first_name`,' ',`s`.`last_name`) AS `student_name` FROM (`permissions` `p` join `students` `s` on(`s`.`student_id` = `p`.`student_id`)) ORDER BY `p`.`permission_id` DESC ;

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `beds`
--
ALTER TABLE `beds`
  ADD PRIMARY KEY (`bed_id`),
  ADD UNIQUE KEY `uq_beds_room_bed` (`room_id`,`bed_no`);

--
-- Tablo için indeksler `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`invoice_id`),
  ADD KEY `idx_invoices_student` (`student_id`),
  ADD KEY `idx_invoices_issue` (`issue_date`),
  ADD KEY `fk_invoices_status` (`status_id`),
  ADD KEY `fk_invoices_method` (`method_id`);

--
-- Tablo için indeksler `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `fk_payments_status` (`status_id`),
  ADD KEY `fk_payments_method` (`method_id`),
  ADD KEY `idx_payments_student` (`student_id`),
  ADD KEY `idx_payments_date` (`payment_date`),
  ADD KEY `idx_payments_invoice` (`payment_id`);

--
-- Tablo için indeksler `payment_allocations`
--
ALTER TABLE `payment_allocations`
  ADD PRIMARY KEY (`payment_id`,`invoice_id`),
  ADD KEY `idx_alloc_payment` (`payment_id`),
  ADD KEY `idx_alloc_invoice` (`invoice_id`);

--
-- Tablo için indeksler `payment_method`
--
ALTER TABLE `payment_method`
  ADD PRIMARY KEY (`method_id`),
  ADD UNIQUE KEY `method_name` (`method_name`);

--
-- Tablo için indeksler `payment_status`
--
ALTER TABLE `payment_status`
  ADD PRIMARY KEY (`status_id`),
  ADD UNIQUE KEY `status_name` (`status_name`);

--
-- Tablo için indeksler `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`permission_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Tablo için indeksler `permission_approved_by`
--
ALTER TABLE `permission_approved_by`
  ADD PRIMARY KEY (`permission_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `permission_created_by`
--
ALTER TABLE `permission_created_by`
  ADD PRIMARY KEY (`permission_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_id`),
  ADD UNIQUE KEY `room_number` (`room_number`);

--
-- Tablo için indeksler `room_assignments`
--
ALTER TABLE `room_assignments`
  ADD PRIMARY KEY (`room_assignments_id`),
  ADD UNIQUE KEY `uq_ra_bed` (`bed_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Tablo için indeksler `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `TC_no` (`TC_no`);

--
-- Tablo için indeksler `student_entry_logs`
--
ALTER TABLE `student_entry_logs`
  ADD PRIMARY KEY (`student_entry_log_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `security_id` (`security_id`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `TC_no` (`TC_no`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `beds`
--
ALTER TABLE `beds`
  MODIFY `bed_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Tablo için AUTO_INCREMENT değeri `invoices`
--
ALTER TABLE `invoices`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=492;

--
-- Tablo için AUTO_INCREMENT değeri `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- Tablo için AUTO_INCREMENT değeri `payment_method`
--
ALTER TABLE `payment_method`
  MODIFY `method_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Tablo için AUTO_INCREMENT değeri `payment_status`
--
ALTER TABLE `payment_status`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `permissions`
--
ALTER TABLE `permissions`
  MODIFY `permission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Tablo için AUTO_INCREMENT değeri `rooms`
--
ALTER TABLE `rooms`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- Tablo için AUTO_INCREMENT değeri `room_assignments`
--
ALTER TABLE `room_assignments`
  MODIFY `room_assignments_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Tablo için AUTO_INCREMENT değeri `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Tablo için AUTO_INCREMENT değeri `student_entry_logs`
--
ALTER TABLE `student_entry_logs`
  MODIFY `student_entry_log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `beds`
--
ALTER TABLE `beds`
  ADD CONSTRAINT `beds_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`);

--
-- Tablo kısıtlamaları `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `fk_invoices_method` FOREIGN KEY (`method_id`) REFERENCES `payment_method` (`method_id`),
  ADD CONSTRAINT `fk_invoices_status` FOREIGN KEY (`status_id`) REFERENCES `payment_status` (`status_id`),
  ADD CONSTRAINT `fk_invoices_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_method` FOREIGN KEY (`method_id`) REFERENCES `payment_method` (`method_id`),
  ADD CONSTRAINT `fk_payments_status` FOREIGN KEY (`status_id`) REFERENCES `payment_status` (`status_id`),
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`);

--
-- Tablo kısıtlamaları `payment_allocations`
--
ALTER TABLE `payment_allocations`
  ADD CONSTRAINT `fk_pa_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`invoice_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pa_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`payment_id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `permissions`
--
ALTER TABLE `permissions`
  ADD CONSTRAINT `permissions_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`);

--
-- Tablo kısıtlamaları `permission_approved_by`
--
ALTER TABLE `permission_approved_by`
  ADD CONSTRAINT `permission_approved_by_ibfk_1` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`permission_id`),
  ADD CONSTRAINT `permission_approved_by_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Tablo kısıtlamaları `permission_created_by`
--
ALTER TABLE `permission_created_by`
  ADD CONSTRAINT `permission_created_by_ibfk_1` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`permission_id`),
  ADD CONSTRAINT `permission_created_by_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Tablo kısıtlamaları `room_assignments`
--
ALTER TABLE `room_assignments`
  ADD CONSTRAINT `fk_ra_bed` FOREIGN KEY (`bed_id`) REFERENCES `beds` (`bed_id`),
  ADD CONSTRAINT `room_assignments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`);

--
-- Tablo kısıtlamaları `student_entry_logs`
--
ALTER TABLE `student_entry_logs`
  ADD CONSTRAINT `student_entry_logs_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `student_entry_logs_ibfk_2` FOREIGN KEY (`security_id`) REFERENCES `users` (`user_id`);

DELIMITER $$
--
-- Olaylar
--
CREATE DEFINER=`root`@`localhost` EVENT `ev_generate_monthly_invoices` ON SCHEDULE EVERY 1 MONTH STARTS '2024-09-17 00:00:03' ON COMPLETION PRESERVE ENABLE DO INSERT INTO invoices (
    student_id,
    total_amount,
    issue_date,
    due_date,
    status_id,
    method_id
  )
  SELECT
    s.student_id,
    40000,
    DATE_FORMAT(NOW(), '%Y-%m-01'),
    DATE_ADD(DATE_FORMAT(NOW(), '%Y-%m-01'), INTERVAL 10 DAY),
    1,
    1
  FROM students s$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
