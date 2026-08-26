# Rapport de performance – Endpoints de l’application

Ce document présente les performances observées sur les principaux endpoints de l’application Symfony. Les valeurs indiquées correspondent aux résultats mesurés dans le projet et permettent de poser un diagnostic fonctionnel et technique sur la charge de chaque route.

## 1. Synthèse générale

La majorité des endpoints affichent de bonnes performances en environnement local, avec des temps de réponse globalement inférieurs à 250 ms pour les pages de lecture et de liste. Les actions les plus lourdes concernent les suppressions avec dépendances, les créations avec traitement de fichiers et les listes volumineuses (portfolio, utilisateurs, médias, albums).

### Tableau récapitulatif

| Endpoint | Type | Temps | Requêtes SQL | Mémoire | Verdict |
|---|---|---:|---:|---:|---|
| / | Front office | 65–145 ms | 0 | 4 MiB | Très bon |
| /guests | Listing invités | 135–219 ms | 47 en 33 ms | 4 MiB | Bon |
| /guest/{id} | Détail invité | 100–139 ms | 2 en 5 ms | 4 MiB | Très bon |
| /portfolio/{id} | Portfolio | 95–144 ms | 3 en 6,7 ms | 4 MiB | Très bon |
| /about | Page statique | 65–101 ms | 0 | 4 MiB | Très bon |
| /login | Connexion | 72–150 ms | 0 | 4 MiB | Très bon |
| /admin/album | Listing albums | 97–130 ms | 2 en 3,5 ms | 4 MiB | Très bon |
| /admin/album/add | Création album | 113–191 ms (affichage), 300 ms (submit) | 1 en 2,2 ms / 5 en 7,3 ms | 4 MiB / 6 MiB | Bon |
| /admin/album/update/{id} | Mise à jour album | 115–175 ms (affichage), <250 ms (submit) | 2 en 3,2 ms / 5 en 8,5 ms | 6 MiB | Bon |
| /admin/album/delete/{id} | Suppression album | <200 ms | 6 en 10,25 ms | 4 MiB | Bon |
| /admin/media | Listing médias | 104–178 ms | 3 en 6 ms | 4 MiB | Bon |
| /admin/media/add | Création média | 135–200 ms (affichage), <250 ms (submit) | 3 en 4,5 ms / 7 en 9,5 ms | 4 MiB / 6 MiB | Bon |
| /admin/media/delete/{id} | Suppression média | <200 ms | 5 en 9 ms | 4 MiB | Bon |
| /admin/user | Listing utilisateurs | 115–134 ms | 3 en 4,3 ms | 4 MiB | Très bon |
| /admin/user/add | Création utilisateur | 132–200 ms (affichage), <1000 ms (submit) | 3 en 4,5 ms / 5 en 10,25 ms | 6 MiB | Bon |
| /admin/user/{id}/delete | Suppression utilisateur | <200 ms | 56 en 41,6 ms | 6 MiB | Moyen |
| /admin/user/{id}/deactivate | Désactivation | <100 ms | 5 en 6 ms | 4 MiB | Très bon |
| /admin/user/{id}/activate | Activation | <100 ms | 5 en 6 ms | 4 MiB | Très bon |

## 2. Analyse globale

### Points forts
- La majorité des pages sont très rapides.
- Les routes statiques ou simples restent sous 150 ms.
- Les écrans de liste paginés se comportent correctement avec des temps acceptables.
- Les requêtes SQL sont globalement faibles, ce qui indique un bon usage des repositories et de la persistance.

### Points de vigilance
- Les suppressions de données liées (utilisateur avec médias/images) peuvent devenir plus lourdes.
- Les pages de portfolio et de listes longues peuvent devenir plus coûteuses à grande échelle.
- Les actions de création/suppression avec dépendances doivent être surveillées en production si le volume de données augmente.

---

## 3. Détail par endpoint

## 1) GET /
### Route : home
### Type : Front office / page d’accueil
### Analyse
- Page légère, sans logique métier conséquente.
- Temps de réponse très satisfaisant.

### Résultat observé
- Temps : 65–145 ms
- SQL : 0
- Mémoire : 4 MiB
- Verdict : Très bon

---

## 2) GET /guests
### Route : guests
### Type : Lecture / listing des invités actifs
### Analyse
- La route charge une liste d’utilisateurs actifs via la repository.
- Le temps de réponse reste raisonnable pour 46 utilisateurs.
- La charge SQL est plus importante car plusieurs lignes sont traitées.

### Résultat observé
- Temps : 135–219 ms
- SQL : 47 en 33 ms
- Mémoire : 4 MiB
- Note : valeurs mesurées pour 46 utilisateurs affichés.
- Verdict : Bon

### Recommandation
- Ajouter une pagination ou un filtrage plus strict si le nombre d’utilisateurs actifs augmente.

---

## 3) GET /guest/{id}
### Route : guest
### Type : Lecture / affichage d’un invité
### Analyse
- La vérification du rôle utilisateur actif est rapide.
- L’affichage d’un profil unique reste très performant.

### Résultat observé
#### Cas normal
- Temps : 100–139 ms
- SQL : 2 en 5 ms
- Mémoire : 4 MiB

#### Cas redirection
- Temps : ~85 ms
- SQL : 1 en 2 ms
- Mémoire : 4 MiB

- Verdict : Très bon

---

## 4) GET /portfolio/{id}
### Route : portfolio
### Type : Lecture / portfolio visuel
### Analyse
- Route potentiellement plus coûteuse selon le nombre d’albums et de médias associés.
- Le rendu reste bon pour les volumes testés, mais il mérite une surveillance si le contenu grandit.

### Résultat observé
- Temps moyen : 95–144 ms
- SQL : 3 en 6,7 ms
- Mémoire : 4 MiB
- Verdict : Très bon

### Recommandation
- Prévoir pagination ou chargement partiel des médias si le nombre de contenus devient important.

---

## 5) GET /about
### Route : about
### Type : Front office / page informative
### Analyse
- Page statique, très légère.

### Résultat observé
- Temps : 65–101 ms
- SQL : 0
- Mémoire : 4 MiB
- Verdict : Très bon

---

## 6) GET /login
### Route : admin_login
### Type : Authentification / vue de connexion
### Analyse
- Route simple, sans logique métier lourde.

### Résultat observé
- Temps : 72–150 ms
- SQL : 0
- Mémoire : 4 MiB
- Verdict : Très bon

---

## 7) GET /admin/album
### Route : admin_album_index
### Type : Back office / listing des albums
### Analyse
- La liste est rapide avec un volume raisonnable.
- L’absence de grande quantité de données ne crée pas de contrainte majeure.

### Résultat observé
- Temps : 97–130 ms
- SQL : 2 en 3,5 ms
- Mémoire : 4 MiB
- Verdict : Très bon

### Recommandation
- Ajouter une pagination si le nombre d’albums augmente dans le temps.

---

## 8) GET /admin/album/add
### Route : admin_album_add
### Type : Back office / création d’un album
### Analyse
- Le formulaire est rapide à afficher.
- La validation et le submit sont légèrement plus longs, mais restent dans des seuils acceptable.

### Résultat observé
#### Affichage
- Temps : 113–191 ms
- SQL : 1 en 2,2 ms
- Mémoire : 4 MiB

#### Soumission
- Temps moyen : 300 ms
- SQL : 5 en 7,3 ms
- Mémoire : 6 MiB

- Verdict : Bon

---

## 9) GET /admin/album/update/{id}
### Route : admin_album_update
### Type : Back office / mise à jour album
### Analyse
- L’écran de modification reste performant.
- Le submit reste acceptable avec un traitement léger.

### Résultat observé
#### Affichage
- Temps : 115–175 ms
- SQL : 2 en 3,2 ms
- Mémoire : 6 MiB

#### Soumission
- Temps : <250 ms
- SQL : 5 en 8,5 ms
- Mémoire : 6 MiB

- Verdict : Bon

---

## 10) GET /admin/album/delete/{id}
### Route : admin_album_delete
### Type : Back office / suppression album
### Analyse
- Suppression plus lourde car elle dépend du contexte de l’album et de ses relations.
- Le temps reste correct mais il faut surveiller le comportement avec davantage de données.

### Résultat observé
- Temps : <200 ms
- SQL : 6 en 10,25 ms
- Mémoire : 4 MiB
- Verdict : Bon

---

## 11) GET /admin/media
### Route : admin_media_index
### Type : Back office / listing médias
### Analyse
- La route reste bien dans des temps corrects.
- Le filtrage par utilisateur réduit la charge côté admin non autorisé.

### Résultat observé
- Temps : 104–178 ms
- SQL : 3 en 6 ms
- Mémoire : 4 MiB
- Verdict : Bon

### Recommandation
- Maintenir la pagination et optimiser les indices sur les champs de tri et de filtrage.

---

## 12) GET /admin/media/add
### Route : admin_media_add
### Type : Back office / création média
### Analyse
- Le formulaire est stable.
- Le temps de soumission reste acceptable mais peut augmenter selon la taille des fichiers.

### Résultat observé
#### Affichage
- Temps : 135–200 ms
- SQL : 3 en 4,5 ms
- Mémoire : 4 MiB

#### Soumission
- Temps : <250 ms
- SQL : 7 en 9,5 ms
- Mémoire : 6 MiB

- Verdict : Bon

### Recommandation
- Prévoir un traitement asynchrone pour les gros fichiers si le site grossit.

---

## 13) GET /admin/media/delete/{id}
### Route : admin_media_delete
### Type : Back office / suppression média
### Analyse
- La suppression reste rapide dans le scénario testée.
- Le nettoyage du média ou des fichiers dépendants peut devenir plus costaud en production.

### Résultat observé
- Temps : <200 ms
- SQL : 5 en 9 ms
- Mémoire : 4 MiB
- Verdict : Bon

---

## 14) GET /admin/user
### Route : admin_user_index
### Type : Back office / listing utilisateurs
### Analyse
- Liste rapide et stable.
- L’utilisation de pagination et de count séparé est adaptée.

### Résultat observé
- Temps : 115–134 ms
- SQL : 3 en 4,3 ms
- Mémoire : 4 MiB
- Verdict : Très bon

---

## 15) GET /admin/user/add
### Route : admin_user_add
### Type : Back office / création utilisateur
### Analyse
- Le formulaire et la création restent dans des temps acceptables.
- Le coût de hashage du mot de passe est le principal facteur de fluctuation.

### Résultat observé
#### Affichage
- Temps : 132–200 ms
- SQL : 3 en 4,5 ms
- Mémoire : 6 MiB

#### Soumission
- Temps : <1000 ms
- SQL : 5 en 10,25 ms
- Mémoire : 6 MiB

- Verdict : Bon

---

## 16) GET /admin/user/{id}/delete
### Route : admin_user_delete
### Type : Back office / suppression utilisateur
### Analyse
- Cette action est la plus lourde du lot lorsqu’un utilisateur est lié à de nombreuses images ou relations.
- Le nombre de requêtes SQL est significatif, ce qui alourdit la suppression.

### Résultat observé
- Temps : <200 ms
- SQL : 56 en 41,6 ms
- Mémoire : 6 MiB
- Note : valeurs mesurées pour un environnement avec environ 50 images liées.
- Verdict : Moyen

### Recommandation
- Vérifier les relations cascade et envisager une suppression logique ou une optimisation des dépendances si le volume augmente.

---

## 17) GET /admin/user/{id}/deactivate
### Route : admin_user_deactivate
### Type : Back office / désactivation utilisateur
### Analyse
- Action très légère, sans coût important.

### Résultat observé
- Temps : <100 ms
- SQL : 5 en 6 ms
- Mémoire : 4 MiB
- Verdict : Très bon

---

## 18) GET /admin/user/{id}/activate
### Route : admin_user_activate
### Type : Back office / activation utilisateur
### Analyse
- Action légère et stable.

### Résultat observé
- Temps : <100 ms
- SQL : 5 en 6 ms
- Mémoire : 4 MiB
- Verdict : Très bon

---

## 4. Recommandations globales

- Surveiller les endpoints à forte relation métier : suppression utilisateur, suppression album, portfolio, media, user et album.
- Renforcer la pagination sur les listes volumineuses.
- Vérifier les relations N+1 sur les entités User, Media et Album.
- Optimiser les index de base de données sur les colonnes de tri et de filtrage.
- Prévoir un traitement asynchrone pour les uploads de média si la volumétrie augmente.
- Garder les pages statiques sous cache pour stabiliser les performances en production.

## 5. Conclusion

Les performances globales de l’application sont satisfaisantes pour les volumes testés. Les routes les plus critiques sont celles liées à la suppression avec dépendances et aux listes volumineuses. À l’échelle de la production, les recommandations prioritaires concernent la pagination, l’optimisation des requêtes SQL et la gestion des relations entre entités.

### Verdict final
- Niveau global : Bon
- Risque principal : croissance du volume de données et suppressions liées
- Priorité d’optimisation : portfolio, admin/media, admin/user, admin/album, suppressions de dépendances
