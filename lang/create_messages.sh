#!/bin/bash
/usr/bin/xgettext --language=PHP --indent --keyword=__ --keyword=_e --keyword=__ngettext:1,2 -s -n --from-code=UTF8 \
	../wp-strava.php \
	../readme.txt

msgfmt -o wp-strava-es_ES.mo wp-strava-es_ES.po
