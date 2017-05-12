Whywebs.com by Mutasem Elayyoub the fastest way to download and sync your Wordpress using phing with only one file build.xml

How to install phing:

Installing Phing
PEAR

The preferred method to install Phing is through PEAR and the Phing PEAR channel. You can install Phing by adding the pear.phing.info channel to your PEAR environment and then installing Phing using the phing channel alias and phing package name:

$ pear channel-discover pear.phing.info
$ pear install [--alldeps] phing/phing
Note: if you would like to install an unstable version of Phing (alpha or beta), install as follows:

$ pear install phing/phing-beta
$ pear install phing/phing-alpha
For more info, refer to the ​PEAR channel.

Composer

Install Phing by adding a dependency to ​phing/phing to the require-dev or require section of your project's composer.json configuration file, and running 'composer install':

{
"require-dev": {
"phing/phing": "2.*"
}
}

How to install Whywebs WordPress

$ phing


The latest wordpress version with some important plugins will be in your public folder

Done!!