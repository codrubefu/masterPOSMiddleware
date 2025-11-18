# Trzddercf Table Schema

Database table: `trzddercf`

## Field Definitions

| Field Name | Data Type | Status | Description |
|------------|-----------|--------|-------------|
| `modp` | char(10) | ✓ Checked | mod plata, avem ppRON (plata mixta), numRON (plata cu numerar), ccRON (plata cu cardul) |
| `nrtrzcc` | char(20) | ✓ Checked | NULL |
| `tipcc` | char(30) | ✓ Checked | NULL |
| `tipv` | char(3) | ✓ Checked | RON |
| `data` | datetime | ✓ Checked | ziua curenta |
| `compid` | char(20) | ✓ Checked | ? |
| `nrbonfint` | numeric(18, 0) | ✗ Unchecked | **Primary Key** |
| `nrbonspec` | char(14) | ✓ Checked | NULL |
| `costtot` | numeric(11, 2) | ✓ Checked | NULL |
| `chit` | bit | ✓ Checked | False |
| `idtrzcf` | numeric(18, 0) | ✓ Checked | nrbonfint din tabela trzcf dupa ce se face inchiderea de zi |
| `casa` | int | ✓ Checked | idcasa din tabela gestcasa |
| `nrdispliv` | numeric(18, 0) | ✓ Checked | 0 |
| `idlogin` | numeric(18, 0) | ✓ Checked | 0 |
| `userlogin` | nvarchar(50) | ✓ Checked | spatiu gol |
| `numerar` | numeric(11, 2) | ✓ Checked | cand plata este Mixta, se trece valoarea incasata cu numerar, daca nu este Mixta se trece 0.00 |
| `card` | numeric(11, 2) | ✓ Checked | cand plata este Mixta, se trece valoarea incasata cu cardul, daca nu este Mixta se trece 0.00 |
| `nrnp` | numeric(18, 0) | ✓ Checked | NULL |
| `datac` | datetime | ✓ Checked | getdate |
| `tichete` | numeric(11, 2) | ✓ Checked | 0.00 |
| `totalron` | computed | ✓ Checked | Camp calculat: CONVERT([numeric](11,2),[stotalron]-[redabs],(0)) |
| `cuibf` | nvarchar(20) | ✓ Checked | 0 |
| `idrapz` | int | ✓ Checked | 0 |
| `anulat` | bit | ✓ Checked | default False |

## Payment Methods (modp)

- `ppRON` - Plata mixta (mixed payment)
- `numRON` - Plata cu numerar (cash payment)
- `ccRON` - Plata cu cardul (card payment)

## Special Notes

- **Primary Key**: `nrbonfint` (currently Unchecked)
- **Computed Field**: `totalron` = `stotalron` - `redabs`
- **Mixed Payments**: When `modp` = 'ppRON', both `numerar` and `card` fields contain values; otherwise, they are 0.00
- **Foreign Keys**:
  - `idtrzcf` → `trzcf.nrbonfint` (after end-of-day closure)
  - `casa` → `gestcasa.idcasa`

## Default Values

- `nrdispliv`: 0
- `idlogin`: 0
- `numerar`: 0.00 (unless mixed payment)
- `card`: 0.00 (unless mixed payment)
- `tichete`: 0.00
- `cuibf`: 0
- `idrapz`: 0
- `anulat`: False
- `chit`: False

---

*Last updated: November 18, 2025*
