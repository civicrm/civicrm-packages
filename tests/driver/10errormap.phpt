--TEST--
DB_driver::error mapping
--SKIPIF--
<?php require_once dirname(__FILE__) . '/skipif.inc'; ?>
--FILE--
<?php
require_once dirname(__FILE__) . '/mktable.inc';
require_once dirname(__FILE__) . '/../errors.inc';
?>
--EXPECT--
DB_ERROR_NOSUCHTABLE for select:  matches expected outcome
DB_ERROR_NOSUCHTABLE for drop:  matches expected outcome
DB_ERROR_NOT_FOUND for drop index:  matches expected outcome
DB_ERROR_ALREADY_EXISTS for create table:  matches expected outcome
DB_ERROR_ALREADY_EXISTS for create index:  matches expected outcome
DB_ERROR_CONSTRAINT for primary key insert duplicate:  matches expected outcome
DB_ERROR_CONSTRAINT for primary key update duplicate:  matches expected outcome
DB_ERROR_CONSTRAINT for unique key insert duplicate:  matches expected outcome
DB_ERROR_CONSTRAINT for unique key update duplicate:  matches expected outcome
DB_ERROR_CONSTRAINT for foreign key on insert:  matches expected outcome
DB_ERROR_CONSTRAINT for foreign key on delete:  matches expected outcome
DB_ERROR_CONSTRAINT_NOT_NULL on insert:  matches expected outcome
DB_ERROR_CONSTRAINT_NOT_NULL on update:  matches expected outcome
DB_ERROR_NOSUCHFIELD joining ON bogus column:  matches expected outcome
DB_ERROR_NOSUCHFIELD joining USING bogus column:  matches expected outcome
DB_ERROR_DIVZERO:  matches expected outcome
DB_ERROR_INVALID_NUMBER putting chars in INT column:  matches expected outcome
DB_ERROR_INVALID_NUMBER putting float in INT column:  matches expected outcome
DB_ERROR_INVALID_NUMBER putting excessive int in INT column:  matches expected outcome
DB_ERROR_INVALID_NUMBER putting int in CHAR column:  matches expected outcome
DB_ERROR_NOSUCHFIELD:  matches expected outcome
DB_ERROR_SYNTAX:  matches expected outcome
DB_ERROR_VALUE_COUNT_ON_ROW:  matches expected outcome
DB_ERROR_INVALID on CHAR column data too long:  matches expected outcome
DB_ERROR_INVALID on VARCHAR column data too long:  matches expected outcome
