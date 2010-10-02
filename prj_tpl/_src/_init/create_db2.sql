/*==============================================================*/
/* table: sessions                                              */
/*==============================================================*/

set names utf8;
-- --基础服务---/*{{{*/
drop table if exists sessions;
create table sessions
(
    sesskey        varchar(32) not null,
    expiry         int(11),
    value          text,
    primary key(sesskey)
) type = innodb;

drop table if exists id_genter;
create table id_genter
(
    id             int(11) not null,
    obj            varchar(30),
    step           int(11)
) type = innodb;

insert into id_genter(id, obj, step) values(1, 'other', 10);
insert into id_genter(id, obj, step) values(1000, 'user', 1);


--/*}}}*/
-- --/*{{{*/  内部支持
drop table if exists staff;
create table  staff
(
    id             int(11) not null,
    ver            int(11),
    createtime     datetime,
    updatetime     datetime,
    logname        varchar(30) unique,
    passwd         varchar(50),
    role           varchar(30),
    primary key (id)
) type = innodb;

insert into staff(id, ver, logname,passwd,role ) values(1,1, 'sysadmin','25906f61ac67563ad782b2ddc751db17', 'admin');
alter table staff add unique index index_staff(logname); 

--/*}}}*/
-- --用户---/*{{{*/
--drop table if exists user;  
--create table user  
--(
--    id             int(11) not null,
--    ver            int(11),
--    createtime     datetime,
--    updatetime     datetime,
--    passportid         varchar(30) not null,
--    username       varchar(30) not null,
--    email          varchar(100) not null,
--    linkman        varchar(50) not null default '',
--    gender         tinyint not null default 0,
--    phone          varchar(20) not null default '',
--    mobile         varchar(20) not null default '',
--    addr           varchar(100) not null default '',
--    post           varchar(10) not null default '',
--    qq             varchar(20) not null default '',
--    msn            varchar(50) not null default '',
--    bakemail       varchar(30) not null default '',
--    randomkey      varchar(30) not null,
--    activekey      varchar(30) not null,
--    status         tinyint(1) not null default 0,
--    pstatus        tinyint(1) not null default 0,
--    remarks        varchar(255) default '',
--    primary key (id),
--    unique passportid (passportid)
--) type = innodb;









 --ZMARK/*{{{*/
--CREATE TABLE IF NOT EXISTS `zmark` 
--(
--    id                             integer(11),
--    ver                            integer(11),
--    createtime                     datetime,
--    updatetime                     datetime,
--    entity                         varchar(64),
--    objid                          varchar(64),

--    tag1                           varchar(255),
--    tag2                           varchar(255),
--    tag3                           varchar(255),
--    tag4                           varchar(255),
--    tag5                           varchar(255),
--    cnt1                           integer(10),                                                                      
--    times1                         integer(10),
--    cnt2                           integer(10),                                                                      
--    times2                         integer(10),
--    cnt3                           integer(10),                                                                      
--    times3                         integer(10),
--    cnt4                           integer(10),                                                                      
--    times4                         integer(10),
--    cnt5                           integer(10),                                                                      
--    times5                         integer(10),
--    FULLTEXT (tag1),
--    FULLTEXT(tag2),
--    FULLTEXT(tag3),
--    FULLTEXT(tag4),
--    FULLTEXT(tag5),
--    primary key (id)
--)
--type = MyIsam;




--CREATE TABLE IF NOT EXISTS `ztags` 
--(
--    id             integer(11),
--    ver            integer(11),
--    createtime     datetime,
--    updatetime     datetime,

--    entity         varchar(64),
--    tag            varchar(255),
--    tagkey         varchar(255),                                                                      
--    tagcnt1        integer(10),                                                                      
--    tagcnt2        integer(10),                                                                      
--    tagcnt3        integer(10),                                                                      
--    tagcnt4        integer(10),                                                                      
--    tagcnt5        integer(10),                                                                      
--    ext1            varchar(255),
--    ext2            varchar(255),
--    ext3            varchar(255),
--    lifestatus     varchar(30)  default 'normal',
--    visible        varchar(30)  default 'public',
--    primary key (id),
--    key k_ztags_1 (entity,tagkey),               
--    key k_ztags_2 (entity,tag),
--    key k_ztags_3 (entity,lifestatus,visible)               
--)
--type = InnoDB;
 /*}}}*/



--drop table if exists qq;  
--create table qq  
--(
--    id             int(11) not null,
--    ver            int(11),
--    createtime     datetime,
--    updatetime     datetime,
--    qq             varchar(30) not null,
--    nikename           varchar(30) not null,
--    status         tinyint(1) not null default 0,
--    primary key (id)
--) type = innodb;



--drop table if exists qq_friends;  
--create table qq_friends
--(
--    id             int(11) not null,
--    ver            int(11),
--    createtime     datetime,
--    updatetime     datetime,
--    qq__id          int(11) not null,
--    qq              varchar(30) not null,
--    friends         text 
--    primary key (id)
--) type = innodb;


--drop table if exists qq_farm_snapshoot;  
--create table qq_farm_snapshoot
--(
--    id             int(11) not null,
--    ver            int(11),
--    createtime     datetime,
--    updatetime     datetime,
--    qq             varchar(30) not null,
--    parser         varchar(30) not null,
--    snapshoot      text 
--    primary key (id)
--) type = innodb;


