-- Quiki SQL Installation

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `quiki`
--

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `title` varchar(255) NOT NULL,
  `content` mediumtext NOT NULL,
  `tags` tinytext NOT NULL,
  UNIQUE KEY `title` (`title`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`title`, `content`, `tags`) VALUES
('Main Page', 'Welcome to Quiki, the quick wiki!\r\n\r\nThis application is an example of what you can do with Chowdah. Take a look at the source and feel free to play around.', 'introduction');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `name` varchar(255) collate latin1_general_ci NOT NULL,
  `password` varchar(255) collate latin1_general_ci NOT NULL,
  `email` varchar(255) collate latin1_general_ci NOT NULL,
  `registered` datetime NOT NULL,
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;