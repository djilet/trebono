DOCKERCOMPOSE=docker-compose
current_dir = $(shell pwd)
RELEASE?=local-dev
REGISTRY?=trebono
IMAGE?=trebono
CI-IMAGE?=root/trebono/trebono-ci

build:
	docker pull ${REGISTRY}/${IMAGE} || true
	docker build -t ${REGISTRY}/${IMAGE}:${RELEASE} --cache-from ${REGISTRY}/${IMAGE}:${RELEASE} .
	# docker push ${REGISTRY}/${IMAGE}:${RELEASE}

release:
	docker pull ${REGISTRY}/${IMAGE} || true
	docker build -t ${REGISTRY}/${IMAGE}:${RELEASE} --cache-from ${REGISTRY}/${IMAGE} .
	docker tag ${REGISTRY}/${IMAGE}:${RELEASE} ${REGISTRY}/${IMAGE}:latest
	docker push ${REGISTRY}/${IMAGE}:${RELEASE}

build-ci:
	docker pull ${REGISTRY}/${CI-IMAGE} || true
	docker build -t ${REGISTRY}/${CI-IMAGE}:${RELEASE} -f CI.Dockerfile --cache-from ${REGISTRY}/${CI-IMAGE}:${RELEASE} .
	docker tag ${REGISTRY}/${CI-IMAGE}:${RELEASE} ${REGISTRY}/${CI-IMAGE}:latest
	# docker push ${REGISTRY}/${CI-IMAGE}:${RELEASE}

release-ci:
	docker pull ${REGISTRY}/${CI-IMAGE} || true
	docker build -t ${REGISTRY}/${CI-IMAGE}:${RELEASE} -f CI.Dockerfile --cache-from ${REGISTRY}/${CI-IMAGE} .
	docker tag ${REGISTRY}/${CI-IMAGE}:${RELEASE} ${REGISTRY}/${CI-IMAGE}:latest
	docker push ${REGISTRY}/${CI-IMAGE}:${RELEASE}
	docker push ${REGISTRY}/${CI-IMAGE}:latest

shell:
	env | egrep -i "KUBE|DB" >env.env
	@docker run -ti --rm -w $$PWD -v $$PWD:$$PWD --env-file env.env \
	${REGISTRY}/${IMAGE}:${RELEASE} bash
	rm -f env.env