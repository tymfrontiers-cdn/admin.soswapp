SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE `path_access` (
  `id` int(11) NOT NULL,
  `user` char(12) NOT NULL,
  `path_name` char(128) NOT NULL,
  `_author` char(12) NOT NULL,
  `_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `path_access` (`id`, `user`, `path_name`, `_author`, `_created`) VALUES
(1, 'DFOWNER', '46uk245faf3z', 'DFOWNER', '2020-06-30 18:40:49');

CREATE TABLE `user` (
  `_id` char(12) NOT NULL,
  `status` char(25) NOT NULL DEFAULT 'PENDING',
  `work_group` char(32) NOT NULL,
  `email` char(55) NOT NULL,
  `phone` char(16) DEFAULT NULL,
  `password` varchar(128) DEFAULT NULL,
  `name` char(32) NOT NULL,
  `surname` char(32) NOT NULL,
  `country_code` char(2) NOT NULL,
  `state_code` char(8) NOT NULL,
  `_author` char(12) NOT NULL,
  `_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `user` (`_id`, `status`, `work_group`, `email`, `phone`, `password`, `name`, `surname`, `country_code`, `state_code`, `_author`, `_created`) VALUES
('DFOWNER', 'ACTIVE', 'OWNER', 'dev@project.info', NULL, '$2y$10$ZGNkNjhkZDc3OTAwZDQ2Z.cOwfggpgrg3GFo/mzscbQf9im5Mf.yK', 'Default', 'Owner', 'NG', 'NGLAG', 'DFOWNER', '2020-06-30 18:40:49');

CREATE TABLE `work_domain` (
  `name` char(98) NOT NULL,
  `acronym` char(16) NOT NULL,
  `path` char(72) NOT NULL,
  `icon` char(72) NOT NULL,
  `description` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `work_domain` (`name`, `acronym`, `path`, `icon`, `description`) VALUES
('project-admin', 'ADM', '/admin', '&lt;i class=&quot;fas fa-cogs&quot;&gt;&lt;/i&gt;', 'Admin Web portal');

CREATE TABLE `work_group` (
  `name` char(32) NOT NULL,
  `rank` tinyint(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `work_group` (`name`, `rank`) VALUES
('ADMIN', 6),
('ADVERTISER', 3),
('ANALYST', 2),
('DEVELOPER', 7),
('EDITOR', 5),
('GUEST', 0),
('MODERATOR', 4),
('OWNER', 14),
('SUPERADMIN', 8),
('USER', 1);

CREATE TABLE `work_path` (
  `name` char(32) NOT NULL,
  `domain` char(98) NOT NULL,
  `type` char(16) DEFAULT 'READ' COMMENT 'ALTER | READ',
  `path` char(56) NOT NULL,
  `nav_visible` tinyint(1) NOT NULL DEFAULT 0,
  `access_rank` tinyint(2) NOT NULL,
  `access_rank_strict` tinyint(1) DEFAULT 0,
  `onclick` char(32) DEFAULT NULL,
  `classname` char(56) DEFAULT NULL,
  `title` char(56) NOT NULL,
  `icon` char(72) DEFAULT NULL,
  `sort` tinyint(3) UNSIGNED DEFAULT 0,
  `description` varchar(256) NOT NULL,
  `_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `work_path` (`name`, `domain`, `type`, `path`, `nav_visible`, `access_rank`, `access_rank_strict`, `onclick`, `classname`, `title`, `icon`, `sort`, `description`, `_created`) VALUES
('46uk245faf3z', 'project-admin', 'ALTER', '/', 0, 7, 0, '', '', 'Ultimate access', '', 0, 'Ultimate domain -path/access', '2020-06-30 18:40:49'),
('73ykw5a64n45', 'project-admin', 'READ', '/settings', 1, 4, 0, NULL, NULL, 'Settings', '&lt;i class=&quot;fas fa-cog&quot;&gt;&lt;/i&gt;', 10, 'Settings list/options', '2020-06-30 18:40:49'),
('c3n4nf33e72c', 'project-admin', 'READ', '/user-dashlists', 1, 5, 0, '', '', 'User Dashlist', '&lt;i class=&quot;fas fa-bars&quot;&gt;&lt;/i&gt;', 8, 'User dashboard shortcuts and services', '2020-05-11 01:49:06'),
('cn33mesdqyw7', 'project-admin', 'READ', '/work-paths', 1, 6, 0, '', '', 'Work paths', '&lt;i class=&quot;fas fa-folder-open&quot;&gt;&lt;/i&gt;', 3, 'List of work paths', '2020-06-30 18:40:49'),
('etkuqz4vhwxg', 'project-admin', 'READ', '/path-accesses', 1, 6, 0, '', '', 'Path access', '&lt;i class=&quot;fas fa-universal-access&quot;&gt;&lt;/i&gt;', 4, 'Path access listing', '2020-06-30 18:40:49'),
('h2wpa3k5djzj', 'project-admin', 'READ', '/work-domains', 1, 4, 0, '', '', 'Work domains', '&lt;i class=&quot;fas fa-globe&quot;&gt;&lt;/i&gt;', 2, 'Work domain list', '2020-06-30 18:40:49'),
('hn6h5prt5638', 'project-admin', 'READ', '/setting-options', 1, 7, 0, '', '', 'Setting options', '&lt;i class=&quot;fas fa-cogs&quot;&gt;&lt;/i&gt;', 11, 'View setting options', '2020-06-30 18:40:49'),
('k73265y4pc45', 'project-admin', 'READ', '/work-domain', 0, 6, 0, NULL, NULL, '/WorkDomain', NULL, 0, 'Work domain management access', '2020-06-30 18:40:49'),
('p4j5qstk4k4z', 'project-admin', 'READ', '/user-dashlist', 0, 7, 0, NULL, NULL, 'User Dashlist /access', NULL, 8, 'Access grant for user dashlist', '2020-05-11 01:52:58'),
('q7a3cst8k3e4', 'project-admin', 'ALTER', '/user', 0, 6, 0, '', '', '/User', '', 0, 'Path access for user management', '2020-06-30 18:40:49'),
('r3e37d5xbs9y', 'project-admin', 'READ', '/users', 1, 4, 0, '', '', 'Users', '&lt;i class=&quot;fas fa-users&quot;&gt;&lt;/i&gt;', 1, 'Admin user accounts', '2020-06-30 18:40:49'),
('rsp2w8xxvm4u', 'project-admin', 'READ', '/dashboard', 1, 4, 0, '', '', 'Dashboard', '&lt;i class=&quot;fas fa-tachometer-alt&quot;&gt;&lt;/i&gt;', 0, 'Admin dashboard', '2020-06-30 18:40:49'),
('t3c3vy55sr43', 'project-admin', 'ALTER', '/work-path', 0, 6, 0, '', '', '/Work-Path', '', 0, 'Work path manager access', '2020-06-30 18:40:49'),
('u25f4m68tm92', 'project-admin', 'ALTER', '/path-access', 0, 6, 0, '', '', '/Path-access', '', 0, 'Path access management grant', '2020-06-30 18:40:49'),
('uyrksf68d64f', 'project-admin', 'ALTER', '/setting', 0, 5, 0, '', '', '/Setting access', '', 0, 'Path access for settings', '2020-06-30 18:40:49'),
('xh83w4yz64dr', 'project-admin', 'ALTER', '/setting-option', 0, 7, 0, '', '', '/Setting option', '', 0, 'Access to manage setting options', '2020-06-30 18:40:49');


ALTER TABLE `path_access`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user` (`user`);

ALTER TABLE `user`
  ADD PRIMARY KEY (`_id`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `work_domain`
  ADD PRIMARY KEY (`name`),
  ADD UNIQUE KEY `acronym` (`acronym`);

ALTER TABLE `work_group`
  ADD PRIMARY KEY (`name`);

ALTER TABLE `work_path`
  ADD PRIMARY KEY (`name`),
  ADD KEY `domain` (`domain`);


ALTER TABLE `path_access`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
