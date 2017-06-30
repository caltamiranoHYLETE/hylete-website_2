#!/bin/bash
list=`ls Icommerce_*.mod`
for l in $list
do
ModName=`ls $l | sed 's|.mod||g'`
ModVersion=`cat $l`
echo  $ModName " " $ModVersion
done 

