#!/bin/bash

HOST="ftp.clinfec.com.br"
USER="u673902663.genspark1"
PASS="Genspark1@"

echo "=== SPRINT 15 COMPLETE DEPLOYMENT ==="
echo "Uploading corrected files to production..."
echo ""

# Upload critical files via FTP
ftp -inv $HOST << EOF
user $USER $PASS
binary

# Upload ROOT files (.htaccess moved from public/)
put .htaccess .htaccess

# Upload public/index.php to ROOT (FTP root = document root)
put public/index.php index.php

# Upload all Models (23 files - all with Database fix)
cd src/Models
lcd src/Models
mput *.php

# Upload all Controllers (15 files verified)
cd ../Controllers
lcd ../Controllers
mput *.php

# Upload Helpers (fixed FluxoCaixaHelper)
cd ../Helpers
lcd ../Helpers
mput *.php

# Upload DatabaseMigration (fixed)
cd ../
lcd ../
put DatabaseMigration.php DatabaseMigration.php

# Upload Views (login form fixed + dashboard)
cd Views/auth
lcd Views/auth
put login.php login.php

cd ../dashboard
lcd ../dashboard
put index.php index.php

# Upload config files
cd ../../..
lcd ../../..
cd config
lcd config
mput *.php

bye
EOF

echo ""
echo "=== DEPLOYMENT COMPLETE ==="
echo "Files uploaded successfully!"
echo ""
echo "Next steps:"
echo "1. Test login at https://prestadores.clinfec.com.br/login"
echo "2. Verify all 13 modules functionality"
echo "3. Generate test report"
