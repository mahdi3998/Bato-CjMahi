# Bato — Telegram VPN commerce platform

این ریپوزیتوری اسکافولد اولیهٔ پروژه Bato است (توسعه‌دهنده: CjMahi).

هدف: پلتفرم فروش و مدیریت سرویس‌های VPN از طریق Telegram با پشتیبانی MWPanel و درگاه پرداخت پاسارگاد.

فایل‌های اولیه شامل:
- composer.json
- .env.example
- docker-compose.yml
- bin/install.php (اسکریپت نصب اولیه)
- database/migrations/0001_init.sql
- نمونه کلاس MWPanelClient و MWPanelTokenManager
- اسکلت Bot در src/Telegram/Bot.php

راهنمای سریع برای دانلود و اجرا (محلی):

1. کلون repo
   git clone https://github.com/mahdi3998/Bato-CjMahi.git

2. وارد پوشه شو و وابستگی‌ها را نصب کن
   cd Bato-CjMahi
   composer install

3. فایل .env را از .env.example بساز و مقادیر را پر کن

4. دیتابیس را بساز و migration را اجرا کن:
   mysql -u root -p < database/migrations/0001_init.sql

5. اجرا یا راه‌اندازی داکر:
   docker compose up -d

توجه: این نسخه اسکافولد اولیه است. برای عملکرد کامل MWPanel، توکن‌ها و credentialها را در .env قرار بده و bin/install.php را اجرا کن.
