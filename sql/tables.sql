-- ----------------------------
-- Table structure for `users`
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `passwords` varchar(255) NOT NULL,
  `role` varchar(11) NOT NULL DEFAULT `user`,
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Table structure for `article`
-- ----------------------------
DROP TABLE IF EXISTS `articles`;
CREATE TABLE `articles` (
    `article_id`  int(11) NOT NULL AUTO_INCREMENT,
    `user_id`  int(11) NOT NULL, 
    `title`       varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
    `content`     text COLLATE utf8_czech_ci,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`article_id`)
) ENGINE = InnoDB AUTO_INCREMENT = 3 DEFAULT CHARSET = utf8 COLLATE = utf8_czech_ci;

-- ----------------------------
-- Table structure for `comment`
-- ----------------------------
DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
    `comment_id`  int(11) NOT NULL AUTO_INCREMENT,
    `article_id`  int(11) NOT NULL,
    `parent_id` int(11) NOT NULL DEFAULT '-1',
    `user_id`  int(11) NOT NULL, 
    `content`     text COLLATE utf8_czech_ci,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`comment_id`)
) ENGINE = InnoDB AUTO_INCREMENT = 3 DEFAULT CHARSET = utf8 COLLATE = utf8_czech_ci;

-- ----------------------------
-- Table structure for `category`
-- ----------------------------
DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
    `category_id`  int(11) NOT NULL AUTO_INCREMENT,
    `category`       varchar(20) COLLATE utf8_czech_ci DEFAULT NULL,
    PRIMARY KEY (`category_id`)
) ENGINE = InnoDB AUTO_INCREMENT = 3 DEFAULT CHARSET = utf8 COLLATE = utf8_czech_ci;

-- ----------------------------
-- Table structure for `portfolio`
-- ----------------------------
DROP TABLE IF EXISTS `portfolio`;
CREATE TABLE `portfolio` (
    `portfolio_id`  int(11) NOT NULL AUTO_INCREMENT,
    `title`       varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
    `description`     text COLLATE utf8_czech_ci,
    `content`     text COLLATE utf8_czech_ci,   
    `img`         varchar(255) DEFAULT NULL,
    `category_id`     int(11) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`portfolio_id`)      
) ENGINE = InnoDB AUTO_INCREMENT = 3 DEFAULT CHARSET = utf8 COLLATE = utf8_czech_ci;

-- ----------------------------
-- Table structure for `statistic`
-- ----------------------------
DROP TABLE IF EXISTS `statistic`;
CREATE TABLE `statistic` (
    `statistic_id`  int(11) NOT NULL AUTO_INCREMENT,
    `page`       varchar(25) COLLATE utf8_czech_ci NOT NULL,
    `count`     int(11) NOT NULL,
    PRIMARY KEY (`statistic_id`)      
) ENGINE = InnoDB AUTO_INCREMENT = 3 DEFAULT CHARSET = utf8 COLLATE = utf8_czech_ci;
