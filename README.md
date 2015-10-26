# Dataelections.fr

Ce dépôt héberge le code source du site
[dataelections.fr](https://dataelections.fr). Il est développé grâce au
framework Symfony2. Vous pouvez lire cet [article de
blog](http://blog.guilro.com/french/2015/10/22/dataelections.html) pour plus
d'information

# Installation

Clonez ce dépôt dans un dossier, et installez la dernière version de la base de
données SQLite :

    $ git clone https://github.com/guilro/dataelections.fr
    $ cd dataelections.fr
    $ curl https://dataelections.fr/data.db3 > web/data.db3

Avec la dernière version de Composer, installez les dépendances. Composer vous
demande les paramètres de votre installation, que vous pouvez laisser tel
quel si vous voulez utiliser SQLite. Choisissez simplement un `secret` unique,
et un `admin_user` et `admin_password` qui vous permettront d'accéder à
l'administration :

    $ curl -sS https://getcomposer.org/installer | php
    $ ./composer.phar install

À vous ensuite de configurer un serveur. Le serveur doit pouvoir écrire dans les
répertoires `app/cache` et `app/logs`. Vous pouvez vous aider de la
[documentation Symfony2](http://symfony.com/doc/current/book/installation.html)
pour cela.

# Utiliser Mysql à la place de SQLite

Pour de meilleurs performances, vous pouvez utiliser Mysql plutôt que SQLite.
Vous devez télécharger la dernière copie de la base de données et l'importer
dans Mysql :

    $ curl https://dataelections.fr/data.sql > web/data.sql.gz
    $ gunzip web/data.sql.gz
    $ mysql -u utilisateur -p nomdelabase < web/data.sql

Pour cela éditez le fichier `app/config/parameters.yml`. Entrez `pdo_mysql`
comme valeur pour `database_driver`, et réglez les autres paramètres du serveur.

# Exécuter les tests

Pour exécuter les tests :

    $ phpunit -c app/

Les tests sont effectués sur une base de données SQLite indépendante de la base
de production.

# Contributions

Toutes les contributions sont les bienvenues. N'hésitez pas à lire [l'article de
blog](http://blog.guilro.com/french/2015/10/23/dataelections.html) présentant le
projet. Vous pouvez ensuite faire des Pull Requests.

Pour respecter le coding style, merci d'utiliser [php-cs-
fixer](http://cs.sensiolabs.org/) avec le fichier de configuration `.php_cs`
fourni à la racine du projet.
