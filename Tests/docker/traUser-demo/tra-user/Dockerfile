FROM debian:8.6
MAINTAINER Rafael Gálvez-Cañero <galvesband@gmail.com>

RUN apt-get update \
 && apt-get -y install apache2 libapache2-mod-php5 php5-mysql mysql-client ca-certificates wget \
 && echo Europe/Madrid > /etc/timezone \
 && dpkg-reconfigure --frontend noninteractive tzdata \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

# Install composer
COPY install-composer.sh /usr/bin/
RUN chmod +x /usr/bin/install-composer.sh \
 && /usr/bin/install-composer.sh \
 && rm /usr/bin/install-composer.sh \
 && ln -s /usr/bin/composer.phar /usr/bin/composer

# Configure apache
COPY 000-default.conf /etc/apache2/sites-available/

ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2

# Configure entry point
COPY wait-for-it.sh /usr/bin/wait-for-it.sh
COPY docker-entrypoint.sh /usr/bin/entrypoint.sh
RUN chmod +x /usr/bin/wait-for-it.sh /usr/bin/entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/usr/bin/entrypoint.sh"]

CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]