# Configuration WebSocket - Jeu de Loto en Temps Réel

## Démarrage du système

Pour utiliser le jeu de loto en temps réel, vous devez démarrer 4 services :

### 1. Serveur Laravel
```bash
php artisan serve
```

### 2. Serveur Reverb (WebSocket)
```bash
php artisan reverb:start
```

### 3. Queue Worker (pour broadcaster les événements)
```bash
php artisan queue:work
```

### 4. Scheduler (pour le tirage automatique)
```bash
php artisan schedule:work
```

## Comment jouer

1. **Créer un compte** - Inscrivez-vous sur la plateforme
2. **Créer ou rejoindre un salon** - Allez dans "Salons" et créez un nouveau salon ou rejoignez-en un existant
3. **Démarrer une partie** - Une fois dans le salon, cliquez sur "Démarrer une partie"
4. **Sélectionner un ticket** - Vous avez 15 secondes pour choisir parmi 4 tickets générés automatiquement
   - Si vous ne sélectionnez pas, un ticket sera choisi aléatoirement pour vous
5. **Attendre les autres joueurs** - Une fois votre ticket sélectionné, attendez que tous les joueurs sélectionnent le leur
6. **Tirage automatique** - Le tirage commence automatiquement dès que tous les joueurs ont sélectionné
   - Les numéros sont tirés toutes les 300ms
   - Vous voyez votre ticket et ceux de vos adversaires
   - Les numéros tirés sont surlignés en temps réel sur tous les tickets
7. **Gagner** - Le premier joueur à compléter une ligne horizontale gagne !

## Fonctionnalités

- ✅ Génération automatique de 4 tickets de loto par joueur
- ✅ Timer de 15 secondes pour la sélection
- ✅ Sélection automatique si timeout
- ✅ Affichage de tous les tickets des joueurs pendant le tirage
- ✅ Tirage automatique en temps réel synchronisé
- ✅ Surlignage des numéros tirés sur tous les tickets
- ✅ Détection automatique du gagnant (première ligne complète)
- ✅ Interface moderne avec animations
- ✅ WebSocket pour synchronisation temps réel
- ✅ Notifications en temps réel des joueurs qui rejoignent/quittent

## Structure du ticket

Chaque ticket de loto contient :
- 3 lignes x 10 colonnes
- 15 numéros (5 par ligne)
- Numéros de 1 à 99
- Maximum 3 numéros par colonne

## Configuration

Les paramètres WebSocket sont dans le fichier `.env` :
- `REVERB_HOST=localhost`
- `REVERB_PORT=8080`
- `BROADCAST_CONNECTION=reverb`
- `QUEUE_CONNECTION=database`
