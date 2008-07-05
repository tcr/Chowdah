#!/bin/sh

echo -n "Chowdah version: "
read -e version

# Chowdah
cp -r chowdah chowdah-$version
tar -czf chowdah-$version.tar.gz chowdah-$version
rm -rf chowdah-$version

# Quiki
cp -r quiki quiki-$version
cp -r chowdah/chowdah/* quiki-$version/quiki/.quiki/chowdah
tar -czf quiki-$version.tar.gz quiki-$version
rm -rf quiki-$version

# Server
cp -r server server-$version
cp -r chowdah/chowdah/* server-$version/server/.server/chowdah
tar -czf server-$version.tar.gz server-$version
rm -rf server-$version