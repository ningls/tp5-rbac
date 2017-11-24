DROP TABLE IF EXISTS [[PREFIX]]admin_user;
CREATE TABLE [[PREFIX]]admin_user {
    `id` tinyint(3) not null PRIMARY KEY auto_increment comment '主键',
    `admin_user` varchar(25) not null default '' comment '管理员用户名',
}charset = 'utf8' engine = innodb comment = '管理用户表';

DROP TABLE IF EXISTS [[PREFIX]]admin_menu;
CREATE TABLE [[PREFIX]]admin_menu {
    `id` tinyint(3) not null PRIMARY KEY auto_increment comment '主键',
    `name` varchar(25) not null default '' comment '菜单名称',
}charset = 'utf8' engine = innodb comment = '菜单表';