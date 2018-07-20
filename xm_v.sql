/*数据库变更*/
/*2018.5.22*/
ALTER TABLE `xm_v_course_videos` DROP COLUMN `title`,DROP COLUMN `images`,DROP COLUMN `creator`;
ALTER TABLE `xm_v_course` DROP COLUMN `introduce`,ADD COLUMN `summray`  text NULL AFTER `title`;
#新建视频库表
CREATE TABLE `xm_v_videos` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `summray` text COMMENT '简介',
  `title` varchar(255) NOT NULL COMMENT '视频标题',
  `src` varchar(150) NOT NULL DEFAULT '0' COMMENT '图片保存地址',
  `videoId` varchar(60) NOT NULL COMMENT '阿里视频ID',
  `creator` int(8) NOT NULL DEFAULT '0' COMMENT '创建者',
  `createdTime` int(11) DEFAULT '0',
  `updatedTime` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
