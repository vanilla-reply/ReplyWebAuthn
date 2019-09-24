FROM shopware/development:latest

RUN apt update \
    && apt install -y libgmp-dev \
    && docker-php-ext-install gmp
RUN openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/ssl/private/selfsigned.key \
    -out /etc/ssl/certs/selfsigned.crt
    -subj "/C=GB/ST=London/L=London/O=Global Security/OU=IT Department/CN=example.com"
COPY ssl-params.conf /etc/apache2/conf-available/ssl-params.conf
COPY default-ssl.conf /etc/apache2/sites-available/default-ssl.conf
