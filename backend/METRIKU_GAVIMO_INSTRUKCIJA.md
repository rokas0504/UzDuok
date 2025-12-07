# Metrikų Gavimo ir Dokumentavimo Instrukcija

**Projektas:** UzDuok
**Data:** 2025-12-07

---

## 📋 Turinys

1. [Backend Metrikų Gavimas](#backend-metrikų-gavimas)
2. [Frontend Metrikų Gavimas](#frontend-metrikų-gavimas)
3. [Screenshot'ų Darymas](#screenshotų-darymas)
4. [Kur Rasti Rezultatus](#kur-rasti-rezultatus)

---

## 🔧 Backend Metrikų Gavimas

### 1. PHPStan - Static Analysis

**Komanda:**
```bash
cd backend
./vendor/bin/phpstan analyse --memory-limit=512M
```

**Rezultatai:**
- **Konsole**: Parodo klaidų skaičių ir detales
- **Failas**: `phpstan-initial.txt` (jau išsaugotas)

**Išsaugoti rezultatus:**
```bash
./vendor/bin/phpstan analyse --memory-limit=512M 2>&1 | tee phpstan-initial.txt
```

**Ką dokumentuoti:**
- Klaidų skaičius (58 errors)
- Pagrindinių klaidų tipai
- Screenshot konsolės išvesties

**Screenshot vieta:**
- Terminalo output su error summary

---

### 2. PHPMD - Code Smells Detection

**Komanda:**
```bash
cd backend
./vendor/bin/phpmd app text phpmd.xml
```

**Rezultatai:**
- **Konsole**: Parodo code smells su failų lokacijomis
- **Failas**: `phpmd-initial.txt` (jau išsaugotas)

**Išsaugoti rezultatus:**
```bash
./vendor/bin/phpmd app text phpmd.xml 2>&1 | tee phpmd-initial.txt
```

**Ką dokumentuoti:**
- Code smells skaičius (7)
- Kiekvieno smell'o tipas ir lokacija
- Screenshot konsolės su results

**Pavyzdys:**
```
/path/to/file.php:88  ShortMethodName  Avoid using short method names like me()
```

---

### 3. PHPMetrics - Complexity & Quality Metrics

**Komanda (Console Output):**
```bash
cd backend
./vendor/bin/phpmetrics --report-cli app
```

**Rezultatai:**
- **Konsole**: Summary metrikų
- **Failas**: `phpmetrics-initial.txt` (jau išsaugotas)

**Komanda (HTML Report):**
```bash
./vendor/bin/phpmetrics --report-html=phpmetrics-report app
```

**HTML Reporto Peržiūra:**
```bash
# macOS
open phpmetrics-report/index.html

# Linux
xdg-open phpmetrics-report/index.html

# Windows
start phpmetrics-report/index.html
```

**HTML Report Struktūra:**
- `index.html` - Pagrindinis puslapis su overview
- `complexity.html` - Cyclomatic complexity grafikai
- `coupling.html` - Coupling Between Objects analizė
- `loc.html` - Lines of Code statistika
- `oop.html` - OOP metrikos
- `all.html` - Visos klasės lentelėje

**Ką dokumentuoti iš HTML reporto:**

1. **Index.html (Dashboard):**
   - Screenshot pagrindinio dashboard
   - LOC, Classes, Methods skaičius
   - Average Cyclomatic Complexity
   - Average Coupling

2. **Complexity.html:**
   - Screenshot Cyclomatic Complexity grafiko
   - Klasių su aukščiausiu CC sąrašas
   - Metodų su aukščiausiu CC

3. **Coupling.html:**
   - Screenshot Coupling grafiko
   - Afferent/Efferent coupling reikšmės
   - Instability metrika

4. **All.html:**
   - Screenshot klasių lentelės
   - Filtruoti pagal CC > 3
   - Rodyti LOC, WMC, CBO stulpelius

**Raktinės Metrikos:**

| Metrika | Reikšmė | Vieta HTML |
|---------|---------|------------|
| Lines of Code | 1329 | index.html → LOC section |
| Logical LOC | 792 | index.html → LOC section |
| Avg Cyclomatic Complexity | 2.28 | complexity.html → Average |
| Avg Efferent Coupling | 3.31 | coupling.html → Metrics |
| Avg Afferent Coupling | 1.38 | coupling.html → Metrics |
| LCOM | 2.41 | oop.html → Cohesion |
| Classes | 29 | index.html → OOP |
| Methods | 119 | index.html → OOP |

**JSON Export (papildoma):**
```bash
./vendor/bin/phpmetrics --report-json=phpmetrics-data.json app
```

---

### 4. PHPUnit - Code Coverage

**Paleisti testus su coverage:**
```bash
cd backend
./vendor/bin/phpunit --coverage-html coverage --coverage-text
```

**HTML Coverage Report:**
```bash
open coverage/index.html
```

**Ką dokumentuoti:**
- Overall coverage %
- Lines covered / total
- Screenshot coverage dashboard
- Failus su <80% coverage

---

## 🎨 Frontend Metrikų Gavimas

### 1. ESLint - Code Quality & Style

**Komanda:**
```bash
cd frontend
npx eslint . --ext .vue,.ts,.js
```

**Išsaugoti rezultatus:**
```bash
npx eslint . --ext .vue,.ts,.js 2>&1 | tee eslint-initial.txt
```

**Rezultatai:**
- **Konsole**: Errors ir warnings su lokacijomis
- **Failas**: `eslint-initial.txt` (jau išsaugotas)

**Suskaičiuoti errors/warnings:**
```bash
# Errors
cat eslint-initial.txt | grep "error" | wc -l
# Output: 142

# Warnings
cat eslint-initial.txt | grep "warning" | wc -l
# Output: 199
```

**Ką dokumentuoti:**
- Total problems (341)
- Errors (142)
- Warnings (199)
- Autofixable problems (111 errors + 148 warnings)
- Top 5 dažniausių problemų

**Grupuoti pagal tipą:**
```bash
# Top error types
cat eslint-initial.txt | grep "error" | awk '{print $4}' | sort | uniq -c | sort -rn | head -10
```

**Screenshot'ai:**
- Konsolės output su summary
- Pavyzdžiai konkrečių klaidų (su line numbers)

---

### 2. TypeScript Type Checking

**Komanda:**
```bash
cd frontend
npx vue-tsc --noEmit
```

**Ką dokumentuoti:**
- Type errors skaičius
- Pagrindinių type problemų pavyzdžiai

---

### 3. Complexity Metrics (papildoma - jei reikia)

**Įdiegti complexity-report:**
```bash
npm install --save-dev complexity-report
```

**Paleisti:**
```bash
npx cr composables/*.ts --format json > complexity-report.json
```

---

## 📸 Screenshot'ų Darymas

### macOS:

**Visas ekranas:**
- `Cmd + Shift + 3` - Išsaugo į Desktop

**Pasirinkta sritis:**
- `Cmd + Shift + 4` - Pasirinkti sritį
- `Cmd + Shift + 4 + Space` - Visa lango nuotrauka

**Su įrankiu (rekomenduojama):**
- `Cmd + Shift + 5` - Screenshot toolbar
  - Galima pasirinkti langą
  - Pridėti cursor'ių
  - Pridėti annotations

### Windows:

**Snipping Tool:**
- `Win + Shift + S` - Atidaro snipping tool
- Pasirinkti sritį
- Screenshot'as nukopijuojamas į clipboard

**Print Screen:**
- `Print Screen` - Visas ekranas
- `Alt + Print Screen` - Aktyvus langas

### Linux:

**Gnome Screenshot:**
```bash
gnome-screenshot -a  # Area selection
gnome-screenshot -w  # Window
```

**Flameshot (rekomenduojama):**
```bash
sudo apt install flameshot
flameshot gui
```

---

## 📂 Kur Rasti Rezultatus

### Backend Failų Medis:

```
backend/
├── phpstan.neon                    # PHPStan konfigūracija
├── phpmd.xml                       # PHPMD konfigūracija
├── phpstan-initial.txt             # ✅ PHPStan rezultatai (PRIEŠ)
├── phpmd-initial.txt               # ✅ PHPMD rezultatai (PRIEŠ)
├── phpmetrics-initial.txt          # ✅ PHPMetrics summary (PRIEŠ)
├── phpmetrics-data.json            # ✅ PHPMetrics JSON (PRIEŠ)
├── phpmetrics-report/              # ✅ HTML Reportas (PRIEŠ)
│   ├── index.html                  # Dashboard
│   ├── complexity.html             # Complexity analizė
│   ├── coupling.html               # Coupling analizė
│   ├── loc.html                    # LOC statistika
│   ├── oop.html                    # OOP metrikos
│   └── all.html                    # Visos klasės
├── coverage/                       # Code coverage HTML (po testų)
│   └── index.html
├── KODO_KOKYBES_ANALIZE.md        # ✅ Pagrindinis dokumentas
└── METRIKU_GAVIMO_INSTRUKCIJA.md  # ✅ Ši instrukcija
```

### Frontend Failų Medis:

```
frontend/
├── eslint.config.mjs               # ESLint konfigūracija
├── eslint-initial.txt              # ✅ ESLint rezultatai (PRIEŠ)
└── complexity-report.json          # (optional) Complexity data
```

---

## 📊 Kaip Užpildyti Dokumentą Metrikomis

### 1. Iš PHPMetrics HTML (`phpmetrics-report/index.html`):

**Atidaryti:**
```bash
cd backend
open phpmetrics-report/index.html
```

**Screenshot'ai reikalingi:**

1. **Dashboard (index.html):**
   - Viršutinė dalis su overview metrics
   - LOC sekcija
   - OOP sekcija
   - Complexity summary

2. **Complexity Page:**
   - Cyclomatic complexity grafikas
   - Klasių lentelė su CC reikšmėmis
   - Filter: CC > 3

3. **Coupling Page:**
   - Coupling grafikas
   - Afferent/Efferent coupling lentelė
   - Instability diagram

4. **All Classes (all.html):**
   - Visa lentelė su stulpeliais:
     - Class name
     - LOC
     - LLOC
     - CC (Cyclomatic Complexity)
     - WMC (Weighted Method Count)
     - CBO (Coupling Between Objects)
   - Rikiuoti pagal CC descending

### 2. Iš Konsolės Output:

**PHPStan klaidos:**
```bash
cat phpstan-initial.txt | grep "Line" | head -20
```
Screenshot konsolės su klaidų sąrašu.

**PHPMD code smells:**
```bash
cat phpmd-initial.txt | grep "app/"
```
Screenshot visų 7 code smells.

**PHPMetrics summary:**
```bash
cat phpmetrics-initial.txt
```
Screenshot summary lentelės.

### 3. Iš ESLint Output:

**Frontend klaidos:**
```bash
cd ../frontend
cat eslint-initial.txt | tail -50
```
Screenshot summary: "✖ 341 problems (142 errors, 199 warnings)"

**Top problemos:**
```bash
# Gauti top 10 dažniausių error tipų
cat eslint-initial.txt | grep "error" | awk '{print $4}' | sort | uniq -c | sort -rn | head -10
```

---

## 🎯 Checklist Dokumentavimui

### Backend:

- [ ] PHPStan: Screenshot errors summary (58 errors)
- [ ] PHPMD: Screenshot 7 code smells
- [ ] PHPMetrics: Screenshot dashboard (index.html)
- [ ] PHPMetrics: Screenshot complexity page
- [ ] PHPMetrics: Screenshot coupling page
- [ ] PHPMetrics: Screenshot all classes table (filtered CC > 3)
- [ ] Lentelė su pagrindinėmis metrikomis (LOC, CC, CBO, etc.)

### Frontend:

- [ ] ESLint: Screenshot summary (341 problems)
- [ ] ESLint: Screenshot pavyzdžių konkrečių klaidų
- [ ] Top 5 error types lentelė
- [ ] Top 5 warning types lentelė

### Bendras:

- [ ] Visos metrikos įkeltos į KODO_KOKYBES_ANALIZE.md
- [ ] Screenshot failai pavadinti aiškiai:
  - `01-phpmetrics-dashboard.png`
  - `02-phpmetrics-complexity.png`
  - `03-phpmetrics-coupling.png`
  - `04-phpstan-errors.png`
  - `05-phpmd-smells.png`
  - `06-eslint-summary.png`
  - etc.

---

## 📌 Greitosios Komandos

### Visos Backend Metrikos:

```bash
cd backend

# 1. PHPStan
./vendor/bin/phpstan analyse --memory-limit=512M 2>&1 | tee phpstan-initial.txt

# 2. PHPMD
./vendor/bin/phpmd app text phpmd.xml 2>&1 | tee phpmd-initial.txt

# 3. PHPMetrics Console
./vendor/bin/phpmetrics --report-cli app 2>&1 | tee phpmetrics-initial.txt

# 4. PHPMetrics HTML
./vendor/bin/phpmetrics --report-html=phpmetrics-report app

# 5. Atidaryti HTML reportą
open phpmetrics-report/index.html
```

### Visos Frontend Metrikos:

```bash
cd ../frontend

# 1. ESLint
npx eslint . --ext .vue,.ts,.js 2>&1 | tee eslint-initial.txt

# 2. Suskaičiuoti
echo "Errors: $(cat eslint-initial.txt | grep error | wc -l)"
echo "Warnings: $(cat eslint-initial.txt | grep warning | wc -l)"
```

---

## 💡 Patarimai

1. **Screenshot'us darykite PRIEŠ pradedant pertvarkymą** - reikės palyginimui!

2. **Saugokite visus .txt failus** - lengviau kopijuoti metrikas į dokumentą

3. **HTML reportai yra interaktyvūs** - galite filtruoti, rikiuoti, zoom'inti grafikus

4. **Kopijuokite konkrečias metrikas** iš HTML lentelių - galite tiesiog Select + Copy

5. **Naudokite browser DevTools** screenshot'ams:
   - Chrome: `Cmd+Shift+P` → "Screenshot" → "Capture node screenshot"
   - Leidžia screenshot'inti konkretų HTML elementą

6. **Screenshot failų pavadinimai:**
   - Naudokite numeracijas: `01-`, `02-`, etc.
   - Aiškus aprašymas: `phpmetrics-complexity-graph.png`
   - Ne: `Screen Shot 2025-12-07 at 10.30.45.png`

---

## ❓ Dažnai Užduodami Klausimai

**Q: Kur matau konkrečios klasės Cyclomatic Complexity?**
A: `phpmetrics-report/all.html` → rasti klasę lentelėje → stulpelis "CC"

**Q: Kaip gauti JSON formatą metrikų?**
A: `./vendor/bin/phpmetrics --report-json=output.json app`

**Q: Kaip filtruoti tik aukšto CC klases?**
A: HTML reporto `all.html` puslapyje galima spausti stulpelio antraštę "CC" - rikiuos descending

**Q: ESLint sako "potentially fixable" - kaip auto-fix?**
A: `npx eslint . --ext .vue,.ts,.js --fix` (DĖMESIO: darykite backup!)

**Q: Kaip gauti tik code smells skaičių?**
A: `./vendor/bin/phpmd app text phpmd.xml | grep "app/" | wc -l`

---

**Sėkmės dokumentuojant metrikas! 🚀**