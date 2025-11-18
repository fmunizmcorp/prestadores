#!/usr/bin/env python3
"""Verificar conteúdo da página inicial"""

import requests

response = requests.get("http://clinfec.com.br/prestadores/", timeout=10)
print("=" * 80)
print("CONTEÚDO DA PÁGINA INICIAL")
print("=" * 80)
print(response.text)
print()
print("=" * 80)
print(f"Status: {response.status_code}")
print(f"Tamanho: {len(response.text)} caracteres")
print("=" * 80)
