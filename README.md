# KyrosCounsel

SaaS multi-tenant operacional para abogados de inmigracion (US + RD + multi-pais).
**100% PHP nativo + MySQL.** Sin Composer, sin Laravel, sin npm/Vite, sin librerias.

## Estado actual: TODOS los modulos operacionales

- ✅ **Multi-tenancy** path-based (`/t/{slug}`) con aislamiento blindado en capa DB
- ✅ **Auth** Argon2 + sesion endurecida + CSRF + rate limit + dummy hash anti-timing
- ✅ **2FA TOTP** nativo (Google Authenticator, Authy, 1Password)
- ✅ **RBAC** por rol (super_admin, tenant_admin, attorney, paralegal, staff)
- ✅ **Cifrado PII** (XChaCha20-Poly1305 si sodium / AES-256-GCM fallback) con HKDF por tenant
- ✅ **Onboarding publico**: registro de bufete con plan + admin inicial
- ✅ **Super Admin**: dashboard global con metricas Chart.js + CRUD tenants + planes
- ✅ **Dashboard tenant**: stats + Chart.js (estados, tipos)
- ✅ **Clientes**: CRUD + busqueda blind-index por pasaporte/A-num
- ✅ **Casos migratorios**: CRUD + cambios de estado + historial + tipos por pais (US/DO/MX)
- ✅ **Documentos**: upload cifrado + integridad SHA-256 + descarga descifrada
- ✅ **Tareas**: CRUD + filtros + asignacion + due dates
- ✅ **Notas** en casos
- ✅ **Workflows**: motor de eventos (client.created, case.status_changed, etc.) con acciones (crear tarea, enviar email)
- ✅ **Billing**: planes (Basico/Pro/Enterprise) + suscripciones + uso vs limites + cambio de plan
- ✅ **Emails** via Resend (con dry-run en local que loguea a `storage/logs/emails-dryrun.log`)
- ✅ **Multi-pais**: catalogos de tipos de caso por US, DO, MX (extensible)
- ✅ **Landing page** publica + pricing
- ✅ **Audit log** append-only + security log

---

## Instalacion en XAMPP

### 1. Crear base de datos

```bash
"C:/xampp/mysql/bin/mysql.exe" -u root < install.sql
```

O en phpMyAdmin: pega el contenido de `install.sql`.

### 2. (Opcional) Activar sodium para cifrado mas fuerte

Edita `C:/xampp/php/php.ini`, descomenta la linea:
```
extension=sodium
```
Reinicia Apache. Si no lo haces, la app usa AES-256-GCM (igual de seguro, AEAD).

### 3. Cambiar claves criptograficas (recomendado)

Edita `config.php`:
```php
define('APP_KEY', '<NUEVO>');         // 32 bytes hex
define('ENCRYPTION_KEY', '<NUEVO>');  // 32 bytes base64
```

Genera con:
```bash
"C:/xampp/php/php.exe" -r "echo bin2hex(random_bytes(32));"
"C:/xampp/php/php.exe" -r "echo base64_encode(random_bytes(32));"
```

### 4. (Opcional) Configurar Resend para emails reales

En `config.php` agrega:
```php
define('RESEND_API_KEY', 're_xxxxx');
define('RESEND_FROM', 'TuApp <noreply@tudominio.com>');
```

Sin esto, los emails se loguean a `storage/logs/emails-dryrun.log` (perfecto para dev).

### 5. Cargar datos demo

```bash
"C:/xampp/php/php.exe" seed.php
```

### 6. Acceder

> **http://localhost/KyrosCounsel/**

---

## Credenciales demo

| Rol | Email | Pass | Va a |
|---|---|---|---|
| Super admin | `super_admin@kyroscounsel.com` | `Admin123!` | `/admin` |
| Admin bufete-demo (RD) | `admin@bufete-demo.com` | `Bufete123!` | `/t/bufete-demo/dashboard` |
| Abogado | `abogado@bufete-demo.com` | `Abogado123!` | `/t/bufete-demo/dashboard` |
| Paralegal | `paralegal@bufete-demo.com` | `Paralegal123!` | `/t/bufete-demo/dashboard` |
| Admin Miami (US) | `admin@miami-immigration.com` | `Miami123!` | `/t/miami-immigration/dashboard` |

---

## Probar el aislamiento multi-tenant

1. Login como `abogado@bufete-demo.com` (tenant 1).
2. Intenta abrir `http://localhost/KyrosCounsel/t/miami-immigration/dashboard` (tenant 2).
3. Resultado esperado: **403 Forbidden** + entrada en `storage/logs/security.log`.

---

## Arquitectura

```
KyrosCounsel/
├── index.php                          # Front controller (router array-based)
├── config.php                         # Constantes
├── install.sql                        # Schema (12 tablas + datos planes)
├── seed.php                           # Datos demo
├── .htaccess                          # Rewrite + headers + bloqueo de directorios
├── README.md
│
├── app/
│   ├── bootstrap.php                  # Carga modulos
│   ├── router.php                     # Router con regex + middleware (auth/tenant/perm)
│   ├── security.php                   # CSP + cifrado PII (sodium o AES-GCM)
│   ├── session.php                    # Sesion endurecida
│   ├── helpers.php                    # e(), url(), csrf, flash, render_with_layout
│   ├── db.php                         # PDO + tenant_*() helpers
│   ├── audit.php                      # audit_log
│   ├── auth.php                       # password_hash, rate limit, guards
│   ├── rbac.php                       # PERM matrix + can() + require_perm()
│   ├── tenant.php                     # resolve_tenant
│   ├── countries.php                  # Catalogos (paises, case_types, document_categories)
│   ├── totp.php                       # 2FA TOTP nativo (Base32 + HOTP)
│   ├── resend.php                     # Cliente Resend via cURL
│   ├── workflows.php                  # Motor de eventos
│   │
│   ├── controllers/                   # Funciones por modulo
│   │   ├── auth_ctrl.php
│   │   ├── public_ctrl.php
│   │   ├── onboarding_ctrl.php
│   │   ├── admin_ctrl.php
│   │   ├── dashboard_ctrl.php
│   │   ├── clients_ctrl.php
│   │   ├── cases_ctrl.php
│   │   ├── documents_ctrl.php
│   │   ├── tasks_ctrl.php
│   │   ├── users_ctrl.php
│   │   ├── workflows_ctrl.php
│   │   ├── billing_ctrl.php
│   │   └── profile_ctrl.php
│   │
│   └── views/                         # PHP nativo, no Twig
│       ├── layouts/
│       │   ├── app.php
│       │   ├── tenant.php             # Sidebar tenant
│       │   ├── admin.php              # Sidebar super admin
│       │   └── public.php             # Header landing
│       └── [auth, landing, onboarding, admin, tenant, clients, cases,
│            documents, tasks, users, workflows, billing, profile]/
│
├── assets/                            # CSS minimo
├── storage/{logs,sessions}            # Fuera de webroot
└── uploads/{tenant_id}/               # Documentos cifrados (.htaccess deny all)
```

---

## Flujo de un request

```
Apache .htaccess
   → index.php
        → bootstrap (config + headers CSP + sesion + modulos)
        → router_dispatch():
            • match path → encuentra ruta
            • CSRF en POST/PUT/DELETE
            • middleware: guest|auth|tenant|super_admin|perm:X
            • llama controller_function($params)
            • controller llama render_with_layout()
```

---

## Multi-tenant blindado

`app/db.php` define dos juegos de funciones:

| Para tablas... | Funciones | Que hace |
|---|---|---|
| **Globales** (tenants, plans, rate_limits) | `db_select`, `db_one`, `db_run` | Prepared statements normales |
| **Por tenant** (users, clients, cases, documents, tasks, notes, workflows, audit_log, ...) | `tenant_select`, `tenant_first`, `tenant_count`, `tenant_insert`, `tenant_update`, `tenant_delete` | **Inyectan `tenant_id` automaticamente** desde `current_tenant()`. Si llamas sin tenant resuelto, lanza excepcion. Rechazan tablas no declaradas en `$GLOBALS['TENANT_TABLES']` |

Reglas:
- Cero string concat en SQL. Solo `:placeholders` con bind tipado.
- Para una nueva tabla con tenant_id, agregala a `$GLOBALS['TENANT_TABLES']`.
- `tenant_update`/`tenant_delete` requieren WHERE no vacio.

---

## Modulos operacionales

### Onboarding (publico)
`/onboarding?plan=pro` → registra bufete + admin. Crea `tenant` (status=trial) + `user` + `subscription` + dispara welcome email.

### Super Admin (`/admin`)
- Dashboard con metricas (tenants total, activos, trial, usuarios) + Chart.js (line ultimos 30d, doughnut por pais)
- CRUD de tenants + cambio de estado (active/suspended/cancelled/pending/trial)
- Listado de planes
- Aislado: solo super_admin entra

### Dashboard tenant (`/t/{slug}/dashboard`)
- Cards: clientes, casos, abiertos, tareas pendientes, documentos
- Chart.js bar (casos por estado), doughnut (top tipos)
- Casos recientes + proximas tareas

### Clientes
- CRUD completo
- PII cifrada: pasaporte, A-number, fecha de nacimiento (XChaCha20-Poly1305 / AES-GCM)
- Blind index para busqueda exacta sin descifrar

### Casos migratorios
- CRUD completo
- Tipos por pais: US (I-130, I-485, I-589, N-400, etc.), DO (residencias, naturalizacion), MX
- Estados: intake, preparing, filed, rfe, approved, denied, withdrawn, closed
- Cambio de estado registra historia + dispara workflows
- Notas inline + tareas asociadas + documentos asociados

### Documentos
- Upload cifrado en disco (uploads/{tenant_id}/...)
- Hash SHA-256 al cifrar; verifica al descifrar
- Categorias: pasaporte, acta nacimiento, I-797, evidencia, traduccion, etc.
- MIME whitelist + size limit 50MB
- `.htaccess deny all + php_flag engine off` en uploads (no se ejecuta nada)

### Tareas
- CRUD + filtros (abiertas, mias, hechas, todas)
- Asignacion a abogado, due_date, prioridad
- Toggle done/pending dispara workflow `task.completed`

### Workflows
- Trigger: client.created, case.created, case.status_changed, task.completed, document.uploaded
- Acciones: create_task (con due_in_days), send_email (con templates `{{first_name}}`)
- Tabla `workflow_runs` registra cada ejecucion (success/failed/skipped)

### Billing
- Cards: plan actual, uso vs limites con barras visuales
- Cambio de plan inmediato (sin Stripe en este Sprint)
- Suscripcion en tabla `subscriptions`

### 2FA
- TOTP RFC 6238 implementado en `app/totp.php` (Base32 nativo, HOTP)
- QR generado por `api.qrserver.com`
- Login con 2FA: paso intermedio en `/login/2fa`
- Tolerancia +/-30s

### Emails (Resend)
- En produccion: configurar `RESEND_API_KEY` en `config.php`
- En dev: dry-run loguea a `storage/logs/emails-dryrun.log`
- Tabla `email_log` registra cada envio (queued/sent/failed)

---

## Seguridad implementada

| Capa | Mecanismo |
|---|---|
| Sesion | httponly + samesite=Strict + regeneracion en login + fingerprint IP+UA + idle 30min + absolute 2h |
| Auth | Argon2 + dummy hash anti-timing + rate limit 5 intentos/15min |
| 2FA | TOTP nativo + rate limit por IP+user (8 intentos/10min) |
| CSRF | Token rotativo cada 30min, validacion en tiempo constante |
| SQL | PDO prepared, EMULATE_PREPARES=false, identifier whitelist |
| Tenant | Helpers tenant_*() inyectan tenant_id; cross-tenant access loguea + 403 |
| PII | XChaCha20-Poly1305 (sodium) o AES-256-GCM (openssl) con HKDF por tenant. Blind index HMAC-SHA256 |
| Documentos | Cifrado AEAD + verificacion SHA-256 al leer |
| Headers | CSP estricto con nonce, X-Frame-Options DENY, Referrer-Policy, Permissions-Policy, HSTS (en prod) |
| Audit | append-only en tabla audit_log + storage/logs/security.log |
| Uploads | MIME whitelist, size limit, .htaccess deny + php disabled |
| Open redirect | Validacion en redirect() |

---

## Despliegue (notas)

Para produccion en VPS:
- PHP-FPM 8.3 + Nginx (mejor que Apache).
- MySQL 8 dedicado.
- HTTPS obligatorio (`SESSION_SECURE=true`, `APP_ENV=production`).
- `APP_DEBUG=false`.
- Activar extension sodium.
- Mover `config.php` fuera del webroot o leer secrets de variables de entorno.
- Usar Redis para rate_limits y sesiones (mas rapido).
- Backups: `mysqldump | gpg --encrypt | s3 cp`, retencion 30 dias.
- Logs a SIEM externo (ELK, Loki).
- WAF delante (Cloudflare) si va publico.
