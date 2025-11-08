# Sprint 7 - Financial Module Views Documentation

## Complete View Inventory (27 views total)

### Dashboard & Main (1 view)
- ✅ **index.php** - Main financial dashboard with indicators, charts, alerts

### Categorias Financeiras (3 views)
- ⏳ **categorias/index.php** - Hierarchical category tree listing
- ⏳ **categorias/create.php** - Create new financial category
- ⏳ **categorias/edit.php** - Edit existing category

### Contas a Pagar (4 views)
- ⏳ **contas_pagar/index.php** - List all accounts payable
- ⏳ **contas_pagar/create.php** - Create new account payable (with installments)
- ⏳ **contas_pagar/show.php** - View account details and payment history
- ⏳ **contas_pagar/pagar.php** - Payment form (modal/inline)

### Contas a Receber (4 views)
- ⏳ **contas_receber/index.php** - List all accounts receivable
- ⏳ **contas_receber/create.php** - Create new account receivable (with boleto option)
- ⏳ **contas_receber/show.php** - View account details and receipt history
- ⏳ **contas_receber/receber.php** - Receipt form (modal/inline)

### Boletos Bancários (3 views)
- ⏳ **boletos/index.php** - List all bank slips
- ⏳ **boletos/show.php** - View boleto for printing (DANFE-like layout)
- ⏳ **boletos/imprimir.php** - Print-optimized boleto view (no layout)

### Notas Fiscais (5 views)
- ⏳ **notas_fiscais/index.php** - List all electronic invoices
- ⏳ **notas_fiscais/create.php** - Create new invoice (NF-e/NFS-e)
- ⏳ **notas_fiscais/edit.php** - Edit draft invoice
- ⏳ **notas_fiscais/show.php** - View invoice details with DANFE
- ⏳ **notas_fiscais/emitir.php** - Emission confirmation modal

### Lançamentos Financeiros (2 views)
- ⏳ **lancamentos/index.php** - List all financial entries (double-entry)
- ⏳ **lancamentos/create.php** - Create manual entry

### Conciliação Bancária (3 views)
- ⏳ **conciliacoes/index.php** - List all bank reconciliations
- ⏳ **conciliacoes/importar.php** - Import OFX file
- ⏳ **conciliacoes/show.php** - View reconciliation with matching

### Fluxo de Caixa (1 view)
- ⏳ **fluxo_caixa/index.php** - Complete cash flow with projections

### Relatórios (2 views)
- ⏳ **relatorios/dre.php** - Income Statement (DRE)
- ⏳ **relatorios/balancete.php** - Trial Balance

## Implementation Priority

### Phase 1 (Critical - Completed)
✅ Dashboard (index.php)

### Phase 2 (High Priority - Next)
1. contas_pagar/index.php
2. contas_pagar/create.php
3. contas_receber/index.php
4. contas_receber/create.php
5. boletos/show.php (for printing)

### Phase 3 (Medium Priority)
6. contas_pagar/show.php
7. contas_receber/show.php
8. lancamentos/index.php
9. fluxo_caixa/index.php
10. notas_fiscais/index.php

### Phase 4 (Low Priority - Can use modals)
11. Payment/receipt forms (can be modals in show views)
12. Edit forms (similar to create)
13. Specialized reports

## View Features

### Common Features Across All Views
- Bootstrap 5 responsive layout
- Header/Footer includes
- CSRF token protection
- Flash messages (success/error)
- Breadcrumbs navigation
- Filter/search functionality
- Pagination where applicable
- DataTables integration for large lists
- Chart.js for visualizations
- Print-friendly versions where needed
- Export options (PDF, Excel) where applicable

### Specific View Technologies
- **Forms**: Bootstrap validation, Select2 for dropdowns, DatePicker for dates
- **Tables**: DataTables with server-side processing for large datasets
- **Charts**: Chart.js for line, bar, pie charts
- **PDFs**: DomPDF or TCPDF for PDF generation (boletos, notas fiscais)
- **Modals**: Bootstrap modals for quick actions
- **Ajax**: For dynamic updates without page reload

## File Structure
```
src/views/financeiro/
├── index.php (Dashboard) ✅
├── categorias/
│   ├── index.php
│   ├── create.php
│   └── edit.php
├── contas_pagar/
│   ├── index.php
│   ├── create.php
│   ├── show.php
│   └── pagar.php
├── contas_receber/
│   ├── index.php
│   ├── create.php
│   ├── show.php
│   └── receber.php
├── boletos/
│   ├── index.php
│   ├── show.php
│   └── imprimir.php
├── notas_fiscais/
│   ├── index.php
│   ├── create.php
│   ├── edit.php
│   ├── show.php
│   └── emitir.php
├── lancamentos/
│   ├── index.php
│   └── create.php
├── conciliacoes/
│   ├── index.php
│   ├── importar.php
│   └── show.php
├── fluxo_caixa/
│   └── index.php
└── relatorios/
    ├── dre.php
    └── balancete.php
```

## Status: 1/27 views completed (4%)
Next: Implement Phase 2 views (contas_pagar, contas_receber, boletos)
