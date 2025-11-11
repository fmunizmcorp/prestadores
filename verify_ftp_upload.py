#!/usr/bin/env python3
"""
Verify FTP upload - check file size and timestamp
"""
import ftplib

FTP_SERVER = "ftp.clinfec.com.br"
FTP_USER = "u673902663.genspark1"
FTP_PASS = "Genspark1@"

ftp = ftplib.FTP(FTP_SERVER)
ftp.login(FTP_USER, FTP_PASS)

print("Connected to FTP\n")

# Check file in the path we uploaded to
path = "../domains/clinfec.com.br/public_html/prestadores/check_notas_fiscais_table.php"

print(f"Checking: {path}\n")

try:
    # Get file info
    ftp.voidcmd(f'TYPE I')
    size = ftp.size(path)
    print(f"✅ File exists")
    print(f"Size: {size:,} bytes")
    print(f"Expected: ~8,590 bytes (new) or ~7,868 bytes (old)\n")
    
    if size > 8500:
        print("✅ File appears to be UPDATED (new version)")
    elif size < 8000:
        print("❌ File appears to be OLD version")
    else:
        print("⚠️  Size unclear, checking content...")
    
    # Try to get modification time
    try:
        mdtm = ftp.sendcmd(f'MDTM {path}')
        print(f"Modified: {mdtm}")
    except:
        pass
        
except Exception as e:
    print(f"❌ Error: {e}")

# Also check root
print("\n" + "="*60)
print("Files in FTP root:")
print("="*60 + "\n")

files = []
ftp.dir(files.append)

for line in files:
    if 'check_notas' in line or 'deployer' in line:
        print(line)

ftp.quit()
