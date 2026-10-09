FROM php:8.2-apache

# PDO SQLite untuk database (umumnya sudah aktif di image resmi)
RUN docker-php-ext-install pdo_sqlite

# Izinkan .htaccess (melindungi folder data/, uploads/, media/)
RUN printf '<Directory /var/www/html>\n    AllowOverride All\n    Require all granted\n</Directory>\n' \
      > /etc/apache2/conf-available/bkk.conf \
 && a2enconf bkk

COPY . /var/www/html/

# Folder yang harus bisa ditulis Apache (www-data)
RUN mkdir -p /var/www/html/data /var/www/html/uploads /var/www/html/media \
 && chown -R www-data:www-data /var/www/html/data /var/www/html/uploads /var/www/html/media

# Pastikan folder data tetap bisa ditulis (penting saat pakai bind mount di Linux)
RUN printf '#!/bin/sh\nchown -R www-data:www-data /var/www/html/data /var/www/html/uploads /var/www/html/media 2>/dev/null\nexec apache2-foreground\n' \
      > /usr/local/bin/bkk-entrypoint \
 && chmod +x /usr/local/bin/bkk-entrypoint
ENTRYPOINT ["bkk-entrypoint"]

EXPOSE 80
