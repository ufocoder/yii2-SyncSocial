CREATE TABLE `posts` (
  `id_post` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `content` varchar(256) NOT NULL,
  PRIMARY KEY (`id_post`)
) ENGINE=InnoDB AUTO_INCREMENT=356 DEFAULT CHARSET=utf8;

CREATE TABLE `sync_model` (
  `model_id` int(11) NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `service_id_author` bigint(20) DEFAULT NULL,
  `service_id_post` bigint(20) DEFAULT NULL,
  `service_url_post` varchar(255) NOT NULL,
  `time_created` int(11) DEFAULT NULL,
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;