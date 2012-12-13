DROP TABLE IF EXISTS `t178_my_group`;
CREATE TABLE  `t178`.`t178_my_group` (
  `groupid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '公会ID',
  `fid` mediumint(8) unsigned DEFAULT NULL COMMENT 'discuz_fid',
  `uid` mediumint(8) unsigned DEFAULT NULL COMMENT 'discuz_uid',
  `capital` int(10) unsigned DEFAULT NULL COMMENT '公会财富值',
  `tcp` int(10) unsigned DEFAULT NULL COMMENT '公会TCP值',
  `level` smallint(5) unsigned DEFAULT NULL COMMENT '公会等级',
  `notice` text COMMENT '公会公告',
  PRIMARY KEY (`groupid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='公会表';

DROP TABLE IF EXISTS `t178_my_group_friend`;
CREATE TABLE  `t178`.`t178_my_group_friend` (
  `group_friendid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '公会友情ID',
  `group_id_a` mediumint(8) unsigned NOT NULL COMMENT '甲公会ID',
  `group_id_b` mediumint(8) unsigned NOT NULL COMMENT '已公会ID',
  PRIMARY KEY (`group_friendid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='友情公会表';

DROP TABLE IF EXISTS `t178_my_group_store`;
CREATE TABLE  `t178`.`t178_my_group_store` (
  `group_storeid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '公会商城ID',
  `store_name` varchar(64) NOT NULL COMMENT '公会商城名称',
  `groupid` mediumint(8) unsigned NOT NULL COMMENT '公会ID',
  PRIMARY KEY (`group_storeid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='公会商城表';

DROP TABLE IF EXISTS `t178_my_group_work`;
CREATE TABLE  `t178`.`t178_my_group_work` (
  `group_workid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '公会打工ID',
  `groupid` mediumint(8) unsigned NOT NULL COMMENT '公会ID',
  PRIMARY KEY (`group_workid`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='公会打工表';