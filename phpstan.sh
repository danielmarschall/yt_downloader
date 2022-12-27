#!/bin/bash

DIR=$( dirname "$0 ")

cd "$DIR"

if [ ! -f ytdwn.php ]; then
	ln -s ytdwn ytdwn.php
fi
phpstan
rm ytdwn.php

