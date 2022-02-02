#!/usr/bin/env bash

set -euo pipefail

echo "--- Getting Magento pod name"
POD=$(kubectl get pods -l autopilothq.com/name=magento --namespace magento -o jsonpath='{.items[*].metadata.name}' --field-selector status.phase=Running --sort-by=.metadata.creationTimestamp | head -n 1)
echo "Pod name is $POD"

echo "--- Packaging"
tar cfvz /tmp/magento-connector-php.tgz --exclude=.git --exclude=.gitignore --exclude=.buildkite .

echo "--- Copying to $POD"
kubectl cp -n magento /tmp/magento-connector-php.tgz "$POD":/tmp/

echo "--- Trigger php bin/magento setup:upgrade"

echo "Deleting existing job if it exists"
kubectl -n magento delete job ap3-stg-magento-setup-upgrade-manual || :

echo "Triggering new job from cronjob"
kubectl -n magento create job --from=cronjob/ap3-stg-magento-setup-upgrade ap3-stg-magento-setup-upgrade-manual
