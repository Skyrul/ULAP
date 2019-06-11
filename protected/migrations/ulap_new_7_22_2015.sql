-- phpMyAdmin SQL Dump
-- version 4.3.11
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jul 21, 2015 at 11:43 PM
-- Server version: 5.6.24
-- PHP Version: 5.5.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `ulap_new`
--

-- --------------------------------------------------------

--
-- Table structure for table `ud_account`
--

CREATE TABLE IF NOT EXISTS `ud_account` (
  `id` int(11) NOT NULL,
  `email_address` varchar(128) NOT NULL,
  `username` varchar(128) NOT NULL,
  `password` varchar(128) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `account_type_id` int(11) DEFAULT NULL,
  `status` tinyint(4) NOT NULL,
  `is_deleted` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ud_account_skill`
--

CREATE TABLE IF NOT EXISTS `ud_account_skill` (
  `id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  `is_deleted` tinyint(4) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ud_account_user`
--

CREATE TABLE IF NOT EXISTS `ud_account_user` (
  `id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `first_name` varchar(80) NOT NULL,
  `last_name` varchar(80) NOT NULL,
  `salary` varchar(60) NOT NULL,
  `salary_type` varchar(60) NOT NULL,
  `date_hire` date NOT NULL,
  `date_termination` date NOT NULL,
  `language` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ud_campaign`
--

CREATE TABLE IF NOT EXISTS `ud_campaign` (
  `id` int(11) NOT NULL,
  `campaign_name` varchar(128) NOT NULL,
  `description` varchar(250) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `is_deleted` tinyint(4) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ud_campaign_skill`
--

CREATE TABLE IF NOT EXISTS `ud_campaign_skill` (
  `id` int(11) NOT NULL,
  `campaign_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  `is_deleted` tinyint(4) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ud_company`
--

CREATE TABLE IF NOT EXISTS `ud_company` (
  `id` int(11) NOT NULL,
  `company_name` varchar(250) NOT NULL,
  `description` varchar(255) NOT NULL,
  `email_address` varchar(128) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `is_deleted` tinyint(4) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ud_company`
--

INSERT INTO `ud_company` (`id`, `company_name`, `description`, `email_address`, `status`, `is_deleted`, `date_created`, `date_updated`) VALUES
(1, 'EngageX', '', '', 1, 0, '2015-07-01 00:00:00', '2015-07-16 00:00:00'),
(2, 'EngageY', '', '', 1, 0, '2015-07-16 00:00:00', '2015-07-16 00:00:00'),
(3, 'EngageZ', '', '', 1, 0, '2015-07-16 00:00:00', '2015-07-16 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `ud_contract`
--

CREATE TABLE IF NOT EXISTS `ud_contract` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `contract_name` varchar(128) NOT NULL,
  `description` varchar(250) NOT NULL,
  `billing_calculation` varchar(60) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `is_deleted` tinyint(4) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ud_contract_skill`
--

CREATE TABLE IF NOT EXISTS `ud_contract_skill` (
  `id` int(11) NOT NULL,
  `contract_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  `is_deleted` tinyint(4) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ud_customer`
--

CREATE TABLE IF NOT EXISTS `ud_customer` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `firstname` varchar(120) NOT NULL,
  `middlename` varchar(120) NOT NULL,
  `lastname` varchar(120) NOT NULL,
  `gender` varchar(6) NOT NULL,
  `phone` varchar(60) NOT NULL,
  `fax` varchar(60) NOT NULL,
  `mobile` varchar(60) NOT NULL,
  `email_address` varchar(128) NOT NULL,
  `address1` varchar(250) NOT NULL,
  `address2` varchar(250) NOT NULL,
  `city` varchar(64) NOT NULL,
  `state` int(11) DEFAULT NULL,
  `zip` varchar(12) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `is_deleted` tinyint(4) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ud_customer`
--

INSERT INTO `ud_customer` (`id`, `company_id`, `firstname`, `middlename`, `lastname`, `gender`, `phone`, `fax`, `mobile`, `email_address`, `address1`, `address2`, `city`, `state`, `zip`, `status`, `is_deleted`, `date_created`, `date_updated`) VALUES
(1, 1, 'Mark', 'Cunanan', 'Juan', 'Female', 'none', 'none', '09356902869', 'markjuan169@gmail.com', '841 Friendship Hi Way', '', 'Angeles City', 7, '2009', 1, 0, '2015-07-18 05:09:59', '2015-07-18 05:09:59');

-- --------------------------------------------------------

--
-- Table structure for table `ud_customer_office`
--

CREATE TABLE IF NOT EXISTS `ud_customer_office` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `office_name` varchar(120) NOT NULL,
  `email_address` varchar(120) NOT NULL,
  `address` varchar(250) NOT NULL,
  `phone` varchar(60) NOT NULL,
  `city` varchar(60) NOT NULL,
  `fax` varchar(60) NOT NULL,
  `state` int(11) DEFAULT NULL,
  `zip` varchar(30) NOT NULL,
  `landmark` varchar(250) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `is_deleted` tinyint(4) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ud_customer_office`
--

INSERT INTO `ud_customer_office` (`id`, `customer_id`, `office_name`, `email_address`, `address`, `phone`, `city`, `fax`, `state`, `zip`, `landmark`, `status`, `is_deleted`, `date_created`, `date_updated`) VALUES
(1, 1, 'Office 1', 'test@gmail.com', 'address123', '123123', '123123', '123123', 2, '123', '123123', 1, 0, '2015-07-18 13:28:35', '2015-07-18 13:28:35'),
(2, 1, 'Office2', 'office2@gmail.com', 'office2Address', ' 123123', 'city', 'fax', 1, '2009', 'Near the tree', 1, 0, '2015-07-18 13:28:09', '2015-07-18 13:28:09');

-- --------------------------------------------------------

--
-- Table structure for table `ud_customer_office_staff`
--

CREATE TABLE IF NOT EXISTS `ud_customer_office_staff` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `customer_office_id` int(11) NOT NULL,
  `staff_name` varchar(120) NOT NULL,
  `email_address` varchar(128) NOT NULL,
  `position` varchar(120) NOT NULL,
  `is_received_email` tinyint(4) NOT NULL,
  `is_portal_access` tinyint(4) NOT NULL,
  `phone` varchar(60) NOT NULL,
  `mobile` varchar(60) NOT NULL,
  `fax` varchar(60) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `is_deleted` tinyint(4) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ud_lookup`
--

CREATE TABLE IF NOT EXISTS `ud_lookup` (
  `id` int(11) NOT NULL,
  `lookup_type` int(11) NOT NULL,
  `slug` varchar(250) NOT NULL,
  `value` varchar(250) NOT NULL,
  `date_created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ud_skill`
--

CREATE TABLE IF NOT EXISTS `ud_skill` (
  `id` int(11) NOT NULL,
  `skill_name` varchar(128) NOT NULL,
  `description` varchar(255) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `is_deleted` tinyint(4) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ud_skill_child`
--

CREATE TABLE IF NOT EXISTS `ud_skill_child` (
  `id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  `child_name` varchar(128) NOT NULL,
  `description` varchar(250) NOT NULL,
  `is_language` tinyint(4) NOT NULL,
  `language` varchar(60) NOT NULL,
  `is_reminder_call` tinyint(4) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `is_deleted` tinyint(4) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ud_skill_disposition`
--

CREATE TABLE IF NOT EXISTS `ud_skill_disposition` (
  `id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  `skill_disposition_name` varchar(128) NOT NULL,
  `description` varchar(255) NOT NULL,
  `is_voice_contact` tinyint(4) NOT NULL,
  `retry_interval` varchar(30) NOT NULL,
  `is_complete_leads` tinyint(4) NOT NULL,
  `is_send_email` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ud_skill_disposition_email`
--

CREATE TABLE IF NOT EXISTS `ud_skill_disposition_email` (
  `id` int(11) NOT NULL,
  `skill_disposition_id` int(11) NOT NULL,
  `email_address` varchar(128) NOT NULL,
  `email_subject` varchar(250) NOT NULL,
  `email_content` text NOT NULL,
  `is_goal_disposition` tinyint(4) NOT NULL,
  `is_details` tinyint(4) NOT NULL,
  `is_callback_date` tinyint(4) NOT NULL,
  `is_callback_time` tinyint(4) NOT NULL,
  `is_note` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ud_skill_disposition_email_setting`
--

CREATE TABLE IF NOT EXISTS `ud_skill_disposition_email_setting` (
  `id` int(11) NOT NULL,
  `skill_disposition_id` int(11) NOT NULL,
  `skill_disposition_email_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `email_address` varchar(128) NOT NULL,
  `is_deleted` tinyint(4) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ud_skill_schedule`
--

CREATE TABLE IF NOT EXISTS `ud_skill_schedule` (
  `id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  `schedule_start` int(11) NOT NULL,
  `schedule_end` int(11) NOT NULL,
  `schedule_day` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `is_deleted` tinyint(4) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `ud_state`
--

CREATE TABLE IF NOT EXISTS `ud_state` (
  `id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) DEFAULT NULL,
  `abbreviation` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `sort` int(11) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `occupied` varchar(255) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `fips_state` varchar(255) DEFAULT NULL,
  `assoc_press` varchar(255) DEFAULT NULL,
  `standard_federal_region` varchar(255) DEFAULT NULL,
  `census_region` varchar(255) DEFAULT NULL,
  `census_region_name` varchar(255) DEFAULT NULL,
  `census_division` varchar(255) DEFAULT NULL,
  `census_division_name` varchar(255) DEFAULT NULL,
  `circuit_court` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `ud_state`
--

INSERT INTO `ud_state` (`id`, `name`, `abbreviation`, `country`, `type`, `sort`, `status`, `occupied`, `notes`, `fips_state`, `assoc_press`, `standard_federal_region`, `census_region`, `census_region_name`, `census_division`, `census_division_name`, `circuit_court`) VALUES
(1, 'Alabama', 'AL', 'USA', 'state', 10, 'current', 'occupied', '', '1', 'Ala.', 'IV', '3', 'South', '6', 'East South Central', '11'),
(2, 'Alaska', 'AK', 'USA', 'state', 10, 'current', 'occupied', '', '2', 'Alaska', 'X', '4', 'West', '9', 'Pacific', '9'),
(3, 'Arizona', 'AZ', 'USA', 'state', 10, 'current', 'occupied', '', '4', 'Ariz.', 'IX', '4', 'West', '8', 'Mountain', '9'),
(4, 'Arkansas', 'AR', 'USA', 'state', 10, 'current', 'occupied', '', '5', 'Ark.', 'VI', '3', 'South', '7', 'West South Central', '8'),
(5, 'California', 'CA', 'USA', 'state', 10, 'current', 'occupied', '', '6', 'Calif.', 'IX', '4', 'West', '9', 'Pacific', '9'),
(6, 'Colorado', 'CO', 'USA', 'state', 10, 'current', 'occupied', '', '8', 'Colo.', 'VIII', '4', 'West', '8', 'Mountain', '10'),
(7, 'Connecticut', 'CT', 'USA', 'state', 10, 'current', 'occupied', '', '9', 'Conn.', 'I', '1', 'Northeast', '1', 'New England', '2'),
(8, 'Delaware', 'DE', 'USA', 'state', 10, 'current', 'occupied', '', '10', 'Del.', 'III', '3', 'South', '5', 'South Atlantic', '3'),
(9, 'Florida', 'FL', 'USA', 'state', 10, 'current', 'occupied', '', '12', 'Fla.', 'IV', '3', 'South', '5', 'South Atlantic', '11'),
(10, 'Georgia', 'GA', 'USA', 'state', 10, 'current', 'occupied', '', '13', 'Ga.', 'IV', '3', 'South', '5', 'South Atlantic', '11'),
(11, 'Hawaii', 'HI', 'USA', 'state', 10, 'current', 'occupied', '', '15', 'Hawaii', 'IX', '4', 'West', '9', 'Pacific', '9'),
(12, 'Idaho', 'ID', 'USA', 'state', 10, 'current', 'occupied', '', '16', 'Idaho', 'X', '4', 'West', '8', 'Mountain', '9'),
(13, 'Illinois', 'IL', 'USA', 'state', 10, 'current', 'occupied', '', '17', 'Ill.', 'V', '2', 'Midwest', '3', 'East North Central', '7'),
(14, 'Indiana', 'IN', 'USA', 'state', 10, 'current', 'occupied', '', '18', 'Ind.', 'V', '2', 'Midwest', '3', 'East North Central', '7'),
(15, 'Iowa', 'IA', 'USA', 'state', 10, 'current', 'occupied', '', '19', 'Iowa', 'VII', '2', 'Midwest', '4', 'West North Central', '8'),
(16, 'Kansas', 'KS', 'USA', 'state', 10, 'current', 'occupied', '', '20', 'Kan.', 'VII', '2', 'Midwest', '4', 'West North Central', '10'),
(17, 'Kentucky', 'KY', 'USA', 'state', 10, 'current', 'occupied', '', '21', 'Ky.', 'IV', '3', 'South', '6', 'East South Central', '6'),
(18, 'Louisiana', 'LA', 'USA', 'state', 10, 'current', 'occupied', '', '22', 'La.', 'VI', '3', 'South', '7', 'West South Central', '5'),
(19, 'Maine', 'ME', 'USA', 'state', 10, 'current', 'occupied', '', '23', 'Maine', 'I', '1', 'Northeast', '1', 'New England', '1'),
(20, 'Maryland', 'MD', 'USA', 'state', 10, 'current', 'occupied', '', '24', 'Md.', 'III', '3', 'South', '5', 'South Atlantic', '4'),
(21, 'Massachusetts', 'MA', 'USA', 'state', 10, 'current', 'occupied', '', '25', 'Mass.', 'I', '1', 'Northeast', '1', 'New England', '1'),
(22, 'Michigan', 'MI', 'USA', 'state', 10, 'current', 'occupied', '', '26', 'Mich.', 'V', '2', 'Midwest', '3', 'East North Central', '6'),
(23, 'Minnesota', 'MN', 'USA', 'state', 10, 'current', 'occupied', '', '27', 'Minn.', 'V', '2', 'Midwest', '4', 'West North Central', '8'),
(24, 'Mississippi', 'MS', 'USA', 'state', 10, 'current', 'occupied', '', '28', 'Miss.', 'IV', '3', 'South', '6', 'East South Central', '5'),
(25, 'Missouri', 'MO', 'USA', 'state', 10, 'current', 'occupied', '', '29', 'Mo.', 'VII', '2', 'Midwest', '4', 'West North Central', '8'),
(26, 'Montana', 'MT', 'USA', 'state', 10, 'current', 'occupied', '', '30', 'Mont.', 'VIII', '4', 'West', '8', 'Mountain', '9'),
(27, 'Nebraska', 'NE', 'USA', 'state', 10, 'current', 'occupied', '', '31', 'Nebr.', 'VII', '2', 'Midwest', '4', 'West North Central', '8'),
(28, 'Nevada', 'NV', 'USA', 'state', 10, 'current', 'occupied', '', '32', 'Nev.', 'IX', '4', 'West', '8', 'Mountain', '9'),
(29, 'New Hampshire', 'NH', 'USA', 'state', 10, 'current', 'occupied', '', '33', 'N.H.', 'I', '1', 'Northeast', '1', 'New England', '1'),
(30, 'New Jersey', 'NJ', 'USA', 'state', 10, 'current', 'occupied', '', '34', 'N.J.', 'II', '1', 'Northeast', '2', 'Mid-Atlantic', '3'),
(31, 'New Mexico', 'NM', 'USA', 'state', 10, 'current', 'occupied', '', '35', 'N.M.', 'VI', '4', 'West', '8', 'Mountain', '10'),
(32, 'New York', 'NY', 'USA', 'state', 10, 'current', 'occupied', '', '36', 'N.Y.', 'II', '1', 'Northeast', '2', 'Mid-Atlantic', '2'),
(33, 'North Carolina', 'NC', 'USA', 'state', 10, 'current', 'occupied', '', '37', 'N.C.', 'IV', '3', 'South', '5', 'South Atlantic', '4'),
(34, 'North Dakota', 'ND', 'USA', 'state', 10, 'current', 'occupied', '', '38', 'N.D.', 'VIII', '2', 'Midwest', '4', 'West North Central', '8'),
(35, 'Ohio', 'OH', 'USA', 'state', 10, 'current', 'occupied', '', '39', 'Ohio', 'V', '2', 'Midwest', '3', 'East North Central', '6'),
(36, 'Oklahoma', 'OK', 'USA', 'state', 10, 'current', 'occupied', '', '40', 'Okla.', 'VI', '3', 'South', '7', 'West South Central', '10'),
(37, 'Oregon', 'OR', 'USA', 'state', 10, 'current', 'occupied', '', '41', 'Ore.', 'X', '4', 'West', '9', 'Pacific', '9'),
(38, 'Pennsylvania', 'PA', 'USA', 'state', 10, 'current', 'occupied', '', '42', 'Pa.', 'III', '1', 'Northeast', '2', 'Mid-Atlantic', '3'),
(39, 'Rhode Island', 'RI', 'USA', 'state', 10, 'current', 'occupied', '', '44', 'R.I.', 'I', '1', 'Northeast', '1', 'New England', '1'),
(40, 'South Carolina', 'SC', 'USA', 'state', 10, 'current', 'occupied', '', '45', 'S.C.', 'IV', '3', 'South', '5', 'South Atlantic', '4'),
(41, 'South Dakota', 'SD', 'USA', 'state', 10, 'current', 'occupied', '', '46', 'S.D.', 'VIII', '2', 'Midwest', '4', 'West North Central', '8'),
(42, 'Tennessee', 'TN', 'USA', 'state', 10, 'current', 'occupied', '', '47', 'Tenn.', 'IV', '3', 'South', '6', 'East South Central', '6'),
(43, 'Texas', 'TX', 'USA', 'state', 10, 'current', 'occupied', '', '48', 'Texas', 'VI', '3', 'South', '7', 'West South Central', '5'),
(44, 'Utah', 'UT', 'USA', 'state', 10, 'current', 'occupied', '', '49', 'Utah', 'VIII', '4', 'West', '8', 'Mountain', '10'),
(45, 'Vermont', 'VT', 'USA', 'state', 10, 'current', 'occupied', '', '50', 'Vt.', 'I', '1', 'Northeast', '1', 'New England', '2'),
(46, 'Virginia', 'VA', 'USA', 'state', 10, 'current', 'occupied', '', '51', 'Va.', 'III', '3', 'South', '5', 'South Atlantic', '4'),
(47, 'Washington', 'WA', 'USA', 'state', 10, 'current', 'occupied', '', '53', 'Wash.', 'X', '4', 'West', '9', 'Pacific', '9'),
(48, 'West Virginia', 'WV', 'USA', 'state', 10, 'current', 'occupied', '', '54', 'W.Va.', 'III', '3', 'South', '5', 'South Atlantic', '4'),
(49, 'Wisconsin', 'WI', 'USA', 'state', 10, 'current', 'occupied', '', '55', 'Wis.', 'V', '2', 'Midwest', '3', 'East North Central', '7'),
(50, 'Wyoming', 'WY', 'USA', 'state', 10, 'current', 'occupied', '', '56', 'Wyo.', 'VIII', '4', 'West', '8', 'Mountain', '10');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ud_account`
--
ALTER TABLE `ud_account`
  ADD PRIMARY KEY (`id`), ADD KEY `username` (`username`), ADD KEY `account_type_id` (`account_type_id`);

--
-- Indexes for table `ud_account_skill`
--
ALTER TABLE `ud_account_skill`
  ADD PRIMARY KEY (`id`), ADD KEY `skill_id` (`skill_id`);

--
-- Indexes for table `ud_account_user`
--
ALTER TABLE `ud_account_user`
  ADD PRIMARY KEY (`id`), ADD KEY `account_id` (`account_id`);

--
-- Indexes for table `ud_campaign`
--
ALTER TABLE `ud_campaign`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ud_campaign_skill`
--
ALTER TABLE `ud_campaign_skill`
  ADD PRIMARY KEY (`id`), ADD KEY `campaign_id` (`campaign_id`), ADD KEY `skill_id` (`skill_id`);

--
-- Indexes for table `ud_company`
--
ALTER TABLE `ud_company`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ud_contract`
--
ALTER TABLE `ud_contract`
  ADD PRIMARY KEY (`id`), ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `ud_contract_skill`
--
ALTER TABLE `ud_contract_skill`
  ADD PRIMARY KEY (`id`), ADD KEY `contract_id` (`contract_id`), ADD KEY `skill_id` (`skill_id`);

--
-- Indexes for table `ud_customer`
--
ALTER TABLE `ud_customer`
  ADD PRIMARY KEY (`id`), ADD KEY `email_address` (`email_address`), ADD KEY `state` (`state`), ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `ud_customer_office`
--
ALTER TABLE `ud_customer_office`
  ADD PRIMARY KEY (`id`), ADD KEY `customer_id` (`customer_id`), ADD KEY `state` (`state`);

--
-- Indexes for table `ud_customer_office_staff`
--
ALTER TABLE `ud_customer_office_staff`
  ADD PRIMARY KEY (`id`), ADD KEY `customer_to_customer_office` (`customer_id`,`customer_office_id`), ADD KEY `customer_office_id` (`customer_office_id`), ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `ud_lookup`
--
ALTER TABLE `ud_lookup`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ud_skill`
--
ALTER TABLE `ud_skill`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ud_skill_child`
--
ALTER TABLE `ud_skill_child`
  ADD PRIMARY KEY (`id`), ADD KEY `skill_id` (`skill_id`);

--
-- Indexes for table `ud_skill_disposition`
--
ALTER TABLE `ud_skill_disposition`
  ADD PRIMARY KEY (`id`), ADD KEY `skill_id` (`skill_id`);

--
-- Indexes for table `ud_skill_disposition_email`
--
ALTER TABLE `ud_skill_disposition_email`
  ADD PRIMARY KEY (`id`), ADD KEY `skill_disposition_id` (`skill_disposition_id`);

--
-- Indexes for table `ud_skill_disposition_email_setting`
--
ALTER TABLE `ud_skill_disposition_email_setting`
  ADD PRIMARY KEY (`id`), ADD KEY `skill_disposition_id` (`skill_disposition_id`), ADD KEY `skill_disposition_email_id` (`skill_disposition_email_id`);

--
-- Indexes for table `ud_skill_schedule`
--
ALTER TABLE `ud_skill_schedule`
  ADD PRIMARY KEY (`id`), ADD KEY `skill_id` (`skill_id`);

--
-- Indexes for table `ud_state`
--
ALTER TABLE `ud_state`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ud_account`
--
ALTER TABLE `ud_account`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ud_account_skill`
--
ALTER TABLE `ud_account_skill`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ud_account_user`
--
ALTER TABLE `ud_account_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ud_campaign`
--
ALTER TABLE `ud_campaign`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ud_campaign_skill`
--
ALTER TABLE `ud_campaign_skill`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ud_company`
--
ALTER TABLE `ud_company`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `ud_contract`
--
ALTER TABLE `ud_contract`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ud_contract_skill`
--
ALTER TABLE `ud_contract_skill`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ud_customer`
--
ALTER TABLE `ud_customer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `ud_customer_office`
--
ALTER TABLE `ud_customer_office`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `ud_customer_office_staff`
--
ALTER TABLE `ud_customer_office_staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ud_lookup`
--
ALTER TABLE `ud_lookup`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ud_skill`
--
ALTER TABLE `ud_skill`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ud_skill_child`
--
ALTER TABLE `ud_skill_child`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ud_skill_disposition`
--
ALTER TABLE `ud_skill_disposition`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ud_skill_disposition_email`
--
ALTER TABLE `ud_skill_disposition_email`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ud_skill_disposition_email_setting`
--
ALTER TABLE `ud_skill_disposition_email_setting`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ud_skill_schedule`
--
ALTER TABLE `ud_skill_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `ud_customer`
--
ALTER TABLE `ud_customer`
ADD CONSTRAINT `ud_customer_ibfk_1` FOREIGN KEY (`state`) REFERENCES `ud_state` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `ud_customer_office`
--
ALTER TABLE `ud_customer_office`
ADD CONSTRAINT `ud_customer_office_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `ud_customer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `ud_customer_office_ibfk_2` FOREIGN KEY (`state`) REFERENCES `ud_state` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `ud_customer_office_staff`
--
ALTER TABLE `ud_customer_office_staff`
ADD CONSTRAINT `ud_customer_office_staff_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `ud_customer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `ud_customer_office_staff_ibfk_2` FOREIGN KEY (`customer_office_id`) REFERENCES `ud_customer_office` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
