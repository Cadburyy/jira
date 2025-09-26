# Dandori Jira

A **Laravel-based project** that mimics the core functionalities of a task management tool.

---

## 🚀 Setup Instructions

### 1. Prerequisites

Before you begin, make sure the following are installed and running:

* **XAMPP** (Apache and MySQL services enabled)
* **Composer** (for managing PHP dependencies)
* **Node.js & npm** (for managing frontend dependencies)

---

### 2. Project Installation

Clone the repository to your local machine:

```bash
git clone https://github.com/Cadburyy/jira.git
```

Navigate into the project directory:

```bash
cd jira
```

Install backend dependencies:

```bash
composer install
```

---

### 3. Environment Configuration

Copy the example environment file:

```bash
cp .env.example .env
```

Generate a new application key:

```bash
php artisan key:generate
```

Open the `.env` file and configure your database settings.
For example, create a new database in **phpMyAdmin** (e.g., `jira_db`) and update the following lines:

```env
DB_DATABASE=jira_db
DB_USERNAME=root
DB_PASSWORD=
```

---

### 4. Database Setup

Run migrations and seeders to create tables and populate them with initial data (roles, permissions, and an admin user):

```bash
php artisan migrate:fresh --seed
```

---

### 5. Frontend Setup

Install frontend dependencies and build assets:

```bash
npm install
npm run build
```

---

### 6. Starting the Application

Start the Laravel development server:

```bash
php artisan serve
```

In a new terminal, start the frontend dev server:

```bash
npm run dev
```

Your application should now be accessible at:
👉 [http://localhost:8000](http://localhost:8000)

---

## 👥 Roles Breakdown

### **Admin**

* Full administrator access
* Manage users, roles, customer and tickets
* Change global theme
* Download ticket data

---

### **AdminTeknisi**

* Able to download ticket reports
* Manage users, customer and tickets
* Assists other roles:

  * **Requestor** → Edit tickets if there are mistakes
  * **Teknisi** → Assign a technician to a ticket

---

### **Requestor**

* Create new tickets (ordered from supplier)

---

### **Teknisi**

* Work on tickets created by requestors
* Update ticket status:

  * **TO DO** → Ticket created, ready to be worked on
  * **IN PROGRESS** → Work started (Check In)
  * **PENDING** → Work paused due to circumstances
  * **FINISH** → Work completed and closed (Check Out)

---

### **Views**

* Read-only role
* Can only view:

  * "Status Ticket" chart
  * Table of WIP (Work in Progress) tickets on the homepage
