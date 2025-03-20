# Paquet Web Application Firewall (WAF) pour Laravel

Ce package a pour but de protéger votre application Laravel contre différents types d'attaques telles que XSS, SQLi, RFI, LFI, User Agent, et bien d'autres encore. Il bloquera également les attaques répétées et enverra une notification par email et/ou slack lorsque l'attaque est détectée. De plus, il enregistre les échecs de connexion et bloque l'IP après un certain nombre de tentatives.

Note : Certaines classes de middleware (i.e. Xss) sont vides car la classe abstraite `Middleware` qu'elles étendent fait tout le travail, dynamiquement. En bref, tout fonctionne ;)

## Démarrer

### 1. Installer

Exécutez la commande suivante :

```bash
composer require likewares/laravel-firewall
```

### 2. Publier

Publier la configuration, la langue et les migrations

```bash
php artisan vendor:publish --tag=firewall
```

### 3. Base de données

Créer les tables de la base de données

```bash
php artisan migrate
```

### 4. Configuration

Vous pouvez modifier les paramètres du pare-feu de votre application à partir du fichier `config/firewall.php`.

## Utilisation

Les middlewares sont déjà définis, il suffit donc de les ajouter aux routes. Le middleware `firewall.all` applique tous les middlewares disponibles dans le tableau `all_middleware` du fichier de configuration.

```php
Route::group(['middleware' => 'firewall.all'], function () {
    Route::get('/', 'HomeController@index');
});
```

Vous pouvez appliquer chaque intergiciel par route. Par exemple, vous pouvez autoriser uniquement les IP figurant sur la liste blanche à accéder à l'administration :

```php
Route::group(['middleware' => 'firewall.whitelist'], function () {
    Route::get('/admin', 'AdminController@index');
});
```

Ou vous pouvez être notifié lorsque quelqu'un qui n'est PAS dans la `whitelist` accède à l'administration, en l'ajoutant à la configuration `inspections` :

```php
Route::group(['middleware' => 'firewall.url'], function () {
    Route::get('/admin', 'AdminController@index');
});
```

Les middlewares disponibles applicables aux routes :

```php
firewall.all

firewall.agent
firewall.bot
firewall.geo
firewall.ip
firewall.lfi
firewall.php
firewall.referrer
firewall.rfi
firewall.session
firewall.sqli
firewall.swear
firewall.url
firewall.whitelist
firewall.xss
```

Vous pouvez également définir des `routes` pour chaque middleware dans `config/firewall.php` et appliquer ce middleware ou `firewall.all` au sommet de toutes les routes.

## Notifications

Le pare-feu enverra une notification dès qu'une attaque aura été détectée. Les emails entrés dans la configuration `notifications.email.to` doivent être des utilisateurs Laravel valides afin d'envoyer des notifications. Consultez la documentation Notifications de Laravel pour plus d'informations.

## License

The MIT License (MIT). Please see [LICENSE](LICENSE.md) for more information.
