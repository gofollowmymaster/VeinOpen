/*
Navicat MySQL Data Transfer

Source Server         : server.cuci.cc
Source Server Version : 50558
Source Host           : server.cuci.cc:3306
Source Database       : admin_v3

Target Server Type    : MYSQL
Target Server Version : 50558
File Encoding         : 65001

Date: 2018-05-04 11:40:19
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for system_auth
-- ----------------------------
DROP TABLE IF EXISTS `system_auth`;
CREATE TABLE `system_auth` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(20) NOT NULL COMMENT '权限名称',
  `status` tinyint(1) unsigned DEFAULT '1' COMMENT '状态(1:禁用,2:启用)',
  `sort` smallint(6) unsigned DEFAULT '0' COMMENT '排序权重',
  `desc` varchar(255) DEFAULT NULL COMMENT '备注说明',
  `create_by` bigint(11) unsigned DEFAULT '0' COMMENT '创建人',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `index_system_auth_title` (`title`) USING BTREE,
  KEY `index_system_auth_status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统权限表';

-- ----------------------------
-- Records of system_auth
-- ----------------------------

-- ----------------------------
-- Table structure for system_auth_node
-- ----------------------------
DROP TABLE IF EXISTS `system_auth_node`;
CREATE TABLE `system_auth_node` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `auth` bigint(20) unsigned DEFAULT NULL COMMENT '角色ID',
  `node` varchar(200) DEFAULT NULL COMMENT '节点路径',
  PRIMARY KEY (`id`),
  KEY `index_system_auth_auth` (`auth`) USING BTREE,
  KEY `index_system_auth_node` (`node`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统角色与节点绑定';

-- ----------------------------
-- Records of system_auth_node
-- ----------------------------

-- ----------------------------
-- Table structure for system_config
-- ----------------------------
DROP TABLE IF EXISTS `system_config`;
CREATE TABLE `system_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL COMMENT '配置编码',
  `value` varchar(500) DEFAULT NULL COMMENT '配置值',
  PRIMARY KEY (`id`),
  KEY `index_system_config_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8 COMMENT='系统参数配置';

-- ----------------------------
-- Records of system_config
-- ----------------------------
INSERT INTO `system_config` VALUES ('1', 'app_name', 'ThinkAdmin');
INSERT INTO `system_config` VALUES ('2', 'site_name', 'ThinkAdmin');
INSERT INTO `system_config` VALUES ('3', 'app_version', '3.0 dev');
INSERT INTO `system_config` VALUES ('4', 'site_copy', '©版权所有 2014-2018 楚才科技');
INSERT INTO `system_config` VALUES ('5', 'browser_icon', 'http://localhost/ThinkAdmin/static/upload/f47b8fe06e38ae99/08e8398da45583b9.png');
INSERT INTO `system_config` VALUES ('6', 'tongji_baidu_key', '');
INSERT INTO `system_config` VALUES ('7', 'miitbeian', '粤ICP备16006642号-2');
INSERT INTO `system_config` VALUES ('8', 'storage_type', 'local');
INSERT INTO `system_config` VALUES ('9', 'storage_local_exts', 'png,jpg,rar,doc,icon,mp4');
INSERT INTO `system_config` VALUES ('10', 'storage_qiniu_bucket', '');
INSERT INTO `system_config` VALUES ('11', 'storage_qiniu_domain', '');
INSERT INTO `system_config` VALUES ('12', 'storage_qiniu_access_key', '');
INSERT INTO `system_config` VALUES ('13', 'storage_qiniu_secret_key', '');
INSERT INTO `system_config` VALUES ('14', 'storage_oss_bucket', 'cuci');
INSERT INTO `system_config` VALUES ('15', 'storage_oss_endpoint', 'oss-cn-beijing.aliyuncs.com');
INSERT INTO `system_config` VALUES ('16', 'storage_oss_domain', 'cuci.oss-cn-beijing.aliyuncs.com');
INSERT INTO `system_config` VALUES ('17', 'storage_oss_keyid', '用你自己的吧');
INSERT INTO `system_config` VALUES ('18', 'storage_oss_secret', '用你自己的吧');
INSERT INTO `system_config` VALUES ('34', 'wechat_appid', 'wx60a43dd8161666d4');
INSERT INTO `system_config` VALUES ('35', 'wechat_appkey', '9890a0d7c91801a609d151099e95b61a');
INSERT INTO `system_config` VALUES ('36', 'storage_oss_is_https', 'http');
INSERT INTO `system_config` VALUES ('37', 'wechat_type', 'thr');
INSERT INTO `system_config` VALUES ('38', 'wechat_token', 'test');
INSERT INTO `system_config` VALUES ('39', 'wechat_appsecret', 'a041bec98ed015d52b99acea5c6a16ef');
INSERT INTO `system_config` VALUES ('40', 'wechat_encodingaeskey', 'BJIUzE0gqlWy0GxfPp4J1oPTBmOrNDIGPNav1YFH5Z5');
INSERT INTO `system_config` VALUES ('41', 'wechat_thr_appid', 'wx60a43dd8161666d4');
INSERT INTO `system_config` VALUES ('42', 'wechat_thr_appkey', '05db2aa335382c66ab56d69b1a9ad0ee');

-- ----------------------------
-- Table structure for system_log
-- ----------------------------
DROP TABLE IF EXISTS `system_log`;
CREATE TABLE `system_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ip` char(15) NOT NULL DEFAULT '' COMMENT '操作者IP地址',
  `node` char(200) NOT NULL DEFAULT '' COMMENT '当前操作节点',
  `username` varchar(32) NOT NULL DEFAULT '' COMMENT '操作人用户名',
  `action` varchar(200) NOT NULL DEFAULT '' COMMENT '操作行为',
  `content` text NOT NULL COMMENT '操作内容描述',
  `create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统操作日志表';

-- ----------------------------
-- Records of system_log
-- ----------------------------

-- ----------------------------
-- Table structure for system_menu
-- ----------------------------
DROP TABLE IF EXISTS `system_menu`;
CREATE TABLE `system_menu` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pid` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '父id',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '名称',
  `node` varchar(200) NOT NULL DEFAULT '' COMMENT '节点代码',
  `icon` varchar(100) NOT NULL DEFAULT '' COMMENT '菜单图标',
  `url` varchar(400) NOT NULL DEFAULT '' COMMENT '链接',
  `params` varchar(500) DEFAULT '' COMMENT '链接参数',
  `target` varchar(20) NOT NULL DEFAULT '_self' COMMENT '链接打开方式',
  `sort` int(11) unsigned DEFAULT '0' COMMENT '菜单排序',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态(0:禁用,1:启用)',
  `create_by` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '创建人',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `index_system_menu_node` (`node`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8 COMMENT='系统菜单表';

-- ----------------------------
-- Records of system_menu
-- ----------------------------
INSERT INTO `system_menu` VALUES ('1', '0', '系统设置', '', '', '#', '', '_self', '9000', '1', '10000', '2018-01-19 15:27:00');
INSERT INTO `system_menu` VALUES ('2', '10', '后台菜单', '', 'fa fa-leaf', 'admin/menu/index', '', '_self', '10', '1', '10000', '2018-01-19 15:27:17');
INSERT INTO `system_menu` VALUES ('3', '10', '系统参数', '', 'fa fa-modx', 'admin/config/index', '', '_self', '20', '1', '10000', '2018-01-19 15:27:57');
INSERT INTO `system_menu` VALUES ('4', '11', '访问授权', '', 'fa fa-group', 'admin/auth/index', '', '_self', '20', '1', '10000', '2018-01-22 11:13:02');
INSERT INTO `system_menu` VALUES ('5', '11', '用户管理', '', 'fa fa-user', 'admin/user/index', '', '_self', '10', '1', '0', '2018-01-23 12:15:12');
INSERT INTO `system_menu` VALUES ('6', '11', '访问节点', '', 'fa fa-fort-awesome', 'admin/node/index', '', '_self', '30', '1', '0', '2018-01-23 12:36:54');
INSERT INTO `system_menu` VALUES ('7', '0', '后台首页', '', '', 'admin/index/main', '', '_self', '1000', '1', '0', '2018-01-23 13:42:30');
INSERT INTO `system_menu` VALUES ('8', '16', '系统日志', '', 'fa fa-code', 'admin/log/index', '', '_self', '10', '1', '0', '2018-01-24 13:52:58');
INSERT INTO `system_menu` VALUES ('9', '10', '文件存储', '', 'fa fa-stop-circle', 'admin/config/file', '', '_self', '30', '1', '0', '2018-01-25 10:54:28');
INSERT INTO `system_menu` VALUES ('10', '1', '系统管理', '', '', '#', '', '_self', '200', '1', '0', '2018-01-25 18:14:28');
INSERT INTO `system_menu` VALUES ('11', '1', '访问权限', '', '', '#', '', '_self', '300', '1', '0', '2018-01-25 18:15:08');
INSERT INTO `system_menu` VALUES ('16', '1', '日志管理', '', '', '#', '', '_self', '400', '1', '0', '2018-02-10 16:31:15');
INSERT INTO `system_menu` VALUES ('17', '0', '微信管理', '', '', '#', '', '_self', '8000', '1', '0', '2018-03-06 14:42:49');
INSERT INTO `system_menu` VALUES ('18', '17', '公众号配置', '', '', '#', '', '_self', '0', '1', '0', '2018-03-06 14:43:05');
INSERT INTO `system_menu` VALUES ('19', '18', '微信授权绑定', '', 'fa fa-cog', 'wechat/config/index', '', '_self', '0', '1', '0', '2018-03-06 14:43:26');
INSERT INTO `system_menu` VALUES ('20', '18', '关注默认回复', '', 'fa fa-comment-o', 'wechat/keys/subscribe', '', '_self', '0', '1', '0', '2018-03-06 14:44:45');
INSERT INTO `system_menu` VALUES ('21', '18', '无反馈默认回复', '', 'fa fa-commenting', 'wechat/keys/defaults', '', '_self', '0', '1', '0', '2018-03-06 14:45:55');
INSERT INTO `system_menu` VALUES ('22', '18', '微信关键字管理', '', 'fa fa-hashtag', 'wechat/keys/index', '', '_self', '0', '1', '0', '2018-03-06 14:46:23');
INSERT INTO `system_menu` VALUES ('23', '17', '微信服务定制', '', '', '#', '', '_self', '0', '1', '0', '2018-03-06 14:47:11');
INSERT INTO `system_menu` VALUES ('24', '23', '微信菜单管理', '', 'fa fa-gg-circle', 'wechat/menu/index', '', '_self', '0', '1', '0', '2018-03-06 14:47:39');
INSERT INTO `system_menu` VALUES ('25', '23', '微信图文管理', '', 'fa fa-map-o', 'wechat/news/index', '', '_self', '0', '1', '0', '2018-03-06 14:48:14');
INSERT INTO `system_menu` VALUES ('26', '17', '微信粉丝管理', '', 'fa fa-user', '#', '', '_self', '0', '1', '0', '2018-03-06 14:48:33');
INSERT INTO `system_menu` VALUES ('27', '26', '微信粉丝列表', '', 'fa fa-users', 'wechat/fans/index', '', '_self', '20', '1', '0', '2018-03-06 14:49:04');
INSERT INTO `system_menu` VALUES ('28', '26', '微信黑名单管理', '', 'fa fa-user-times', 'wechat/fans_block/index', '', '_self', '30', '1', '0', '2018-03-06 14:49:22');
INSERT INTO `system_menu` VALUES ('29', '26', '微信标签管理', '', 'fa fa-tags', 'wechat/tags/index', '', '_self', '10', '1', '0', '2018-03-06 14:49:39');
INSERT INTO `system_menu` VALUES ('32', '0', '商城管理', '', '', '#', '', '_self', '2000', '1', '0', '2018-03-20 16:46:07');
INSERT INTO `system_menu` VALUES ('33', '32', '商品管理', '', '', '#', '', '_self', '0', '1', '0', '2018-03-20 16:46:21');
INSERT INTO `system_menu` VALUES ('34', '33', '产品管理', '', 'fa fa-modx', 'store/goods/index', '', '_self', '0', '1', '0', '2018-03-20 16:46:45');
INSERT INTO `system_menu` VALUES ('35', '33', '规格管理', '', 'fa fa-hashtag', 'store/goods_spec/index', '', '_self', '0', '1', '0', '2018-03-20 16:47:08');
INSERT INTO `system_menu` VALUES ('36', '33', '分类管理', '', 'fa fa-product-hunt', 'store/goods_cate/index', '', '_self', '0', '1', '0', '2018-03-20 16:47:50');
INSERT INTO `system_menu` VALUES ('37', '33', '品牌管理', '', 'fa fa-scribd', 'store/goods_brand/index', '', '_self', '0', '1', '0', '2018-03-20 16:48:05');
INSERT INTO `system_menu` VALUES ('38', '32', '订单管理', '', '', '#', '', '_self', '0', '1', '0', '2018-04-21 15:07:36');
INSERT INTO `system_menu` VALUES ('39', '38', '订单列表', '', 'fa fa-adjust', 'store/order/index', '', '_self', '0', '1', '0', '2018-04-21 15:07:54');
INSERT INTO `system_menu` VALUES ('40', '32', '商城配置', '', '', '#', '', '_self', '0', '1', '0', '2018-04-21 15:08:17');
INSERT INTO `system_menu` VALUES ('41', '40', '参数管理', '', '', '#', '', '_self', '0', '0', '0', '2018-04-21 15:08:25');
INSERT INTO `system_menu` VALUES ('42', '40', '快递公司', '', 'fa fa-mixcloud', 'store/express/index', '', '_self', '0', '1', '0', '2018-04-21 15:08:50');

-- ----------------------------
-- Table structure for system_node
-- ----------------------------
DROP TABLE IF EXISTS `system_node`;
CREATE TABLE `system_node` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `node` varchar(100) DEFAULT NULL COMMENT '节点代码',
  `title` varchar(500) DEFAULT NULL COMMENT '节点标题',
  `is_menu` tinyint(1) unsigned DEFAULT '0' COMMENT '是否可设置为菜单',
  `is_auth` tinyint(1) unsigned DEFAULT '1' COMMENT '是否启动RBAC权限控制',
  `is_login` tinyint(1) unsigned DEFAULT '1' COMMENT '是否启动登录控制',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `index_system_node_node` (`node`)
) ENGINE=InnoDB AUTO_INCREMENT=145 DEFAULT CHARSET=utf8 COMMENT='系统节点表';

-- ----------------------------
-- Records of system_node
-- ----------------------------
INSERT INTO `system_node` VALUES ('13', 'admin', '系统设置', '0', '1', '1', '2018-05-04 11:02:34');
INSERT INTO `system_node` VALUES ('14', 'admin/auth', '权限管理', '0', '1', '1', '2018-05-04 11:06:55');
INSERT INTO `system_node` VALUES ('15', 'admin/auth/index', '权限列表', '1', '1', '1', '2018-05-04 11:06:56');
INSERT INTO `system_node` VALUES ('16', 'admin/auth/apply', '权限配置', '0', '1', '1', '2018-05-04 11:06:56');
INSERT INTO `system_node` VALUES ('17', 'admin/auth/add', '添加权限', '0', '1', '1', '2018-05-04 11:06:56');
INSERT INTO `system_node` VALUES ('18', 'admin/auth/edit', '编辑权限', '0', '1', '1', '2018-05-04 11:06:56');
INSERT INTO `system_node` VALUES ('19', 'admin/auth/forbid', '禁用权限', '0', '1', '1', '2018-05-04 11:06:56');
INSERT INTO `system_node` VALUES ('20', 'admin/auth/resume', '启用权限', '0', '1', '1', '2018-05-04 11:06:56');
INSERT INTO `system_node` VALUES ('21', 'admin/auth/del', '删除权限', '0', '1', '1', '2018-05-04 11:06:56');
INSERT INTO `system_node` VALUES ('22', 'admin/config', '系统配置', '0', '1', '1', '2018-05-04 11:08:18');
INSERT INTO `system_node` VALUES ('23', 'admin/config/index', '系统参数', '1', '1', '1', '2018-05-04 11:08:25');
INSERT INTO `system_node` VALUES ('24', 'admin/config/file', '文件存储', '1', '1', '1', '2018-05-04 11:08:27');
INSERT INTO `system_node` VALUES ('25', 'admin/log', '日志管理', '0', '1', '1', '2018-05-04 11:08:43');
INSERT INTO `system_node` VALUES ('26', 'admin/log/index', '日志管理', '1', '1', '1', '2018-05-04 11:08:43');
INSERT INTO `system_node` VALUES ('28', 'admin/log/del', '日志删除', '0', '1', '1', '2018-05-04 11:08:43');
INSERT INTO `system_node` VALUES ('29', 'admin/menu', '系统菜单', '0', '1', '1', '2018-05-04 11:09:54');
INSERT INTO `system_node` VALUES ('30', 'admin/menu/index', '菜单列表', '1', '1', '1', '2018-05-04 11:09:54');
INSERT INTO `system_node` VALUES ('31', 'admin/menu/add', '添加菜单', '0', '1', '1', '2018-05-04 11:09:55');
INSERT INTO `system_node` VALUES ('32', 'admin/menu/edit', '编辑菜单', '0', '1', '1', '2018-05-04 11:09:55');
INSERT INTO `system_node` VALUES ('33', 'admin/menu/del', '删除菜单', '0', '1', '1', '2018-05-04 11:09:55');
INSERT INTO `system_node` VALUES ('34', 'admin/menu/forbid', '禁用菜单', '0', '1', '1', '2018-05-04 11:09:55');
INSERT INTO `system_node` VALUES ('35', 'admin/menu/resume', '启用菜单', '0', '1', '1', '2018-05-04 11:09:55');
INSERT INTO `system_node` VALUES ('36', 'admin/node', '节点管理', '0', '1', '1', '2018-05-04 11:10:20');
INSERT INTO `system_node` VALUES ('37', 'admin/node/index', '节点列表', '1', '1', '1', '2018-05-04 11:10:20');
INSERT INTO `system_node` VALUES ('38', 'admin/node/clear', '清理节点', '0', '1', '1', '2018-05-04 11:10:21');
INSERT INTO `system_node` VALUES ('39', 'admin/node/save', '更新节点', '0', '1', '1', '2018-05-04 11:10:21');
INSERT INTO `system_node` VALUES ('40', 'admin/user', '系统用户', '0', '1', '1', '2018-05-04 11:10:43');
INSERT INTO `system_node` VALUES ('41', 'admin/user/index', '用户列表', '1', '1', '1', '2018-05-04 11:10:43');
INSERT INTO `system_node` VALUES ('42', 'admin/user/auth', '用户授权', '0', '1', '1', '2018-05-04 11:10:43');
INSERT INTO `system_node` VALUES ('43', 'admin/user/add', '添加用户', '0', '1', '1', '2018-05-04 11:10:43');
INSERT INTO `system_node` VALUES ('44', 'admin/user/edit', '编辑用户', '0', '1', '1', '2018-05-04 11:10:43');
INSERT INTO `system_node` VALUES ('45', 'admin/user/pass', '修改密码', '0', '1', '1', '2018-05-04 11:10:43');
INSERT INTO `system_node` VALUES ('46', 'admin/user/del', '删除用户', '0', '1', '1', '2018-05-04 11:10:43');
INSERT INTO `system_node` VALUES ('47', 'admin/user/forbid', '禁用启用', '0', '1', '1', '2018-05-04 11:10:43');
INSERT INTO `system_node` VALUES ('48', 'admin/user/resume', '启用用户', '0', '1', '1', '2018-05-04 11:10:44');
INSERT INTO `system_node` VALUES ('49', 'store', '商城管理', '0', '1', '1', '2018-05-04 11:11:28');
INSERT INTO `system_node` VALUES ('50', 'store/express', '快递公司管理', '0', '1', '1', '2018-05-04 11:11:39');
INSERT INTO `system_node` VALUES ('51', 'store/express/index', '快递公司列表', '1', '1', '1', '2018-05-04 11:11:39');
INSERT INTO `system_node` VALUES ('52', 'store/express/add', '添加快递公司', '0', '1', '1', '2018-05-04 11:11:39');
INSERT INTO `system_node` VALUES ('53', 'store/express/edit', '编辑快递公司', '0', '1', '1', '2018-05-04 11:11:39');
INSERT INTO `system_node` VALUES ('54', 'store/express/del', '删除快递公司', '0', '1', '1', '2018-05-04 11:11:39');
INSERT INTO `system_node` VALUES ('55', 'store/express/forbid', '禁用快递公司', '0', '1', '1', '2018-05-04 11:11:39');
INSERT INTO `system_node` VALUES ('56', 'store/express/resume', '启用快递公司', '0', '1', '1', '2018-05-04 11:11:40');
INSERT INTO `system_node` VALUES ('57', 'store/order', '订单管理', '0', '1', '1', '2018-05-04 11:12:14');
INSERT INTO `system_node` VALUES ('58', 'store/order/index', '订单列表', '1', '1', '1', '2018-05-04 11:12:17');
INSERT INTO `system_node` VALUES ('59', 'store/order/address', '修改地址', '0', '1', '1', '2018-05-04 11:12:19');
INSERT INTO `system_node` VALUES ('76', 'wechat', '微信管理', '0', '1', '1', '2018-05-04 11:14:59');
INSERT INTO `system_node` VALUES ('78', 'wechat/config', '微信对接管理', '0', '1', '1', '2018-05-04 11:16:20');
INSERT INTO `system_node` VALUES ('79', 'wechat/config/index', '微信对接配置', '1', '1', '1', '2018-05-04 11:16:23');
INSERT INTO `system_node` VALUES ('80', 'wechat/fans', '微信粉丝管理', '0', '1', '1', '2018-05-04 11:16:31');
INSERT INTO `system_node` VALUES ('81', 'wechat/fans/index', '微信粉丝列表', '1', '1', '1', '2018-05-04 11:16:32');
INSERT INTO `system_node` VALUES ('82', 'wechat/fans/backadd', '微信粉丝拉黑', '0', '1', '1', '2018-05-04 11:16:32');
INSERT INTO `system_node` VALUES ('83', 'wechat/fans/tagset', '设置粉丝标签', '0', '1', '1', '2018-05-04 11:16:32');
INSERT INTO `system_node` VALUES ('84', 'wechat/fans/tagadd', '添加粉丝标签', '0', '1', '1', '2018-05-04 11:16:32');
INSERT INTO `system_node` VALUES ('85', 'wechat/fans/tagdel', '删除粉丝标签', '0', '1', '1', '2018-05-04 11:16:32');
INSERT INTO `system_node` VALUES ('86', 'wechat/fans/sync', '同步粉丝列表', '0', '1', '1', '2018-05-04 11:16:32');
INSERT INTO `system_node` VALUES ('87', 'wechat/fans_block', '粉丝黑名单管理', '0', '1', '1', '2018-05-04 11:17:25');
INSERT INTO `system_node` VALUES ('88', 'wechat/fans_block/index', '粉丝黑名单列表', '1', '1', '1', '2018-05-04 11:17:50');
INSERT INTO `system_node` VALUES ('89', 'wechat/fans_block/backdel', '移除粉丝黑名单', '0', '1', '1', '2018-05-04 11:17:51');
INSERT INTO `system_node` VALUES ('90', 'wechat/keys', '微信关键字', '0', '1', '1', '2018-05-04 11:18:09');
INSERT INTO `system_node` VALUES ('91', 'wechat/keys/index', '关键字列表', '1', '1', '1', '2018-05-04 11:18:09');
INSERT INTO `system_node` VALUES ('92', 'wechat/keys/add', '添加关键字', '0', '1', '1', '2018-05-04 11:18:09');
INSERT INTO `system_node` VALUES ('93', 'wechat/keys/edit', '编辑关键字', '0', '1', '1', '2018-05-04 11:18:09');
INSERT INTO `system_node` VALUES ('94', 'wechat/keys/del', '删除关键字', '0', '1', '1', '2018-05-04 11:18:09');
INSERT INTO `system_node` VALUES ('95', 'wechat/keys/forbid', '禁用关键字', '0', '1', '1', '2018-05-04 11:18:09');
INSERT INTO `system_node` VALUES ('96', 'wechat/keys/resume', '启用关键字', '0', '1', '1', '2018-05-04 11:18:09');
INSERT INTO `system_node` VALUES ('97', 'wechat/keys/subscribe', '关注回复', '1', '1', '1', '2018-05-04 11:18:09');
INSERT INTO `system_node` VALUES ('98', 'wechat/keys/defaults', '默认回复', '1', '1', '1', '2018-05-04 11:18:09');
INSERT INTO `system_node` VALUES ('99', 'wechat/menu', '微信菜单管理', '0', '1', '1', '2018-05-04 11:18:57');
INSERT INTO `system_node` VALUES ('100', 'wechat/menu/index', '微信菜单展示', '1', '1', '1', '2018-05-04 11:19:10');
INSERT INTO `system_node` VALUES ('101', 'wechat/menu/edit', '编辑微信菜单', '0', '1', '1', '2018-05-04 11:19:22');
INSERT INTO `system_node` VALUES ('102', 'wechat/menu/cancel', '取消微信菜单', '0', '1', '1', '2018-05-04 11:19:26');
INSERT INTO `system_node` VALUES ('103', 'wechat/news/index', '微信图文列表', '1', '1', '1', '2018-05-04 11:19:28');
INSERT INTO `system_node` VALUES ('104', 'wechat/news/select', '微信图文选择', '0', '1', '1', '2018-05-04 11:19:28');
INSERT INTO `system_node` VALUES ('105', 'wechat/news/image', '微信图片选择', '0', '1', '1', '2018-05-04 11:19:28');
INSERT INTO `system_node` VALUES ('106', 'wechat/news/add', '添加微信图文', '0', '1', '1', '2018-05-04 11:19:28');
INSERT INTO `system_node` VALUES ('107', 'wechat/news/edit', '编辑微信图文', '0', '1', '1', '2018-05-04 11:19:28');
INSERT INTO `system_node` VALUES ('108', 'wechat/news/del', '删除微信图文', '0', '1', '1', '2018-05-04 11:19:28');
INSERT INTO `system_node` VALUES ('109', 'wechat/news/push', '推送微信图文', '0', '1', '1', '2018-05-04 11:19:28');
INSERT INTO `system_node` VALUES ('110', 'wechat/news', '微信图文管理', '0', '1', '1', '2018-05-04 11:19:35');
INSERT INTO `system_node` VALUES ('111', 'wechat/tags', '微信粉丝标签管理', '0', '1', '1', '2018-05-04 11:20:28');
INSERT INTO `system_node` VALUES ('112', 'wechat/tags/index', '粉丝标签列表', '1', '1', '1', '2018-05-04 11:20:28');
INSERT INTO `system_node` VALUES ('113', 'wechat/tags/add', '添加粉丝标签', '0', '1', '1', '2018-05-04 11:20:28');
INSERT INTO `system_node` VALUES ('114', 'wechat/tags/edit', '编辑粉丝标签', '0', '1', '1', '2018-05-04 11:20:29');
INSERT INTO `system_node` VALUES ('115', 'wechat/tags/del', '删除粉丝标签', '0', '1', '1', '2018-05-04 11:20:29');
INSERT INTO `system_node` VALUES ('116', 'wechat/tags/sync', '同步粉丝标签', '0', '1', '1', '2018-05-04 11:20:29');
INSERT INTO `system_node` VALUES ('117', 'store/goods', '商品管理', '0', '1', '1', '2018-05-04 11:29:55');
INSERT INTO `system_node` VALUES ('118', 'store/goods/index', '商品列表', '1', '1', '1', '2018-05-04 11:29:56');
INSERT INTO `system_node` VALUES ('119', 'store/goods/add', '添加商品', '0', '1', '1', '2018-05-04 11:29:56');
INSERT INTO `system_node` VALUES ('120', 'store/goods/edit', '编辑商品', '0', '1', '1', '2018-05-04 11:29:56');
INSERT INTO `system_node` VALUES ('121', 'store/goods/del', '删除商品', '0', '1', '1', '2018-05-04 11:29:56');
INSERT INTO `system_node` VALUES ('122', 'store/goods/forbid', '下架商品', '0', '1', '1', '2018-05-04 11:29:56');
INSERT INTO `system_node` VALUES ('123', 'store/goods/resume', '上架商品', '0', '1', '1', '2018-05-04 11:29:57');
INSERT INTO `system_node` VALUES ('124', 'store/goods_brand', '商品品牌管理', '0', '1', '1', '2018-05-04 11:30:44');
INSERT INTO `system_node` VALUES ('125', 'store/goods_brand/index', '商品品牌列表', '1', '1', '1', '2018-05-04 11:30:52');
INSERT INTO `system_node` VALUES ('126', 'store/goods_brand/add', '添加商品品牌', '0', '1', '1', '2018-05-04 11:30:55');
INSERT INTO `system_node` VALUES ('127', 'store/goods_brand/edit', '编辑商品品牌', '0', '1', '1', '2018-05-04 11:30:56');
INSERT INTO `system_node` VALUES ('128', 'store/goods_brand/del', '删除商品品牌', '0', '1', '1', '2018-05-04 11:30:56');
INSERT INTO `system_node` VALUES ('129', 'store/goods_brand/forbid', '禁用商品品牌', '0', '1', '1', '2018-05-04 11:30:56');
INSERT INTO `system_node` VALUES ('130', 'store/goods_brand/resume', '启用商品品牌', '0', '1', '1', '2018-05-04 11:30:56');
INSERT INTO `system_node` VALUES ('131', 'store/goods_cate', '商品分类管理', '0', '1', '1', '2018-05-04 11:31:19');
INSERT INTO `system_node` VALUES ('132', 'store/goods_cate/index', '商品分类列表', '1', '1', '1', '2018-05-04 11:31:23');
INSERT INTO `system_node` VALUES ('133', 'store/goods_cate/add', '添加商品分类', '0', '1', '1', '2018-05-04 11:31:23');
INSERT INTO `system_node` VALUES ('134', 'store/goods_cate/edit', '编辑商品分类', '0', '1', '1', '2018-05-04 11:31:23');
INSERT INTO `system_node` VALUES ('135', 'store/goods_cate/del', '删除商品分类', '0', '1', '1', '2018-05-04 11:31:24');
INSERT INTO `system_node` VALUES ('136', 'store/goods_cate/forbid', '禁用商品分类', '0', '1', '1', '2018-05-04 11:31:24');
INSERT INTO `system_node` VALUES ('137', 'store/goods_cate/resume', '启用商品分类', '0', '1', '1', '2018-05-04 11:31:24');
INSERT INTO `system_node` VALUES ('138', 'store/goods_spec', '商品规格管理', '0', '1', '1', '2018-05-04 11:31:47');
INSERT INTO `system_node` VALUES ('139', 'store/goods_spec/index', '商品规格列表', '1', '1', '1', '2018-05-04 11:31:47');
INSERT INTO `system_node` VALUES ('140', 'store/goods_spec/add', '添加商品规格', '0', '1', '1', '2018-05-04 11:31:47');
INSERT INTO `system_node` VALUES ('141', 'store/goods_spec/edit', '编辑商品规格', '0', '1', '1', '2018-05-04 11:31:48');
INSERT INTO `system_node` VALUES ('142', 'store/goods_spec/del', '删除商品规格', '0', '1', '1', '2018-05-04 11:31:48');
INSERT INTO `system_node` VALUES ('143', 'store/goods_spec/forbid', '禁用商品规格', '0', '1', '1', '2018-05-04 11:31:48');
INSERT INTO `system_node` VALUES ('144', 'store/goods_spec/resume', '启用商品规格', '0', '1', '1', '2018-05-04 11:31:48');

-- ----------------------------
-- Table structure for system_sequence
-- ----------------------------
DROP TABLE IF EXISTS `system_sequence`;
CREATE TABLE `system_sequence` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `type` varchar(20) DEFAULT NULL COMMENT '序号类型',
  `sequence` char(50) NOT NULL COMMENT '序号值',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `index_system_sequence_unique` (`type`,`sequence`) USING BTREE,
  KEY `index_system_sequence_type` (`type`),
  KEY `index_system_sequence_number` (`sequence`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统序号表';

-- ----------------------------
-- Records of system_sequence
-- ----------------------------

-- ----------------------------
-- Table structure for system_user
-- ----------------------------
DROP TABLE IF EXISTS `system_user`;
CREATE TABLE `system_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL DEFAULT '' COMMENT '用户登录名',
  `password` char(32) NOT NULL DEFAULT '' COMMENT '用户登录密码',
  `qq` varchar(16) DEFAULT NULL COMMENT '联系QQ',
  `mail` varchar(32) DEFAULT NULL COMMENT '联系邮箱',
  `phone` varchar(16) DEFAULT NULL COMMENT '联系手机号',
  `desc` varchar(255) DEFAULT '' COMMENT '备注说明',
  `login_num` bigint(20) unsigned DEFAULT '0' COMMENT '登录次数',
  `login_at` datetime DEFAULT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '状态(0:禁用,1:启用)',
  `authorize` varchar(255) DEFAULT NULL,
  `is_deleted` tinyint(1) unsigned DEFAULT '0' COMMENT '删除状态(1:删除,0:未删)',
  `create_by` bigint(20) unsigned DEFAULT NULL COMMENT '创建人',
  `create_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `index_system_user_username` (`username`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=10001 DEFAULT CHARSET=utf8 COMMENT='系统用户表';

-- ----------------------------
-- Records of system_user
-- ----------------------------
INSERT INTO `system_user` VALUES ('10000', 'admin', '21232f297a57a5a743894a0e4a801fc3', '22222222', '', '', '', '22973', '2018-03-26 17:06:48', '1', '2,4', '0', null, '2015-11-13 15:14:22');

