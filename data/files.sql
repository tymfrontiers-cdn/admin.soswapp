SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

DROP TABLE IF EXISTS `file_default`;
CREATE TABLE `file_default` (
  `id` int(11) NOT NULL,
  `user` char(32) NOT NULL,
  `set_key` char(32) NOT NULL,
  `file_id` int(11) NOT NULL,
  `_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `file_meta`;
CREATE TABLE `file_meta` (
  `id` int(11) NOT NULL,
  `_locked` tinyint(1) DEFAULT 0,
  `_checksum` text DEFAULT NULL,
  `_watermarked` tinyint(1) DEFAULT 0,
  `nice_name` varchar(256) NOT NULL,
  `type_group` char(32) DEFAULT NULL,
  `caption` char(125) NOT NULL,
  `_path` char(125) NOT NULL,
  `owner` char(128) NOT NULL,
  `privacy` char(25) NOT NULL,
  `_name` varchar(250) NOT NULL,
  `_type` char(95) NOT NULL,
  `_size` int(11) NOT NULL,
  `_creator` char(16) NOT NULL DEFAULT 'SYSTEM',
  `_updated` datetime NOT NULL DEFAULT current_timestamp(),
  `_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE `file_default`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `file_meta`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `_name` (`_name`),
  ADD KEY `owner` (`owner`);


ALTER TABLE `file_default`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `file_meta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
