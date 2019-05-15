#!/bin/bash

######################################
# senTorrent.sh
# Utilité: Liste les torrents a envoyer a Alldebrid et à récupérer les torrents terminés
# Usage: bash /root/senTorrent.sh
# Auteur: BFAM
# Version : 1.0.1
# Mise à jour le: 21/03/2017
######################################


######################################
#
# Configuration :
# 
######################################
DATADIR="/home/Downloads/Torrents"

# Creation d'un fichier de tracabilité pour savoir si le script tourne
touch "torrentEnCours.txt"

# Renommage des dossiers / fichiers pour éviter les espaces qui pourraient faire planter la suite
IFS=$(echo -en "\n\b")
for y in $(ls $DATADIR/); do
        mv $DATADIR/`echo $y | sed 's/ /\\ /g'` $DATADIR/`echo "$y" | sed 's/ /_/g'`
done
clear

echo -e "Demarrage de la detection de torrent \n"

echo "debut recuperation des torrents finis"
php /home/Config/ScriptHome/sendTorrent.php
rm "torrentEnCours.txt"


