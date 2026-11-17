#!/usr/bin/env bash
# Моніторинг доступності API Pelovit. Запускається кроном (кожні 3 хв).
# Бʼє /api/settings — перевіряє весь ланцюг nginx → Laravel → БД.
# При падінні/відновленні шле лист через Resend на ADMIN_EMAIL.
# Лист — ЛИШЕ на зміну стану (up↔down), без спаму на кожну перевірку.
#
# Окремо стежить за диском: лист, коли зайнято понад DISK_LIMIT% (див. аварію 21.09).
#
# Обмеження: якщо сервер мертвий повністю (мережа/живлення), крон не запуститься
# й листа не буде — для цього потрібен зовнішній монітор (напр. UptimeRobot).
set -u

ENV_FILE=/opt/pelovit/.env
DIR=/opt/pelovit/monitor
STATE="$DIR/.state"
LOG="$DIR/health.log"
URL="https://develop-site.online/api/settings"
TIMEOUT=10
RETRIES=3
DISK_LIMIT=85
DISK_STATE="$DIR/.disk_state"

ts() { date '+%Y-%m-%d %H:%M:%S'; }

# Значення з .env без його виконання (щоб не чіпати лапки/спецсимволи).
getenv() { grep -m1 "^$1=" "$ENV_FILE" 2>/dev/null | cut -d= -f2- | sed -e 's/^"//' -e 's/"$//'; }

RESEND_API_KEY=$(getenv RESEND_API_KEY)
# Відправник — тестовий адрес Resend: шле лише на пошту власника акаунта Resend,
# зате не залежить від верифікації домену (develop-site.online не верифікований → 403).
ALERT_TO=$(getenv ADMIN_EMAIL)

mkdir -p "$DIR"

# Тримаємо лог маленьким (підстраховка, хоч ротація логів уже є глобально).
if [ -f "$LOG" ] && [ "$(stat -c%s "$LOG" 2>/dev/null || echo 0)" -gt 1000000 ]; then
  tail -n 500 "$LOG" > "$LOG.tmp" && mv "$LOG.tmp" "$LOG"
fi

BODY=/tmp/pelovit_health_body
LAST_CODE=""
check() {
  local code
  code=$(curl -s -o "$BODY" -w '%{http_code}' --max-time "$TIMEOUT" "$URL" 2>/dev/null)
  LAST_CODE="$code"
  # Успіх = 200 І тіло парситься як JSON (ловить «200, але зламано»).
  [ "$code" = "200" ] && jq -e . "$BODY" >/dev/null 2>&1
}

ok=1
for i in $(seq 1 "$RETRIES"); do
  if check; then ok=0; break; fi
  sleep 3
done
rm -f "$BODY"

prev="up"
[ -f "$STATE" ] && prev=$(cat "$STATE")

send() {
  local subject="$1" body="$2"
  if [ -z "$RESEND_API_KEY" ]; then echo "$(ts) ALERT-SKIP: no RESEND_API_KEY" >> "$LOG"; return; fi
  curl -s -o /dev/null --max-time 15 -X POST https://api.resend.com/emails \
    -H "Authorization: Bearer $RESEND_API_KEY" \
    -H "Content-Type: application/json" \
    -d "$(jq -n --arg from "Pelovit Monitor <onboarding@resend.dev>" --arg to "$ALERT_TO" \
             --arg s "$subject" --arg t "$body" \
             '{from:$from, to:[$to], subject:$s, text:$t}')"
}

if [ "$ok" -eq 0 ]; then
  echo "$(ts) UP" >> "$LOG"
  if [ "$prev" = "down" ]; then
    send "✅ Pelovit відновився" "Сайт знову відповідає: $URL повернув 200 о $(ts)."
    echo "$(ts) RECOVERED -> alert sent" >> "$LOG"
  fi
  echo "up" > "$STATE"
else
  echo "$(ts) DOWN (код: ${LAST_CODE:-timeout})" >> "$LOG"
  if [ "$prev" != "down" ]; then
    send "🔴 Pelovit лежить" "Сайт не відповідає: $URL — код ${LAST_CODE:-timeout} о $(ts) (після $RETRIES спроб). Перевір docker і диск на сервері."
    echo "$(ts) WENT DOWN -> alert sent" >> "$LOG"
  fi
  echo "down" > "$STATE"
fi

# --- Диск: повний диск уже раз поклав API на 40 днів ---
disk=$(df --output=pcent / | tail -1 | tr -dc '0-9')
dprev="ok"
[ -f "$DISK_STATE" ] && dprev=$(cat "$DISK_STATE")
if [ "${disk:-0}" -ge "$DISK_LIMIT" ]; then
  if [ "$dprev" != "full" ]; then
    send "🟠 Pelovit: диск зайнятий на ${disk}%" "На сервері зайнято ${disk}% диска (поріг ${DISK_LIMIT}%). Перевір: docker system df, journalctl --disk-usage. Зазвичай допомагає docker image prune -af."
    echo "$(ts) DISK ${disk}% -> alert sent" >> "$LOG"
  fi
  echo "full" > "$DISK_STATE"
else
  [ "$dprev" = "full" ] && echo "$(ts) DISK back to ${disk}%" >> "$LOG"
  echo "ok" > "$DISK_STATE"
fi
