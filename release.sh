#!/usr/bin/env bash

set -eo pipefail

if [ -z "$PACKAGIST_TOKEN" ]; then
    echo "PACKAGIST_TOKEN environment variable is not set."
    exit 1
fi

confirm() {
  VERSION=$1
  shift
  echo -n "Release $VERSION y/n? "
  read REPLY

  if [ "$REPLY" = y -o "$REPLY" = Y ]; then
    "$@"
  else
    echo "Releasing $VERSION cancelled"
  fi
}

REMOTE='git@github.com:autopilot3/ortto-magento2-connector.git'

release() {
    VERSION="v$1"
    echo "Releasing $VERSION to $REMOTE"
    git checkout v2.4.2
    git tag $VERSION
    git push $REMOTE --tags
    curl -XPOST -H'content-type:application/json' "https://packagist.org/api/update-package?username=ortto&apiToken=$PACKAGIST_TOKEN" -d'{"repository":{"url":"https://packagist.org/packages/ortto/magento2-connector"}}'
}
LATEST=$(git ls-remote --tags $REMOTE | cut -d'/' -f 3 | sort -V | tail -n 1)
VERSION=''

if [ -z "$1" ]; then
    LOCAL=$(git tag | sort -V | tail -1)
    echo "Usage: release.sh <version> (Example: release.sh 1.0.0)"
    echo "Latest Tags => Local: $LOCAL, Released: $LATEST"
    exit 1
else
    VERSION="v$(echo $1 | tr A-Z a-z | sed 's/v//')"
fi

if [[ $LATEST == $VERSION ]]; then
     echo "$LATEST has already been deployed!"
     exit 1
fi

confirm "$VERSION" release $VERSION
