# Simple Gallery
## Install Dev

1. Set environments with te following example
```bash
$ cp .env.example .env
```

2. Install frontend framework and dependencies
```bash
$ cd frontend
$ docker run --rm --tty -u 1000 --volume $PWD:/app node:18.14.0-bullseye /bin/sh -c "cd /app; yarn install"
```
