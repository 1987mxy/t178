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
  `fid` mediumint(8) unsigned NOT NULL COMMENT 'discuz_fid',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '公会状态',
  `capital` int(10) unsigned DEFAULT '0' COMMENT '公会财富值',
  `tcp` int(10) unsigned DEFAULT '0' COMMENT '公会TCP值',
  `level` smallint(5) unsigned DEFAULT '1' COMMENT '公会等级',
  `notice` text COMMENT '公会公告',
  `build_time` int(10) unsigned DEFAULT NULL COMMENT '建立时间',
  `apply_time` int(10) unsigned DEFAULT NULL COMMENT '申请时间',
  PRIMARY KEY (`groupid`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='公会表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `t178_my_group_relation`
--

DROP TABLE IF EXISTS `t178_my_group_relation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t178_my_group_relation` (
  `group_relationid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '公会关系ID',
  `group_id_a` mediumint(8) unsigned NOT NULL COMMENT '甲公会ID',
  `group_id_b` mediumint(8) unsigned NOT NULL COMMENT '乙公会ID',
  `relation` tinyint(1) unsigned NOT NULL COMMENT '公会关系',
  `del_flag` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '删除标记',
  PRIMARY KEY (`group_relationid`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='公会关系表';
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `game_serverid` int(4) unsigned NOT NULL COMMENT '游戏服务器ID',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '入驻状态',
  `join_time` int(10) unsigned DEFAULT NULL COMMENT '入驻时间',
  `apply_time` int(10) unsigned NOT NULL COMMENT '入驻申请时间',
  PRIMARY KEY (`group_gameid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=gbk ROW_FORMAT=DYNAMIC COMMENT='公会入驻游戏表';
/*!40101 SET character_set_client = @saved_cs_client */;

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
  `capital` decimal(7,2) DEFAULT '0.00' COMMENT '财富值',
  `contributed` int(10) unsigned DEFAULT '0' COMMENT 'TCP贡献值',
  `referrer` mediumint(8) unsigned DEFAULT NULL COMMENT '推荐人ID',
  PRIMARY KEY (`group_memberid`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='公会成员表';
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='公会商城表';
/*!40101 SET character_set_client = @saved_cs_client */;

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
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='公会打工表';
/*!40101 SET character_set_client = @saved_cs_client */;

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
-- Table structure for table `t178_my_group_signing`
--

DROP TABLE IF EXISTS `t178_my_group_signing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t178_my_group_signing` (
  `group_signingid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '公会签到ID',
  `groupid` int(10) unsigned NOT NULL COMMENT '公会ID',
  `uid` mediumint(8) unsigned NOT NULL COMMENT 'Discuz的uid',
  `date` date NOT NULL COMMENT '签到日期',
  PRIMARY KEY (`group_signingid`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='公会签到表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `t178_my_group_member_game`
--

DROP TABLE IF EXISTS `t178_my_group_member_game`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `t178_my_group_member_game` (
  `group_member_gameid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '公会会员游戏入驻ID',
  `uid` mediumint(8) unsigned NOT NULL COMMENT 'Discuz的uid',
  `group_gameid` mediumint(8) unsigned NOT NULL COMMENT '公会游戏ID',
  `gruopid` mediumint(8) unsigned NOT NULL COMMENT '公会ID',
  `game_serverid` mediumint(8) unsigned NOT NULL COMMENT '游戏服务器ID',
  `join_time` int(10) unsigned NOT NULL COMMENT '入驻时间',
  PRIMARY KEY (`group_member_gameid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='公会会员游戏入驻表';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2013-01-08 23:19:34
