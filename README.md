# OpenEnergyCoop

**OpenEnergyCoop** es un proyecto de software libre desarrollado en Laravel para apoyar a cooperativas energéticas. Proporciona una API robusta, un dashboard moderno, una web pública y una app móvil para gestionar comunidades de autoconsumo energético.

---

## Presentación general

OpenEnergyCoop está pensado para:

-   Cooperativas energéticas que necesitan herramientas digitales modernas, libres y adaptables.
-   Personas desarrolladoras interesadas en colaborar y ampliar funcionalidades.
-   Ciudadanos interesados en cooperativas energéticas y comunidades de autoconsumo compartido.

---

## Tecnologías principales

-   **Backend:** Laravel con Filament, Sanctum, Spatie Permissions, Laravel Localization, MeiliSearch, Laravel Excel, Spatie Media Library, Fortify (2FA), y más.
-   **Frontend web:** Nuxt con TailwindCSS, Axios, Vue I18n, Matomo para analítica, Chart.js/ApexCharts.
-   **Dashboard:** Nuxt + Material Design + Laravel Echo para notificaciones en tiempo real.
-   **App móvil:** React Native + Expo + EAS con autenticación Sanctum/JWT, notificaciones push y más.

---

## Estructura del proyecto

### Backend Laravel (openenergycoop-api)

-   Modular y organizado en carpetas bajo `app/Models`:

    -   `Core/`: Usuarios, Organizaciones, Roles
    -   `Web/`: Páginas, Menús, SEO
    -   `Energy/`: Instalaciones, Consumos, Producción
    -   `Economy/`: Wallets y transacciones
    -   `Community/`: Eventos, Mensajes, Notificaciones
    -   `Gamification/`: Retos y logros
    -   `Personalization/`: Dashboards y widgets

-   APIs versionadas bajo `routes/api.php` (`/api/v1/*`).

---

### Frontend web (Nuxt)

-   Rutas dinámicas por cooperativa (`/[cooperativa]/...`).
-   Componentes organizados para reutilización y escalabilidad.
-   Internacionalización con Vue I18n.
-   Integración con la API backend y Matomo para analítica.

---

### App móvil (React Native + Expo)

-   Estructura basada en Expo Router.
-   Autenticación integrada con backend.
-   Notificaciones push.
-   Conexión a APIs para consumo y producción energética, comunidad, wallet, etc.

---

## Roadmap de desarrollo

### Fase 1 – Usuarios, roles y cooperativas

-   Modelos: User, UserProfile, Organization, Roles, ConsentLog, AuditLog, UserDevice, Image
-   Autenticación con Google/Apple y sistema granular de permisos.

### Fase 2 – Contenido y sitio web básico

-   Modelos: AppSettings, Page, PageComponent, Hero, TextContent, Banner, Menu, FAQ, SocialLink, Contact, SEO, Collaborators.

### Fase 3 – Noticias, artículos y documentos

-   Modelos: Article, Comment, Tags, Document, Category.

### Fase 4 – Instalaciones, consumo y producción

-   Modelos: EnergyInstallation, ConsumptionPoint, Municipality, Province, Region, ProductionProject, EnergyMeter, EnergyReading, WeatherSnapshots.

### Fase 5 – Wallet y transacciones

-   Modelos: Wallet, WalletTransaction, WalletConversion, WalletTransfer, ProductionParticipations.

### Fase 6 – Eventos y comunidad

-   Modelos: Event, EventAttendance, Message, FormSubmission, NewsletterSubscription, Notification, NotificationSetting.

### Fase 7 – Impacto ambiental y gamificación

-   Modelos: Plant, PlantGroup, CooperativePlantConfig, ImpactMetrics, CommunityMetrics, EnergyChallenge, UserChallengeProgress, Achievement, UserAchievement.

### Fase 8 – Dashboard personal y widgets

-   Modelos: DashboardWidget, UserWidgetPreference, DashboardView, UserSettings, Surveys, SurveyResponses.

### Fase 9 – Integraciones externas

-   Festivalprogram API (eventos), SpaNewsAPI (noticias), SpaWeather/OpenWeatherMap (clima), REE API (precios energía), Coinbase/CoinGecko (cambios), Odoo, Mautic, Twilio, Nexmo, IPinfo, Matomo.

### Fase 10 – Foro (opcional)

-   Modelos: Forum, Thread, Post, Reaction.

---

## Licencia

OpenEnergyCoop está licenciado bajo **GPLv3** para garantizar que las cooperativas puedan usar, modificar y compartir el software de manera libre y ética.

---

## Contribuir

¡Las contribuciones son bienvenidas! Por favor, revisa las [CONTRIBUTING.md](CONTRIBUTING.md) para más detalles sobre el proceso.

---
