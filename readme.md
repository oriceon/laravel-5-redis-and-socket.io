#Install clean Laravel 5

`composer create-project laravel/laravel --prefer-dist /path/to/your/laravel`

remove compiled.php from vendor folder then update dependencies

`composer update`

`php artisan optimize`


#Create vhost for chat.dev

`sudo nano /etc/nginx/conf.d/chat.conf`

and add config

```
server {

    listen  80;
    server_name chat.dev www.chat.dev;
    set $root_path '/vagrant/shared/www/chat/public';
    root $root_path;
    
    sendfile off;

    index index.php index.html index.htm;

    try_files $uri $uri/ @rewrite;

    location @rewrite {
        rewrite ^/(.*)$ /index.php?_url=/$1;
    }

    location ~ \.php {
        fastcgi_pass   127.0.0.1:9000;
        fastcgi_index /index.php;

        include /etc/nginx/fastcgi_params;

        fastcgi_split_path_info       ^(.+\.php)(/.+)$;
        fastcgi_param PATH_INFO       $fastcgi_path_info;
        fastcgi_param PATH_TRANSLATED $document_root$fastcgi_path_info;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~* ^/(css|img|js|flv|swf|download)/(.+)$ {
        root $root_path;
        expires 0;
        break;
    }

    location ~ /\.ht {
        deny all;
    }

}
```

then reload nginx

`sudo service nginx reload`

and test if `http://chat.dev/` is workin` ok!


##Corrupted or non-updating files VirtualBox bug

If you're using the VirtualBox provider, then VirtualBox shared folders are the default synced folder type. These synced folders use the VirtualBox shared folder system to sync file changes from the guest to the host and vice versa.

There is a VirtualBox bug related to sendfile which can result in corrupted or non-updating files. You should deactivate sendfile in any web servers config files you may be running.

**In Nginx:**

`sendfile off;`

**In Apache:**

`EnableSendfile Off`

See vagrant docs: http://docs.vagrantup.com/v2/synced-folders/virtualbox.html


#Create users

edit .env file from your laravel folder and fill database credentials

run `php artisan migrate:install` and `php artisan migrate` to create and load default users tables

then go to http://chat.dev/auth/register and create two users as you want.


#Install Redis Server

`sudo apt-get install redis-server`

#Install NodeJs

`curl -sL https://deb.nodesource.com/setup_0.12 | sudo bash -`

then install it with `sudo apt-get install -y nodejs`

#Install nodejs packages

Run this command in your root laravel folder

`npm install express ioredis socket.io --save --no-bin-links`

If all installed ok you should have these tree packages in /vagrant/shared/www/chat/node_modules


#Update composer.json

add

```
"predis/predis": "~1.1@dev"
```

and run `composer update`

**Open config/app.php**

To avoid conflict with Redis in PHP environment we will modify also the alias to the Redis module of Laravel.

locate

`'Redis'    => 'Illuminate\Support\Facades\Redis',`

and renamed into

`'L5Redis'    => 'Illuminate\Support\Facades\Redis',`


#Create routes, controllers and views for our Socket Chat

**open app/Http/routes.php and add**

```
Route::get('home', 'ChatController@index');
Route::get('systemMessage', 'ChatController@systemMessage');
```

**open app/Http/Controllers and add ChatController from our archive**

`app/Http/Controllers/ChatController.php`

**open resources/views/ and replace app.blade.php and home.blade.php views from our archive**
*be carrefu, you should replace it only if you have a fresh installation, else you could broke your existing file!*

**chat.js server**

Copy chat.js from our root archive to your laravel and run next command to start socket server

`node chat.js`

You should receive `Listening on *:8080`

#Let`s do a test

1) Open two browsers at http://chat.dev/home and login with two users you created

2) Write some messages between users

3) Open http://www.chat.dev/systemMessage to send a system message to all connected users


#Notice

This is a simple demo of how stuff works. You could extend as you want!

#Thanks

[codetutorial.io](http://www.codetutorial.io/laravel-5-and-socket-io-tutorial/) for their nice tutorial

