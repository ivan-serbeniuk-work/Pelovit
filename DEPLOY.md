# Деплой на сервер (Docker + MariaDB)

Стек: **Laravel** (API + адмінка MoonShine, php-fpm+nginx) · **Nuxt SSR** (вітрина) ·
**MariaDB**. Хост-nginx термінує HTTPS і розподіляє трафік між контейнерами.

```
Інтернет ──HTTPS──> host nginx ─┬─ /api /admin /vendor /storage /products …  → laravel:8000
                                └─ /  (усе інше, SSR)                          → nuxt:3000
```

## 0. Передумови на сервері
```bash
# Docker + compose-плагін (якщо ще нема)
curl -fsSL https://get.docker.com | sh
docker compose version
```

## 1. Код і .env
```bash
git clone git@github.com:ivan-serbeniuk-work/Pelovit.git /opt/pelovit
cd /opt/pelovit
cp .env.docker.example .env
# згенерувати APP_KEY:
echo "APP_KEY=base64:$(openssl rand -base64 32)"   # вставити в .env
nano .env   # заповнити: DB паролі, LIQPAY_*, RESEND_API_KEY, домени
```
`.env` вже містить домен `develop-site.online` у прикладі — заміни, якщо інший.

## 2. Збірка та запуск
```bash
docker compose up -d --build
docker compose ps           # усі healthy/running
docker compose logs -f laravel   # переконатися, що міграції пройшли
```
Міграції виконуються автоматично при старті `laravel` (entrypoint).

## 3. Адмін-користувач (обов'язково — БД чиста)
```bash
docker compose exec laravel php artisan moonshine:user
```
(За потреби демо-каталог: `docker compose exec laravel php artisan db:seed --force` —
додасть тестові категорії/товари. Для чистого прод-старту пропусти й додай товари в `/admin`.)

## 4. Хост-nginx
У вже існуючий TLS-сервер certbot для `develop-site.online` додай вміст
`deploy/nginx-host.conf` (замінивши дефолтний `location /`). Далі:
```bash
nginx -t && systemctl reload nginx
```

**Після релізу з відео** хостовий конфіг треба оновити руками — деплой його не чіпає:
додати `location /videos/` (є в `deploy/nginx-host.conf` і в
`deploy/develop-site.online.conf`) і підняти `client_max_body_size` до `60M`, інакше
завантаження ролика впаде з 413, а сам ролик віддаватиметься 404. Далі той самий
`nginx -t && systemctl reload nginx`.

## 5. Перевірка
- `https://develop-site.online` — вітрина (Nuxt).
- `https://develop-site.online/admin` — адмінка.
- `https://develop-site.online/api/settings` — має віддати JSON.
- Оформи тестове замовлення з LiqPay → оплати sandbox-карткою `4242 4242 4242 4242` →
  **тепер колбек дійде** (APP_URL публічний) і замовлення стане «Оплачено».

## Оновлення надалі

Звичайний шлях — **нічого робити не треба**: push у `main` запускає GitHub Actions,
який збирає обидва образи у себе, кладе в GHCR і на сервері робить лише `pull` +
рестарт. Сервер більше нічого не збирає (раніше це займало ~7 хвилин на 1 ГБ RAM).

Вручну, якщо треба відкотитись або задеплоїти конкретний коміт:
```bash
cd /opt/pelovit && git pull
echo "$GHCR_TOKEN" | docker login ghcr.io -u <github-user> --password-stdin
export IMAGE_TAG=<повний sha коміту>   # без змінної береться :latest
docker compose pull laravel nuxt
docker compose up -d --no-build
```
Міграції виконуються автоматично при старті `laravel` (entrypoint).

Зібрати образ прямо на сервері (аварійний варіант, потребує вільної памʼяті):
```bash
docker compose up -d --build
```

## Важливо
- **LiqPay**: зараз sandbox-ключі. Після активації кабінету — впиши бойові `LIQPAY_*`
  і `LIQPAY_SANDBOX=false`, `docker compose up -d`.
- **Нова Пошта**: `NOVA_POSHTA_API_KEY` порожній → автозаповнення міст вимкнено, у формі
  замовлення діє тимчасовий `REQUIRE_NP=false` (місто/відділення вводяться текстом). Коли
  додаси ключ НП — постав `REQUIRE_NP = true` у `frontend/app/pages/order/index.vue`,
  запушь у `main` і дочекайся деплою (образ nuxt перезбереться в CI).
- **Дані**: свіжа БД порожня. Товари/категорії — через `/admin`, або імпортуй дамп своєї
  локальної БД (`docker compose exec -T db mariadb -u root -p pelovit < dump.sql`).
- **Ресурси**: збірка Nuxt важка для 1 ГБ RAM — swap (у тебе 2 ГБ) обов'язковий.
- **Відео в тілі статті**: редактор працює з `relative_urls: false`, тож вставлений ролик
  зберігається з абсолютним URL (`https://домен/videos/...`). Зміна домену вимагатиме
  `UPDATE posts SET body = REPLACE(body, 'https://старий', 'https://новий')`.
- **Відео**: залиті ролики лежать у томі `uploads_videos` (`public/videos`), як і фото в
  `uploads_images`. Том створюється сам при `docker compose up -d`, але на старому стеку
  його немає — тобто реліз з відео вимагає саме деплою, а не лише міграції. Ліміт файлу —
  50 МБ у валідації, 60 МБ у php.ini і nginx. Транскодування немає: що залили, те й
  віддається, тож стежити за розміром роликів доводиться вручну.
- **Логи / диск**: json-логи контейнерів обмежено ротацією (10 МБ × 5 на контейнер),
  щоб не повторилось переповнення диска. Задано у двох місцях: `logging:` у
  `docker-compose.yml` (діє з наступного деплою) і глобально в `/etc/docker/daemon.json`
  на сервері. Щоб активувати глобальний дефолт одразу, демон треба перезапустити раз:
  `systemctl restart docker` (короткий простій, контейнери піднімаються самі). Перевірка:
  `docker inspect --format '{{json .HostConfig.LogConfig}}' pelovit-nuxt-1`.
```
