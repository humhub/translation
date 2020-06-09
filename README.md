HumHub - Translation Module
===========================

This module is used by the HumHub community to maintain the translations of the HumHub core and marketplace modules.

**This module is not intended for use in HumHub instances!**

## Installation:

- Install 
  - Download & Put files into /protected/modules/translation/
  - Or: git submodule add https://github.com/humhub/humhub-modules-translation.git protected/modules/translation

- Enable it under Admin -> Modules
- Open via Administration -> Manage -> Translation

## Extract module translations

In order to initialize the message files of an installed custom module with id `my-module`, execute the following command within the
`protected` directory of your installation:

```
php yii message/extract-module my-module
```

## Update archive

The following command is used to build the message archive. The message archive includes all

```
php yii translation/build-archive
```

## Further reading

- [Yii2 Internationalization](https://www.yiiframework.com/doc/guide/2.0/en/tutorial-i18n#internationalization)