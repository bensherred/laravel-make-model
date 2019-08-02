# Laravel Make Model

This package extends the Laravel make:controller and make:model commands to
create policies, requests and views all within 1 command. The arguments you
specify will also affect the controller that gets generated. Installing
this package also creates a make:view command.

* [Installation](#installation)
* [Usage](#usage)
* [Credits](#credits)
* [License](#license)

## Installation
You can install this package via composer:

``` bash
composer require bensherred/laravel-make-model
```

The service provider will automatically register or you may manually add the
service provider in your ```config/app.php``` file:

``` php
'providers' => [
    // ...
    BenSherred\MakeModel\MakeModelServiceProvider::class,
];
```

## Usage
There are 2 parts to this package, the extension of the Laravel make:model
command and the Laravel make:controller command.

### make:model
If you run the following command, you will notice 1 extra option and that
the *--all* argument has been updated.

First off, running the following command will not only generate a migration,
factory and resource controller but it will also now create a policy, request
classes and views.

``` bash
php artisan make:model Post --all
```

The additional classes/files that will get created for this example are:

* app/Http/Requests/Post/StoreRequest.php
* app/Http/Requests/Post/UpdateRequest.php
* app/Policies/PostPolicy.php
* resources/views/post/create.blade.php
* resources/views/post/edit.blade.php
* resources/views/post/index.blade.php
* resources/views/post/show.blade.php

The controller that gets generated will automatically return the views,
include the requests and policy.

Another option that is available is to just create the policy for the model.
This can be done by running the following command:

``` bash
php artisan make:model Post --policy
```

### make:controller
The extension of the make:controller is very similar to the make:model
command, but has a few additional options.

##### Creating a policy
To create a policy along with the controller you can add the --policy option.
This will only get added to the controller automatically if you specify a
model for the controller.

``` bash
php artisan make:controller PostController --model=Post --polcy
``` 

Running the above command will create a resource controller and use the Post
model, along with creating the PostPolicy and including the authorizeResource
option in the controller.

**If you create a resource controller the policy will be create, but the
authorizeResource option will not be automatically added to the controller.**

##### Create requests
If you would like to create custom request classes for your controller, you
can add the --requests option to the command. This will create a StoreRequest
and UpdateRequest class under app/Http/Requests/{Model}/.

This option will work for both model controllers and resource controllers. If
you create a model controller by specifying the --model= option, it will use
the model name to create the requests folder. However, if you create a resource
controller, it will remove the world Controller from the controller name and
use that instead.

``` bash
php artisan make:controller PostController --resource --requests
```

Therefore, running the following command will create a StoreRequest and
UpdateRequest class under app/Http/Requests/Post.

##### Create views
Another option which has been added to the make:controller command is the
ability to automatically create views. You can this by specifying the --views
option. If you were creating a PostController and added this option, the
following views would be created under a folder called post:

* create.blade.php
* edit.blade.php
* index.blade.php
* show.blade.php

Like the --requests option, if you specify a model it will use the model name
for the folder. However, if you create a resource controller it will work
out the name of the folder based on the controller name.

If you create a model or resource controller, the views will also be
automatically added to the controller. If a model controller is generated, it
will also pass the model through to the controller.

**Please note if you specify the --api option, this option will be ignored**

##### Using multiple options
If you would like to use multiple options, you can daisy chain them on.

For example, running the command below would create a model controller along
with then policy and the 2 request classes:

``` bash
php artisan make:controller PostController --model=Post --policy --requests
``` 

Alternatively, you can use all 3 together:

``` bash
php artisan make:controller PostController --model=Post --policy --requests --views
``` 

### make:view
This package also creates a make:view command available.

Running the following command will create an index.blade.php file under a
blog folder in views.

``` bash
php artisan make:view blog/index
```

## Credits
- [Ben Sherred](https://github.com/bensherred)
- [ShawnCZek](https://github.com/shawnczek)

## License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
