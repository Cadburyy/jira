Dandori Jira
This is a Laravel-based project that mimics the core functionalities of a task management tool.

Setup Instructions

1. Prerequisites

Before you begin, ensure you have the following installed and running:

`XAMPP: With Apache and MySQL services enabled.`
`Composer: For managing PHP dependencies.`
`Node.js & npm: For managing frontend dependencies.`

2. Project Installation

Clone the repository to your local machine:
`git clone https://github.com/Cadburyy/jira.git`

Navigate into the project directory:
`cd jira`

Install the backend dependencies:
`composer install`

3. Environment Configuration

Copy the example environment file:
`cp .env.example .env`

Generate a new application key:
`php artisan key:generate`

Open the .env file and configure your database settings. Create a new database in phpMyAdmin (e.g., jira_db) and update the following lines:
`DB_DATABASE=jira_db`
`DB_USERNAME=root`
`DB_PASSWORD=`

4. Database Setup

Run the migrations and seeders to create your database tables and populate them with initial data, including roles, permissions, and an administrator user.
`php artisan migrate:fresh --seed`

5. Frontend Setup

Install the frontend dependencies and compile your assets.
`npm install`
`npm run build`

6. Starting the Application

You can now start the development servers to run your application.

Start the Laravel development server:
`php artisan serve`

In a new terminal, start the frontend dev server:
`npm run dev`

Your application should now be accessible in your web browser at http://localhost:8000.


Roles Breakdown :

`Admin`
Administrator of the website, able to create and manage users, roles, and ticket itself as well as global theme changing and downloading ticket data.

`AdminTeknisi`
Administrator that able to download ticket reports aswell and helps :
Requestor - Edit ticket if there's a mistype
Teknisi - Choosing the teknisi for the ticket

`Requestor`
Requesting a ticket that are ordered from the supplier

`Teknisi`
Work to the ticket created by requestor and also changes the status of the ticket such as :
TO DO - Ticket just been created and now ready to be worked on
IN PROGRESS - Started working to the ticket (Check IN)
PENDING - Paused the work due to under some circumstances
FINISH - Work is done and the ticket is closed (Check OUT)

`Views`
Able to see to only "Status Ticket" chart and a table on the home page where it shows a table of WIP Tickets.