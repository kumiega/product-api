# Product API

Projekt powstał w oparciu o [API Platform](https://api-platform.com) i [Symfony](https://symfony.com). Projekt pozwala na tworzenie API REST dla produktów i kategorii. Zawiera ona również funkcje do wysyłania powiadomień e-mailowych oraz zapisywania logów.

---

## Środowisko 

Do uruchomienia projektu wymagany jest [Docker](https://www.docker.com/products/docker-desktop/) oraz [Docker Compose](https://docs.docker.com/compose/install/). 

---

## Instalacja
1. **Sklonuj repozytorium**:
```bash
git clone https://github.com/kumiega/product-api
cd product-api
```

2. **Zbuduj obrazy Docker**:
```bash
docker compose build --no-cache
```

3. **Uruchom kontenery**:
```bash
docker compose up --wait
```

---

## Konfiguracja
### Plik środowiskowy (`.env`)
Zmodyfikuj wartości w pliku `api/.env`. Pamiętaj, żeby docelowe wartości były zmienione tylko w środowisku produkcyjnym.

```
SERVER_NAME=localhost
DATABASE_URL=postgresql://api-platform:!ChangeMe!@database:5432/api?serverVersion=15&charset=utf8
MERCURE_PUBLISHER_JWT_KEY=!ChangeThisMercureJWTSecretKey!
MERCURE_SUBSCRIBER_JWT_KEY=!ChangeThisMercureJWTSecretKey!
```

### Ważne ustawienia
- 🔐 **Zmień domyślne hasła** w zmiennych `DATABASE_URL`, `MERCURE_*`
- 🌐 Dostosuj `SERVER_NAME` dla środowiska produkcyjnego
- 🛡️ Włącz HTTPS w produkcji poprzez konfigurację w `docker-compose.yml`

---

## Uruchomienie
1. **Wykonaj migracje bazy danych**:

```bash
docker compose exec <container_name> bin/console doctrine:migrations:migrate
```

2. **Dostępne usługi**:

| Usługa       | URL                          | Port    |
|--------------|------------------------------|---------|
| API          | https://localhost/docs/      | 443     |
| PostgreSQL   | postgresql://database:5432   | 5432    |
| PWA (Next.js)| http://localhost:3000        | 3000    |


3. **Przetestuj API**:
Skorzystaj z [Postman](https://www.postman.com) lub innego klienta HTTP. Możesz także wykorzystać https://localhost/docs/ do przeglądania API. Poniżej przykład zapytania POST przy pomocy CURL. 

```bash
curl -X POST "https://localhost/api/products" \
-H "Content-Type: application/json" \
-d '{"name": "Test", "price": "99.99"}'
```

---

## Zarządzanie
### Podstawowe komendy
| Akcja                     | Komenda                                                   |
|---------------------------|-----------------------------------------------------------|
| Zatrzymaj kontenery       | `docker compose down`                                     |
| Aktualizuj zależności PHP | `docker compose exec <container_name> composer install`   |
| Wejdź do kontenera PHP    | `docker compose exec <container_name> bash`               |

---

## Dokumentacja
- [Oficjalna dokumentacja API Platform](https://api-platform.com/docs/v3.4)
- [Konfiguracja FrankenPHP](https://frankenphp.dev/docs/production/)
- [Dostosowywanie Docker Compose](https://api-platform.com/docs/v2.5/deployment/docker-compose/)

![Architektura API Platform](https://api-platform.com/static/3119f13b70a0dc5f0c3f1e435da5d062/architecture.png)
