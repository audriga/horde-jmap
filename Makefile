project := $(OPENXPORT_PROJECT)

build_tools_directory=build/tools
composer=$(shell ls $(build_tools_directory)/composer_fresh.phar 2> /dev/null)
composer_lts=$(shell ls $(build_tools_directory)/composer_lts.phar 2> /dev/null)
version=$(shell git tag --sort=committerdate | tail -1)

all: init

# Remove all temporary build files
.PHONY: clean
clean:
	rm -rf build/ vendor/

# Installs composer from web if not already installed
.PHONY: composer
composer:
ifeq (, $(composer))
	git submodule update --init --recursive
	@echo "No composer command available, downloading a copy from the web"
	mkdir -p $(build_tools_directory)
	curl -sS https://getcomposer.org/installer | php
	mv composer.phar $(build_tools_directory)/composer_fresh.phar
endif

# Installs composer LTS version from web if not already installed.
# TODO Switch from pinning specific version to LTS pinning see
#   https://github.com/composer/composer/issues/10682
.PHONY: composer_lts
composer_lts:
ifeq (, $(composer_lts))
	git submodule update --init --recursive
	@echo "No composer LTS command available, downloading a copy from the web"
	mkdir -p $(build_tools_directory)
	curl -sS https://getcomposer.org/installer | php -- --version 2.2.11
	mv composer.phar $(build_tools_directory)/composer_lts.phar
endif

# Initialize project. Run this before any other target.
.PHONY: init
init: composer
	rm $(build_tools_directory)/composer.phar || true
	ln $(build_tools_directory)/composer_fresh.phar $(build_tools_directory)/composer.phar
	php $(build_tools_directory)/composer.phar install --prefer-dist --no-dev

# Update dependencies and make dev tools available for development
.PHONY: update
update:
	php $(build_tools_directory)/composer.phar update --prefer-dist

# Switch to PHP 5.6 mode. In case you need to build for PHP 5.6
# Requires podman for linting based on https://github.com/dbfx/github-phplint
.PHONY: php56_mode
php56_mode: composer_lts
	rm $(build_tools_directory)/composer.phar
	ln $(build_tools_directory)/composer_lts.phar $(build_tools_directory)/composer.phar
	php $(build_tools_directory)/composer.phar update --prefer-dist --no-dev

	podman run --rm --name php56 -v "$(PWD)":"$(PWD)" -w "$(PWD)" docker.io/phpdockerio/php56-cli sh -c "! (find . -type f -name \"*.php\" -not -path \"./tests/*\" $1 -exec php -l -n {} \; | grep -v \"No syntax errors detected\")"

# Switch to PHP 7 mode. In case you need to build for PHP 7
# Requires podman for linting based on https://github.com/dbfx/github-phplint
.PHONY: php70_mode
php70_mode: composer_lts
	rm $(build_tools_directory)/composer.phar || true
	ln $(build_tools_directory)/composer_lts.phar $(build_tools_directory)/composer.phar
	php $(build_tools_directory)/composer.phar update --prefer-dist --no-dev

	# Lint for PHP 7.0
	podman run --rm --name php70  -v "$(PWD)":"$(PWD)" -w "$(PWD)" docker.io/jetpulp/php70-cli sh -c "! (find . -type f -name \"*.php\" -not -path \"./tests/*\" $1 -exec php -l -n {} \; | grep -v \"No syntax errors detected\")"

# Switch to PHP 8 mode
# TODO broken for now due to https://github.com/composer/composer/issues/10702
.PHONY: php81_mode
php81_mode: composer
	rm $(build_tools_directory)/composer.phar || true
	ln $(build_tools_directory)/composer_fresh.phar $(build_tools_directory)/composer.phar
	php $(build_tools_directory)/composer.phar update --prefer-dist --no-dev

	# Lint for installed PHP version (should be 8.1)
	sh -c "! (find . -type f -name \"*.php\" -not -path \"./build/*\" $1 -exec php -l -n {} \; | grep -v \"No syntax errors detected\")" || true

# Switch to Graylog PHP 5.6 mode. In case you need to build for PHP 5.6 and include graylog
.PHONY: graylog_php56_mode
graylog_php56_mode:
	make php56_mode
	php $(build_tools_directory)/composer.phar require paragonie/constant_time_encoding:'<2' psr/log:'<2'
	php $(build_tools_directory)/composer.phar update --prefer-dist --no-dev

# Switch to Graylog PHP 7 mode. In case you need to build for PHP 7 and include graylog
.PHONY: graylog_php70_mode
graylog_php70_mode:
	make php70_mode
	php $(build_tools_directory)/composer.phar require psr/log:'<2'
	php $(build_tools_directory)/composer.phar update --prefer-dist --no-dev

# Switch to Graylog PHP 8.1 mode. In case you need to build for PHP 8.1 and include graylog
.PHONY: graylog_php81_mode
graylog_php81_mode:
	make php81_mode
	php $(build_tools_directory)/composer.phar require graylog2/gelf-php
	php $(build_tools_directory)/composer.phar update --prefer-dist --no-dev

# Linting with PHP-CS
.PHONY: lint
lint:
	# Make devtools available again
	php $(build_tools_directory)/composer.phar install --prefer-dist

	# Lint with CodeSniffer
	vendor/bin/phpcs src/

# Run Unit tests
.PHONY: unit_test
unit_test:
	php $(build_tools_directory)/composer.phar install --prefer-dist
	vendor/bin/phpunit -c tests/phpunit.xml --testdox

.PHONY: zip
zip:
# In case of project build: use a predefined config
ifeq (integration,$(project))
	cp tests/integration/horde_config.php config/config.php
else ifeq (uni_mannheim,$(project))
	rm config/config.default.php || true
	rm config/config.php || true
else ifneq (, $(project))
	cp ../projects/$(project)/horde_config.php config/config.php
endif
	php $(build_tools_directory)/composer.phar install --prefer-dist --no-dev
	php $(build_tools_directory)/composer.phar archive -f zip --dir=build/archives --file=jmap-horde-$(version).zip

# In case of project build: rename and put jmap folder to root level
ifneq (, $(project))
	mkdir -p build/tmp/jmap
	unzip -q build/archives/jmap-horde-$(version).zip -d build/tmp/jmap
	cd build/tmp && zip -qmr jmap-horde-$(version)-$(project).zip jmap/ && mv jmap-horde-$(version)-$(project).zip ../archives
endif

.PHONY: fulltest
fulltest: lint unit_test
