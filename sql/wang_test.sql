/*
 Navicat Premium Data Transfer

 Source Server         : Localhost_5.7
 Source Server Type    : MySQL
 Source Server Version : 50743 (5.7.43)
 Source Host           : localhost:3306
 Source Schema         : wang_test

 Target Server Type    : MySQL
 Target Server Version : 50743 (5.7.43)
 File Encoding         : 65001

 Date: 23/10/2023 14:52:32
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for tiezi
-- ----------------------------
DROP TABLE IF EXISTS `tiezi`;
CREATE TABLE `tiezi` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` text COLLATE utf8mb4_unicode_ci,
  `author` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `replies` int(11) DEFAULT NULL,
  `ip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `idate` timestamp NULL DEFAULT NULL,
  `ndate` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------
-- Records of tiezi
-- ----------------------------
BEGIN;
INSERT INTO `tiezi` (`id`, `subject`, `body`, `author`, `replies`, `ip`, `idate`, `ndate`) VALUES (1, 'test', 'test', 'test', 0, '127.0.0.1', '2023-10-23 06:44:21', '2023-10-23 06:44:21');
INSERT INTO `tiezi` (`id`, `subject`, `body`, `author`, `replies`, `ip`, `idate`, `ndate`) VALUES (2, 'test', 'test', 'test', 0, '127.0.0.1', '2023-10-23 06:44:23', '2023-10-23 06:44:23');
INSERT INTO `tiezi` (`id`, `subject`, `body`, `author`, `replies`, `ip`, `idate`, `ndate`) VALUES (3, 'test', 'test', 'test', 0, '127.0.0.1', '2023-10-23 06:45:44', '2023-10-23 06:45:44');
INSERT INTO `tiezi` (`id`, `subject`, `body`, `author`, `replies`, `ip`, `idate`, `ndate`) VALUES (4, 'test', 'test', 'test', 0, '127.0.0.1', '2023-10-23 06:45:44', '2023-10-23 06:45:44');
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
