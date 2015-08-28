#!/bin/sh
##
## INSTALLATION INSTRUCTIONS 

## We will try to do all of this starting from a barebones Ubuntu installation using
## the birch.2 release (see here for updated information
## https://github.com/edx/configuration/wiki/edX-Ubuntu-12.04-64-bit-Installation)

## On Amazon, if using a community Ubuntu 12.04 64 bit AMI, it is necessary to expand the
## root partition before launching. The default is 8GB. Make it at least 25GB (preferance is 50GB). 

## The remainder assumes that the following instance is launched with sufficient storage:

## AMI ID ubuntu-precise-12.04-amd64-server-20150401 (ami-00615068)

## Connect to the remote server

## sudo apt-get update -y
## sudo apt-get upgrade -y
## sudo reboot

export OPENEDX_RELEASE=named-release/birch.2
wget https://raw.githubusercontent.com/edx/configuration/master/util/install/sandbox.sh -O - | bash


# To set up the virtual host without interfering with other OpenEdX ports, 
# we will use port 4444 in the attached files

sudo apt-get install apache2
sudo apt-get install php5 php5-mysql libapache2-mod-php5
sudo apt-get install php5 libapache2-mod-php5 libapache2-mod-auth-mysql php5-mysql

# On OpenEdX, apache installation will fail because port 80 is already in use by nginx.
# Later we will replace the appropriate files

# Modify /etc/apache2/ports.conf (replace with included version)
# Add the file chatapp.conf to /etc/apache2/sites-available
# and then symlink it to sites/enabled

cd /edx/app/edxapp
sudo -u edxapp git clone https://github.com/ybergner/CPSX-xBlock/ --branch yoav
sudo cp -r CPSX-xBlock/chatapp /var/www
sudo cp -r CPSX-xBlock/ports.conf /etc/apache2/
sudo cp -r CPSX-xBlock/chatapp.conf /etc/apache2/sites-available/

## OLD WAY
# sudo ln -s /etc/apache2/sites-available/chatapp.conf /etc/apache2/sites-enabled/chatapp
# sudo rm /etc/apache2/sites-enabled/000-default
#### NEW WAY
#
sudo a2dissite default
sudo a2ensite chatapp.conf

# Need to add this line to cms.envs.json under FEATURES
# "ALLOW_ALL_ADVANCED_COMPONENTS": true,
# or just replace cms envs file with this one
sudo cp CPSX-xBlock/cms.env.json .
sudo cp CPSX-xBlock/lms.env.json .

# Now install the XBlock
sudo -u edxapp /edx/bin/pip.edxapp install CPSX-xBlock/xblock/
sudo cp -r CPSX-xBlock/xblock/cpsx/public /edx/app/edxapp/venvs/edxapp/local/lib/python2.7/site-packages/cpsx/
# # If installed: Upgrade the XBlock using --upgrade
# sudo -u edxapp /edx/bin/pip.edxapp install yourXBlock/ --upgrade

# restart the cms
sudo /edx/bin/supervisorctl restart edxapp:

# Then this should work to start the virtual host
sudo /etc/init.d/apache2 start


# Create a MySQL DDBB and import the structure from the sql.dump

mysql -u root create database ajax_chat

# Dump MySQL structure to the new created DDBB, 

mysql -u root ajax_chat < CPSX-xBlock/chatapp/mysql-dump/sql.dump

# test chatapp by visiting collaborative-assessment.org:4444

# Then, of course, need to add "cpsx" to list of advanced modules in course.
