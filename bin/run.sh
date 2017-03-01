path=/home/work
php=$path/php7/bin/php
dir=$path/queue
supervise=/bin/supervise
superviseStatus=$dir/bin/status/queue
start() {
    echo "starting:"
    $supervise -f "$php $dir/index.php" -p "$superviseStatus" > /dev/null  2> $superviseStatus/supervise.log 
    while true
    do
        pid=`od -d --skip-bytes=16 $superviseStatus/status | awk '{print $2}'`
        echo $pid;

        hasPid=`ps --no-heading $pid| wc -l`
        if [ $hasPid -ge 1 ] 
        then
            break;
        fi
    done
    echo "success"
}
stop() {
    ps ax | grep 'queue/index.php' | grep -v grep | awk '{print $1}' | xargs kill -9 
    ps ax | grep 'movieQueueServer'| grep -v grep |  awk '{print $1}' | xargs kill -9 ;
}
#会自动重启
restart() {
    ps ax | grep 'movieQueueServer' | grep -v grep | awk '{print $1}' | xargs kill -9 ;
}

case "$1" in
start)
    start
    ;;

stop)
    stop
    ;;

restart)
    restart
    ;;
*)

echo "Usage: $0 {start|stop|restart}"
exit 1
esac
