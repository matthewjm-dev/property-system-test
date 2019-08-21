In Phase Solutions skeleton v1.00

The skeleton package is the base upon which apps and core versions are built. 

Skeleton currently comes pre-packed with 2 apps:
- ipscms, this is an administration sytem for site building (seperate repo)
- site, a front end site to be accessed by users, ready for building

To init a skeleton project for development:
- Fork the skeleton respository to a new project
- Pull down the new project repository to the desired location
- Copy environment.ini.example to environment.ini and configure for the current system
- Configure package.json for the required packages, can be edited later
- Run npm install to install all dev packages
- Create / configure config-*.ini files in /apps for apps
- Configure composer.json
- Run composer update
- Configure Grunfile.js for apps
- Configure public/robots.txt
- Configure .gitignore
- Run grunt production to compile all assets for live
- Commit all changes and push to project
- DONE

To install project for live:
- pull the project repository to the desired location
- copy environment.ini.example to environment.ini and configure for the current system
- Run composer install
- DONE

Node Modules & Composer
- These are two seperate package management systems used for different reasons
- Node modules are development environment only packages which are used for grunt, or the contents of which are copied out into
the public directory to be committed for live. Files can also be accessed for purposes such as bootstrap where the necessary files
are imported to be compiled so ar enot needed on live. Node modules is a very large directory which should never be installed on live.
- Composer installs its packed to either the "vendor" directory or a custom specified location (for example package apps or core), these
files are required on the live environment so composer must be run on live. Packages inside the vendor folder are autoloaded into the 
project so do not need to be manually included.

All assets are compiled on the development environment using grunt production, production assets are committed to the project
so that they do not need to be compiled on the live environment, reducing the chances of soemthing going wrong. Therefore to update
a live site, it needs to be edited on development, compiled, pushed, then pulled down to live. Composer install is still required on
the live system as the packages it installed are required (In Phase Solutions core being the main item).

Support: support@inphasesolutions.com