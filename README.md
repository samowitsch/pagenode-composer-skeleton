# pagenode-composer-skeleton

Welcome to ```pagenode-composer-skeleton``` !

This skeleton uses [Pagenode](https://pagenode.org/) as a Flatfile PHP Content Management Library in a composer stylish way.

The original Pagenode sources are splitted and the used ```erusev/parsedown``` code is required with composer. Feel free to modify this skeleton to your needs.

## Requirements

Needs installed [composer](https://getcomposer.org/), [local DDEV environment](https://ddev.readthedocs.io/en/latest/), [docker and docker-compose](https://docs.docker.com/get-docker/).

## Installation

```
$ git clone git@github.com:samowitsch/pagenode-composer-skeleton.git
$ cd pagenode-composer-skeleton
$ ddev start
$ ddev launch
```
## Installation with custom project name

If you wants to use a custom project name instead then do the following:

```
$ git clone git@github.com:samowitsch/pagenode-composer-skeleton.git YOUR-PROJECT-NAME
```

Change the name in ```.ddev/config.yaml``` to YOUR-PROJECT-NAME.

```
$ cd YOUR-PROJECT-NAME
$ ddev start
$ ddev launch
```

## Deployment

A basic ```andres-montanez/magallanes``` deployment config is included. Modify it to your needs for further documentation [see here](https://www.magephp.com/).

Some example commands:

* ```./bin/mage deploy production```
* ```./bin/mage releases:list production```

## Build toolchain

A simple example ```Makefile``` is in this repo. No fancy bleeding edge stuff (e.g. webpack). Only simple basic concatenation and minify.

## Email process.php

Included is a simple process.php to handle simple contact forms using xhr for example with e.g. [jQuery Form Plugin](https://github.com/jquery-form/form).

## Images

### using original size

```<img src="/assets/images/dummy-image.jpg" loading="lazy" title="Dummy image original size" />```

### using included CIMAGE to crop images

This skeleton includes [Image conversion on the fly using PHP](https://github.com/mosbth/cimage). For documentation [see here](https://cimage.se/). Or if you dont like it replace it with your favourite solution e.g. [adaptive images](http://adaptive-images.com/).

```<img src="/image/400/300/dummy-image.jpg" loading="lazy" title="Dummy image cropped size" />```

