-- 菜谱小程序数据库
-- 创建数据库
CREATE DATABASE IF NOT EXISTS caipu DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE caipu;

-- 管理员表
CREATE TABLE IF NOT EXISTS `admin` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '管理员ID',
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(255) NOT NULL COMMENT '密码',
  `nickname` varchar(50) DEFAULT '' COMMENT '昵称',
  `avatar` varchar(255) DEFAULT '' COMMENT '头像',
  `status` tinyint(1) DEFAULT 1 COMMENT '状态：0禁用 1启用',
  `last_login_time` int(11) DEFAULT 0 COMMENT '最后登录时间',
  `last_login_ip` varchar(50) DEFAULT '' COMMENT '最后登录IP',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员表';

-- 用户表
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `openid` varchar(100) DEFAULT '' COMMENT '微信openid',
  `unionid` varchar(100) DEFAULT '' COMMENT '微信unionid',
  `session_key` varchar(100) DEFAULT '' COMMENT '会话密钥',
  `nickname` varchar(100) DEFAULT '' COMMENT '昵称',
  `avatar` varchar(255) DEFAULT '' COMMENT '头像',
  `phone` varchar(20) DEFAULT '' COMMENT '手机号',
  `gender` tinyint(1) DEFAULT 0 COMMENT '性别：0未知 1男 2女',
  `status` tinyint(1) DEFAULT 1 COMMENT '状态：0禁用 1正常',
  `is_admin` tinyint(1) DEFAULT 0 COMMENT '是否管理员：0否 1是',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `openid` (`openid`),
  KEY `phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户表';

-- 菜谱分类表
CREATE TABLE IF NOT EXISTS `category` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '分类ID',
  `name` varchar(50) NOT NULL COMMENT '分类名称',
  `icon` varchar(255) DEFAULT '' COMMENT '分类图标',
  `color` varchar(20) DEFAULT '#FF6B6B' COMMENT '分类颜色',
  `sort` int(11) DEFAULT 0 COMMENT '排序',
  `status` tinyint(1) DEFAULT 1 COMMENT '状态：0隐藏 1显示',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `sort` (`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='菜谱分类表';

-- 菜谱表
CREATE TABLE IF NOT EXISTS `recipe` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '菜谱ID',
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '发布者用户ID',
  `category_id` int(11) UNSIGNED NOT NULL COMMENT '分类ID',
  `title` varchar(100) NOT NULL COMMENT '菜谱名称',
  `image` varchar(255) NOT NULL COMMENT '主图',
  `images` text COMMENT '图片列表，JSON格式',
  `description` varchar(500) DEFAULT '' COMMENT '简述',
  `materials` text COMMENT '所需食材，JSON格式',
  `steps` text COMMENT '制作步骤，JSON格式',
  `tips` varchar(500) DEFAULT '' COMMENT '小贴士',
  `suitable_people` varchar(255) DEFAULT '' COMMENT '适合人群',
  `cooking_time` varchar(50) DEFAULT '' COMMENT '烹饪时间',
  `difficulty` tinyint(1) DEFAULT 1 COMMENT '难度：1简单 2中等 3困难',
  `view_count` int(11) DEFAULT 0 COMMENT '浏览数',
  `like_count` int(11) DEFAULT 0 COMMENT '点赞数',
  `favorite_count` int(11) DEFAULT 0 COMMENT '收藏数',
  `is_daily` tinyint(1) DEFAULT 0 COMMENT '是否每日推荐：0否 1是',
  `status` tinyint(1) DEFAULT 1 COMMENT '状态：0待审核 1正常 2下架',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `category_id` (`category_id`),
  KEY `is_daily` (`is_daily`),
  KEY `status` (`status`),
  KEY `create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='菜谱表';

-- 点赞表
CREATE TABLE IF NOT EXISTS `like_record` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '点赞ID',
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '用户ID',
  `recipe_id` int(11) UNSIGNED NOT NULL COMMENT '菜谱ID',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_recipe` (`user_id`, `recipe_id`),
  KEY `recipe_id` (`recipe_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='点赞表';

-- 收藏表
CREATE TABLE IF NOT EXISTS `favorite` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '收藏ID',
  `user_id` int(11) UNSIGNED NOT NULL COMMENT '用户ID',
  `recipe_id` int(11) UNSIGNED NOT NULL COMMENT '菜谱ID',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_recipe` (`user_id`, `recipe_id`),
  KEY `recipe_id` (`recipe_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='收藏表';

-- 插入默认管理员账号（密码：admin123，需要用password_hash加密）
INSERT INTO `admin` (`username`, `password`, `nickname`, `status`, `create_time`, `update_time`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '管理员', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 插入默认分类数据
INSERT INTO `category` (`name`, `icon`, `color`, `sort`, `status`, `create_time`, `update_time`) VALUES
('川菜', '', '#FF6B6B', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('豫菜', '', '#4ECDC4', 2, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('粤菜', '', '#45B7D1', 3, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('湘菜', '', '#96CEB4', 4, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('鲁菜', '', '#FFEAA7', 5, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('苏菜', '', '#DDA0DD', 6, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('浙菜', '', '#87CEEB', 7, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('闽菜', '', '#FFA07A', 8, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('徽菜', '', '#98FB98', 9, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
('家常菜', '', '#FFB6C1', 10, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
