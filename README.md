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
* .sass is not supported!


Getting Started
---------------

1) Run `composer require cm/neos-thememodule`
2) Run `./flow doctrine:migrate`
3) You need a site package in `Packages/Sites` which has your scss code. Currently no other locations are working
4) Have a look in `CM.Neos.ThemeModule\Configuration\Settings.yaml` there are some defaults defined which you can easily override and/or extend to your needs


Defining a scss variable
------------------------

You can define the scss variables which should be available on the backend module.
To define define new variables add them to your `Settings.yaml`. Don't edit the `Settings.yaml` in this package as this
break the possibility to update the package, as it would for any other package.

Basic schema, for more details see `Configuration/Settings.yaml`:
```
CM:
  Neos:
    ThemeModule:
      scss:
        presetVariables:
          color: # group
            label: 'Your Color Settings'
            type:
              color: # Possible types are: color, font, textfield
                greyBase: # Name of your scss variable, can be any name if unique
                  value: '#000000' # default value
                  scssVariableName: '$grey-base' # variable name in your scss file                  
                  label: 'Grey Base' # The label which should be shown in the backend.
              font: # Possible types are: color, font, textfield
                fontFamilyMonospace:
                  value: 'Consolas'
                  fontFallbackValue: 'Monaco, Consolas, "Courier New", monospace' # Fallback fonts
                  scssVariableName: '$font-family-monospace'      
                  label: 'Font Family Monospace'
                  options: # Google Webfonts get added automatically
                    'Arial': 'Arial' # Additional font (e.g. not included in Google Webfonts)
                    'Consolas': 'Consolas' # Additional font (e.g. not included in Google Webfonts)
              textfield: # Possible types are: color, font, textfield
                fontSize: # Name of your scss variable, can be any name if unique
                  value: '16px' # default value
                  scssVariableName: '$font-size' # variable name in your scss file                  
                  label: 'Font Size' # The label which should be shown in the backend.
                fontWidth: # Name of your scss variable, can be any name if unique
                  value: '16px' # default value
                  scssVariableName: '$font-width' # variable name in your scss file                  
                  label: 'Font Width' # The label which should be shown in the backend.                                                         
```

**Defining color:**
To add an input form with an additional color picker add this and change it to your needs

```
type:
  color: # Possible types are: color, font, textfield
    greyBase: # Name of your scss variable, can be any name if unique
      value: '#000000' # default value
      scssVariableName: '$grey-base' # variable name in your scss file                  
      label: 'Grey Base' # The label which should be shown in the backend.
```


**Defining fonts:**
To add a selectbox with fonts from Google Webfonts and other additional fonts add this and change it to your needs

```
type:
  font: # Possible types are: color, font, textfield
    fontFamilyMonospace:
      value: 'Consolas'
      fontFallbackValue: 'Monaco, Consolas, "Courier New", monospace' # Fallback fonts
      scssVariableName: '$font-family-monospace'      
      label: 'Font Family Monospace'
      options: # Google Webfonts get added automatically
        'Arial': 'Arial' # Additional font (e.g. not included in Google Webfonts)
        'Consolas': 'Consolas' # Additional font (e.g. not included in Google Webfonts)
```

**Defining textbox:**
To add a regular input form add this and change it to your needs

```
type:
  textfield: # Possible types are: color, font, textfield
    fontSize: # Name of your scss variable, can be any name if unique
      value: '16px' # default value
      scssVariableName: '$font-size' # variable name in your scss file                  
      label: 'Font Size' # The label which should be shown in the backend.
```



License
-------

Licensed under GPLv3+