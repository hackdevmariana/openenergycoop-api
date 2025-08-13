# OpenEnergyCoop

**OpenEnergyCoop** es un proyecto de software libre desarrollado en Laravel para apoyar a cooperativas energéticas. Proporciona una API robusta, un dashboard moderno, una web pública y una app móvil para gestionar comunidades de autoconsumo energético.

---

## Presentación general

OpenEnergyCoop está pensado para:

- Cooperativas energéticas que necesitan herramientas digitales modernas, libres y adaptables.
- Personas desarrolladoras interesadas en colaborar y ampliar funcionalidades.
- Ciudadanos interesados en cooperativas energéticas y comunidades de autoconsumo compartido.

---

## Tecnologías principales

- **Backend:** Laravel con Filament, Sanctum, Spatie Permissions, Laravel Localization, MeiliSearch, Laravel Excel, Spatie Media Library, Fortify (2FA), y más.
- **Frontend web:** Nuxt con TailwindCSS, Axios, Vue I18n, Matomo para analítica, Chart.js/ApexCharts.
- **Dashboard:** Nuxt + Material Design + Laravel Echo para notificaciones en tiempo real.
- **App móvil:** React Native + Expo + EAS con autenticación Sanctum/JWT, notificaciones push y más.

---

## Estructura del proyecto

### Backend Laravel (openenergycoop-api)

- Modular y organizado en carpetas bajo `app/Models`:

  - `Core/`: Usuarios, Organizaciones, Roles
  - `Web/`: Páginas, Menús, SEO
  - `Energy/`: Instalaciones, Consumos, Producción
  - `Economy/`: Wallets y transacciones
  - `Community/`: Eventos, Mensajes, Notificaciones
  - `Gamification/`: Retos y logros
  - `Personalization/`: Dashboards y widgets

- APIs versionadas bajo `routes/api.php` (`/api/v1/*`).

---

### Frontend web (Nuxt)

- Rutas dinámicas por cooperativa (`/[cooperativa]/...`).
- Componentes organizados para reutilización y escalabilidad.
- Internacionalización con Vue I18n.
- Integración con la API backend y Matomo para analítica.

---

### App móvil (React Native + Expo)

- Estructura basada en Expo Router.
- Autenticación integrada con backend.
- Notificaciones push.
- Conexión a APIs para consumo y producción energética, comunidad, wallet, etc.

---

## Estado actual del desarrollo

### ✅ **Fase 1 – Usuarios, roles y cooperativas - COMPLETADA**

**Modelos Implementados:**
- ✅ `User` con sistema de roles (Spatie Permissions)
- ✅ `Organization` para cooperativas
- ✅ `AppSetting` para configuraciones
- ✅ `OrganizationFeature` para funcionalidades

**Autenticación y Seguridad:**
- ✅ Laravel Sanctum para autenticación API
- ✅ Laravel Fortify para 2FA
- ✅ Sistema de control de acceso basado en roles
- ✅ Middleware personalizado para permisos

**Panel Administrativo:**
- ✅ Panel admin de Filament con control de acceso
- ✅ Recursos para AppSettings, Organizations y OrganizationFeatures
- ✅ Control de acceso solo para administradores

**Fundación de la API:**
- ✅ Rutas API versionadas (`/api/v1/*`)
- ✅ Middleware de autenticación Sanctum
- ✅ Controlador de AppSettings implementado

**Herramientas de Desarrollo:**
- ✅ Framework de testing Pest
- ✅ IDE Helper para desarrollo
- ✅ Laravel Scout + MeiliSearch para búsquedas
- ✅ Laravel Excel para exportación de datos
- ✅ Spatie Media Library para gestión de archivos
- ✅ Laravel Auditing para auditorías
- ✅ Laravel Activity Log para seguimiento de actividad
- ✅ Documentación Swagger/OpenAPI
- ✅ Soporte multiidioma (Laravel Translatable + Localization)

---

## Roadmap de desarrollo

### 🚧 **Fase 2 – Contenido y sitio web básico - EN PROGRESO**
- Modelos: AppSettings, Page, PageComponent, Hero, TextContent, Banner, Menu, FAQ, SocialLink, Contact, SEO, Collaborators.

### 📋 **Fase 3 – Noticias, artículos y documentos**
- Modelos: Article, Comment, Tags, Document, Category.

### 📋 **Fase 4 – Instalaciones, consumo y producción**
- Modelos: EnergyInstallation, ConsumptionPoint, Municipality, Province, Region, ProductionProject, EnergyMeter, EnergyReading, WeatherSnapshots.

### 📋 **Fase 5 – Wallet y transacciones**
- Modelos: Wallet, WalletTransaction, WalletConversion, WalletTransfer, ProductionParticipations.

### �� **Fase 6 – Eventos y comunidad**
- Modelos: Event, EventAttendance, Message, FormSubmission, NewsletterSubscription, Notification, NotificationSetting.

### �� **Fase 7 – Impacto ambiental y gamificación**
- Modelos: Plant, PlantGroup, CooperativePlantConfig, ImpactMetrics, CommunityMetrics, EnergyChallenge, UserChallengeProgress, Achievement, UserAchievement.

### 📋 **Fase 8 – Dashboard personal y widgets**
- Modelos: DashboardWidget, UserWidgetPreference, DashboardView, UserSettings, Surveys, SurveyResponses.

### 📋 **Fase 9 – Integraciones externas**
- Festivalprogram API (eventos), SpaNewsAPI (noticias), SpaWeather/OpenWeatherMap (clima), REE API (precios energía), Coinbase/CoinGecko (cambios), Odoo, Mautic, Twilio, Nexmo, IPinfo, Matomo.

### �� **Fase 10 – Foro (opcional)**
- Modelos: Forum, Thread, Post, Reaction.

---

## Instalación y configuración

### Prerrequisitos
- PHP 8.2+
- Composer
- Node.js & NPM
- Base de datos (MySQL/PostgreSQL/SQLite)

### Configuración del Backend
```bash
# Clonar el repositorio
git clone https://github.com/your-org/openenergycoop-api.git
cd openenergycoop-api

# Instalar dependencias
composer install

# Configuración del entorno
cp .env.example .env
php artisan key:generate

# Configuración de la base de datos
php artisan migrate
php artisan db:seed

# Instalar Filament
php artisan filament:install

# Generar helpers del IDE
php artisan ide-helper:generate
php artisan ide-helper:models

# Ejecutar tests
./vendor/bin/pest
```

### Comandos de desarrollo
```bash
# Iniciar servidor de desarrollo
composer run dev

# Ejecutar tests
composer test

# Generar documentación de la API
php artisan l5-swagger:generate
```

---

## Licencia

OpenEnergyCoop está licenciado bajo **GPLv3** para garantizar que las cooperativas puedan usar, modificar y compartir el software de manera libre y ética.

---

## Contribuir

¡Las contribuciones son bienvenidas! Por favor, revisa las [CONTRIBUTING.md](CONTRIBUTING.md) para más detalles sobre el proceso.

---

## Soporte

Para soporte y preguntas, por favor abre un issue en GitHub o contacta al equipo de desarrollo.

---

*Construido con ❤️ para la comunidad de cooperativas energéticas*
