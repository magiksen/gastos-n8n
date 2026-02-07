# Gastos n8n - Automatizacion de Gastos con Telegram

Sistema de gestion de gastos mensuales con bot de Telegram via n8n.

## Arquitectura

```
Telegram Bot <-> n8n Workflows <-> Laravel API <-> SQLite/MySQL
```

- **Laravel API**: Backend REST para CRUD de gastos y tracking mensual
- **n8n Workflows**: Bot de Telegram + notificaciones automaticas
- **Base de datos**: SQLite (default) o MySQL

## Estructura de la Base de Datos

### Tabla `gastos` (servicios base)
| Campo | Tipo | Descripcion |
|-------|------|-------------|
| servicio | string | Nombre del servicio |
| dia_pago | int | Dia del mes de pago (1-31) |
| monto | decimal | Monto mensual |
| activo | boolean | Si esta activo |

### Tabla `gastos_mensuales` (tracking por mes)
| Campo | Tipo | Descripcion |
|-------|------|-------------|
| gasto_id | FK | Referencia al servicio |
| mes | int | Mes (1-12) |
| anio | int | Anio |
| pagado | boolean | Si fue pagado |
| fecha_pago | date | Fecha de pago |
| comprobante_path | string | Ruta del comprobante |
| notas | text | Notas adicionales |

## API Endpoints

Todos los endpoints requieren header `X-API-Token` o query param `api_token`.

| Metodo | Endpoint | Descripcion |
|--------|----------|-------------|
| GET | `/api/gastos` | Listar servicios activos |
| POST | `/api/gastos` | Agregar servicio |
| DELETE | `/api/gastos/{id}` | Eliminar servicio |
| GET | `/api/gastos/mensuales?mes=X&anio=Y` | Ver gastos del mes |
| POST | `/api/gastos/generar-mes` | Generar entries del mes |
| PUT | `/api/gastos/mensuales/{id}/pagar` | Marcar como pagado |
| PUT | `/api/gastos/mensuales/{id}/no-pagar` | Desmarcar pagado |
| POST | `/api/gastos/mensuales/{id}/comprobante` | Subir comprobante |
| GET | `/api/gastos/proximos?dias=3` | Pagos proximos |
| GET | `/api/gastos/vencidos` | Pagos vencidos |
| GET | `/api/gastos/buscar?nombre=X` | Buscar por nombre |

## Comandos de Telegram

| Comando | Descripcion |
|---------|-------------|
| `/ver` | Ver lista de gastos del mes actual |
| `/agregar Servicio,dia,monto` | Agregar un nuevo gasto |
| `/quitar Servicio` | Eliminar un gasto |
| `/pagar Servicio` | Marcar gasto como pagado |
| `/pendientes` | Ver pagos proximos (5 dias) |
| `/generar` | Generar gastos del mes actual |
| `/help` o `/start` | Ver comandos disponibles |
| Enviar foto con caption `/comprobante Servicio` | Subir comprobante |

## Instalacion en Servidor (EasyPanel)

### 1. Configurar Laravel API

```bash
# Clonar o subir el proyecto al servidor
git clone <tu-repo> /app/gastos-n8n

# Instalar dependencias
composer install --no-dev --optimize-autoloader

# Copiar y configurar .env
cp .env.example .env
php artisan key:generate

# Configurar en .env:
# APP_URL=https://tu-dominio.com
# API_TOKEN=un-token-seguro-generado (genera uno con: openssl rand -hex 32)
# DB_CONNECTION=sqlite (o mysql si prefieres)

# Ejecutar migraciones y seeders
php artisan migrate --force
php artisan db:seed

# Link de storage para comprobantes
php artisan storage:link

# Optimizar
php artisan config:cache
php artisan route:cache
```

### 2. Crear Bot de Telegram

1. Habla con [@BotFather](https://t.me/BotFather) en Telegram
2. Envia `/newbot` y sigue las instrucciones
3. Guarda el **token del bot**
4. Configura los comandos con `/setcommands`:
   ```
   ver - Ver lista de gastos del mes
   agregar - Agregar gasto (Servicio,dia,monto)
   quitar - Eliminar un gasto
   pagar - Marcar gasto como pagado
   pendientes - Ver pagos proximos
   generar - Generar gastos del mes
   help - Ver ayuda
   ```
5. Obten tu **Chat ID**: habla con [@userinfobot](https://t.me/userinfobot)

### 3. Configurar n8n

#### Variables de entorno en n8n:

Configura estas variables en Settings > Variables de tu n8n:

| Variable | Valor |
|----------|-------|
| `API_BASE_URL` | `https://tu-dominio-laravel.com` |
| `API_TOKEN` | El mismo token que pusiste en Laravel `.env` |
| `TELEGRAM_CHAT_ID` | Tu Chat ID de Telegram |

#### Credenciales de Telegram:

1. Ve a **Credentials** en n8n
2. Crea una nueva credencial de tipo **Telegram API**
3. Pega el token del bot de BotFather
4. Nombra la credencial "Telegram Bot"

#### Importar Workflows:

1. Ve a **Workflows** en n8n
2. Importa `n8n-workflows/telegram-bot-gastos.json`
3. Importa `n8n-workflows/notificacion-diaria-gastos.json`
4. En cada workflow:
   - Actualiza las credenciales de Telegram en cada nodo que lo requiera
   - Activa el workflow

### 4. Verificar

1. Abre el chat con tu bot en Telegram
2. Envia `/start` - deberia responder con el menu de comandos
3. Envia `/ver` - deberia mostrar la lista de gastos del mes
4. Envia `/pendientes` - deberia mostrar pagos proximos

## Flujo de Uso Mensual

1. **Inicio de mes**: El workflow diario auto-genera las entradas del mes nuevo
2. **Cada manana (8 AM)**: Recibes notificacion de pagos vencidos y proximos
3. **Al pagar**: Envia `/pagar Netflix` para marcar como pagado
4. **Comprobante**: Envia foto con caption `/comprobante Netflix`
5. **Revisar**: Envia `/ver` para ver el estado actual

## Desarrollo Local

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

Probar API:
```bash
curl -H "X-API-Token: changeme" http://localhost:8000/api/gastos
curl -H "X-API-Token: changeme" http://localhost:8000/api/gastos/mensuales
```
