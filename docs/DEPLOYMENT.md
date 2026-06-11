# Deployment Runbook — Yahoot

Production: <https://yahoot.web.id>

Single all-in-one Docker image (`supervisord` runs nginx + PHP-FPM + Reverb + queue worker + scheduler in one container — see `Dockerfile` target `production`). **Postgres and Redis run as containers** on the server, each on its own Docker network (`postgres` and `redis`); the nginx-certbot proxy is on the `yahoot` network. The app container joins all three so it reaches every service by container name. TLS is terminated by an already-running [`jonasalfredsson/docker-nginx-certbot`](https://github.com/jonasalfredsson/docker-nginx-certbot) reverse proxy.

---

## 0. Prerequisites

| Item | Value / Source |
|------|----------------|
| Local: Docker + buildx | `docker login` to Docker Hub as `azmifauzan` |
| Local: `sshpass` | `sudo apt-get install -y sshpass` (password auth used) |
| Deploy host | `.env` → `DEPLOYMENT_SERVER_HOST` |
| Deploy user | `.env` → `DEPLOYMENT_SERVER_USERNAME` (`ubuntu`) |
| Deploy password | `.env` → `DEPLOYMENT_SERVER_PASSWORD` |
| Server: Docker + compose plugin | already installed |
| Server: Postgres 17 | **container** on the `postgres` network |
| Server: Redis 7 | **container** on the `redis` network |
| Server: networks | `yahoot` (proxy) + `postgres` + `redis` — all **already exist** |
| Server: nginx-certbot proxy | **already running**, vhost dir `/home/ubuntu/nginxconf/user_conf.d` |
| App dir on server | `/home/ubuntu/yahoot` |
| Image repo | `azmifauzan/yahoot` |
| Tag format | `{ddMMYY}-{counter}` e.g. `110626-1` |

> Security: `.env` holds the server password in plaintext. Keep `.env` out of git (verify `.gitignore`). Prefer migrating to SSH key auth later.

Load deploy vars into the local shell (used by every step below):

```bash
set -a; source .env; set +a
SSH="sshpass -p $DEPLOYMENT_SERVER_PASSWORD ssh -o StrictHostKeyChecking=accept-new $DEPLOYMENT_SERVER_USERNAME@$DEPLOYMENT_SERVER_HOST"
SCP="sshpass -p $DEPLOYMENT_SERVER_PASSWORD scp -o StrictHostKeyChecking=accept-new"
```

---

## 1. Build Docker image — tag `azmifauzan/yahoot:{ddMMYY}-{counter}`

Counter auto-increments per same-day build (checks existing local tags):

```bash
DATE=$(date +%d%m%y)                                   # e.g. 110626
N=$(docker images azmifauzan/yahoot --format '{{.Tag}}' \
     | grep "^${DATE}-" | sed "s/${DATE}-//" | sort -n | tail -1)
COUNTER=$(( ${N:-0} + 1 ))
TAG="${DATE}-${COUNTER}"
IMAGE="azmifauzan/yahoot:${TAG}"
echo "Building ${IMAGE}"

docker build --target production -t "$IMAGE" -t azmifauzan/yahoot:latest .
```

> Build runs `npm run build` + `composer install --no-dev` inside the image. Building for the server's arch: if local is arm64 and server is amd64, use
> `docker buildx build --platform linux/amd64 --target production -t "$IMAGE" --load .`

---

## 2. Push to Docker Hub

```bash
docker login                       # once, as azmifauzan
docker push "$IMAGE"
docker push azmifauzan/yahoot:latest
```

Verify: <https://hub.docker.com/r/azmifauzan/yahoot/tags>

---

## 3. SSH to deploy server (creds from `.env`)

```bash
$SSH 'echo connected as $(whoami) on $(hostname); docker --version'
```

Expected: `connected as ubuntu ...` + Docker version. If host key prompt loops, the `accept-new` option already auto-trusts first connect.

---

## 4. Create server compose at `/home/ubuntu/yahoot`

Files needed on server: `docker-compose.yml` + `.env` (production values).

### 4a. Make dir + copy env

```bash
$SSH 'mkdir -p /home/ubuntu/yahoot'
$SCP .env $DEPLOYMENT_SERVER_USERNAME@$DEPLOYMENT_SERVER_HOST:/home/ubuntu/yahoot/.env
```

> Ensure the copied `.env` has **production** values:
> `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://yahoot.web.id`,
> `DB_HOST=<postgres-container-name>`, `DB_DATABASE=yahoot`, `DB_PASSWORD=<from step 4c inspect>`,
> `REDIS_HOST=<redis-container-name>`, `REDIS_PASSWORD=<from step 4c inspect>`,
> `SESSION_DOMAIN=.yahoot.web.id`,
> `REVERB_HOST=yahoot.web.id`, `REVERB_SCHEME=https`, `REVERB_PORT=443`,
> `VITE_REVERB_HOST=yahoot.web.id`, `VITE_REVERB_SCHEME=wss`, `VITE_REVERB_PORT=443`.
> (VITE_* are baked at build time — if changed, rebuild image in step 1.)
> Strip the `DEPLOYMENT_SERVER_*` lines from the server copy — not needed there.

### 4b. Compose file

The app joins **three external networks** — `yahoot` (nginx-certbot proxy), `postgres` (DB container), `redis` (Redis container) — all already on the server, so it resolves each service by container name.

Create `/home/ubuntu/yahoot/docker-compose.yml`:

```yaml
services:
  yahoot:
    image: azmifauzan/yahoot:latest   # pin to ${TAG} for reproducible deploys
    container_name: yahoot-app
    restart: unless-stopped
    env_file:
      - .env
    volumes:
      - storage_data:/var/www/html/storage/app
    networks:
      - yahoot      # shared with nginx-certbot proxy
      - postgres    # reach Postgres container by name
      - redis       # reach Redis container by name

volumes:
  storage_data:

networks:
  yahoot:
    external: true
  postgres:
    external: true
  redis:
    external: true
```

Ship it:

```bash
$SCP docker/compose/docker-compose.server.yml \
  $DEPLOYMENT_SERVER_USERNAME@$DEPLOYMENT_SERVER_HOST:/home/ubuntu/yahoot/docker-compose.yml
```

> Keep a committed copy at `docker/compose/docker-compose.server.yml` so the server file is version-controlled. (Create it from the YAML above.)

Postgres lives on its own `postgres` network, Redis on its own `redis` network, the proxy on `yahoot`. The app container joins **all three** (step 4b compose) so it resolves each service by container name. No need to move the DB/Redis containers. Confirm the networks + members:

```bash
PG=<postgres-container-name>      # docker ps --filter ancestor=postgres
RD=<redis-container-name>         # docker ps --filter ancestor=redis
$SSH "docker network inspect postgres --format '{{range .Containers}}{{.Name}} {{end}}'"
$SSH "docker network inspect redis    --format '{{range .Containers}}{{.Name}} {{end}}'"
```

> Set `DB_HOST=$PG` and `REDIS_HOST=$RD` in the server `.env` (container names, resolved via the shared networks).

### 4c. Read DB + Redis passwords from containers → save to `.env`

Inspect the running containers for their credentials, save into the server `.env` (and locally for reference):

```bash
# Postgres password (POSTGRES_PASSWORD env baked into the container)
$SSH "docker inspect $PG --format '{{range .Config.Env}}{{println .}}{{end}}' | grep -E 'POSTGRES_(PASSWORD|USER|DB)'"

# Redis password — check container env first, then its config/launch command
$SSH "docker inspect $RD --format '{{range .Config.Env}}{{println .}}{{end}}' | grep -iE 'REDIS_PASSWORD|REQUIREPASS'"
$SSH "docker inspect $RD --format '{{json .Args}}'"          # look for --requirepass <value>
$SSH "docker exec $RD redis-cli CONFIG GET requirepass"      # if reachable without auth
```

Write the discovered values into both `.env` files:

```
DB_HOST=<postgres-container-name>
DB_DATABASE=yahoot
DB_USERNAME=<POSTGRES_USER>
DB_PASSWORD=<POSTGRES_PASSWORD>
REDIS_HOST=<redis-container-name>
REDIS_PASSWORD=<redis requirepass | empty if none>
```

> Keep these out of git — they live only in `.env`. Re-copy the updated `.env` to the server (step 4a) if edited after the first push.

### 4d. Create the `yahoot` database inside the Postgres container

```bash
# Skips creation if the DB already exists
$SSH "docker exec $PG psql -U <POSTGRES_USER> -tc \"SELECT 1 FROM pg_database WHERE datname='yahoot'\" \
      | grep -q 1 || docker exec $PG psql -U <POSTGRES_USER> -c 'CREATE DATABASE yahoot'"
# Verify
$SSH "docker exec $PG psql -U <POSTGRES_USER> -lqt | cut -d'|' -f1 | grep -qw yahoot && echo 'DB yahoot OK'"
```

---

## 5. Deploy container with compose

```bash
$SSH 'cd /home/ubuntu/yahoot && docker compose pull && docker compose up -d'
$SSH 'cd /home/ubuntu/yahoot && docker compose ps'
```

### 5a. Post-deploy app setup (first deploy + after migrations)

```bash
# First deploy: migrate + seed (DB yahoot already created in step 4d)
$SSH 'docker exec yahoot-app php artisan migrate --seed --force'
# Subsequent deploys: migrate only (drop --seed)
$SSH 'docker exec yahoot-app php artisan config:cache && \
      docker exec yahoot-app php artisan route:cache && \
      docker exec yahoot-app php artisan view:cache'
$SSH 'docker exec yahoot-app php artisan storage:link'   # if not baked
```

Check app + reverb + workers are up under supervisor:

```bash
$SSH 'docker exec yahoot-app supervisorctl status'
$SSH 'docker logs --tail=50 yahoot-app'
```

---

## 6. Add nginx reverse-proxy vhost (proxy already running)

Drop a new vhost into `/home/ubuntu/nginxconf/user_conf.d`. The nginx-certbot image auto-requests a Let's Encrypt cert for every `server_name` it finds with `ssl_certificate .../live/<domain>/...`.

Create `docker/nginx/proxy/yahoot.web.id.conf` locally, then ship it:

```nginx
server {
    listen 80;
    server_name yahoot.web.id;
    # nginx-certbot serves ACME challenge + redirects to https automatically
    location / { return 301 https://$host$request_uri; }
}

map $http_upgrade $connection_upgrade {
    default upgrade;
    ''      close;
}

server {
    listen 443 ssl;
    server_name yahoot.web.id;

    ssl_certificate     /etc/letsencrypt/live/yahoot.web.id/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yahoot.web.id/privkey.pem;

    client_max_body_size 20M;   # avatar / quiz image uploads

    # Reverb WebSocket (Pusher protocol path) → container :8080
    location /app {
        proxy_pass http://yahoot-app:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection $connection_upgrade;
        proxy_set_header Host $host;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_read_timeout 3600s;
    }

    # App (HTTP) → container :80
    location / {
        proxy_pass http://yahoot-app:80;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

Ship it:

```bash
$SCP docker/nginx/proxy/yahoot.web.id.conf \
  $DEPLOYMENT_SERVER_USERNAME@$DEPLOYMENT_SERVER_HOST:/home/ubuntu/nginxconf/user_conf.d/yahoot.web.id.conf
```

> Prereq for cert issuance: DNS `A` record `yahoot.web.id` → `DEPLOYMENT_SERVER_HOST`, ports 80+443 open in firewall, and a valid `CERTBOT_EMAIL` already set on the proxy container.
> The proxy and `yahoot-app` must share the `yahoot` network (step 4b) for `proxy_pass http://yahoot-app` to resolve. Connect the proxy if not already: `$SSH "docker network connect yahoot <proxy-container>"`.

---

## 7. Restart nginx-proxy container

Find the proxy container name, then reload (zero-downtime) or restart:

```bash
$SSH 'docker ps --filter ancestor=jonasal/nginx-certbot --format "{{.Names}}"'
PROXY=<name-from-above>          # e.g. nginx-certbot
$SSH "docker exec $PROXY nginx -t"        # validate config first
$SSH "docker exec $PROXY nginx -s reload" # graceful reload, no drop
# If a new domain was added the container must re-scan for certs:
$SSH "docker restart $PROXY"
```

---

## 8. Watch SSL certificate generation

```bash
$SSH "docker logs -f $PROXY"
```

Look for:
- `Requesting a certificate for yahoot.web.id`
- `Successfully received certificate`
- files appear: `$SSH "docker exec $PROXY ls -l /etc/letsencrypt/live/yahoot.web.id/"`

Common failures:
- **DNS not propagated** → ACME `unauthorized` / connection refused. Verify `dig +short yahoot.web.id`.
- **Rate limit** → use Let's Encrypt staging on the proxy until config is proven, then switch to prod.
- **Port 80 blocked** → HTTP-01 challenge fails; open firewall.

---

## 9. Smoke-test production — <https://yahoot.web.id>

```bash
# TLS + cert
curl -sSI https://yahoot.web.id | head -n 1                       # HTTP/2 200
echo | openssl s_client -connect yahoot.web.id:443 -servername yahoot.web.id 2>/dev/null \
  | openssl x509 -noout -issuer -dates                            # Let's Encrypt, valid dates

# HTTP→HTTPS redirect
curl -sSI http://yahoot.web.id | grep -i location                 # → https://

# App health
curl -sS https://yahoot.web.id/up                                 # Laravel health endpoint

# WebSocket handshake (Reverb)
curl -sSI "https://yahoot.web.id/app/${REVERB_APP_KEY}" | head -n 1
```

Then manual browser checks:
1. Landing page loads, assets (CSS/fonts) 200 — no mixed-content warnings.
2. Create a game session → 6-digit code + QR render.
3. Open `/play/{code}` in a second browser, join with avatar.
4. Host starts question → confirm **live** countdown + answer sync (WebSocket working over `wss`).
5. Finish game → leaderboard + podium confetti.
6. `$SSH 'docker logs --tail=100 yahoot-app'` — no errors; `supervisorctl status` all RUNNING.

---

## Rollback

```bash
$SSH 'cd /home/ubuntu/yahoot && \
  sed -i "s#azmifauzan/yahoot:.*#azmifauzan/yahoot:<previous-tag>#" docker-compose.yml && \
  docker compose up -d'
```

## Quick redeploy (subsequent releases)

```bash
# local
set -a; source .env; set +a
DATE=$(date +%d%m%y); TAG="${DATE}-1"; IMAGE="azmifauzan/yahoot:${TAG}"
docker build --target production -t "$IMAGE" -t azmifauzan/yahoot:latest .
docker push "$IMAGE" && docker push azmifauzan/yahoot:latest
# server
$SSH 'cd /home/ubuntu/yahoot && docker compose pull && docker compose up -d && \
      docker exec yahoot-app php artisan migrate --force && \
      docker exec yahoot-app php artisan config:cache'
```
