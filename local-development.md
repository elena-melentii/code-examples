---
title: Local Development
description: 
published: true
date: 2022-03-15T15:36:37.454Z
tags: 
editor: markdown
dateCreated: 2019-09-24T15:24:26.265Z
---

[![Docker Repository on Quay](https://quay.io/repository/45air/wordpress/status 'Docker Repository on Quay')](https://quay.io/repository/45air/wordpress)

Docker based local development environment that works on Mac, Windows, and Linux. Geared to WordPress development but generally any type of site should work. Uses public Docker images hosted on Docker Hub and our own public Docker images hosted on Quay for setting up the development enviroment for your sites.

Extra add ons and automation for 45AIR customers which can be configured by running `airlocal auth config`, you can check your logged in status with `airlocal auth status` as well after configuring your authentication to make sure it is still valid.

# Prerequisites

> It is recommended that you use the latest versions of docker and docker-compose.

{.is-info}

* [Docker](https://docs.docker.com/install/)
* [Docker Compose](https://docs.docker.com/compose/install/)
* [Node v10](https://nodejs.org/dist/latest-v10.x/) (or [nvm](https://github.com/nvm-sh/nvm#installation-and-update) set to use v10)
* npm >= 6.4.1

> **MAC USERS using Docker for Desktop**: Disable ALL experimental features, especially gRPC FUSE (from Preferences > Experimental Features):
> ![screen_shot_2021-01-20_at_2.02.14_pm.png](/screen_shot_2021-01-20_at_2.02.14_pm.png)
{.is-warning}


# Installation

> **WINDOWS USERS: From this point forward you are using the Ubuntu Subsystem For Linux Shell**
{.is-warning}

1) Let's first make sure we can run docker commands without using `sudo` as this will cause errors during airlocal site provisioning.

```bash
docker ps | grep 'permission denied'
```

If the above command comes back with no output then we are golden you can skip to step 2, but if it returns something into the console we need to add our user to the docker group.

```bash
sudo usermod -aG docker $USER
```

Log out and log back in so that your group membership is re-evaluated.

If testing on a virtual machine, it may be necessary to restart the virtual machine for changes to take effect.

On a desktop Linux environment such as X Windows, log out of your session completely and then log back in.

Verify you can run `docker ps | grep 'permission denied'` now and it comes back empty.

If you initially ran Docker CLI commands using `sudo` before adding your user to the `docker` group, you may see the following error, which indicates that your `~/.docker/` directory was created with incorrect permissions due to the `sudo` commands.

```
WARNING: Error loading config file: /home/user/.docker/config.json -
stat /home/user/.docker/config.json: permission denied
```

To fix this problem, either remove the `~/.docker/` directory (it will be recreated automatically, but any custom settings will be lost), or change its ownership and pemissions using the following commands:

```bash
sudo chown "$USER":"$USER" /home/"$USER"/.docker -R
sudo chmod g+rwx "/home/$USER/.docker" -R
```

2) We want to make sure we clean up any older versions that might be globally installed as we now recommend that you install the package locally for just your logged in user as the global package can be hard to upgrade and problematic.

```bash
sudo rm -rf /usr/lib/node_modules/@45air
sudo rm -rf /usr/lib/node_modules/@tmbi
sudo rm -rf ~/.npm
```
If you are a Mac user on Monterey or later (Mac OSX 12.x), the above should be for your own local user directory, so they would be like this:
```bash
sudo rm -rf ~/node_modules/@tmbi
sudo rm -rf ~/node_modules/@45air
sudo rm -rf ~/.npm
```
Then we want to make sure we don't have any simlinks or references to `airlocal` in our path.

```bash
which airlocal
which airlocal-hosts
```

If anything is returned from the above two commands then we need to delete that reference off the system. So for example a common return might be `/usr/local/bin/airlocal` and we would run...

```bash
sudo rm /usr/local/bin/airlocal
```

Keep removing and retrying `which airlocal` and `which airlocal-hosts` until they both return nothing.

3) Now we will be installing the latest airlocal package from npm in our users home directory and add the `node_modules/.bin` directory into our `PATH`.

You will need your GitLab PAT (Personal Access Token) which you should set as the value of `GITLAB_TOKEN` in the command below.

The command below will install the latest version of airlocal from [https://devops.45air.co/tmbi/cli/air-local-docker/-/packages](https://devops.45air.co/tmbi/cli/air-local-docker/-/packages).

```bash
cd ~
export GITLAB_TOKEN=""
npm config set @tmbi:registry https://devops.45air.co/api/v4/packages/npm/
npm config set -- '//devops.45air.co/api/v4/packages/npm/:_authToken' "${GITLAB_TOKEN}"
npm install @tmbi/air-local-docker
echo "export PATH=$PATH:~/node_modules/.bin" >> ~/.bashrc
source ~/.bashrc
```
If you are a Mac user and are on Monterey or later (OSX 12.x), you should run the last two commands above as for zsh:
```
echo "export PATH=$PATH:~/node_modules/.bin" >> ~/.zshrc
source ~/.zshrc
```
Verify we have airlocal in our `PATH` by running...

```bash
which airlocal
```
Which should now return something like `/home/YOURUSERNAME/node_modules/.bin/airlocal`. Then verify the commands are working...

```bash
airlocal version
```
The version number should be output in your shell.

> **MAC USERS:** Edit `~/node_modules/@tmbi/air-local-docker/global/docker-compose.yml` and delete one of the two `/` in line 21 (in `services > gateway > volumes`). The line must read `- "/var/run/docker.sock:/tmp/docker.sock:ro"`
>  ![file-to-fix.png](/file-to-fix.png)
{.is-warning}

> **Note on ports:** airlocal uses ports 80 and 3306 from the host machine. Make sure those ports are free (e.g. shut down any other web server or MySQL instance that may be running in your machine).
{.is-warning}

4) Setup authentication with docker so we can pull down the baseimage from GCR.

Create a file in the root of your user directory named `gcr.json` containing the following...

```json
{
  "type": "service_account",
  "project_id": "air-services",
  "private_key_id": "cf398f8a04de2b405accb2acdb4de5e2ae262579",
  "private_key": "-----BEGIN PRIVATE KEY-----\nMIIEvwIBADANBgkqhkiG9w0BAQEFAASCBKkwggSlAgEAAoIBAQDOO9c90acoRgB/\ns0kKAV50AkhmoxwAZZzE2f98glj2EiH2skpunI1Falgw0vwURTcrkM5oVRC3zHgf\n6E0uzMU9DeNavJ5qhyf1qm6cAOXp9cD/uF/bCGHoKjdJC4yc6WhsEOvoTbXoQiIp\nUr/XwuOp5P1stoPLXlSCIMLcooDXXCly6DO9atVlm75ZaTXedzjhsjRD2JVSpCUV\n9rqJSjDN52uYSMVY5txIElccG1BMBBTp2baJP8hd1nTry2OvZZKK49gAuMl1aRgr\ntbTWgZwYfrrzA+LAlvNrsLAPotu4U9ki6ZfLG+bKGRBGgM4xP6zVgWFPQnQugVzE\nywdpwiNTAgMBAAECggEAHr6ZBeQ9USmuZVsq5kzx8cVtWa+zOvPq/QWqqrsb3AM0\nAPzvT7IS9Wg3IYAyiCsRYHND8hXMWjonJkqRRwrL1KA/ZoV78FGZyWvQ9XzEya4T\nSYwI7jQ/tEam20LXgYTiRkn3gTgkiC1jvllRtC6flfJHPW+wEh9L5eJQamfnYTNd\niEHr7/IkPrRM1+FSRCgVdqH0BOaIJVgxxqGGjh5NudkMc+RABn7fF4WKEblE82xw\nLC9AvqBCOPTdPk5U40mYMcqe4Xq1o+55ELtcZJ4CNCoi2T65cM6+J3aULwdNulwA\nVQWuIztPu9l+9TT8xwEbt5bystfi8uThFao8ZEAz0QKBgQDs2DQxt4ZPXVCxmEta\nEQjfTdUrFQ5SOZBl7l008w79bAIDqRmTxdsBu8/iP/f+ZJ6Zd6sc/JzECGvWEwTM\n1OJv5jLsJWVoKoEFLQovM0FvC/BpKIXxt7zdAVlOP64RkJBwbrZfAgfh9XrdSeGP\n3A+eIGN/dGIYrs6dFCYw66cszwKBgQDe6dl4/I/9IKW5Du8pYEL34BN3ybz55BYg\nw6DuqzkpNjjhOl4ZPSFnMYeJlE6/C8genrBvk2xWAHBhPWe0ULncLJvxCtDPHgtr\n8o2fG3U9tZtKGoas3hWYRTga+Hqc8DiES3EHmvnX44VxVKoXgCm/+fW7coWmNVbv\nasDEtuWqPQKBgQC3sPPCP5G2yiwEoZaHk0CrWIVlNZ523ViGBfpaT80ndfaV+jfx\nKXozfQO9eVmQ/18Wrf6Tq8S2McXZMcT+THoWyZZjGpns0VJhvk3wz7MHOl5KYPiJ\nwbSEQECQdMk/rDyqRuPBCiUs9iRFrsB3v/iI7pvcxVozxJhQscjxFkQBdwKBgQDH\nyaviZerfog0mSZ5M5TvgUfLg3+0Bw02Z2/w3LTs0FqbwJpID6OgxRxEFW+kgDX01\nBGF5/xWQFyCh5pk14UxTa5/wcBMqLvFptKD9w2xW/JfL2O5vrTSJnzBj6+RpGFxv\nJfaOLHZU6lTFeG5iVboVmACIGmJyz0e1mWZgaNR6uQKBgQCvdkZMf6IiiPwX0zrO\n+tY3p0bwJbwD05jTpAydzpqP/65WsX7i5MV1sNUE2FHZQz30WOp0O7zEXy/m44jh\n2frrj1SbyFDNj+8jzLkZ4muQWi/Cu4FxwSF31b7ersGmUaLAOo5tLE8MK1+fOQEt\nlIPez170iF2/LWj9soQc/57tQg==\n-----END PRIVATE KEY-----\n",
  "client_email": "gcr-pull-local-development@air-services.iam.gserviceaccount.com",
  "client_id": "115909541519705935719",
  "auth_uri": "https://accounts.google.com/o/oauth2/auth",
  "token_uri": "https://oauth2.googleapis.com/token",
  "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
  "client_x509_cert_url": "https://www.googleapis.com/robot/v1/metadata/x509/gcr-pull-local-development%40air-services.iam.gserviceaccount.com"
}
```
Then run the following command to authenticate docker with the credentials we just created.

```bash
cat gcr.json | docker login -u _json_key --password-stdin https://gcr.io
```

You should now be able to pull from our private Google container registry, you can verify with the following command. If this fails you may need to explore other options for authentication from the official docs. [https://cloud.google.com/container-registry/docs/advanced-authentication](https://cloud.google.com/container-registry/docs/advanced-authentication)

```bash
docker pull gcr.io/air-services/tmbi/docker/baseimage-wordpress
```

**NOTE: MacOS users if you have an issue pulling after running docker login follow these direction to fix**

It seems to be a manifestation of this bug with the MacOS keychain -> [https://github.com/kubernetes/kompose/issues/1043#issuecomment-453376891](https://github.com/kubernetes/kompose/issues/1043#issuecomment-453376891)

```bash
rm ~/.docker/config.json
```

In Docker for Mac preferences, untick "Securely store Docker logins in macOS keychain", then restart your machine. Then run the login command again.

```bash
cat gcr.json | docker login -u _json_key --password-stdin https://gcr.io
```

You should now be able to pull the image without an authentication error.


# Configuration

The first time you run a Air Local Docker command, default configuration settings will be used if you have not manually configured Air Local Docker beforehand. By default, Air Local Docker will store all environments within the `air-local-docker-sites` directory (*normally in your home folder but it may vary*) and try to manage your hosts file when creating and deleting environments.

If you would like to customize the environment path or opt to not have Air Local Docker update your hosts file, run `airlocal configure` and follow the prompts.

## Updating

> **NOTE: Updating airlocal from very old versions (< 0.3.0) requires to re-create the MySQL data volume and delete all sites. Take a backup of your sites' files and data before running these steps! You will only need to do this if you run into mysql related errors when setting up new sites.**
{.is-warning}

```bash
airlocal delete all
docker volume rm global_mysqlData
docker volume create global_mysqlData
```
NOTE: If the above command ```docker volume rm global_mysqlData``` gives you an error like this: 
```Error response from daemon: remove global_mysqlData: volume is in use - [49da905197195bc5fd9402798d4ab844e24aa67b9af474682caf25997c594f7e]```
Use the following command to fix it:
```
docker rm -f $(docker ps -a -q)
```
Going into Docker Desktop and forcing the mySQL containers to stop will not work. You will want the above command.
## Updating images

`airlocal image update all`

# Commands Documentation

```bash
airlocal --help
```
Or check out -> [https://devops.45air.co/tmbi/cli/air-local-docker](https://devops.45air.co/tmbi/cli/air-local-docker)

# Local Environment Setup

In this example I'll show how to start working on [fhm-docker](https://devops.45air.co/tmbi/fhm-docker) repo locally using the `develop` branch code and the `develop` database currently in use, it would be similar for any site which has been ported to the new docker setup.

> This guide is assuming you are using the airlocal default settings and are on a hosted VM pay attention to if the commands are to be run on the VM machine or your local machine. Only minimal steps will be run on your local computer so assume unless it is said otherwise all commands take place logged into the VM as your user and you have already run through the full **Installing** portion of the guide successfully.
{.is-info}

1) First lets stop all existing sites you may be running with `airlocal` and also any other docker containers randomly running. This will make sure we are in a good place to begin and won't get errors because of anything external.

```bash
airlocal stop all
docker stop $(docker ps -a -q)
```

2) Verify we stopped everything... The command below will print all the running docker containers on your system, if it comes back blank your good to go. If it comes back with running containers you should stop them before continuing.

```bash
docker ps
```

3) Now lets take care of making sure our system is up to date as well as this is good practice when working on linux machines.

```bash
sudo apt-get update
sudo apt-get upgrade -y
```
**NOTE:** MAC USERS: the above is for linux, not Mac Terminal. Ignore.
---

If you have not setup an SSH key in Ubuntu Shell yet then follow these directions.

```bash
ssh-keygen -t ed25519
```

Just use the defaults for all questions, keep hitting enter, don't set a password.

Let get the host key for our Gitlab so we can skip that step later when cloning.

```bash
ssh-keyscan devops.45air.co >> ~/.ssh/known_hosts
chmod 644 ~/.ssh/known_hosts
```

# Authenticating AirLocal

1) Let's authenticate `airlocal` with our [DevOps Gitlab](https://devops.45air.co) account. You will need to create a PAT (Personal Access Token) for your account and know your group ID to continue.
**HINT... HINT...** TMBI group ID is `4`

![screenshot_from_2019-12-12_20-28-23.png](/screenshot_from_2019-12-12_20-28-23.png)

You can generate a PAT here -> https://devops.45air.co/profile/personal_access_tokens
You will want to give it all available permissions and depending on your security profile set an expiration date or not.

```bash
airlocal auth config
```

2) Verify authentication.

```bash
airlocal auth status
```

# Creating New Environment

1) Ok now it is time to create our new site for development. When authenticated the prompts will be slightly different then you may be used to, but the only extra info you will need to gather is the project ID of the repo for the site you are developing. FHM is `810` for example.

![screenshot_from_2019-12-12_20-41-43.png](/screenshot_from_2019-12-12_20-41-43.png)

```bash
test@ip-10-231-0-104:~/air-local-docker-sites$ airlocal create
? What is the devops.45air.co Gitlab project ID? (Project Overview > Details - Right under the repo title) 810
? Which environment would you like setup? develop
? What is the primary hostname for your site? (Must be a .test domain Ex: docker.test) fhm.test
? Are there additional domains the site should respond to? No
? Do you want to set a proxy for media assets? (i.e. Serving /wp-content/uploads/ directory assets from a production site) Yes
? Proxy URL http://staging.familyhandyman.com
```
NOTE: Proxy url should be **http** and not **https**. Check your full URL in the browser if you're having issues like not being able to reach the site, or the site cannot find resources like the CSS.

You would be looking to see something like this...

```bash
✔ Done building
✔ Done setting up project
Successfully Created Site!
Visit your new site @ http://fhm.test
NOTE: It could take up to 60 seconds for the site to come completely up
test@ip-10-231-0-104:~/air-local-docker-sites$ ls
fhm-test
test@ip-10-231-0-104:~/air-local-docker-sites$ cd f*
test@ip-10-231-0-104:~/air-local-docker-sites/fhm-test$ ls
config  docker-compose.yml  fhm-docker
test@ip-10-231-0-104:~/air-local-docker-sites/fhm-test$ ll
total 28
drwxrwxr-x 4 test test 4096 Dec 13 01:49 ./
drwxrwxr-x 3 test test 4096 Dec 13 01:47 ../
-rw-rw-r-- 1 test test   60 Dec 13 01:48 .config.json
-rw-rw-r-- 1 test test   32 Dec 13 01:47 .env
drwxrwxr-x 5 test test 4096 Dec 13 01:48 config/
-rw-rw-r-- 1 test test 1219 Dec 13 01:48 docker-compose.yml
drwxrwxr-x 8 test test 4096 Dec 13 01:49 fhm-docker/
```

Your repo has been cloned already in the site folder `fhm-docker` and some information about the install itself is located in `.config.json` this file is how `airlocal` knows its a managed site.

2) Because of the reverse proxy the website will only respond to the URL we passed during `airlocal create` so for example the IP to the VM for this demo is `3.228.49.190` your VM won't have this same IP, odviously, but for this example if I try visiting http://3.228.49.190 I will get a NGINX 500 error page. This is expected behavior. What we need to do is set the URL `fhm.test` in our local `hosts` file.

> This step is happening on our PC **NOT** the VM we have been running on all other commands on.
{.is-warning}

I personally use Fedora Linux so my hosts file is located at `/etc/hosts` this should be the same for MacOS. You windows folk will be editing `C:\Windows\System32\Drivers\etc\hosts`. Also your IP address for your VM will be different, and if your running this locally without a VM you would use `127.0.0.1`.

```bash
sudo echo "3.228.49.190 fhm.test" >> /etc/hosts
```

Now we can visit `http://fhm.test` in our browser on our PC and the site should be resolving the WordPress setup and configuration page because we do not have a database setup yet. This is expected at this point and if you see this page you are doing :ok_hand: :thumbsup:


# Container Overview

Now lets go ahead and take a look at what is running just for reference. We are back on the VM now btw.

```bash
test@ip-10-231-0-104:~/air-local-docker-sites/fhm-test$ docker ps
CONTAINER ID        IMAGE                    COMMAND                  CREATED             STATUS              PORTS                                            NAMES
6029aef19338        fhm-test_phpfpm          "/usr/local/bin/dock…"   13 minutes ago      Up 13 minutes       80/tcp, 9000/tcp                                 fhm-test_phpfpm_1
2f06f519a908        redis:latest             "docker-entrypoint.s…"   13 minutes ago      Up 13 minutes       6379/tcp                                         fhm-test_redis_1
3e088434d0f5        phpmyadmin/phpmyadmin    "/docker-entrypoint.…"   18 minutes ago      Up 14 minutes       0.0.0.0:8092->80/tcp                             global_phpmyadmin_1
1eeb42864a4b        mysql:5.7                "docker-entrypoint.s…"   18 minutes ago      Up 14 minutes       0.0.0.0:3306->3306/tcp, 33060/tcp                global_mysql_1
bf4929392cfa        schickling/mailcatcher   "mailcatcher --no-qu…"   18 minutes ago      Up 14 minutes       0.0.0.0:1025->1025/tcp, 0.0.0.0:1080->1080/tcp   global_mailcatcher_1
e3180b7acfc6        jwilder/nginx-proxy      "/app/docker-entrypo…"   18 minutes ago      Up 14 minutes       0.0.0.0:80->80/tcp, 0.0.0.0:443->443/tcp         global_gateway_1
f9569adf085b        andyshinn/dnsmasq        "dnsmasq -k -A /test…"   18 minutes ago      Up 14 minutes       53/tcp, 53/udp                                   global_dns_1
```

Here are the running containers explained

**fhm-test_phpfpm**
Our main PHP container with all the site files. It's ports are not directly open to the outside world the site is running on port 80 internally and php is listening on port 9000.

**redis:latest**
Local instance of redis same as production uses.

**phpmyadmin/phpmyadmin**
GUI for taking a look at the database and many other thing, I'm sure you are all familar already.
Access phpMyAdmin by navigating to [http://localhost:8092](http://localhost:8092).
- Username: `root`
- Password: `password`

**mysql:5.7**
You guessed it, our database instance. Also available to your host on port 3306

**jwilder/nginx-proxy**
This is our reverse proxy gateway to access the website over port 80 or 443, it binds to 127.0.0.1 or localhost. Also it has support for creating SSL certificates.

You can enable this by setting `VIRTUAL_HOST=fhm.test` on the PHP and NGINX containers via docker compose.

[jwilder/nginx-proxy](https://github.com/jwilder/nginx-proxy)

**schickling/mailcatcher**
Catches mail from the host. [Mailcatcher](https://mailcatcher.me/)

**andyshinn/dnsmasq**
Handles DNS resolution between the containers so they can all see each other by hostname.

# Database

**IMPORTANT: Mac users who have the M1 (or later) chipsets need to edit their global docker-compose.yml file. Please click here and make your changes before continuing here.**

We can now manage the databases via `airlocal snapshots` or `airlocal ss` for short. They are stored and manged in our `.airsnapshots` directory which is mounted into the running container at `/var/www/.snapshots` you don't particularly need this information to use the CLI commands but it's always nice to know what is going on so you can understand how to troubleshoot things down the road if need be. For our purposes though all we need to do is first export and `pull` the database into our snapshots directory (also you are free to drop any `.sql` file into your `.airsnapshots` directory and use it).

```bash
airlocal ss list
```
This will list all the available database files that have been pulled or can be imported into any `airlocal` site.

```bash
airlocal ss pull
```

You will use the abbreviated site name from the repo title, the environment, and choose if you need to force a new export or if a file less than 24 hours old is acceptable to save time because you won't wait for the export from the database.

The output should be similar to this...

```
test@ip-10-231-0-104:~/air-local-docker-sites/fhm-test$ airlocal ss pull
? Enter the site name (use the short 2-3 letter abbreviation from the Gitlab project) fhm
? Which environment to pull? develop
? Should we force the snapshot even if it is less than 24 hours old? false
Database export pipeline is running
Database export pipeline is running
Database export pipeline is running
Database export pipeline is running
Database export pipeline is running
Database export pipeline is running
Database export pipeline is running
Database export pipeline is running
Database export pipeline is running
Database export pipeline is running
Database export pipeline is running
Database export pipeline is running
Database export pipeline is running
Database export pipeline is running
Database export pipeline is running
Database export pipeline is running
Database export pipeline is running
Database export pipeline is running
Database export pipeline is running
Database export pipeline is running
Database export pipeline is running
Database export pipeline is running
Database export pipeline is running
Database export pipeline is running
Database export pipeline is running
Database export pipeline is running
Database export pipeline is running
Database export pipeline is running
Database export pipeline is running
Database export pipeline is running
Database export pipeline is running
Database export pipeline is running
Database export pipeline is success
Database file can be downloaded for 3 hours from:
https://storage.googleapis.com/air-cloud-db-storage/tmbi/fhm/export-local-develop-12-13-2019.sql?x-goog-signature=5764d3447025f024a61715841bec10ad55fb0fa82173333\=10800&x-goog-signedheaders=host
Automatically downloading snapshot to /home/test/.airsnapshots/fhm_develop.sql
  % Total    % Received % Xferd  Average Speed   Time    Time     Time  Current
                                 Dload  Upload   Total   Spent    Left  Speed
100  831M  100  831M    0     0   287M      0  0:00:02  0:00:02 --:--:--  287M
Snapshot export completed!
```

The signed URL good for 3 hours is output for reference but you won't need it because the file has already been downloaded into `.airsnapshots`. I removed most of the URL from the output above because it is extremely long and won't do anyone any good anyways after 3 hours from now.

Now lets `airlocal ss list` and you should see our filename listed. If so we are :ok_hand: :thumbsup:

Import the DB now.

```bash
airlocal ss load fhm_develop.sql
```
**NOTE:** If you encounter an error like below while trying to import your database, you will need to change the WP image file version in the Docker file for your site. The error should be something like this:

```bash
service "phpfpm" is not running container #1
Error running wp cli command wp db import .snapshots/[fhm_developer_sql]
```

(Can be for any site).

To address this, do the following:

1.) Open your Dockerfile for your site. For FHM, it should be here: `air-local-docker-sites > fhm-test > fhm-docker > Dockerfile`

2.) Edit the line `gcr.io/air-services/tmbi/docker/baseimage-wordpress:1.30.1` to version 1.32.0, like this:

`gcr.io/air-services/tmbi/docker/baseimage-wordpress:1.32.0`

**Note**: If you run into a "*MySQL server has gone away*" error when attempting to use `airlocal ss load`, try the following:
- Log into MySQL in the global MySQL container, and increase the `max_allowed_packet` setting -
    - `docker exec -it global-mysql-1 /bin/bash`
    - `mysql -u root -p` and then input "password" at the prompt
    - `SET GLOBAL max_allowed_packet=10073741824;`
    - Exit the shell and try `airlocal ss load` again (do NOT restart containers beforehand, or else the the setting will reset back to the default value!)
- If you use Docker Desktop, you can also try increasing the disk space allocated for Docker:
    - Open Docker Desktop and click on the settings gear icon
    - Go `Resources` -> `Disk Image Size`
    - Raise the slider to increase the amount of disk space allocated for your Docker files
    - Exit and try `airlocal ss load` again


After successfully loading the DB, replace the site URL in the database:

```
airlocal wp search-replace https://rf-test.familyhandyman.com http://fhm.test
```

Now the site should be resolving at http://fhm.test

As you make changes to any of the mounted folders or files they will reflect right away inside the container and on the site.

Now edit the docker-compose.yaml file and look for the 'ports' section. Create it if missing. Add the following:

```bash
ports:
  - "443:443"
```

You can now run `airlocal restart`, which will stop the container and restart, using port 443.

# Bumblebee 

Go back to air-local-docker-sites folder and clone Bumblebee from the repo at https://devops.45air.co/tmbi/themes/bumblebee

```bash
git clone git@devops.45air.co:tmbi/themes/bumblebee.git
```

Add Bumblebee to volumes in each site. For FHM you should edit this file: `air-local-docker-sites/fhm-test/docker-compose.yml `

Add the volume under "volumes" in the file, at the bottom:

```
- '~/air-local-docker-sites/bumblebee:/var/www/web/wp-content/themes/bumblebee'
```

Save and go back to your site directory (fhm-test) and do local restart

```bash
airlocal restart
```

This should run much faster now as well.

Change to the Bumblebee folder in air-local-docker-sites and run `npm install` and `gulp` to build CSS files from the cloned Bumblebee repo

```bash
npm install
npm run gulp
```

Edit the CSS files to test. (modify a background color), and run gulp again.

If you see the CSS changes, you can revert and rebuild. Now changes to the Bumblebee theme under air-local-docker-sites will show up in your site (fhm-test in this example).


# Elasticsearch

TMBI sites use [ElasticPress](https://wordpress.org/plugins/elasticpress/) for internal search and  filtering on category archive pages. To make these features work correctly in airlocal environments, you must setup a local Elasticsearch instance and set an environment variable to tell ElasticPress where to find it as documented below.

## Per machine setup steps

The first step in making ElasticPress work in airlocal is to setup a local Elasticsearch instance. To do so, follow the instructions in [this article](https://docs.bonsai.io/article/95-testing-elasticsearch-locally) for your platform. **This step only has to be completed once per machine.**

If you followed the instructions correctly, you should get some output like this when you run `curl localhost:9200` in a terminal:

```json
{
  "name": "camtl82094.local",
  "cluster_name": "elasticsearch_brew",
  "cluster_uuid": "xu_OYnePT22D5qN1YgY_6Q",
  "version": {
    "number": "7.10.2-SNAPSHOT",
    "build_flavor": "oss",
    "build_type": "tar",
    "build_hash": "unknown",
    "build_date": "2021-01-16T01:41:27.115673Z",
    "build_snapshot": true,
    "lucene_version": "8.7.0",
    "minimum_wire_compatibility_version": "6.8.0",
    "minimum_index_compatibility_version": "6.0.0-beta1"
  }
}
```

> **MacOS users:** If you don't get the correct output after running this command, start the elasticsearch service by running `brew services start elasticsearch` and try again. From that point, elasticsearch should automatically start when your machine boots.

## Per site setup steps

Once you've setup a local Elasticsearch instance, you need to tell ElasticPress where to find it, and finally index some content in Elasticsearch. **These steps must be completed once for each airlocal environment**.

### Set the `ELASTICSEARCH_HOST` environment variable

Edit the `docker-compose.yml` file for the site (located under `~/air-local-docker-sites/<slug>-test/` by default) and add a new `ELASTICSEARCH_HOST` variable with the value "host.docker.internal:9200" under services > phpfpm > environment.

The result should look something like this:

![elasticsearch-host-var.png](/elasticsearch-host-var.png)

After making this change, restart your airlocal environment by running `airlocal restart <slug>.test`.

### Index some content

Now that you have Elasticsearch setup and ElasticPress is pointing to the right place, you need to index some content in Elasticsearch. To do that, you can run the [wp elasticpress index](http://10up.github.io/ElasticPress/tutorial-wp-cli.html) command.

The easiest thing to do is index all content by running `airlocal wp elasticpress index --setup`, but with our volume of content this is not advisable. You may want to run a more specific command to just index a certain subset of posts. Some examples are included below. **Note that all examples are being executed inside of airlocal shell.**

#### Indexing the 500 most recent posts

```
wp elasticpress index --setup --include=$(wp post list --posts_per_page=500 --orderby=date --order=DESC --format=ids | sed 's/ /,/g')
```

#### Indexing 1000 posts in the "topics" category V2

```
wp elasticpress index --setup --include=$(wp post list --posts_per_page=1000 --categories-v2=topics --format=ids | sed 's/ /,/g')
```

> **IMPORTANT:** When you pass the `--setup` flag, ElasticPress will delete all previously indexed documents before indexing. You should only use this flag the first time you index content in an environment.
{.is-warning}
