SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

DROP TABLE IF EXISTS `setting`;
CREATE TABLE `setting` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user` char(56) NOT NULL,
  `skey` char(26) NOT NULL,
  `sval` char(128) NOT NULL,
  `_updated` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `setting_option`;
CREATE TABLE `setting_option` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` char(28) NOT NULL,
  `domain` char(32) NOT NULL,
  `multi_val` tinyint(1) DEFAULT 0,
  `type` char(28) NOT NULL,
  `type_variant` varchar(512) DEFAULT NULL,
  `title` char(52) NOT NULL,
  `description` varchar(256) NOT NULL,
  `_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `user_dashlist`;
CREATE TABLE `user_dashlist` (
  `id` int(10) UNSIGNED NOT NULL,
  `path` varchar(256) NOT NULL,
  `onclick` char(32) DEFAULT NULL,
  `classname` char(56) DEFAULT NULL,
  `title` char(56) NOT NULL,
  `subtitle` char(72) DEFAULT NULL,
  `icon` char(72) DEFAULT NULL,
  `sort` tinyint(3) UNSIGNED DEFAULT 0,
  `description` varchar(256) NOT NULL,
  `_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE `setting`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `setting_option`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name` (`name`);

ALTER TABLE `user_dashlist`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `setting`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `setting_option`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `user_dashlist`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
