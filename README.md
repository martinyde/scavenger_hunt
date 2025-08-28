# README

## Installation

1 ) Start docker

```shell
docker compose up -d
```

2 ) Install packages

```shell
docker compose composer install
```

3 ) Build database

```shell
docker compose exec phpfpm bin/console doctrine:migrations:migrate
```

## Apply fixtures

```shell
docker compose exec phpfpm bin/console doctrine:fixtures:load
```

## Assets

### Build assets

```shell
docker compose run --rm node npm install
```

### Watch asset changes

```shell
docker compose run --rm node npm run watch
```

## Messenger

Uses scheduler and messages. To consume all scheduler messages created over the next 900 seconds run:

```shell
docker compose exec phpfpm bin/console messenger:consume --env=prod --no-debug --time-limit=900 --failure-limit=1 scheduler_default
```

## User management

Create a new user:

```shell
docker compose exec phpfpm bin/console app:user:create EMAIL@EMAIL.com --password=PASSWORD --roles=[ROLE_ADMIN,ROLE_USER]
```
