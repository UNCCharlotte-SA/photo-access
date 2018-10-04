-- phpMyAdmin SQL Dump
-- version 4.1.12
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jun 26, 2014 at 04:56 PM
-- Server version: 5.6.16
-- PHP Version: 5.5.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `photo_access`
--

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE IF NOT EXISTS `countries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `short_name` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `long_form_name` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `abbreviations` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `capital` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `created_user_id` int(11) NOT NULL,
  `created_date` datetime NOT NULL,
  `updated_user_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `short_name` (`short_name`),
  UNIQUE KEY `long_form_name` (`long_form_name`),
  UNIQUE KEY `abbreviations` (`abbreviations`),
  KEY `created_by` (`created_user_id`),
  KEY `updated_by` (`updated_user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=197 ;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `short_name`, `long_form_name`, `abbreviations`, `capital`, `created_user_id`, `created_date`, `updated_user_id`, `updated_date`) VALUES
(1, 'Afghanistan', 'Islamic Republic of Afghanistan', 'AF', 'Kabul', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(2, 'Albania', 'Republic of Albania', 'AL', 'Tirana', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(3, 'Algeria', 'People''s Democratic Republic of Algeria', 'AG', 'Algiers', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(4, 'Andorra', 'Principality of Andorra', 'AN', 'Andorra la Vella', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(5, 'Angola', 'Republic of Angola', 'AO', 'Luanda', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(6, 'Antiqua and Barbuda', 'Antiqua and Barbuda', 'AC', 'Saint John''s', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(7, 'Argentina', 'Argentine Republic', 'AR', 'Buenos Aires', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(8, 'Armenia', 'Republic of Armenia', 'AM', 'Yerevan', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(9, 'Australia', 'Commonwealth of Australia', 'AS', 'Canberra', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(10, 'Austria', 'Republic of Austria', 'AU', 'Vienna', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(11, 'Azerbaijan', 'Republic of Azerbaijan', 'AJ', 'Baku', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(12, 'Bahamas', 'Commonwealth of The Bahamas', 'BF', 'Nassau', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(13, 'Bahrain', 'Kingdom of Bahrain', 'BA', 'Manama', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(14, 'Bangladesh', 'People''s Republic of Bangladesh', 'BG', 'Dhaka', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(15, 'Barbados', 'Barbados', 'BB', 'Bridgetown', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(16, 'Belarus', 'Republic of Belarus', 'BO', 'Minsk', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(17, 'Belgium', 'Kingdom of Belgium', 'BE', 'Brussels', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(18, 'Belize', 'Belize', 'BH', 'Belmopan', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(19, 'Benin', 'Republic of Benin', 'BN', 'Porto-Novo', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(20, 'Bhutan', 'Kingdom of Bhutan', 'BT', 'Thimphu', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(21, 'Bolivia', 'Plurinational State of Bolivia', 'BL', 'La Paz', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(22, 'Bosnia and Herzegovina', 'Bosnia and Herzegovina', 'BK', 'Sarajevo', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(23, 'Botswana', 'Republic of Botswana', 'BC', 'Gaborone', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(24, 'Brazil', 'Federative Republic of Brazil', 'BR', 'Brasília', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(25, 'Brunei', 'Brunei Darussalam', 'BX', 'Bandar Seri Begawan', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(26, 'Bulgaria', 'Republic of Bulgaria', 'BU', 'Sofia', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(27, 'Burkina Faso', 'Burkina Faso', 'UV', 'Ouagadougou', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(28, 'Burma', 'Union of Burma', 'BM', 'Rangoon', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(29, 'Burundi', 'Republic of Burundi', 'BY', 'Bujumbura', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(30, 'Cabo Verde', 'Republic of Cabo Verde', 'CV', 'Praia', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(31, 'Cambodia', 'Kingdom of Cambodia', 'CB', 'Phnom Penh', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(32, 'Cameroon', 'Republic of Cameroon', 'CM', 'Yaoundé', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(33, 'Canada', 'Canada', 'CA', 'Ottawa', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(34, 'Central African Republic', 'Central African Republic', 'CT', 'Bangui', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(35, 'Chad', 'Republic of Chad', 'CD', 'N''Djamena', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(36, 'Chile', 'Republic of Chile', 'CI', 'Santiago', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(37, 'China', 'People''s Republic of China', 'CH', 'Beijing', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(38, 'Colombia', 'Republic of Colombia', 'CO', 'Bogotá', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(39, 'Comoros', 'Union of the Comoros', 'CN', 'Moroni', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(40, 'Congo (Brazzaville)', 'Republic of the Congo', 'CF', 'Brazzaville', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(41, 'Congo (Kinshasa)', 'Democratic Republic of the Congo', 'CG', 'Kinshasa', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(42, 'Costa Rica', 'Republic of Costa Rica', 'CS', 'San José', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(43, 'Côte d''Ivoire', 'Republic of Côte d''Ivoire', 'IV', 'Yamoussoukro', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(44, 'Croatia', 'Republic of Croatia', 'HR', 'Zagreb', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(45, 'Cuba', 'Republic of Cuba', 'CU', 'Havana', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(46, 'Cyprus', 'Republic of Cyprus', 'CY', 'Nicosia', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(47, 'Czech Republic', 'Czech Republic', 'EZ', 'Prague', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(48, 'Denmark', 'Kingdom of Denmark', 'DA', 'Copenhagen', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(49, 'Djibouti', 'Republic of Djibouti', 'DJ', 'Djibouti', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(50, 'Dominica', 'Commonwealth of Dominica', 'DO', 'Roseau', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(51, 'Dominican Republic', 'Dominican Republic', 'DR', 'Santo Domingo', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(52, 'Ecuador', 'Republic of Ecuador', 'EC', 'Quito', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(53, 'Egypt', 'Arab Republic of Egypt', 'EG', 'Cairo', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(54, 'El Salvado', 'Republic of El Salvador', 'ES', 'San Salvador', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(55, 'Equatorial Guinea', 'Republic of Equatorial Guinea', 'EK', 'Malabo', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(56, 'Eritrea', 'State of Eritrea', 'ER', 'Asmara', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(57, 'Estonia', 'Republic of Estonia', 'EN', 'Tallinn', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(58, 'Ethiopia', 'Federal Democratic Republic of Ethiopia', 'ET', 'Addis Ababa', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(59, 'Fiji', 'Republic of Fiji', 'FJ', 'Suva', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(60, 'Finland', 'Republic of Finland', 'FI', 'Helsinki', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(61, 'France', 'French Republic', 'FR', 'Paris', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(62, 'Gabon', 'Gabonese Republic', 'GB', 'Libreville', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(63, 'Gambia', 'Republic of The Gambia', 'GA', 'Banjul', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(64, 'Georgia', 'Georgia', 'GG', 'Tbilisi', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(65, 'Germany', 'Federal Republic of Germany', 'GM', 'Berlin', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(66, 'Ghana', 'Republic of Ghana', 'GH', 'Accra', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(67, 'Greece', 'Hellenic Republic', 'GR', 'Athens', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(68, 'Grenada', 'Grenada', 'GJ', 'Saint George''s', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(69, 'Guatemala', 'Republic of Guatemala', 'GT', 'Guatemala', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(70, 'Guinea', 'Republic of Guinea', 'GV', 'Conakry', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(71, 'Guinea-Bissau', 'Republic of Guinea-Bissau', 'PU', 'Bissau', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(72, 'Guyana', 'Co-operative Republic of Guyana', 'GY', 'Georgetown', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(73, 'Haiti', 'Republic of Haiti', 'HA', 'Port-au-Prince', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(74, 'Holy See', 'Holy See', 'VT', 'Vatican City', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(75, 'Honduras', 'Republic of Honduras', 'HO', 'Tegucigalpa', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(76, 'Hungary', 'Hungary', 'HU', 'Budapest', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(77, 'Iceland', 'Republic of Iceland', 'IC', 'Reykjavík', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(78, 'India', 'Republic of India', 'IN', 'New Delhi', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(79, 'Indonesia', 'Republic of Indonesia', 'ID', 'Jakarta', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(80, 'Iran', 'Islamic Republic of Iran', 'IR', 'Tehran', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(81, 'Iraq', 'Republic of Iraq', 'IZ', 'Baghdad', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(82, 'Ireland', 'Ireland', 'EI', 'Dublin', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(83, 'Israel', 'State of Israel', 'IS', 'Jerusalem', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(84, 'Italy', 'Italian Republic', 'IT', 'Rome', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(85, 'Jamaica', 'Jamaica', 'JM', 'Kingston', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(86, 'Japan', 'Japan', 'JA', 'Tokyo', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(87, 'Jordan', 'Hashemite Kingdom of Jordan', 'JO', 'Amman', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(88, 'Kazakhstan', 'Republic of Kazakhstan', 'KZ', 'Astana', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(89, 'Kenya', 'Republic of Kenya', 'KE', 'Nairobi', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(90, 'Kiribati', 'Republic of Kiribati', 'KR', 'Tarawa', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(91, 'Kosovo', 'Republic of Kosovo', 'KV', 'Pristina', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(92, 'Kuwait', 'State of Kuwait', 'KU', 'Kuwait', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(93, 'Kyrgyzstan', 'Kyrgyz Republic', 'KG', 'Bishkek', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(94, 'Laos', 'Lao People''s Democratic Republic', 'LA', 'Vientiane', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(95, 'Latvia', 'Republic of Latvia', 'LG', 'Riga', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(96, 'Lebanon', 'Lebanese Republic', 'LE', 'Beirut', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(97, 'Lesotho', 'Kingdom of Lesotho', 'LT', 'Maseru', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(98, 'Liberia', 'Republic of Liberia', 'LI', 'Monrovia', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(99, 'Libya', 'Libya', 'LY', 'Tripoli', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(100, 'Liechtenstein', 'Principality of Liechtenstein', 'LS', 'Vaduz', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(101, 'Lithuania', 'Republic of Lithuania', 'LH', 'Vilnius', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(102, 'Luxembourg', 'Grand Duchy of Luxembourg', 'LU', 'Luxembourg', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(103, 'Macedonia', 'Republic of Macedonia', 'MK', 'Skopje', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(104, 'Madagascar', 'Republic of Madagascar', 'MA', 'Antananarivo', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(105, 'Malawi', 'Republic of Malawi', 'MI', 'Lilongwe', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(106, 'Malaysia', 'Malaysia', 'MY', 'Kuala Lumpur', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(107, 'Maldives', 'Republic of Maldives', 'MV', 'Male', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(108, 'Mali', 'Republic of Mali', 'ML', 'Bamako', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(109, 'Malta', 'Republic of Malta', 'MT', 'Valletta', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(110, 'Marshall  Islands', 'Republic of the Marshall Islands', 'RM', 'Majuro', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(111, 'Mauritania', 'Islamic Republic of Mauritania', 'MR', 'Nouakchott', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(112, 'Mauritius', 'Republic of Mauritius', 'MP', 'Port Louis', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(113, 'Mexico', 'United Mexican States', 'MX', 'Mexico', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(114, 'Micronesia', 'Federated States of Micronesia', 'FM', 'Palikir', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(115, 'Moldova', 'Republic of Moldova', 'MD', 'Chisinau', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(116, 'Monaco', 'Principality of Monaco', 'MN', 'Monaco', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(117, 'Mongolia', 'Mongolia', 'MG', 'Ulaanbaatar', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(118, 'Montenegro', 'Montenegro', 'MJ', 'Podgorica', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(119, 'Morocco', 'Kingdom of Morocco', 'MO', 'Rabat', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(120, 'Mozambique', 'Republic of Mozambique', 'MZ', 'Maputo', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(121, 'Namibia', 'Republic of Namibia', 'WA', 'Windhoek', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(122, 'Nauru', 'Republic of Nauru', 'NR', 'Yaren District', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(123, 'Nepal', 'Federal Democratic Republic of Nepal', 'NP', 'Kathmandu', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(124, 'Netherlands', 'Kingdom of the Netherlands', 'NL', 'Amsterdam', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(125, 'New Zealand', 'New Zealand', 'NZ', 'Wellington', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(126, 'Nicaragua', 'Republic of Nicaragua', 'NU', 'Managua', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(127, 'Niger', 'Republic of Niger', 'NG', 'Niamey', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(128, 'Nigeria', 'Federal Republic of Nigeria', 'NI', 'Abuja', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(129, 'North Korea', 'Democratic People''s Republic of Korea', 'KN', 'Pyongyang', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(130, 'Norway', 'Kingdom of Norway', 'NO', 'Oslo', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(131, 'Oman', 'Sultanate of Oman', 'MU', 'Muscat', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(132, 'Pakistan', 'Islamic Republic of Pakistan', 'PK', 'Islamabad', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(133, 'Palau', 'Republic of Palau', 'PS', 'Melekeok', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(134, 'Panama', 'Republic of Panama', 'PM', 'Panama', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(135, 'Papua New Guinea', 'Independent State of Papua New Guinea', 'PP', 'Port Moresby', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(136, 'Paraguay', 'Republic of Paraguay', 'PA', 'Asunción', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(137, 'Peru', 'Republic of Peru', 'PE', 'Lima', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(138, 'Philippines', 'Republic of the Philippines', 'RP', 'Manila', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(139, 'Poland', 'Republic of Poland', 'PL', 'Warsaw', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(140, 'Portugal', 'Portuguese Republic', 'PO', 'Lisbon', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(141, 'Qatar', 'State of Qatar', 'QA', 'Doha', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(142, 'Romania', 'Romania', 'RO', 'Bucharest', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(143, 'Russia', 'Russian Federation', 'RS', 'Moscow', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(144, 'Rwanda', 'Republic of Rwanda', 'RW', 'Kigali', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(145, 'Saint Kitts and Nevis', 'Federation of Saint Kitts and Nevis', 'SC', 'Basseterre', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(146, 'Saint Lucia', 'Saint Lucia', 'ST', 'Castries', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(147, 'Saint Vincent and the Grenadines', 'Saint Vincent and the Grenadines', 'VC', 'Kingstown', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(148, 'Samoa', 'Independent State of Samoa', 'WS', 'Apia', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(149, 'San  Marino', 'Republic of San Marino', 'SM', 'San Marino', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(150, 'Sao Tome and Principe', 'Democratic Republic of Sao Tome and Principe', 'TP', 'São Tomé', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(151, 'Saudi Arabia', 'Kingdom of Saudi Arabia', 'SA', 'Riyadh', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(152, 'Senegal', 'Republic of Senegal', 'SG', 'Dakar', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(153, 'Serbia', 'Republic of Serbia', 'RI', 'Belgrade', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(154, 'Seychelles', 'Republic of Seychelles', 'SE', 'Victoria', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(155, 'Sierra Leone', 'Republic of Sierra Leone', 'SL', 'Freetown', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(156, 'Singapore', 'Republic of Singapore', 'SN', 'Singapore', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(157, 'Slovakia', 'Slovak Republic', 'LO', 'Bratislava', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(158, 'Slovenia', 'Republic of Slovenia', 'SI', 'Ljubljana', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(159, 'Solomon Islands', 'Solomon Islands', 'BP', 'Honiara', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(160, 'Somalia', 'Federal Republic of Somalia', 'SO', 'Mogadishu', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(161, 'South Africa', 'Republic of South Africa', 'SF', 'Pretoria', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(162, 'South Korea', 'Republic of Korea', 'KS', 'Seoul', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(163, 'South Sudan', 'Republic of South Sudan', 'OD', 'Juba', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(164, 'Spain', 'Kingdom of Spain', 'SP', 'Madrid', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(165, 'Sri Lanka', 'Democratic Socialist Republic of Sri Lanka', 'CE', 'Colombo Sri Jayewardenepura Kott', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(166, 'Sudan', 'Republic of the Sudan', 'SU', 'Khartoum', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(167, 'Suriname', 'Republic of Suriname', 'NS', 'Paramaribo', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(168, 'Swaziland', 'Kingdom of Swaziland', 'WZ', 'Mbabane', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(169, 'Sweden', 'Kingdom of Sweden', 'SW', 'Stockholm', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(170, 'Switzerland', 'Swiss Confederation', 'SZ', 'Bern', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(171, 'Syria', 'Syrian Arab Republic', 'SY', 'Damascus', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(172, 'Taiwan', 'Taiwan', 'TW', 'Taipei', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(173, 'Tajikistan', 'Republic of Tajikistan', 'TI', 'Dushanbe', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(174, 'Tanzania', 'United Republic of Tanzania', 'TZ', 'Dar es Salaam', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(175, 'Thailand', 'Kingdom of Thailand', 'TH', 'Bangkok', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(176, 'Timor-Leste', 'Democratic Republic of Timor-Leste', 'TT', 'Dili', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(177, 'Togo', 'Togolese Republic', 'TO', 'Lomé', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(178, 'Tonga', 'Kingdom of Tonga', 'TN', 'Nuku''alofa', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(179, 'Trinidad and Tobago', 'Republic of Trinidad and Tobago', 'TD', 'Port-of-Spain', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(180, 'Tunisia', 'Tunisian Republic', 'TS', 'Tunis', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(181, 'Turkey', 'Republic of Turkey', 'TU', 'Ankara', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(182, 'Turkmenistan', 'Turkmenistan', 'TX', 'Ashgabat', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(183, 'Tuvalu', 'Tuvalu', 'TV', 'Funafuti', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(184, 'Uganda', 'Republic of Uganda', 'UG', 'Kampala', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(185, 'Ukraine', 'Ukraine', 'UP', 'Kyiv', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(186, 'United Arab Emirates', 'United Arab Emirates', 'AE', 'Abu Dhabi', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(187, 'United Kingdom', 'United Kingdom of Great Britain and Northern Ireland', 'UK', 'London', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(188, 'United States', 'United States of America', 'US', 'Washington DC', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(189, 'Uruguay', 'Oriental Republic of Uruguay', 'UY', 'Montevideo', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(190, 'Uzbekistan', 'Republic of Uzbekistan', 'UZ', 'Tashkent', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(191, 'Vanuatu', 'Republic of Vanuatu', 'NH', 'Port-Vila', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(192, 'Venezuela', 'Bolivarian Republic of Venezuela', 'VE', 'Caracas', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(193, 'Vietnam', 'Socialist Republic of Vietnam', 'VM', 'Hanoi', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(194, 'Yemen', 'Republic of Yemen', 'YM', 'Sanaa', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(195, 'Zambia', 'Republic of Zambia', 'ZA', 'Lusaka', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(196, 'Zimbabwe', 'Republic of Zimbabwe', 'ZI', 'Harare', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09');

-- --------------------------------------------------------

--
-- Table structure for table `country_state_province`
--

CREATE TABLE IF NOT EXISTS `country_state_province` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country_id` int(11) NOT NULL,
  `abbreviations` varchar(2) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `state_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `created_user_id` int(11) NOT NULL,
  `created_date` datetime NOT NULL,
  `updated_user_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `abbreviations` (`abbreviations`),
  UNIQUE KEY `state_name` (`state_name`),
  KEY `created_by` (`created_user_id`),
  KEY `updated_by` (`updated_user_id`),
  KEY `country_id` (`country_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=53 ;

--
-- Dumping data for table `country_state_province`
--

INSERT INTO `country_state_province` (`id`, `country_id`, `abbreviations`, `state_name`, `created_user_id`, `created_date`, `updated_user_id`, `updated_date`) VALUES
(1, 188, 'AL', 'Alabama', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(2, 188, 'AK', 'Alaska', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(5, 188, 'AZ', 'Arizona', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(6, 188, 'AR', 'Arkansas', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(7, 188, 'CA', 'California', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(8, 188, 'CO', 'Colorado', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(9, 188, 'CT', 'Connecticut', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(10, 188, 'DE', 'Delaware', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(11, 188, 'FL', 'Florida', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(12, 188, 'GA', 'Georgia', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(13, 188, 'HI', 'Hawaii', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(14, 188, 'ID', 'Idaho', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(15, 188, 'IL', 'Illinois', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(16, 188, 'IN', 'Indiana', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(17, 188, 'IA', 'Iowa', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(18, 188, 'KS', 'Kansas', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(19, 188, 'KY', 'Kentucky', 1, '2014-03-17 08:08:08', 1, '2014-03-25 20:33:31'),
(20, 188, 'LA', 'Louisiana', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(21, 188, 'ME', 'Maine', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(22, 188, 'MD', 'Maryland', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(23, 188, 'MA', 'Massachusetts', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(24, 188, 'MI', 'Michigan', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(25, 188, 'MN', 'Minnesota', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(26, 188, 'MS', 'Mississippi', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(27, 188, 'MO', 'Missouri', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(28, 188, 'MT', 'Montana', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(29, 188, 'NE', 'Nebraska', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(30, 188, 'NV', 'Nevada', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(31, 188, 'NH', 'New Hampshire', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(32, 188, 'NJ', 'New Jersey', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(33, 188, 'NM', 'New Mexico', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(34, 188, 'NY', 'New York', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(35, 188, 'NC', 'North Carolina', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(36, 188, 'ND', 'North Dakota', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(37, 188, 'OH', 'Ohio', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(38, 188, 'OK', 'Oklahoma', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(39, 188, 'OR', 'Oregon', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(40, 188, 'PA', 'Pennsylvania', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(41, 188, 'RI', 'Rhode Island', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(42, 188, 'SC', 'South Carolina', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(43, 188, 'SD', 'South Dakota', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(44, 188, 'TN', 'Tennessee', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(45, 188, 'TX', 'Texas', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(46, 188, 'UT', 'Utah', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(47, 188, 'VT', 'Vermont', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(48, 188, 'VA', 'Virginia', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(49, 188, 'WA', 'Washington', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(50, 188, 'WV', 'West Virginia', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(51, 188, 'WI', 'Wisconsin', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09'),
(52, 188, 'WY', 'Wyoming', 1, '2014-03-17 08:08:08', 1, '2014-03-17 13:09:09');

-- --------------------------------------------------------

--
-- Table structure for table `field_label`
--

CREATE TABLE IF NOT EXISTS `field_label` (
  `field_name` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `table_name` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`field_name`,`table_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `field_label`
--

INSERT INTO `field_label` (`field_name`, `table_name`) VALUES
('received_mail', 'patron_users'),
('researcher_category', 'patron_users');

-- --------------------------------------------------------

--
-- Table structure for table `field_value`
--

CREATE TABLE IF NOT EXISTS `field_value` (
  `field_label_name` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `field_value` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `ordered` smallint(6) NOT NULL,
  `active` set('Y','N') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`field_label_name`,`field_value`),
  KEY `field_value` (`field_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `field_value`
--

INSERT INTO `field_value` (`field_label_name`, `field_value`, `ordered`, `active`) VALUES
('received_mail', 'Email Special Collections', 2, 'Y'),
('received_mail', 'Request for Materials', 1, 'Y'),
('researcher_category', 'Faculty', 3, 'Y'),
('researcher_category', 'Graduate Student', 2, 'Y'),
('researcher_category', 'High School Student', 4, 'Y'),
('researcher_category', 'Independent Researcher', 5, 'Y'),
('researcher_category', 'Staff', 6, 'Y'),
('researcher_category', 'Undergraduate Student', 1, 'Y');

-- --------------------------------------------------------

--
-- Table structure for table `patron_photo_identification`
--

CREATE TABLE IF NOT EXISTS `patron_photo_identification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patron_user_id` int(11) NOT NULL,
  `staff_user_id` int(11) NOT NULL,
  `checked_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `patron_user_id` (`patron_user_id`),
  KEY `staff_user_id` (`staff_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `patron_request_materials`
--

CREATE TABLE IF NOT EXISTS `patron_request_materials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `call_number` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `box_volume` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `copy_issue_folder_number` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `item_date` varchar(128) DEFAULT NULL,
  `title` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `author` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `notes` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `schedule_visit` datetime NOT NULL,
  `created_user_id` int(11) NOT NULL,
  `created_date` datetime NOT NULL,
  `updated_user_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `request_status` enum('Draft','Submit','Cancel','In Process','Reschedule','Accept','Completed') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Draft',
  `status_order` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `created_user_id` (`created_user_id`),
  KEY `updated_user_id` (`updated_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `photo_access_admin_request_notes`
--

CREATE TABLE IF NOT EXISTS `photo_access_admin_request_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_id` int(11) NOT NULL,
  `notes` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `created_user_id` int(11) NOT NULL,
  `admin_notes_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `request_id` (`request_id`),
  KEY `created_by` (`created_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `photo_access_config`
--

CREATE TABLE IF NOT EXISTS `photo_access_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(256) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=29 ;

--
-- Dumping data for table `photo_access_config`
--

INSERT INTO `photo_access_config` (`id`, `key`, `value`) VALUES
(1, 'ldap_account_suffix', '@uncc.edu'),
(2, 'ldap_base_dn', 'ou=people,ou=unccharlotte,dc=its,dc=uncc,dc=edu'),
(3, 'ldap_domain_controllers', 'its.uncc.edu'),
(4, 'ldap_admin_username', 'SVC_AtkinsProject'),
(5, 'ldap_admin_password', 'Z!dyunZ;HD'),
(6, 'ldap_real_primarygroup', 'true'),
(7, 'ldap_use_ssl', NULL),
(8, 'ldap_use_tls', NULL),
(9, 'ldap_recursive_groups', 'true'),
(10, 'ldap_ad_port', '389'),
(11, 'ldap_sso', NULL),
(12, 'ldap_dept_access', 'LIBR'),
(13, 'ldap_allow_standard_access', 'Y'),
(14, 'ldap_authentication_type', 'LDAP'),
(15, 'ldap_default_group', '5'),
(16, 'ldap_store_password', 'N'),
(17, 'email_prefix', '[Atkins Patron]'),
(18, 'email_transport', 'PHP Default'),
(19, 'email_site_domain', 'localhost'),
(20, 'email_smtp_host', 'smtp.gmail.com'),
(21, 'email_smtp_port', '587'),
(22, 'email_host_requires_login', 'Y'),
(23, 'email_smtp_username', 'atkins.autoreply@gmail.com'),
(24, 'email_smtp_password', 'DIA4MailReply'),
(25, 'email_smtp_server_timeout', '60'),
(26, 'email_enable_tls', NULL),
(27, 'email_from', 'admin'),
(28, 'email_send_type', 'Separated');

-- --------------------------------------------------------

--
-- Table structure for table `photo_access_email`
--

CREATE TABLE IF NOT EXISTS `photo_access_email` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(256) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `body` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `email_status` enum('Draft','Sent','In Process','Reply','Completed') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Draft',
  `status_order` int(11) NOT NULL,
  `created_user_id` int(11) NOT NULL,
  `created_date` datetime NOT NULL,
  `updated_user_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_user_id` (`created_user_id`),
  KEY `updated_user_id` (`updated_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `photo_access_email_reply`
--

CREATE TABLE IF NOT EXISTS `photo_access_email_reply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mail_id` int(11) NOT NULL,
  `reply_body` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `replied_date` datetime NOT NULL,
  `read_status` enum('Y','N') DEFAULT 'N',
  PRIMARY KEY (`id`),
  KEY `mail_id` (`mail_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `photo_access_policy`
--

CREATE TABLE IF NOT EXISTS `photo_access_policy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `policy_name` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `policy_content` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `active` enum('Y','N') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
  `created_user_id` int(11) NOT NULL,
  `created_date` datetime NOT NULL,
  `updated_user_id` int(11) DEFAULT NULL,
  `updated_date` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `created_user_id` (`created_user_id`),
  KEY `updated_user_id` (`updated_user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `photo_access_policy`
--

INSERT INTO `photo_access_policy` (`id`, `policy_name`, `policy_content`, `active`, `created_user_id`, `created_date`, `updated_user_id`, `updated_date`) VALUES
(1, 'Patron Registration', 'University of North Carolina at Charlotte\r\nAtkins Library -Special Collections\r\nPublic Services Division\r\nPolicies and Procedures for the Use of Collections\r\nThe Special Collections Mary and Harry L. Dalton Rare Book and Manuscript Reading Room is available for the research needs of students, faculty, staff and the general public. All materials are housed in a controlled environment and generally, can only be used in the Special Collections Reading Room. Our responsibility for proper care of the unique and rare materials entrusted to us requires us to institute safeguards in the Reading Room. Researchers interested in our collections are subject to the regulations outlined below.\r\n1. Registration is required once a year.\r\n2. In order to gain access to Special Collection materials, researchers must present photographic identification\r\n3. Materials must be used in the Reading Room. Researchers must complete a Request for Materials Form for each item requested. More than one item may be requested, but use is limited to a single item at a time.\r\n4. Lockers are provided for personal possessions and assigned by Reading Room Staff. Personal possessions such as briefcases, purses, pocketbooks, backpacks, coats, jackets, etc., should be stowed away in lockers and kept away Special Collection materials.\r\n5. Special Collection materials must be used with appropriate care and attention. Patrons are held responsible for materials check out to them and must abide by the following:\r\n• No pens, tobacco, food, drink, gum and cosmetics are allowed in the Reading Room. Pencils are provide and available in the Reading Room.\r\n• Researchers using the collections are allowed only pencil and paper to take notes.\r\n• Computers and scanners are allowed in the Reading Room.\r\n• Rare books should not be opened and laid face down on tables\r\n• Objects such as pencils should not be used as bookmarks.\r\n• Items should be placed flat on table and should not be held in lap or propped against edge of table.\r\n• No marks or erasures should be made on material.\r\n• Items should be kept in the order in which they were presented. If order of material appears to have been disturbed, please notify Reading Room Staff.\r\n• Straighten contents in folders after use before replacing folders in boxes.\r\n• Do not fold or tear documents.\r\n6. For manuscript collections, researchers should use only one folder at a time and take care in handling contents of folders by gently turning pages of collections.\r\n7. For rare books, researchers are allowed three books at a time. Book cradles and book weights are available for use.\r\n8. Researchers requesting reproduction of materials should make arrangements with Reading Room Reference Staff. Please do not remove items from folder. Reproduction requests are subject to review and approval on a case by case basis. Large photocopy order of 100 or more pages require a down payment 50% of estimated cost before order can be processed. Orders to be mailed will be invoiced and payment is required before copies will be sent.\r\n9. UNC Charlotte Atkins Library Special Collections does not hold literary rights to all material in its collections. The researcher is responsible for securing those rights when needed. Copyright information is available upon request.\r\n10. Access to some documents, especially files in University Archives, may be restricted. Permission to use must be secured from the appropriate university official or individual.\r\n11. Proper acknowledgement of manuscript and university archives materials must be made in any resulting writings or publications. The proper form of citation is the full name of the collection used, followed by: University of North Carolina at Charlotte Atkins Library Special Collections.\r\n12. Publication of material requires prior permission of the AUL, Associate University Librarian of Special Collections. Permission to examine and copy materials does not constitute Permission to Publish.\r\n13. No requests for materials will be accepted later than 30 minutes before closing; materials check-out to researchers must be returned to the Reading Room Desk no later than 5 minutes before closing.\r\n14. The Reading Room is designated a quiet study area. Please keep any conversations to a minimum. Talking on cell phones in the Reading Room is not allowed.\r\n15. Researchers should understand that failure to comply with these rules may result in denial of access to the collections.', 'Y', 1, '2014-03-20 12:00:00', 1, '2014-06-26 20:54:05');

-- --------------------------------------------------------

--
-- Table structure for table `photo_access_users`
--

CREATE TABLE IF NOT EXISTS `photo_access_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_group_id` int(11) NOT NULL,
  `user_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `first_name` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `address_1` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `address_2` varchar(128) DEFAULT NULL,
  `city` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `state_province` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `postal_code` int(5) DEFAULT NULL,
  `country_id` int(11) DEFAULT '188',
  `email` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(15) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `institution` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `researcher_category` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `policy_acceptance_date` date DEFAULT NULL,
  `department` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `received_mail` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `authentication_method` enum('LDAP','Database') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `active` enum('Y','N') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Y',
  `created_by` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `created_date` datetime NOT NULL,
  `updated_by` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `user_group_id` (`user_group_id`),
  KEY `state_id` (`state_province`),
  KEY `research_category` (`researcher_category`),
  KEY `country_id` (`country_id`),
  KEY `received_mail` (`received_mail`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `photo_access_users`
--

INSERT INTO `photo_access_users` (`id`, `user_group_id`, `user_id`, `password`, `first_name`, `last_name`, `address_1`, `address_2`, `city`, `state_province`, `postal_code`, `country_id`, `email`, `phone`, `institution`, `researcher_category`, `policy_acceptance_date`, `department`, `received_mail`, `authentication_method`, `active`, `created_by`, `created_date`, `updated_by`, `updated_date`) VALUES
(1, 1, 'admin', 'd033e22ae348aeb5660fc2140aec3585', 'Admin', 'System', '9201 University City Blvd', 'Atkins Library', 'Charlotte', 'North Carolina', 28223, 188, 'support.library@uncc.edu', '704-687-1137', 'UNC Charlotte', 'Staff', '2013-03-19', 'LIBR', '', 'Database', 'Y', 'admin', '2014-03-17 12:47:00', 'admin', '2014-03-25 17:08:39'),
(2, 5, 'bnguye21', NULL, 'Bach', 'Nguyen', NULL, NULL, NULL, NULL, NULL, 188, 'bnguye21@uncc.edu', '', 'UNC Charlotte', 'Staff', '2014-04-22', 'LIBR', NULL, 'LDAP', 'Y', 'admin', '2014-06-23 15:41:18', 'bnguye21', '2014-06-23 15:49:53');

-- --------------------------------------------------------

--
-- Table structure for table `photo_access_user_group`
--

CREATE TABLE IF NOT EXISTS `photo_access_user_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`group_name`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `photo_access_user_group`
--

INSERT INTO `photo_access_user_group` (`id`, `group_name`) VALUES
(1, 'Admin'),
(2, 'Manager'),
(3, 'Editor'),
(4, 'Author'),
(5, 'Reader');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `countries`
--
ALTER TABLE `countries`
  ADD CONSTRAINT `countries_ibfk_1` FOREIGN KEY (`created_user_id`) REFERENCES `photo_access_users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `countries_ibfk_2` FOREIGN KEY (`updated_user_id`) REFERENCES `photo_access_users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `country_state_province`
--
ALTER TABLE `country_state_province`
  ADD CONSTRAINT `country_state_province_ibfk_3` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `country_state_province_ibfk_4` FOREIGN KEY (`created_user_id`) REFERENCES `photo_access_users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `country_state_province_ibfk_5` FOREIGN KEY (`updated_user_id`) REFERENCES `photo_access_users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `field_value`
--
ALTER TABLE `field_value`
  ADD CONSTRAINT `field_value_ibfk_1` FOREIGN KEY (`field_label_name`) REFERENCES `field_label` (`field_name`) ON UPDATE CASCADE;

--
-- Constraints for table `patron_photo_identification`
--
ALTER TABLE `patron_photo_identification`
  ADD CONSTRAINT `patron_photo_identification_ibfk_1` FOREIGN KEY (`patron_user_id`) REFERENCES `photo_access_users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `patron_photo_identification_ibfk_2` FOREIGN KEY (`staff_user_id`) REFERENCES `photo_access_users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `patron_request_materials`
--
ALTER TABLE `patron_request_materials`
  ADD CONSTRAINT `patron_request_materials_ibfk_1` FOREIGN KEY (`created_user_id`) REFERENCES `photo_access_users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `patron_request_materials_ibfk_2` FOREIGN KEY (`updated_user_id`) REFERENCES `photo_access_users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `photo_access_admin_request_notes`
--
ALTER TABLE `photo_access_admin_request_notes`
  ADD CONSTRAINT `photo_access_admin_request_notes_ibfk_3` FOREIGN KEY (`request_id`) REFERENCES `patron_request_materials` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `photo_access_admin_request_notes_ibfk_4` FOREIGN KEY (`created_user_id`) REFERENCES `photo_access_users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `photo_access_email`
--
ALTER TABLE `photo_access_email`
  ADD CONSTRAINT `photo_access_email_ibfk_1` FOREIGN KEY (`created_user_id`) REFERENCES `photo_access_users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `photo_access_email_ibfk_2` FOREIGN KEY (`updated_user_id`) REFERENCES `photo_access_users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `photo_access_email_reply`
--
ALTER TABLE `photo_access_email_reply`
  ADD CONSTRAINT `photo_access_email_reply_ibfk_1` FOREIGN KEY (`mail_id`) REFERENCES `photo_access_email` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `photo_access_policy`
--
ALTER TABLE `photo_access_policy`
  ADD CONSTRAINT `photo_access_policy_ibfk_2` FOREIGN KEY (`updated_user_id`) REFERENCES `photo_access_users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `photo_access_policy_ibfk_1` FOREIGN KEY (`created_user_id`) REFERENCES `photo_access_users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `photo_access_users`
--
ALTER TABLE `photo_access_users`
  ADD CONSTRAINT `photo_access_users_ibfk_116` FOREIGN KEY (`user_group_id`) REFERENCES `photo_access_user_group` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `photo_access_users_ibfk_117` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `photo_access_users_ibfk_118` FOREIGN KEY (`researcher_category`) REFERENCES `field_value` (`field_value`) ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
