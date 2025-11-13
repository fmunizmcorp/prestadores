#!/bin/bash
FTP_HOST="ftp.clinfec.com.br"
FTP_USER="u673902663.genspark1"
FTP_PASS="Genspark1@"
LOCAL_FILE="diagnostic_1762907942.php"

ftp -inv $FTP_HOST << EOF
user $FTP_USER $FTP_PASS
binary
put $LOCAL_FILE
bye
EOF
