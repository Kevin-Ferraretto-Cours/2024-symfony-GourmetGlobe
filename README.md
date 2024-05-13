GourmetGlobe
========================

Specifications
------------

  * PHP 8.2.0 ou supérieur ;
  * L'extension PHP PDO-SQLite est activée ;
  * et les [exigences habituelles de l'application Symfony][1].

Utilisation
-----

Il n'est pas nécessaire de configurer quoi que ce soit avant de lancer l'application. Il existe deux façons différentes d'exécuter cette application en fonction de vos besoins :

**Option 1.** [Télécharger Symfony CLI][2] et exécutez la commande suivante après avoir cloner le repo:

```bash
cd GourmetGlobe/
symfony serve
```

Accédez ensuite à l'application dans votre navigateur à l'adresse URL indiquée. (<http://localhost:8000> par défaut).

**Option 2.** Utiliser un serveur web comme Nginx ou Apache pour exécuter l'application
(lire la documentation sur la [configuration d'un serveur web pour Symfony][3]).

Sur votre machine locale, vous pouvez exécuter cette commande pour utiliser le serveur web PHP intégré :

```bash
cd GourmetGlobe/
php -S localhost:8000 -t public/
```


[1]: https://symfony.com/doc/current/setup.html#technical-requirements
[2]: https://symfony.com/download
[3]: https://symfony.com/doc/current/setup/web_server_configuration.html
