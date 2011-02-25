# Making Apache2 handle **CoffeeScript** files automatically

During my forays into CoffeeScript I decided I wanted to be able to include .coffee
files directly into my source without using the JS interpreter.

## step 1
To achieve this, I used the Apache module [mod_ext_filter](http://httpd.apache.org/docs/current/mod/mod_ext_filter.html) so firstly you need to
enable this module:
> sudo a2enmod ext_filter && sudo service apache2 restart

## step 2
Now that the module is running, we add a filter to an Apache config file. All config
files are equal so use whichever you want (I used */etc/apache2/sites-enabled/000-default*)

We use the ExtFilterDefine op, and it basically looks like this:
> ExtFilterDefine coffee mode=output intype=text/coffee outtype=text/javascript cmd="/usr/local/bin/coffee -sbp"

That bit goes directly into the upper level (for me, <VirtualHost>::@text). Reading that should hopefully be
self-explanatory - we take in a text/coffee filetype and output a text/javascript MIME, and pass it through
that command in the middle. The "s" is "read from stdIO", "b" is "bare mode", and "p" is "print the source out at the end"

The "bare mode" usage is something I use because most of my coffee files contain a single class and this way makes the classes 
get exposed outside of the file they are defined in. *The other way of achieving this is adding "this.SomeClass = SomeClass" at the end of the file.*

## step 3
We aren't quite there. Next, add a filetype handler to a .htaccess (or an apache config, it's up to you):
> AddType text/coffee .coffee

## step 4
Restart your web server!
> sudo service apache2 restart

Hopefully that should all work for you.
