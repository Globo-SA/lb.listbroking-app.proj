#!/usr/bin/env bash

usage ()
{
 echo $0
 echo "usage: $0 TagName Environment [schema-update]"
}

# test command line args
if [ $# -lt 2 ]
then
    usage
    exit 1
fi

TAGNAME=$1
ENVIRONMENT=$2

if [ "$ENVIRONMENT" == "prod" ] || [ "$ENVIRONMENT" == "PROD" ]
then
    PROJECT_PATH="/var/www/listbroking.adctools.com/httpdocs"
else
    PROJECT_PATH="/var/www/staging.listbroking.adctools.com/httpdocs"
fi

PREVIOUS_PATH=$(pwd);

echo "Go to production directory $PROJECT_PATH"
cd $PROJECT_PATH

if [[ $TAGNAME != *"/"* ]]; then
    rm -rf $TAGNAME
fi

if [ -d lb-adctools-com ]; then
    rm -rf lb-adctools-com
fi

git clone git@git.adclick.pt:tools/lb-adctools-com.git
cd lb-adctools-com
git checkout $TAGNAME

if [ "$ENVIRONMENT" == "prod" ] || [ "$ENVIRONMENT" == "PROD" ]
then
    cp deploy/prod_parameters.yml app/config/parameters.yml
else
    cp deploy/staging_parameters.yml app/config/parameters.yml
fi

composer install

if [ "$3" ] && [ "$3" == "schema-update" ]
then
    app/console doctrine:schema:update  --env=prod --force
fi

echo "Update static files"
bower update
app/console assets:install
app/console assetic:dump --env=ENVIRONMENT

setfacl -R -m g:apache:rwX -m g:users:rwX app/cache app/logs app/spool
setfacl -dR -m g:apache:rwX -m g:users:rwX app/cache app/logs app/spool

cd ..
mv lb-adctools-com $TAGNAME

rm production
ln -s $TAGNAME production

cd $PREVIOUS_PATH
