import os
import logging
import json
import re


_log = logging.getLogger('pgbouncer')
PG_URL_PATTERN = re.compile(r'^postgres://(.*):(.*)@(.*):(.*)/(.*)$')


def preprocess_commands(ctx):
    # This instructs the build pack to rewrite the configuration file
    #  at runtime.  This replaces markers prefixed with @, such as
    #  @TMPDIR with the location in the environment at runtime.
    return (('$HOME/.bp/bin/rewrite',
             '"$HOME/vendor-pgbouncer/etc/pgbouncer/pgbouncer.ini"'),)


def service_commands(ctx):
    # Instructs the build pack that we need this command run on startup.
    #  This simply references the pgbouncer files we've included with
    #  the app.
    return {
        'pgbouncer': ('$HOME/vendor-pgbouncer/usr/sbin/pgbouncer',
                      '-v "$HOME/vendor-pgbouncer/etc/pgbouncer/pgbouncer.ini"')
    }


def service_environment(ctx):
    # We need to add the lib directory to the LD_LIBRARY_PATH so that
    #  pgbouncer can find the libevent library.  It's not included by
    #  default, so we need to include it with the app and indicate
    #  the location from which it can be found.
    return {
        'LD_LIBRARY_PATH': '$HOME/vendor-pgbouncer/usr/lib',
    }


def load_db_urls():
    # Utiilty method which parses VCAP_SERVICES, loops through all
    #  all of the elephantsql services bound, grabs the credentials
    #  and formats pgbouncer database connection strings from them.
    dbs = []
    services = json.loads(os.environ['VCAP_SERVICES'])
    for db in services['osb-postgresql']:
        m = PG_URL_PATTERN.match(db['credentials']['uri'])
        if m:
            dbs.append("%s = host=%s port=%s user=%s password=%s" %
                       (m.group(5), m.group(3), m.group(4),
                        m.group(1), m.group(2)))
    return "\n".join(dbs)


def compile(install):
    # Called during staging by the build pack.  The first step is
    #  to move the vendor directory out of htdocs, so that it's not
    #  publicly accessible.
    (install.builder
        .move()
        .under('{BUILD_DIR}/htdocs')
        .where_name_matches('.*\/vendor-pgbouncer\/.*')
        .into('{BUILD_DIR}')
        .done())
    # Now we get our pgbouncer formatted database connections and
    #  insert them into the pgbouncer.ini file.  They are inserted
    #  at the "#PGBOUNCER_DB_STRING" place holder that is located
    #  in the configuration file.
    install.builder._ctx['PGBOUNCER_DB_STRING'] = load_db_urls()
    (install
        .config()
        .to('vendor-pgbouncer/etc/pgbouncer')
        .rewrite()
        .done())
    return 0
