#!/usr/bin/env bash
# Installs the WordPress PHPUnit test library matching the installed WordPress version,
# and creates the test database. Run inside the web container; safe to re-run.
set -euo pipefail

WP_DIR=/var/www/html/wordpress
TESTS_DIR="$WP_DIR/wp-tests-lib"
WP_VERSION=$(wp core version --path="$WP_DIR")

if [ "$(cat "$TESTS_DIR/.version" 2>/dev/null)" != "$WP_VERSION" ]; then
	echo "Installing WordPress $WP_VERSION test library..."
	rm -rf "$TESTS_DIR"
	mkdir -p "$TESTS_DIR"
	curl -fsSL "https://github.com/WordPress/wordpress-develop/archive/refs/tags/${WP_VERSION}.tar.gz" \
		| tar -xz -C "$TESTS_DIR" --strip-components=3 \
			"wordpress-develop-${WP_VERSION}/tests/phpunit/includes" \
			"wordpress-develop-${WP_VERSION}/tests/phpunit/data"

	cat > "$TESTS_DIR/wp-tests-config.php" <<'PHP'
<?php
define( 'ABSPATH', '/var/www/html/wordpress/' );
define( 'WP_PLUGIN_DIR', '/var/www/html/wordpress/wp-content/test-plugins' );
define( 'WP_DEFAULT_THEME', 'default' );
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );

define( 'DB_NAME', 'tests' );
define( 'DB_USER', 'db' );
define( 'DB_PASSWORD', 'db' );
define( 'DB_HOST', 'db' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

$table_prefix = 'wptests_';

define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );
define( 'WP_PHP_BINARY', 'php' );
define( 'WPLANG', '' );
PHP

	echo "$WP_VERSION" > "$TESTS_DIR/.version"
fi

mysql -uroot -proot -hdb -e "CREATE DATABASE IF NOT EXISTS tests; GRANT ALL ON tests.* TO 'db'@'%';"
