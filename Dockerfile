ARG BASE_IMAGE=joaodockeiro/webserver-ubuntu:1.0
FROM ${BASE_IMAGE}

LABEL maintainer="Joao Vitor Ribeiro"
LABEL description="Agenda de contatos em Apache + PHP + MySQL no mesmo container, com deploy via Git e persistencia apenas do MySQL."

ENV DEBIAN_FRONTEND=noninteractive
ARG APP_REPO_URL=https://github.com/jvsribeiro/pos_atividade.git
ENV APP_REPO_URL=${APP_REPO_URL}

RUN apt-get update \
    && apt-get install -y --no-install-recommends git php-mysql \
    && rm -rf /var/lib/apt/lists/*

RUN rm -f /var/www/html/index.html \
    && git clone "${APP_REPO_URL}" /tmp/app \
    && test -f /tmp/app/cadastro_contatos.php \
    && test -f /tmp/app/banco_contatos.sql \
    && cp /tmp/app/cadastro_contatos.php /var/www/html/ \
    && cp /tmp/app/banco_contatos.sql /var/www/html/ \
    && rm -rf /tmp/app

COPY start.sh /usr/local/bin/start.sh

RUN chmod +x /usr/local/bin/start.sh

EXPOSE 80 3306

CMD ["/usr/local/bin/start.sh"]
