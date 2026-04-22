# TestLogging - Huong dan test Phase F

Tai lieu nay gom cac luong test de demo Phase F (Logging, Backup, Queue, Scheduler, Cron).

## Luu y truoc khi test
- Thu muc goc project: `/home/derrick/Project/Task1/PostaHub`
- Chay tat ca lenh trong thu muc tren.
- `.env` can dung SMTP neu muon test gui mail that.
- Neu dung queue `database`, phai co worker dang chay.

## Luong 0 - Chuan bi moi truong test
### Muc tieu
Dam bao cache config sach truoc khi test.

### Lenh
```bash
cd /home/derrick/Project/Task1/PostaHub
php artisan optimize:clear
```

## Luong 1 - Kiem tra da co command backup va scheduler
### Muc tieu
Chung minh da dang ky command va lich chay tu dong.

### Lenh
```bash
php artisan list app
php artisan schedule:list
```

### Ky vong
- Co command: `app:backup-users`
- Co lich: `app:backup-users` vao `01:00` moi ngay

## Luong 2 - Test backup users bang tay
### Muc tieu
Chung minh command backup tao file CSV that.

### Lenh
```bash
php artisan app:backup-users
ls -lt storage/app/private/backups/users | head
```

### Ky vong
- Console bao: `Da backup users: ...`
- Co file moi trong `storage/app/private/backups/users`

## Luong 3 - Test queue + mail thong bao admin
### Muc tieu
Chung minh sau backup thi co job gui mail va worker xu ly duoc.

### Cach chay
Mo 2 terminal.

### Terminal A (queue worker)
```bash
cd /home/derrick/Project/Task1/PostaHub
php artisan queue:work -v
```

### Terminal B (tao job)
```bash
cd /home/derrick/Project/Task1/PostaHub
php artisan app:backup-users
```

### Kiem tra them
```bash
php artisan queue:failed
```

### Ky vong
- Terminal A thay job duoc xu ly (processed)
- `queue:failed` khong co ban ghi loi
- Admin nhan duoc mail (hoac vao spam folder)

## Luong 4 - Test log rieng log_mypage
### Muc tieu
Chung minh channel `log_mypage` ghi file rieng.

### Lenh
```bash
php artisan tinker
```
Trong Tinker, nhap:
```php
Log::channel('log_mypage')->error('test log mypage from tinker');
exit
```
Sau do kiem tra:
```bash
ls -lt storage/logs | head
```

### Ky vong
- Co file `log_mypage-YYYY-MM-DD.log`
- Co dong log vua tao

## Luong 5 - Test cron goi scheduler moi phut
### Muc tieu
Chung minh cron da cai va dang kich hoat scheduler.

### Kiem tra crontab
```bash
crontab -l
```
Phai co dong tuong tu:
```cron
* * * * * cd /home/derrick/Project/Task1/PostaHub && /usr/bin/php artisan schedule:run >> /home/derrick/Project/Task1/PostaHub/storage/logs/cron.log 2>&1
```

### Theo doi log cron
```bash
tail -f storage/logs/cron.log
```

### Ky vong
- Moi phut co log scheduler run
- Thuong thay: `No scheduled commands are ready to run.` (binh thuong neu chua den 01:00)

## Luong 6 - Checklist demo nhanh cho leader
### Muc tieu
Demo nhanh trong 2-3 phut.

### Lenh
```bash
cd /home/derrick/Project/Task1/PostaHub
php artisan list app
php artisan schedule:list
php artisan app:backup-users
ls -lt storage/app/private/backups/users | head
php artisan queue:failed
```

### Noi dung can noi khi demo
- Da co command backup users
- Da co scheduler 01:00 daily
- Da tao file backup thanh cong
- Da tich hop queue + mail thong bao
- Da co log rieng cho mypage

## Loi thuong gap va cach xu ly nhanh
- `No such file or directory` voi `storage/app/backups/users`:
  - Dung dung path: `storage/app/private/backups/users`
- Khong thay mail:
  - Kiem tra `.env` SMTP
  - Chay `php artisan config:clear`
  - Dam bao co `php artisan queue:work`
  - Kiem tra `php artisan queue:failed`
- Cron khong chay:
  - Kiem tra `crontab -l`
  - Kiem tra `systemctl status cron`

