DROP TABLE IF EXISTS member;
CREATE TABLE member ( 
    id integer primary key auto_increment,
    name varchar(128) , 
    phone varchar(128) , 
    country varchar(128),
    confirmed boolean
);
