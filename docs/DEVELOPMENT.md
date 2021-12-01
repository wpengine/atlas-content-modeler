# Contributing

## Required Software
- [Composer](https://getcomposer.org/download/)
- [Nodejs](https://nodejs.org/en/)

## Optional Software for end-2-end testing
- [Docker Desktop](https://www.docker.com/products/docker-desktop)
- [Chromedriver](https://formulae.brew.sh/cask/chromedriver)

## Project Setup

After cloning this repository, you will need to install the required packages.

If you do not have composer or npm installed locally run
```
make build
```

Otherwise, to manually install dependencies, run

Install composer
```
composer install
```

Install npm packages
```
npm install
```

### Which Node.js version should I use?

We recommend using a Node.js version manager such as Fast Node Manager ([fnm](https://github.com/Schniz/fnm)) to ensure your version of Node.js matches ours when working on Atlas Content Modeler.

[Install fnm](https://github.com/Schniz/fnm#installation), close and reopen your shell, then run these commands from the Atlas Content Modeler project root:

```
fnm install
fnm use
```

`node -v` will then return the same version from this project's `.nvmrc`.

## Testing

### Automated test setup

The Makefile will allow for most testing without much setup. To run all tests at once run
```
make test-all
```

This will run lints, unit testing, the Jest test suite and all end-to-end testing.

You can also run individual test suites as needed. For a full description of available tests to run see
```
make help
```

#### Running a single end-to-end (acceptance) test

```
TEST=<test> make test-e2e
```

Example:

```
TEST=CreateContentModelMediaFieldCest:i_can_add_a_media_field_to_a_content_model make test-e2e
```

#### Running a single PHP unit test

```
TEST=<test> make test-php-unit
```

Example:

```
TEST=tests/integration/api-validation/test-graphql-endpoints.php make test-php-unit
```

### Manual test setup
#### PHP Linting, code standards, and unit tests

_Before performing any of these commands, you should have run `composer install` during Project Setup._

**PHP Code Sniffer**
[PHP Code Sniffer](https://github.com/squizlabs/PHP_CodeSniffer) is configured for the [WordPress code standards](https://make.wordpress.org/core/handbook/best-practices/coding-standards/).

Run php linter.
```
composer lint
```

Run the syntax check.
```
composer phpcs
```

Some syntax errors can be fixed by phpcs.
```
composer phpcs:fix
```

**WordPress Unit Tests**
In order to run WordPress unit tests, the test framework needs to be set up.
```
/bin/bash tests/install-wp-tests.sh atlas_content_modeler_tests db_name db_password
```

If you connect to mysql via a sock connection, you can run the following.
```
/bin/bash tests/install-wp-tests.sh atlas_content_modeler_tests db_name db_password localhost:/path/to/mysql/mysqld.sock
```

Run `phpunit` directly.
```
vendor/bin/phpunit
```

Or run `phpunit` as a composer command.
```
composer test
```

#### End-2-End Testing

_Before running end-2-end tests, ensure you have ran `composer install` from the Project Setup._

[Codeception](https://codeception.com/) is used for running end-2-end tests in the browser.

##### 1. Environment Setup
1. Install [Google Chrome](https://www.google.com/chrome/).
1. Install [Chromedriver](https://chromedriver.chromium.org/downloads)
    - The major version will need to match your Google Chrome [version](https://www.whatismybrowser.com/detect/what-version-of-chrome-do-i-have). See [Chromedriver Version Selection](https://chromedriver.chromium.org/downloads/version-selection).
    - Unzip the chromedriver zip file and move `chromedriver` application into the `/usr/local/bin` directory.
    `mv chromedriver /usr/local/bin`
    - In shell, run `chromedriver --version`. _Note: If you are using OS X, it may prevent this program from opening. Open "Security & Privacy" and allow chromedriver_.
    - Run `chromedriver --version` again. _Note: On OS X, you may be prompted for a final time, click "Open"_. When you can see the version, chromedriver is ready.

##### 2. End-2-End Testing Site Setup
A running WordPress test site will be needed to run browser tests against. This test site's database will be reset after each test. Do not use your development site for this.

1. Prepare a test WordPress site.
    We have provided a Docker build to reduce the setup needed. You are welcome to set up your own WordPress end-2-end testing site.
    1. Install [Docker Desktop](https://www.docker.com/get-started).
    1. Run `docker-compose up -d --build`. If building for the first time, it could take some time to download and build the images.
    1. Run `docker-compose exec --workdir=/var/www/html/ --user=www-data wordpress wp plugin install wp-graphql --activate`
    1. Run `docker-compose exec --workdir=/var/www/html/ --user=www-data wordpress wp plugin activate atlas-content-modeler`
    1. Run `docker-compose exec --workdir=/var/www/html/wp-content/plugins/atlas-content-modeler --user=www-data wordpress wp db export tests/_data/dump.sql`
1. Copy `.env.testing.example` to `.env.testing`.
    - If you are using the provided Docker build, you will not need to adjust any variables in the `.env.testing` file.
    - If you are not using the provided Docker build, edit the `.env.testing` file with your test WordPress site information.
1. Run `vendor/bin/codecept run acceptance` to start the end-2-end tests.

##### Browser testing documentation
- [Codeception Acceptance Tests](https://codeception.com/docs/03-AcceptanceTests)
    - Base framework for browser testing in php.
- [WPBrowser](https://wpbrowser.wptestkit.dev/)
    - WordPress framework wrapping Codeception for browser testing WordPress.

## Deployment

Developers with full GitHub repository access can create public releases.

Before tagging a release, make sure to notify other WP Engine teams ahead of time in the `#oss-releases` channel in Slack. For normal releases, a 24 hour notice is desirable. For releases containing changes that break backwards compatibility, a one week notice is desirable.

### To release the plugin

1. Create a PR to update the version and changelog. [Example release PR](https://github.com/wpengine/atlas-content-modeler/pull/100).
2. If necessary, update the required PHP and WordPress versions listed in the header of the plugin's main file.
3. When the release PR is approved and merged, tag the commit you wish to publish with the release version in the form `x.y.z`. [Example release tag](https://github.com/wpengine/atlas-content-modeler/releases/tag/0.2.0).

You can tag in GitHub by creating a release, or via the command line locally:

```shell
git checkout [commit-hash]
git tag [x.y.z]
git push --tags
```

CircleCI will build and deploy the plugin zip. The latest version is available here:

`https://wordpress.org/plugins/atlas-content-modeler/`
