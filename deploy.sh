#!/bin/bash
START_DIR="$(pwd)"
SOURCE="$(dirname "$(realpath "$0")")"
USERNAME=$(cat student_username.txt | head -1)
DESTINATION="$USERNAME@student.math.hr:~/public_html/rp2/"
STATUS_FILE="$SOURCE/git_status.txt" 

RED='\033[0;31m'
GREEN='\033[0;32m'
NC='\033[0m'

cd $SOURCE
echo -e "$(git rev-parse HEAD)\n$(git status --short)" > $STATUS_FILE
echo "git_status.txt isprintan!"
cd $START_DIR


echo "$SOURCE --> $DESTINATION"

rsync -ar "$@" -e 'ssh -o HostKeyAlgorithms=ssh-rsa' --chmod=Du=rwx,Dg=rx,Do=rx,Fu=rw,Fg=r,Fo=r --exclude '*.git/*' $SOURCE $DESTINATION

if [ $? -eq 0 ]; then
	echo -e "${GREEN}USPJEŠAN DEPLOY! :)${NC}"
else
	echo -e "${RED}DEPLOY FAILED, code: $?${NC}"
fi

rm $STATUS_FILE
