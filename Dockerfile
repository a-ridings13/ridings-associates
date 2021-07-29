FROM siteworxpro/php-node:7.4.2-12.14.1

RUN apt update && apt dist-upgrade -yq

RUN rm -Rf /var/www/html
ADD ./ /var/www/html

COPY ./var/etc/apache/apache2.conf /etc/apache2/apache2.conf
COPY ./var/etc/apache/sites-available/000-default.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

RUN composer install --no-dev
RUN sudo npm install
RUN sudo npm run production
RUN cp var/config/docker.php var/config/config.php
RUN chmod +x bin/*

EXPOSE 80
CMD ["bin/entry_point.sh"]
