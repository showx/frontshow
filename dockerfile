FROM show/php-fpm:8.0.3
LABEL maintainer="shengsheng"
ENV FRONTSHOW_VERSION 1.0.0
RUN sed -i 's#http://deb.debian.org#https://mirrors.163.com#g' /etc/apt/sources.list
RUN apt-get update
RUN apt-get install -y ssh rsync
# RUN apt-get install -y git-all
RUN apt-get install -y git
RUN apt-get install -y nodejs npm
RUN mkdir ~/.ssh
COPY ./ /frontshow
RUN cp /frontshow/ssh_key/id_rsa.pub ~/.ssh/id_rsa.pub
RUN cp /frontshow/ssh_key/id_rsa ~/.ssh/id_rsa
RUN chmod 700 ~/.ssh
RUN chmod 600 ~/.ssh/*
CMD ["php", "/frontshow/server.php", "start"]
EXPOSE 9501