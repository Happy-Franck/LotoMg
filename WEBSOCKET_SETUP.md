# Configuration WebSocket avec Laravel Reverb

## Démarrage du système

Pour utiliser le chat en temps réel, vous devez démarrer 3 services :

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

## Alternative : Utiliser un seul terminal

Vous pouvez aussi utiliser `screen` ou `tmux` pour gérer plusieurs processus, ou simplement ouvrir 3 terminaux différents.

## Test du système

1. Créez un compte utilisateur
2. Créez un salon
3. Ouvrez le salon dans deux navigateurs différents (ou deux fenêtres en navigation privée)
4. Connectez-vous avec deux utilisateurs différents
5. Rejoignez le même salon avec les deux utilisateurs
6. Envoyez des messages - ils devraient apparaître en temps réel !

## Configuration

Les paramètres WebSocket sont dans le fichier `.env` :
- `REVERB_HOST=localhost`
- `REVERB_PORT=8080`
- `BROADCAST_CONNECTION=reverb`
- `QUEUE_CONNECTION=database`

## Fonctionnalités

- ✅ Messages en temps réel
- ✅ Authentification des channels (seuls les membres du salon peuvent voir les messages)
- ✅ Interface chat moderne avec Tailwind CSS
- ✅ Affichage de l'auteur et de l'heure des messages
- ✅ Auto-scroll vers les nouveaux messages
