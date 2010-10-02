drop table if exists sessions;
create table sessions
(
    sesskey        varchar(32) not null,
    expiry         int(11),
    value          text,
    primary key(sesskey)
) type = innodb;

