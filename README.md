Backend Site/Theme Module for Neos
==================================

This packages allows you to customize your theme package in a backend module.
 
	
Features
--------

* Full customizable, predefine any scss variables
* Support for bootstrap, foundation and many more  
* Change colors and fonts in the backend of Neos
* Add custom additional scss/css code
* Render your SCSS files
* No dependencies to sass compiler libraries


Requirements / Limitations
--------------------------

* This package is tested with Neos 3.0


Getting Started
---------------

1) Run `composer require cm/neos-thememodule`
2) Run `./flow doctrine:migrate`
3) You need a site package in `Packages/Sites` which has your scss code. Currently no other locations are working
4) Have a look in `CM.Neos.ThemeModule\Configuration\Settings.yaml` there are some defaults defined which you can easily override and/or extend to your needs


License
-------

Licensed under GPLv3+