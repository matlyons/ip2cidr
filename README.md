# IP 2 CIDR
PHP tool to convert IP ranges to CIDR

Can be run on any webserver with PHP.

IP ranges can be provided as full range
or as full start IP and last octet of end IP

* 192.168.0.0-192.168.0.255
* 192.168.0.0-255

Results viewed inline on webpage or can be downloade as TXT

Limited to no input validation. Will just output 0.0.0.0/24 if input is invalid.
