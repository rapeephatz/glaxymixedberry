FROM php:8.2-cli

# ติดตั้ง mysqli
RUN docker-php-ext-install mysqli

WORKDIR /app
COPY . .

EXPOSE 3000
CMD ["sh", "-c", "php -S 0.0.0.0:$PORT"]

