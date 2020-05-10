!/bin/bash

# ------------ 
# Create symlink for all system and start docker 
#
# This program will install 
# Then the programm will download docker-compose.yml from GIT
# and frenchRenamer system service
#
# Cloud -> /srv/dev-disk-by-label-Nas/6 - Nextcloud/
# Config -> /srv/dev-disk-by-label-Nas/99-Config/
# Downloads -> /srv/dev-disk-by-label-Nas/4 - Downloads/
# Films -> /srv/dev-disk-by-label-Nas/1 - Medias/B - Videos/Films/
# FolderWatch -> /home/Downloads/FolderWatch/
# Multimedia -> /srv/dev-disk-by-label-Nas/1 - Medias/
# Series -> /srv/dev-disk-by-label-Nas/1 - Medias/B - Videos/Series/
# tempMovies -> Downloads/tempMovies/
# Torrents -> /home/Downloads/Torrents/

ln -s "/srv/dev-disk-by-label-Nas/6 - Nextcloud/" /home/Cloud
ln -s "/srv/dev-disk-by-label-Nas/99-Config/" /home/Config
ln -s "/srv/dev-disk-by-label-Nas/4 - Downloads/" /home/Downloads
ln -s "/srv/dev-disk-by-label-Nas/1 - Medias/B - Videos/Films/" /home/Films
ln -s "/home/Downloads/FolderWatch/" /home/FolderWatch"
ln -s "/srv/dev-disk-by-label-Nas/1 - Medias/" /home/Multimedia
ln -s "/srv/dev-disk-by-label-Nas/1 - Medias/B - Videos/Series/" /home/Series
ln -s "/home/Downloads/tempMovies/" /home/tempMovies
ln -s "/home/Downloads/Torrents/" /home/Torrents

cd /home/Config/docker
docker-compose up -d
