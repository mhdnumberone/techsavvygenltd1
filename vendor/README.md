# مجلد المكتبات الخارجية
# Vendor Directory

هذا المجلد مخصص للمكتبات الخارجية والحزم التي يتم تثبيتها عبر Composer أو أي أداة إدارة حزم أخرى.

This directory is dedicated to external libraries and packages installed via Composer or other package managers.

## المكتبات المستخدمة
## Used Libraries

- Stripe PHP SDK
- PayPal PHP SDK
- PHPMailer
- TCPDF (لإنشاء ملفات PDF)
- FPDF (لإنشاء ملفات PDF)
- MPDF (لإنشاء ملفات PDF)
- PHPExcel (للتعامل مع ملفات Excel)
- Carbon (للتعامل مع التواريخ والأوقات)
- Guzzle (لإجراء طلبات HTTP)
- Monolog (لتسجيل الأحداث)
- Symfony Components (مكونات متنوعة)
- Laravel Packages (حزم متنوعة)

## التثبيت
## Installation

يمكن تثبيت هذه المكتبات باستخدام Composer:

```bash
composer require stripe/stripe-php
composer require paypal/rest-api-sdk-php
composer require phpmailer/phpmailer
composer require tecnickcom/tcpdf
composer require setasign/fpdf
composer require mpdf/mpdf
composer require phpoffice/phpspreadsheet
composer require nesbot/carbon
composer require guzzlehttp/guzzle
composer require monolog/monolog
```

## ملاحظة
## Note

يجب تثبيت المكتبات المطلوبة قبل تشغيل المشروع.

Required libraries must be installed before running the project.
