<div align="center">
    <img alt="Kudos logo" src="assets/plugin/icon-128x128.png" width="128">
    <h1>Kudos Donations</h1>
    Add a donation button to any page on your website. Easy & fast setup. Works with Mollie payments.
    <br>
    <br>
    <img src="assets/plugin/demo-1.gif" alt="Kudos Donations">
</div>

## Development

Use the [npm](https://www.npmjs.com/) and [composer](https://getcomposer.org/) package managers to install the required
dependencies.

### Build

````bash
# install dependencies
npm install
composer install

# start public facing development
npm run start:front

# start admin facing development
npm run start:admin

# build all assets
npm run build
````

## Credits

This software uses the following open source packages:

- [Tailwind CSS](https://tailwindcss.com/) - A utility-first CSS framework.
- [Headless UI](https://github.com/tailwindlabs/headlessui) - Completely unstyled, fully accessible UI components,
  designed to integrate beautifully with Tailwind CSS.
- [Heroicons](https://heroicons.com/) - Beautiful hand-crafted SVG icons, by the makers of Tailwind CSS.
- [Mollie API](https://github.com/mollie/mollie-api-php) - Mollie API client for PHP.
- [React Hook Form](https://github.com/react-hook-form/react-hook-form) - Performant, flexible and extensible forms.
  with easy-to-use validation.
- [Symfony-DI](https://symfony.com/components/DependencyInjection) - Dependency injection component from Symfony.
- [Twig](https://twig.symfony.com/) - A modern template engine for PHP.
- [ActionScheduler](https://actionscheduler.org/) - WordPress Job Queue with Background Processing.