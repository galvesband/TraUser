# Run a demo docker image #

This is not a perfect solution, but is useful because it allows
me to set a demo of the bundle pretty fast. The cons of this
approach is that the project source tree is served as a volume
to the docker container, which is far from ideal.

## How to ##

### Launching it ###

Set up `docker` and `docker-compose` in your system or server,
cd into this directory and do

```bash
$ docker-compose up --build -d
```

This will build a simple apache image and download an image
with `mariadb`, and then it will launch them and serve the
app from the port `8000`.

But **be careful**:

 - The database password should be changed, especially
   if the server faces internet.
 
 - The docker image has access to the source tree and
   in fact will overwrite the file
   `Tests/test-app/config/parameters.yml` to set up the
   database information. THIS WILL PROBABLY MESS UP
   THE PERMISSIONS ALL OVER THE vendor AND cache DIRs. Beware.
   
 - The file `Tests/test-app/config/parameters.yml` should
   not exists before launching the container...
   
And take into account that mailing will not be available
because no mail service is available to the application or
the image.

### Setting it up ###

Enter in the container and add an user, install the assets, etc:

```bash
$ docker exec -it trauserdemo_tra-user_1 /bin/bash
# cd /var/www/traUserBundle
# Tests/test-app/bin/console doctrine:schema:create
# Tests/test-app/bin/console Assets:install --symlink Tests/test-app/web/
# Tests/test-app/bin/console galvesband_tra_user:add_command -s username email@host.net password
# Tests/test-app/bin/console doctrine:fixtures:load --fixtures=Tests/Fixtures/LoadRoleData.php --fixtures=Tests/Fixtures/LoadGroupData.php --append
```

The browse to `http://server-name:8000/admin/login` and use
the auth info from the user you just created to log in and
set up roles, groups, users, etcétera.