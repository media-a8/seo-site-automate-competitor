# seo-site-automate-competitor

โครง PHP front controller สำหรับ SEO landing page แบบ static ที่ xCloud clone ไปทุกไซต์

Cloudflare ชี้โดเมนมาที่ xCloud จากนั้น xCloud clone repo นี้เป็น document root แล้ว n8n เติม `config/site.json`, `pages/`, `meta/`, `partials/`, `posts/` และ `assets/css/theme.css`

บทความเก็บใน `posts/` แต่เปิดที่ `/articles` และ `/articles/{slug}` ชื่อโฟลเดอร์ไม่ซ้ำกับ URL เพื่อกัน redirect 301 ของเว็บเซิร์ฟเวอร์ เมนูบทความและหน้ารายการโผล่เมื่อมีบทความอย่างน้อย 1 บทความ

ขอบเขตคือหน้า landing แบบ static เท่านั้น ไม่มี login, ฐานข้อมูล หรือระบบหลังบ้าน
