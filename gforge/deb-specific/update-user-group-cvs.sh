#!/bin/sh
if [ $(id -u) != 0 ] 
then
	echo "You must be root to run this, please enter passwd"
	su -c $0
else
	# Fill ldap tables
	# Should be safe to comment this soon
	/usr/lib/gforge/bin/install-ldap.sh update > /dev/null 2>&1

	[ -d /var/lib/gforge/dumps ] || \
	mkdir /var/lib/gforge/dumps && \
	chown gforge:gforge /var/lib/gforge/dumps

	/usr/lib/gforge/bin/user_dump_update.pl
	/usr/lib/gforge/bin/group_dump_update.pl
	/usr/lib/gforge/bin/ssh_dump_update.pl
	[ -f /usr/lib/gforge/bin/cvs_dump_update.pl ] && /usr/lib/gforge/bin/cvs_dump_update.pl

	#CB#su gforge -c /usr/lib/gforge/bin/dump_database.pl -s /bin/sh
	#CB#su gforge -c /usr/lib/gforge/bin/ssh_dump.pl -s /bin/sh

	# Create user, groups and cvs archives
	#CB#/usr/lib/gforge/bin/new_parse.pl

	# Fill ssh authorized_keys
	#CB#/usr/lib/gforge/bin/ssh_create.pl
fi
