[![Build Status](https://travis-ci.org/hollodotme/github-org-analyzer.svg?branch=master)](https://travis-ci.org/hollodotme/github-org-analyzer)
[![codecov](https://codecov.io/gh/hollodotme/github-org-analyzer/branch/master/graph/badge.svg)](https://codecov.io/gh/hollodotme/github-org-analyzer)
[![phpstan enabled](https://img.shields.io/badge/phpstan-enabled-green.svg)](https://github.com/phpstan/phpstan)

# GitHub Organization Analyzer

## Description

This in an online analyzer of GitHub organizations using [GitHub's GraphQL API](https://developer.github.com/v4/).

You can find the app at: https://analyze-github.org

## Development setup

Follow these steps to bring up the development environment:
```bash
git clone https://github.com/hollodotme/analyze-github.org
cd analyze-github.org
docker-compose up -d
docker-compose exec php composer update -o -v -d /repo
echo '127.0.0.1    dev.analyze-github.org' >> /etc/hosts
open http://dev.analyze-github.org
```

## Run tests

```bash
docker-compose exec php vendor/bin/phpunit.phar -c build
```

## Run PHPStan

```bash
docker-compose exec php vendor/bin/phpstan.phar analyze --level max src cgi public
```

## Contributing

Contributions are welcome and will be fully credited. Please see the [contribution guide](.github/CONTRIBUTING.md) for details.


