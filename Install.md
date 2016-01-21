
## INSTALLATION INSTRUCTIONS

# WARNINGS: (1) This file is not intended to be used as a single-purpose installer, although bits and pieces may reasonably be used as such. (2) The cps Xblock was written for the cypress release of OpenEdX, running the edx fullstack an Ubuntu 12.04 64-bit server. It is not guaranteed (or even expected) to work with any other release or server. (2) The domain name / IP of the server is hardcoded in two places in the cps Xblock code. This is unfortunate but we haven't figured out how to get around it, so make sure to change the domain name where indicated below.

# START HERE IF YOU DO NOT HAVE A WORKING CYPRESS INSTANCE

# To install edx fullstack locally with the cypress release using the Vagrant and VirtualBox (see https://github.com/edx/configuration/wiki/edx-Full-stack--installation-using-Vagrant-Virtualbox):

mkdir fullstack
cd fullstack
export OPENEDX_RELEASE=named-release/cypress
curl -L https://raw.githubusercontent.com/edx/configuration/master/vagrant/release/fullstack/Vagrantfile > Vagrantfile
vagrant plugin install vagrant-hostsupdater
vagrant up


## To install edx fullstack on AWS with the cypress release using the community Ubuntu 12.04 64 bit AMI (see  https://github.com/edx/configuration/wiki/edX-Ubuntu-12.04-64-bit-Installation)

## Note: We have checked that everything works starting with the following AMI releases:
##   ubuntu-precise-12.04-amd64-server-20151117 - ami-0011546a
##   ubuntu-precise-12.04-amd64-server-20150401 - ami-00615068

sudo apt-get update -y
sudo apt-get upgrade -y
sudo reboot
export OPENEDX_RELEASE=named-release/cypress
wget https://raw.githubusercontent.com/edx/configuration/master/util/install/sandbox.sh -O - | bash


## START HERE IF YOU ALREADY HAVE A WORKING CYPRESS INSTANCE

## clone cps Xblock

cd /edx/app/edxapp
sudo -u edxapp git clone https://github.com/ybergner/cpsx

## IMPORTANT: In the following two files, replace "your-domain.org" with the domain name or IP of you server. For instance, if using the Vagrant / VirtualBox setup, the default IP address used by VirtualBox is 192.168.33.10. So replace "your-domain.org" with "192.168.33.10" as instructed.

## File 1: "/edx/app/edxapp/cpsx/chatapp.conf"
## Replace the ServerName field. You may also want to replace the ServerAlias field (see http://httpd.apache.org/docs/2.2/mod/core.html#serveralias)

## File 2: "/edx/app/edxapp/venvs/edxapp/local/lib/python2.7/site-packages/cpsx/public/html/cpsx.html"
## Replace the embedded text in the <iframe> tag.


## Set up apache2. We will use apache2 on port 4444 to set up the virtual host without interfering with other OpenEdX ports. Note that the initial apache installation will fail because the default port 80 is already in use by nginx. That is OK, we replace the appropriate files following install.

sudo apt-get install apache2 -y
sudo apt-get install php5 php5-mysql libapache2-mod-php5 -y
sudo apt-get install php5 libapache2-mod-php5 libapache2-mod-auth-mysql php5-mysql -y

## Modify /etc/apache2/ports.conf

sudo cp -r cpsx/ports.conf /etc/apache2/

## Add the file chatapp.conf to /etc/apache2/sites-available and then symlink it to sites/enabled

sudo cp -r cpsx/chatapp /var/www
sudo cp -r cpsx/chatapp.conf /etc/apache2/sites-available/
sudo a2dissite default
sudo a2ensite chatapp.conf
sudo service apache2 reload

## Start the virtual host
sudo /etc/init.d/apache2 start

## Create a MySQL DDBB and import the structure from the sql.dump

mysql -u root -e "create database ajax_chat"
mysql -u root ajax_chat < cpsx/chatapp/mysql-dump/sql.dump

## At this point, you can test chatapp by visiting "<your-domain.org>:4444",


## To Set up cypress to work with the Xblock, add this line to cms.envs.json under FEATURES:
  ## "ALLOW_ALL_ADVANCED_COMPONENTS": true,
## Or just replace default cms envs file with this one:

sudo cp cpsx/cypress_envs/cms.env.json cms.env.json

## Finally install the XBlock
sudo -u edxapp /edx/bin/pip.edxapp install cpsx/xblock/

## And copy over these resources:
sudo cp -r cpsx/xblock/cpsx/public /edx/app/edxapp/venvs/edxapp/local/lib/python2.7/site-packages/cpsx/

## Restart the cms using
sudo /edx/bin/supervisorctl restart edxapp:

## And then add "cpsx" to list of advanced modules in course on Studio.
