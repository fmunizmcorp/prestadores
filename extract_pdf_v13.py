#!/usr/bin/env python3
"""
Extract full text from V13 test reports
"""
import sys
from pypdf import PdfReader

def extract_pdf_text(pdf_path):
    """Extract all text from PDF"""
    try:
        reader = PdfReader(pdf_path)
        text = ""
        for page_num, page in enumerate(reader.pages, 1):
            text += f"\n{'='*80}\n"
            text += f"PÁGINA {page_num}\n"
            text += f"{'='*80}\n"
            text += page.extract_text()
        return text
    except Exception as e:
        return f"ERROR: {e}"

if __name__ == "__main__":
    print("\n" + "="*80)
    print("RELATÓRIO DE TESTES V13 - COMPLETO")
    print("="*80)
    text1 = extract_pdf_text("RELATORIO_TESTES_V13.pdf")
    print(text1)
    
    print("\n\n" + "="*80)
    print("SUMÁRIO EXECUTIVO V13 - COMPLETO")
    print("="*80)
    text2 = extract_pdf_text("SUMARIO_EXECUTIVO_V13.pdf")
    print(text2)
