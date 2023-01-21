<img alt="Drupal Logo" src="https://www.drupal.org/files/Wordmark_blue_RGB.png" height="60px">

Drupal is an open source content management platform supporting a variety of
websites ranging from personal weblogs to large community-driven websites. For
more information, visit the Drupal website and join the Drupal community.

# Drupal Test Wireframes | Calibrate

## Getting started

### Git clone the project on your local machine:

```bash
git clone git@github.com:secrethome/miguel-leal-drupal-test.git
cd miguel-leal-drupal-test
```

### Run composer install:

```bash
composer install
```

## Setup your environment settings:

Fill the env variables on file, mentioned below, with all your server details.
Note: A Hash salt is also mandatory and can be generated using the command at the end of this document.

```bash
cp .env.example .env
```

### Install a clean site using the existing and pre-exported configs:

```bash
drush site:install minimal --existing-config
```

### If you decide to setup the site by import an existing database:

Run the following command to import a db:

```bash
drush sql-cli < /path/to/db-exported.sql
```

### Finally:

```bash
drush cr
```

```bash
drush config-import -y
```

### Quick tip: generating a hash salt for Drupal

```bash
drush php-eval 'echo \Drupal\Component\Utility\Crypt::randomBytesBase64(55) . "\n";'
```

### Troubleshooting
