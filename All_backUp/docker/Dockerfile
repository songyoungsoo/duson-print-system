# 두손기획인쇄 Docker 이미지
# PHP 7.4 + Apache + MySQL Client

FROM php:7.4-apache

LABEL maintainer="Duson Planning <dsp1830@naver.com>"
LABEL description="두손기획인쇄 Enterprise Print Management System"
LABEL version="1.0.0"

# 시스템 패키지 설치
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libcurl4-openssl-dev \
    libssl-dev \
    unzip \
    curl \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# PHP 확장 설치
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    mysqli \
    pdo_mysql \
    gd \
    mbstring \
    curl \
    zip \
    opcache

# Apache mod_rewrite 활성화
RUN a2enmod rewrite

# PHP 설정
RUN echo "upload_max_filesize = 50M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 50M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini

# 타임존 설정
RUN echo "date.timezone = Asia/Seoul" >> /usr/local/etc/php/conf.d/timezone.ini

# 작업 디렉토리 설정
WORKDIR /var/www/html

# 애플리케이션 복사
COPY --chown=www-data:www-data . /var/www/html/

# 업로드 디렉토리 생성 및 권한 설정
RUN mkdir -p /var/www/html/ImgFolder \
    /var/www/html/mlangorder_printauto/upload \
    /var/www/html/session \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/ImgFolder \
    && chmod -R 777 /var/www/html/mlangorder_printauto/upload

# 엔트리포인트 스크립트 복사
COPY docker/docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# 포트 노출
EXPOSE 80

# 엔트리포인트
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
