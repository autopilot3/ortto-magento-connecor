#!/usr/bin/env bash

set -euo pipefail

REMOTE_USER=$1
REMOTE_HOST=$2
SITE=$3

INSTALL_DIR="~/${SITE}/app/code/Ortto/Connector"

function ssh_command() {
  local command=$1
  ssh "${REMOTE_USER}@${REMOTE_HOST}" -t "cd ~/${SITE}/ && $command"
}

function magento_command() {
  local command=$1
  ssh_command "php -d memory_limit=-1 bin/magento $command"
}

echo "--- Creating installation directory"
ssh_command "mkdir -p $INSTALL_DIR"

echo "--- Syncing into installation directory"
rsync \
  -azP --delete \
  --exclude ".buildkite" \
  --exclude ".git" \
  --exclude ".gitignore" \
  ./ \
  "${REMOTE_USER}@${REMOTE_HOST}:$INSTALL_DIR"

echo "--- Running setup:upgrade"
magento_command "setup:upgrade"

echo "--- Running setup:di:compile"
magento_command "setup:di:compile"

echo "--- Running setup:static-content:deploy -f"
magento_command "setup:static-content:deploy -f"

echo "--- Running cache:flush"
magento_command "cache:flush"
