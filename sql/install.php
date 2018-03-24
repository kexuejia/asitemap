<?php


$sql = array();


$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'asitemap_conf` (
        `id_asitemap_conf` int(10) NOT NULL AUTO_INCREMENT,
        `param_name` varchar(50) NOT NULL,
        `param_value` varchar(3000) NOT NULL,
        `param_desc` text,
        PRIMARY KEY (`id_asitemap_conf`),
        UNIQUE KEY `idx_param_name` (`param_name`)
        )
        ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'asitemap_pages` (
        `id_asitemap_pages` int(10) NOT NULL AUTO_INCREMENT,
        `page_id` int(10),
        `page_name` varchar(50) NOT NULL,
        `page_type` varchar(50) NOT NULL,
        `page_link` varchar(100) NOT NULL,
        `page_priority` DECIMAL(2,1) NOT NULL,
        `page_frequency` varchar(20) NOT NULL,
        UNIQUE KEY `idx_page_name` (`page_name`),
        PRIMARY KEY (`id_asitemap_pages`)
        )
        ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8';

$sql[] = 'INSERT INTO '._DB_PREFIX_.'asitemap_conf
                  (param_name,param_value,param_desc) values ("sitemap_enabled","","Sitemap state")
                  ON DUPLICATE KEY UPDATE
                  param_desc = "Sitemap state"
                  ';

$sql[] = 'INSERT INTO '._DB_PREFIX_.'asitemap_conf
                  (param_name,param_value,param_desc) values ("asitemap_link","new_sitemap.xml","Sitemap link")
                  ON DUPLICATE KEY UPDATE
                  param_desc = "Sitemap link"

                  ';

$sql[] = 'INSERT INTO '._DB_PREFIX_.'asitemap_conf
                  (param_name,param_value,param_desc) values  ("p_p_enabled","","Product pages sitemap enabled")
                  ON DUPLICATE KEY UPDATE
                  param_desc = "Product pages sitemap enabled"
                  ';

$sql[] = 'INSERT INTO '._DB_PREFIX_.'asitemap_conf
                  (param_name,param_value,param_desc) values  ("p_p_priority","1.0","Product pages sitemap priority")
                  ON DUPLICATE KEY UPDATE
                  param_desc = "Product pages sitemap priority"
                  ';
$sql[] = 'INSERT INTO '._DB_PREFIX_.'asitemap_conf
                  (param_name,param_value,param_desc) values  ("p_p_changefreq","weekly","Product pages sitemap changes frequency")
                  ON DUPLICATE KEY UPDATE
                  param_desc = "Product pages sitemap changes frequency"
                  ';

$sql[] = 'INSERT INTO '._DB_PREFIX_.'asitemap_conf
                  (param_name,param_value,param_desc) values  ("i_enabled","","Images sitemap enabled")
                  ON DUPLICATE KEY UPDATE
                  param_desc = "Images sitemap enabled"
                  ';



?>
