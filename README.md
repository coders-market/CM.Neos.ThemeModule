# Backend Site/Theme Module for Neos

This packages allows you to customize your theme package in a backend module.
 
	
## Features

* Full customizable, predefine any scss variables
* Support for bootstrap, foundation and many more  
* Change colors and fonts in the backend of Neos
* Add custom additional scss/css code
* Render your SCSS files
* No dependencies to sass compiler libraries
* Additonal Viewhelper
* Support for google webfonts


## Requirements / Limitations

* This package is tested with Neos 3.0 and 3.1
* .sass is not supported!


## Getting Started

1) Run `composer require cm/neos-thememodule`
2) Run `./flow doctrine:migrate`
3) You need a site package in `Packages/Sites` which has your scss code. Currently no other locations are working
4) Have a look in `CM.Neos.ThemeModule\Configuration\Settings.yaml` there are some defaults defined which you can easily override and/or extend to your needs


### Adapt Settings.yaml to your needs

In the Settings.yaml your have to declare the location of your scss code and your compiled css.

 
```
CM:
  Neos:
    ThemeModule:
      scss:
        # folder of your scss code
        importPaths: 'resource://Vendor.Site.Package/Private/Styles/'
        # Contains all scss rules and @imports to other files
        mainScssFile: 'Main.scss'
        # Expanded, Nested (default), Compressed, Compact, Crunched
        formatter: 'Leafo\ScssPhp\Formatter\Nested'
        # Output path has to be in Package Public folder
        outputPath: 'resource://Vendor.Site.Package/Public/Styles/'
        outputFilename: 'Main.css'
``` 

**IMPORTANT:** All your scss code has to be below specified `importPaths` using relative paths in a scss `@import` to a 
folder outside the `importPaths` will fail.


### Defining a scss variable

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

**Attention:**
In order to be able to override your defined scss variables in your scss file, the variable must have the value `!default`


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

### Inspector Validator for color values: hex, rgb, rgba

Add to your node property the validator like this:

```
'Neos.NodeTypes:Column':
  properties:
    backgroundColor:
      type: string
      validation:
        '../../CM.Neos.ThemeModule/JavaScript/Inspector/Validators/ColorValidator': []
```

> Currently the there's a bug which makes it neccessary to add `../../` this issue is already reported. 
See [github issue #1562](https://github.com/neos/neos-development-collection/issues/1562)


**Valid values are:**
* Hex: #fff or #ffffff
* rgb: rgb(255,255,255)
* rgba: rgba(255,255,255,0.3)


## Contributing

I highly encourage everyone to provide PRs for missing functionality, improvements or bugfixes. 


## License

Licensed under GPLv3+