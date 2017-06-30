#!/bin/bash

doc_root=$(doc-root.sh)
inst=$1

if [ -z "$inst" ]; then
    echo "Usage: runSchedulerOperations.sh [instance]"
    exit 0
fi

if ps -ef | grep "$inst/shell/[s]cheduler.php" >/dev/null 2>&1; then
    echo "Previous instance still running. Exiting..."
    exit 0
fi

php "$doc_root/$inst/shell/scheduler.php" run
