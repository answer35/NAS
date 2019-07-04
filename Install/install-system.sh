#!/bin/bash

# ------------ 
# Install base system programs and dependencies
#
# This program will install 
#   - update distrib upgrade
#   - OMV
#   - docker io
#   - docker compose
#   - PHP
#   - git
#
# Then the programm will download docker-compose.yml from GIT
# and frenchRenamer system service
#
# after downloading, please use OMV to create nas drive and mount it
# then create symlinks:
# Cloud -> /srv/dev-disk-by-label-Nas/6 - Nextcloud/
# Config -> /srv/dev-disk-by-label-Nas/99-Config/
# Downloads -> /srv/dev-disk-by-label-Nas/4 - Downloads/
# Films -> /srv/dev-disk-by-label-Nas/1 - Medias/B - Videos/Films/
# FolderWatch -> /home/Downloads/FolderWatch/
# Multimedia -> /srv/dev-disk-by-label-Nas/1 - Medias/
# Series -> /srv/dev-disk-by-label-Nas/1 - Medias/B - Videos/Series/
# tempMovies -> Downloads/tempMovies/
# Torrents -> /home/Downloads/Torrents/


#------------------
#Update system
apt update
apt upgrade --yes
apt update
apt dist-upgrade
apt install --yes apt-transport-https

#add OMV repository
cat <<EOF >> /etc/apt/sources.list.d/openmediavault.list
deb https://packages.openmediavault.org/public arrakis main
# deb https://downloads.sourceforge.net/project/openmediavault/packages arrakis main
## Uncomment the following line to add software from the proposed repository.
# deb https://packages.openmediavault.org/public arrakis-proposed main
# deb https://downloads.sourceforge.net/project/openmediavault/packages arrakis-proposed main
## This software is not part of OpenMediaVault, but is offered by third-party
## developers as a service to OpenMediaVault users.
# deb https://packages.openmediavault.org/public arrakis partner
# deb https://downloads.sourceforge.net/project/openmediavault/packages arrakis partner
EOF

apt install --yes dirmngr
apt-key adv --keyserver keys.gnupg.net --recv-key 24863F0C716B980B


#Install OMV
apt update
apt --allow-unauthenticated install openmediavault-keyring
apt update
apt --yes --auto-remove --show-upgraded \
    --allow-downgrades --allow-change-held-packages \
    --no-install-recommends \
    --option Dpkg::Options::="--force-confdef" \
    --option DPkg::Options::="--force-confold" \
    install postfix openmediavault
apt install --yes openmediavault

# Initialize the system and database.
omv-initsystem

#Install docker IO
apt install --yes apt-transport-https ca-certificates curl gnupg2 software-properties-common
curl -fsSL https://download.docker.com/linux/$(. /etc/os-release; echo "$ID")/gpg | apt-key add -
add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/$(. /etc/os-release; echo "$ID") $(lsb_release -cs) stable"
apt update
apt install --yes docker-ce
systemctl enable docker

#Install docker-compose
curl -L "https://github.com/docker/compose/releases/download/1.24.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
chmod +x /usr/local/bin/docker-compose

#Install php
apt install --yes php php-cli php-common php-curl php-mbstring php-mysql php-xml

#Install git
apt install --yes git

#debug plex container transcode issue
sed -i 's/<opts>defaults,nofail,user_xattr,noexec/<opts>defaults,nofail,user_xattr/g' /etc/openmediavault/config.xml
omv-mkconfb fstab
reboot

