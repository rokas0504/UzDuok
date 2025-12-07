# 📸 Screenshot'ų Gidas - Kodo Kokybės Metrikos

**Projektas:** UzDuok
**Paskirtis:** Word dokumentui įkelti įrodymus

---

## 🎯 Reikalingi Screenshot'ai

### ✅ Backend (PHP) - 8 screenshot'ai

#### 1. PHPMetrics Dashboard
**Failas:** `phpmetrics-report/index.html`
**Kaip atidaryti:**
```bash
cd backend
open phpmetrics-report/index.html
```

**Ką fotografuoti:**
- [ ] Viršutinė dashboard dalis su 4 metric cards:
  - LOC (Lines of Code)
  - Complexity
  - Coupling
  - OOP Stats
- [ ] Apačioje matomas "Violations" Summary

**Metrikos kurias turi matyti:**
- Lines of code: **1329**
- Logical LOC: **792**
- Classes: **29**
- Methods: **119**
- Avg CC: **2.28**
- Avg Efferent Coupling: **3.31**

**Pavadinti failą:** `01-phpmetrics-dashboard.png`

---

#### 2. PHPMetrics Complexity Page
**Failas:** `phpmetrics-report/complexity.html`
**Kaip atidaryti:**
```bash
open phpmetrics-report/complexity.html
```

**Ką fotografuoti:**
- [ ] Cyclomatic Complexity grafikas (viršuje)
- [ ] Lentelė su klasėmis ir jų CC reikšmėmis

**Kaip filtruoti aukšto CC klases:**
1. Atsidaryti `complexity.html`
2. Lentelėje spausti "Cyclomatic complexity" stulpelio antraštę
3. Rikiuosis descending (nuo didžiausio)
4. Screenshot top 10 klasių

**Metrikos kurias turi matyti:**
- Average Cyclomatic Complexity: **2.28**
- Max CC klasės (jei yra > 5)

**Pavadinti failą:** `02-phpmetrics-complexity.png`

---

#### 3. PHPMetrics Coupling Page
**Failas:** `phpmetrics-report/coupling.html`
**Kaip atidaryti:**
```bash
open phpmetrics-report/coupling.html
```

**Ką fotografuoti:**
- [ ] Coupling metrics summary (viršuje)
- [ ] Afferent vs Efferent Coupling grafikas
- [ ] Instability metrika

**Metrikos kurias turi matyti:**
- Avg Afferent Coupling: **1.38**
- Avg Efferent Coupling: **3.31**
- Avg Instability: **0.69**

**Pavadinti failą:** `03-phpmetrics-coupling.png`

---

#### 4. PHPMetrics All Classes Table
**Failas:** `phpmetrics-report/all.html`
**Kaip atidaryti:**
```bash
open phpmetrics-report/all.html
```

**Ką fotografuoti:**
- [ ] Lentelė su VISOMIS klasėmis
- [ ] Stulpeliai: Class Name, LOC, LLOC, CC, WMC, CBO
- [ ] Filtruoti CC > 3 (jei yra)

**Kaip filtruoti:**
1. Atsidaryti `all.html`
2. Lentelėje spausti "CC" stulpelio antraštę
3. Rikiuosis descending
4. Screenshot įdomiausiųausių klasių (arba viską)

**Stulpelių reikšmės:**
- **LOC** - Lines of Code
- **LLOC** - Logical LOC
- **CC** - Cyclomatic Complexity
- **WMC** - Weighted Method Count
- **CBO** - Coupling Between Objects

**Pavadinti failą:** `04-phpmetrics-all-classes.png`

---

#### 5. PHPStan Errors (Konsolė)
**Kaip gauti:**
```bash
cd backend
cat phpstan-initial.txt | head -100
```

**Arba paleisti iš naujo:**
```bash
./vendor/bin/phpstan analyse --memory-limit=512M
```

**Ką fotografuoti:**
- [ ] Konsolės output su klaidų sąrašu
- [ ] Pirmų 20-30 klaidų pavyzdžiai
- [ ] Apačioje Summary: "Found X errors"

**Metrika kurią turi matyti:**
- Total errors: **58**

**Pavadinti failą:** `05-phpstan-errors.png`

---

#### 6. PHPMD Code Smells (Konsolė)
**Kaip gauti:**
```bash
cd backend
cat phpmd-initial.txt | grep "app/"
```

**Arba paleisti iš naujo:**
```bash
./vendor/bin/phpmd app text phpmd.xml
```

**Ką fotografuoti:**
- [ ] Konsolės output su VISAIS 7 code smells
- [ ] Kiekvienas smell su:
  - Failo keliu
  - Linijos numeriu
  - Smell tipu (pvz: ShortMethodName)
  - Aprašymu

**Code smells kuriuos turi matyti (7):**
1. AuthController.php:88 - ShortMethodName
2. Controller.php:64 - ShortMethodName
3. Repository.php:12 - TooManyPublicMethods
4. PointService.php:87 - MissingImport
5. PointService.php:91 - MissingImport
6. TaskService.php:134 - UnusedLocalVariable
7. UserService.php:77 - UnusedFormalParameter

**Pavadinti failą:** `06-phpmd-code-smells.png`

---

#### 7. PHPMetrics Summary (Konsolė)
**Kaip gauti:**
```bash
cd backend
cat phpmetrics-initial.txt
```

**Arba paleisti iš naujo:**
```bash
./vendor/bin/phpmetrics --report-cli app
```

**Ką fotografuoti:**
- [ ] VISĄ konsolės output su metric lentelėmis
- [ ] LOC sekcija
- [ ] OOP sekcija
- [ ] Coupling sekcija
- [ ] Complexity sekcija
- [ ] Violations sekcija

**Pavadinti failą:** `07-phpmetrics-console-summary.png`

---

#### 8. Code Coverage (jei jau yra testai)
**Kaip gauti:**
```bash
cd backend
./vendor/bin/phpunit --coverage-text
```

**Jei dar nėra testų:**
- Screenshot rašykite: "Coverage: 0% (testai nesukurti)"
- Arba praleiskite šį screenshot'ą dabar

**Pavadinti failą:** `08-code-coverage.png`

---

### ✅ Frontend (TypeScript/Vue) - 4 screenshot'ai

#### 9. ESLint Summary (Konsolė)
**Kaip gauti:**
```bash
cd frontend
cat eslint-initial.txt | tail -10
```

**Arba paleisti iš naujo:**
```bash
npx eslint . --ext .vue,.ts,.js
```

**Ką fotografuoti:**
- [ ] Paskutines 10 eilučių su summary
- [ ] Turi būti matoma eilutė:
  ```
  ✖ 341 problems (142 errors, 199 warnings)
  111 errors and 148 warnings potentially fixable with the `--fix` option.
  ```

**Metrika kurią turi matyti:**
- Total: **341 problems**
- Errors: **142**
- Warnings: **199**

**Pavadinti failą:** `09-eslint-summary.png`

---

#### 10. ESLint Errors Pavyzdžiai (Konsolė)
**Kaip gauti:**
```bash
cd frontend
cat eslint-initial.txt | head -50
```

**Ką fotografuoti:**
- [ ] Pirmų 40-50 eilučių su konkrečiomis klaidomis
- [ ] Turi būti matomas:
  - Failo kelias
  - Linijos numeris
  - Error tipas (pvz: @typescript-eslint/no-explicit-any)
  - Aprašymas

**Pavyzdys:**
```
/path/to/file.vue
  88:17  error  Unexpected any. Specify a different type  @typescript-eslint/no-explicit-any
  111:17 error  Unexpected any. Specify a different type  @typescript-eslint/no-explicit-any
```

**Pavadinti failą:** `10-eslint-errors-examples.png`

---

#### 11. ESLint Top Error Types
**Kaip gauti:**
```bash
cd frontend
cat eslint-initial.txt | grep "error" | awk '{print $4}' | sort | uniq -c | sort -rn | head -10
```

**Ką fotografuoti:**
- [ ] Top 10 dažniausių error tipų su skaičiais

**Metrikos kurias turi matyti (top 5):**
1. vue/singleline-html-element-content-newline: ~38
2. @typescript-eslint/no-explicit-any: ~21
3. @stylistic/brace-style: ~18
4. @stylistic/eol-last: ~7
5. @typescript-eslint/no-unused-vars: ~5

**Pavadinti failą:** `11-eslint-top-errors.png`

---

#### 12. Frontend Probleminiai Failai
**Kaip gauti:**
Atsidaryti `frontend/eslint-initial.txt` teksto editoriuje ir surasti:

**Ką fotografuoti:**
- [ ] 5 problemiškiausių failų sąrašą su error/warning skaičiais:

1. `components/Calendar/TaskModal.vue` - 44 errors, 165 warnings
2. `pages/statistics-table.vue` - 35 errors, 171 warnings
3. `pages/shop-manage.vue` - 18 errors, 43 warnings
4. `components/Calendar/WeekCalendar.vue` - 13 errors, 20 warnings
5. `composables/useAuth.ts` - 15 errors, 0 warnings

**Pavadinti failą:** `12-frontend-problematic-files.png`

---

## 🖼️ Kaip Daryti Screenshot'us

### macOS (rekomenduojama):
1. **Pasirinkta sritis:**
   - Spauskite `Cmd + Shift + 4`
   - Vilkite pelę aplink norimą sritį
   - Atleidus pelę, screenshot išsaugomas Desktop'e

2. **Visas langas:**
   - Spauskite `Cmd + Shift + 4 + Space`
   - Spausti ant terminalo/browser lango
   - Screenshot išsaugomas Desktop'e

3. **Su įrankiu (geriausias):**
   - Spauskite `Cmd + Shift + 5`
   - Pasirinkite "Capture Selected Portion" arba "Capture Selected Window"
   - Galite pridėti cursor'ių, annotations

### Windows:
1. **Snipping Tool:**
   - Spauskite `Win + Shift + S`
   - Pasirinkite sritį
   - Screenshot nukopijuotas į clipboard
   - Įklijuokite į Paint ir išsaugokite

### Linux:
1. **Screenshot įrankis:**
   - Ubuntu: `gnome-screenshot -a` (area selection)
   - Arba įdiekite Flameshot: `sudo apt install flameshot`
   - Paleisti: `flameshot gui`

---

## 📁 Kaip Organizuoti Screenshot'us

### Failų pavadinimai:
```
01-phpmetrics-dashboard.png
02-phpmetrics-complexity.png
03-phpmetrics-coupling.png
04-phpmetrics-all-classes.png
05-phpstan-errors.png
06-phpmd-code-smells.png
07-phpmetrics-console-summary.png
08-code-coverage.png
09-eslint-summary.png
10-eslint-errors-examples.png
11-eslint-top-errors.png
12-frontend-problematic-files.png
```

### Katalogo struktūra:
```
screenshots/
├── backend/
│   ├── 01-phpmetrics-dashboard.png
│   ├── 02-phpmetrics-complexity.png
│   ├── 03-phpmetrics-coupling.png
│   ├── 04-phpmetrics-all-classes.png
│   ├── 05-phpstan-errors.png
│   ├── 06-phpmd-code-smells.png
│   ├── 07-phpmetrics-console-summary.png
│   └── 08-code-coverage.png
└── frontend/
    ├── 09-eslint-summary.png
    ├── 10-eslint-errors-examples.png
    ├── 11-eslint-top-errors.png
    └── 12-frontend-problematic-files.png
```

---

## ✅ Checklist

### Prieš darydami screenshot'us:

- [ ] PHPMetrics HTML reportas sugeneruotas (`phpmetrics-report/`)
- [ ] Visi .txt failai išsaugoti (phpstan, phpmd, phpmetrics, eslint)
- [ ] Browser/Terminal langai išdidinti (full screen geriau)
- [ ] Font dydis terminaluje įskaitomas (ne per mažas)

### Backend screenshot'ai (8):

- [ ] 01 - PHPMetrics Dashboard (HTML)
- [ ] 02 - PHPMetrics Complexity (HTML)
- [ ] 03 - PHPMetrics Coupling (HTML)
- [ ] 04 - PHPMetrics All Classes Table (HTML)
- [ ] 05 - PHPStan Errors (Console)
- [ ] 06 - PHPMD Code Smells (Console)
- [ ] 07 - PHPMetrics Console Summary (Console)
- [ ] 08 - Code Coverage (jei yra)

### Frontend screenshot'ai (4):

- [ ] 09 - ESLint Summary (Console)
- [ ] 10 - ESLint Error Examples (Console)
- [ ] 11 - ESLint Top Errors (Console)
- [ ] 12 - Frontend Problematic Files (Console/Text)

### Po screenshot'ų:

- [ ] Visi failai pavadiniti pagal konvenciją (01-, 02-, etc.)
- [ ] Visi screenshot'ai įskaitomi (tekstas matomas)
- [ ] Sukurtas screenshots/ katalogas su backend/ ir frontend/ subfolders
- [ ] Screenshot'ai nukopijuoti į tą katalogą

---

## 🔍 Kaip Surasti Konkrečias Metrikas

### PHPMetrics HTML:

| Metrika | Vieta HTML |
|---------|-----------|
| LOC | index.html → LOC card (viršuje) |
| Avg CC | complexity.html → Summary |
| Avg Coupling | coupling.html → Metrics table |
| Classes Count | index.html → OOP card |
| Methods Count | index.html → OOP card |
| LCOM | oop.html → Cohesion section |
| Violations | index.html → Violations card |

### Konsolės Output:

| Metrika | Komanda |
|---------|---------|
| PHPStan Errors | `cat phpstan-initial.txt \| tail -5` |
| PHPMD Smells Count | `cat phpmd-initial.txt \| grep "app/" \| wc -l` |
| ESLint Total | `cat eslint-initial.txt \| tail -3` |
| ESLint Errors | `cat eslint-initial.txt \| grep error \| wc -l` |

---

## 💡 Patarimai

1. **HTML reportus geriau screenshot'inti naudojant browser:**
   - Chrome: `Cmd+Shift+P` → "Capture node screenshot"
   - Galima screenshot'inti tik specific HTML element (pvz: table)

2. **Konsolės screenshot'ui:**
   - Išdidinti terminal font (View → Make Text Bigger)
   - Naudoti šviesią temą (lengviau skaityti Word'e)
   - Įsitikinti kad visas tekstas telpa į ekraną

3. **Word dokumente:**
   - Įkelti screenshot'us su caption'ais
   - Pavyzdžiui: "Pav. 1. PHPMetrics Dashboard - pradinės metrikos"

4. **Jei screenshot per didelis:**
   - Resize naudojant Preview (macOS) arba Paint (Windows)
   - Rekomenduojamas plotis: 1200-1600px

---

**Sėkmės darant screenshot'us! 📸✨**

Jei kils klausimų, žiūrėkite `METRIKU_GAVIMO_INSTRUKCIJA.md`