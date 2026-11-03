# CI image for Kudos Donations.
#
# One image per PHP version, built by .forgejo/workflows/ci-image.yml and
# pushed to code.iseard.media/michael/kudos-ci:php<version>.
#
# Contains PHP + the extensions the plugin and WP test suite need, Composer,
# php-scoper (PHP >= 8.2 only), Node (from .nvmrc), git, unzip and subversion.

ARG PHP_VERSION=8.2

FROM docker.io/library/php:${PHP_VERSION}-cli

ARG NODE_VERSION=26.13.0
ARG PHP_SCOPER_VERSION=0.18.20

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1

# PHP 7.4/8.0 images are based on Debian bullseye, which is end-of-life and
# has moved to archive.debian.org.
RUN if grep -q bullseye /etc/os-release; then \
      rm -f /etc/apt/sources.list.d/*; \
      printf '%s\n' \
        'deb http://archive.debian.org/debian bullseye main' \
        'deb http://archive.debian.org/debian-security bullseye-security main' \
        > /etc/apt/sources.list; \
    fi \
 && apt-get update \
 && apt-get install -y --no-install-recommends \
      ca-certificates curl git jq unzip subversion xz-utils \
 && rm -rf /var/lib/apt/lists/* \
 && git config --system --add safe.directory '*'

COPY --from=ghcr.io/mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions mysqli gd zip intl bcmath \
 && echo 'memory_limit=-1' > /usr/local/etc/php/conf.d/zz-ci.ini

COPY --from=docker.io/library/composer:2.10 /usr/bin/composer /usr/local/bin/composer

# php-scoper 0.18 requires PHP 8.2+; only the build/release jobs need it.
RUN if php -r 'exit(PHP_VERSION_ID >= 80200 ? 0 : 1);'; then \
      curl -fsSL -o /usr/local/bin/php-scoper \
        "https://github.com/humbug/php-scoper/releases/download/${PHP_SCOPER_VERSION}/php-scoper.phar" \
      && chmod +x /usr/local/bin/php-scoper; \
    fi

# Official Node tarball, checksum-verified. Extracting in one RUN is much
# faster than COPY --from=node:..., which checksums each of npm's files.
RUN set -eux; \
    case "$(dpkg --print-architecture)" in \
      amd64) arch=x64 ;; arm64) arch=arm64 ;; \
      *) echo "Unsupported architecture" >&2; exit 1 ;; \
    esac; \
    file="node-v${NODE_VERSION}-linux-${arch}.tar.xz"; \
    cd /tmp; \
    curl -fsSLO "https://nodejs.org/dist/v${NODE_VERSION}/${file}"; \
    curl -fsSL "https://nodejs.org/dist/v${NODE_VERSION}/SHASUMS256.txt" | grep " ${file}\$" | sha256sum -c -; \
    tar -xJf "$file" -C /usr/local --strip-components=1 --no-same-owner \
      --exclude='*/CHANGELOG.md' --exclude='*/README.md' --exclude='*/LICENSE' \
      --exclude='*/include' --exclude='*/share'; \
    rm "$file"; \
    node --version; npm --version; composer --version
