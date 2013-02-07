#!/bin/sh

if [ $(/sbin/pidof php| /usr/bin/wc -w) -gt 0 ]
then
    exit
else
    data=`date +%Y%m%d`
    czas=`date +%H.%M.%S`
    path="/root/wof/scripts"


    export APPLICATION_ENV=production

    echo "Starting..."
    /bin/cp $path/1_.log $path/$data-$czas.1_.log
    /bin/cp $path/2_.log $path/$data-$czas.2_.log
    /usr/bin/php -c /etc/php-cli/ -f $path/server.php < /dev/null 1>$path/1_.log 2>$path/2_.log &
fi
