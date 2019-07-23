In Phase Solutions skeleton v1.00

The skeleton package is the base upon which apps and core versions are built. 

Skeleton currently comes pre-packed with 2 apps:
- admin, this is an administration sytem for site building
- site, a front end site to be accessed by users

To fully install skeleton project for development:
- Fork the skeleton respository to a new project
- Pull down the new project repository to the desired location
- Copy environment.ini.example to environment.ini and configure for the current system
- Configure package.json for the required packages, can be edited later
- Run npm install to install all dev packages
- Configure composer.json 
- Run composer install
- Configure Grunfile.js
- Run grunt production to compile all assets for live
- DONE

To fully install project for live:
- pull the project repository to the desired location
- copy environment.ini.example to environment.ini and configure for the current system
- Run composer install
- DONE

All assets are compiled on the development environment using grunt production, production assets are committed to the project
so that they do not need to be compiled on the live environment, reducing the chances of soemthing going wrong. Therefore to update
a live site, it needs to be edited on development, compiled, pushed, then pulled down to live. Composer install is still required on
the live system as the packages it installed are required (In Phase Solutions core being the main item).

Support: support@inphasesolutions.com