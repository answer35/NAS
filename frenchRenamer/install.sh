#!/bin/bash
######################################
# senTorrent.sh
# Utilité: Liste les torrents a envoyer a Alldebrid et à récupérer les torre# Usage: bash /root/senTorrent.sh
# Auteur: BFAM
# Version : 1.0.1
# Mise à jour le: 21/03/2017
######################################
echo "Installation de jdk11"
sudo apt update
apt install --yes dirmngr
sudo apt install --yes default-jdk
echo 'deb http://ftp.debian.org/debian stretch-backports main' | sudo tee /etc/apt/sources.list.d/stretch-backports.list
sudo apt update
sudo apt install --yes openjdk-11-jdk
apt install libjna-java libjna-jni

echo "Installation de filebot"
wget -O filebot.deb 'https://app.filebot.net/download.php?type=deb&arch=amd64'
sudo dpkg -i filebot.deb
apt install -f 
filebot --license FileBot_License_P7949491.psm
echo "Copie des fichiers d'installation du deamon"
cp etc/init.d/frenchRenamer /etc/init.d/frenchRenamer
cp usr/bin/frenchRenamer /usr/bin/frenchRenamer
echo "Droit d'execution sur les fichiers"
chmod +x /etc/init.d/frenchRenamer
chmod +x /usr/bin/frenchRenamer
echo "inscription en tant que service"
sudo update-rc.d frenchRenamer defaults
echo "fin"
