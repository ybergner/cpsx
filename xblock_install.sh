#!/bin/sh

sudo -u edxapp /edx/bin/pip.edxapp install xblock/
sudo cp -r xblock/cpsx/public /edx/app/edxapp/venvs/edxapp/local/lib/python2.7/site-packages/cpsx/