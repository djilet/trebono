#!/usr/bin/env bash
cp _kubernetes/docker/.dockerignore .
LANG=en_us_8859_1
set -e
TAG=$(date +%m%h%y%H%M)
docker pull trebono/trebono-ci || true
docker build -t trebono/trebono-ci:${TAG} --cache-from trebono/trebono-ci -f _kubernetes/docker/CI.Dockerfile .
docker tag trebono/trebono-ci:${TAG} trebono/trebono-ci:latest
docker push trebono/trebono-ci:${TAG}
docker push trebono/trebono-ci:latest