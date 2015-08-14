
# cd /edx/app/edxapp
# sudo git clone https://github.com/ybergner/CPSX-xBlock/ --branch yoav --single-branch
export CPSX_HOSTNAME="collaborative-assessment.org"

sudo -u edxapp /edx/bin/pip.edxapp install xblock/ --upgrade
sudo cp -r xblock/cpsx/public /edx/app/edxapp/venvs/edxapp/local/lib/python2.7/site-packages/cpsx/
sudo /edx/bin/supervisorctl -c /edx/etc/supervisord.conf restart edxapp:
