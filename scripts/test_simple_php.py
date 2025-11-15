#!/usr/bin/env python3
"""Testar PHP simples"""
import ftplib
from io import BytesIO

# Upload de arquivo de teste simples
ftp = ftplib.FTP('ftp.clinfec.com.br')
ftp.set_pasv(True)
ftp.login('u673902663.genspark1', 'Genspark1@')
ftp.cwd('/public_html/prestadores')

# Criar arquivo de teste super simples
test_php = b"""<?php
phpinfo();
"""

ftp.storbinary('STOR test_phpinfo.php', BytesIO(test_php))
print("✅ Arquivo test_phpinfo.php enviado")
print("🌐 Teste em: https://prestadores.clinfec.com.br/test_phpinfo.php")

# Criar outro teste ainda mais simples
test_simple = b"""<?php
echo "PHP FUNCIONA!";
echo "<br>Versao: " . PHP_VERSION;
echo "<br>Diretorio: " . __DIR__;
"""

ftp.storbinary('STOR test_simple.php', BytesIO(test_simple))
print("✅ Arquivo test_simple.php enviado")
print("🌐 Teste em: https://prestadores.clinfec.com.br/test_simple.php")

ftp.quit()
