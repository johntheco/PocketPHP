# PocketPHP

> **Minimalistic server boilerplate written with pure PHP.**
> <br>
> <br>
> **Developed by:** `John Theco`<br>
> **Email:** `john.theco.dev@gmail.com`<br>
> **Version:** `0.0.1`<br>

PocketPHP is extremely minimalistic micro-boilerplate for building simple PHP websites and applications with support of basic functionality such as `models`, `routing`, `templating`, `error Handling`, `static files` and `regexp routes handling`. PocketPHP was in development throughout a few years, but mainly was suitable only for educational purposes, such as learning about basic mechanisms of server applications, MVC design pattern, or PHP programming language, or simple prototyping just for myself.

`I've stopped developing it or using it a long time ago and keeping it just for the sake of history.`

## Structure

> **PocketPHP file and directory structure**

| **Directory/File** | **Usage** |
| ------------- | --------- |
| `public` | Default static files folder. |
| `routes` | Website routes directory. |
| `server` | PocketPHP core source. |
| `views`  | Website views directory. |
| `.htaccess` | Apache configuration file for redirecting all requests to `index.php` in the website root directory. |
| `index.php` | Startup `index.php` script in the website root directory. |

## Configuration file

> **How server's configuration works?**

PocketPHP configuration is based on `config/config.php` script, containing the following parameters:

| **Name** | **Property** |
| -------- | ------------ |
| `ROOT`   | `$_SERVER[DOCUMENT_ROOT]` global variable shortcut. |
| `LAYOUT` | Default server's layout used for templating purposes. |
| `ASSETS` | Static files folder. Default folder is named `public` and located in the website root. |
| `HOST` | MySQL `Host` connection parameter. |
| `DATABASE` | MySQL `Database` connection parameter. |
| `USERNAME` | MySQL `Username` connection parameter. |
| `PASSWORD` | MySQL `Database` connection parameter. |
| `MONGO_PORT` | MongoDB `Port` connection parameter. |

## Routing

> **How routing works?**

PocketPHP reads all PHP scripts in the `routes` folder of the website root and then uses them for routing, templating, and static files loading purposes. Every route has `route` parameter (route's URL, e.g. `/` route is equivalent to `your-domain.com/` URL), `view` parameter (associated HTML view page), and list of `arguments`, such as `title`, `layout`, `css`, `js`, and etc. You can look at `routes/home.php` file to learn the basic syntax for route declaration.

The `route` parameter can also work with regular expressions e.g. `'#^/regexp*(/)[/a-zA-Z0-9_]*$#'`. You can find `routes/regexp.php` file and `views/regexp.php` to see the whole picture.

By the way, `Router` supports such HTTP methods as `GET`, `POST`, `PUT`, `PATCH` and `DELETE`, which you can use in your website. With that `Router` script also can detect usage of unsupported methods with `405` error, and detect requests to the nonexistent routes with special `404` page.


## Static Files

> **How static files work?**

PocketPHP has support for static files. Configuration file `config/config.php` contains the `ASSETS` parameter, which sets the location of the static files folder. Once it set up, you can use all your assets (css, js, images, etc) by simply requesting it with `your-domain.com/[path-to-file]` (e.g., if you need file from `public/assets/css/style.css` you just request `your-domain.com/assets/css/style.css`). `Router` script will automatically match required MIME-type with the static file.

## Templating

> **How templating works?**

PocketPHP has basic templating functionality for reducing need to write repetitive HTML code. 

Also, templating in PocketPHP supports partials, which you can include in your layouts and views. Actually, in the default `main.php` layout already used few of partials - `lang-detector.php`, `css-autoloader.php` and `js-autoloader.php`. The first one helps to identify default language of browser, and next two used for including all css and js files, attached to current route.

In case, if current route point to the nonexistent layout or view, PocketPHP will show an error page suggesting creation of requested files. You can try it out with routes `no-view` and `no-template`, configured in the `routes/errors.php` route script.


# License

PocketPHP is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
