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
Generally I run a local dev environment similar to:

Apache HTTPD 2.2.x
PHP 5.3.3
Mysql 5.x

The  PHP  version  *must*  be  5.3.3  because  doctrine  has
problems  with 5.3.2.  Otherwise you  can probably  use just
about anything you want for a server and a database.

You also really need command line access to your web server.
I'm sure  you could  pull it  off to  work with  and install
AweCMS without  shell access but  you'll need CLI  access to
install the  database using the doctrine  command line tool,
which requires access to the PHP command line tool.

AweCMS is meant to  be targeted initially towards developers
but organizing the directory treet  in a manner suitable for
normal shared hosts  is a goal of the project  to allow wide
distribution. Anyway, to start, you'll need or want SSH.

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

And that's it. 

There is  also a windows version  called `install.bat`. This
version  is however,  significantly  less  dynamic than  its
Linux style cousin and you'll probably need to edit this one
by hand to add your MySQL root password.

If neither of  these work then you just need  to be aware of
one or two things. AweCMS  needs a database and some initial
tables and rows, mostly for two reasons...
    A) so that  you can log in  to the admin area and  
    B) so the frontend CMS has a default home page

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

USAGE - 03.01
================================================================
once your system is installed you should be able to go to

http://yoursite.com/

(or whatever domain you chose)

and 

http://yoursite.com/admin

and hopefully it "just works"

There is a default admin user with the following credentials

username: root
password: admin

you can  use this to  log in to  the AweCMS admin  and start
poking around.

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
may wish  to manually  write some  static getter  and setter
methods on each of your models. Feel free to do so.

The automatic form building has to do with two main things

1) the annotations that you add to the models and
2) placing a very small controller file in the admin module

look at the current system to see how this is done.

long story short,  all you need to do is  write a model with
those magical @Awe annotations on  each column that you want
to become an  automagic form field. then just  write a small
controller class  that extends the AutoAdmin  class and then
give it a few initial values to tell it the bare bones basic
stuff it needs  to know to give you  standard CRUD functions
meaning:

1) the name and namespace of the entity
2) human readable name and plural version (for the UI)
3) the controller name (for the redirects e.g. after saving)

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

IMPORTANT 

Features
----------------------------------------------------------------
File upload field type
Asset manager for uploads
FCKEditor/TinyMCE fields
List pagination
List datagrid

Architecture Features
----------------------------------------------------------------
CMS database route + caching
Configuration settings (database? file? json? serialized php?)
Layout selection config setting

Architecture Modifications
----------------------------------------------------------------
Use annotations instead of column names for autocrud save
Migrate to partials instead of placeholders

NICE

Features
----------------------------------------------------------------
Create only fields (non editable after creation)
List labels (specify alternate labels for the datagrid columns)
Sub-entity datagrid
Sub-entity pagination
Inline edit vs non-inline edit

Architecture Modifications
----------------------------------------------------------------
Third-pary/vendory module directory tree structure
Per module entities (use multiple Entities folders)
Per module routes in (in module bootstrap)
Widgets per layout, not just per CMS page

Architecture Features
----------------------------------------------------------------
full page caching by URL
(WidgetLayout? = save: choice of Layout + Choice of widgets.
    to allow same layout but diff widgets per modules)
