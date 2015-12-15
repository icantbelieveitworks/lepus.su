CREATE TABLE `log_ip` (
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `ip` bigint(20) NOT NULL,
  `platform` varchar(24) DEFAULT NULL,
  `browser` varchar(24) DEFAULT NULL,
  `time` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `log_ip` (`id`, `uid`, `ip`, `platform`, `browser`, `time`) VALUES
(3, 6, 2147483647, 'Linux', 'Chrome', 1449324663),
(4, 6, 2147483647, 'Linux', 'Chrome', 1449324759),
(5, 6, 3232235620, 'Linux', 'Chrome', 1449327690),
(6, 6, 3232235620, 'Linux', 'Chrome', 1449370201),
(7, 6, 3232235620, 'Linux', 'Chrome', 1449371525),
(8, 6, 3232235620, 'Linux', 'Chrome', 1449371555),
(9, 6, 3232235620, 'Linux', 'Chrome', 1449371816),
(10, 6, 3232235620, 'Linux', 'Chrome', 1449371856),
(11, 6, 3232235620, 'Linux', 'Chrome', 1449418375),
(12, 6, 3232235620, 'Linux', 'Chrome', 1449418446),
(13, 6, 3232235620, 'Linux', 'Chrome', 1449440874),
(14, 1, 3232235620, 'Linux', 'Chrome', 1449668875),
(15, 6, 3232235620, 'Linux', 'Chrome', 1449756682),
(16, 6, 3232235620, 'Linux', 'Chrome', 1449757674),
(17, 6, 3232235620, 'Linux', 'Chrome', 1449758281),
(18, 6, 3232235620, 'Linux', 'Chrome', 1449758325),
(19, 6, 3232235620, 'Linux', 'Chrome', 1449758357),
(20, 6, 3232235620, 'Linux', 'Chrome', 1449818586),
(21, 6, 3232235620, 'Linux', 'Chrome', 1449857159),
(22, 6, 3232235620, 'Linux', 'Chrome', 1449871361),
(23, 6, 3232235620, 'Linux', 'Chrome', 1450017941),
(24, 6, 3232235620, 'Linux', 'Chrome', 1450087444),
(25, 6, 3232235620, 'Linux', 'Chrome', 1450195698);

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `login` varchar(128) NOT NULL,
  `passwd` varchar(128) NOT NULL,
  `session` varchar(128) DEFAULT NULL,
  `data` varchar(1024) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `users` (`id`, `login`, `passwd`, `session`, `data`) VALUES
(1, 'poiuty@lepus.su', '$2y$10$P8Pvp74CTd/sn6eBrLxL3uPAIkeLE1jP6zmDqEjoiqg/mhltMTKSO', 'ab6933a481ca85b5a4506210127be7c75db13e438a0ce5b17b29fa0b0d17310744ef0cdef5be10a662d0384bd462f7d1edc9a7fff3c8150be29d8c4a697932b2', '{"balance":500,"phone":null,"regDate":"1448450707","access":"1","lastIP":"127.0.0.1","apiKey":"ec374361f6e0d83147924890027c28e8"}'),
(6, 'admin@lepus.su', '$2y$10$/wOuC/bn/AAj3FBjZHclWeejBh6r.EXNukouKJdvwLQo1i7o1UEC.', '73bdf17b62e91861b5eda4b64648ce9d898aba91dc195fe8001c1229a1ab1729614b6f94b567d715970c42d1e23fe691126c9b661069ac7586ac65efef152422', '{"balance":0,"phone":null,"regDate":1449312798,"accsess":1,"lastIP":3232235620,"apiKey":"mrS3YXnfsAc42VkblJxVdXE40UGBVcwl"}');

ALTER TABLE `log_ip`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uid` (`uid`),
  ADD KEY `ip` (`ip`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mail` (`login`),
  ADD UNIQUE KEY `session` (`session`),
  ADD KEY `passwd` (`passwd`);

