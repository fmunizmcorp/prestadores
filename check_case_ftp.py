import ftplib
ftp = ftplib.FTP('ftp.clinfec.com.br')
ftp.login('u673902663.genspark1', 'Genspark1@')
ftp.cwd('/src')
print("Conteúdo de /src:")
ftp.retrlines('LIST', print)
ftp.quit()
