# Voucher Management System

A robust, multi-outlet loyalty and voucher transaction system built with **Native PHP** and **MySQL**. Designed for scalability, security, and ease of use.

## 🚀 Key Features

### 🏢 Core Management
- **Multi-Outlet Support**: Manage different store locations.
- **Role-Based Access**: Super Admin vs Outlet Admin.
- **Centralized Customers**: One customer account works across all outlets.
- **Dynamic Settings**: Configure Business Name, Currency (Points/Stamp/Coin), Timezone, and more.

### 💰 Transactions
- **Earn Points**: Flexible point assignment based on purchase amount.
- **Redeem Promos**: Exchange points for active promotions.
- **Immutable Entry Logs**: Audit-friendly transaction history.

### 📢 Marketing & Engagement
- **Promo Management**: Create rich promos with image support (secure upload).
- **WhatsApp Integration**:
  - Automated notifications for "Earn Points" & "Redeem Success" (via RuangWA API).
  - Manual "Click-to-Chat" feature for Customers.
- **Flash Messages**: Clean, session-based UI notifications.

### 📊 Insights & Reporting
- **Dashboard**: Real-time statistics widgets.
- **Reports Module**:
  - Latest Registered Customers.
  - Top Customers (Highest Points).
  - Recent Transaction Activity.

### 🛡️ Security & Architecture
- **CSRF Protection**: Middleware to prevent Cross-Site Request Forgery.
- **Secure File Uploads**: Strict MIME type and extension validation.
- **Database Singleton**: Optimized connection management.
- **Environment Variables**: Sensitive config loaded from `.env` file.
- **PHP 8.1+**: Leveraging modern PHP features.

---

## 🛠️ Tech Stack

- **Backend**: PHP 8.1 (Native)
- **Database**: MySQL / MariaDB
- **Frontend**: HTML5, CSS3 (Admin Template), Vanilla JS
- **Dependency Manager**: Composer
- **Services**: RuangWA (WhatsApp API)

---

## ⚙️ Installation

### Prerequisites
- PHP >= 8.1
- Composer
- Web Server (Apache/Nginx/XAMPP/Laragon)
- MySQL

### Steps

1. **Clone the Repository**
   ```bash
   git clone https://github.com/arielthekillers/voucher-app.git
   cd voucher-app
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Configure Environment**
   Copy the example environment file and configure your database credentials.
   ```bash
   cp .env.example .env
   ```
   Open `.env` and update:
   ```ini
   DB_HOST=localhost
   DB_NAME=voucher
   DB_USER=root
   DB_PASS=your_password
   ```

4. **Import Database**
   Import the provided SQL file (if available) or run migrations to set up the `users`, `customers`, `transactions`, `promos`, `settings`, and `outlets` tables.

5. **Access the App**
   Open your browser and navigate to the project directory (e.g., `http://localhost/voucher/public/admin/login.php`).

---

## 🔐 Default Credentials (Local Seeder)

> **Note**: These are default accounts for testing. Change immediately in production.

```text
Username: admin
Password: admin123
```

---

## 🧠 Project Structure

```
voucher/
├── app/
│   ├── config/      # App & DB Config
│   ├── core/        # Core Classes (Database, DotEnv)
│   ├── helpers/     # Helper Functions (Flash)
│   ├── middleware/  # Auth & CSRF Middleware
│   └── services/    # External Services (WhatsApp)
│
├── public/
│   └── admin/       # Admin Interface Controllers & Views
│
├── resources/
│   └── views/       # Shared Layouts (Header, Sidebar)
│
├── storage/
│   └── uploads/     # User Uploaded Content
│
├── .env             # Environment Variables
└── composer.json    # Dependencies & Autoloading
```

---

## 👨‍💻 Author

**Ariel The Killers**
[GitHub Profile](https://github.com/arielthekillers)
