phplogmon
=========

PHP based log file analyzer used to monitor all kinds of system access activity.

A cron job is used to evaluate log files and detect access events. Access events are
identified using regular expressions and categorized according to several attributes
derived from the log entries:

* Type of access
	* Graned: Access has been granted by the system.
	* Denied: Access has been denied by the system.
	* Error:  Access caused a system error.
* Source log
	* The group of log files this event has been retrieved from. 
* Network
	* The network the access is coming from.
* Service
	* The accessed service.
* Host IP (if available)
	* The accessing host's IP address.
* User (if available)
	* The accessing user name. 
* Host MAC address (if available)
	* The accessing host's mac address.

Based upone IP address, user name, MAC address additional informations are queried
(like DNS, GeoIP information, Vendor, etc.).

Alle collected informations are stored in a database for evaluation by the web
interface or other scripts.

See INSTALL file for how to install, configure and use  phplogmon.

History
=======
* 2014-02-02 Initial release of phplogmon v1.0.0
