#!/bin/sh

echo -n "Chowdah version: "
read -e chowdahversion

# Chowdah
tar -czf chowdah-$chowdahversion.tar.gz chowdah/*

# Quiki
cp -r chowdah/chowdah/* quiki/quiki/.quiki/chowdah
tar -czf app-quiki-$chowdahversion.tar.gz quiki/*

# Server
cp -r chowdah/chowdah/* server/server/.server/chowdah
tar -czf app-server-$chowdahversion.tar.gz server/*