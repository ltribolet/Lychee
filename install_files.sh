#!/bin/sh
NO_COLOR="\033[0m"
GREEN="\033[38;5;010m"
YELLOW="\033[38;5;011m"
ORANGE="\033[38;5;214m"

echo "\n${YELLOW}creating file for css personalization${NO_COLOR}"
echo "touch public/dist/user.css"
touch public/dist/user.css

echo "\n${YELLOW}creating default sqlite database${NO_COLOR}"
echo "touch database/database.sqlite"
touch database/database.sqlite

echo "\n${YELLOW}setting up hooks for git pull and git commits${NO_COLOR}"
echo "cp pre-commit .git/hooks/"
cp pre-commit .git/hooks/
