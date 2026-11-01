# ไฟล์ที่ pipeline วางทับ

วางไฟล์ที่ n8n สร้างไว้ที่นี่: `partials/nav.html`, `partials/footer.html`, `pages/*.html`, `meta/*.json`, `posts/*`, `assets/css/theme.css`, `config/site.json`

## URL → ไฟล์

| URL | ไฟล์ |
|-----|------|
| `/` | `pages/homepage.html` + `meta/homepage.json` |
| `/about`, `/contact`, … | `pages/{slug}.html` + `meta/{slug}.json` |
| `/articles` | หน้ารายการบทความ มีได้เมื่อมีบทความอย่างน้อย 1 บทความ ไม่งั้น 404 |
| `/articles/{slug}` | `posts/{slug}.html` + `posts/{slug}.json` |
| อื่นๆ | 404 (หน้า `notfound` เดิม) |

slug ของบทความใช้ได้แค่ `a-z`, `0-9` และขีดกลาง บทความจะนับก็ต่อเมื่อมีทั้ง `.html` และ `.json` คู่กัน

## บทความ

ไฟล์อยู่ที่ `posts/` ไม่ใช่ `articles/` เพราะ URL สาธารณะคือ `/articles` ถ้าโฟลเดอร์บนดิสก์ชื่อเดียวกับ URL เว็บเซิร์ฟเวอร์จะ redirect 301 ไป `/articles/` ก่อนถึง PHP และ canonical จะไม่ตรงกับ URL

`posts/{slug}.html` คือ fragment ของ body เหมือน `pages/*.html`

`posts/{slug}.json` ใช้ฟิลด์เดียวกับ `meta/*.json`:

- `meta_title`
- `meta_description`
- `social_tags.og_title`, `og_description`, `og_image`, `og_type`
- `date` (ไม่บังคับ, `YYYY-MM-DD`) ใช้เรียงรายการ ใหม่สุดก่อน

เมื่อมีบทความอย่างน้อย 1 บทความ เมนูจะได้ลิงก์ `/articles` อัตโนมัติ และ `/articles` ตอบ 200 ถ้ายังไม่มีบทความ ลิงก์นี้จะไม่ถูกแทรก และ `/articles` ตอบ 404 แม้จะมี `pages/articles.html`

`pages/articles.html` และ `meta/articles.json` เป็นตัวเลือก override ของไซต์ n8n ไม่ต้องสร้าง ถ้าไม่มี ระบบใช้หน้าและ meta สำเร็จรูป marker `<!--ARTICLE_LIST-->` ใช้เฉพาะตอน override ถ้าไม่มี marker รายการจะถูกต่อท้าย body
