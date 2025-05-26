# VICIdial Database Cleanup and Optimization Script

This guide will help you set up and run a script to clean up and optimize your VICIdial database.

---

## Step 1 – Create MySQL User for Cleanup Script

### Log in to MySQL as root

```bash
mysql -u root -p
```

Enter your root password when prompted.

### Create the user `dbadmin` with a secure password

```sql
CREATE USER 'dbadmin'@'localhost' IDENTIFIED BY 'V3ry$trongP@ssw0rd!';
```

### Grant all privileges to `dbadmin` (for localhost only)

```sql
GRANT ALL PRIVILEGES ON *.* TO 'dbadmin'@'localhost' WITH GRANT OPTION;
```

### Apply the changes

```sql
FLUSH PRIVILEGES;
```

---

## Step 2 – Download the Cleanup Script

SSH into your VICIdial server and download the script:

```bash
wget https://github.com/Omid-Mohajerani/VICIdial/blob/main/vicidial_db_cleanup/clean_db.php
```

Make the script executable:

```bash
chmod 755 clean_db.php
```

> **Note:** By default, the script deletes records older than 12 months. You can open the script and modify this value as needed.

---

## Step 3 – Run the Script Manually

Run the script with PHP:

```bash
php clean_db.php
```

---

## Step 4 – (Optional) Schedule with Cron

To run the cleanup daily, open the crontab:

```bash
crontab -e
```

Add the following line to run the script every day at 2 AM:

```bash
0 2 * * * /usr/bin/php /path/to/clean_db.php
```

Replace `/path/to/clean_db.php` with the actual path where the script is saved.

---

