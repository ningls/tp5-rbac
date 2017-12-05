/* 管理用户表 */
DROP TABLE IF EXISTS [[PREFIX]]admin_user;
CREATE TABLE [[PREFIX]]admin_user(
    `id` tinyint(3) unsigned not null PRIMARY KEY auto_increment comment '主键',
    `admin_user` varchar(25) not null default '' comment '管理员用户名',
    `admin_pass` varchar(32) not null default '' comment '登录密码',
    `admin_name` varchar(32) not null default '' comment '管理员姓名',
    `admin_phone` varchar(15) not null default '' comment '管理员手机号',
    `role_id` tinyint(3) unsigned not null default 0 comment '角色id',
    `create_user_id` tinyint(3) unsigned not null default 0 comment '创建人id',
    `last_login` int(10) unsigned not null default 0 comment '上次登录时间',
    `status` tinyint(2) unsigned not null default 0 comment '状态 0-正常，1-禁用，9-删除',
    `add_time` int(10) unsigned not null default 0 comment '增加时间',
    unique `admin_user`(`admin_user`),
    unique `admin_phone`(`admin_phone`)
)charset = 'utf8' engine = innodb comment = '管理用户表';

/* 管理角色表 */
DROP TABLE IF EXISTS [[PREFIX]]admin_role;
CREATE TABLE [[PREFIX]]admin_role(
    `id` tinyint(3) unsigned not null PRIMARY KEY auto_increment comment '主键,角色id',
    `role_name` varchar(25) not null default '' comment '角色名称',
    `role_id` tinyint(3) unsigned not null default 0 comment '角色id',
    `parent_id` tinyint(3) unsigned not null default 0 comment '父id',
    `create_user_id` tinyint(3) unsigned not null default 0 comment '创建人id',
    `status` tinyint(2) unsigned not null default 0 comment '状态 0-正常，1-禁用，9-删除',
    `add_time` int(10) unsigned not null default 0 comment '增加时间',
    key `role`(`role_id`),
    key `add_time`(`add_time`)
)charset = 'utf8' engine = innodb comment = '管理用户表';

/* 添加超级管理员 */
INSERT INTO [[PREFIX]]admin_role(`role_name`,`role_id`,`parent_id`,`create_user_id`,`status`,`add_time`) values('超级管理员',1,0,1,0,unix_timestamp(now()));

/* 菜单表 */
DROP TABLE IF EXISTS [[PREFIX]]admin_menu;
CREATE TABLE [[PREFIX]]admin_menu(
    `id` tinyint(3) unsigned not null PRIMARY KEY auto_increment comment '主键',
    `name` varchar(50) not null default '' comment '菜单名称',
    `url` varchar(200) not null default '' comment '控制地址',
    `parent_id` tinyint(3) unsigned not null default 0 comment '父id',
    `status` tinyint(3) unsigned not null default 0 comment '状态 0-正常，1-禁用,8-测试,9-删除',
    `sort` tinyint(3) unsigned not null default 0 comment '排序',
    `add_time` int(10) unsigned not null default 0 comment '添加时间',
    key `status`(`status`),
    key `sort`(`sort`),
    key `parent`(`parent_id`)
)charset = 'utf8' engine = innodb comment = '菜单表';

/* 插入菜单表初始数据 */
INSERT INTO [[PREFIX]]admin_menu(`name`,`url`,`parent_id`,`sort`,`add_time`) values
('系统管理','',0,99,unix_timestamp(now())),
('菜单管理','menu/index',1,1,unix_timestamp(now())),
('角色管理','role/index',1,2,unix_timestamp(now())),
('用户管理','role/admin_user',1,3,unix_timestamp(now())),
('行为日志','system/log',1,4,unix_timestamp(now())),
('系统配置','system/config',1,5,unix_timestamp(now())),
('新增菜单','menu/add_menu',2,1,unix_timestamp(now())),
('删除菜单','menu/del_menu',2,1,unix_timestamp(now())),
('编辑菜单','menu/edit_menu',2,1,unix_timestamp(now())),
('菜单权限','auth/auth_by_menu',2,2,unix_timestamp(now())),
('角色权限','auth/auth_by_role',3,2,unix_timestamp(now()));

/* 权限表 */
DROP TABLE IF EXISTS [[PREFIX]]admin_role_auth;
CREATE TABLE [[PREFIX]]admin_role_auth(
    `id` tinyint(3) unsigned not null PRIMARY KEY auto_increment comment '主键',
	`role_id` tinyint(3) unsigned not null default 0 comment '角色id',
	`role_auth` varchar(5000) not null default '' comment '角色规则-json格式',
    `update_at` int(10) unsigned not null default 0 comment '修改时间',
    key `role`(`role_id`)
)charset = 'utf8' engine = innodb comment = '权限表';

/* 行为日志表 */
DROP TABLE IF EXISTS [[PREFIX]]admin_log;
CREATE TABLE [[PREFIX]]admin_log(
    `id` int not null PRIMARY KEY auto_increment comment '主键',
    `admin_id` tinyint(3) unsigned not null default 0 comment '管理员id',
    `view_name` varchar(50) not null default '' comment '访问名称',
    `view_url` varchar(200) not null default '' comment '访问地址',
    `info` VARCHAR(200) not null default '' comment '信息',
    `view_at` int(10) unsigned not null default 0 comment '访问时间',
    `view_ip` char(20) not null default '' comment '访问ip',
    key `admin_id`(`admin_id`),
    key `view_at`(`view_at`)
)charset = 'utf8' engine = innodb comment = '后台日志表';

/* 短信验证码表 */
DROP TABLE IF EXISTS [[PREFIX]]sms_code;
CREATE TABLE [[PREFIX]]sms_code(
    `id` int not null PRIMARY KEY auto_increment comment '主键',
    `code` mediumint(8) unsigned not null default 0 comment '验证码code，最多8位',
    `phone` varchar(15) not null default '' comment '手机号',
    `expire_time` int(10) unsigned not null default 0 comment '过期时间',
    `status` tinyint(3) unsigned not null default 0 comment '状态，0-未使用，1已使用',
    key `phone`(`phone`)
)charset = 'utf8' engine = innodb comment = '短信验证码表';

/* 全局设置表 */
DROP TABLE IF EXISTS [[PREFIX]]global_setting;
CREATE TABLE [[PREFIX]]global_setting(
	`id` tinyint(3) unsigned not null PRIMARY KEY auto_increment comment '主键',
	`key` varchar(50) not null default '' comment '配置key',
  `value` varchar(200) not null default '' comment '配置值',
  `comment` varchar(200) not null default '' comment '配置说明',
  key `key`(`key`)
)charset = 'utf8' engine = innodb comment = '全局设置表';

/* 插入全局表初始数据 */
INSERT INTO [[PREFIX]]global_setting(`key`,`value`,`comment`) values
('system_name','','后台名称'),
('log_open',1,'是否开启日志，1-是，0-否'),
('sms_verify',0,'后台登录短信验证，1-是，0-否'),
('sms_expire',3000,'短信验证码过期时间'),
('page_limit',15,'后台数据每页页数'),
('api_auth_open',0,'是否开启api权限验证，1-是，0-否');
