A PHP engine for managing email letter series and email subscribers.

License is [AGPL 3.0](http://www.tldrlegal.com/l/AGPL3)

##Requirements

Kohana 3.3 (I recommend using 3.4 unstable git branch, it has MySQLi driver)
MySQL or MariaDB
PHP 5 with enabled OpenSSL and APCu

## Notes
### Config Writer

* Writer still needs to be enabled: `Kohana::$config->attach(new Config_File_Writer);`
* All comments in config file are lost on write.
* Feature is highly unreliable. You can lose the file. You can have two users writing config simultaneously.
* Do not use in web environment.
