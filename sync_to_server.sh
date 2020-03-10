#!/bin/bash
# Rätt användare???
while true
do 
  rsync -avz --exclude '.*' ./ app-beta_speleo_se-wordpress@basecamp.tvartom.com:/opt/speleo/beta_speleo_se-wordpress/wordpress/wp-content/plugins/speleo-se-grottan
  inotifywait -r ./
done