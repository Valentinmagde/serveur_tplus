# Issue: “storage/logs/laravel-2019-11-22.log” could not be opened: failed to open stream: Permission denied

* Solution:
    Alright I got the answer:

    AWS AMI uses webapp as the web user, not apache or ec2-user as the file shows. In that case, the webapp user has no access rights over those files.

    - sudo chown $USER:webapp ./storage -R

    - find ./storage -type d -exec chmod 775 {} \;

    - find ./storage -type f -exec chmod 664 {} \;
    # link: https://stackoverflow.com/questions/58988042/storage-logs-laravel-2019-11-22-log-could-not-be-opened-failed-to-open-stream

# Issue: "storage/oauth-private.key" does not exist or is not readable.

* Solution:
  - php artisan passport:install
  # link: https://github.com/laravel/passport/issues/418

# Issue: Class 'DOMDocument' not found

* Solution:

    You need to install the DOM extension. You can do so on Debian / Ubuntu using:

    - sudo apt-get install php-dom
    # link: https://stackoverflow.com/questions/14395239/class-domdocument-not-found