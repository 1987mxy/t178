-- MySQL dump 10.13  Distrib 5.1.59, for Win32 (ia32)
--
-- Host: localhost    Database: t178
-- ------------------------------------------------------
-- Server version	5.1.59-community

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
-- Table structure for table `t178_my_group`
--

DROP TABLE IF EXISTS `t178_my_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t178_my_group` (
  `groupid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '公会ID',
  `fid` mediumint(8) unsigned DEFAULT NULL COMMENT 'discuz_fid',
  `uid` mediumint(8) unsigned DEFAULT NULL COMMENT 'discuz_uid',
  `capital` int(10) unsigned DEFAULT NULL COMMENT '公会财富值',
  `tcp` int(10) unsigned DEFAULT NULL COMMENT '公会TCP值',
  `level` smallint(5) unsigned DEFAULT NULL COMMENT '公会等级',
  `notice` text COMMENT '公会公告',
  PRIMARY KEY (`groupid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='公会表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t178_my_group`
--

LOCK TABLES `t178_my_group` WRITE;
/*!40000 ALTER TABLE `t178_my_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `t178_my_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t178_my_group_friend`
--

DROP TABLE IF EXISTS `t178_my_group_friend`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t178_my_group_friend` (
  `group_friendid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '公会友情ID',
  `group_id_a` mediumint(8) unsigned NOT NULL COMMENT '甲公会ID',
  `group_id_b` mediumint(8) unsigned NOT NULL COMMENT '乙公会ID',
  `del_flag` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '删除标记',
  PRIMARY KEY (`group_friendid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='友情公会表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t178_my_group_friend`
--

LOCK TABLES `t178_my_group_friend` WRITE;
/*!40000 ALTER TABLE `t178_my_group_friend` DISABLE KEYS */;
/*!40000 ALTER TABLE `t178_my_group_friend` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t178_my_group_game`
--

DROP TABLE IF EXISTS `t178_my_group_game`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t178_my_group_game` (
  `group_gameid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '公会入驻游戏ID',
  `groupid` mediumint(8) unsigned NOT NULL COMMENT '公会ID',
  `gameid` mediumint(8) unsigned NOT NULL COMMENT '游戏ID',
  `status` smallint(5) unsigned NOT NULL COMMENT '入驻状态',
  `join_time` int(10) unsigned DEFAULT NULL COMMENT '入驻时间',
  `apply_time` int(10) unsigned NOT NULL COMMENT '入驻申请时间',
  PRIMARY KEY (`group_gameid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=gbk ROW_FORMAT=DYNAMIC COMMENT='公会入驻游戏表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t178_my_group_game`
--

LOCK TABLES `t178_my_group_game` WRITE;
/*!40000 ALTER TABLE `t178_my_group_game` DISABLE KEYS */;
/*!40000 ALTER TABLE `t178_my_group_game` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t178_my_group_member`
--

DROP TABLE IF EXISTS `t178_my_group_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t178_my_group_member` (
  `group_memberid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '公会成员ID',
  `uid` mediumint(8) unsigned NOT NULL COMMENT 'discuz的uid',
  `username` char(15) NOT NULL COMMENT 'discuz的username',
  `groupid` mediumint(8) unsigned NOT NULL COMMENT '公会ID',
  `tcp` int(10) unsigned DEFAULT '0' COMMENT 'TCP值',
  `contributed` int(10) unsigned DEFAULT '0' COMMENT 'TCP贡献值',
  `referrer` mediumint(8) unsigned DEFAULT NULL COMMENT '推荐人ID',
  `signin_time` int(10) unsigned DEFAULT NULL COMMENT '签到时间',
  PRIMARY KEY (`group_memberid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='公会成员表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t178_my_group_member`
--

LOCK TABLES `t178_my_group_member` WRITE;
/*!40000 ALTER TABLE `t178_my_group_member` DISABLE KEYS */;
/*!40000 ALTER TABLE `t178_my_group_member` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t178_my_group_store`
--

DROP TABLE IF EXISTS `t178_my_group_store`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t178_my_group_store` (
  `group_storeid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '公会商城ID',
  `store_name` varchar(64) NOT NULL COMMENT '公会商城名称',
  `groupid` mediumint(8) unsigned NOT NULL COMMENT '公会ID',
  PRIMARY KEY (`group_storeid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='公会商城表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t178_my_group_store`
--

LOCK TABLES `t178_my_group_store` WRITE;
/*!40000 ALTER TABLE `t178_my_group_store` DISABLE KEYS */;
/*!40000 ALTER TABLE `t178_my_group_store` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t178_my_group_work`
--

DROP TABLE IF EXISTS `t178_my_group_work`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t178_my_group_work` (
  `group_workid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '公会打工ID',
  `groupid` mediumint(8) unsigned NOT NULL COMMENT '公会ID',
  PRIMARY KEY (`group_workid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='公会打工表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t178_my_group_work`
--

LOCK TABLES `t178_my_group_work` WRITE;
/*!40000 ALTER TABLE `t178_my_group_work` DISABLE KEYS */;
/*!40000 ALTER TABLE `t178_my_group_work` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t178_my_tcp_log`
--

DROP TABLE IF EXISTS `t178_my_tcp_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t178_my_tcp_log` (
  `tcp_logid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'TCP日志ID',
  `logger_type` varchar(16) NOT NULL COMMENT '日志记录者类型',
  `loggerid` mediumint(8) unsigned NOT NULL COMMENT '日志记录者ID',
  `time` int(10) unsigned NOT NULL COMMENT '日志记录时间',
  `content` text COMMENT '日志',
  `memo` text COMMENT '日志备注',
  PRIMARY KEY (`tcp_logid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='TCP日志表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `t178_my_tcp_log`
--

LOCK TABLES `t178_my_tcp_log` WRITE;
/*!40000 ALTER TABLE `t178_my_tcp_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `t178_my_tcp_log` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-12-17  2:02:00