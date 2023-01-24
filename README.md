<img alt="Drupal Logo" src="https://www.drupal.org/files/Wordmark_blue_RGB.png" height="60px">

Drupal is an open source content management platform supporting a variety of
websites ranging from personal weblogs to large community-driven websites. For
more information, visit the Drupal website and join the Drupal community.

# Drupal Test Wireframes | Calibrate

## Getting started

### Local Setup

I use [Docksal](https://docksal.io/) for local development.

### Git clone the project on your local machine:

```bash
git clone git@github.com:secrethome/miguel-leal-drupal-test.git
cd miguel-leal-drupal-test
```

#### Set up Docksal:

```bash
fin system start
fin start
```

### Run composer install:

```bash
fin composer install
```

## Setup your environment settings:

Fill the env variables on file, mentioned below, with all your server details.
Note: A Hash salt is also mandatory and can be generated using the command at the end of this document.

```bash
cp .env.example .env
```

### Install a clean site using the existing and pre-exported configs:

```bash
fin drush site:install minimal --existing-config
```

### If you decide to setup the site by import an existing database:

Run the following command to import a db:

```bash
fin drush sql-cli < /path/to/db-exported.sql
```

### Finally:

```bash
fin drush cr
```

```bash
fin drush config-import -y
```

## Install pre-defined taxonomies for basic pages.

```bash
fin drush calibrate:create_basic_pages_terms
```

## Compile sass styles.

```bash
cd web/themes/custom/calibrate_drupal_test && npm install && npm run prod
```

### Quick tip: generating a hash salt for Drupal

```bash
fin drush php-eval 'echo \Drupal\Component\Utility\Crypt::randomBytesBase64(55) . "\n";'
```
