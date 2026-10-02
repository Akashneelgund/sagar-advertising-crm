#!/bin/bash
set -e

# Adapt Apache port to Render dynamic $PORT (default 80)
PORT="${PORT:-80}"
echo "Configuring Apache to listen on port $PORT..."
sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf
sed -i "s/:80/:$PORT/" /etc/apache2/sites-available/000-default.conf

# Check database setup mode
if [ -n "$DB_HOST" ] && [ "$DB_HOST" != "127.0.0.1" ] && [ "$DB_HOST" != "localhost" ]; then
    echo "Using external MySQL database at $DB_HOST..."
else
    echo "Starting local MariaDB daemon..."
    mkdir -p /var/run/mysqld /var/lib/mysql
    chown -R mysql:mysql /var/run/mysqld /var/lib/mysql

    if [ ! -d "/var/lib/mysql/mysql" ]; then
        mysql_install_db --user=mysql --datadir=/var/lib/mysql > /dev/null
    fi

    service mariadb start || /etc/init.d/mariadb start

    # Wait for MariaDB to become ready
    for i in {1..30}; do
        if mysqladmin ping -u root --silent 2>/dev/null; then
            echo "MariaDB is ready!"
            break
        fi
        echo "Waiting for MariaDB to start... ($i/30)"
        sleep 1
    done

    echo "Ensuring database 'sagar_advertising_crm' exists..."
    mysql -u root -e "CREATE DATABASE IF NOT EXISTS \`sagar_advertising_crm\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

    echo "Configuring database user permissions..."
    mysql -u root -e "
    CREATE USER IF NOT EXISTS 'crm_user'@'%' IDENTIFIED BY 'crm_password';
    GRANT ALL PRIVILEGES ON *.* TO 'crm_user'@'%' WITH GRANT OPTION;
    CREATE USER IF NOT EXISTS 'crm_user'@'localhost' IDENTIFIED BY 'crm_password';
    GRANT ALL PRIVILEGES ON *.* TO 'crm_user'@'localhost' WITH GRANT OPTION;
    CREATE USER IF NOT EXISTS 'crm_user'@'127.0.0.1' IDENTIFIED BY 'crm_password';
    GRANT ALL PRIVILEGES ON *.* TO 'crm_user'@'127.0.0.1' WITH GRANT OPTION;
    GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' WITH GRANT OPTION;
    FLUSH PRIVILEGES;
    " 2>/dev/null || true

    TABLE_COUNT=$(mysql -u root -N -s -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'sagar_advertising_crm';" 2>/dev/null || echo 0)
    if [ "$TABLE_COUNT" -eq 0 ]; then
        echo "Importing initial database schema & seed data from DATABASE_SETUP.sql..."
        mysql -u root --default-character-set=utf8mb4 sagar_advertising_crm < /var/www/html/DATABASE_SETUP.sql
        echo "Database imported and seeded successfully!"
    else
        echo "Database already contains $TABLE_COUNT tables. Skipping import."
    fi
fi

echo "Starting Apache web server on port $PORT..."
exec apache2-foreground