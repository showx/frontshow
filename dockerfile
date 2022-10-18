FROM show/php-fpm:8.0.3
LABEL maintainer="shengsheng"
ENV FRONTSHOW_VERSION 1.0.0
RUN apt-get update
RUN apt-get install -y git-all
RUN apt-get install -y nodejs npm

COPY ./ /frontshow
CMD ["php","/frontshow/server.php","start"]
EXPOSE 9501