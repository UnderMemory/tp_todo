# Projet TodoListe
Installation minimale du package Symfony

```bash
symfony new (my_project_name) --version=5.2
```

## Objectifs
- Voir les composants requis au fur et à mesure
- Gestion de données avec système CRUD
- Class Formulaires et controles (contraintes)
- 2 entités relation ManyToOne
- Déploiement sur Heroku

# Etape 1
## Check system 

```bash
symfony check:requirements
```

## Install de composants
Voir ces adresses : 
~ https://packagist.org/
~ https://flex.symfony.com/

```bash
composer require symfony/maker-bundle
```

## Configuration BDD 
nom = db_dev_todolist
On a besoin de doctrine = ORM 
Voir la doc guide : https://symfony.com/doc/current/doctrine.html#installing-doctrine

```bash
# On tape symfony console doctrine et on nous donne :
composer require symfony/orm-pack
# Le fichier env à été modifié (on va pouvoir créer les connections)
DATABASE_URL="mysql://root:@127.0.0.1:3306/db_dev_todolist"
# puis
symfony console doctrine:database:create
```
#
# Entité
## Principe de relation
- Une Todo appartient à une catégorie.
- Une catégorie contient zéro ou plusieurs Todo.

## Entité 
- Category(name(string))
- Todo(title(string), content(text), created_at(datetime), updated_at(datetime), date_for(datetime), #category)

```bash
symfony console make:entity category
# puis 
symfony console make:entity todo
# puis la relation
symfony console make:entity todo
# on ajoute le champ category
# on choisit comme type : relation
```

#
# Migrations

```bash
php bin/console make:migration
symfony console doctrine:migrations:migrate
```

#
# Fixtures (optionel)

```bash
# En mode --dev dans un 1er temps
composer require orm-fixtures --dev
```

## Alimenter les tables 
__NB__ : 
- Voir comment définir des dates de créations et d'update dès la création d'une Todo.
- Constructeur de la classe Todo.

### Analyse
1. La table Category doit être remplie en premier
- On part d'un tableau de catégorie.
- Pour chaque catégorie :
    je veux l'enregistrer dans la table physique. 
- Sous symfony, tout passe par l'objet --> la classe Category
2. La table Todo
- On crée un objet Todo.
__NB__ : la méthode `setCategory` qui a besoin d'un objet Category comme argument

#
# Controllers
## TestController
L'objectif est de voir le format de rendu que propose le controller, sachant que Twig n'est pas installé. 

```bash
symfony console make:controller Test
```

## Installer Twig

```bash
composer require twig
```

## Todo Controller

```bash
symfony console make:controller Todo
# On a une vue créée dans le dossier Template
```

### La page d'accueil des ToDo
Le controller va récupérer notre premier enregistrement de la table Todo et le passer à la vue 'todo/index'

La mise en forme est gérée par des tables Bootstrap

### La mise en page Détail (voir)
1. Une méthode et sa route.

```php
# Le repository en injection de dépendance
    public function detail($id, TodoRepository $repo): Response
```

2. Une vue dans template ToDo.
3. Le lien au niveau du btn voir de la page d'accueil.

#
# Formulaires
## Install 

```bash
composer require form validator
```

## Generate form
## Etape 1
Génération de la classe du nom que l'on souhaite. 

```bash
symfony console make:form
#TodoFormType à été choisi
``` 

## Etape 2
On crée une méthode dans le TodoController. Dans notre cas, ce sera la méthode `create`.
On va créer le lien du bouton pour tester le cheminement jusqu'à la vue `create.html.twig`.

### Problématique des routes

```bash
# Besoin d'installer le profiler pour debugger
composer require --dev symfony/profiler-pack
#aussi
symfony console debug:router
```

### Voir :
1. La forme des urls. Ex: `/todo, /todo/1, todo/1/edit`.
2. L'ordre de placement des méthodes peut influer.
3. Possibilité d'ajouter un paramètre priority ( LIRE LA DOC ! ).

## Etape 3 
Gestion du formulaire dans la méthode adéquate du controller. 
Affichage du formulaire dans la vue.

### TodoController / Create() : traiter le formulaire
• Voir l'injonction de dépendance. 

```php
public function create(Request $request, EntityManagerInterface $em) : Response
```

### Améliorer le visuel
Dans config/package/twig.yaml

```yaml
form_themes: ["bootstrap_4_layout.html.twig"]
```

### Problématique du champ category
Il fait référence à une relation avec une entité
On va ajouter des types à la classe `TodoFormType`

### Ajouter d'autres types 
Voir la doc. PLusieurs options concurrentes.

## TodoController : update()
- On installe un bundle dont le rôle est de faire la correspondance entre une url avec l'id d'un objet et l'objet passé en param. 

```bash
composer req sensio/framework-extra-bundle
```

## Créer un lessage flash
- Voir la doc. Taper : Flash et sélectionner `Flash messages`.
- 1 partie dans le controller : la construction du message
- 1 autre dans la vue `update.html.twig` : l'affichage selon le choix proposé dans la doc.

## TodoController : delete
### Méthode 1
Un lien depuis la page d'accueil

```bash
composer require symfony/security-csrf
```

### Méthode 2 
- Lien dans la page Update
- On a ajouté une confirmation en JavaScript
__NB__ : Attention à l'emplacement de `{% block javascript %}`.

#
# Ajouter une NavBar
- Un fichier _navbar.html.twig avec une navbar bootstrap.
    -> Un bouton acceuil 
    -> Un titre
    -> Un menu déroulant 
- L'inclure dans base.html.twig dans un block `{% block navbar %}`.

# Contraintes de formulaires

## Dans TodoFormType
Voir pour inhiber le contrôle HTML5

```php
 public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Todo::class,
            'attr' => [
                'novalidate' => 'novalidate'
            ]
        ]);
    }
```

Voir les contraintes des champs.
Ici, dans le cas où un champ est considéré comme nullable = false dans la database.
> Voir empty_data 

```php
    ->add('title', TextType::class, [
        'label' => "Un titre en quelque mots",
        'empty_data' => "",
```

## Dans l'entité Todo
Ne pas oublier d'importer la classe mais pas Mozart\Assert.
Copier/coller depuis la doc.
Un exemple :

```php
    # La classe à importer
    use Symfony\Component\Validator\Constraints as Assert;

   /**
     * @Assert\NotBlank(message="Ce champ ne peut être vide")
     * @Assert\Length(min = 15, minMessage = "Au minimum {{ limit }} caractères")
     * @ORM\Column(type="string", length=255)
     */
    private $title;
```

#
# Version de l'appli avec SQLite

## Procédure à suivre
1. Installer SQLiteStudio
2. Définir la connexion dans le fichier env.

```bash
 DATABASE_URL="sqlite:///%kernel.project_dir%/var/todo.db"
 ```

3. Créer ce fichier 

```bash
symfony console doctrine:database:create
```

4. Créer une migration pour base de donnée SQLite

```bash
# Virer les migrations actuelles
symfony console make:migration
symfony console doctrine:migrations:migrate
symfony console doctrine:fixtures:load 
```

5. Fixtures

```bash
symfony console doctrine:fixtures:load 
```

6. Tester et voir dans SQLiteStudio

#
# PostGreSQL
#
## Installation
1. Installation de PostGreSQL

```yaml
url : https://www.enterprisedb.com/downloads/postgres-postgresql-downloads
```

2. DLL dans php.ini

```bash
# 2 extensions à décommenter
extension=pdo_pgsql
extension=pgsql
```

3. Installer l'interface pgAdmin
4. Configurer Symfony

```yaml
# dans config\packages/doctrine.yaml, ajouter : 
dbal :
    driver: 'pdo_pgsql'
    charset: utf8
```

5. Connexion à PostgreSql dans le fichier .env

```bash
 DATABASE_URL="postgresql://postgres:iui0195gaz5@127.0.0.1:5432/db_pg_todolist"
```
6. Créer la base de données

```bash
symfony console doctrine:database:create
```

7. Migrations

```bash
# Virer les migrations actuelles
symfony console make:migration
symfony console doctrine:migrations:migrate
symfony console doctrine:fixtures:load 
```

8. Fixtures

```bash
symfony console doctrine:fixtures:load 
``` 

## Migrations et fixtures en prod
- Aller voir dans 
/bundles.php

```php
    Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle::class => ['all' => true]
```

- Aller dans composer.json

```js
# Décaler cette ligne dans require
  "doctrine/doctrine-fixtures-bundle": "^3.4"

# Puis on ajoute une structure dans scripts :
   "scripts": {
        "compile":[
            "php bin/console doctrine:migrations:migrate",
            "php bin/console doctrine:fixtures:load --no-interaction --env=PROD"
        ],
```


## Compte Heroku

1. Créer un compte
2. Installer Heroku cmd line
    > https://devcenter.heroku.com/articles/heroku-cli#download-and-install
3. Depuis un terminal : taper : `heroku`

## Créer une application
1. heroku create
> Faire un login si besoin
2. Configurer en mode prod

```bash
    heroku config:set APP_ENV=prod
```

3. PostgreSQL
Dans Heroku, on doit lui dire le SGBD a utiliser. 
Chez Heroku, on trouve ça dans les addons.

```bash
# Voir aussi l'interface utilisateur de Heroku
    heroku addons:create heroku-postgresql:hobby-dev
```

Après l'install, normalement, il a créé une variable d'environnement DATABASE_URL.

## Déploiement 

```bash
    git push heroku
```

### En cas d'erreurs 
1. Voir le fichier log
2. Cas de problème avec composer 

```bash
# Supprimer composer.lock
composer install
# Puis
git add. , commit, push
# Et
git push heroku