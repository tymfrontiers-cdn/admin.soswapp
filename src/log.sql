SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

DROP TABLE IF EXISTS `email_outbox`;
CREATE TABLE `email_outbox` (
  `id` int(11) UNSIGNED NOT NULL,
  `priority` tinyint(4) NOT NULL DEFAULT 5,
  `qid` char(128) NOT NULL,
  `transport` char(25) NOT NULL COMMENT 'SMTP,API',
  `gateway` char(25) NOT NULL COMMENT 'MAILGUN,NATIVE',
  `domain` char(55) NOT NULL,
  `batch` char(128) NOT NULL,
  `has_attachment` tinyint(1) NOT NULL DEFAULT 0,
  `status` char(3) NOT NULL DEFAULT 'Q' COMMENT 'Q,D,S',
  `subject` char(95) NOT NULL,
  `msg_text` varchar(2048) NOT NULL,
  `msg_html` text NOT NULL,
  `sender` char(55) NOT NULL,
  `receiver` char(55) NOT NULL,
  `cc` varchar(512) NOT NULL,
  `bcc` varchar(512) NOT NULL,
  `headers` varchar(10240) DEFAULT NULL,
  `_created` datetime NOT NULL DEFAULT current_timestamp(),
  `_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `email_outbox_attachment`;
CREATE TABLE `email_outbox_attachment` (
  `id` int(10) UNSIGNED NOT NULL,
  `ebatch` char(128) NOT NULL,
  `fid` int(11) NOT NULL,
  `_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `event_log`;
CREATE TABLE `event_log` (
  `id` int(11) NOT NULL,
  `user` char(12) NOT NULL,
  `channel` char(95) NOT NULL,
  `title` char(128) NOT NULL,
  `info` text NOT NULL,
  `_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `otp_email`;
CREATE TABLE `otp_email` (
  `id` int(10) UNSIGNED NOT NULL,
  `ref` char(32) NOT NULL,
  `user` char(72) NOT NULL,
  `code` char(32) NOT NULL,
  `qid` varchar(256) NOT NULL,
  `subject` char(72) NOT NULL,
  `message` text NOT NULL,
  `message_text` varchar(512) NOT NULL,
  `sender` char(128) NOT NULL,
  `receiver` char(128) NOT NULL,
  `expiry` datetime DEFAULT NULL,
  `_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


ALTER TABLE `email_outbox`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `email_outbox_attachment`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `event_log`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `otp_email`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ref` (`ref`);


ALTER TABLE `email_outbox`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `email_outbox_attachment`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `event_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `otp_email`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
