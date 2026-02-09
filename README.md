# Gastos App - Control de Gastos Mensuales

Sistema web para gestionar gastos mensuales con interfaz visual, autenticacion y notificaciones por email.

## Funcionalidades

- **Dashboard mensual** — Resumen de totales (total, pagado, pendiente) + tabla de gastos
- **Marcar pagado/pendiente** — Un clic para cambiar el estado
- **Subir comprobantes** — Sube capturas de pago (imagenes)
- **Gestion de servicios** — Agrega, edita y elimina gastos fijos
- **Generacion automatica** — Al entrar a un mes nuevo, se crean los gastos automaticamente
- **Alertas visuales** — Pagos vencidos (rojo) y proximos en 3 dias (amarillo)
- **Email diario (8 AM)** — Resumen de pagos vencidos y proximos
- **Login seguro** — Un solo usuario, registro deshabilitado

## Stack

- **Laravel 11** + PHP 8.3
- **Blade** + Tailwind CSS (via Breeze)
- **SQLite** (default)
- **Docker** + Nginx + Supervisor + Cron

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

## Desarrollo Local

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
npm run build
php artisan serve
```

Credenciales por defecto: `admin@gastos.app` / `password`

## Despliegue en VPS con Docker

Ver la guia completa paso a paso en [GUIA-INSTALACION-VPS.md](GUIA-INSTALACION-VPS.md).

Resumen rapido:

```bash
docker build -t gastos-app .
docker run -d --name gastos-app --restart unless-stopped \
  -p 127.0.0.1:8080:80 \
  -e APP_ENV=production \
  -e APP_URL=https://tu-dominio.com \
  -e ADMIN_EMAIL=tu@email.com \
  -e ADMIN_PASSWORD=tu-password \
  -e NOTIFICATION_EMAIL=tu@email.com \
  -e MAIL_HOST=smtp.gmail.com \
  -e MAIL_PORT=587 \
  -e MAIL_USERNAME=tu@gmail.com \
  -e MAIL_PASSWORD=tu-app-password \
  -v gastos-data:/var/www/html/database \
  -v gastos-storage:/var/www/html/storage \
  gastos-app
docker exec gastos-app php artisan db:seed --force
```

## Variables de Entorno

| Variable | Descripcion | Ejemplo |
|----------|-------------|---------|
| `ADMIN_EMAIL` | Email del usuario admin | `admin@gastos.app` |
| `ADMIN_PASSWORD` | Password del admin | `password` |
| `ADMIN_NAME` | Nombre del admin | `Admin` |
| `NOTIFICATION_EMAIL` | Email destino de notificaciones | `tu@email.com` |
| `MAIL_MAILER` | Driver de correo | `smtp` |
| `MAIL_HOST` | Servidor SMTP | `smtp.gmail.com` |
| `MAIL_PORT` | Puerto SMTP | `587` |
| `MAIL_USERNAME` | Usuario SMTP | `tu@gmail.com` |
| `MAIL_PASSWORD` | Password SMTP | `app-password` |
| `API_TOKEN` | Token para API REST (opcional) | `openssl rand -hex 32` |

## API REST (opcional)

La API REST sigue disponible para integraciones externas. Requiere header `X-API-Token`.

| Metodo | Endpoint | Descripcion |
|--------|----------|-------------|
| GET | `/api/gastos` | Listar servicios activos |
| POST | `/api/gastos` | Agregar servicio |
| DELETE | `/api/gastos/{id}` | Eliminar servicio |
| GET | `/api/gastos/mensuales?mes=X&anio=Y` | Ver gastos del mes |
| POST | `/api/gastos/generar-mes` | Generar entries del mes |
| PUT | `/api/gastos/mensuales/{id}/pagar` | Marcar como pagado |
| POST | `/api/gastos/mensuales/{id}/comprobante` | Subir comprobante |
| GET | `/api/gastos/proximos?dias=3` | Pagos proximos |
| GET | `/api/gastos/vencidos` | Pagos vencidos |
