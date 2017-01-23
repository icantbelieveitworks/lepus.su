#!/bin/bash
PID="$(pidof ./lepus)"
if [ -z "$PID" ]; then
	cd /usr/local/lepuscp/ && ./lepus > /usr/local/lepuscp/logs/console.log 2>&1 &
fi
