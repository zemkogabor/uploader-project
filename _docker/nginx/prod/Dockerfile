# Production ready nginx dockerfile which contains frontend files.

FROM node:18.14.0-bullseye as build_stage

WORKDIR /app

COPY frontend/package.json /app/package.json
COPY frontend/yarn.lock /app/yarn.lock

RUN yarn install --production --non-interactive

COPY frontend/public/ /app/public
COPY frontend/src/ /app/src
COPY frontend/vite.config.js /app
COPY frontend/index.html /app

RUN yarn build

FROM nginx:1.21.6-alpine

WORKDIR /app

COPY --from=build_stage /app/dist /app/dist
COPY _docker/nginx/prod/conf.d/ /etc/nginx/conf.d
COPY frontend/env.sh /app

RUN chmod +x env.sh

CMD ["/bin/sh", "-c", "/app/env.sh /app/dist/assets && nginx -g \"daemon off;\""]
