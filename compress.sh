#!/bin/bash
sass scss/default.scss static/css/default.css
VERSION=$(git tag -l | sort -r | head -1)
echo "${VERSION}"
sed -i -E "s/^ \* Version\: .*$/ * Version: ${VERSION}/g" wp-time.php
(cd ../ && zip -r wp-time/${VERSION}.zip \
  wp-time/index.html \
  wp-time/LICENSE \
  wp-time/README.md \
  wp-time/wp-time.php \
  wp-time/dist \
  wp-time/static)