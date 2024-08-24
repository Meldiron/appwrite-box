FROM appwrite/appwrite:1.6.0-RC9

WORKDIR /usr/src/code

COPY ./supervisord/ /etc/

RUN apk update

# Install Appwrite Console

# Install Appwrite Assistant

# Install MariaDB
RUN apk add mariadb mariadb-client

# Install Redis
RUN apk add redis 

# Install Traefik

# Install Open Runtimes Executor

CMD ["supervisord", "-c", "/etc/supervisord.conf", "-u", "root"]
