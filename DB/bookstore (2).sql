-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 22 Feb 2026 pada 04.43
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bookstore`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `icon` varchar(225) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `icon`, `created_at`) VALUES
(2, 'Romansa', '696ae83b3f2bf.png', '0000-00-00 00:00:00'),
(3, 'Horor', '696ae82e4a123.png', '0000-00-00 00:00:00'),
(4, 'Matematika', '696ae70a2bce0.webp', '0000-00-00 00:00:00'),
(5, 'Bahasa Indonesia', '696ae82267c23.jfif', '0000-00-00 00:00:00'),
(6, 'Bahasa Inggris', '696ae7af498df.png', '0000-00-00 00:00:00'),
(7, 'Komik', '696ae72d2b8b7.png', '0000-00-00 00:00:00'),
(8, 'Novel', '', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `keranjang`
--

CREATE TABLE `keranjang` (
  `id_keranjang` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `id_produk` int(11) DEFAULT NULL,
  `qty` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `messages`
--

CREATE TABLE `messages` (
  `id_message` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `messages`
--

INSERT INTO `messages` (`id_message`, `id_produk`, `sender_id`, `receiver_id`, `message`, `created_at`, `is_read`) VALUES
(1, 0, 2, 3, 'halo', '2026-01-15 10:34:01', 1),
(2, 0, 2, 3, 'halo', '2026-01-15 10:36:35', 1),
(3, 0, 4, 3, 'hola', '2026-01-17 01:44:13', 1),
(4, 0, 3, 2, 'Apakah ada yang bisa saya bantu ?', '2026-01-17 12:42:30', 0),
(5, 0, 3, 4, 'Halo apakah ada yang bisa saya bantu ?', '2026-01-17 12:42:47', 1),
(6, 0, 4, 3, 'saya ingin membeli buku one piece nya apakah  masih ada?', '2026-01-17 13:31:07', 1),
(7, 0, 4, 3, 'Halo, apakah buku ini tersedia?', '2026-01-17 13:31:37', 1),
(8, 0, 4, 3, 'Apakah One Piece tersedia?', '2026-01-18 01:57:10', 1),
(9, 0, 4, 3, 'Berapa harga buku One Piece?', '2026-01-18 01:57:20', 1),
(10, 0, 3, 4, ' tentu nya ada', '2026-01-18 06:55:24', 1),
(11, 0, 3, 4, 'boleh di checkout', '2026-01-18 06:55:50', 1),
(12, 0, 4, 3, 'Apakah One Piece tersedia?', '2026-01-18 07:55:40', 1),
(13, 0, 3, 4, 'tentu ada', '2026-01-18 07:56:06', 1),
(14, 0, 6, 3, 'Halo, saya tertarik dengan koleksi buku di Aksara Jiwa! 📚', '2026-01-19 01:39:40', 1),
(15, 0, 3, 6, 'khsadjkakjf', '2026-01-19 01:40:10', 1),
(16, 0, 3, 6, 'tiudak', '2026-01-19 01:48:47', 1),
(17, 0, 3, 2, 'tes', '2026-01-19 01:58:20', 0),
(18, 0, 3, 6, 'cdscsdvcd\\', '2026-01-19 01:58:28', 1),
(19, 0, 6, 3, 'hola', '2026-01-19 04:29:45', 0),
(20, 4, 4, 7, 'halo', '2026-01-22 00:58:45', 0),
(21, 3, 4, 3, 'tes', '2026-01-22 03:16:40', 1),
(22, 0, 3, 4, 'oke', '2026-01-22 03:17:15', 0),
(23, 3, 4, 3, 'tes', '2026-01-22 04:05:52', 1),
(24, 2, 4, 3, 'hola', '2026-01-23 07:22:27', 1),
(25, 2, 4, 3, 'tes', '2026-01-23 07:41:02', 1),
(26, 2, 4, 3, 'esk', '2026-01-23 07:45:49', 1),
(27, 2, 3, 4, 'oke', '2026-01-23 07:52:35', 1),
(28, 7, 4, 8, 'Halo, saya tertarik dengan koleksi buku di Aksara Jiwa! 📚', '2026-01-29 02:01:25', 1),
(29, 7, 4, 8, 'Bisa rekomendasikan buku fiksi terbaik?', '2026-01-29 02:01:44', 1),
(30, 7, 8, 4, 'boleh', '2026-01-29 02:03:14', 1),
(31, 7, 8, 4, 'kita ada di buku hujan di ujung senja', '2026-01-29 02:03:26', 1),
(32, 7, 4, 8, 'harga nya berapa?', '2026-01-29 02:03:48', 1),
(33, 7, 8, 4, '12000', '2026-01-29 07:20:33', 1),
(34, 7, 4, 8, 'oke saya order yah', '2026-01-29 07:20:55', 1),
(35, 7, 8, 4, 'baik silahkan', '2026-01-29 07:21:11', 1),
(36, 3, 4, 3, 'hola', '2026-02-08 13:42:22', 1),
(37, 3, 3, 4, 'oke', '2026-02-08 13:42:44', 1),
(38, 3, 4, 3, 'harga nya ada yang murah', '2026-02-08 13:42:59', 1),
(39, 3, 4, 3, 'hola', '2026-02-08 13:48:42', 1),
(40, 3, 4, 3, 'tes', '2026-02-08 13:49:01', 1),
(41, 3, 4, 3, 'tes', '2026-02-08 13:51:39', 1),
(42, 3, 4, 3, 'cek', '2026-02-08 13:52:11', 1),
(43, 3, 4, 3, 'tes', '2026-02-08 13:57:27', 1),
(44, 3, 3, 4, 'boleh', '2026-02-08 13:58:32', 1),
(45, 3, 4, 3, 'oke', '2026-02-08 13:58:49', 1),
(46, 3, 4, 3, 'tes', '2026-02-08 14:00:30', 1),
(47, 3, 4, 3, 'cek', '2026-02-08 14:20:09', 1),
(48, 3, 3, 4, 'siap', '2026-02-09 01:21:25', 1),
(49, 9, 4, 11, 'Halo, saya ingin bertanya tentang koleksi buku di Aksara Jiwa!', '2026-02-09 13:34:18', 0),
(50, 9, 4, 11, 'Apakah buku ini tersedia stoknya? 📚', '2026-02-09 13:34:30', 0),
(51, 7, 4, 8, 'tes', '2026-02-12 02:29:41', 1),
(52, 7, 8, 4, 'boleh', '2026-02-12 02:30:03', 1),
(53, 3, 4, 3, 'p', '2026-02-12 05:06:39', 0),
(54, 7, 4, 8, 'cok', '2026-02-12 05:08:36', 1),
(55, 11, 4, 8, 'Halo, saya ingin bertanya tentang koleksi buku di Aksara Jiwa!', '2026-02-12 06:59:38', 0),
(56, 7, 4, 8, 'hallo', '2026-02-16 00:10:05', 1),
(57, 7, 8, 4, 'boleh', '2026-02-16 00:10:54', 1),
(58, 7, 4, 8, 'Haloo', '2026-02-16 01:19:08', 1),
(59, 7, 8, 4, 'Boleh', '2026-02-16 01:20:04', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `id_order` int(11) NOT NULL,
  `kode_pesanan` varchar(50) NOT NULL,
  `id_pembeli` int(11) NOT NULL,
  `id_penjual` int(11) NOT NULL,
  `total_harga` int(11) NOT NULL,
  `total_modal` int(11) NOT NULL,
  `total_penjualan` int(11) NOT NULL,
  `total_keuntungan` int(11) NOT NULL,
  `metode_pembayaran` enum('transfer','qris') NOT NULL,
  `bukti_tf` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','menunggu_verifikasi','approved','tolak','dikirim','diterima','refund') NOT NULL DEFAULT 'pending',
  `no_resi` varchar(100) DEFAULT NULL,
  `alamat_pembeli` text DEFAULT NULL,
  `refund_at` datetime DEFAULT NULL,
  `link_lacak` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `orders`
--

INSERT INTO `orders` (`id_order`, `kode_pesanan`, `id_pembeli`, `id_penjual`, `total_harga`, `total_modal`, `total_penjualan`, `total_keuntungan`, `metode_pembayaran`, `bukti_tf`, `created_at`, `status`, `no_resi`, `alamat_pembeli`, `refund_at`, `link_lacak`) VALUES
(1, '', 4, 0, 300000, 0, 0, 0, 'transfer', NULL, '2026-01-18 00:05:42', 'pending', NULL, NULL, NULL, NULL),
(7, 'ORD202601184304', 4, 0, 45000, 0, 0, 0, 'transfer', NULL, '2026-01-18 03:43:04', 'pending', NULL, NULL, NULL, NULL),
(8, 'ORD202601188181', 4, 0, 45000, 0, 0, 0, 'transfer', NULL, '2026-01-18 03:43:50', 'pending', NULL, NULL, NULL, NULL),
(9, 'ORD202601183417', 4, 0, 45000, 0, 0, 0, 'transfer', NULL, '2026-01-18 03:44:36', 'pending', NULL, NULL, NULL, NULL),
(10, 'ORD202601184491', 4, 0, 45000, 0, 0, 0, 'transfer', NULL, '2026-01-18 03:44:47', 'pending', NULL, NULL, NULL, NULL),
(11, 'ORD202601181504', 4, 0, 45000, 0, 0, 0, 'transfer', NULL, '2026-01-18 03:47:29', 'pending', NULL, NULL, NULL, NULL),
(12, 'ORD202601182479', 4, 0, 45000, 0, 0, 0, 'transfer', NULL, '2026-01-18 03:51:45', 'pending', NULL, NULL, NULL, NULL),
(13, 'ORD202601187361', 4, 0, 45000, 0, 0, 0, 'transfer', NULL, '2026-01-18 03:55:43', 'pending', NULL, NULL, NULL, NULL),
(15, 'ORD202601185408', 4, 0, 45000, 0, 0, 0, 'transfer', NULL, '2026-01-18 04:20:22', 'pending', NULL, NULL, NULL, NULL),
(16, 'ORD202601187243', 4, 0, 45000, 0, 0, 0, 'transfer', NULL, '2026-01-18 04:23:12', 'pending', NULL, NULL, NULL, NULL),
(17, 'ORD202601187530', 4, 0, 45000, 0, 0, 0, 'transfer', NULL, '2026-01-18 04:23:46', 'pending', NULL, NULL, NULL, NULL),
(18, 'ORD202601188450', 4, 0, 45000, 0, 0, 0, 'transfer', NULL, '2026-01-18 04:24:15', 'pending', NULL, NULL, NULL, NULL),
(19, 'ORD202601188242', 4, 0, 45000, 0, 0, 0, 'transfer', NULL, '2026-01-18 04:26:37', 'pending', NULL, NULL, NULL, NULL),
(20, 'ORD20260118113119358', 4, 0, 45000, 0, 0, 0, 'transfer', NULL, '2026-01-18 04:31:19', 'pending', NULL, NULL, NULL, NULL),
(21, 'ORD20260118114444', 4, 0, 45000, 0, 0, 0, 'transfer', NULL, '2026-01-18 04:44:44', 'dikirim', 'JNT123', NULL, NULL, NULL),
(22, 'ORD20260118114511', 4, 0, 45000, 0, 0, 0, 'transfer', 'BUKTI_1768712106.png', '2026-01-18 04:45:11', 'dikirim', 'JNT13987372', NULL, NULL, 'https://tracking-dummy.com/lacak?resi=JNT13987372'),
(23, 'ORD20260118120409', 4, 0, 300000, 0, 0, 0, 'transfer', 'BUKTI_1768712664.png', '2026-01-18 05:04:09', 'refund', NULL, NULL, '2026-01-24 13:55:06', NULL),
(24, 'ORD20260118145634', 4, 0, 300000, 0, 0, 0, 'transfer', 'BUKTI_1768723040.png', '2026-01-18 07:56:34', 'dikirim', 'AJW-20260123-24', NULL, NULL, '../auth/track.php?resi=AJW-20260123-24'),
(25, 'ORD20260118150155', 4, 0, 300000, 0, 0, 0, 'transfer', 'BUKTI_1768723364.png', '2026-01-18 08:01:55', 'menunggu_verifikasi', NULL, NULL, NULL, NULL),
(26, 'ORD20260118150331', 4, 0, 45000, 0, 0, 0, 'transfer', 'BUKTI_1769168762.png', '2026-01-18 08:03:31', 'menunggu_verifikasi', NULL, NULL, NULL, NULL),
(27, 'ORD20260118151735', 4, 0, 45000, 0, 0, 0, 'transfer', 'BUKTI_1768726806.png', '2026-01-18 08:17:35', 'dikirim', 'AJW-20260123-27', NULL, NULL, '../auth/track.php?resi=AJW-20260123-27'),
(28, 'ORD20260118152032', 4, 0, 45000, 0, 0, 0, 'transfer', 'BUKTI_1768724901.png', '2026-01-18 08:20:32', 'menunggu_verifikasi', NULL, NULL, NULL, NULL),
(29, 'ORD20260118153024', 4, 0, 45000, 0, 0, 0, 'transfer', 'BUKTI_1768725033.png', '2026-01-18 08:30:24', 'pending', NULL, NULL, NULL, NULL),
(30, 'ORD20260118153504', 4, 0, 45000, 0, 0, 0, 'transfer', 'BUKTI_1768736930.png', '2026-01-18 08:35:04', 'dikirim', 'JNT98673561287', NULL, NULL, 'https://tracking-dummy.com/lacak?resi=JNT98673561287'),
(31, 'ORD20260118153713', 4, 0, 45000, 0, 0, 0, 'transfer', 'BUKTI_1768736913.png', '2026-01-18 08:37:13', 'dikirim', 'JNT5678', NULL, NULL, NULL),
(32, 'ORD20260118153742', 4, 0, 45000, 0, 0, 0, 'transfer', 'BUKTI_1768734609.png', '2026-01-18 08:37:42', 'dikirim', 'JNT234', NULL, NULL, NULL),
(33, 'ORD20260120130525', 4, 0, 350000, 0, 0, 0, 'transfer', NULL, '2026-01-20 06:05:25', 'pending', NULL, NULL, NULL, NULL),
(34, 'ORD20260122083757', 4, 0, 50000, 0, 0, 0, 'transfer', 'BUKTI_1769045965.png', '2026-01-22 01:37:57', 'menunggu_verifikasi', NULL, NULL, NULL, NULL),
(35, 'ORD20260122111354', 4, 0, 50000, 0, 0, 0, 'transfer', 'BUKTI_1769055252.png', '2026-01-22 04:13:54', 'dikirim', 'JNT123435', NULL, NULL, NULL),
(36, 'ORD20260122111555', 4, 0, 50000, 0, 0, 0, 'transfer', 'BUKTI_1769055373.png', '2026-01-22 04:15:55', 'dikirim', 'JNT543561423', NULL, NULL, NULL),
(37, 'ORD20260123171129', 4, 0, 300000, 0, 0, 0, 'transfer', 'BUKTI_1769163464.png', '2026-01-23 10:11:29', 'dikirim', 'JNT45686465', NULL, NULL, 'https://tracking-dummy.com/lacak?resi=JNT45686465'),
(38, 'ORD20260126110109', 4, 0, 300000, 0, 0, 0, 'transfer', NULL, '2026-01-26 04:01:09', 'pending', NULL, NULL, NULL, NULL),
(39, 'ORD20260126120324', 4, 0, 365000, 0, 0, 0, 'transfer', '{\"3\":{\"file\":\"1769405680_download (5).jfif\",\"status\":\"menunggu_verifikasi\",\"uploaded_at\":\"2026-01-26 12:34:40\"},\"8\":{\"file\":\"1769405702_download (4).png\",\"status\":\"menunggu_verifikasi\",\"uploaded_at\":\"2026-01-26 12:35:02\"}}', '2026-01-26 05:03:24', '', NULL, NULL, NULL, NULL),
(40, 'ORD20260126175355', 4, 0, 365000, 0, 0, 0, 'qris', 'BUKTI_1769425263.jfif', '2026-01-26 10:53:55', 'refund', NULL, NULL, '2026-01-26 21:04:32', NULL),
(41, 'ORD20260126181537', 4, 0, 365000, 0, 0, 0, 'transfer', 'BUKTI_1769426265.jfif', '2026-01-26 11:15:37', 'refund', NULL, NULL, '2026-01-26 21:04:35', NULL),
(42, 'ORD20260126195242', 4, 0, 110000, 0, 0, 0, 'qris', '{\"3\":{\"file\":\"BUKTI_1769431974_3.jfif\",\"uploaded_at\":\"2026-01-26 19:52:54\",\"status\":\"uploaded\"},\"8\":{\"file\":\"BUKTI_1769431988_8.png\",\"uploaded_at\":\"2026-01-26 19:53:08\",\"status\":\"uploaded\"}}', '2026-01-26 12:52:42', '', 'JNTE 439478823', NULL, NULL, 'https://tracking-dummy.com/lacak?resi=JNTE 439478823'),
(43, 'ORD20260126215749', 9, 0, 110000, 0, 0, 0, 'transfer', '{\"3\":{\"file\":\"BUKTI_1769439483_3.jfif\",\"uploaded_at\":\"2026-01-26 21:58:03\",\"status\":\"uploaded\"},\"8\":{\"file\":\"BUKTI_1769439494_8.png\",\"uploaded_at\":\"2026-01-26 21:58:14\",\"status\":\"uploaded\"}}', '2026-01-26 14:57:49', '', 'jnt1234543', NULL, NULL, 'https://tracking-dummy.com/lacak?resi=jnt1234543'),
(44, 'ORD20260127091303', 9, 0, 110000, 0, 0, 0, 'qris', '{\"3\":{\"file\":\"BUKTI_1769479998_3.jfif\",\"uploaded_at\":\"2026-01-27 09:13:18\",\"status\":\"uploaded\"},\"8\":{\"file\":\"BUKTI_1769480008_8.png\",\"uploaded_at\":\"2026-01-27 09:13:28\",\"status\":\"uploaded\"}}', '2026-01-27 02:13:03', 'dikirim', 'JNT123354253', NULL, NULL, 'https://tracking-dummy.com/lacak?resi=JNT123354253'),
(45, 'ORD20260127132704', 4, 0, 110000, 0, 0, 0, 'qris', '{\"3\":{\"file\":\"BUKTI_1769495263_3.jfif\",\"uploaded_at\":\"2026-01-27 13:27:43\",\"status\":\"uploaded\"},\"8\":{\"file\":\"BUKTI_1769495273_8.png\",\"uploaded_at\":\"2026-01-27 13:27:53\",\"status\":\"uploaded\"}}', '2026-01-27 06:27:04', 'dikirim', 'JNT76342163715', NULL, NULL, 'https://tracking-dummy.com/lacak?resi=JNT76342163715'),
(46, 'ORD20260129085712', 4, 0, 110000, 0, 0, 0, 'transfer', '{\"3\":{\"file\":\"BUKTI_1769651847_3.jfif\",\"uploaded_at\":\"2026-01-29 08:57:27\",\"status\":\"uploaded\"},\"8\":{\"file\":\"BUKTI_1769651865_8.png\",\"uploaded_at\":\"2026-01-29 08:57:45\",\"status\":\"uploaded\"}}', '2026-01-29 01:57:12', 'dikirim', 'JNT13987372', NULL, NULL, 'https://tracking-dummy.com/lacak?resi=JNT13987372'),
(47, 'ORD20260208213427', 8, 0, 365000, 0, 0, 0, 'transfer', '{\"8\":{\"file\":\"BUKTI_1770561325_8.jfif\",\"uploaded_at\":\"2026-02-08 21:35:25\",\"status\":\"uploaded\"},\"3\":{\"file\":\"BUKTI_1770561334_3.jfif\",\"uploaded_at\":\"2026-02-08 21:35:34\",\"status\":\"uploaded\"}}', '2026-02-08 14:34:27', 'dikirim', 'JNT123', NULL, NULL, 'https://tracking-dummy.com/lacak?resi=JNT123'),
(48, 'ORD20260208214954', 4, 0, 365000, 0, 0, 0, 'transfer', '{\"3\":{\"file\":\"BUKTI_1770562202_3.jfif\",\"uploaded_at\":\"2026-02-08 21:50:02\",\"status\":\"uploaded\"},\"8\":{\"file\":\"BUKTI_1770562210_8.jfif\",\"uploaded_at\":\"2026-02-08 21:50:10\",\"status\":\"uploaded\"}}', '2026-02-08 14:49:54', 'dikirim', 'JNT192389', NULL, NULL, 'https://tracking-dummy.com/lacak?resi=JNT192389'),
(49, 'ORD20260209094122', 4, 0, 124000, 0, 0, 0, 'qris', '{\"11\":{\"file\":\"BUKTI_1770604901_11.jfif\",\"uploaded_at\":\"2026-02-09 09:41:41\",\"status\":\"uploaded\"},\"8\":{\"file\":\"BUKTI_1770604914_8.jfif\",\"uploaded_at\":\"2026-02-09 09:41:54\",\"status\":\"uploaded\"}}', '2026-02-09 02:41:22', 'dikirim', 'Jnttyy453421', NULL, NULL, 'https://tracking-dummy.com/lacak?resi=Jnttyy453421'),
(50, 'ORD20260210083431', 4, 0, 65000, 0, 0, 0, 'qris', '{\"8\":{\"file\":\"BUKTI_1770687283_8.jfif\",\"uploaded_at\":\"2026-02-10 08:34:43\",\"status\":\"uploaded\"}}', '2026-02-10 01:34:31', 'dikirim', 'JNT6737285378', NULL, NULL, 'https://tracking-dummy.com/lacak?resi=JNT6737285378'),
(51, 'ORD20260210111635', 3, 0, 365000, 0, 0, 0, 'qris', '{\"8\":{\"file\":\"BUKTI_1770697009_8.jfif\",\"uploaded_at\":\"2026-02-10 11:16:49\",\"status\":\"uploaded\"},\"3\":{\"file\":\"BUKTI_1770697020_3.jfif\",\"uploaded_at\":\"2026-02-10 11:17:00\",\"status\":\"uploaded\"}}', '2026-02-10 04:16:35', 'refund', NULL, NULL, '2026-02-16 14:57:44', NULL),
(52, 'ORD20260212104801', 4, 0, 65000, 0, 0, 0, 'transfer', '{\"8\":{\"file\":\"BUKTI_1770868123_8.jfif\",\"uploaded_at\":\"2026-02-12 10:48:43\",\"status\":\"uploaded\"}}', '2026-02-12 03:48:01', 'dikirim', 'JNTYT97847672', NULL, NULL, 'https://tracking-dummy.com/lacak?resi=JNTYT97847672'),
(53, 'ORD20260212111303', 4, 0, 365000, 0, 0, 0, 'transfer', '{\"3\":{\"file\":\"BUKTI_1770869596_3.jfif\",\"uploaded_at\":\"2026-02-12 11:13:16\",\"status\":\"uploaded\"},\"8\":{\"file\":\"BUKTI_1770869609_8.jfif\",\"uploaded_at\":\"2026-02-12 11:13:29\",\"status\":\"uploaded\"}}', '2026-02-12 04:13:03', 'dikirim', 'JNT5632643861', NULL, NULL, 'https://tracking-dummy.com/lacak?resi=JNT5632643861'),
(54, 'ORD20260212114736', 4, 0, 365000, 0, 0, 0, 'transfer', '{\"8\":{\"file\":\"BUKTI_1770871672_8.jfif\",\"uploaded_at\":\"2026-02-12 11:47:52\",\"status\":\"uploaded\"},\"3\":{\"file\":\"BUKTI_1770871682_3.jfif\",\"uploaded_at\":\"2026-02-12 11:48:02\",\"status\":\"uploaded\"}}', '2026-02-12 04:47:36', '', NULL, NULL, '2026-02-12 11:48:12', NULL),
(55, 'ORD20260216071552', 4, 0, 365000, 0, 0, 0, 'transfer', '{\"8\":{\"file\":\"BUKTI_1771200964_8.jfif\",\"uploaded_at\":\"2026-02-16 07:16:04\",\"status\":\"uploaded\"},\"3\":{\"file\":\"BUKTI_1771200975_3.jfif\",\"uploaded_at\":\"2026-02-16 07:16:15\",\"status\":\"uploaded\"}}', '2026-02-16 00:15:52', '', 'JNT13987372', NULL, NULL, 'https://tracking-dummy.com/lacak?resi=JNT13987372'),
(56, 'ORD20260216072354', 4, 0, 365000, 0, 0, 0, 'qris', '{\"8\":{\"file\":\"BUKTI_1771201443_8.jfif\",\"uploaded_at\":\"2026-02-16 07:24:03\",\"status\":\"uploaded\"},\"3\":{\"file\":\"BUKTI_1771201451_3.jfif\",\"uploaded_at\":\"2026-02-16 07:24:11\",\"status\":\"uploaded\"}}', '2026-02-16 00:23:54', '', NULL, NULL, NULL, NULL),
(57, 'ORD20260216072744', 4, 0, 365000, 0, 0, 0, 'qris', '{\"8\":{\"file\":\"BUKTI_1771201672_8.jfif\",\"uploaded_at\":\"2026-02-16 07:27:52\",\"status\":\"uploaded\"},\"3\":{\"file\":\"BUKTI_1771201681_3.jfif\",\"uploaded_at\":\"2026-02-16 07:28:01\",\"status\":\"uploaded\"}}', '2026-02-16 00:27:44', '', NULL, NULL, NULL, NULL),
(58, 'ORD20260216074644', 4, 0, 365000, 0, 0, 0, 'transfer', '{\"8\":{\"file\":\"BUKTI_1771202815_8.jfif\",\"uploaded_at\":\"2026-02-16 07:46:55\",\"status\":\"uploaded\"},\"3\":{\"file\":\"BUKTI_1771202824_3.jfif\",\"uploaded_at\":\"2026-02-16 07:47:04\",\"status\":\"uploaded\"}}', '2026-02-16 00:46:44', '', NULL, NULL, NULL, NULL),
(59, 'ORD20260216081746', 4, 0, 365000, 0, 0, 0, 'transfer', '{\"8\":{\"file\":\"BUKTI_1771204677_8.jfif\",\"uploaded_at\":\"2026-02-16 08:17:57\",\"status\":\"uploaded\"},\"3\":{\"file\":\"BUKTI_1771204685_3.jfif\",\"uploaded_at\":\"2026-02-16 08:18:05\",\"status\":\"uploaded\"}}', '2026-02-16 01:17:46', '', NULL, NULL, NULL, NULL),
(60, 'ORD20260216100947994', 4, 8, 65000, 30000, 0, 0, 'transfer', '{}', '2026-02-16 03:09:47', 'refund', NULL, NULL, '2026-02-16 15:03:40', NULL),
(61, 'ORD20260216100947276', 4, 3, 300000, 200000, 0, 0, 'transfer', '{}', '2026-02-16 03:09:47', '', NULL, NULL, NULL, NULL),
(62, 'ORD20260216101044633', 4, 8, 65000, 30000, 0, 0, 'transfer', '{\"8\":{\"file\":\"BUKTI_1771211484_8.jfif\",\"uploaded_at\":\"2026-02-16 10:11:24\",\"status\":\"uploaded\"}}', '2026-02-16 03:10:44', '', NULL, NULL, NULL, NULL),
(63, 'ORD20260216101044129', 4, 3, 300000, 200000, 0, 0, 'transfer', '{\"3\":{\"file\":\"BUKTI_1771211514_3.jfif\",\"uploaded_at\":\"2026-02-16 10:11:54\",\"status\":\"uploaded\"}}', '2026-02-16 03:10:44', '', NULL, NULL, NULL, NULL),
(64, 'ORD20260216104620165', 4, 0, 65000, 30000, 0, 0, 'qris', '', '2026-02-16 03:46:20', 'pending', NULL, NULL, NULL, NULL),
(65, 'ORD20260216104825663', 4, 0, 65000, 30000, 0, 0, 'qris', '{\"8\":{\"file\":\"BUKTI_1771216729_8.jfif\",\"uploaded_at\":\"2026-02-16 11:38:49\",\"status\":\"uploaded\"}}', '2026-02-16 03:48:25', 'menunggu_verifikasi', NULL, NULL, NULL, NULL),
(66, 'ORD20260216104825194', 4, 0, 300000, 200000, 0, 0, 'qris', '{\"3\":{\"file\":\"BUKTI_1771216761_3.jfif\",\"uploaded_at\":\"2026-02-16 11:39:21\",\"status\":\"uploaded\"}}', '2026-02-16 03:48:25', '', NULL, NULL, NULL, NULL),
(67, 'ORD20260216151347748', 4, 0, 65000, 30000, 0, 0, 'transfer', '{\"8\":{\"file\":\"BUKTI_1771229640_8.jfif\",\"uploaded_at\":\"2026-02-16 15:14:00\",\"status\":\"uploaded\"}}', '2026-02-16 08:13:47', 'menunggu_verifikasi', NULL, NULL, NULL, NULL),
(68, 'ORD20260216151347620', 4, 0, 300000, 200000, 0, 0, 'transfer', '{\"3\":{\"file\":\"BUKTI_1771229657_3.jfif\",\"uploaded_at\":\"2026-02-16 15:14:17\",\"status\":\"uploaded\"}}', '2026-02-16 08:13:47', 'menunggu_verifikasi', NULL, NULL, NULL, NULL),
(69, 'ORD20260216151648756', 4, 0, 65000, 30000, 0, 0, 'transfer', '{\"8\":{\"file\":\"BUKTI_1771229818_8.jfif\",\"uploaded_at\":\"2026-02-16 15:16:58\",\"status\":\"uploaded\"}}', '2026-02-16 08:16:48', 'menunggu_verifikasi', NULL, NULL, NULL, NULL),
(70, 'ORD20260216151648841', 4, 0, 300000, 200000, 0, 0, 'transfer', '{\"3\":{\"file\":\"BUKTI_1771229840_3.jfif\",\"uploaded_at\":\"2026-02-16 15:17:20\",\"status\":\"uploaded\"}}', '2026-02-16 08:16:48', 'menunggu_verifikasi', NULL, NULL, NULL, NULL),
(71, 'ORD20260216152445951', 4, 0, 65000, 30000, 0, 0, 'qris', '{\"8\":{\"file\":\"BUKTI_1771230301_8.jfif\",\"uploaded_at\":\"2026-02-16 15:25:01\",\"status\":\"uploaded\"}}', '2026-02-16 08:24:45', '', NULL, NULL, NULL, NULL),
(72, 'ORD20260216152445537', 4, 0, 300000, 200000, 0, 0, 'qris', '{\"3\":{\"file\":\"BUKTI_1771230314_3.jfif\",\"uploaded_at\":\"2026-02-16 15:25:14\",\"status\":\"uploaded\"}}', '2026-02-16 08:24:45', '', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `order_details`
--

CREATE TABLE `order_details` (
  `id_detail` int(11) NOT NULL,
  `id_order` int(11) NOT NULL,
  `id_penjual` int(11) NOT NULL,
  `id_produk` int(11) NOT NULL,
  `nama_buku` varchar(255) NOT NULL,
  `qty` int(11) NOT NULL,
  `harga` int(11) NOT NULL,
  `modal` int(11) NOT NULL,
  `subtotal_penjualan` int(11) NOT NULL,
  `subtotal_modal` int(11) NOT NULL,
  `approved` tinyint(1) DEFAULT 0,
  `status_detail` enum('pending','menunggu_verifikasi','approved','tolak','dikirim','diterima','refund') DEFAULT 'menunggu_verifikasi',
  `refund_at` datetime DEFAULT NULL,
  `ekspedisi` enum('jne','jnt','sicepat','pos','tiki','wahana','ninja','anteraja','idexpress') DEFAULT NULL,
  `no_resi` varchar(100) DEFAULT NULL,
  `link_lacak` text DEFAULT NULL,
  `shipped_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `order_details`
--

INSERT INTO `order_details` (`id_detail`, `id_order`, `id_penjual`, `id_produk`, `nama_buku`, `qty`, `harga`, `modal`, `subtotal_penjualan`, `subtotal_modal`, `approved`, `status_detail`, `refund_at`, `ekspedisi`, `no_resi`, `link_lacak`, `shipped_at`) VALUES
(1, 21, 0, 2, 'One Piece Vol. 01 – Romance Dawn', 1, 45000, 30000, 45000, 30000, 0, 'pending', NULL, NULL, NULL, NULL, NULL),
(2, 22, 0, 2, 'One Piece Vol. 01 – Romance Dawn', 1, 45000, 30000, 45000, 30000, 0, 'pending', NULL, NULL, NULL, NULL, NULL),
(3, 23, 0, 3, 'One Piece Vol. 01 – Romance Dawn', 1, 300000, 200000, 300000, 200000, 0, 'pending', NULL, NULL, NULL, NULL, NULL),
(4, 24, 0, 3, 'One Piece Vol. 01 – Romance Dawn', 1, 300000, 200000, 300000, 200000, 0, 'pending', NULL, NULL, NULL, NULL, NULL),
(5, 25, 0, 3, 'One Piece Vol. 01 – Romance Dawn', 1, 300000, 200000, 300000, 200000, 0, 'pending', NULL, NULL, NULL, NULL, NULL),
(6, 26, 0, 2, 'One Piece Vol. 01 – Romance Dawn', 1, 45000, 30000, 45000, 30000, 0, 'pending', NULL, NULL, NULL, NULL, NULL),
(7, 27, 0, 2, 'One Piece Vol. 01 – Romance Dawn', 1, 45000, 30000, 45000, 30000, 0, 'pending', NULL, NULL, NULL, NULL, NULL),
(8, 28, 0, 2, 'One Piece Vol. 01 – Romance Dawn', 1, 45000, 30000, 45000, 30000, 0, 'pending', NULL, NULL, NULL, NULL, NULL),
(9, 29, 0, 2, 'One Piece Vol. 01 – Romance Dawn', 1, 45000, 30000, 45000, 30000, 0, 'pending', NULL, NULL, NULL, NULL, NULL),
(10, 30, 0, 2, 'One Piece Vol. 01 – Romance Dawn', 1, 45000, 30000, 45000, 30000, 0, 'approved', NULL, NULL, NULL, NULL, NULL),
(11, 31, 0, 2, 'One Piece Vol. 01 – Romance Dawn', 1, 45000, 30000, 45000, 30000, 0, 'pending', NULL, NULL, NULL, NULL, NULL),
(12, 32, 0, 2, 'One Piece Vol. 01 – Romance Dawn', 1, 45000, 30000, 45000, 30000, 0, 'pending', NULL, NULL, NULL, NULL, NULL),
(13, 33, 0, 3, 'One Piece Vol. 01 – Romance Dawn', 1, 300000, 200000, 300000, 200000, 0, 'pending', NULL, NULL, NULL, NULL, NULL),
(14, 33, 0, 4, 'Laskar Pelangi', 1, 50000, 20000, 50000, 20000, 0, 'pending', NULL, NULL, NULL, NULL, NULL),
(15, 34, 0, 4, 'Laskar Pelangi', 1, 50000, 20000, 50000, 20000, 0, 'pending', NULL, NULL, NULL, NULL, NULL),
(16, 35, 0, 4, 'Laskar Pelangi', 1, 50000, 20000, 50000, 20000, 0, 'pending', NULL, NULL, NULL, NULL, NULL),
(17, 36, 0, 4, 'Laskar Pelangi', 1, 50000, 20000, 50000, 20000, 0, 'pending', NULL, NULL, NULL, NULL, NULL),
(18, 37, 0, 3, 'One Piece Vol. 01 – Romance Dawn', 1, 300000, 200000, 300000, 200000, 0, 'pending', NULL, NULL, NULL, NULL, NULL),
(19, 38, 0, 3, 'One Piece Vol. 01 – Romance Dawn', 1, 300000, 200000, 300000, 200000, 0, 'pending', NULL, NULL, NULL, NULL, NULL),
(20, 39, 0, 3, 'One Piece Vol. 01 – Romance Dawn', 1, 300000, 200000, 300000, 200000, 0, 'tolak', '2026-02-16 15:50:03', NULL, NULL, NULL, NULL),
(21, 39, 0, 7, 'Hujan di Ujung Senja', 1, 65000, 30000, 65000, 30000, 0, 'pending', NULL, NULL, NULL, NULL, NULL),
(26, 42, 0, 2, 'One Piece Vol. 01 – Romance Dawn', 1, 45000, 30000, 45000, 30000, 0, 'tolak', '2026-02-16 15:50:00', NULL, NULL, NULL, NULL),
(27, 42, 0, 7, 'Hujan di Ujung Senja', 1, 65000, 30000, 65000, 30000, 0, 'pending', NULL, NULL, NULL, NULL, NULL),
(28, 43, 0, 2, 'One Piece Vol. 01 – Romance Dawn', 1, 45000, 30000, 45000, 30000, 0, 'tolak', '2026-02-16 15:49:57', NULL, NULL, NULL, NULL),
(29, 43, 0, 7, 'Hujan di Ujung Senja', 1, 65000, 30000, 65000, 30000, 0, 'pending', NULL, NULL, NULL, NULL, NULL),
(30, 44, 0, 2, 'One Piece Vol. 01 – Romance Dawn', 1, 45000, 30000, 45000, 30000, 1, 'approved', NULL, NULL, NULL, NULL, NULL),
(31, 44, 0, 7, 'Hujan di Ujung Senja', 1, 65000, 30000, 65000, 30000, 0, 'pending', NULL, NULL, NULL, NULL, NULL),
(32, 45, 0, 2, 'One Piece Vol. 01 – Romance Dawn', 1, 45000, 30000, 45000, 30000, 0, 'approved', NULL, NULL, NULL, NULL, NULL),
(33, 45, 0, 7, 'Hujan di Ujung Senja', 1, 65000, 30000, 65000, 30000, 0, 'approved', NULL, NULL, NULL, NULL, NULL),
(34, 46, 0, 2, 'One Piece Vol. 01 – Romance Dawn', 1, 45000, 30000, 45000, 30000, 0, 'approved', NULL, NULL, NULL, NULL, NULL),
(35, 46, 0, 7, 'Hujan di Ujung Senja', 1, 65000, 30000, 65000, 30000, 0, 'approved', NULL, NULL, NULL, NULL, NULL),
(36, 47, 0, 7, 'Hujan di Ujung Senja', 1, 65000, 30000, 65000, 30000, 0, 'approved', NULL, NULL, NULL, NULL, NULL),
(37, 47, 0, 3, 'One Piece Vol. 01 – Romance Dawn', 1, 300000, 200000, 300000, 200000, 0, 'approved', NULL, NULL, NULL, NULL, NULL),
(38, 48, 0, 3, 'One Piece Vol. 01 – Romance Dawn', 1, 300000, 200000, 300000, 200000, 0, 'approved', NULL, NULL, NULL, NULL, NULL),
(39, 48, 0, 7, 'Hujan di Ujung Senja', 1, 65000, 30000, 65000, 30000, 0, 'approved', NULL, NULL, NULL, NULL, NULL),
(40, 49, 0, 9, 'Semangkok Mi ayam', 1, 59000, 30000, 59000, 30000, 0, 'approved', NULL, NULL, NULL, NULL, NULL),
(41, 49, 0, 7, 'Hujan di Ujung Senja', 1, 65000, 30000, 65000, 30000, 0, 'approved', NULL, NULL, NULL, NULL, NULL),
(42, 50, 0, 7, 'Hujan di Ujung Senja', 1, 65000, 30000, 65000, 30000, 0, 'approved', NULL, NULL, NULL, NULL, NULL),
(45, 52, 0, 7, 'Hujan di Ujung Senja', 1, 65000, 30000, 65000, 30000, 0, 'approved', NULL, NULL, NULL, NULL, NULL),
(46, 53, 0, 3, 'One Piece Vol. 01 – Romance Dawn', 1, 300000, 200000, 300000, 200000, 0, 'approved', NULL, NULL, NULL, NULL, NULL),
(47, 53, 0, 7, 'Hujan di Ujung Senja', 1, 65000, 30000, 65000, 30000, 0, 'approved', NULL, NULL, NULL, NULL, NULL),
(48, 54, 0, 7, 'Hujan di Ujung Senja', 1, 65000, 30000, 65000, 30000, 0, 'menunggu_verifikasi', NULL, NULL, NULL, NULL, NULL),
(50, 55, 0, 7, 'Hujan di Ujung Senja', 1, 65000, 30000, 65000, 30000, 0, 'dikirim', NULL, 'sicepat', '24354643232', 'https://cekresi.com/sicepat/24354643232', '2026-02-16 15:06:48'),
(51, 55, 0, 3, 'One Piece Vol. 01 – Romance Dawn', 1, 300000, 200000, 300000, 200000, 0, 'approved', NULL, NULL, NULL, NULL, NULL),
(52, 56, 0, 7, 'Hujan di Ujung Senja', 1, 65000, 30000, 65000, 30000, 0, 'approved', NULL, 'anteraja', '13243546576', 'https://cekresi.com/anteraja/13243546576', '2026-02-16 08:44:34'),
(53, 56, 0, 3, 'One Piece Vol. 01 – Romance Dawn', 1, 300000, 200000, 300000, 200000, 0, '', '2026-02-16 12:20:15', NULL, NULL, NULL, NULL),
(54, 57, 0, 7, 'Hujan di Ujung Senja', 1, 65000, 30000, 65000, 30000, 0, 'approved', NULL, 'jnt', '13862736787', 'https://cekresi.com/jnt/13862736787', '2026-02-16 08:42:18'),
(55, 57, 0, 3, 'One Piece Vol. 01 – Romance Dawn', 1, 300000, 200000, 300000, 200000, 0, 'dikirim', NULL, 'jnt', '34352342342', 'https://cekresi.com/jnt/34352342342', '2026-02-16 15:02:14'),
(56, 58, 0, 7, 'Hujan di Ujung Senja', 1, 65000, 30000, 65000, 30000, 0, 'approved', NULL, NULL, 'JNT13987372', 'https://tracking-dummy.com/lacak?resi=JNT13987372', '2026-02-16 07:48:13'),
(57, 58, 0, 3, 'One Piece Vol. 01 – Romance Dawn', 1, 300000, 200000, 300000, 200000, 0, '', '2026-02-16 12:15:44', NULL, NULL, NULL, NULL),
(58, 59, 0, 7, 'Hujan di Ujung Senja', 1, 65000, 30000, 65000, 30000, 0, 'approved', NULL, NULL, 'JNT13987372', 'https://tracking-dummy.com/lacak?resi=JNT13987372', '2026-02-16 08:20:27'),
(59, 59, 0, 3, 'One Piece Vol. 01 – Romance Dawn', 1, 300000, 200000, 300000, 200000, 0, '', '2026-02-16 12:15:37', NULL, NULL, NULL, NULL),
(61, 61, 0, 3, 'One Piece Vol. 01 – Romance Dawn', 1, 300000, 200000, 300000, 200000, 0, 'approved', NULL, 'jnt', '567895', 'https://cekresi.com/jnt/567895', '2026-02-16 12:13:07'),
(62, 62, 0, 7, 'Hujan di Ujung Senja', 1, 65000, 30000, 65000, 30000, 0, 'approved', NULL, 'sicepat', '464547476', 'https://cekresi.com/sicepat/464547476', '2026-02-16 10:13:07'),
(63, 63, 0, 3, 'One Piece Vol. 01 – Romance Dawn', 1, 300000, 200000, 300000, 200000, 0, 'approved', NULL, 'jnt', '437586457836489', 'https://cekresi.com/jnt/437586457836489', '2026-02-16 10:12:19'),
(64, 65, 8, 7, 'Hujan di Ujung Senja', 1, 65000, 30000, 65000, 30000, 0, 'pending', NULL, NULL, NULL, NULL, NULL),
(65, 66, 3, 3, 'One Piece Vol. 01 – Romance Dawn', 1, 300000, 200000, 300000, 200000, 0, 'approved', NULL, 'sicepat', '7654321234567', 'https://cekresi.com/sicepat/7654321234567', '2026-02-16 11:39:46'),
(66, 67, 8, 7, 'Hujan di Ujung Senja', 1, 65000, 30000, 65000, 30000, 0, 'pending', NULL, NULL, NULL, NULL, NULL),
(67, 68, 3, 3, 'One Piece Vol. 01 – Romance Dawn', 1, 300000, 200000, 300000, 200000, 0, 'pending', NULL, NULL, NULL, NULL, NULL),
(68, 69, 8, 7, 'Hujan di Ujung Senja', 1, 65000, 30000, 65000, 30000, 0, 'pending', NULL, NULL, NULL, NULL, NULL),
(69, 70, 3, 3, 'One Piece Vol. 01 – Romance Dawn', 1, 300000, 200000, 300000, 200000, 0, 'pending', NULL, NULL, NULL, NULL, NULL),
(70, 71, 8, 7, 'Hujan di Ujung Senja', 1, 65000, 30000, 65000, 30000, 0, 'dikirim', NULL, 'pos', '1324312132', 'https://cekresi.com/pos-indonesia/1324312132', '2026-02-16 15:50:16'),
(71, 72, 3, 3, 'One Piece Vol. 01 – Romance Dawn', 1, 300000, 200000, 300000, 200000, 0, 'dikirim', NULL, 'jnt', '13212314343', 'https://cekresi.com/jnt/13212314343', '2026-02-16 15:25:54');

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `id_produk` int(11) NOT NULL,
  `id_penjual` int(11) DEFAULT NULL,
  `nama_buku` varchar(150) DEFAULT NULL,
  `id_kategori` int(11) DEFAULT NULL,
  `gambar` varchar(255) NOT NULL,
  `stok` int(11) DEFAULT NULL,
  `harga` int(11) DEFAULT NULL,
  `modal` int(11) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`id_produk`, `id_penjual`, `nama_buku`, `id_kategori`, `gambar`, `stok`, `harga`, `modal`, `deskripsi`, `created_at`) VALUES
(2, 3, 'One Piece Vol. 012 – Romance Dawn', 7, 'buku_696afe3facea1.jfif', 0, 45000, 30000, 'One Piece Volume 01 menceritakan awal petualangan Monkey D. Luffy dalam mengejar impian menjadi Raja Bajak Laut. Komik ini penuh aksi, humor, dan persahabatan, cocok untuk remaja hingga dewasa.', '2026-01-17 02:26:38'),
(3, 3, 'One Piece Vol. 01 – Romance Dawn', 7, 'buku_696aff4dca3e3.jfif', 34, 300000, 200000, 'fhhgtedahgfjgtedsf', '2026-01-17 03:11:55'),
(4, 7, 'Laskar Pelangi', 2, 'buku_696f01c425bf9.png', 6, 50000, 20000, 'dfbdvbfnsvdhjfvshvj', '2026-01-20 04:17:08'),
(7, 8, 'Hujan di Ujung Senja', 8, 'buku_6976e9fc3ce42.jfif', 93, 65000, 30000, 'hsvdjavfhyjgfhdbffdjjkhfasg', '2026-01-26 04:13:48'),
(8, 11, 'Semangkok Mi ayam', 8, 'buku_6989473e69042.jfif', 0, 50000, 20000, 'hsbdhjbsfnb,fnjhjs', '2026-02-09 02:32:30'),
(9, 11, 'Semangkok Mi ayam', 8, 'buku_6989478c752e4.jfif', 9, 59000, 30000, 'jsfnmabnsfb', '2026-02-09 02:33:48'),
(11, 8, 'One Piece Vol. 01 – Romance Dawn', 8, 'buku_6989dddea32b8.jfif', 90, 200000, 80000, 'jhdggukhfyjfng mnghnfh', '2026-02-09 13:15:10');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `nik` char(16) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','penjual','pembeli') NOT NULL,
  `status` enum('online','offline','nonaktif') DEFAULT 'offline',
  `last_activity` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `alamat` varchar(255) NOT NULL,
  `last_photo_update` datetime DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expired` datetime DEFAULT NULL,
  `norek` varchar(50) DEFAULT NULL,
  `nama_bank` varchar(50) DEFAULT NULL,
  `qris` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id_user`, `nik`, `nama`, `email`, `image`, `password`, `role`, `status`, `last_activity`, `created_at`, `updated_at`, `alamat`, `last_photo_update`, `reset_token`, `reset_expired`, `norek`, `nama_bank`, `qris`) VALUES
(1, '1234567890123456', 'Reifan Evdnra', 'reifanevandra18@gmail.com', '../img/profile/sa_1_1770639653.jpg', '$2y$10$VyFrdYVPI1guAg8v3ZnENO4yRo6Lbv0M90KKFzsocCyRThSdhUD2m', 'super_admin', 'offline', '2026-02-16 08:12:29', '2026-01-15 03:37:38', '2026-02-16 01:14:44', 'dbsnfsdnms', '2026-02-09 19:20:53', NULL, NULL, NULL, NULL, NULL),
(2, '1654346715156317', 'Reifan Evandra', 'reifanevandra81@gmail.com', '', '$2y$10$RTKIw3Yb0ndFv2r3FdKLQO7/TmjOc2WWcbKjNpadgFaLtYl3sBUQW', 'pembeli', 'offline', '2026-01-20 07:15:15', '2026-01-15 06:21:16', '2026-02-09 01:40:25', 'dsgadfgsdgsfgh', NULL, NULL, NULL, NULL, NULL, NULL),
(3, '1234567890123457', 'Reyhan Saputra', 'marsel@gmail.com', '../uploads/profile/profile_3_1768615402.png', '$2y$10$HOnoCFB4EyIs/7g7jll4muTQyTUY65lNMXpMaRrTenPtY10ruiIdu', 'penjual', 'online', '2026-02-16 09:28:53', '2026-01-15 07:00:32', '2026-02-16 02:28:53', 'dfgfhmjbbhgcfxd', '2026-01-17 09:03:22', NULL, NULL, '1234567890123', 'Mandiri', 'qris_6976e3a84c10a.png'),
(4, '7897897897897897', 'azzamka', 'ashiap@gmail.com', '1768697260_fil.jpg', '$2y$10$ZXFp905aNWg2D79BRCx.BOQbG35aZbUSFWSyrIWMc5VfDdN64f2Ja', 'pembeli', 'online', '2026-02-16 09:26:25', '2026-01-17 01:23:56', '2026-02-16 02:26:25', 'Jalan jalan ke maluku', NULL, NULL, NULL, NULL, NULL, NULL),
(7, '0383675675666687', 'herman', 'herman@gmail.com', '', '$2y$10$uo9Hcn56z9s2VBoWc5deX.cnu3uVVI21ovgMaNJHTw6WH9sEpn7Iy', 'penjual', 'offline', '2026-01-20 11:15:12', '2026-01-20 04:14:53', '2026-01-20 04:15:12', 'sdfdghdsfgfdh', NULL, NULL, NULL, NULL, NULL, NULL),
(8, '2653672362547615', 'MANDI', 'azzamk438@gmail.com', '', '$2y$10$8Kn1b3iDKiBTCfhQVig54.Av1mZHVpNTtbIB99ExuwY.6Ty0ZbVTm', 'penjual', 'online', '2026-02-16 09:26:54', '2026-01-26 03:06:37', '2026-02-16 02:26:54', 'JL.Malaka', NULL, NULL, NULL, '987654321098', 'BRI', 'qris_6976e8d15c792.jfif'),
(9, '4354678654321223', 'Sela waktu', 'sela@gmail.com', '1769439571_file.jpg', '$2y$10$X7ugGHoFf67EaHin2ZarWu42JpZ9r6w5Y/tZs2bdrWNFDEJ8UyzP.', 'pembeli', 'online', '2026-01-26 21:57:21', '2026-01-26 14:57:07', '2026-02-09 13:02:18', 'fhjfkgnvmvklhgdjffh', NULL, NULL, NULL, NULL, NULL, NULL),
(12, '8246827381288274', 'Hilmi al-iza', 'HIlmi@gmail.com', '../img/profile/profile_12_1770641770.png', '$2y$10$d2RDgFhWtsFrfRq61qunqOQgYYREJt5SZLkOwLZLA5EQRAtkQfHqy', 'penjual', 'offline', NULL, '2026-02-09 12:25:46', '2026-02-16 00:59:28', 'jl cakung penggilingan\r\n', '2026-02-09 19:56:10', NULL, NULL, NULL, NULL, NULL),
(14, '3752247826427683', 'gagas', 'gagas@gmail.com', '', '$2y$10$g0RkOPbTw3YybaZYzOtD5.88E5k./aJ1cHhVYdclk14A7HSsfVmAm', 'pembeli', 'offline', '2026-02-10 11:21:30', '2026-02-10 04:21:16', '2026-02-16 01:13:45', 'JL ', NULL, NULL, NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indeks untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  ADD PRIMARY KEY (`id_keranjang`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indeks untuk tabel `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id_message`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id_order`),
  ADD UNIQUE KEY `kode_pesanan` (`kode_pesanan`),
  ADD KEY `id_pembeli` (`id_pembeli`);

--
-- Indeks untuk tabel `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_order` (`id_order`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`),
  ADD KEY `id_penjual` (`id_penjual`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `nik` (`nik`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  MODIFY `id_keranjang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- AUTO_INCREMENT untuk tabel `messages`
--
ALTER TABLE `messages`
  MODIFY `id_message` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `id_order` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT untuk tabel `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  ADD CONSTRAINT `keranjang_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `keranjang_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`id_pembeli`) REFERENCES `users` (`id_user`);

--
-- Ketidakleluasaan untuk tabel `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`),
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
