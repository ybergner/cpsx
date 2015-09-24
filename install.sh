#!/bin/sh
##
## INSTALLATION INSTRUCTIONS 
##
## WARNING: although this file is set up as a bash script,
## it is not intended to be used as a single-purpose installer.
## However, bits and pieces may reasonably be used!
##

## We will try to do all of this starting from a barebones Ubuntu installation using
## the cypress release (see here for updated information
## https://github.com/edx/configuration/wiki/edX-Ubuntu-12.04-64-bit-Installation)

## On Amazon, if using a community Ubuntu 12.04 64 bit AMI, it is necessary to expand the
## root partition before launching. The default is 8GB. Make it at least 25GB (preferance is 50GB). 

## The remainder assumes that the following instance is launched with sufficient storage:

## AMI ID ubuntu-precise-12.04-amd64-server-20150401 (ami-00615068)

## Bare cypress installation as follows:

## Connect to the remote server

## sudo apt-get update -y
## sudo apt-get upgrade -y
## sudo reboot

## export OPENEDX_RELEASE=named-release/cypress
## wget https://raw.githubusercontent.com/edx/configuration/master/util/install/sandbox.sh -O - | bash


# To set up the virtual host without interfering with other OpenEdX ports, 
# we will use apache2 on port 4444 in the subsequent steps
# On OpenEdX, apache installation will fail because port 80 is already in use by nginx.
# Later we will replace the appropriate files

sudo apt-get install apache2
sudo apt-get install php5 php5-mysql libapache2-mod-php5
sudo apt-get install php5 libapache2-mod-php5 libapache2-mod-auth-mysql php5-mysql


# Modify /etc/apache2/ports.conf (replace with included version)
# Add the file chatapp.conf to /etc/apache2/sites-available
# and then symlink it to sites/enabled

cd /edx/app/edxapp
sudo -u edxapp git clone https://github.com/ybergner/cpsx

### IMPORTANT: You must edit chatapp.conf to reflect your server's domain name
sudo cp -r cpsx/chatapp /var/www
sudo cp -r cpsx/ports.conf /etc/apache2/
sudo cp -r cpsx/chatapp.conf /etc/apache2/sites-available/

## OLD WAY
# sudo ln -s /etc/apache2/sites-available/chatapp.conf /etc/apache2/sites-enabled/chatapp
# sudo rm /etc/apache2/sites-enabled/000-default
#### NEW WAY
#
sudo a2dissite default
sudo a2ensite chatapp.conf

# Then this should work to start the virtual host
sudo /etc/init.d/apache2 start

# The app still won't work until the databases it needs are created.
# Create a MySQL DDBB and import the structure from the sql.dump

mysql -u root -e "create database ajax_chat"
mysql -u root ajax_chat < cpsx/chatapp/mysql-dump/sql.dump

# can test chatapp by visiting collaborative-assessment.org:4444


# Need to add this line to cms.envs.json under FEATURES
# "ALLOW_ALL_ADVANCED_COMPONENTS": true,
# or just replace default cms envs file with this one
sudo -u edxapp cp cpsx/cypress_envs/cms.env.json .
# OPTIONAL: sudo cp cpsx/cypress_envs/lms.env.json .

# Now install the XBlock
sudo -u edxapp /edx/bin/pip.edxapp install cpsx/xblock/
# and copy over these resources
sudo cp -r cpsx/xblock/cpsx/public /edx/app/edxapp/venvs/edxapp/local/lib/python2.7/site-packages/cpsx/
# # If installed: Upgrade the XBlock using --upgrade

# restart the cms
sudo /edx/bin/supervisorctl restart edxapp:

# Then, of course, need to add "cpsx" to list of advanced modules in course.
