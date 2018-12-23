FROM 10.2.42.27:5000/nmi/php-web:latest
ARG BUILD_ENV=dev

ADD . /var/www/html

RUN sh build.sh -e ${BUILD_ENV}

WORKDIR /var/www/html
