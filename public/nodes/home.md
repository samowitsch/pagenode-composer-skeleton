title: Home
date: 2021.12.18 22:31
keywords: My custom keywords
description: My custom description

---

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
$ composer install
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
$ composer install
$ ddev start
$ ddev launch
```

## public folder structure

This is the basic folder structure of ```/public```.

```shell
.
├── assets
│   ├── css
│   │   └── styles.css
│   └── js
│       └── main.js
├── img
│   └── dummy-image.jpg
├── imgp.php
├── index.php
├── nodes               // in the nodes folder are all markdown files for landingpages
│   ├── 404.md          // 404 error page
│   └── home.md         // /home or / page
├── process.php
└── templates           // here are the templates
    ├── node.html.php
    └── partials
        ├── resources.footer.html.php
        └── resources.header.html.php
```

## Deployment

A basic ```andres-montanez/magallanes``` deployment config is included. Modify it to your needs for further documentation [see here](https://www.magephp.com/).

Some example commands:

* ```./bin/mage deploy production```
* ```./bin/mage releases:list production```

> **Note:** maybe a ssh connection with use of ssh key is needed. depending to your hoster it is possible to store a public key.

Example ssh ~/config entry:

```shell
Host CONFIGURATION-NAME
    HostName YOUR.HOST.NAME
    User YOUR.USER.NAME
    IdentityFile /home/[YOUR.LOCAL.USERNAME]/.ssh/id_rsa
```

## Build toolchain

A simple example ```Makefile``` is in this repo. No fancy bleeding edge stuff (e.g. webpack). Only simple basic concatenation and minify.

## Email process.php

Included is a simple process.php to handle simple contact forms using xhr for example with e.g. [jQuery Form Plugin](https://github.com/jquery-form/form).

## Images

### using original size

```<img src="/img/dummy-image.jpg" loading="lazy" title="Dummy image original size" />```

<img src="/img/dummy-image.jpg" loading="lazy" title="Dummy image original size" />

### using included CIMAGE to crop images

This skeleton includes [Image conversion on the fly using PHP](https://github.com/mosbth/cimage). For documentation [see here](https://cimage.se/). Or if you dont like it replace it with your favourite solution e.g. [adaptive images](http://adaptive-images.com/).

```<img src="/image/400/300/dummy-image.jpg" loading="lazy" title="Dummy image cropped size" />```

<img src="/image/400/300/dummy-image.jpg" loading="lazy" title="Dummy image cropped size" />

