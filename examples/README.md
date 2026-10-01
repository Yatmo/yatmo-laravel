# Demo application

The listing site of the screenshot, on a fresh Laravel application:

```bash
laravel new maison-demo && cd maison-demo
composer require yatmo/laravel
# .env: YATMO_KEY, YATMO_FRONTEND_KEY, YATMO_COUNTRY=BE, YATMO_LANGUAGE=FR
cp vendor/yatmo/laravel/examples/routes.php routes/web.php
cp -r vendor/yatmo/laravel/examples/views/* resources/views/
php artisan serve
```

Then open `/listings/rue-de-la-loi-16` (full address: pin, street in the first title) and `/listings/zuid-antwerp-loft`
(discreet listing: circle of 300 m, city-only titles). The routes file holds two hard-coded listings in place of a model.
