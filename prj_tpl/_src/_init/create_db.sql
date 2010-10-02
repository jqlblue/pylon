
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
insert into id_genter(id, obj, step) values(100, 'module', 3);
insert into id_genter(id, obj, step) values(100, 'userconf', 1);
insert into id_genter(id, obj, step) values(100, 'website', 5);
insert into id_genter(id, obj, step) values(1000,'order',3);
insert into id_genter(id, obj, step) values(1000,'account',1);


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
drop table if exists user;  
create table user  
(
    id             int(11) not null,
    ver            int(11),
    createtime     datetime,
    updatetime     datetime,
    passportid         varchar(30) not null,
    username       varchar(30) not null,
    email          varchar(100) not null,
    linkman        varchar(50) not null default '',
    gender         tinyint not null default 0,
    phone          varchar(20) not null default '',
    mobile         varchar(20) not null default '',
    addr           varchar(100) not null default '',
    post           varchar(10) not null default '',
    qq             varchar(20) not null default '',
    msn            varchar(50) not null default '',
    bakemail       varchar(30) not null default '',
    randomkey      varchar(30) not null,
    activekey      varchar(30) not null,
    status         tinyint(1) not null default 0,
    pstatus        tinyint(1) not null default 0,
    remarks        varchar(255) default '',
    primary key (id),
    unique passportid (passportid)
) type = innodb;



/* 支付信息表 */
drop table if exists payment;
create table payment
(
    id             int(11) not null,
    ver            int(11),
    createtime     datetime,
    updatetime     datetime,
    siteowner__id    int(11) not null,
    payee          varchar(20) not null,
    idcard         varchar(30) not null,
    addr           varchar(255) not null default '',
    post           varchar(10) not null default '',
    bank           varchar(20) not null,
    banknu         varchar(30) not null,
    openbank       varchar(100) not null,
    primary key (id),
    unique siteowner__id (siteowner__id)    
) type = innodb;


drop table if exists payment_his;
create table payment_his
(
    id             int(11) not null,
    ver            int(11),
    createtime     datetime,
    updatetime     datetime,
    siteowner__id    int(11) not null,
    payee          varchar(20) not null,
    idcard         varchar(30) not null,
    addr           varchar(255) not null default '',
    post           varchar(10) not null default '',
    bank           varchar(20) not null,
    banknu         varchar(30) not null,
    openbank       varchar(100) not null,
    primary key (id,ver)
) type = innodb;


/*
create trigger payment_trigger  before update  on payment
for each row 
insert payment_his(
    id,ver,createtime,updatetime,
    siteowner__id,payee,idcard,addr,post,bank,banknu,openbank)
values(
    OLD.id,OLD.ver,OLD.createtime,OLD.updatetime,
    OLD.siteowner__id,OLD.payee,OLD.idcard,OLD.addr,OLD.post,OLD.bank,OLD.banknu,OLD.openbank
    );

*/
--/*}}}*/
-- ---帐务--/*{{{*/

drop table if exists account;
create table  account
(
    id             int(11) not null,
    ver            int(11),
    createtime     datetime,
    updatetime     datetime,
    balance        int(11),
    credit         int(11),
    usetype        varchar(20),
    ownerid       int(11),
    status         varchar(20),
    primary key (id)
) type = innodb;


drop table if exists accountitem;
create table  accountitem
(
    id             int(11) not null,
    ver            int(11),
    createtime     datetime,
    updatetime     datetime,
    usetype        varchar(20),
    account__id    int(11),
    transid        int(11),
    prebalance     int(11),
    money          int(11),
    currency       varchar(30),
    primary key (id)
) type = innodb;



drop table if exists accounttrans;
create table  accounttrans
(
    id             int(11) not null,
    ver            int(11),
    createtime     datetime,
    updatetime     datetime,
    occurtime      datetime,
    dealobj        varchar(255),
    dealcls        varchar(255),
    ownerid        int(11),
    ownercls       varchar(255),
    usetag         varchar(255),
    note                           text,
    relid          int(11) default '0',
    primary key (id)
) type = innodb;
--/*}}}*/
-- --ZMARK/*{{{*/
CREATE TABLE IF NOT EXISTS `zmark` 
(
    id                             integer(11),
    ver                            integer(11),
    createtime                     datetime,
    updatetime                     datetime,
    entity                         varchar(64),
    objid                          varchar(64),

    tag1                           varchar(255),
    tag2                           varchar(255),
    tag3                           varchar(255),
    tag4                           varchar(255),
    tag5                           varchar(255),
    cnt1                           integer(10),                                                                      
    times1                         integer(10),
    cnt2                           integer(10),                                                                      
    times2                         integer(10),
    cnt3                           integer(10),                                                                      
    times3                         integer(10),
    cnt4                           integer(10),                                                                      
    times4                         integer(10),
    cnt5                           integer(10),                                                                      
    times5                         integer(10),
    FULLTEXT (tag1),
    FULLTEXT(tag2),
    FULLTEXT(tag3),
    FULLTEXT(tag4),
    FULLTEXT(tag5),
    primary key (id)
)
type = MyIsam;


CREATE TABLE IF NOT EXISTS `zcntrecord` 
(
    id                              integer(11),                                                                     
    ver                             integer(11),                                                                     
    createtime                      datetime,
    updatetime                      datetime, 
    entity                          varchar(64),
    objid                           varchar(64),

    user                            varchar(64),
    cntval1                         integer(10),                                                                      
    cntval2                         integer(10),                                                                      
    cntval3                         integer(10),                                                                      
    cntval4                         integer(10),                                                                      
    cntval5                         integer(10),                                                                      
    primary key (id) ,
    key user_entity_objid (user,entity,objid)               
)
type = InnoDB;                      


CREATE TABLE IF NOT EXISTS `ztags` 
(
    id             integer(11),
    ver            integer(11),
    createtime     datetime,
    updatetime     datetime,

    entity         varchar(64),
    tag            varchar(255),
    tagkey         varchar(255),                                                                      
    tagcnt1        integer(10),                                                                      
    tagcnt2        integer(10),                                                                      
    tagcnt3        integer(10),                                                                      
    tagcnt4        integer(10),                                                                      
    tagcnt5        integer(10),                                                                      
    ext1            varchar(255),
    ext2            varchar(255),
    ext3            varchar(255),
    lifestatus     varchar(30)  default 'normal',
    visible        varchar(30)  default 'public',
    primary key (id),
    key k_ztags_1 (entity,tagkey),               
    key k_ztags_2 (entity,tag),
    key k_ztags_3 (entity,lifestatus,visible)               
)
type = InnoDB;
-- /*}}}*/
-- --pay/*{{{*/
drop table if exists orders;
create table orders
(
    id             int(11) not null,
    ver            int(11),
    createtime     datetime,
    updatetime     datetime,

    customer       int(11),
    pdttype        varchar(30),
    pdtkey         varchar(50),
    quantity       int(11),
    unitprice      int(11),
    currency       varchar(30),
    paychannel     varchar(30),
    total          int(11),
    status         varchar(30),
    orders         text,
    ordertype      varchar(30), 
    primary key (id)
) type = innodb;

drop table if exists orderstatus;
create table orderstatus
(
    id            int(11) not null,
    ver           int(11),
    createtime    datetime,
    updatetime    datetime,

    orderid       int(11) unique,
    paychannel    varchar(30),
    statuspay     varchar(30),
    statusaccount varchar(30),
    statusdeliver varchar(30),
    primary key (id)
) type = innodb;

drop table if exists payrecord;
create table  payrecord
(
    id             int(11) not null,
    ver            int(11),
    createtime     datetime,
    updatetime     datetime,

    orderid        varchar(30) unique,
    payid         varchar(50),
    paychannel    varchar(30),
    paytime       datetime,
    data          text, 
    primary key (id)
) type = innodb;

insert into account(id, ver, balance,credit,usetype,ownerid,status ) values(1,1, 0,0,"GSP_MONEY",1,'active');
insert into account(id, ver, balance,credit,usetype,ownerid,status ) values(2,1, 0,0,"GSP_COIN",1,'active');
-- /*}}}*/


-- --农场牧场知识库/*{{{*/

drop table if exists animalsdata;
CREATE TABLE IF NOT EXISTS `animalsdata` (
    `id` int(15) NOT NULL,
    `name` varchar(100) NOT NULL,
    `childNum` int(5) NOT NULL,
    `childName` varchar(100) NOT NULL,
    `u` varchar(100) NOT NULL,
    `house` varchar(100) NOT NULL,
    `tip` text NOT NULL,
    `info` text NOT NULL,
    `cLevel` int(5) NOT NULL default '0',
    `matureHours` int(5) NOT NULL default '0',
    `cycleHours` int(5) NOT NULL default '0',
    `procreationHours` int(5) NOT NULL default '0',
    `lifeHours` int(5) NOT NULL default '0',
    `productSecs` int(10) NOT NULL default '0',
    `cycleCnt` int(5) NOT NULL,
    `consum` int(5) NOT NULL,
    `price` int(10) NOT NULL,
    `childSale` int(10) NOT NULL,
    `parentSale` int(10) NOT NULL,
    `expect` int(10) NOT NULL,
    `childExp` int(5) NOT NULL,
    `parentExp` int(5) NOT NULL,
    `pic` varchar(255) NOT NULL,
    UNIQUE KEY `id` (`id`)
) ENGINE=innodb DEFAULT CHARSET=utf8;

drop table if exists cropsdata;
CREATE TABLE IF NOT EXISTS `cropsdata` (
        `id` int(5) NOT NULL,
        `name` varchar(50) NOT NULL,
        `harvestTimes` int(3) NOT NULL default '0',
        `growHours` int(5) NOT NULL default '0',
        `matureHours` int(5) NOT NULL default '0',
        `cLevel` int(5) NOT NULL default '-1',
        `price` int(10) NOT NULL default '0',
        `sale` int(10) NOT NULL default '0',
        `output` int(10) NOT NULL default '0',
        `expect` int(10) NOT NULL default '0',
        `cropExp` int(10) NOT NULL default '0',
        `inshop` int(1) NOT NULL default '0',
        `pic` varchar(80) NOT NULL,
        `tip` text NOT NULL,
        `isRed` int(1) NOT NULL default '0',
        `isFlower` int(1) NOT NULL default '0',
        UNIQUE KEY `id` (`id`)
) ENGINE=innodb DEFAULT CHARSET=utf8;

drop table if exists userfarm;
CREATE TABLE IF NOT EXISTS `userfarm` (
    `qq` int(15) NOT NULL,
    `nick` varchar(255) NOT NULL,
    `headpic` varchar(255) NOT NULL,
    `uid` int(15) NOT NULL,
    `exp` int(10) NOT NULL,
    `money` int(16) NOT NULL,
    `servertime` int(15) NOT NULL,
    `farmlands` text NOT NULL,
    `dog` text NOT NULL,
    `mintime` int(15) NOT NULL default '0',
    `update` int(15) NOT NULL,
    UNIQUE KEY `qq` (`qq`)
) ENGINE=innodb DEFAULT CHARSET=utf8;

-- /*}}}*/
