#!/usr/bin/env bash

set -euo pipefail

NAMESPACE=magento
CRONJOB=ap3-stg-magento-setup-upgrade

echo "--- Getting Magento pod name"
POD=$(kubectl get pods -l autopilothq.com/app=magento --namespace magento -o jsonpath='{.items[*].metadata.name}' --field-selector status.phase=Running --sort-by=.metadata.creationTimestamp | head -n 1)
echo "Pod name is $POD"

echo "--- Packaging"
tar cfvz /tmp/magento-connector-php.tgz --exclude=.git --exclude=.gitignore --exclude=.buildkite .

echo "--- Copying to $POD"
kubectl cp -n $NAMESPACE /tmp/magento-connector-php.tgz "$POD":/bitnami/magento/

echo "--- Trigger php bin/magento setup:upgrade"

echo "Deleting existing job if it exists"
kubectl -n $NAMESPACE delete job ${CRONJOB}-manual || :

echo "Triggering new job from cronjob"
kubectl -n $NAMESPACE create job --from=cronjob/${CRONJOB} ${CRONJOB}-manual

echo "Waiting for the job to complete"
kubectl -n $NAMESPACE wait --for=condition=complete --timeout=300s job/${CRONJOB}-manual &
completion_pid=$!

# wait for failure as background process - capture PID
kubectl -n $NAMESPACE wait --for=condition=failed --timeout=300s job/${CRONJOB}-manual && exit 1 &
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
