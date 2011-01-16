Hello and welcome to AweCMS
    a Zend Framework 1.11 and Doctrine 2.0 integration.

TABLE OF CONTENTS
================================================================
00.01 - Overview
01.01 - Requirements
02.01 - Installation
03.01 - Usage
04.01 - Architecture
05.01 - TODO

OVERVIEW - 00.01
================================================================
AweCMS gives you automatic admin  forms for your models. All
you have to do is add  a little extra information along with
your models to describe what kind of form fields you want to
use  for  each  column.  Full  support  for  Zend  forms  is
available including the specification of initial options and
validators.

AweCMS already  has some  basic example models  that provide
some generic CMS and blogging  functionality as well as user
login and membership signup models.

There is a layout and theming  system so that you can update
the system to  look like anything you  wish. Installation of
themes is as simple as dragging and dropping your theme into
the themes folder.

REQUIREMENTS - 01.01
================================================================

Currently, though it should work on any OS, AweCMS is only
supported on the following configuration

Linux (Ubuntu 10.10 or CentOS 5.3 should be fine)
Apache HTTPD 2.2
PHP 5.3.3
MySQL 5.0

The PHP version *must* be 5.3.3 because 5.3.2 has problems

You also really need command line access to your web server.
I'm sure  you could  pull it  off to  work with  and install
AweCMS without  shell access but  you'll need CLI  access to
install the  database using the doctrine  command line tool,
which requires access to the PHP command line tool.

INSTALLATION - 02.01
================================================================
AweCMS is pretty  much self contained and  doesn't require a
lot of extra work to get running.

There is an installation file called `install.sh` which is a
shell script written for BASH. Just make sure that this file
is executable  and then run  it as  you would run  any other
normal bash script from the command line. An example of this
would look something like:

chmod 755 install.sh
./install.sh

And that's it.  If this doesn't work for some  reason, or if
you're  on windows  for example,  then you  just need  to be
aware of one or two things. AweCMS needs a database and some
initial tables, mostly for two reasons... A) so that you can
log in to the admin area  and B) so the frontend CMS doesn't
go bonkers because it can't find a default home page.

Other than  having the  database, AweCMS  wants to  have two
folders writable by the web server:

var 
application/doctrine/Proxies

both of  these are used  by the doctrine subsystem,  the var
folder is where the doctrine  query log goes and the Proxies
folder is better explained  by the doctrine reference manual
but suffice it to say that these folders must exist and your
web server must be able to write to both of them.

if the  install script doesn't  work for you then  your best
bet is to simply read it and try to run each of the commands
yourself.

once your system is installed you should be able to go to

http://yoursite.com/

and 

http://yoursite.com/admin

and hopefully it "just works"

USAGE - 03.01
================================================================
There is a default admin user with the following credentials

username: root
password: admin

you can use this to log in to the AweCMS admin and start poking
around.

The core of the system hinges on writing Doctrine 2 models
with annotations. YAML and XML are not supported right now,
only writing regular PHP classes with standard DocBlock
annotations.

All the doctrine models live in

application/doctrine/Entities

and they all extend

application/doctrine/Entities/AbstractEntity.php

the main reason  for this extension is  to provide automagic
getter and setter methods to make initial development life a
little easier.  In the  future, for performance  reasons you
may wish to write manual or static getter and setter methods
on each of your models. Feel free to do so.

The automatic form building has to do with two main things

1) the annotations that you add to the models and
2) placing a very small controller file in the admin area

look at the current system to see how this is done.

long story short, all you need to do is write a model with 
those magical @Awe annotations on each column that you want to 
become an automagic form field. then just write a small controller
class that extends the AutoAdmin class and then give it a few
initial values to tell it the bare bones basic stuff it needs
to know to give you standard CRUD functions meaning:

1) the name and namespace of the entity
2) human readable name and plural version (for the UI)
3) the controller name (for the redirects, e.g. after saving)

Alternatively, as  usual, if you don't  want the automagical
stuff and just wanna make use of the good old Zend Framework
and Doctrine  2 subsystem, you  can simply extend  the Admin
class as opposed to the AutoAdmin.

You will, of course have  to create your own views, helpers,
forms templates, and controller  actions just like any other
piece of a regular Zend Framework MVC app.

Architecture - 04.01
================================================================
For now all I can really say is: read the code. 

However, I can give a brief overview of a few key
components and folders worth checking out

library/Awe/Annotations
    this  is just  a file  with a  list of  the annotations.
    if  you  want to  add  another  annotation to  add  some
    functionality to the system, great, just put the name in
    here and you can use it.

library/Awe/Controller
    by far the most in  depth and possibly confusing portion
    this is  really sort of the  AweCMS core right now  in a
    big  way.  You'll  have  to  wade  through  most  of  it
    yourself but long story  short, mainly these files build
    on  each  other  to provide  switchable  layouts,  login
    protection  and finally  on top  of all  that, the  auto
    admin generator.
    
library/Awe/Form
    also  part of  the  autoadmin generator,  this is  quite
    possibly  the defacto  core of  AweCMS right  now as  it
    contains  perhaps the  single  most important  component
    which is,  well the automatic form  generator. The basic
    rule is that form elements  don't get created unless the
    model  has an  @Awe annotation  on that  field. If  that
    annotation does exist then, well,  read the code in here
    to find out what each  annotation does. Adding new field
    types is as  simple as adding an annotation  to the file
    mentioned above  and then adding  some logic in  here to
    catch it.

application/doctrine/Entities
    this is where models go

application/themes
    this is  where the layouts  and PHTML templates  go, the
    core components  of the  theme. things that  are dynamic
    and database dependent.

media/themes
    the media folder is eventually intended for file uploads
    but for now it just holds the frontend theme assets like
    CSS and js files. broken in to admin and frontend types.
    if you're  looking to  make a theme  for your  site, you
    probably are wanting to make a frontend theme.

bin
    this is where the doctrine command line tool lives

application/resources
    this has the init code for layouts, routers and doctrine

TODO - 05.01
================================================================
File uploads
Pagination for entity lists
Inline paginated foreign entity lists
CMS router and route caching
Inline edit vs non-inline edit
