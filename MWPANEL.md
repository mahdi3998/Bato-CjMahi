# MWPanel Integration

راهنمای پیاده‌سازی و استفاده از MWPanel adapter

تنظیمات مورد نیاز در .env:

MWPANEL_ENABLED=true
MWPANEL_BASE_URL=https://panel.example.com
MWPANEL_USERNAME=your_user
MWPANEL_PASSWORD=your_pass
MWPANEL_TIMEOUT=20
MWPANEL_CONNECT_TIMEOUT=10
MWPANEL_VERIFY_SSL=true

نکات:
- MWPanelTokenManager توکن‌ها را در جدول mwpanel_tokens ذخیره می‌کند؛ توکن‌ها در cleartext در DB قرار می‌گیرند در این اسکافولد. در محیط production باید encrypted storage یا KMS استفاده شود.
- MWPanelClient از Guzzle استفاده می‌کند و ری‌ترای برای status های 5xx انجام می‌دهد.
- برای تست end-to-end روی محیط واقعی باید مقادیر (BASE_URL, USER, PASS) را قرار دهید و اطمینان حاصل کنید که پنل از بیرون قابل دسترس است.
