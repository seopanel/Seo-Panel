# Seo Panel - World's first SEO Control Panel for Multiple Websites

An Award Winning open source seo control panel for managing search engine optimization of your websites

## Requirements

   1. PHP, MYSQL, Web Server(e.g APACHE)

   2. CURL enabled with PHP, Refer following link to install curl with php if it is not installed.

        http://php.net/manual/en/curl.setup.php


## Seo Panel Installation: Simple 5 minute installation

Simply use Softaculous / Installatron / Webuzo / AMPPS to install SEO Panel or Follow the below steps

1. Download and Unzip the package.

2. Upload all the files contained in this folder (retaining the directory structure) to a web accessible directory on your server or hosting account.

3. Change the permissions on config/sp-config.php to be writable by all (666 or -rw-rw-rw- within your FTP Client)

4. Change the permissions on the tmp directory to be writable by all (777 or -rwxrwxrwx within your FTP Client)

5. Using your web browser visit the location you placed Seo Panel with the addition of install/index.php or pointing directly to install/

    Eg: http://www.yourdomain.com/seopanel/install/

6. Follow the steps and fill out all the requested information.

7. Change the permissions on config/sp-config.php to be writable only by yourself (644 or -rw-r--r-- within your FTP Client)

8. Please use the following login details to access Admin Interface.

#### Admin Section:

Username: spadmin

Password: spadmin

9. Go to Admin Panel => System Settings => MOZ Settings
    
   Add moz api key using the link shown in the page

#### Note:

a. Please change the password of administrator by visiting Profile link on right top of the seo panel to prevent from security threats.

b. Remove install directory of seo panel


## Docker Installation

Follow below steps to install seo panel as docker container.

- Copy sample_env file to .env file and edit required details in it

	`cp sample_env .env`

- Execute following command to start docker containers 

	`docker compose up`

- Take following link in browser and follow the installation instructions

	http://localhost/


## Online Seo Panel Resources:


a) Seo Panel Help Guide: http://docs.seopanel.in/

b) Seo Panel Forum: http://forum.seopanel.in/

b) Seo Panel Support: https://www.seopanel.org/support/

e) Seo Panel Contact: https://www.seopanel.org/contact/


## The major features of Seo Panel:

a) Automatic Directory Submission Tool

b) Keyword Position Checker

c) Site Auditor

d) Moz Rank Checker

e) Backlinks Checker

f) Search Engine Saturation Checker

g) Seo Panel Plugins

h) Meta Tag Generator

