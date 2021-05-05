# Contributing

**Required Software**
- [Composer]()
- [Nodejs]()

**Optional Software** Required for end-2-end testing
- [Docker Desktop]()
- [Chromedriver]()

## Project Setup

After cloning this repository, you will need to install the required packages.

Install composer
```
composer install
```

Install npm packages
```
npm install
```

### PHP Linting, code standards, and unit tests

_Before any performing any of these commands, you should have ran `composer install` during Project Setup._

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
/bin/bash tests/install-wp-tests.sh wpe_content_model_tests db_name db_password
```

If you connect to mysql via a sock connection, you can run the following.
```
/bin/bash tests/install-wp-tests.sh wpe_content_model_tests db_name db_password localhost:/path/to/mysql/mysqld.sock
```

Run `phpunit` directly.
```
vendor/bin/phpunit
```

Or run `phpunit` as a composer command.
```
composer test
```

### End-2-End Testing

_Before running end-2-end tests, ensure you have ran `composer install` from the Project Setup._

[Codeception](https://codeception.com/) is used for running end-2-end tests in the browser.

#### 1. Environment Setup
1. Install [Google Chrome](https://www.google.com/chrome/).
1. Install [Chromedriver](https://chromedriver.chromium.org/downloads)
		- The major version will need to match your Google Chrome [version](https://www.whatismybrowser.com/detect/what-version-of-chrome-do-i-have). See [Chromedriver Version Selection](https://chromedriver.chromium.org/downloads/version-selection).
		- Unzip the chromedriver zip file and move `chromedriver` application into the `/usr/local/bin` directory.
			`mv chromedriver /usr/local/bin`
		- In shell, run `chromedriver --version`. _Note: If you are using OS X, it may prevent this program from opening. Open "Security & Privacy" and allow chromedriver_.
		- Run `chromedriver --version` again. _Note: On OS X, you may be prompted for a final time, click "Open"_. When you can see the version, chromedriver is ready.

#### 2. End-2-End Testing Site Setup
A running WordPress test site will be needed to run browser tests against. This test site's database will be reset after each test. Do not use your development site for this.

1. Prepare a test WordPress site.
		- We have provided a Docker build to reduce the setup needed. You are welcome to set up your own WordPress end-2-end testing site.
			1. Install [Docker Desktop](https://www.docker.com/get-started).
			1. Run `docker-compose up -d --build`. If building for the first time, it could take some time to download and build the images.
			1. Run `docker-compose exec --workdir=/var/www/html/ --user=www-data wordpress wp plugin install wp-graphql --activate`
			1. Run `docker-compose exec --workdir=/var/www/html/wp-content/plugins/wpe-content-model --user=www-data wordpress wp db export tests/_data/dump.sql`
1. Copy `.env.testing.example` to `.env.testing`.
		- If you are using the provided Docker build, you will not need to adjust any variables in the `.env.testing` file.
		- If you are not using the provided Docker build, edit the `.env.testing` file with your test WordPress site information.
1. Run `vendor/bin/codecept run acceptance` to start the end-2-end tests.

#### Browser testing documentation
- [Codeception Acceptance Tests](https://codeception.com/docs/03-AcceptanceTests)
	- Base framework for browser testing in php.
- [WPBrowser](https://wpbrowser.wptestkit.dev/)
	- WordPress framework wrapping Codeception for browser testing WordPress.
