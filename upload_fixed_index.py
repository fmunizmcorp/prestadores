import ftplib
ftp = ftplib.FTP('ftp.clinfec.com.br')
ftp.login('u673902663.genspark1', 'Genspark1@')
with open('public/index.php', 'rb') as f:
    ftp.storbinary('STOR /public/index.php', f)
print("✓ public/index.php uploaded")
ftp.quit()
