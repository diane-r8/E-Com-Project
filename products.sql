-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 22, 2025 at 10:19 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ecom`
--

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int NOT NULL,
  `availability` tinyint(1) NOT NULL DEFAULT '1',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `category_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock`, `availability`, `image`, `created_at`, `updated_at`, `category_id`) VALUES
(2, 'one', 'A beautiful bouquet of fresh roses.', 500.00, 10, 1, 'souvenir1', '2025-03-22 06:29:29', '2025-03-22 06:29:29', 1),
(3, 'two', 'Bright sunflowers to brighten your day.', 450.00, 8, 1, 'souvenir2', '2025-03-22 06:29:30', '2025-03-22 06:29:30', 1),
(4, 'three', 'A colorful bouquet of fresh tulips.', 600.00, 5, 1, 'souvenir3', '2025-03-22 06:29:30', '2025-03-22 06:29:30', 1),
(5, 'four', 'An elegant arrangement of white lilies.', 700.00, 7, 1, 'souvenir4', '2025-03-22 06:29:30', '2025-03-22 06:29:30', 1),
(6, 'five', 'A charming basket filled with mixed fresh flowers.', 550.00, 12, 1, 'souvenir5', '2025-03-22 06:29:30', '2025-03-22 06:29:30', 1),
(8, 'Elegant Pearl Ring', 'A stunning handmade pearl ring with a touch of elegance.', 120.00, 15, 1, 'ring-1', NULL, NULL, 7),
(9, 'Crystal Flower Ring', 'A beautiful floral design ring with embedded crystals.', 150.00, 10, 1, 'ring-2', NULL, NULL, 7),
(10, 'Gemstone Cluster Ring', 'A colorful gemstone ring featuring multiple dazzling stones.', 180.00, 8, 1, 'ring-3', NULL, NULL, 7),
(11, 'Vintage Silver Ring', 'A timeless vintage silver ring for a classic look.', 200.00, 5, 1, 'ring-4', NULL, NULL, 7),
(12, 'Handcrafted Wooden Ring', 'A unique and eco-friendly ring made from premium wood.', 130.00, 12, 1, 'ring-5', NULL, NULL, 7),
(13, 'Luxury Gem Ring', 'A luxurious handcrafted ring with premium gemstones.', 250.00, 7, 1, 'ring-6', NULL, NULL, 7),
(14, 'BINI Glow Bracelet', 'A stylish bracelet inspired by BINI’s vibrant energy, glowing with charm.', 100.00, 20, 1, 'bini-1', NULL, NULL, 2),
(15, 'BINI Ocean Pearl Bracelet', 'A handcrafted bracelet featuring pearls, representing BINI’s elegance.', 120.00, 15, 1, 'bini-2', NULL, NULL, 2),
(16, 'BINI Dream Shell Bracelet', 'Inspired by the waves of dreams, just like BINI’s journey to stardom.', 110.00, 18, 1, 'bini-3', NULL, NULL, 2),
(17, 'BINI Rainbow Beads Bracelet', 'A colorful bead bracelet symbolizing each member’s unique talent.', 130.00, 10, 1, 'bini-4', NULL, NULL, 2),
(18, 'BINI Star Charm Bracelet', 'A dainty bracelet with star charms, celebrating BINI’s rising success.', 140.00, 12, 1, 'bini-5', NULL, NULL, 2),
(19, 'BINI Handmade Rope Bracelet', 'A handcrafted rope bracelet inspired by BINI’s unity and strength.', 125.00, 15, 1, 'bini-6', NULL, NULL, 2),
(20, 'BINI Summer Vibes Bracelet', 'A fun and stylish bracelet perfect for beach days, just like BINI’s vibe.', 135.00, 10, 1, 'bini-7', NULL, NULL, 2),
(21, 'BINI Friendship Bracelet', 'A meaningful bracelet that represents BINI’s bond and connection with fans.', 150.00, 8, 1, 'bini-8', NULL, NULL, 2),
(22, 'Blue Graduation Sash', 'A classic blue graduation sash perfect for celebrating achievements.', 250.00, 10, 1, 'blue-sash', NULL, NULL, 4),
(23, 'Pink Graduation Bouquet', 'A beautiful pink-themed graduation bouquet to honor a special graduate.', 500.00, 5, 1, 'gradbo-1', NULL, NULL, 4),
(24, 'Yellow Graduation Bouquet', 'A bright yellow bouquet symbolizing success and happiness.', 500.00, 5, 1, 'gradbo-2', NULL, NULL, 4),
(25, 'Pink & Blue Graduation Bouquet', 'A lovely mixed-color bouquet with a pink and blue theme.', 550.00, 4, 1, 'gradbo-3', NULL, NULL, 4),
(26, 'Mini Graduation Bouquets Set', 'A set of mini bouquets perfect for group gifting.', 600.00, 6, 1, 'gradbo-4', NULL, NULL, 4),
(27, 'Graduation Flower Set', 'A variety of graduation-themed floral arrangements.', 750.00, 3, 1, 'gradbo-5', NULL, NULL, 4),
(28, 'Graduation Lanyard', 'A stylish and customizable lanyard for graduation day.', 180.00, 15, 1, 'lanyard', NULL, NULL, 4),
(29, 'Navy Graduation Sash', 'A deep navy blue sash representing prestige and honor.', 250.00, 10, 1, 'navy-sash', NULL, NULL, 4),
(30, 'Pink Graduation Sash', 'A bright and elegant pink sash for a standout graduate.', 250.00, 8, 1, 'pink-sash', NULL, NULL, 4),
(31, 'Red Graduation Sash', 'A bold red graduation sash for a memorable ceremony.', 250.00, 10, 1, 'red-sash', NULL, NULL, 4),
(32, 'Red Version 2 Graduation Sash', 'A stylish alternate red sash design for graduates.', 250.00, 7, 1, 'red-ver2-sash', NULL, NULL, 4),
(33, 'Assorted Graduation Sash Set', 'A collection of graduation sashes in multiple colors.', 900.00, 3, 1, 'sash', NULL, NULL, 4),
(34, 'Violet Graduation Sash', 'A royal violet sash for an elegant graduation look.', 250.00, 9, 1, 'violet-sash', NULL, NULL, 4),
(35, 'Violet Version 2 Graduation Sash', 'A deeper violet variant of the classic graduation sash.', 250.00, 6, 1, 'violet-ver2-sash', NULL, NULL, 4),
(36, 'Yellow Graduation Sash', 'A bright and cheerful yellow graduation sash.', 250.00, 8, 1, 'yellow-sash', NULL, NULL, 4),
(37, 'Yellow Version 2 Graduation Sash', 'A golden-yellow variation of the classic sash.', 250.00, 7, 1, 'yellow-ver2-sash', NULL, NULL, 4),
(38, 'Bento Bliss Cake', 'A beautifully decorated bento cake, perfect for celebrations. Comes in a cute box with a heart-shaped topper.', 350.00, 10, 1, 'bentocake', NULL, NULL, 3),
(39, 'Bentoquet Classic', 'A unique combo of a flower bouquet and a mini gift, ideal for special occasions.', 499.00, 8, 1, 'bentoquet-1', NULL, NULL, 3),
(40, 'Bentoquet Elegant', 'A delicate bentoquet featuring fresh flowers and a special surprise.', 550.00, 5, 1, 'bentoquet-2', NULL, NULL, 3),
(41, 'Exclusive Promo Deal', 'Limited-time discounts on our best-selling gifts. Don\'t miss out!', 200.00, 20, 1, 'how', NULL, NULL, 3),
(42, 'Mini Blooms Collection', 'A set of adorable mini bouquets, great for gifting in bulk.', 399.00, 15, 1, 'minis', NULL, NULL, 3),
(43, 'Mix & Match Gift Set', 'A customizable bundle where you can combine different items to create the perfect present.', 600.00, 12, 1, 'mixmatch', NULL, NULL, 3),
(44, 'Sash & Bouquet Blue Edition', 'A blue-themed sash and bouquet set, perfect for graduation or awards ceremonies.', 450.00, 10, 1, 'sash-bouquet-3', NULL, NULL, 3),
(45, 'Sash & Bouquet White & Gold Edition', 'An elegant white and gold combination for a sophisticated touch.', 480.00, 9, 1, 'sash-bouquet-4', NULL, NULL, 3),
(46, 'Sash & Bouquet Classic Gold Edition', 'A timeless gold-themed sash and bouquet for a stylish celebration.', 499.00, 7, 1, 'sash-bouquet-5', NULL, NULL, 3),
(47, 'Sash & Bouquet Pink Edition', 'A soft pink-colored set that exudes charm and elegance.', 430.00, 10, 1, 'sash-bouquet-1', NULL, NULL, 3),
(48, 'Sash & Bouquet Red Edition', 'A vibrant red-themed sash and bouquet for bold and beautiful moments.', 420.00, 10, 1, 'sash-bouquet-2', NULL, NULL, 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
