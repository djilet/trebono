FROM poc-trebono-git.da-rz.net:5050/root/trebono/trebono-ci

#code
COPY . /srv/app
COPY _kubernetes/docker/vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /srv/app

RUN set -xe \
    && composer update 

RUN apt-get update && apt-get install -y gettext-base

COPY .bp-config/php/conf.d/php.ini "$PHP_INI_DIR/php.ini"

# TODO: find correct settings instead!!
RUN chmod -R 777 /srv/app/var/

COPY _kubernetes/docker/entrypoint /
CMD ["_kubernetes/docker/entrypoint/entrypoint.sh"]