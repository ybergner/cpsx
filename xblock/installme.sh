vi /home/nunpa/IBL-Tutoring-Chat-XBlock/setup.py; sudo -u edxapp /edx/bin/pip.edxapp install /home/nunpa/IBL-Tutoring-Chat-XBlock; sudo /edx/bin/supervisorctl -c /edx/etc/supervisord.conf restart edxapp:; tail -f /edx/var/log/supervisor/cmstderr.log

