<div align="center">
    <img alt="Kudos logo" src="assets/icon-256x256.png" width="75">
    <h1>Kudos Donations</h1>
    Add a donation button to any page on your website. Easy & fast setup. Works with Mollie payments.
    <br>
    <br>
    <img src="assets/demo-1.gif" alt="Kudos Donations">
</div>

## Development

Use the [yarn](https://yarnpkg.com/) and [composer](https://getcomposer.org/) package managers to install the required dependencies.

### Build

````bash
# install dependencies
yarn install
composer install

# start development
yarn run start

# build assets for export
yarn run build:production

# export plugin to KUDOS_EXPORT folder and produce installable zip
yarn export
````

### Docker

Included is a docker environment used for development of the plugin. This environment includes the following containers:
- WordPress - The WordPress software.
- MariaDB - Database container.
- Adminer - A web interface for manipulating the database.
- ngrok - Makes local environment accessible publicly (useful for Mollie webhooks).

To use the included docker environment run the following from the docker sub-folder:
````bash
docker-composer up -d
````

Once up and running you can access the various containers from these addresses:

- Wordpress - http://localhost:8080
- ngrok - http://localhost:4040
- Adminer - http://localhost:8081


## Credits
This software uses the following open source packages:
- [Tailwindcss](https://tailwindcss.com/) - A utility-first CSS framework.
- [Mollie API](https://github.com/mollie/mollie-api-php) - Mollie API client for PHP.
- [Micromodal](https://micromodal.now.sh/) - A lightweight, configurable and a11y-enabled modal library written in pure JavaScript.
- [jQuery Validation](https://github.com/jquery-validation/jquery-validation) - Provides drop-in validation for your existing forms. 
- [Twig](https://twig.symfony.com/) - A modern template engine for PHP.
- [WordPress Plugin Boilerplate](https://github.com/DevinVinson/WordPress-Plugin-Boilerplate) -  A foundation for WordPress Plugin Development.
- [Webpack](https://webpack.js.org/) - Module bundler.
- [Babel](https://github.com/babel/babel-loader) - A compiler for writing next generation JavaScript.
- [PostCSS](https://github.com/postcss/postcss-loader) - A tool for transforming CSS with JavaScript.

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.