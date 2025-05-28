# 🧾 Proxmox VE 8.4.1 Installation on Hetzner (Debian 12 Base) ...

## ✅ Hardware Used
- **Dedicated Server**: Hetzner 

---

## 🔧 Phase 1: Install Debian 12 via Hetzner Rescue

1. Boot into **Hetzner Rescue System** via Robot panel.
2. Run `installimage`:
   ```bash
   installimage
   ```
3. Select:
   - OS: `Debian 12`
   - Partition layout: (RAID0/RAID1 optional)
   - Bootloader: UEFI
   - Use both disks as desired
4. Once installed:
   ```bash
   reboot
   ```

---

Set Hostname 

```bash
hostnamectl set-hostname proxmox-host

OLD_HOSTNAME=$(hostname)
NEW_HOSTNAME=proxmox-host

# Replace old hostname with new one in /etc/hosts
sed -i "s/\b$OLD_HOSTNAME\b/$NEW_HOSTNAME/g" /etc/hosts

printf "nameserver 1.1.1.1\nnameserver 8.8.8.8\n" > /etc/resolv.conf


apt update && apt upgrade -y
```



## 🛠️ Phase 2: Convert Debian into Proxmox VE

SSH into your Debian system:

```bash
ssh root@yourproxmoxip
```

### 1. Add Proxmox Repositories

```bash
curl -o /etc/apt/trusted.gpg.d/proxmox-release-bookworm.gpg http://download.proxmox.com/debian/proxmox-release-bookworm.gpg
echo "deb http://download.proxmox.com/debian/pve bookworm pve-no-subscription" > /etc/apt/sources.list.d/pve-install-repo.list
echo '# deb https://enterprise.proxmox.com/debian/pve bookworm InRelease' > /etc/apt/sources.list.d/pve-enterprise.list
```

### 2. Update and Install Proxmox

```bash
apt update 
apt install proxmox-ve -y
```

---
```

Apply changes and reboot:

```bash
reboot
```

---

## 🔐 Phase 3: Web Login & Cleanup

### Login
- URL: `https://yourproxmoxip:8006`
- User: `root`
- Realm: `Linux PAM authentication`
- Password: *(use your root password)*

---

