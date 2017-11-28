/* 管理用户表 */
DROP TABLE IF EXISTS [[PREFIX]]admin_user;
CREATE TABLE [[PREFIX]]admin_user(
    `id` tinyint(3) unsigned not null PRIMARY KEY auto_increment comment '主键',
    `admin_user` varchar(25) not null default '' comment '管理员用户名',
    `admin_pass` varchar(32) not null default '' comment '登录密码',
    `admin_name` varchar(32) not null default '' comment '管理员姓名',
    `admin_phone` varchar(15) not null default '' comment '管理员手机号',
    `role_id` tinyint(3) unsigned not null default 0 comment '角色id',
    `last_login` int(10) unsigned not null default 0 comment '上次登录时间',
    `status` tinyint(2) unsigned not null default 0 comment '状态 0-正常，1-禁用，9-删除',
    `add_time` int(10) unsigned not null default 0 comment '增加时间'
)charset = 'utf8' engine = innodb comment = '管理用户表';

/* 管理用户表 */
DROP TABLE IF EXISTS [[PREFIX]]admin_role;
CREATE TABLE [[PREFIX]]admin_role(
    `id` tinyint(3) unsigned not null PRIMARY KEY auto_increment comment '主键,角色id',
    `role_name` varchar(25) not null default '' comment '角色名称',
    `role_id` tinyint(3) unsigned not null default 0 comment '角色id',
    `parent_id` tinyint(3) unsigned not null default 0 comment '父id',
    `status` tinyint(2) unsigned not null default 0 comment '状态 0-正常，1-禁用，9-删除',
    `add_time` int(10) unsigned not null default 0 comment '增加时间'
)charset = 'utf8' engine = innodb comment = '管理用户表';

/* 添加超级管理员 */
INSERT INTO [[PREFIX]]admin_role(`role_name`,`role_id`,`parent_id`,`status`,`add_time`) values('超级管理员',1,0,0,unix_timestamp(now()));

/* 菜单表 */
DROP TABLE IF EXISTS [[PREFIX]]admin_menu;
CREATE TABLE [[PREFIX]]admin_menu(
    `id` tinyint(3) unsigned not null PRIMARY KEY auto_increment comment '主键',
    `name` varchar(50) not null default '' comment '菜单名称',
    `url` varchar(200) not null default '' comment '控制地址',
    `parent_id` tinyint(3) unsigned not null default 0 comment '父id',
    `is_left_menu` tinyint(3) unsigned not null default 0 comment '是否左侧菜单1-是，0-否',
    `status` tinyint(3) unsigned not null default 0 comment '状态 0-正常，1-禁用,8-测试,9-删除',
    `add_at` int(10) unsigned not null default 0 comment '添加时间'
)charset = 'utf8' engine = innodb comment = '菜单表';

/* 权限表 */
DROP TABLE IF EXISTS [[PREFIX]]admin_role_auth;
CREATE TABLE [[PREFIX]]admin_role_auth(
    `id` tinyint(3) not null PRIMARY KEY auto_increment comment '主键',
	`role_id` tinyint(3) unsigned not null default 0 comment '角色id',
	`role_auth` varchar(5000) not null default '' comment '角色规则-json格式',
    `update_at` int(10) unsigned not null default 0 comment '修改时间'
)charset = 'utf8' engine = innodb comment = '权限表';

/* 行为日志表 */
DROP TABLE IF EXISTS [[PREFIX]]admin_log;
CREATE TABLE [[PREFIX]]admin_log(
    `id` tinyint(3) not null PRIMARY KEY auto_increment comment '主键',
    `admin_id` tinyint(3) unsigned not null default 0 comment '管理员id',
    `view_name` varchar(50) not null default '' comment '访问名称',
    `view_url` varchar(200) not null default '' comment '访问地址',
    `view_at` int(10) unsigned not null default 0 comment '访问时间',
    `view_ip` char(20) not null default '' comment '访问ip'
)charset = 'utf8' engine = innodb comment = '后台日志表';

/* 短信验证码表 */
DROP TABLE IF EXISTS [[PREFIX]]sms_code;
CREATE TABLE [[PREFIX]]sms_code(
    `id` tinyint(3) not null PRIMARY KEY auto_increment comment '主键',
    `code` mediumint(8) unsigned not null default 0 comment '验证码code，最多8位',
    `phone` varchar(15) not null default '' comment '手机号',
    `expire_time` int(10) unsigned not null default 0 comment '过期时间',
    `status` tinyint(3) unsigned not null default 0 comment '状态，0-未使用，1已使用'
)charset = 'utf8' engine = innodb comment = '短信验证码表';

/* 全局设置表 */
DROP TABLE IF EXISTS [[PREFIX]]global_setting;
CREATE TABLE [[PREFIX]]global_setting(
	`id` tinyint(3) not null PRIMARY KEY auto_increment comment '主键',
	`key` varchar(50) not null default '' comment '配置key',
  `value` varchar(200) not null default '' comment '配置值',
  `comment` varchar(200) not null default '' comment '配置说明'
)charset = 'utf8' engine = innodb comment = '全局设置表';

/* 插入全局表初始数据 */
INSERT INTO [[PREFIX]]global_setting(`key`,`value`,`comment`) values
('log_open',1,'是否开启日志，1-是，0-否'),
('sms_verify',0,'后台登录短信验证，1-是，0-否'),
('sms_expire',3000,'短信验证码过期时间'),
('page_limit',15,'后台数据每页页数'),
('api_auth_open',0,'是否开启api权限验证，1-是，0-否');
