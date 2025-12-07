# 📊 Kodo Kokybės Metrikų Analizė - Greitasis Startas

**Projektas:** UzDuok
**Data:** 2025-12-07
**Statusas:** ✅ Pradinė analizė ATLIKTA

---

## 🚀 Greitas Startas

### 1️⃣ Peržiūrėti HTML Reportus

```bash
cd backend

# Atidaryti PHPMetrics dashboard
open phpmetrics-report/index.html

# Atidaryti complexity analizę
open phpmetrics-report/complexity.html

# Atidaryti coupling analizę
open phpmetrics-report/coupling.html

# Atidaryti visų klasių lentelę
open phpmetrics-report/all.html
```

### 2️⃣ Peržiūrėti Konsolės Rezultatus

```bash
# PHPStan klaidos
cat phpstan-initial.txt | head -50

# PHPMD code smells
cat phpmd-initial.txt

# PHPMetrics summary
cat phpmetrics-initial.txt

# Visos metrikos (summary)
cat METRIKOS_SUMMARY.txt
```

### 3️⃣ Frontend Rezultatai

```bash
cd ../frontend

# ESLint summary
cat eslint-initial.txt | tail -10

# Errors count
cat eslint-initial.txt | grep "error" | wc -l
# Output: 142

# Warnings count
cat eslint-initial.txt | grep "warning" | wc -l
# Output: 199
```

---

## 📁 Dokumentacijos Failai

| Failas | Aprašymas | Dydis |
|--------|-----------|-------|
| **KODO_KOKYBES_ANALIZE.md** | 📋 Pagrindinis dokumentas su visa analize | 9.4 KB |
| **METRIKOS_SUMMARY.txt** | 📊 Visos metrikos vienoje vietoje | 19 KB |
| **METRIKU_GAVIMO_INSTRUKCIJA.md** | 📖 Kaip gauti metrikas ir jas dokumentuoti | 12 KB |
| **SCREENSHOT_GIDAS.md** | 📸 Detalus gidas screenshot'ams daryti | 11 KB |

---

## 📈 Pagrindinės Metrikos (Suvestinė)

### Backend (PHP/Laravel):

| Metrika | Reikšmė | Šaltinis |
|---------|---------|----------|
| **Lines of Code** | 1329 | PHPMetrics |
| **Logical LOC** | 792 | PHPMetrics |
| **Klasių skaičius** | 29 | PHPMetrics |
| **Metodų skaičius** | 119 | PHPMetrics |
| **Avg Cyclomatic Complexity** | 2.28 | PHPMetrics |
| **Avg Efferent Coupling** | 3.31 | PHPMetrics |
| **LCOM (Cohesion)** | 2.41 | PHPMetrics |
| **PHPStan Errors** | 58 | PHPStan |
| **Code Smells** | 7 | PHPMD |
| **Code Coverage** | 0% | PHPUnit |

### Frontend (Vue.js/TypeScript):

| Metrika | Reikšmė | Šaltinis |
|---------|---------|----------|
| **ESLint Errors** | 142 | ESLint |
| **ESLint Warnings** | 199 | ESLint |
| **Total Problems** | 341 | ESLint |
| **Autofixable** | 259 | ESLint |

---

## 🎯 Identifikuotos Problemos

### Backend - Top 3 Metodai Pertvarkymui:

1. **TaskService::updateStatus()** (lines 132-155)
   - ⚠️ CC ≈ 4
   - ⚠️ Unused variable: `$oldStatus`
   - ⚠️ Pažeidžia SRP

2. **PointService::purchaseShopItem()** (lines 81-123)
   - ⚠️ Long method (43 linijos)
   - ⚠️ Multiple responsibilities
   - ⚠️ Missing imports

3. **TaskService::createPeriodicTasks()** (lines 64-100)
   - ⚠️ CC ≈ 5
   - ⚠️ Nested loops
   - ⚠️ Long method (37 linijos)

### Frontend - Top 2 Failai Pertvarkymui:

1. **useAuth.ts** (114 linijos)
   - ⚠️ 6x `any` tipai
   - ⚠️ 2x useless try-catch
   - ⚠️ Unused variable

2. **TaskModal.vue** (704 linijos)
   - ⚠️ 6x `any` tipai
   - ⚠️ 44 errors, 165 warnings
   - ⚠️ Per didelis komponentas

---

## 🛠️ PHPMD Code Smells (7)

1. ❌ `AuthController::me()` - ShortMethodName
2. ❌ `Controller::ok()` - ShortMethodName
3. ❌ `Repository` - TooManyPublicMethods (11 > 10)
4. ❌ `PointService.php:87` - MissingImport
5. ❌ `PointService.php:91` - MissingImport
6. ❌ `TaskService.php:134` - UnusedLocalVariable
7. ❌ `UserService.php:77` - UnusedFormalParameter

---

## 📸 Kaip Daryti Screenshot'us

### Reikalingi Screenshot'ai (12):

**Backend (8):**
1. PHPMetrics Dashboard (HTML)
2. PHPMetrics Complexity (HTML)
3. PHPMetrics Coupling (HTML)
4. PHPMetrics All Classes (HTML)
5. PHPStan Errors (Console)
6. PHPMD Code Smells (Console)
7. PHPMetrics Console Summary (Console)
8. Code Coverage (jei yra)

**Frontend (4):**
9. ESLint Summary (Console)
10. ESLint Error Examples (Console)
11. ESLint Top Errors (Console)
12. Frontend Problematic Files (Console)

**Detalūs nurodymai:** Žiūrėti `SCREENSHOT_GIDAS.md`

### Greitosios Komandos Screenshot'ams:

```bash
# macOS - pasirinkta sritis
Cmd + Shift + 4

# macOS - visas langas
Cmd + Shift + 4 + Space

# macOS - screenshot toolbar (rekomenduojama)
Cmd + Shift + 5
```

---

## 🔍 Kaip Surasti Konkrečias Metrikas

### HTML Reportuose:

| Ieškote | Vieta |
|---------|-------|
| LOC | `index.html` → LOC card |
| Cyclomatic Complexity | `complexity.html` → Summary |
| Coupling | `coupling.html` → Metrics table |
| Klasių sąrašas | `all.html` → Table |
| LCOM | `oop.html` → Cohesion |
| Violations | `index.html` → Violations card |

### Konsolės Failuose:

| Ieškote | Failas | Komanda |
|---------|--------|---------|
| PHPStan klaidos | phpstan-initial.txt | `cat phpstan-initial.txt \| head -50` |
| Code smells | phpmd-initial.txt | `cat phpmd-initial.txt` |
| Metrics summary | phpmetrics-initial.txt | `cat phpmetrics-initial.txt` |
| ESLint summary | frontend/eslint-initial.txt | `cd ../frontend && cat eslint-initial.txt \| tail -5` |

---

## 📂 Failų Struktūra

```
backend/
├── 📋 KODO_KOKYBES_ANALIZE.md        ← Pagrindinis dokumentas
├── 📊 METRIKOS_SUMMARY.txt            ← Visos metrikos
├── 📖 METRIKU_GAVIMO_INSTRUKCIJA.md  ← Instrukcija
├── 📸 SCREENSHOT_GIDAS.md             ← Screenshot'ų gidas
├── 🚀 README_METRIKOS.md              ← ŠIS failas (greitas startas)
│
├── 📁 phpmetrics-report/              ← HTML reportai
│   ├── index.html                     (Dashboard)
│   ├── complexity.html                (Complexity)
│   ├── coupling.html                  (Coupling)
│   └── all.html                       (All classes)
│
├── 📄 phpstan-initial.txt             ← PHPStan rezultatai
├── 📄 phpmd-initial.txt               ← PHPMD rezultatai
└── 📄 phpmetrics-initial.txt          ← PHPMetrics summary

frontend/
└── 📄 eslint-initial.txt              ← ESLint rezultatai
```

---

## ✅ Checklist - Kas Jau Padaryta

- [x] Įdiegti kodo analizės įrankius (PHPStan, PHPMD, PHPMetrics, ESLint)
- [x] Paleisti pradinę analizę
- [x] Sugeneruoti HTML reportus
- [x] Išsaugoti visus rezultatus į .txt failus
- [x] Identifikuoti probleminius metodus/klases
- [x] Suskaičiuoti code smells (7 backend)
- [x] Suskaičiuoti ESLint problems (341 frontend)
- [x] Sukurti dokumentaciją
- [ ] Padaryti screenshot'us įrodymams
- [ ] Sukurti vienetų testus
- [ ] Atlikti kodo pertvarkymą
- [ ] Pakartotinai išmatuoti metrikas
- [ ] Parašyti išvadas

---

## 🎯 Kiti Žingsniai

### 1. Dokumentavimas (DABAR):

1. Atidaryti `SCREENSHOT_GIDAS.md`
2. Padaryti 12 screenshot'ų pagal instrukciją
3. Išsaugoti į `screenshots/` katalogą
4. Įkelti į Word dokumentą

### 2. Testų Rašymas:

1. Peržiūrėti `tests/` katalogą
2. Sukurti testus `TaskService::updateStatus()`
3. Sukurti testus `PointService::purchaseShopItem()`
4. Pasiekti 80-100% coverage

### 3. Refactoring:

1. Pertvarkyti `TaskService::updateStatus()`
2. Pertvarkyti `PointService::purchaseShopItem()`
3. Pataisyti 8 code smells
4. Pertvarkyti `useAuth.ts` (frontend)

### 4. Pakartotinė Analizė:

1. Paleisti visas analizes iš naujo
2. Palyginti metrikus PRIEŠ vs PO
3. Parašyti išvadas

---

## 💡 Naudingos Komandos

### Visų Reportų Atidarymas:

```bash
cd backend

# Atidaryti visus HTML reportus
open phpmetrics-report/*.html

# Arba po vieną
open phpmetrics-report/index.html
open phpmetrics-report/complexity.html
open phpmetrics-report/coupling.html
open phpmetrics-report/all.html
```

### Metrikų Peržiūra:

```bash
# Visa backend informacija
cat METRIKOS_SUMMARY.txt

# Tik code smells
cat phpmd-initial.txt | grep "app/"

# Tik PHPStan summary
cat phpstan-initial.txt | tail -20

# Frontend summary
cd ../frontend && cat eslint-initial.txt | tail -5
```

### Statistika:

```bash
# Backend LOC
find app -name "*.php" -exec wc -l {} + | tail -1

# Frontend LOC
cd ../frontend
find . -name "*.vue" -o -name "*.ts" | grep -v node_modules | xargs wc -l | tail -1

# ESLint errors count
cd ../frontend
cat eslint-initial.txt | grep " error " | wc -l
```

---

## 📞 Pagalba

**Jei kyla klausimų:**

1. **Kaip gauti metrikas?** → Žiūrėti `METRIKU_GAVIMO_INSTRUKCIJA.md`
2. **Kaip daryti screenshot'us?** → Žiūrėti `SCREENSHOT_GIDAS.md`
3. **Kur rasti konkrečią metriką?** → Žiūrėti `METRIKOS_SUMMARY.txt`
4. **Visa analizė?** → Žiūrėti `KODO_KOKYBES_ANALIZE.md`

---

## 🎓 Terminų Žodynas

| Terminas | Reikšmė |
|----------|---------|
| **LOC** | Lines of Code - kodo eilučių skaičius |
| **LLOC** | Logical Lines of Code - loginių eilučių skaičius |
| **CC** | Cyclomatic Complexity - ciklomatinis sudėtingumas |
| **CBO** | Coupling Between Objects - priklausomybės tarp objektų |
| **WMC** | Weighted Method Count - svertinis metodų skaičius |
| **LCOM** | Lack of Cohesion of Methods - metodų susietumo trūkumas |
| **DIT** | Depth of Inheritance Tree - paveldėjimo gylis |
| **SRP** | Single Responsibility Principle - vienos atsakomybės principas |
| **Code Smell** | Blogos praktikos, dizaino problemos kode |

---

**Sėkmės su užduotimi! 🚀📊**

Jei reikia pagalbos, žiūrėkite kitus dokumentus arba klauskite!