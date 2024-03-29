#!/bin/bash
#
# Script: vicidial_backup_and_transfer.sh
# Author: Omid Mohajerani (YouTube: https://www.youtube.com/omidmohajerani)
# Purpose: This script performs a backup of Vicidial data and transfers it to an SFTP server.
#          It is intended to be run as a cron job every Sunday at 2:00 AM.
# Cron Job: 0 2 * * 0 /bin/bash /usr/src/vicidial_backup_and_transfer.sh
# Script Location: /usr/src/
#

# Check if expect is installed
if ! command -v expect &> /dev/null
then
    echo "The 'expect' command is not installed."
    echo "Please install it using the following command:"
    echo "sudo zypper install expect"
    exit 1
fi

# Create /usr/local/src/backup directory if it doesn't exist
backupDir="/usr/local/src/backup"
if [ ! -d "$backupDir" ]; then
    mkdir -p "$backupDir"
fi

# Call the backup script to generate the local file
backupScript="/usr/share/astguiclient/ADMIN_backup.pl --archive_path=$backupDir"
$backupScript

# Check if the backup file exists
if [ ! -f "$backupDir"/*.tar.gz ]; then
    echo "Backup file not found. Exiting."
    exit 1
fi

# Get the name of the local file
localFile=$(ls "$backupDir"/*.tar.gz)

# Define variables - replace them with your SFTP details
server="192.168.2.53"
username="support"
password="mFQNc4q7R15I"
remoteDir="VICIDIALBACKUP"

# Get current date
currentDate=$(date +"%Y-%m-%d")

# Construct the remote file name with the current date
remoteFile="$remoteDir/vicidial-backup-$currentDate.tar.gz"

# Construct the expect script to handle SFTP connection and file transfer
expect -c "
spawn sftp $username@$server
expect {
    \"password:\" {
        send \"$password\n\"
        expect {
            \"sftp>\" {
                send \"put $localFile $remoteFile\n\"
                expect \"sftp>\"
                send \"exit\n\"
                puts \"Vicidial backup transferred to SFTP server successfully.\"
            }
            timeout {
                puts \"Failed to connect to SFTP server: Timeout.\"
                exit 1
            }
            eof {
                puts \"Failed to connect to SFTP server: End of file.\"
                exit 1
            }
        }
    }
    \"Connection refused\" {
        puts \"Failed to connect to SFTP server: Connection refused.\"
        exit 1
    }
    \"Connection timed out\" {
        puts \"Failed to connect to SFTP server: Connection timed out.\"
        exit 1
    }
    \"No route to host\" {
        puts \"Failed to connect to SFTP server: No route to host.\"
        exit 1
    }
    \"Name or service not known\" {
        puts \"Failed to connect to SFTP server: Name or service not known.\"
        exit 1
    }
    \"Network is unreachable\" {
        puts \"Failed to connect to SFTP server: Network is unreachable.\"
        exit 1
    }
}
"
