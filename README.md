# GourmetGlobe 🌍🍽️

Une application web Symfony permettant de gérer une base de données de recettes et d'ingrédients du monde entier.

## 📋 Présentation

GourmetGlobe est une application web développée avec Symfony 7 qui permet aux utilisateurs de:
- Consulter, créer et gérer des recettes
- Gérer des ingrédients
- Rechercher des recettes par ingrédients
- Commenter des recettes
- Ajouter des recettes à ses favoris

## 🛠️ Prérequis techniques

- PHP 8.2.0 ou supérieur
- Extension PHP PDO-SQLite activée
- [Exigences habituelles de l'application Symfony](https://symfony.com/doc/current/setup.html#technical-requirements)
- SQLite (base de données)

## 🚀 Installation

Aucune configuration spécifique n'est nécessaire avant de lancer l'application.

### Option 1: Utiliser Symfony CLI (recommandé)

1. Cloner le dépôt:
```bash
git clone https://github.com/Kevin-Ferraretto-Cours/2024-symfony-GourmetGlobe.git
```

2. Se déplacer dans le répertoire du projet:
```bash
cd 2024-symfony-GourmetGlobe/
```

3. Installer les dépendances:
```bash
composer install
```

4. [Télécharger Symfony CLI](https://symfony.com/download) et lancer le serveur:
```bash
symfony serve
```

5. Accéder à l'application dans votre navigateur à l'adresse indiquée (http://localhost:8000 par défaut).

### Option 2: Utiliser le serveur PHP intégré

1. Cloner le dépôt:
```bash
git clone https://github.com/Kevin-Ferraretto-Cours/2024-symfony-GourmetGlobe.git
```

2. Se déplacer dans le répertoire du projet:
```bash
cd 2024-symfony-GourmetGlobe/
```

3. Installer les dépendances:
```bash
composer install
```

4. Lancer le serveur PHP intégré:
```bash
php -S localhost:8000 -t public/
```

5. Accéder à l'application dans votre navigateur à l'adresse http://localhost:8000.

### Option 3: Utiliser un serveur web (production)

Pour déployer l'application sur un serveur web comme Nginx ou Apache, consultez la [documentation sur la configuration d'un serveur web pour Symfony](https://symfony.com/doc/current/setup/web_server_configuration.html).

## 📊 Modèle de données

L'application gère les entités suivantes:

### Recette
- Nom
- Texte descriptif
- Durée totale de préparation
- Nombre de personnes
- Photo
- Ingrédients (plusieurs avec quantité pour chaque)

### Ingrédient
- Nom

### Commentaire
- Texte
- Date

### Utilisateur
- Informations standard d'authentification
- Recettes favorites

## 🔍 Fonctionnalités

- **Gestion des utilisateurs**
  - Inscription
  - Connexion/Déconnexion

- **Gestion des recettes**
  - Liste complète avec photos, titres et durées
  - Visualisation détaillée de chaque recette
  - Ajout de nouvelles recettes
  - Modification de ses propres recettes
  - Suppression de ses propres recettes
  - Ajout/retrait des favoris
  - Ajout de commentaires

- **Gestion des ingrédients**
  - Liste complète
  - Visualisation détaillée avec recettes associées
  - Ajout de nouveaux ingrédients
  - Modification de ses propres ingrédients
  - Suppression de ses propres ingrédients (si non utilisés)

- **Recherche avancée**
  - Recherche de recettes par ingrédients
  - Affichage du pourcentage de disponibilité des ingrédients

## 📝 Licence

Ce projet est sous licence [MIT](LICENSE)

## 👨‍💻 Auteur

[Kevin Ferraretto](https://kevin-ferraretto.fr/)

---

*Projet réalisé dans le cadre du cours SN2 - Développement Web avec Symfony*
