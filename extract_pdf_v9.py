#!/usr/bin/env python3
"""
Extract text from V9 test report PDFs
"""
import sys
from PyPDF2 import PdfReader

def extract_pdf_text(pdf_path, output_path):
    """Extract all text from PDF and save to text file"""
    try:
        reader = PdfReader(pdf_path)
        text = []
        
        print(f"📄 Extracting from: {pdf_path}")
        print(f"   Pages: {len(reader.pages)}")
        
        for i, page in enumerate(reader.pages, 1):
            print(f"   Processing page {i}...")
            page_text = page.extract_text()
            text.append(f"\n{'='*80}\n")
            text.append(f"PAGE {i}\n")
            text.append(f"{'='*80}\n")
            text.append(page_text)
        
        full_text = ''.join(text)
        
        with open(output_path, 'w', encoding='utf-8') as f:
            f.write(full_text)
        
        print(f"✅ Extracted {len(full_text)} characters to: {output_path}")
        return True
        
    except Exception as e:
        print(f"❌ Error: {e}")
        return False

if __name__ == "__main__":
    if len(sys.argv) != 3:
        print("Usage: python3 extract_pdf_v9.py <input.pdf> <output.txt>")
        sys.exit(1)
    
    pdf_path = sys.argv[1]
    output_path = sys.argv[2]
    
    success = extract_pdf_text(pdf_path, output_path)
    sys.exit(0 if success else 1)
