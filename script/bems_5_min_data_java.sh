#!/bin/sh

# 무등산 태양광 측정
java -Dlog4j.configuration=file:/kevin/san/common/log4j.xml -jar /kevin/san/prog/bems_san.jar
sleep 1

java -Dlog4j.configuration=file:/kevin/ami/common/log4j.xml -jar /kevin/ami/prog/curooAmiClient.jar
