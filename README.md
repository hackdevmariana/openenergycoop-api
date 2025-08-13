# OpenEnergyCoop

**OpenEnergyCoop** is a free software project developed in Laravel to support energy cooperatives. It provides a robust API, modern dashboard, public website, and mobile app to manage shared energy consumption communities.

---

## General Overview

OpenEnergyCoop is designed for:

- Energy cooperatives that need modern, free, and adaptable digital tools.
- Developers interested in collaborating and expanding functionalities.
- Citizens interested in energy cooperatives and shared self-consumption communities.

---

## Main Technologies

- **Backend:** Laravel with Filament, Sanctum, Spatie Permissions, Laravel Localization, MeiliSearch, Laravel Excel, Spatie Media Library, Fortify (2FA), and more.
- **Web Frontend:** Nuxt with TailwindCSS, Axios, Vue I18n, Matomo for analytics, Chart.js/ApexCharts.
- **Dashboard:** Nuxt + Material Design + Laravel Echo for real-time notifications.
- **Mobile App:** React Native + Expo + EAS with Sanctum/JWT authentication, push notifications, and more.

---

## Project Structure

### Backend Laravel (openenergycoop-api)

- Modular and organized in folders under `app/Models`:

  - `Core/`: Users, Organizations, Roles
  - `Web/`: Pages, Menus, SEO
  - `Energy/`: Installations, Consumption, Production
  - `Economy/`: Wallets and transactions
  - `Community/`: Events, Messages, Notifications
  - `Gamification/`: Challenges and achievements
  - `Personalization/`: Dashboards and widgets

- Versioned APIs under `routes/api.php` (`/api/v1/*`).

---

### Web Frontend (Nuxt)

- Dynamic routes per cooperative (`/[cooperative]/...`).
- Components organized for reusability and scalability.
- Internationalization with Vue I18n.
- Integration with backend API and Matomo for analytics.

---

### Mobile App (React Native + Expo)

- Structure based on Expo Router.
- Integrated authentication with backend.
- Push notifications.
- Connection to APIs for energy consumption and production, community, wallet, etc.

---

## Current Development Status

### ✅ **Phase 1 - Users, Roles and Cooperatives - COMPLETED**

**Implemented Models:**
- ✅ `User` with role system (Spatie Permissions)
- ✅ `Organization` for cooperatives
- ✅ `AppSetting` for configurations
- ✅ `OrganizationFeature` for functionalities

**Authentication & Security:**
- ✅ Laravel Sanctum for API authentication
- ✅ Laravel Fortify for 2FA
- ✅ Role-based access control system
- ✅ Custom middleware for permissions

**Admin Panel:**
- ✅ Filament admin panel with access control
- ✅ Resources for AppSettings, Organizations, and OrganizationFeatures
- ✅ Admin-only access control

**API Foundation:**
- ✅ Versioned API routes (`/api/v1/*`)
- ✅ Sanctum authentication middleware
- ✅ AppSettings controller implemented

**Development Tools:**
- ✅ Pest testing framework
- ✅ IDE Helper for development
- ✅ Laravel Scout + MeiliSearch for search
- ✅ Laravel Excel for data export
- ✅ Spatie Media Library for file management
- ✅ Laravel Auditing for audit trails
- ✅ Laravel Activity Log for activity tracking
- ✅ Swagger/OpenAPI documentation
- ✅ Multi-language support (Laravel Translatable + Localization)

---

## Development Roadmap

### 🚧 **Phase 2 - Content and Basic Website - IN PROGRESS**
- Models: AppSettings, Page, PageComponent, Hero, TextContent, Banner, Menu, FAQ, SocialLink, Contact, SEO, Collaborators.

### 📋 **Phase 3 - News, Articles and Documents**
- Models: Article, Comment, Tags, Document, Category.

### 📋 **Phase 4 - Installations, Consumption and Production**
- Models: EnergyInstallation, ConsumptionPoint, Municipality, Province, Region, ProductionProject, EnergyMeter, EnergyReading, WeatherSnapshots.

###  **Phase 5 - Wallet and Transactions**
- Models: Wallet, WalletTransaction, WalletConversion, WalletTransfer, ProductionParticipations.

###  **Phase 6 - Events and Community**
- Models: Event, EventAttendance, Message, FormSubmission, NewsletterSubscription, Notification, NotificationSetting.

### 📋 **Phase 7 - Environmental Impact and Gamification**
- Models: Plant, PlantGroup, CooperativePlantConfig, ImpactMetrics, CommunityMetrics, EnergyChallenge, UserChallengeProgress, Achievement, UserAchievement.

### 📋 **Phase 8 - Personal Dashboard and Widgets**
- Models: DashboardWidget, UserWidgetPreference, DashboardView, UserSettings, Surveys, SurveyResponses.

### 📋 **Phase 9 - External Integrations**
- Festivalprogram API (events), SpaNewsAPI (news), SpaWeather/OpenWeatherMap (weather), REE API (energy prices), Coinbase/CoinGecko (exchanges), Odoo, Mautic, Twilio, Nexmo, IPinfo, Matomo.

###  **Phase 10 - Forum (Optional)**
- Models: Forum, Thread, Post, Reaction.

---

## Installation and Setup

### Prerequisites
- PHP 8.2+
- Composer
- Node.js & NPM
- Database (MySQL/PostgreSQL/SQLite)

### Backend Setup
```bash
# Clone the repository
git clone https://github.com/your-org/openenergycoop-api.git
cd openenergycoop-api

# Install dependencies
composer install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan db:seed

# Install Filament
php artisan filament:install

# Generate IDE helpers
php artisan ide-helper:generate
php artisan ide-helper:models

# Run tests
./vendor/bin/pest
```

### Development Commands
```bash
# Start development server
composer run dev

# Run tests
composer test

# Generate API documentation
php artisan l5-swagger:generate
```

---

## License

OpenEnergyCoop is licensed under **GPLv3** to ensure that cooperatives can use, modify, and share the software freely and ethically.

---

## Contributing

Contributions are welcome! Please review [CONTRIBUTING.md](CONTRIBUTING.md) for more details on the process.

---

## Support

For support and questions, please open an issue on GitHub or contact the development team.

---

*Built with ❤️ for the energy cooperative community*
