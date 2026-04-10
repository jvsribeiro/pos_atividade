#!/bin/bash
set -euo pipefail

MYSQL_DATA_DIR="/var/lib/mysql"
MYSQL_DATABASE="${MYSQL_DATABASE:-agenda_contatos}"
MYSQL_APP_USER="${MYSQL_APP_USER:-agenda_user}"
MYSQL_APP_PASSWORD="${MYSQL_APP_PASSWORD:-agenda123}"
SQL_FILE="/var/www/html/banco_contatos.sql"

mkdir -p /var/run/mysqld "${MYSQL_DATA_DIR}"
chown -R mysql:mysql /var/run/mysqld "${MYSQL_DATA_DIR}"

if [ ! -d "${MYSQL_DATA_DIR}/mysql" ]; then
  echo "Inicializando o diretorio de dados do MySQL..."

  if command -v mysqld >/dev/null 2>&1; then
    mysqld --initialize-insecure --user=mysql --datadir="${MYSQL_DATA_DIR}" >/dev/null 2>&1 || true
  fi

  if [ ! -d "${MYSQL_DATA_DIR}/mysql" ] && command -v mariadb-install-db >/dev/null 2>&1; then
    mariadb-install-db --user=mysql --datadir="${MYSQL_DATA_DIR}" >/dev/null 2>&1
  fi

  if [ ! -d "${MYSQL_DATA_DIR}/mysql" ] && command -v mysql_install_db >/dev/null 2>&1; then
    mysql_install_db --user=mysql --datadir="${MYSQL_DATA_DIR}" >/dev/null 2>&1
  fi
fi

if [ -x /etc/init.d/mysql ]; then
  MYSQL_SERVICE="mysql"
elif [ -x /etc/init.d/mariadb ]; then
  MYSQL_SERVICE="mariadb"
else
  echo "Servico MySQL/MariaDB nao encontrado na imagem base." >&2
  exit 1
fi

service "${MYSQL_SERVICE}" start

until mysqladmin ping --silent >/dev/null 2>&1; do
  sleep 2
done

mysql <<SQL
CREATE DATABASE IF NOT EXISTS \`${MYSQL_DATABASE}\`;
CREATE USER IF NOT EXISTS '${MYSQL_APP_USER}'@'localhost' IDENTIFIED BY '${MYSQL_APP_PASSWORD}';
GRANT ALL PRIVILEGES ON \`${MYSQL_DATABASE}\`.* TO '${MYSQL_APP_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL

if ! mysql "${MYSQL_DATABASE}" -Nse "SHOW TABLES LIKE 'contatos';" | grep -q "^contatos$"; then
  mysql "${MYSQL_DATABASE}" < "${SQL_FILE}"
fi

exec apachectl -D FOREGROUND
