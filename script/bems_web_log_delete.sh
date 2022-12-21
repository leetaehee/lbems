#!/bin/sh
cd /kevin/lbems/web/logs/*
find ./*.log -mtime +7 -exec rm -f {} \;