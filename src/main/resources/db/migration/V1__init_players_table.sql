create table players(
    id int primary key auto_increment,
    name varchar(100)  null,
    surname varchar(100)  null,
    birth_date datetime  null,
    footed varchar(100)  null,
    position varchar(100)  null,
    done bit
)