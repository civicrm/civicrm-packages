--TEST--
DB_driver::error mapping
--INI--
error_reporting = 2047
--SKIPIF--
<?php chdir(dirname(__FILE__)); require_once './skipif.inc'; ?>
--FILE--
<?php
require_once './mktable.inc';
require_once '../errors.inc';
?>
--EXPECT--
Trying to provoke DB_ERROR_NOSUCHTABLE for select:  matches expected outcome
Trying to provoke DB_ERROR_NOSUCHTABLE for drop:  matches expected outcome
Trying to provoke DB_ERROR_NOT_FOUND for drop index:  matches expected outcome
Trying to provoke DB_ERROR_ALREADY_EXISTS for create table:  matches expected outcome
Trying to provoke DB_ERROR_ALREADY_EXISTS for create index:  matches expected outcome
Trying to provoke DB_ERROR_CONSTRAINT for primary key insert duplicate:  matches expected outcome
Trying to provoke DB_ERROR_CONSTRAINT for primary key update duplicate:  matches expected outcome
Trying to provoke DB_ERROR_CONSTRAINT for unique key insert duplicate:  matches expected outcome
Trying to provoke DB_ERROR_CONSTRAINT for unique key update duplicate:  matches expected outcome
Trying to provoke DB_ERROR_CONSTRAINT for foreign key on insert:  matches expected outcome
Trying to provoke DB_ERROR_CONSTRAINT for foreign key on delete:  matches expected outcome
Trying to provoke DB_ERROR_CONSTRAINT_NOT_NULL on insert:  matches expected outcome
Trying to provoke DB_ERROR_CONSTRAINT_NOT_NULL on update:  matches expected outcome
Trying to provoke DB_ERROR_DIVZERO:  matches expected outcome
Trying to provoke DB_ERROR_INVALID_NUMBER:  matches expected outcome
Trying to provoke DB_ERROR_NOSUCHFIELD:  matches expected outcome
Trying to provoke DB_ERROR_SYNTAX:  matches expected outcome
