
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

insert into id_genter(id, obj, step) values(1000,'order',3);
insert into id_genter(id, obj, step) values(1000,'account',1);
insert into account(id, ver, balance,credit,usetype,ownerid,status ) values(1,1, 0,0,"GSP_MONEY",1,'active');
insert into account(id, ver, balance,credit,usetype,ownerid,status ) values(2,1, 0,0,"GSP_COIN",1,'active');

alter table accountitem add `currency` varchar(30)  after `money`;
