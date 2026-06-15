#!/bin/bash
set -e

echo "--- Generating Tag ---"
DATE=$(date +%d%m%y)
N=$(docker images azmifauzan/yahoot --format '{{.Tag}}' | grep "^${DATE}-" | sed "s/${DATE}-//" | sort -n | tail -1)
COUNTER=$(( ${N:-0} + 1 ))
TAG="${DATE}-${COUNTER}"
IMAGE="azmifauzan/yahoot:${TAG}"
echo "Building ${IMAGE}"

echo "--- Building Docker Image ---"
set -a; source .env.production; set +a
docker build --target production \
  --build-arg VITE_APP_NAME="$VITE_APP_NAME" \
  --build-arg VITE_REVERB_APP_KEY="$VITE_REVERB_APP_KEY" \
  --build-arg VITE_REVERB_HOST="$VITE_REVERB_HOST" \
  --build-arg VITE_REVERB_PORT="$VITE_REVERB_PORT" \
  --build-arg VITE_REVERB_SCHEME="$VITE_REVERB_SCHEME" \
  -t "$IMAGE" -t azmifauzan/yahoot:latest .

echo "--- Pushing Image to Docker Hub ---"
docker push "$IMAGE"
docker push azmifauzan/yahoot:latest

echo "--- Deploying to Server ---"
set -a; source .env; set +a
SSH="ssh -i $DEPLOYMENT_SERVER_SSH_KEY -o StrictHostKeyChecking=accept-new -o BatchMode=yes $DEPLOYMENT_SERVER_USERNAME@$DEPLOYMENT_SERVER_HOST"

$SSH 'cd /home/ubuntu/yahoot && sudo docker compose pull && sudo docker compose up -d && sudo docker exec yahoot-app php artisan migrate --force && sudo docker exec yahoot-app php artisan config:cache && sudo docker exec yahoot-app php artisan route:cache && sudo docker exec yahoot-app php artisan view:cache'

echo "--- Deployment Complete ---"
