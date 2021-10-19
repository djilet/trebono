#!/usr/bin/env bash
cp _kubernetes/docker/.dockerignore .
LANG=en_us_8859_1
set -e
TAG=$(date +%m%h%y%H%M)
docker pull trebono/trebono || true
docker build -t trebono/trebono:${TAG} --cache-from trebono/trebono -f _kubernetes/docker/Dockerfile .
docker tag trebono/trebono:${TAG} trebono/trebono:latest
docker push trebono/trebono:${TAG}
docker push trebono/trebono:latest