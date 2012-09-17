# zf2-geoname

[![Build Status](https://secure.travis-ci.org/heartsentwined/zf2-geoname.png)](http://travis-ci.org/heartsentwined/zf2-geoname)

Maintain a local copy of the [Geonames](http://geonames.org) (places) database.

**Attention**: the Geonames database is around 1.5GiB - 2GiB in size, when installed in a MySQL database. Are you sure you need a local copy, instead of the official [webservices](http://www.geonames.org/export/ws-overview.html)?

# Installation

[Composer](http://getcomposer.org/):

```json
{
    "require": {
        "heartsentwined/zf2-geoname": "1.*"
    }
}
```

Then add `Geoname` to the `modules` key in `(app root)/config/application.config.yml`

Geoname module will also hook onto your application's database, through [`DoctrineORMModule`](https://github.com/doctrine/DoctrineORMModule). It will create a number of tables with the prefix `geoname_*`, and will use the default EntityManager `doctrine.entitymanager.orm_default`. If your settings are different, please modify the `doctrine` section of `config/module.config.yml` as needed.

Geoname module makes use of the [Cron module](https://github.com/heartsentwined/zf2-cron), so make sure you follow its settings, and have set up your cron job properly.

# Config

Copy `config/geoname.local.php.dist` to `(app root)/config/autoload/geoname.local.php`, and modify the settings.

- `tmpDir`: temporary directory for storing geonames database source files.
- `cron`: (cron expression) how frequently Geoname should be run.

How frequent should `cron` be? The recommended setup is every 15 minutes, which is also the default.

# Usage

## Database sync

Just follow the installation instructions. Geoname module will install and update its database in your cron jobs.

## Querying the database

You can use the Doctrine 2 ORM API directly. Mapping files are located at `(zf2-geoname)/src/Geoname/Entity/Mapping`. All places' hierarchy, from `continent`, `country`, to the various administration levels, have been properly captured in the `parent` and `children` fields of the `Place` entity.

**TODO**: add a set of API for common tasks, e.g. finding a place by name, listing all places in a country, etc.