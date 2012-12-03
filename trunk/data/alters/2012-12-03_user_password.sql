ALTER TABLE  `user` ADD  `password` CHAR( 64 ) NOT NULL ,
ADD UNIQUE (
`password`
);