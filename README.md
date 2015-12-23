# IntegratedExportBundle #
This bundle export data from collection 'content' to '.xml', '.csv' and '.xlsx' formats.

## Requirements ##
* phpoffice/phpexcel

## Documentation ##
* [Integrated for Developers](http://integratedfordevelopers.com/ "Integrated for Developers")

## Installation ##
This bundle can be installed following these steps:

### Install using composer ###

    $ php composer.phar require integrated/export-bundle:~1.0

### Enable the bundle ###

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new Integrated\Bundle\ExportBundle\IntegratedExportBundle()
            // ...
        );
    }

### Import the routing ###

    # app/config/routing.yml
    integrated_export:
        resource: @IntegratedExportBundle/Resources/config/routing.xml

## License ##
This bundle is under the MIT license. See the complete license in the bundle:

    LICENSE

## Contributing ##
Pull requests are welcome. Please see our [CONTRIBUTING guide](http://integratedfordevelopers.com/contributing "CONTRIBUTING guide").

## About ##
This bundle is part of the Integrated project. You can read more about this project on the
[Integrated for Developers](http://integratedfordevelopers.com/ "Integrated for Developers") website.