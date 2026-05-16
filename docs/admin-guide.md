# Guide d'administration · GreenAmal

Ce guide explique comment gérer le site **greenamal.com** depuis l'espace
administrateur. Aucune connaissance technique n'est requise.

---

## 1. Se connecter à l'espace admin

1. Ouvrir un navigateur (Chrome, Safari, Firefox).
2. Aller à l'adresse : **https://greenamal.com/admin/login**
3. Saisir :
   - **Email** : votre adresse email d'administrateur
   - **Mot de passe** : votre mot de passe
4. Cliquer sur **Se connecter**.

> **Premier accès** : si c'est la toute première connexion, l'email est
> `admin@greenamal.com` et le mot de passe est `admin123`. **Le changer
> immédiatement** (voir section 8).

Une fois connectée, vous voyez le **tableau de bord** avec les chiffres
clés du site (revenus, commandes, clients).

---

## 2. Le menu principal

À gauche (ou en haut sur mobile), vous trouverez les sections suivantes :

| Section | À quoi ça sert |
|---|---|
| **Tableau de bord** | Vue d'ensemble : revenus, commandes du jour, top produits |
| **Commandes** | Liste des commandes reçues, leur statut, leurs détails |
| **Produits** | Catalogue : ajouter, modifier, supprimer, mettre en avant |
| **Catégories** | Familles de produits (Huiles, Couscous, Savons…) |
| **Coupons** | Codes promo (ex. FIRST25 pour −25% sur la première commande) |
| **Clients** | Liste des comptes clients et leur historique |
| **Paramètres** | Réglages globaux, mode "Bientôt disponible", équipe admin |

---

## 3. Ajouter un nouveau produit

1. Aller dans **Produits** dans le menu.
2. Cliquer sur le bouton **+ Nouveau produit** en haut à droite.
3. Remplir les champs :
   - **Nom du produit** (ex. *Huile d'argan pure 100 ml*)
   - **Slug** (généré automatiquement à partir du nom — ne pas modifier sauf cas particulier)
   - **SKU** (référence interne, optionnel)
   - **Catégorie** : choisir dans la liste déroulante
   - **Description courte** : 1–2 phrases pour la fiche produit
   - **Description longue** : détails complets, usage, origine
   - **Prix** en dirhams (ex. `89`)
   - **Prix barré** (optionnel) : si en promotion, mettre l'ancien prix
   - **Stock** : nombre d'unités disponibles
   - **Statut** : `active` pour publier · `draft` pour brouillon · `archived` pour masquer
   - **En vedette** (coche) : si activé, le produit apparaît sur la page d'accueil
4. **Galerie produit** : glisser-déposer les photos. La première photo devient l'image principale.
5. Cliquer **Enregistrer**.

Le produit apparaît immédiatement sur la boutique : https://greenamal.com/boutique

---

## 4. Modifier un produit existant (prix, description, stock…)

1. **Produits** → trouver le produit dans la liste (utiliser la recherche).
2. Cliquer sur le **nom du produit** ou l'icône crayon ✏️ pour ouvrir la fiche.
3. Modifier ce qui doit l'être (prix, description, stock, photos, statut…).
4. Cliquer **Enregistrer**.

> Pour **changer le prix** uniquement : modifier le champ "Prix", puis Enregistrer.

> Pour **mettre un produit hors stock** sans le supprimer : passer le champ "Stock" à `0`, ou changer le statut à `archived`.

> Pour **mettre un produit en avant sur la page d'accueil** : cocher la case
> "En vedette". Maximum 4 produits sont affichés en page d'accueil
> (les plus vendus parmi ceux en vedette).

---

## 5. Gérer les commandes

1. **Commandes** dans le menu → liste de toutes les commandes triées par date.
2. Cliquer sur un numéro de commande (ex. `GA-2026-0001`) pour voir le détail :
   - Informations client (nom, email, téléphone, adresse)
   - Articles commandés et quantités
   - Total, frais de livraison, réduction
3. **Changer le statut** d'une commande dans la liste déroulante :
   - **En attente** : commande reçue, paiement à la livraison
   - **En préparation** : commande en cours de préparation
   - **Expédiée** : colis remis au transporteur
   - **Livrée** : reçue par le client
   - **Annulée** : annulation
4. À chaque changement de statut, **un email automatique est envoyé au client**.

> **Astuce** : marquer la commande "En préparation" dès qu'elle est traitée,
> puis "Expédiée" quand le transporteur prend le colis. Cela rassure le client.

---

## 6. Gérer les catégories

1. **Catégories** dans le menu.
2. Pour **ajouter** une catégorie : cliquer **+ Nouvelle catégorie**.
3. Remplir : nom, description, image (image carrée 800×800 recommandée), ordre d'affichage.
4. **Modifier** : cliquer sur le nom de la catégorie existante.

> L'ordre d'affichage détermine la position sur la page d'accueil et dans
> les menus. Plus le chiffre est petit, plus la catégorie apparaît tôt.

---

## 7. Gérer les coupons et promotions

1. **Coupons** dans le menu → liste des codes existants.
2. **+ Nouveau coupon** pour en créer un. Remplir :
   - **Code** : ce que le client tapera au panier (ex. `RAMADAN20`)
   - **Type de remise** : pourcentage (`%`) ou montant fixe en DH
   - **Valeur** : ex. `20` pour 20% ou 50 DH
   - **Cible** : tous les produits, une catégorie précise, ou un produit
   - **Conditions** : montant minimum du panier, limite par client
   - **Période** : dates de début et de fin
   - **Actif** : oui/non
3. **Enregistrer**.

> **Coupon FIRST25** : déjà en place, donne −25% sur la première commande
> via un compte client. Ne pas le supprimer.

---

## 8. Changer son mot de passe administrateur

1. **Paramètres** dans le menu.
2. Faire défiler jusqu'à la section **Sécurité · mot de passe administrateur**.
3. Saisir :
   - Mot de passe actuel
   - Nouveau mot de passe (minimum 8 caractères)
   - Confirmation
4. Cliquer **Modifier le mot de passe**.

Un message vert ✓ confirme le changement.

---

## 9. Mode "Bientôt disponible"

Si vous devez mettre le site en pause (rupture de stock complète,
vacances, refonte), vous pouvez activer le mode **Bientôt disponible** :

1. **Paramètres** → section **Affichage & démo**.
2. Activer le bouton **Mode "Bientôt disponible"** → Enregistrer.

Les visiteurs voient alors une page d'attente au lieu de la boutique.
L'espace admin reste accessible normalement.

Pour désactiver : repasser le bouton sur "Désactivé" → Enregistrer.

---

## 10. Ajouter ou modifier l'image de la page d'accueil

1. **Paramètres** → section **Image du hero (page d'accueil)**.
2. Glisser-déposer la nouvelle image (paysage, 1600×900 ou plus).
3. Enregistrer.

L'image apparaît immédiatement en haut de la page d'accueil.

---

## 11. Liens importants

| À quoi ça sert | Adresse |
|---|---|
| Site public (vu par les clients) | https://greenamal.com |
| Espace admin | https://greenamal.com/admin/login |
| Boutique publique | https://greenamal.com/boutique |
| Toutes les catégories | https://greenamal.com/categories |
| Page contact | https://greenamal.com/contact |
| Mot de passe oublié (client) | https://greenamal.com/mot-de-passe-oublie |
| Création de compte client | https://greenamal.com/inscription |

---

## 12. Problèmes fréquents

**Je ne peux pas me connecter à l'admin.**
- Vérifier l'email et le mot de passe (attention aux majuscules).
- Cliquer sur "Mot de passe oublié" sur la page de connexion (réservé aux clients · pour l'admin, contacter le technicien).

**Une commande n'arrive pas dans mes emails.**
- Vérifier le dossier spam / courrier indésirable.
- Les emails sont envoyés depuis `noreply@greenamal.com`.

**Un produit ne s'affiche pas sur le site malgré l'enregistrement.**
- Vérifier que le statut est bien `active` (et pas `draft`).
- Vérifier que le stock est supérieur à `0`.
- Vider le cache du navigateur (Ctrl+Shift+R / Cmd+Shift+R).

**Les photos d'un produit sont pixellisées ou tordues.**
- Utiliser des photos carrées 1200×1200 ou plus, format JPG ou WebP.
- Le site les redimensionne automatiquement pour les versions mobile.

**Le prix s'affiche avec un chiffre bizarre au lieu de "DH".**
- Contacter le technicien — c'est un réglage à corriger dans le fichier de configuration.

---

## 13. Sécurité — bonnes pratiques

- **Ne jamais partager** son mot de passe administrateur.
- **Le changer** tous les 3–6 mois.
- **Se déconnecter** après chaque session sur un ordinateur partagé
  (bouton "Déconnexion" en haut à droite).
- En cas de doute (email étrange, comportement anormal du site),
  contacter immédiatement le technicien.

---

## Contact technique

Pour toute question technique ou problème que ce guide ne couvre pas,
contacter le technicien responsable du site.
