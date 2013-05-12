--
-- Table 'sourcestate'
--

DROP TABLE IF EXISTS sourcestate;
CREATE TABLE sourcestate (
	id INT NOT NULL AUTO_INCREMENT,
	monitor CHAR(32) NOT NULL,
	file VARCHAR(1024) NOT NULL,
	mtime INT NOT NULL,
	last INT NOT NULL,
	PRIMARY KEY ( id ),
	INDEX ( monitor )
) ENGINE=InnoDB;

--
-- Table 'ipevent'
--

DROP TABLE IF EXISTS ipevent;
CREATE TABLE ipevent (
	status INT NOT NULL,
	service CHAR(8) NOT NULL,
	ip CHAR(40) NOT NULL,
	user CHAR(32) NOT NULL,
	count INT NOT NULL,
	first INT NOT NULL,
	last INT NOT NULL,
	line VARCHAR(4096) NOT NULL,
	PRIMARY KEY ( status, service, ip, user ),
	INDEX ( status ),
	INDEX ( service ),
	INDEX ( ip )
) ENGINE=InnoDB;

--
-- Table 'ipinfo'
--

DROP TABLE IF EXISTS ipinfo;
CREATE TABLE ipinfo (
	ip CHAR(40) NOT NULL,
	host VARCHAR(256) NOT NULL,
	continentcode CHAR(2),
	countrycode CHAR(2),
	countryname VARCHAR(256),
	region CHAR(2),
	city VARCHAR(256),
	postalcode VARCHAR(256),
	latitude DOUBLE,
	longitude DOUBLE,
	PRIMARY KEY ( ip )
) ENGINE=InnoDB;

--
-- Table 'macevent'
--

DROP TABLE IF EXISTS macevent;
CREATE TABLE macevent (
	status INT NOT NULL,
	service CHAR(8) NOT NULL,
	mac CHAR(17) NOT NULL,
	ip CHAR(40) NOT NULL,
	count INT NOT NULL,
	first INT NOT NULL,
	last INT NOT NULL,
	line VARCHAR(4096) NOT NULL,
	PRIMARY KEY ( status, service, mac, ip ),
	INDEX ( status ),
	INDEX ( service ),
	INDEX ( mac )
) ENGINE=InnoDB;

--
-- Table 'macvendorid'
--

DROP TABLE IF EXISTS macvendorid;
CREATE TABLE macvendorid (
	mac CHAR(8) NOT NULL,
	vendorid VARCHAR(256) NOT NULL,
	INDEX ( mac )
) ENGINE=InnoDB;

--
-- EOF
--
