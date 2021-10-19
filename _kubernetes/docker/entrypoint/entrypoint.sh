#!/bin/bash

#/vendor/bin/phinx migrate -c phinx_main.php -e the_only
#/vendor/bin/phinx migrate -c phinx_personal.php -e the_only
#/vendor/bin/phinx migrate -c phinx_control.php -e the_only
# pwd > pwd
# export VCAP_SERVICES=""
# eval $(cat docker-entrypoint/cf.env | tr -d '[:blank:]' | tr -d '\n')

cat _kubernetes/docker/entrypoint/cf.env.tmpl | envsubst > cf.env && source _kubernetes/docker/entrypoint/source.env

/srv/app/vendor/bin/phinx migrate -c /srv/app/phinx_main.php -e the_only
/srv/app/vendor/bin/phinx migrate -c /srv/app/phinx_personal.php -e the_only
/srv/app/vendor/bin/phinx migrate -c /srv/app/phinx_control.php -e the_only

#.bp-config/options.json
php /srv/app/module/receipt/workers/signature_create.php &>> /srv/app/var/log/test.log &
php /srv/app/module/receipt/workers/signature_verify.php &>> /srv/app/var/log/test.log &
php /srv/app/module/receipt/workers/line_recognize.php &>> /srv/app/var/log/test.log &
php /srv/app/admin/workers/send_mail.php &>> /srv/app/var/log/test.log &

apache2-foreground