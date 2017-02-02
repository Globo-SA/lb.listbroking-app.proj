echo "Linking parameters"
ln -sf deploy/dev_parameters.yml app/config/parameters.yml

echo "Installing dependencies"
dshell composer install

echo "Creating database"
docker exec -i dev.mysql.1 mysql -uroot listbroking \
    < deploy/development_setup.sql \
    && echo "database imported" \
    || (echo "[ERROR] database import failed")

echo "Update static files"
dshell bower update
dshell app/console assets:install --env=dev
dshell app/console assetic:dump --env=dev



