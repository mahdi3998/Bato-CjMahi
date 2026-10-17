# INSTALLATION

این دستورالعمل نصب ابتدایی برای توسعه/تست محلی است.

1. کلون ریپو
   git clone https://github.com/mahdi3998/Bato-CjMahi.git
   cd Bato-CjMahi

2. نصب composer
   composer install

3. فایل .env را بساز و مقادیر را پر کن (از .env.example استفاده کن)

4. دیتابیس را بساز و migration‌ها را اجرا کن:
   mysql -u root -p < database/migrations/0001_init.sql
   mysql -u root -p < database/migrations/0002_subscriptions.sql

5. یک محصول نمونه وارد کن

6. اجرای worker و bot
   php bin/worker.php &
   php bin/run_bot.php

