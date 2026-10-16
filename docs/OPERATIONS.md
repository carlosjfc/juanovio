# Queue worker and scheduler services

Juanovio uses the same Linux/systemd management process as `material-freight`: `bin/manage-services` renders and installs marked units, targets one deployment identity, and leaves enabling/starting explicit. Two services are provided: `juanovio-INSTANCE-worker-default.service` and `juanovio-INSTANCE-scheduler.service`. Juanovio currently has no separate email/report workloads. Nothing here creates server accounts or installs services automatically on a developer machine.

## 1. Configure the application

The examples assume Ubuntu, a stable application path `/var/www/html/juanovio`, PHP at `/usr/bin/php`, and a dedicated Linux account `juanovio`. Substitute the actual host paths, PHP executable and PHP-FPM pool user. Use the PHP version/extensions required by `composer.lock`. Keep each staging/production deployment's identity, credentials, database, storage, cache namespace and preferably Linux account separate from Material Freight and other applications.

Set these in the server's existing environment (never overwrite `.env` during deployment):

```dotenv
SERVICE_INSTANCE=juanovio-production
QUEUE_CONNECTION=database
DB_QUEUE_RETRY_AFTER=90
CACHE_STORE=database
```

All asynchronous queue-name defaults use `SERVICE_INSTANCE.default`. Managed workers support database and Redis; `DB_QUEUE`/`REDIS_QUEUE` overrides must begin with the instance followed by a dot, for example `juanovio-production.inquiries`. Only letters, digits, dots, underscores and hyphens are allowed, and a nonempty queue suffix is required. Workers consume the configured queue rather than assuming `.default`. Never share an unprefixed backlog across applications. Drain existing jobs in their original application before changing queue names.

Cache and Redis prefixes default to `SERVICE_INSTANCE-cache-` and `SERVICE_INSTANCE-database-`. Explicit `CACHE_PREFIX`/`REDIS_PREFIX` overrides must remain unique per deployment; startup checks do not validate these overrides. Without a service identity, prefixes retain the application-name fallback, but managed services refuse startup. Keep runtime storage separate too: file caches do not use the cache prefix.

The distributed `.env.example` now uses `SERVICE_INSTANCE=juanovio-local` and database queue/cache stores. Existing `.env` files are not changed. Configure an actual persistent backend; for database queues/cache run `php artisan migrate --force` after reviewing migrations and backups (use `php artisan migrate` locally). Existing migrations create jobs/failed-job and cache/lock tables. SQLite needs a writable database file **and parent directory**; use a dedicated runtime directory with the same restricted runtime ACLs below, not a writable source tree. Redis is optional; use a dedicated connection/database and `REDIS_QUEUE_RETRY_AFTER=90` if selected. Use persistent shared cache for multi-instance scheduler locks and worker restart signals. Changing cache prefixes makes old entries inaccessible under the new prefix; plan the transition and rebuild configuration separately for each deployment.

Workers validate cached Laravel configuration at startup and process only that instance's default queue. They use a 60-second timeout, 3 attempts, 10-second retry backoff, 3-second polling and hourly recycling. Retry intervals must exceed 60 seconds; keep job-specific timeouts below the retry interval and the 180-second systemd stop allowance. Redis blocking polling is rejected to preserve timely shutdown. Do not run another supervisor over the same workload or a cron scheduler alongside this scheduler.

Inquiry notifications currently send synchronously and are not changed by installing these services. Changing `QUEUE_CONNECTION` alone does not queue `TradeInquiry`. Before queuing inquiry data, define encrypted payloads, retention and failed-delivery recovery, and update form success semantics/tests. The scheduler currently has no business tasks configured; future schedules belong in `routes/console.php`.

## 2. Create the Linux runtime account

Run on the target Ubuntu server with administrator access. Skip account creation if `id juanovio` already succeeds. For separate staging use an account such as `juanovio-staging` throughout instead.

```bash
sudo adduser --system --group --no-create-home --home /nonexistent --shell /usr/sbin/nologin juanovio
sudo apt-get install acl
id juanovio
```

The account needs no password, SSH access or sudo privileges. `--user` selects this existing Linux account; `SERVICE_INSTANCE` identifies the deployment. These are separate concepts.

Identify the actual PHP-FPM pool user first. The following uses `www-data`; substitute the actual account. A dedicated PHP-FPM pool running as `juanovio` provides stronger per-application isolation than a shared web account. Grant read/traverse access to the application, and write access only to runtime directories, preserving deployment ownership:

```bash
sudo setfacl -R -m u:juanovio:rX /var/www/html/juanovio
sudo setfacl -R -m u:juanovio:rwX,u:www-data:rwX /var/www/html/juanovio/storage /var/www/html/juanovio/bootstrap/cache
sudo find /var/www/html/juanovio/storage /var/www/html/juanovio/bootstrap/cache -type d -exec setfacl -m d:u:juanovio:rwx,d:u:www-data:rwx {} +
```

Protect `.env` and cached configuration from unrelated accounts. Workers need no write access to source, `vendor` or `.env`. Default directory ACLs let both runtime users access newly created logs/caches under the service's `0027` umask; explicitly restrictive file modes can still override access. Inspect parent traversal with `namei`; grant only traversal on a specific blocking parent when necessary. Apply runtime permissions to shared symlink targets as well. Repeat permissions for each new release; deployment tools can remove ACLs.

## 3. Validate, install and start

From the stable application root:

```bash
cd /var/www/html/juanovio
php artisan config:cache
namei -l /var/www/html/juanovio/artisan
sudo -u juanovio test -r /var/www/html/juanovio/.env
sudo -u juanovio test -w /var/www/html/juanovio/storage
sudo -u juanovio test -w /var/www/html/juanovio/bootstrap/cache
sudo -u www-data test -w /var/www/html/juanovio/storage
sudo -u www-data test -w /var/www/html/juanovio/bootstrap/cache
sudo -u juanovio /usr/bin/php artisan website:check-service-instance juanovio-production
bin/manage-services --render --user juanovio --php /usr/bin/php
bin/manage-services --install --user juanovio --php /usr/bin/php
bin/manage-services --enable
bin/manage-services --start
bin/manage-services --status
```

Access tests produce no output on success. Review rendered units before installation. On Linux, validate installed units using `systemd-analyze verify /etc/systemd/system/juanovio-juanovio-production-worker-default.service /etc/systemd/system/juanovio-juanovio-production-scheduler.service` before enabling. Installation requires root or sudo and writes only these units in `/etc/systemd/system`, then reloads systemd. It refuses symlinked/unmanaged existing units or an instance bound to another application path. Installation does not enable/start services or grant account permissions.

The manager reads only `SERVICE_INSTANCE` from `.env`, without executing it. Alternatively pass `--instance juanovio-production` explicitly; a conflicting `.env` identity is rejected. For atomic releases use `--path /var/www/juanovio/current` consistently to preserve the stable systemd path, and run commands from that deployment. Laravel's startup check also detects stale cached identities. Paths must be absolute and contain no spaces or systemd expansion characters.

## 4. Daily operations and release/rollback

```bash
bin/manage-services --status
bin/manage-services --check
bin/manage-services --diagnose --php /usr/bin/php
bin/manage-services --restart
bin/manage-services --stop
bin/manage-services --start
journalctl -u juanovio-juanovio-production-worker-default.service -n 50 --no-pager
journalctl -u juanovio-juanovio-production-scheduler.service -n 50 --no-pager
php artisan queue:failed
php artisan schedule:list
```

`--status`/`--check` returns nonzero for inactive or failed services. `--diagnose` checks identity and queue settings only; it sends no mail, processes no jobs, and does not check connectivity or heartbeat health. Restrict journal access because logs may contain inquiry or operational data. Review failed-job records and their retention; retry only after establishing job ownership and resolving the cause. Automatic retries can repeat side effects; queued jobs must account for this.

Stopping stops the scheduler first, then the worker. Both use SIGTERM, a 180-second grace period, automatic restart and journal logging. After code/config changes or rollback, rebuild Laravel caches and restart the services. If template/account/PHP settings change, install the updated units before restarting. Preserve environment, keys, persistent storage and backend data across releases.

Target-server acceptance still requires actual account/file access, backend connectivity, unit verification, both services active, journal review, a controlled job, graceful shutdown/restart, and reboot startup. Local automated tests do not replace Linux/systemd acceptance.
