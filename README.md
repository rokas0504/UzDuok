# Projekto settupas
Pastaba: Turit tureti suinstaliave dockeri ir atsisiunte docker app

#### Pirma pasiklonuojam
```bash
git clone "repisitorijos http linkas"
```
## Backas
### Einam i backo direktorija
```bash
cd UzDuok/backend/
```

### Nusikopinam failiukus
Mac/linux komandos:
```bash
cp docker/local/docker-compose.yml . 
cp .env.example .env
```
Windows komandos:
```bash
Copy-Item docker/local/docker-compose.yml . -Force
Copy-Item .env.example .env -Force
```

### Susinstalinam
```bash
composer install
```

### Paleidziam dockerio konteineri
```bash
docker network create UzDuokBackend-network
docker compose up -d
```

### Nusikopijuojam rakta ir pasileidziam migracijas bei seederius
```bash
docker docker exec -it UzDuokBackend-laravel sh
php artisan key:generate
php artisan migrate:fresh
php artisan db:seed
```
# Frontas
### Nuo root folderio keliaujame i fronto direktorija
```bash
cd UzDuok/frontend/
```
### Nusikopinam
```bash
cp .env.example .env
```
Windows komandos:
```bash
Copy-Item .env.example .env -Force
```

### Susuinstalinam
```bash
npm install
```

### Paleidziam dokerio konteineri
```bash
docker compose up -d
```

### Patikrinam are veikia:
http://localhost:3000/
http://localhost:8080/ (Patikrinti logina DB_DATABASE=UzDuokBackend DB_USERNAME=uzduok)
http://localhost/