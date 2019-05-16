#!/bin/bash
######################################
# senTorrent.sh
# Utilité: Liste les torrents a envoyer a Alldebrid et à récupérer les torre# Usage: bash /root/senTorrent.sh
# Auteur: BFAM
# Version : 1.0.1
# Mise à jour le: 21/03/2017
######################################
echo "Installation de jdk8"
echo "deb http://ppa.launchpad.net/webupd8team/java/ubuntu xenial main" | tee /etc/apt/sources.list.d/webupd8team-java.list
echo "deb-src http://ppa.launchpad.net/webupd8team/java/ubuntu xenial main" | tee -a /etc/apt/sources.list.d/webupd8team-java.list
apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys EEA14886
apt-get update
apt-get install oracle-java8-installer
echo "Installation de filebot"
wget -O filebot.deb 'https://app.filebot.net/download.php?type=deb&arch=amd64'
sudo dpkg -i filebot.deb
echo "Copie des fichiers d'installation du deamon"
cp etc/init.d/frenchRenamer /etc/init.d/frenchRenamer
cp usr/bin/frenchRenamer /usr/bin/frenchRenamer
echo "Droit d'execution sur les fichiers"
chmod +x /etc/init.d/frenchRenamer
chmod +x /usr/bin/frenchRenamer
echo "inscription en tant que service"
sudo update-rc.d frenchRenamer defaults
echo "fin"
