FROM show/php-fpm:8.0.3
LABEL maintainer="shengsheng"
ENV FRONTSHOW_VERSION 1.0.0
RUN sed -i 's#http://deb.debian.org#https://mirrors.163.com#g' /etc/apt/sources.list
RUN apt-get update
RUN apt-get install -y ssh 
# RUN apt-get install rsync
# 为了后期编辑方便
RUN apt-get install -y vim
# RUN apt-get install -y git-all
RUN apt-get install -y git
RUN apt-get install -y python2
# RUN apt-get install -y nodejs npm
# RUN mkdir /show
COPY ./node/node-v10.16.3-linux-x64.tar.xz /show/node-v10.16.3-linux-x64.tar.xz
RUN cd /show && tar -xvf node-v10.16.3-linux-x64.tar.xz -C /usr/local/
RUN ln -s /usr/local/node-v10.16.3-linux-x64/bin/node /usr/bin/node
RUN ln -s /usr/local/node-v10.16.3-linux-x64/bin/npm /usr/bin/npm
RUN npm config set registry https://registry.npm.taobao.org
RUN mkdir ~/.ssh
COPY ./ /frontshow
RUN cp /frontshow/ssh_key/id_rsa.pub ~/.ssh/id_rsa.pub
RUN cp /frontshow/ssh_key/id_rsa ~/.ssh/id_rsa
RUN cp /frontshow/ssh_key/config ~/.ssh/config
RUN chmod 700 ~/.ssh
RUN chmod 600 ~/.ssh/*
RUN cd /frontshow/ && composer install --prefer-dist
CMD ["php", "/frontshow/server.php", "start"]
EXPOSE 9501