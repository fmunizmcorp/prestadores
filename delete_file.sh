#!/bin/bash
ftp -n ftp.clinfec.com.br << FTPEOF
user u673902663.genspark1 Genspark1@
delete cadastroinicial.php
bye
FTPEOF
