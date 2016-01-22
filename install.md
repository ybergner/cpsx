
## Installation Instructions

**WARNINGS**: (1) The cps Xblock was written for the cypress release of OpenEdX, running the edX fullstack on an Ubuntu 12.04 64-bit server. It is not guaranteed (or even expected) to work with any other release or server. (2) The domain name / IP of the server is hardcoded into the cps Xblock code in two places. This is unfortunate, but we haven't figured out how to get around it, so make sure to change the domain name where indicated below.

This file is pretty long. Any help setting is up as a script would be welcome.

#### Start here if you don't have Cypress running

To [install the edx fullstack using Vagrant and VirtualBox]( https://github.com/edx/configuration/wiki/edx-Full-stack--installation-using-Vagrant-Virtualbox) with the Cypress release:

```
mkdir fullstack
cd fullstack
export OPENEDX_RELEASE=named-release/cypress
curl -L https://raw.githubusercontent.com/edx/configuration/master/vagrant/release/fullstack/Vagrantfile > Vagrantfile
vagrant plugin install vagrant-hostsupdater
vagrant up
```

To [install the edx fullstack on AWS using the community Ubuntu 12.04 64 bit AMI](https://github.com/edx/configuration/wiki/edX-Ubuntu-12.04-64-bit-Installation) with the Cypress release: 

```
sudo apt-get update -y
sudo apt-get upgrade -y
sudo reboot
export OPENEDX_RELEASE=named-release/cypress
wget https://raw.githubusercontent.com/edx/configuration/master/util/install/ansible-bootstrap.sh -O - | sudo bash
wget https://raw.githubusercontent.com/edx/configuration/$OPENEDX_RELEASE/util/install/sandbox.sh -O - | bash
```
Note: We have checked that cpsx works with the following AMI releases
   * ubuntu-precise-12.04-amd64-server-20151117 - ami-0011546a
   * ubuntu-precise-12.04-amd64-server-20150401 - ami-00615068

Also note that if you get a broken pipe after waiting on the second wget command, it is a good idea to check that everything was installed properly. If in doubt, run the wget command again. 

#### Start here if you are already running Cypress

Clone the cps Xblock. All following code snippits assume you are in `/edx/app/edxapp`.
```
cd /edx/app/edxapp
sudo -u edxapp git clone https://github.com/peterhalpin/cpsx
```

**IMPORTANT**: In the following two files, replace `your-domain.org` with the domain name or IP of your server. For instance, if using the Vagrant / VirtualBox setup, the default IP address used by VirtualBox is `192.168.33.10`. So replace `your-domain.org` with `192.168.33.10` as instructed.

* File 1: `cpsx/chatapp.conf`. Replace the `ServerName` field. You may also want to replace the [ServerAlias](http://httpd.apache.org/docs/2.2/mod/core.html#serveralias).

* File 2: `cpsx/xblock/cpsx/public/html/cpsx.html`. Replace the embedded text in the `<iframe>` tag.

Next set up Apache2. Note that the initial installation will fail because the default port 80 is already in use by nginx.  After the install we modify the default to port 4444.
```
sudo apt-get install apache2 -y
sudo apt-get install php5 php5-mysql libapache2-mod-php5 -y
sudo apt-get install php5 libapache2-mod-php5 libapache2-mod-auth-mysql php5-mysql -y
```

Modify the default port
```
sudo cp -r cpsx/ports.conf /etc/apache2/
```

Next copy chatapp.conf over to sites-available and then symlink it to sites-enabled
```
sudo cp -r cpsx/chatapp /var/www
sudo cp -r cpsx/chatapp.conf /etc/apache2/sites-available/
sudo a2dissite default
sudo a2ensite chatapp.conf
sudo service apache2 reload
```

Start the virtual host
```
sudo /etc/init.d/apache2 start
```

Create a MySQL DB and import the structure from the sql.dump
```
mysql -u root -e "create database ajax_chat"
mysql -u root ajax_chat < cpsx/chatapp/mysql-dump/sql.dump
```

At this point, you can test chatapp by visiting `<your-domain.org>:4444`.

Next set up Cypress to work with the Xblock. First add this line to `cms.envs.json` under FEATURES:

* `"ALLOW_ALL_ADVANCED_COMPONENTS": true,`

Or just replace the default file with this one:
```
sudo cp cpsx/cypress_envs/cms.env.json cms.env.json
```

Then install the XBlock and copy over some resources
```
sudo -u edxapp /edx/bin/pip.edxapp install cpsx/xblock/
sudo cp -r cpsx/xblock/cpsx/public /edx/app/edxapp/venvs/edxapp/local/lib/python2.7/site-packages/cpsx/
```

You may need to restart the cms
```
sudo /edx/bin/supervisorctl restart edxapp:
```

Your Cypress instance is now configured to allow students to use the chatapp while answering problems. The last step is to add `"cpsx"` to the `Advanced Module List` under the Advanced Settings of the course(s) in which you want to use the Xblock. 
