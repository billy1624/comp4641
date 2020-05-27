# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 127.0.0.1 (MySQL 5.6.35-log)
# Database: comp4641
# Generation Time: 2020-04-17 06:51:52 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table tweets
# ------------------------------------------------------------

CREATE TABLE `tweets` (
  `id` bigint(30) unsigned NOT NULL AUTO_INCREMENT,
  `date` datetime DEFAULT NULL,
  `user` varchar(255) DEFAULT NULL,
  `is_retweet` varchar(255) DEFAULT NULL,
  `is_quote` varchar(255) DEFAULT NULL,
  `text` text,
  `quoted_text` text,
  `original_tweet` text,
  `lat` varchar(255) DEFAULT NULL,
  `long` varchar(255) DEFAULT NULL,
  `hts` varchar(255) DEFAULT NULL,
  `mentions` varchar(255) DEFAULT NULL,
  `tweet_id` varchar(255) DEFAULT NULL,
  `likes` varchar(255) DEFAULT NULL,
  `retweets` varchar(255) DEFAULT NULL,
  `replies` varchar(255) DEFAULT NULL,
  `quote_count` varchar(255) DEFAULT NULL,
  `original_tweet_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



# Dump of table votes
# ------------------------------------------------------------

CREATE TABLE `votes` (
  `id` bigint(30) unsigned NOT NULL AUTO_INCREMENT,
  `tweets_id` int(30) unsigned NOT NULL,
  `user` varchar(255) DEFAULT NULL,
  `emotions` varchar(255) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tweets_id` (`tweets_id`),
  KEY `user` (`user`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
