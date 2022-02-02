#!/usr/bin/env bash

set -euo pipefail

echo "--- Getting Magento pod name"
POD=$(kubectl get pods -l autopilothq.com/name=magento --namespace magento -o jsonpath='{.items[*].metadata.name}' --field-selector status.phase=Running --sort-by=.metadata.creationTimestamp | head -n 1)
echo "Pod name is $POD"

echo "--- Packaging"
tar cfvz /tmp/magento-connector-php.tgz --exclude=.git --exclude=.gitignore --exclude=.buildkite .

echo "--- Copying to $POD"
kubectl cp -n magento /tmp/magento-connector-php.tgz "$POD":/bitnami/magento/

echo "--- Trigger php bin/magento setup:upgrade"

echo "Deleting existing job if it exists"
kubectl -n magento delete job ap3-stg-magento-setup-upgrade-manual || :

echo "Triggering new job from cronjob"
kubectl -n magento create job --from=cronjob/ap3-stg-magento-setup-upgrade ap3-stg-magento-setup-upgrade-manual

echo "Waiting for the job to complete"
kubectl -n magento wait --for=condition=complete job/ap3-stg-magento-setup-upgrade-manual &
completion_pid=$!

# wait for failure as background process - capture PID
kubectl -n magento wait --for=condition=failed job/ap3-stg-magento-setup-upgrade-manual && exit 1 &
failure_pid=$!

# capture exit code of the first subprocess to exit
wait -n $completion_pid $failure_pid

# store exit code in variable
exit_code=$?

if ((exit_code == 0)); then
  echo "Job completed"
else
  echo "Job failed with exit code ${exit_code}, exiting..."
fi

exit $exit_code
