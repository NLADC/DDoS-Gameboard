#!/bin/bash
echo "Prefix=$1"
for i in {0..2}
  do
      user="testuser_$1$i@nomoreddos.org"
      php artisan ddosspelbord:readFeed -u $user -p Gerald13 $2 &
  done

