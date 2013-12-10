--
-- Drop existing tables (in reverse order)
--

DROP TABLE IF EXISTS log;
DROP TABLE IF EXISTS event;
DROP TABLE IF EXISTS user;
DROP TABLE IF EXISTS hostmac;
DROP TABLE IF EXISTS hostip;
DROP TABLE IF EXISTS service;
DROP TABLE IF EXISTS loghost;
DROP TABLE IF EXISTS sourcestate;

--
-- Table 'sourcestate'
--

CREATE TABLE sourcestate (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
	sourceid VARCHAR(64) NOT NULL,
	file VARCHAR(1024) NOT NULL,
	mtime INT NOT NULL,
	last INT NOT NULL,
	PRIMARY KEY ( id ),
	INDEX ( sourceid )
) ENGINE=InnoDB CHARSET=utf8;

--
-- Table 'loghost'
--

CREATE TABLE loghost (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
	loghost VARCHAR(64) NOT NULL,
	PRIMARY KEY ( id ),
	UNIQUE KEY ( loghost )
) ENGINE=InnoDB CHARSET=utf8;

--
-- Table 'service'
--

CREATE TABLE service (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
	service VARCHAR(32) NOT NULL,
	PRIMARY KEY ( id ),
	UNIQUE KEY ( service )
) ENGINE=InnoDB CHARSET=utf8;

--
-- Table 'hostip'
--

CREATE TABLE hostip (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
	hostip VARCHAR(40) NOT NULL,
	host VARCHAR(64) NOT NULL,
	continentcode CHAR(2),
	countrycode CHAR(2),
	countryname VARCHAR(64),
	region CHAR(2),
	city VARCHAR(64),
	postalcode VARCHAR(64),
	latitude DOUBLE,
	longitude DOUBLE,
	PRIMARY KEY ( id ),
	UNIQUE KEY ( hostip, host )
) ENGINE=InnoDB CHARSET=utf8;

--
-- Table 'hostmac'
--

CREATE TABLE hostmac (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
	hostmac CHAR(17) NOT NULL,
	vendor VARCHAR(64) NOT NULL,
	PRIMARY KEY ( id ),
	UNIQUE KEY ( hostmac, vendor )
) ENGINE=InnoDB;

--
-- Table 'user'
--

CREATE TABLE user (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
	user VARCHAR(64) NOT NULL,
	PRIMARY KEY ( id ),
	UNIQUE KEY ( user )
) ENGINE=InnoDB CHARSET=utf8;

--
-- Table 'event'
--

CREATE TABLE event (
	id INT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,
	loghostid INT UNSIGNED NOT NULL,
	serviceid INT UNSIGNED NOT NULL,
	typeid INT UNSIGNED NOT NULL,
	hostipid INT UNSIGNED NOT NULL,
	hostmacid INT UNSIGNED NOT NULL,
	userid INT UNSIGNED NOT NULL,
	count INT NOT NULL,
	first INT NOT NULL,
	last INT NOT NULL,
	PRIMARY KEY ( id ),
	UNIQUE KEY ( loghostid, serviceid, typeid, hostipid, hostmacid, userid ),
	FOREIGN KEY ( loghostid ) REFERENCES loghost ( id ),
	FOREIGN KEY ( serviceid ) REFERENCES service ( id ),
	FOREIGN KEY ( hostipid ) REFERENCES hostip ( id ),
	FOREIGN KEY ( hostmacid ) REFERENCES hostmac ( id ),
	FOREIGN KEY ( userid ) REFERENCES user ( id ),
	INDEX ( loghostid ),
	INDEX ( serviceid ),
	INDEX ( typeid ),
	INDEX ( hostipid ),
	INDEX ( hostmacid ),
	INDEX ( userid )
) ENGINE=InnoDB CHARSET=utf8;

--
-- Table 'log'
--

CREATE TABLE log (
	eventid INT UNSIGNED NOT NULL,
	line VARCHAR(1024) NOT NULL,
	FOREIGN KEY ( eventid ) REFERENCES event ( id ),
	INDEX ( eventid )
) ENGINE=InnoDB CHARSET=utf8;

--
-- EOF
--
