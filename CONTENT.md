# ไฟล์ที่ pipeline วางทับ

วางไฟล์ที่ n8n สร้างไว้ที่นี่: `partials/nav.html`, `partials/footer.html`, `pages/*.html`, `meta/*.json`, `articles/*`, `assets/css/theme.css`, `config/site.json`

## URL → ไฟล์

| URL | ไฟล์ |
|-----|------|
| `/` | `pages/homepage.html` + `meta/homepage.json` |
| `/about`, `/contact`, … | `pages/{slug}.html` + `meta/{slug}.json` |
| `/articles` | `pages/articles.html` + `meta/articles.json` แล้วแทรกรายการบทความ |
| `/articles/{slug}` | `articles/{slug}.html` + `articles/{slug}.json` |
| อื่นๆ | 404 (หน้า `notfound` เดิม) |

slug ของบทความใช้ได้แค่ `a-z`, `0-9` และขีดกลาง บทความจะนับก็ต่อเมื่อมีทั้ง `.html` และ `.json` คู่กัน

## บทความ

`articles/{slug}.html` คือ fragment ของ body เหมือน `pages/*.html`

`articles/{slug}.json` ใช้ฟิลด์เดียวกับ `meta/*.json`:

- `meta_title`
- `meta_description`
- `social_tags.og_title`, `og_description`, `og_image`, `og_type`
- `date` (ไม่บังคับ, `YYYY-MM-DD`) ใช้เรียงรายการ ใหม่สุดก่อน

หน้ารายการ `/articles` ต้องมี `pages/articles.html` และ `meta/articles.json` (n8n สร้างให้) ใน HTML ใส่ marker `<!--ARTICLE_LIST-->` ระบบจะแทนที่ด้วยการ์ดบทความ ถ้าไม่มี marker รายการจะถูกต่อท้าย body
