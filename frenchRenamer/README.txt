# Copier le dossier frenchRenamer dans /root
# Vous placer dans le dossier :
cd /root/frenchRenamer

# modifier le script pour ajuster vos chemins :
nano usr/bin/frenchRenamer

# modifier les lignes suivantes avec vos dossiers
# Global declare
tempFolder='/home/tempMovies'
movieFolder='/home/Films'
export HOME=/home/benoit
methode="move" 

# La methode peut etre move | copy | keeplink | symlink | hardlink | test

# lancer l'installation des softs :
bash install.sh

# vérifier le lancement du deamon :
service frenchRenamer status

# Si la commade retourne active : c'est bon ! Reboot
sudo reboot

# apres le redemarage revérifier que le deamon s'est lancé
service frenchRenamer status

# Utilisation du deamon (en cas de modification du script, ou de plantage du deamon):
service frenchRenamer start | restart | stop

# Changer les répertoires de couchpotato pour qu'il renomme les fichiers dans votre "tempFolder" et non plus dans le "movieFolder" et enjoy !