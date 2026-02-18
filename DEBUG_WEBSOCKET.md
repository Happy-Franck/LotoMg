# Debug WebSocket - Checklist

## 1. Vérifier que Reverb tourne
```bash
php artisan reverb:start
```
Tu devrais voir : `INFO  Starting server on 0.0.0.0:8080 (localhost).`

## 2. Vérifier la console JavaScript (F12)

Ouvre la console dans les deux navigateurs. Tu devrais voir :
```
Connecting to salon channel: 1
✅ Successfully subscribed to salon channel
```

Si tu vois une erreur, note-la.

## 3. Tester manuellement le broadcast

Dans un navigateur, va sur :
```
http://localhost:8000/test-broadcast/1
```
(remplace 1 par l'ID de ton salon)

Dans l'autre navigateur, tu devrais voir dans la console :
```
👤 UserJoinedSalon event received: {user: {...}, participants_count: X}
```

## 4. Vérifier les logs Laravel

```bash
tail -f storage/logs/laravel.log
```

Quand tu rejoins un salon, tu devrais voir :
```
User joined salon
Channel authorization
Broadcast sent for UserJoinedSalon
```

## 5. Vérifier l'autorisation du channel

Dans la console JavaScript, tape :
```javascript
Echo.connector.pusher.connection.state
```

Tu devrais voir : `"connected"`

## 6. Problèmes courants

### Le broadcast ne passe pas
- ✅ Reverb est démarré ?
- ✅ Les assets sont compilés ? (`npm run build`)
- ✅ Le cache est vidé ? (`php artisan config:clear`)
- ✅ L'utilisateur est bien dans le salon ?

### L'événement n'arrive pas
- Vérifie que tu es bien connecté au channel (console JS)
- Vérifie que l'autorisation du channel passe (logs Laravel)
- Vérifie que Reverb reçoit bien l'événement (terminal Reverb)

### L'événement arrive mais la liste ne se met pas à jour
- Vérifie que la fonction `addParticipant()` est bien appelée (console JS)
- Vérifie que l'élément `participants-list` existe dans le DOM
- Vérifie qu'il n'y a pas d'erreur JavaScript

## 7. Test rapide

Ouvre deux navigateurs côte à côte :
1. Navigateur A : Connecté au salon
2. Navigateur B : Connecté au salon
3. Dans les deux : Ouvre la console (F12)
4. Dans Navigateur A : Va sur `/test-broadcast/1`
5. Dans Navigateur B : Tu devrais voir l'événement dans la console

Si ça marche, le problème est dans le code du join.
Si ça ne marche pas, le problème est dans la configuration WebSocket.
