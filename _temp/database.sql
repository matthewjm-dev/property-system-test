/*
SQLyog Professional v12.12 (64 bit)
MySQL - 5.7.24 : Database - schema_info
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `ips_cms_admin_config_field` */

DROP TABLE IF EXISTS `ips_cms_admin_config_field`;

CREATE TABLE `ips_cms_admin_config_field` (
  `acffid` int(11) NOT NULL AUTO_INCREMENT,
  `acfgid` int(11) DEFAULT NULL,
  `position` int(11) DEFAULT NULL,
  `dbslug` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `options` text,
  `required` int(11) DEFAULT NULL,
  `value` text,
  PRIMARY KEY (`acffid`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

/*Data for the table `ips_cms_admin_config_field` */

insert  into `ips_cms_admin_config_field`(`acffid`,`acfgid`,`position`,`dbslug`,`title`,`type`,`options`,`required`,`value`) values (1,2,0,'site_logo','Site Logo','image_picker',NULL,NULL,'5'),(2,2,0,'home_page_id','Home Page','int','',1,'1'),(3,1,0,'contact_email','Contact Email Address','email',NULL,0,'enquiries@inphasesolutions.com'),(4,1,0,'contact_phone','Contact Phone Number','text',NULL,0,'010101010101'),(5,6,0,'contact_fom_subject','Admin Email Subject','text','0',0,NULL),(6,6,0,'contact_form_recipients','Admin Recipients','text',NULL,0,'admin@matdragon.com'),(7,6,0,'contact_form_message','Success Message','editor',NULL,0,'<p>Thank you for contacting us, we&#39;ll get back to you as soon as possible!</p>\r\n');

/*Table structure for table `ips_cms_admin_config_group` */

DROP TABLE IF EXISTS `ips_cms_admin_config_group`;

CREATE TABLE `ips_cms_admin_config_group` (
  `acfgid` int(11) NOT NULL AUTO_INCREMENT,
  `position` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '0',
  `level` int(11) NOT NULL DEFAULT '0',
  `icon` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`acfgid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

/*Data for the table `ips_cms_admin_config_group` */

insert  into `ips_cms_admin_config_group`(`acfgid`,`position`,`title`,`level`,`icon`) values (1,0,'General',2,'fas fa-cog'),(2,0,'Site Settings',2,'fas fa-wrench'),(3,0,'Developer Settings',1,'fas fa-toolbox'),(4,0,'User Settings',1,''),(6,0,'Contact Form Settings',2,NULL);

/*Table structure for table `ips_cms_admin_file` */

DROP TABLE IF EXISTS `ips_cms_admin_file`;

CREATE TABLE `ips_cms_admin_file` (
  `afid` int(11) NOT NULL AUTO_INCREMENT,
  `created` int(11) DEFAULT NULL,
  `modified` int(11) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `user` int(11) DEFAULT NULL,
  `basename` varchar(255) DEFAULT NULL,
  `extension` varchar(50) DEFAULT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `alt` varchar(255) DEFAULT NULL,
  `img_width` int(11) DEFAULT NULL,
  `img_height` int(11) DEFAULT NULL,
  `size` int(11) DEFAULT NULL,
  PRIMARY KEY (`afid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Data for the table `ips_cms_admin_file` */

/*Table structure for table `ips_cms_admin_log` */

DROP TABLE IF EXISTS `ips_cms_admin_log`;

CREATE TABLE `ips_cms_admin_log` (
  `alid` int(11) NOT NULL AUTO_INCREMENT,
  `created` int(11) NOT NULL DEFAULT '0',
  `user` int(11) NOT NULL DEFAULT '0',
  `title` varchar(50) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`alid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

/*Data for the table `ips_cms_admin_log` */

insert  into `ips_cms_admin_log`(`alid`,`created`,`user`,`title`,`content`) values (1,1563284637,1,'Updated Module item','Page has been successfully updated!'),(2,1563284830,1,'Deleted Field','Deleted Field item - field_mfid: 22 ( Files ) mid: 7');

/*Table structure for table `ips_cms_admin_module` */

DROP TABLE IF EXISTS `ips_cms_admin_module`;

CREATE TABLE `ips_cms_admin_module` (
  `mid` int(11) NOT NULL AUTO_INCREMENT,
  `created` int(11) DEFAULT NULL,
  `modified` int(11) DEFAULT NULL,
  `parent` int(11) NOT NULL DEFAULT '0',
  `slug` varchar(255) DEFAULT NULL,
  `dbslug` varchar(255) DEFAULT NULL,
  `pkey` varchar(50) DEFAULT NULL,
  `level` int(11) DEFAULT '1',
  `title` text,
  `title_single` varchar(255) DEFAULT NULL,
  `description` text,
  `icon` text,
  PRIMARY KEY (`mid`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `dbslug` (`dbslug`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1;

/*Data for the table `ips_cms_admin_module` */

insert  into `ips_cms_admin_module`(`mid`,`created`,`modified`,`parent`,`slug`,`dbslug`,`pkey`,`level`,`title`,`title_single`,`description`,`icon`) values (1,NULL,NULL,0,'navigation','navigation','navid',1,'Navigation','Navigation','These are your navigation items that can be configured to link to a page on the site or to a cusom URL. Each item can be placed into a Navigation group which appear in specific locations.','fas fa-tags'),(2,NULL,NULL,1,'navigation-groups','navigation_groups','navgrpid',1,'Navigation Groups','Navigation Group','Your navigation groups are collections of navigation items which are displayed in specific locations.',''),(3,NULL,1559575618,0,'pages','page','pageid',4,'Pages','Page',NULL,'fas fa-book-open'),(4,1559128208,1559128208,0,'contact-form-submissions','contact_form_submissions','cfsid',1,'Contact Form Submissions','Contact Form Submission',NULL,'fas fa-address-book');

/*Table structure for table `ips_cms_admin_module_field` */

DROP TABLE IF EXISTS `ips_cms_admin_module_field`;

CREATE TABLE `ips_cms_admin_module_field` (
  `mfid` int(11) NOT NULL AUTO_INCREMENT,
  `created` int(11) DEFAULT NULL,
  `modified` int(11) DEFAULT NULL,
  `dbslug` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `position` int(11) DEFAULT NULL,
  `mid` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `default` text,
  `placeholder` text,
  `placeholder_selectable` tinyint(1) DEFAULT NULL,
  `options` text,
  `link` int(11) DEFAULT NULL,
  `link_field` int(11) DEFAULT NULL,
  `item_link` int(11) DEFAULT NULL,
  `show_list` int(11) DEFAULT NULL,
  `required` int(11) DEFAULT NULL,
  `searchable` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`mfid`)
) ENGINE=MyISAM AUTO_INCREMENT=53 DEFAULT CHARSET=latin1;

/*Data for the table `ips_cms_admin_module_field` */

insert  into `ips_cms_admin_module_field`(`mfid`,`created`,`modified`,`dbslug`,`title`,`description`,`position`,`mid`,`type`,`default`,`placeholder`,`placeholder_selectable`,`options`,`link`,`link_field`,`item_link`,`show_list`,`required`,`searchable`) values (1,1559313863,1559313863,'title','Title','Title',1,1,'text',NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL),(4,1559313863,1559313863,'slug','Slug','',0,3,'text','','',0,NULL,NULL,NULL,0,1,1,NULL),(5,1559313863,1559313863,'content','Content','test',2,3,'editor','','',0,NULL,NULL,NULL,0,0,0,NULL),(10,1559313863,1559313863,'url','Url','',0,1,'text','','',NULL,NULL,NULL,NULL,0,1,0,NULL),(24,1559313863,1559313848,'page','Page',NULL,0,1,'linkselect','0','No Page',1,NULL,3,0,0,1,0,NULL),(31,1558366797,1559313863,'group','Group',NULL,0,1,'linkselect',NULL,NULL,0,NULL,2,0,0,1,1,NULL),(32,1558367905,1558367905,'dbslug','DBSlug',NULL,0,2,'text',NULL,NULL,NULL,NULL,NULL,NULL,0,1,NULL,NULL),(43,1559660475,1560507877,'name','Name',NULL,0,4,'text',NULL,'Enter your name',0,NULL,NULL,NULL,0,1,1,NULL),(44,1559660507,1560507896,'email_address','Email Address',NULL,0,4,'email',NULL,'Enter your email address',0,NULL,NULL,NULL,0,1,1,NULL),(45,1559660528,1560507986,'phone_number','Phone Number',NULL,0,4,'text',NULL,'Enter your phone number',0,NULL,NULL,NULL,0,0,0,NULL),(47,1559661299,1560507966,'enquiry_type','Enquiry Type',NULL,0,4,'select',NULL,NULL,0,'a:3:{i:0;a:2:{s:5:\"value\";s:1:\"1\";s:4:\"text\";s:13:\"General Query\";}i:1;a:2:{s:5:\"value\";s:1:\"2\";s:4:\"text\";s:27:\"Quote / Pricing Information\";}i:2;a:2:{s:5:\"value\";s:1:\"3\";s:4:\"text\";s:13:\"Support Query\";}}',NULL,NULL,0,0,0,NULL),(48,1559661322,1560508003,'enquiry','Enquiry',NULL,0,4,'textarea',NULL,'Enter your enquiry here',0,NULL,NULL,NULL,0,0,0,NULL),(49,1559749441,1559749441,'contact_email_text','Contact Email Text',NULL,0,3,'textarea',NULL,NULL,0,NULL,NULL,NULL,3,0,0,NULL),(50,1559749463,1559749463,'contact_phone_text','Contact Phone Text',NULL,0,3,'textarea',NULL,NULL,0,NULL,NULL,NULL,3,0,0,NULL),(51,1560508191,1560511309,'consent','Consent',NULL,0,4,'check',NULL,NULL,0,'a:1:{i:0;a:2:{s:5:\"value\";s:1:\"1\";s:4:\"text\";s:244:\"Do you consent to us storing your data in order to effectively respond to your query? Please read our <a href=\"/terms-and-conditions\" target=\"_blank\">terms & conditions</a> and <a href=\"/privacy-statement\" target=\"_blank\">privacy statement</a>.\";}}',NULL,NULL,0,0,1,NULL);

/*Table structure for table `ips_cms_admin_user` */

DROP TABLE IF EXISTS `ips_cms_admin_user`;

CREATE TABLE `ips_cms_admin_user` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `created` int(11) DEFAULT NULL,
  `modified` int(11) DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `salt` text NOT NULL,
  `password` text NOT NULL,
  `level` int(11) NOT NULL DEFAULT '0',
  `reset` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

/*Data for the table `ips_cms_admin_user` */

insert  into `ips_cms_admin_user`(`uid`,`created`,`modified`,`username`,`email`,`salt`,`password`,`level`,`reset`) values (1,NULL,NULL,'admin','admin@matdragon.com','sadb9su2d8fb2e8fh29hf9h2poslsk','beb9d048116798f36ac9080249df3fe75b458c04655b863d568b7555a402d9235fdcce432fcd49fb369eb8a7fc2eaf8504a06b696668619ac26eaa72073f28b0',1,''),(2,NULL,NULL,'tester','test@matdragon.com','48cc681b53a0e726f3ad1d837ca23ad9','bb3cc276aa01de6d01dd49df804f216454ae8ee911a3e4024f2ad10843ef227a7850a09115eb543b312e16d575632f3ddf28ba2c30bc4f69b656dead5cae5599',2,NULL);

/*Table structure for table `ips_cms_contact_form_submissions` */

DROP TABLE IF EXISTS `ips_cms_contact_form_submissions`;

CREATE TABLE `ips_cms_contact_form_submissions` (
  `cfsid` int(11) NOT NULL AUTO_INCREMENT,
  `created` int(11) DEFAULT NULL,
  `modified` int(11) DEFAULT NULL,
  `live` tinyint(1) DEFAULT '0',
  `removed` tinyint(1) DEFAULT '0',
  `locked` tinyint(1) DEFAULT '0',
  `position` int(11) DEFAULT '0',
  `title` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email_address` varchar(255) DEFAULT NULL,
  `phone_number` varchar(255) DEFAULT NULL,
  `enquiry_type` text,
  `enquiry` text,
  `consent` text,
  PRIMARY KEY (`cfsid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Data for the table `ips_cms_contact_form_submissions` */

/*Table structure for table `ips_cms_navigation` */

DROP TABLE IF EXISTS `ips_cms_navigation`;

CREATE TABLE `ips_cms_navigation` (
  `navid` int(11) NOT NULL AUTO_INCREMENT,
  `created` int(11) DEFAULT NULL,
  `modified` int(11) DEFAULT NULL,
  `live` tinyint(1) DEFAULT NULL,
  `removed` tinyint(1) DEFAULT NULL,
  `locked` tinyint(1) DEFAULT NULL,
  `position` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `page` varchar(255) DEFAULT NULL,
  `group` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`navid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

/*Data for the table `ips_cms_navigation` */

insert  into `ips_cms_navigation`(`navid`,`created`,`modified`,`live`,`removed`,`locked`,`position`,`title`,`url`,`page`,`group`) values (1,1551958245,1558366828,1,0,1,NULL,'Home','/','1','1'),(2,1552063683,1558367041,1,0,NULL,NULL,'About Us',NULL,'2','1'),(3,1552063780,1558367070,1,0,NULL,NULL,'Contact',NULL,'3','1'),(4,1560510366,1560510366,1,0,0,0,'Terms & Conditions',NULL,'7','2'),(5,1560510382,1560510382,1,0,0,0,'Privacy Statement',NULL,'8','2');

/*Table structure for table `ips_cms_navigation_groups` */

DROP TABLE IF EXISTS `ips_cms_navigation_groups`;

CREATE TABLE `ips_cms_navigation_groups` (
  `navgrpid` int(11) NOT NULL AUTO_INCREMENT,
  `created` int(11) DEFAULT NULL,
  `modified` int(11) DEFAULT NULL,
  `live` tinyint(1) DEFAULT NULL,
  `removed` tinyint(1) DEFAULT NULL,
  `locked` tinyint(1) DEFAULT NULL,
  `position` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `dbslug` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`navgrpid`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

/*Data for the table `ips_cms_navigation_groups` */

insert  into `ips_cms_navigation_groups`(`navgrpid`,`created`,`modified`,`live`,`removed`,`locked`,`position`,`title`,`dbslug`) values (1,1558366725,1558367922,1,0,1,NULL,'Header Navigation','header_navigation'),(2,1558366745,1558367936,1,0,1,NULL,'Footer Navigation','footer_navigation');

/*Table structure for table `ips_cms_page` */

DROP TABLE IF EXISTS `ips_cms_page`;

CREATE TABLE `ips_cms_page` (
  `pageid` int(11) NOT NULL AUTO_INCREMENT,
  `created` int(11) DEFAULT NULL,
  `modified` int(11) DEFAULT NULL,
  `live` tinyint(1) DEFAULT NULL,
  `removed` tinyint(1) DEFAULT NULL,
  `locked` tinyint(1) DEFAULT NULL,
  `position` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `content` text,
  `contact_email_text` text,
  `contact_phone_text` text,
  PRIMARY KEY (`pageid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

/*Data for the table `ips_cms_page` */

insert  into `ips_cms_page`(`pageid`,`created`,`modified`,`live`,`removed`,`locked`,`position`,`title`,`slug`,`content`,`contact_email_text`,`contact_phone_text`) values (1,1553516262,1563284637,1,0,1,0,'Home Page','home-page','',NULL,NULL),(2,1553533070,1560328187,1,0,1,0,'About Us','about-us','<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis eu mauris in purus lobortis maximus. Nullam eget pharetra orci. Donec dapibus eu leo sed elementum. Maecenas vestibulum lacinia placerat. Phasellus ipsum lorem, pretium quis varius ac, dictum in nibh. Proin auctor, massa in ornare facilisis, risus ante pretium purus, eget placerat lorem velit id sem. Phasellus augue felis, luctus id ullamcorper eget, tincidunt non mauris. Mauris ac justo eget enim auctor facilisis nec id erat.</p>\r\n\r\n<p>Mauris sed ante nec eros commodo fermentum. Phasellus rhoncus interdum tellus maximus ultricies. Nullam id ligula ac ipsum malesuada laoreet vel id diam. Nunc sem purus, porta vitae sodales ut, viverra nec lectus. Aliquam erat volutpat. Ut felis orci, porta ut metus id, pulvinar maximus sapien. Phasellus orci quam, finibus in libero sed, tincidunt pretium lacus. Maecenas pulvinar ante vel diam tincidunt interdum ac ut lectus. Praesent nibh nulla, vestibulum ut ultricies at, dapibus in tortor. Phasellus semper diam consectetur lobortis pretium.</p>\r\n',NULL,NULL),(3,1556702209,1559749980,1,0,1,0,'Contact','contact','<p>Nulla magna massa, convallis nec libero porta, aliquam vehicula tellus. Interdum et malesuada fames ac ante ipsum primis in faucibus. Sed in lacus at metus scelerisque eleifend. Maecenas vitae ipsum enim. Phasellus aliquam ante ut auctor accumsan. Suspendisse nec odio nisl.</p>\r\n',NULL,NULL),(4,1560510287,1560510287,1,0,1,0,'Terms & Conditions','terms-and-conditions',NULL,NULL,NULL),(5,1560510317,1560510317,1,0,1,0,'Privacy Statement','privacy-statement',NULL,NULL,NULL);

/*Table structure for table `ips_cms_routes` */

DROP TABLE IF EXISTS `ips_cms_routes`;

CREATE TABLE `ips_cms_routes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uri` text NOT NULL,
  `route` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Data for the table `ips_cms_routes` */

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
