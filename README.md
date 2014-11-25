REST PHP Tutorial
=================

This repository holds the screencast code, script and HATEOAS for the
[RESTful APIs in the Real World Episode 1](http://knpuniversity.com/screencast/rest)
course from KnpUniversity.

For more details, see the following blog posts:

* [What the REST?](http://knpuniversity.com/blog/what-the-rest)
* [REST Revisited](http://knpuniversity.com/blog/rest-revisited)

Installation
------------

1) Download/install Composer into this directory. See http://getcomposer.org

2) Download the vendor files by running:

```
php composer.phar install
```

3) Point your web server at this directory, or use the built-in PHP web
   server, which is nice and friendly (but requires PHP 5.4+)

```
cd web
php -S localhost:8000
````

4) Make sure a few directories are writeable:

```
mkdir logs
mkdir data
chmod 777 logs data
```

5) Load up the app in your browser!

    http://localhost:8000

Collaboration
-------------

As we start writing the content for this tutorial, we invite you to read
through it, try things out, and offer improvements, either as issues on this
repository or as pull requests. REST is hard, so the more smart minds we
can have on it, the better it will be for everyone.

Cheers!
