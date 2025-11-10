import ftplib
ftp = ftplib.FTP('ftp.clinfec.com.br')
ftp.login('u673902663.genspark1', 'Genspark1@')
with open('test_error.php', 'rb') as f:
    ftp.storbinary('STOR /test_error.php', f)
print("✓ test_error.php uploaded")
ftp.quit()
