FROM docker:dind
ARG DEBIAN_FRONTEND=noninteractive

WORKDIR /app

RUN apk update
RUN apk add php php-cli screen

COPY appwrite appwrite
COPY src src

RUN chmod +x src/start.sh

CMD ["sh", "-c", "src/start.sh"]