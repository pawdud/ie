CREATE TABLE IF NOT EXISTS `action` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'wiadomo - id',
  `id_project` char(40) NOT NULL COMMENT 'id projektu - klucz w sesji generowany po udanym zapisaniu obrazka',
  `id_action` char(40) NOT NULL COMMENT 'hash akcji (obroc, przytnij, itd) genrowany po wykonaniu kazdorazowej transformacji obrazka',
  `position` int(10) unsigned NOT NULL COMMENT 'numer akcji - przydatny przy cofaniu  np.: 1, 2, 3, 4, 5',
  `image` char(50) NOT NULL COMMENT 'nazwa obrazka, nazwa jest np.: dwwfsewfmmcd.jpg',
  `json_data` varchar(1000) NOT NULL COMMENT 'Dodatkowe dane w formacie json zwiazane z akcja np.: {"action" : "rotate"}',
  `created` datetime NOT NULL COMMENT 'czas utworzenia akcji',
  `updated` datetime NOT NULL COMMENT 'czas aktualizacji akcji (np.: przy cofaniu)',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;