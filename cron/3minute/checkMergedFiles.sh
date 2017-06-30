#!/bin/bash 

if [ "$(set | grep apple | wc -l)" -gt 0 ]; then
    is_mac=1
fi

# Should we use options for stat suitable for Mac (BSD) or Linux ?
stat_opts='-c "%Y"'
if [ "$is_mac" ]; then
    stat_opts='-f "%m" -t "%s"'
fi
#echo stat_opts: "$stat_opts"

# Check merged CSS files
ts_css_last=$(cat ../../var/ts_css_last 2> /dev/null | tr -d '"') 
if [ -z "$ts_css_last" ]; then
    ts_css_last=0
fi
ts_css=$(find ../../skin/frontend -name "*.css" | xargs stat $stat_opts 2> /dev/null | sort | tail -n 1 | tr -d '"')
echo ts_css: $ts_css >> ../../var/acmf.log
echo ts_css_last $ts_css_last  >> ../../var/acmf.log

shopt -s extglob

if [ $ts_css -gt $ts_css_last ]; then
    echo "Removing old merged CSS ..."
    echo $(date) "Removing old merged CSS ..." >> ../../var/merge_auto_clean.log

    if [ -e ../../var/count_css_merge ]; then
        count=$(cat ../../var/count_css_merge)
    else
        count=0
    fi

    rm ../../media/css/!(*.$count.css) 2> /dev/null

    let count++

    echo -n $count > ../../var/count_css_merge
fi
echo $ts_css > ../../var/ts_css_last


# Check merged JS files
ts_js_last=$(cat ../../var/ts_js_last 2> /dev/null | tr -d '"')
if [ -z "$ts_js_last" ]; then
    ts_js_last=0
fi
ts_js=$(find ../../js ../../skin/frontend -name "*.js" | xargs stat $stat_opts 2> /dev/null | sort | tail -n 1 | tr -d '"')
echo ts_js: $ts_js >> ../../var/acmf.log
echo ts_js_last $ts_js_last  >> ../../var/acmf.log

if [ $ts_js -gt $ts_js_last ]; then
    echo "Removing old merged JS ..."
    echo $(date) "Removing old merged JS ..." >> ../../var/merge_auto_clean.log

    if [ -e ../../var/count_js_merge ]; then
        count=$(cat ../../var/count_js_merge)
    else
        count=0
    fi

    rm ../../media/js/!(*.$count.js) 2> /dev/null

    let count++

    echo -n $count > ../../var/count_js_merge
fi
echo $ts_js > ../../var/ts_js_last

shopt -u extglob
