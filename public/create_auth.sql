/* 管理用户表 */
DROP TABLE IF EXISTS [[PREFIX]]admin_user;
CREATE TABLE [[PREFIX]]admin_user {
    `id` tinyint(3) unsigned not null PRIMARY KEY auto_increment comment '主键',
    `admin_user` varchar(25) not null default '' comment '管理员用户名',
    `admin_pass` varchar(32) not null default '' comment '登录密码',
    `admin_name` varchar(32) not null default '' comment '管理员姓名',
    `admin_phone` varchar(15) not null default '' comment '管理员手机号',
    `role_id` tinyint(3) unsigned not null default 0 comment '角色id',
    `last_login` int(10) unsigned not null default 0 comment '上次登录时间',
    `status` tinyint(2) unsigned not null default 0 comment '状态 0-正常，1-禁用，9-删除',
    'add_time' int(10) unsigned not null default 0 comment '增加时间'
}charset = 'utf8' engine = innodb comment = '管理用户表';

/* 管理用户表 */
DROP TABLE IF EXISTS [[PREFIX]]admin_role;
CREATE TABLE [[PREFIX]]admin_role {
    `id` tinyint(3) unsigned not null PRIMARY KEY auto_increment comment '主键,角色id',
    `role_name` varchar(25) not null default '' comment '角色名称',
    `admin_pass` varchar(32) not null default '' comment '登录密码',
    `admin_name` varchar(32) not null default '' comment '管理员姓名',
    `admin_phone` varchar(15) not null default '' comment '管理员手机号',
    `role_id` tinyint(3) unsigned not null default 0 comment '角色id',
    `parent_id` tinyint(3) unsigned not null default 0 comment '父id',
    `last_login` int(10) unsigned not null default 0 comment '上次登录时间',
    `status` tinyint(2) unsigned not null default 0 comment '状态 0-正常，1-禁用，9-删除',
    'add_time' int(10) unsigned not null default 0 comment '增加时间'
}charset = 'utf8' engine = innodb comment = '管理用户表';

/* 菜单表 */
DROP TABLE IF EXISTS [[PREFIX]]admin_menu;
CREATE TABLE [[PREFIX]]admin_menu {
    `id` tinyint(3) unsigned not null PRIMARY KEY auto_increment comment '主键',
    `name` varchar(25) not null default '' comment '菜单名称',
    `url` varchar(1000) not null default '' comment '控制地址',
    `parent_id` tinyint(3) unsigned not null default 0 comment '父id',
    `left_menu` tinyint(3) unsigned not null default 0 comment '是否左侧菜单1-是，0-否',
}charset = 'utf8' engine = innodb comment = '菜单表';

/* 权限表 */
DROP TABLE IF EXISTS [[PREFIX]]admin_role_rule;
CREATE TABLE [[PREFIX]]admin_role_auth {
    `id` tinyint(3) not null PRIMARY KEY auto_increment comment '主键',
	`role_id` tinyint(3) unsigned not null default 0 comment '角色id',
	`role_auth` varchar(5000) not null default '' comment '角色规则-json格式',
}charset = 'utf8' engine = innodb comment = '权限表';

/* 全局设置表 */
DROP TABLE IF EXISTS [[PREFIX]]global_setting 
CREATE TABLE [[PREFIX]]global_setting {
	`api_open` tinyint(1) unsigned not null default 0 comment 'api权限控制,0-关闭，1-开启',
	`sms_check` tinyint(1) unsigned not null default 0 comment '短信验证，0-关闭，1-开启',
	`page_limit` tinyint(3) unsigned not null default 0 comment '后台表单每页显示数',
}charset = 'utf8' engine = innodb comment = '全局设置表';