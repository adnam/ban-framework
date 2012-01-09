CREATE TABLE IF NOT EXISTS `users` (
  `id` varchar(37) NOT NULL,
  `username` varchar(64) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(64) NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `posts` (
  `id` varchar(37) NOT NULL,
  `user_id` varchar(37) NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` TEXT NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `post_comments` (
  `id` varchar(37) NOT NULL,
  `user_id` varchar(37) NOT NULL,
  `post_id` varchar(37) NOT NULL,
  `comment` TEXT NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

