drop table if exists id_genter;
drop table if exists sessions; 
drop table if exists book; 
drop table if exists car_item; 
drop table if exists buycar; 

/*==============================================================*/
/* table: sessions                                              */
/*==============================================================*/
create table sessions
(/*{{{*/
    sesskey                        varchar(32)                    not null,
    expiry                         integer(11),
    value                          text,
    primary key(sesskey)
)/*}}}*/
comment = "mysql table"
type = innodb;

create table id_genter
(
    id                             integer(11),
    obj                            varchar(30),
    step                           integer(11)
)
comment = "mysql table";
insert into id_genter(id, obj, step) values(1, 'other', 10);

create table book
(
    id                              integer(11),
    ver                             integer(11),
    createtime                      datetime,
    updatetime                      datetime,
    name                            varchar(30),
    author__id                       integer(11),
    price                           varchar(30),
    summary                         varchar(255),
    primary key (id)
)
type = innodb;


create table book2
(
    id                              integer(11),
    ver                             integer(11),
    createtime                      datetime,
    updatetime                      datetime,
    name                            varchar(30),
    fstauthor__author                 integer(11),
    secauthor__author                 integer(11),
    price                           varchar(30),
    summary                         varchar(255),
    primary key (id)
)
type = innodb;

create table user_1
(
    id                              integer(11),
    ver                             integer(11),
    createtime                      datetime,
    updatetime                      datetime,
    logname                            varchar(30),
    name                            varchar(30),
    passwd                          varchar(30),
    primary key (id)
)
type = innodb;

create table user_2
(
    id                              integer(11),
    ver                             integer(11),
    createtime                      datetime,
    updatetime                      datetime,
    logname                            varchar(30),
    name                            varchar(30),
    passwd                          varchar(30),
    primary key (id)
)
type = innodb;

create table author
(
    id                              integer(11),
    ver                             integer(11),
    createtime                      datetime,
    updatetime                      datetime,
    name                            varchar(30),
    birthday                        varchar(30),
    lang                            varchar(30),
    primary key (id)
)
type = innodb;


create table car_item
(
    id                                 integer(11),
    owner                              integer(11),
    book__id                              integer(11),
    count                               integer(11)
)
type = innodb;


create table buycar
(
    id                              integer(11),
    ver                             integer(11),
    owner                           varchar(30),
    status                          integer(11),
    createtime                      datetime,
    updatetime                      datetime,
    primary key (id)
)
type = innodb;



