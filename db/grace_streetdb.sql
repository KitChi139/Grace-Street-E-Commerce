-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 24, 2024 at 09:31 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `grace_streetdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `ID` int(11) NOT NULL,
  `Product_Name` varchar(255) DEFAULT NULL,
  `Product_Price` decimal(10,2) DEFAULT NULL,
  `Product_Quantity` int(11) DEFAULT NULL,
  `Product_Image` blob DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`ID`, `Product_Name`, `Product_Price`, `Product_Quantity`, `Product_Image`, `user_id`, `user_email`) VALUES
(115, 'francis', 600.00, 1, 0x766972676f2e706e67, 20, 'matthew@gmail.com'),
(143, 'matthew', 560.00, 1, 0x6d6f64656c2e706e67, 15, 'ron@gmail.com'),
(144, 'Brave Heart', 500.00, 1, 0x425241564548454152542e6a7067, 15, 'ron@gmail.com'),
(145, 'sample1', 816.00, 2, 0x686f6d652e706e67, 15, 'ron@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE `contact` (
  `id` int(11) NOT NULL,
  `name` varchar(20) NOT NULL,
  `email` varchar(50) NOT NULL,
  `number` bigint(20) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grace_user`
--

CREATE TABLE `grace_user` (
  `id` int(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `role` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grace_user`
--

INSERT INTO `grace_user` (`id`, `username`, `email`, `password`, `role`) VALUES
(15, 'ron', 'ron@gmail.com', 'c1ecdc5b69acd9e42bf97f806206bdc769c42ee4', ''),
(19, 'admins', 'admins@gmail.com', 'c6c92702a84b81ae490d98d6ec71b92d1802a190', 'admin'),
(26, 'jam', 'jam@gmail.com', '534fc104463b0867a8e4a4c33ab06b6416d54aee', ''),
(27, 'Employee', 'employee@gmail.com', 'caf322f0bbed721eac4a36bf7aff1103079faf25', 'employee');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `from_id` int(11) NOT NULL,
  `to_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `ID` int(11) NOT NULL,
  `Name` varchar(255) DEFAULT NULL,
  `Number` varchar(20) DEFAULT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `Method` varchar(50) DEFAULT NULL,
  `Address` text DEFAULT NULL,
  `Total_Products` varchar(1000) DEFAULT NULL,
  `Total_Price` decimal(10,2) DEFAULT NULL,
  `Placed_on` date DEFAULT NULL,
  `Order_Status` varchar(255) DEFAULT NULL,
  `order_email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`ID`, `Name`, `Number`, `Email`, `Method`, `Address`, `Total_Products`, `Total_Price`, `Placed_on`, `Order_Status`, `order_email`) VALUES
(50, 'jemicah jairus Ventura quinto', '0999039293', 'jairusquinto566@gmail.com', 'cash_on_delivery', 'kaymito', '<span style=\"display: block; margin-bottom: 10px;\">- Thony PHP31.00(1)</span>', 31.00, '2024-05-24', '1', 'ron@gmail.com'),
(51, 'jemicah jairus Ventura quinto', '09992029392', 'jairusquinto566@gmail.com', 'cash_on_delivery', 'kaymito', '<span style=\"display: block; margin-bottom: 10px;\">- sample1 PHP408.00(1)</span><span style=\"display: block; margin-bottom: 10px;\">- matthew PHP560.00(1)</span><span style=\"display: block; margin-bottom: 10px;\">- Brave Heart PHP500.00(1)</span><span style=\"display: block; margin-bottom: 10px;\">- Chosen Generation PHP500.00(1)</span><span style=\"display: block; margin-bottom: 10px;\">- sample2 PHP590.00(1)</span>', 2558.00, '2024-05-24', '0', 'ron@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `product_list`
--

CREATE TABLE `product_list` (
  `id` int(100) NOT NULL,
  `product_image` varchar(100) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `product_stock_s` int(100) NOT NULL,
  `product_stock_m` int(100) NOT NULL,
  `product_stock_l` int(100) NOT NULL,
  `product_stock_xl` int(100) NOT NULL,
  `product_stock_xxl` int(100) NOT NULL,
  `product_price` varchar(100) NOT NULL,
  `product_discount` int(100) NOT NULL,
  `product_status` varchar(11) NOT NULL,
  `date` date DEFAULT NULL,
  `gender` varchar(20) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_list`
--

INSERT INTO `product_list` (`id`, `product_image`, `product_name`, `product_stock_s`, `product_stock_m`, `product_stock_l`, `product_stock_xl`, `product_stock_xxl`, `product_price`, `product_discount`, `product_status`, `date`, `gender`, `description`) VALUES
(63, 'WAYTRUTHLIFE.jpg', 'Way Truth Lifeee', 10, 5, 2, 0, 0, '500', 0, 'Available', '2024-05-08', 'Mens', ''),
(64, 'SPOILERALERT.jpg', 'Spoiler Alert', 10, 0, 0, 2, 0, '500', 0, 'Available', '2024-05-08', 'Mens', ''),
(66, 'SETAAPART.jpg', 'Set Apart', 10, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', 'Mens', ''),
(67, 'SELAH.jpg', 'Selah', 10, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', 'Mens', ''),
(68, 'SALTYANDLIT.jpg', 'Salty And Lit', 10, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', 'Womens', ''),
(69, 'PRAY247.jpg', 'Pray 24/7', 10, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', 'Womens', ''),
(70, 'PRAISEONREPEAT.jpg', 'Praise on Repeat', 10, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', 'Mens', ''),
(71, 'ph-11134207-7r98x-lr2wtdahnnlw06_tn.jpg', 'Light of the world', 50, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', 'Mens', ''),
(72, 'ph-11134207-7r98v-lr2wtdahf8781b.jpg', 'Jesus is King', 10, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', 'Womens', ''),
(73, 'ph-11134207-7r98u-lr2wtdahqgqs9c_tn.jpg', 'SteadFast', 10, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', 'Womens', ''),
(74, 'ph-11134207-7r98t-lr2wtdbbb77o82_tn.jpg', 'I am Child of God', 10, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', 'Womens', ''),
(75, 'ph-11134207-7r98r-lr2l0oww18tx42_tn.jpg', 'No Longer Slave', 10, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', 'Womens', ''),
(76, 'ph-11134207-7r98q-lr2wtdbbcls4e5_tn.jpg', 'His love is Phenomenal', 10, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', 'Womens', ''),
(77, 'ph-11134207-7r98q-lr2wtdahjfwk38_tn.jpg', 'Be The Light', 10, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', 'Mens', ''),
(78, 'ph-11134207-7r98p-lr2wtdahgmrof6_tn.jpg', 'Living for the King', 10, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', 'Mens', ''),
(79, 'NOLONGERSLAVE.jpg', 'No longer Slave', 10, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', 'Mens', ''),
(80, 'MADENEW.jpg', 'Made New', 10, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', 'Mens', ''),
(81, 'JESUSJESUS.jpg', 'Jesus Jesus', 10, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', 'Mens', ''),
(82, 'IMNOTASHAMED.jpg', 'Not Ashamed', 10, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', 'Mens', ''),
(83, 'HISMERCYFLOWS.jpg', 'His Mercy Flows', 10, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', 'Womens', ''),
(84, 'HEISABLE.jpg', 'He is Able', 10, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', 'Mens', ''),
(85, 'GODFIDENCE.jpg', 'Gofidence', 10, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', 'Mens', ''),
(86, 'GOAGAINSTTHECURRENT.jpg', 'Against the Current', 10, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', 'Mens', ''),
(87, 'GGVV.jpg', 'GGVV', 10, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', 'Womens', ''),
(88, 'FEARNOT.jpg', 'Fear Not', 10, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', 'Womens', ''),
(89, 'FEARHASNOPLACE.jpg', 'Hear has no place', 10, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', '', ''),
(90, 'FAITHOVERFEAR.jpg', 'Faith over Fear', 10, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', '', ''),
(91, 'FAITHITTILYOUMAKEIT.jpg', 'Faith it - Make it', 10, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', '', ''),
(92, 'ELEVATEYOURFAITH.jpg', 'Elevate Faith', 10, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', '', ''),
(93, 'DRUMMERFORTHELORD.jpg', 'Drummer for the lord', 10, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', '', ''),
(94, 'CREATEDWITHAPURPOSE.jpg', 'Created with Purpose', 10, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', '', ''),
(95, 'CREATEDTOWORSHIP.jpg', 'Created to worship', 10, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', '', ''),
(96, 'COUNTITALLJOY.jpg', 'Count it All joy', 10, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', '', ''),
(97, 'CITIZENOFHEAVEN.jpg', 'Citizen of Heaven', 10, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', '', ''),
(98, 'CHOSENNOTFORSAKEN.jpg', 'Chosen not Forsaken', 10, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', '', ''),
(99, 'CHOSENGENERATION.jpg', 'Chosen Generation', 9, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', '', ''),
(100, 'CHOOSELIFE.jpg', 'Choose Life', 10, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', '', ''),
(101, 'BRAVEHEART.jpg', 'Brave Heart', 9, 0, 0, 0, 0, '500', 0, 'Available', '2024-05-08', '', ''),
(102, 'model.png', 'matthew', 22, 22, 3, 4, 2, '800', 30, 'Available', '2024-05-24', 'Womens', 'quality'),
(103, 'home.png', 'sample1', 20, 32, 32, 23, 23, '680', 40, 'Available', '2024-05-24', 'Mens', 'quality'),
(104, 'leo.png', 'sample2', 32, 2, 23, 21, 2, '590', 0, 'Available', '2024-05-24', 'Mens', 'dawdawd');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `ID` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_email` varchar(255) DEFAULT NULL,
  `Wishlist_Image` blob DEFAULT NULL,
  `Wishlist_Name` varchar(255) DEFAULT NULL,
  `Wishlist_Price` decimal(10,2) DEFAULT NULL,
  `Wishlist_Quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`ID`, `user_id`, `user_email`, `Wishlist_Image`, `Wishlist_Name`, `Wishlist_Price`, `Wishlist_Quantity`) VALUES
(31, 15, 'ron@gmail.com', 0x6c656f2e706e67, 'sample2', 590.00, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `grace_user`
--
ALTER TABLE `grace_user`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `product_list`
--
ALTER TABLE `product_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=146;

--
-- AUTO_INCREMENT for table `contact`
--
ALTER TABLE `contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `grace_user`
--
ALTER TABLE `grace_user`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `product_list`
--
ALTER TABLE `product_list`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
