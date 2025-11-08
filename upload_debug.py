import ftplib
ftp = ftplib.FTP('ftp.clinfec.com.br')
ftp.login('u673902663.genspark1', 'Genspark1@')
with open('debug.php', 'rb') as f:
    ftp.storbinary('STOR /debug.php', f)
print("✓ debug.php uploaded")
ftp.quit()
