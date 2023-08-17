#/bin/bash

DOCKER_VOLUME_PATH="/var/lib/docker/volumes/"
APP_PATH="/var/www/apps/pipeline-doc/pipelinedoc-docker"
DATE=$( date '+%F_%H-%M-%S' )
VOLUME_NAME="pipelinedoc-docker_pipeline-doc"
CODE_PATH="/apps/pipelinedoc-source"

# Rename volume
mv "${DOCKER_VOLUME_PATH}${VOLUME_NAME}" \
"${DOCKER_VOLUME_PATH}${VOLUME_NAME}.rollback.${DATE}"

# Update app code
cd "${APP_PATH}${CODE_PATH}"
git pull

# Restart containers
cd "${APP_PATH}/"
docker-compose down
docker volume rm -f "${VOLUME_NAME}"
docker-compose up -d --build