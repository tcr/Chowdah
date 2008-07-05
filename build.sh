#!/bin/sh

echo -n "Chowdah version: "
read -e chowdahversion

# Chowdah
cp chowdah chowdah-$chowdahversion
tar -czf chowdah-$chowdahversion.tar.gz chowdah-$chowdahversion

# Quiki
cp quiki quiki-$chowdahversion
cp -r chowdah/chowdah/* quiki-$chowdahversion/quiki/.quiki/chowdah
tar -czf quiki-$chowdahversion.tar.gz quiki-$chowdahversion

# Server
cp server server-$chowdahversion
cp -r chowdah/chowdah/* server-$chowdahversion/server/.server/chowdah
tar -czf app-server-$chowdahversion.tar.gz server-$chowdahversion