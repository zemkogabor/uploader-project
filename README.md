# Uploader Project
## Install Dev

1. Set environments in `_env` folder

2. Install frontend framework and dependencies
```bash
docker-compose run -u 1000 --rm frontend yarn install
```

3. Install backend

```bash
docker-compose run -u 1000 --rm backend composer install
```

4. Start containers

```bash
docker-compose up -d
```

5. Generate OAuth keys

```bash
mkdir -p oauth-keys

openssl genrsa -out oauth-keys/private.key
openssl rsa -in oauth-keys/private.key -pubout -out oauth-keys/public.key

chmod 600 oauth-keys/private.key
chmod 600 oauth-keys/public.key
```

6. Run OAuth migrations

```bash
docker-compose exec -u 1000 oauth php cli.php migrations:migrate
```

6. Create client

```bash 
docker-compose exec -u 1000 oauth php cli.php client:create "Test Client" "secret" "http://127.0.0.1" --confidential
```
(Copy the generated client ID key to FRONTEND_APP_AUTH_CLIENT_ID env.)

7. Create user

```bash 
docker-compose exec -u 1000 oauth php cli.php user:create "test@example.com" "Test User Name" "secret"
```

8. Run backend migrations
```
docker-compose exec -u 1000 backend php bin/cli.php migrations:migrate
```

## Useful scripts

### Remove expired access tokens 
TODO: must be added to cronjob

```bash
docker-compose exec oauth bash
php cli.php clear-expired-tokens
```

## Create images

```
docker buildx build -t zemkog/uploader-project-nginx:20230403 . --platform=linux/arm64,linux/amd64 -f _docker/nginx/prod/Dockerfile --push
docker buildx build -t zemkog/uploader-project-backend:20230403 . --platform=linux/arm64,linux/amd64 -f _docker/backend/prod/Dockerfile --push
```

