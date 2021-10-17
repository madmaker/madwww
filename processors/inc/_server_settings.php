<?php
/** @noinspection SpellCheckingInspection */
define('mp_url','madwww.ru');
define('cloud_url','https://madwww.ru/');

define('staticcontent_url','https://static2.madwww.org/');

define('db_host', 'localhost');//mariadb for docker

define('db_user', 'root');

define('db_pass', 'qwerty');

//MADSMTP
define('madsmtp_port', '3033');
define('madsmtp_key','bf709005906087dc1256bb4449d8774d');
define('madsmtp_hash','d41d8cd98f00b204e9800998ecf8427e');

define('madsmtp_host_backend', 'node1.madwww.org');//host.docker.internal for docker local installation
define('madsmtp_host_frontend', 'node1.madwww.org');//node1.madwww.org.local for docker local installation

define('madsmtp_protocol','https');

//MADIMAP
define('madimap_port', '443');
define('madimap_key','bf709005906087dc1256bb4449d8774d');
define('madimap_hash','d41d8cd98f00b204e9800998ecf8427e');

define('madimap_host_backend', 'madimap.madwww.org');//host.docker.internal for docker local installation
define('madimap_host_frontend', 'madimap.madwww.org');//localhost for docker local installation


define('madimap_protocol','https');

//MADSMS
define('madsms_port','3034');
define('madsms_key','bf709005906087dc1256bb4449d8774d');
define('madsms_hash','d41d8cd98f00b204e9800998ecf8427e');

define('madsms_host','node1.madwww.org');

define('madsms_protocol','https');
