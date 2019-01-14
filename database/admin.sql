-- MySQL dump 10.13  Distrib 5.7.22, for Linux (x86_64)
--
-- Host: localhost    Database: shop
-- ------------------------------------------------------
-- Server version	5.7.22-0ubuntu18.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `admin_menu`
--

LOCK TABLES `admin_menu` WRITE;
/*!40000 ALTER TABLE `admin_menu` DISABLE KEYS */;
INSERT INTO `admin_menu` VALUES (1,0,1,'首页','fa-bar-chart','/',NULL,NULL,'2019-01-14 08:24:17'),(2,0,2,'后台管理','fa-tasks',NULL,NULL,NULL,'2019-01-14 08:24:49'),(3,2,3,'用户','fa-users','auth/users',NULL,NULL,'2019-01-14 08:25:00'),(4,2,4,'角色','fa-user','auth/roles',NULL,NULL,'2019-01-14 08:25:11'),(5,2,5,'权限','fa-ban','auth/permissions',NULL,NULL,'2019-01-14 08:25:21'),(6,2,6,'菜单','fa-bars','auth/menu',NULL,NULL,'2019-01-14 08:25:37'),(7,2,7,'操作日志','fa-history','auth/logs',NULL,NULL,'2019-01-14 08:25:48'),(8,0,0,'订单','fa-bars','/orders',NULL,'2019-01-12 07:59:26','2019-01-12 07:59:26'),(9,0,0,'用户','fa-users','/users',NULL,'2019-01-12 07:59:57','2019-01-12 07:59:57'),(10,0,0,'产品','fa-cube','/products',NULL,'2019-01-12 08:00:20','2019-01-12 08:00:20'),(11,0,0,'优惠券','fa-dollar','/coupon_codes',NULL,'2019-01-12 08:00:57','2019-01-12 08:00:57');
/*!40000 ALTER TABLE `admin_menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_permissions`
--

LOCK TABLES `admin_permissions` WRITE;
/*!40000 ALTER TABLE `admin_permissions` DISABLE KEYS */;
INSERT INTO `admin_permissions` VALUES (1,'All permission','*','','*',NULL,NULL),(2,'Dashboard','dashboard','GET','/',NULL,NULL),(3,'Login','auth.login','','/auth/login\r\n/auth/logout',NULL,NULL),(4,'User setting','auth.setting','GET,PUT','/auth/setting',NULL,NULL),(5,'Auth management','auth.management','','/auth/roles\r\n/auth/permissions\r\n/auth/menu\r\n/auth/logs',NULL,NULL),(6,'用户管理','users','','/users*','2019-01-12 07:52:15','2019-01-12 07:52:30'),(7,'产品管理','products','','/products*','2019-01-12 07:52:57','2019-01-12 07:52:57'),(8,'优惠券管理','coupons','','/coupon_codes*','2019-01-12 07:55:32','2019-01-12 07:55:32'),(9,'订单管理','orders','','/orders*','2019-01-12 07:55:55','2019-01-12 07:55:55');
/*!40000 ALTER TABLE `admin_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_role_menu`
--

LOCK TABLES `admin_role_menu` WRITE;
/*!40000 ALTER TABLE `admin_role_menu` DISABLE KEYS */;
INSERT INTO `admin_role_menu` VALUES (1,2,NULL,NULL);
/*!40000 ALTER TABLE `admin_role_menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_role_permissions`
--

LOCK TABLES `admin_role_permissions` WRITE;
/*!40000 ALTER TABLE `admin_role_permissions` DISABLE KEYS */;
INSERT INTO `admin_role_permissions` VALUES (1,1,NULL,NULL),(2,6,NULL,NULL),(2,7,NULL,NULL),(2,8,NULL,NULL),(2,9,NULL,NULL),(2,2,NULL,NULL),(2,3,NULL,NULL);
/*!40000 ALTER TABLE `admin_role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_role_users`
--

LOCK TABLES `admin_role_users` WRITE;
/*!40000 ALTER TABLE `admin_role_users` DISABLE KEYS */;
INSERT INTO `admin_role_users` VALUES (1,1,NULL,NULL),(2,2,NULL,NULL);
/*!40000 ALTER TABLE `admin_role_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_roles`
--

LOCK TABLES `admin_roles` WRITE;
/*!40000 ALTER TABLE `admin_roles` DISABLE KEYS */;
INSERT INTO `admin_roles` VALUES (1,'Administrator','administrator','2019-01-12 07:49:21','2019-01-12 07:49:21'),(2,'运营人员','operator','2019-01-12 07:57:30','2019-01-12 07:57:30');
/*!40000 ALTER TABLE `admin_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_user_permissions`
--

LOCK TABLES `admin_user_permissions` WRITE;
/*!40000 ALTER TABLE `admin_user_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `admin_user_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `admin_users`
--

LOCK TABLES `admin_users` WRITE;
/*!40000 ALTER TABLE `admin_users` DISABLE KEYS */;
INSERT INTO `admin_users` VALUES (1,'admin','$2y$10$ernlR.rVWg9z636kSkZnaedltbq0eLF5zKRKQe7pPtVbblQNraVLW','Administrator',NULL,'yDOf14WHlgo93iFc8EcEJEG0aYofuFx1egF7dMYtuakpoStPHyH7QSd3vwOa','2019-01-12 07:49:21','2019-01-12 07:49:21'),(2,'operator','$2y$10$SunNeXmqoMn5qJFUBF8G8eAnUHDliQRbC3AU.ZzoLUepsNchcERk.','运营人员',NULL,'OTptFzsbuXNJXwnNelDtuOv5iHvRjC0TthshFsAWvqpNYpAGn2XWdxf0cw2C','2019-01-12 07:58:49','2019-01-12 07:58:49');
/*!40000 ALTER TABLE `admin_users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-01-14  2:00:53
