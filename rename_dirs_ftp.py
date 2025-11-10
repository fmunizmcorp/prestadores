import ftplib

ftp = ftplib.FTP('ftp.clinfec.com.br')
ftp.login('u673902663.genspark1', 'Genspark1@')

print("Renomeando diretórios no servidor...")

# Renomear controllers → Controllers
try:
    ftp.cwd('/src')
    ftp.rename('controllers', 'Controllers')
    print("✓ controllers → Controllers")
except Exception as e:
    print(f"controllers: {e}")

# Renomear models → Models
try:
    ftp.cwd('/src')
    ftp.rename('models', 'Models')
    print("✓ models → Models")
except Exception as e:
    print(f"models: {e}")

# Renomear helpers → Helpers
try:
    ftp.cwd('/src')
    ftp.rename('helpers', 'Helpers')
    print("✓ helpers → Helpers")
except Exception as e:
    print(f"helpers: {e}")

# Renomear views → Views
try:
    ftp.cwd('/src')
    ftp.rename('views', 'Views')
    print("✓ views → Views")
except Exception as e:
    print(f"views: {e}")

ftp.quit()
print("\n✓ Renomeação concluída!")
