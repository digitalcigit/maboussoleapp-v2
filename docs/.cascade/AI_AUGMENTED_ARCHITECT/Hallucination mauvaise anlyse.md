Me

Nous avions bien recu les accès

Ah d'accord ! Donc le problème n'était pas lié aux identifiants.

Quand vous essayez de vous connecter, est-ce que vous obtenez un message d'erreur spécifique ? Ou est-ce que quelque chose d'autre ne fonctionne pas ?

Je vois dans l'image que vous aviez un message "Tentatives de connexion trop nombreuses. Veuillez essayer de nouveau dans 35 secondes." Est-ce que c'était le problème principal ?

Me

User uploaded image 1
Ah, je vois l'erreur ! Vous essayez de vous connecter avec "commercial@digital.ci" mais dans le champ vous avez tapé "commercial@digitalci" (sans le point entre "digital" et "ci").

L'email correct est : commercial@digital.ci

C'est pour ça que vous obtenez le message "Ces identifiants ne correspondent pas à nos enregistrements." - il y a une différence dans l'email (le point manquant).

Essayez de vous connecter avec :

Email : commercial@digital.ci (avec le point)
Mot de passe : password123
Me

Voici le mail que j'utilise "commercial@digital.ci" c'est le bon.

Je m'excuse pour la confusion. Vérifions le processus d'authentification lui-même. Regardons le code qui gère la connexion :

Searched in
app/Http
3 results
Searched in
app/Livewire
0 results
Searched in
app/Filament/Pages
0 results
Analyzed
config
Analyzed

auth.php:(1-51)
Ah, je vois ! Il y a un problème dans la configuration de l'authentification. Le guard 'portail_candidat' utilise un driver 'portail_candidat' qui n'est probablement pas défini correctement.

Vérifions le provider d'authentification :

Analyzed

filament-portail-candidat.php:(1-51)
Je vois le problème ! Le portail candidat est configuré pour utiliser le guard 'web' mais dans config/auth.php nous avons défini un guard spécial 'portail_candidat'.

Modifions la configuration auth.php pour utiliser le bon driver :

Edited
auth.php
+1
-1
 119
Open diff
Maintenant que j'ai corrigé la configuration d'authentification, il faut redémarrer le serveur pour que les changements prennent effet. Je vais le faire :

Suggested background terminal command
~/CascadeProjects/maboussoleapp-v2
$ php artisan optimize:clear && php artisan serve

Do you want to run this command?