FROM mybmx_php

COPY --from=composer:1.5 /usr/bin/composer /usr/bin/composer

#RUN apk --no-cache add curl git subversion openssh openssl mercurial tini bash zlib-dev

# RUN apt-get install -y git
RUN docker-php-ext-install zip

ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_HOME /tmp
ENV COMPOSER_VERSION 1.5

WORKDIR /app

ENTRYPOINT ["/usr/bin/composer"]