Step 1 Create MYSQL User for our clean up script 


# Create a MySQL User for Local Maintenance (`dbadmin`)

## Step 1 – Log in to MySQL as root

```bash
mysql -u root -p
```

Enter your root password when prompted.

---

## Step 2 – Create the user `dbadmin` with a secure password

```sql
CREATE USER 'dbadmin'@'localhost' IDENTIFIED BY 'V3ry$trongP@ssw0rd!';
```

---

## Step 3 – Grant all privileges to `dbadmin` (for localhost only)

```sql
GRANT ALL PRIVILEGES ON *.* TO 'dbadmin'@'localhost' WITH GRANT OPTION;
```

---

## Step 4 – Apply the changes

```sql
FLUSH PRIVILEGES;
```

---

Step 2 - Download the script :

ssh to your vicidiial and download the cleanup script 
wget 


chmod 755 cleanup_db.php
 
by default it will delete all the records older than 12 month . you can open and edit the file to set to other value 



Step 3 - Run it manually 

php cleanup_db.php


Step 4 ( optional ) scheudle it cron to run every day



