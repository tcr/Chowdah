-- Quiki SQL Installation

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `quiki`
--

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;

CREATE TABLE `pages` (
  `title` varchar(255) NOT NULL,
  `content` mediumtext NOT NULL,
  `tags` tinytext NOT NULL,
  UNIQUE KEY `title` (`title`)
) ENGINE=MyISAM;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`title`, `content`, `tags`) VALUES
('Main Page', 'Welcome to *Quiki*, the _quick wiki_!\r\n\r\nThis application is an example of what you can do with "Chowdah":http://chowdah.googlecode.com/. Take a look at the source and feel free to play around.', 'introduction');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `registered` datetime NOT NULL,
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM;