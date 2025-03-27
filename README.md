How to Run the RideShare PHP Project Locally
Step 1: Clone the Repository
1. Open your terminal or command prompt.
2. Run the following command to clone the project:
   ```bash
   git clone https://github.com/faisalamirLPU/RideShare.git
   ```
3. Navigate to the project directory:
   ```bash
   cd RideShare
   ```
Step 2: Set Up a Local Server (XAMPP/WAMP/MAMP)
You need a local server to run PHP and MySQL. Follow the steps below based on your system:

Option 1: Using XAMPP (Recommended)
1. Download and install XAMPP from https://www.apachefriends.org.
2. Start Apache and MySQL from the XAMPP Control Panel.
3. Place the project folder (RideShare) inside the `htdocs` directory:
   ```bash
   C:\xampp\htdocs\RideShare
   ```

Option 2: Using Built-in PHP Server (Alternative)
If you don't want to use XAMPP, you can run the project using PHP's built-in server:
   ```bash
   php -S localhost:8000
   ```
Step 3: Set Up the Database
1. Open phpMyAdmin by visiting: http://localhost/phpmyadmin/
2. Create a new database named `caps_rideshare`.
3. Import the SQL file located in the project directory:
   - Go to Import > Choose the SQL file (e.g., `/database/caps_rideshare.sql`) > Click Go.
Step 4: Configure the Database Connection
1. Open the file `database/db_config.php` in any text editor (like VS Code).
2. Update the following lines if necessary (e.g., based on your database username and password):
   ```php
   $servername = "localhost";
   $username = "root";      // Change if you have a different DB user
   $password = "";          // Enter your database password
   $dbname = "caps_rideshare";
   ```
Step 5: Run the Project
1. If using XAMPP, start your local server and access the project at:
   http://localhost/RideShare/
2. If using PHP's built-in server, visit:
   http://localhost:8000/
Step 6: Login and Explore
You can now register as a driver or passenger and explore the features of the RideShare platform.
Additional Tips
- Admin Access: Create an admin account in the database or through the web interface (if implemented).
- Troubleshooting:
  - Ensure Apache and MySQL services are running.
  - Check database credentials in the `db_config.php` file.
