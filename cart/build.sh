#!/bin/bash

## Get -e option (environment)
getopts e: option
ENV=${OPTARG:-dev}

_echo () {
   str="$@"
   dm="/"
   dc=2
   max=100
   len=$((${#str}))
   osf=$((max - len))
   os1=$((osf / 2))
   os2=$((max - os1 - len))
   echo; echo;
   for i in $(seq $((max))); do printf $dm; done; echo;
   for i in $(seq $dc); do printf $dm; done;
   for i in $(seq $((os1-dc))); do printf " "; done;
   printf "$str"
   for i in $(seq $((os2-dc))); do printf " "; done;
   for i in $(seq $dc); do printf $dm; done; echo;
   for i in $(seq $((max))); do printf $dm; done; echo;
   echo; echo;
}

_echo "Build started (ENV: $ENV)"


if [[ "$ENV" == "prod" ]]
then
   ## Install not including require dev for production
   php composer.phar install -n -o --no-dev --no-scripts || { printf "Composer install FAILED\n";  exit 1; }

   ## Replace .env.example with Env Vars 
   envsubst < ".env.example" > ".env"
else
   ## Install including require dev for tests
   php composer.phar install -n || { printf "Composer install FAILED\n";  exit 1; }

   ## Copy .env.dev to .env
   cp -f .env.dev .env
fi


## Print Env vars
_echo "Environment Vars"
cat .env

_echo "Build finished"

