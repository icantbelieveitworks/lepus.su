CREATE TABLE `log_ip` (
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `ip` bigint(20) NOT NULL,
  `platform` varchar(24) DEFAULT NULL,
  `browser` varchar(24) DEFAULT NULL,
  `time` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `support` (
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `title` varchar(32) NOT NULL,
  `open` varchar(128) NOT NULL,
  `last` varchar(128) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `support_msg` (
  `id` int(11) NOT NULL,
  `tid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `msg` mediumtext NOT NULL,
  `time` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `login` varchar(128) NOT NULL,
  `passwd` varchar(128) NOT NULL,
  `session` varchar(128) DEFAULT NULL,
  `data` varchar(1024) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `users` (`id`, `login`, `passwd`, `session`, `data`) VALUES
(1, 'poiuty@lepus.su', '$2y$10$/wOuC/bn/AAj3FBjZHclWeejBh6r.EXNukouKJdvwLQo1i7o1UEC.', '2c9ec52e729e83c1a455aef73f73cb2e21a3cd68695b2dc3abe436a381cad96fc2329250de0019653073da29f11d162b9be5146ad3b4d48b68ea0636b3f2fc33', '{"balance":500,"phone":"74959816180","regDate":"1448450707","access":"2","lastIP":"127.0.0.1","apiKey":"ec374361f6e0d83147924890027c28e8"}'),
(2, 'admin@lepus.su', '$2y$10$/wOuC/bn/AAj3FBjZHclWeejBh6r.EXNukouKJdvwLQo1i7o1UEC.', 'cf173fa5f1db5a8914aec5394d9d55f34903ae4ab8d2d78dd22927309c6c33725d4c21e815c23781ff8abaf188d02dd8a7977213bcb20f4c81f832681f64ee87', '{"balance":0,"phone":null,"regDate":1449312798,"access":1,"lastIP":3232235620,"apiKey":"mrS3YXnfsAc42VkblJxVdXE40UGBVcwl"}');

ALTER TABLE `log_ip`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uid` (`uid`),
  ADD KEY `ip` (`ip`);

ALTER TABLE `support`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uid` (`uid`);

ALTER TABLE `support_msg`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tid` (`tid`),
  ADD KEY `uid` (`uid`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mail` (`login`),
  ADD UNIQUE KEY `session` (`session`),
  ADD KEY `passwd` (`passwd`);

ALTER TABLE `log_ip`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `support`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `support_msg`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
