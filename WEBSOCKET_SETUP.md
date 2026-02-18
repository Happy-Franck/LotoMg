# Configuration WebSocket - Jeu de Loto en Temps Réel

## Démarrage du système

Pour utiliser le jeu de loto en temps réel, vous devez démarrer 3 services :

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

## Comment jouer

1. **Créer un compte** - Inscrivez-vous sur la plateforme
2. **Créer ou rejoindre un salon** - Allez dans "Salons" et créez un nouveau salon ou rejoignez-en un existant
3. **Démarrer une partie** - Une fois dans le salon, cliquez sur "Démarrer une partie"
4. **Sélectionner un ticket** - Vous avez 15 secondes pour choisir parmi 4 tickets générés automatiquement
   - Si vous ne sélectionnez pas, un ticket sera choisi aléatoirement pour vous
5. **Tirage automatique** - Une fois tous les joueurs prêts, le tirage commence automatiquement
6. **Gagner** - Le premier joueur à compléter une ligne horizontale gagne !

## Fonctionnalités

- ✅ Génération automatique de 4 tickets de loto par joueur
- ✅ Timer de 15 secondes pour la sélection
- ✅ Sélection automatique si timeout
- ✅ Tirage en temps réel synchronisé pour tous les joueurs
- ✅ Détection automatique du gagnant
- ✅ Interface moderne avec animations
- ✅ WebSocket pour synchronisation temps réel

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
