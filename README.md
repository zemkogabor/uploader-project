# Simple Gallery
## Install Dev

1. Set environments in `_env` folder

2. Install frontend framework and dependencies
```bash
docker-compose run -u 1000 --rm frontend yarn install
```

3. Install tusd-hook
```bash
docker-compose run -u 1000 --rm tusd-hook yarn install
```

4. Start containers

```bash
docker-compose up -d
```

## Useful scripts

### Remove expired access tokens 
TODO: must be added to cronjob

```bash
docker-compose exec oauth bash
php cli.php clear-expired-tokens
```

