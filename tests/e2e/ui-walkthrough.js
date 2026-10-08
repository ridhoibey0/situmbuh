const { chromium } = require('playwright-core');
const fs = require('fs');
const path = require('path');

const BASE = 'http://127.0.0.1:8765';
const OUT = path.join(__dirname, 'shots');
const CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const results = [];
async function newMobile(browser) {
  const c = await browser.newContext(MOBILE);
  await c.addInitScript(() => { try { sessionStorage.setItem('situba-testimoni-closed', '1'); } catch (e) {} });
  return c;
}
let n = 0;

const MOBILE = { viewport: { width: 390, height: 844 }, deviceScaleFactor: 2, isMobile: true, hasTouch: true, locale: 'id-ID' };
const DESKTOP = { viewport: { width: 1280, height: 800 }, deviceScaleFactor: 1, locale: 'id-ID' };

async function settle(page) {
  await page.waitForLoadState('networkidle').catch(() => {});
  // tutup modal testimoni bila muncul
  await page.waitForTimeout(1500);
  await page.evaluate(() => {
    document.querySelectorAll('.modal.show .btn-close, .modal.show [data-bs-dismiss="modal"]').forEach((b) => b.click());
  }).catch(() => {});
  await page.waitForTimeout(800);
}

async function shot(page, slug, opts = {}) {
  n += 1;
  const file = `${String(n).padStart(2, '0')}-${slug}.png`;
  await page.screenshot({ path: path.join(OUT, file), fullPage: opts.full !== false });
  return file;
}

async function shotEl(page, slug, locator) {
  n += 1;
  const file = `${String(n).padStart(2, '0')}-${slug}.png`;
  await locator.screenshot({ path: path.join(OUT, file) });
  return file;
}

const fuCard = (page) => page.locator('.u-panel', { has: page.locator('h2', { hasText: /^Tindak lanjut$/ }) }).first();
const factorCard = (page) => page.locator('.u-panel', { has: page.locator('h2', { hasText: /Mengapa prioritas/ }) }).first();

async function step(page, group, name, fn) {
  const rec = { group, name, ok: false, note: '', file: null };
  try {
    const r = await fn();
    rec.ok = true;
    if (r && r.note) rec.note = r.note;
    rec.file = r && r.file ? r.file : null;
  } catch (e) {
    rec.note = String(e.message || e).split('\n')[0];
    try { rec.file = await shot(page, 'GAGAL-' + name.replace(/\W+/g, '-').slice(0, 30)); } catch (_) {}
  }
  results.push(rec);
  console.log(rec.ok ? 'OK  ' : 'FAIL', group, '|', name, rec.note ? '| ' + rec.note : '');
}

async function expectText(page, ...texts) {
  const body = await page.locator('body').innerText();
  for (const t of texts) if (!body.toLowerCase().includes(t.toLowerCase())) throw new Error(`teks tidak ditemukan: "${t}"`);
}

async function login(page, phone) {
  await page.goto(BASE + '/login');
  await page.fill('input[name=phone]', phone);
  await page.fill('input[name=password]', 'password');
  await Promise.all([page.waitForNavigation(), page.click('button.btn-submit')]);
  await settle(page);
}

async function visit(page, url, slug, texts = [], full = true) {
  await page.goto(BASE + url);
  await settle(page);
  if (texts.length) await expectText(page, ...texts);
  return { file: await shot(page, slug, { full }) };
}

(async () => {
  fs.rmSync(OUT, { recursive: true, force: true });
  fs.mkdirSync(OUT, { recursive: true });
  const browser = await chromium.launch({ executablePath: CHROME, headless: true });

  // ------------------------------------------------------------ ORANG TUA (mobile)
  let ctx = await newMobile(browser);
  let page = await ctx.newPage();
  const G1 = 'Orang Tua';

  await step(page, G1, 'Halaman login', async () => {
    await page.goto(BASE + '/login');
    await settle(page);
    return { file: await shot(page, 'login', { full: false }) };
  });
  await step(page, G1, 'Login sebagai orang tua', async () => {
    await login(page, '081100000101');
    await expectText(page, 'Menu');
    return { file: await shot(page, 'beranda-orang-tua') };
  });
  await step(page, G1, 'Daftar data anak (lebih dari satu anak)', () => visit(page, '/users/children', 'data-anak', ['Data Anak', 'Aktif']));
  await step(page, G1, 'Tambah anak baru', async () => {
    await page.goto(BASE + '/users/children/create');
    await settle(page);
    await page.fill('#name', 'Anak Uji Coba');
    await page.selectOption('#gender', 'female');
    const bod = new Date(); bod.setMonth(bod.getMonth() - 5);
    await page.fill('#bod', bod.toISOString().slice(0, 10));
    await page.fill('#weight', '3.2'); await page.fill('#height', '49');
    await page.fill('#head_circumference', '34'); await page.fill('#arm_circumference', '11');
    const filled = await shot(page, 'form-tambah-anak');
    await Promise.all([page.waitForNavigation(), page.click('button[type=submit]')]);
    await settle(page);
    await expectText(page, 'Anak Uji Coba', 'berhasil ditambahkan');
    return { file: filled, note: 'Anak tersimpan dan menjadi anak aktif' };
  });
  await step(page, G1, 'Validasi input tidak masuk akal ditolak', async () => {
    await page.goto(BASE + '/users/children/create');
    await settle(page);
    await page.fill('#name', 'Salah Input');
    await page.selectOption('#gender', 'male');
    const bod = new Date(); bod.setMonth(bod.getMonth() - 5);
    await page.fill('#bod', bod.toISOString().slice(0, 10));
    await page.fill('#weight', '900'); await page.fill('#height', '49');
    await page.fill('#head_circumference', '34'); await page.fill('#arm_circumference', '11');
    await page.evaluate(() => document.querySelectorAll('input[type=number]').forEach((i) => { i.removeAttribute('step'); i.removeAttribute('max'); i.removeAttribute('min'); }));
    await page.evaluate(() => document.querySelector('form').setAttribute('novalidate', ''));
    await Promise.all([page.waitForNavigation(), page.click('button[type=submit]')]);
    await settle(page);
    await expectText(page, 'weight');
    return { file: await shot(page, 'validasi-berat-900'), note: 'Server menolak berat 900 kg' };
  });
  // kembali ke anak demo pertama
  await step(page, G1, 'Ringkasan bahasa sederhana + grafik pertumbuhan', async () => {
    await page.goto(BASE + '/users/children');
    await settle(page);
    await page.locator('.u-row:has-text("Indah Permata") form button').click();
    await settle(page);
    await page.goto(BASE + '/users/growth-monitoring');
    await settle(page);
    await expectText(page, 'Indah Permata', 'Perlu dipantau', 'bukan diagnosis');
    return { file: await shot(page, 'pertumbuhan-ringkasan') };
  });
  await step(page, G1, 'Form tambah pengukuran', async () => {
    await page.goto(BASE + '/users/measurement');
    await settle(page);
    const today = new Date().toISOString().slice(0, 10);
    await page.fill('#measured_at', today);
    await page.fill('#weight', '9.4'); await page.fill('#height', '74');
    await page.fill('#head_circumference', '46'); await page.fill('#arm_circumference', '14');
    const filled = await shot(page, 'form-pengukuran');
    await Promise.all([page.waitForNavigation(), page.click('button[type=submit]')]);
    await settle(page);
    await expectText(page, 'Pertumbuhan');
    return { file: filled, note: 'Setelah simpan diarahkan ke halaman pertumbuhan' };
  });
  await step(page, G1, 'Pemilihan skrining KPSP', () => visit(page, '/users/questioner', 'kpsp-menu', [], false));
  await step(page, G1, 'Profil akun orang tua', () => visit(page, '/users/profile', 'profil', ['Nama'], false));
  await step(page, G1, 'Asisten informasi mengenali data anak (saran pertanyaan memakai nama anak)', () => visit(page, '/users/chat-ai', 'chat-ai', ['Asisten Situmbuh', 'Tahu data', 'Jelaskan kondisi pertumbuhan'], false));
  await step(page, G1, 'Orang tua tidak dapat membuka area kader (403)', async () => {
    const res = await page.goto(BASE + '/kader');
    if (res.status() !== 403) throw new Error('status ' + res.status());
    return { file: await shot(page, 'akses-ditolak-403', { full: false }), note: 'HTTP 403' };
  });
  await ctx.close();

  // ------------------------------------------------------------ KADER (mobile)
  ctx = await newMobile(browser);
  page = await ctx.newPage();
  const G2 = 'Kader';

  await step(page, G2, 'Login kader diarahkan ke /kader', async () => {
    await login(page, '081100000001');
    if (!page.url().includes('/kader')) throw new Error('url ' + page.url());
    await expectText(page, 'Anak yang Ditangani');
    return { file: await shot(page, 'kader-daftar-prioritas', { full: false }), note: 'Daftar berurutan skor tertinggi' };
  });
  await step(page, G2, 'Ringkasan dan daftar anak berurutan prioritas', async () => {
    await page.goto(BASE + '/kader');
    await settle(page);
    await expectText(page, 'Prioritas Tinggi', 'Belum diukur');
    const first = await page.locator('a.u-row').first().innerText();
    if (!/Tinggi/.test(first)) throw new Error('anak teratas bukan prioritas tinggi: ' + first.slice(0, 40));
    return { file: await shot(page, 'kader-daftar-penuh'), note: 'Anak teratas berlevel tinggi' };
  });
  await step(page, G2, 'Filter prioritas tinggi', () => visit(page, '/kader?level=tinggi', 'kader-filter-tinggi', ['Prioritas Tinggi']));
  await step(page, G2, 'Filter pemantauan terlewat', () => visit(page, '/kader?missed=1', 'kader-filter-terlewat', ['terlewat']));
  await step(page, G2, 'Detail anak: penjelasan faktor prioritas', async () => {
    await page.goto(BASE + '/kader?search=Naufal');
    await settle(page);
    await page.locator('a.u-row').first().click();
    await settle(page);
    await expectText(page, 'Mengapa prioritas ini?', 'bukan diagnosis');
    return { file: await shotEl(page, 'detail-faktor-prioritas', factorCard(page)) };
  });
  const childUrl = page.url();
  await step(page, G2, 'Membuat tindak lanjut', async () => {
    await page.selectOption('#action_type', 'home_visit');
    await page.fill('#notes', 'Kunjungan rumah dan konseling gizi bersama orang tua');
    const filled = await shotEl(page, 'form-tindak-lanjut', fuCard(page));
    await Promise.all([page.waitForNavigation(), page.click('form[action*="follow-ups"] button[type=submit]')]);
    await settle(page);
    await expectText(page, 'Tindak lanjut dibuat', 'Kunjungan rumah');
    return { file: filled, note: 'Tindak lanjut tersimpan dengan status Belum dikerjakan' };
  });
  await step(page, G2, 'Tindak lanjut tampil pada detail anak', async () => {
    await expectText(page, 'Belum dikerjakan');
    return { file: await shotEl(page, 'tindak-lanjut-dibuat', fuCard(page)) };
  });
  await step(page, G2, 'Menandai tindak lanjut selesai', async () => {
    await page.fill('textarea[name=result_notes]', 'Orang tua sudah dikonseling dan paham jadwal makan');
    await Promise.all([page.waitForNavigation(), page.click('button[name=status][value=done]')]);
    await settle(page);
    await expectText(page, 'Selesai', 'Menunggu pengukuran berikutnya');
    return { file: await shotEl(page, 'tindak-lanjut-selesai', fuCard(page)), note: 'Menunggu pengukuran berikutnya untuk evaluasi' };
  });
  await step(page, G2, 'Mencatat pengukuran berikutnya', async () => {
    await page.fill('#weight', '9.1'); await page.fill('#height', '75');
    const filled = await shot(page, 'form-pengukuran-kader', { full: false });
    await Promise.all([page.waitForNavigation(), page.click('form[action*="measurements"] button[type=submit]')]);
    await settle(page);
    await expectText(page, 'Pengukuran tersimpan');
    return { file: filled };
  });
  await step(page, G2, 'Evaluasi hasil sebelum dan sesudah muncul otomatis', async () => {
    await expectText(page, 'Sebelum:', 'Sesudah:');
    const body = await page.locator('body').innerText();
    const label = /Membaik|Tetap|Memburuk/.exec(body);
    return { file: await shotEl(page, 'evaluasi-sebelum-sesudah', fuCard(page)), note: 'Hasil: ' + (label ? label[0] : '-') };
  });
  await step(page, G2, 'Contoh evaluasi tindak lanjut pada data demo (membaik)', async () => {
    await page.goto(BASE + '/kader?search=Zaki');
    await settle(page);
    await page.locator('a.u-row').first().click();
    await settle(page);
    await expectText(page, 'Membaik');
    return { file: await shotEl(page, 'evaluasi-data-demo', fuCard(page)) };
  });
  await step(page, G2, 'Daftar tindak lanjut (terlambat disorot)', () => visit(page, '/kader/follow-ups', 'daftar-tindak-lanjut', ['Tindak Lanjut', '(terlewat)']));
  await step(page, G2, 'Mendaftarkan anak baru', async () => {
    await page.goto(BASE + '/kader/children/create');
    await settle(page);
    await page.fill('#name', 'Bayi Daftar Kader');
    await page.selectOption('#gender', 'male');
    const bod = new Date(); bod.setMonth(bod.getMonth() - 2);
    await page.fill('#bod', bod.toISOString().slice(0, 10));
    await page.fill('#parent_phone', '081100000102');
    const filled = await shot(page, 'form-daftar-anak');
    await Promise.all([page.waitForNavigation(), page.click('button[type=submit]')]);
    await settle(page);
    await expectText(page, 'Bayi Daftar Kader', 'Anak berhasil didaftarkan', 'Orang tua:');
    return { file: filled, note: 'Anak otomatis tertaut ke akun orang tua lewat nomor HP' };
  });
  await step(page, G2, 'Anak yang belum pernah diukur ditandai', async () => {
    await page.goto(BASE + '/kader?search=Bayi');
    await settle(page);
    await expectText(page, 'Belum pernah diukur');
    return { file: await shot(page, 'belum-pernah-diukur', { full: false }) };
  });
  await step(page, G2, 'Mendaftarkan anak dengan orang tua yang belum punya akun', async () => {
    await page.goto(BASE + '/kader/children/create');
    await settle(page);
    await page.fill('#name', 'Bayi Menunggu');
    await page.selectOption('#gender', 'female');
    const bod = new Date(); bod.setMonth(bod.getMonth() - 1);
    await page.fill('#bod', bod.toISOString().slice(0, 10));
    await page.fill('#parent_phone', '0811-8888-7777');
    await Promise.all([page.waitForNavigation(), page.click('button[type=submit]')]);
    await settle(page);
    await expectText(page, 'Bayi Menunggu', 'Orang tua belum punya akun', '081188887777');
    return { file: await shot(page, 'orang-tua-belum-punya-akun', { full: false }), note: 'Nomor HP disimpan, anak menunggu akun orang tua' };
  });
  await step(page, G2, 'Tombol hubungi orang tua lewat WhatsApp', async () => {
    const link = page.locator('a[href^="https://wa.me/"]');
    if ((await link.count()) === 0) throw new Error('tautan WhatsApp tidak ada');
    const href = await link.first().getAttribute('href');
    if (!href.includes('628118888777')) throw new Error('nomor tidak sesuai: ' + href);
    return { file: await shot(page, 'tombol-whatsapp', { full: false }), note: 'Pesan netral terisi otomatis' };
  });
  await ctx.close();

  // ------------------------------------------------------------ ORANG TUA 2 (mobile)
  ctx = await newMobile(browser);
  page = await ctx.newPage();
  const G5 = 'Orang Tua (anak berisiko dan KPSP)';
  await step(page, G5, 'Login orang tua kedua', async () => {
    await login(page, '081100000102');
    await expectText(page, 'Menu');
    return {};
  });
  await step(page, G5, 'Anak yang didaftarkan kader muncul di akun orang tua', async () => {
    await page.goto(BASE + '/users/children');
    await settle(page);
    await expectText(page, 'Bayi Daftar Kader', 'Mawar Sari');
    return { file: await shot(page, 'orang-tua-dua-anak-dari-kader') };
  });
  await step(page, G5, 'Ringkasan untuk anak prioritas tinggi (bahasa sederhana)', async () => {
    await page.goto(BASE + '/users/children');
    await settle(page);
    await page.locator('.u-row:has-text("Mawar Sari") form button').click();
    await settle(page);
    await page.goto(BASE + '/users/growth-monitoring');
    await settle(page);
    await expectText(page, 'Perlu perhatian segera', 'bukan diagnosis');
    const body = await page.locator('body').innerText();
    if (/skor\s*\d/i.test(body)) throw new Error('angka skor tampil untuk orang tua');
    return { file: await shot(page, 'ringkasan-orang-tua-tinggi', { full: false }), note: 'Tanpa angka skor' };
  });
  await step(page, G5, 'Mengisi KPSP dan melihat hasil', async () => {
    await page.goto(BASE + '/users/children');
    await settle(page);
    await page.locator('.u-row:has-text("Bayi Daftar Kader") form button').click();
    await settle(page);
    await page.goto(BASE + '/users/questioner/1');
    await settle(page);
    const n = await page.locator('input[type=radio][value=true]').count();
    if (n === 0) throw new Error('tidak ada pertanyaan KPSP untuk usia anak');
    await page.evaluate(() => document.querySelectorAll('input[type=radio][value=true]').forEach((r) => { r.checked = true; r.dispatchEvent(new Event('change', { bubbles: true })); }));
    const form = await shot(page, 'kpsp-pertanyaan', { full: false });
    await Promise.all([page.waitForNavigation(), page.locator('form button[type=submit], form input[type=submit]').last().click()]);
    await settle(page);
    if (!page.url().includes('/questioner/result/')) throw new Error('url ' + page.url());
    await shot(page, 'kpsp-hasil');
    return { file: form, note: n + ' pertanyaan dijawab, hasil tersimpan' };
  });
  await ctx.close();

  // ------------------------------------------------------------ TENAGA KESEHATAN (mobile)
  ctx = await newMobile(browser);
  page = await ctx.newPage();
  const G3 = 'Tenaga Kesehatan';
  await step(page, G3, 'Login nakes dan melihat anak yang ditugaskan', async () => {
    await login(page, '081100000002');
    await expectText(page, 'Anak yang Ditangani');
    return { file: await shot(page, 'nakes-daftar', { full: false }) };
  });
  await step(page, G3, 'Nakes hanya meninjau (tanpa form pencatatan)', async () => {
    await page.locator('a.u-row').first().click();
    await settle(page);
    await expectText(page, 'Mengapa prioritas ini?');
    const body = await page.locator('body').innerText();
    if (/catat pengukuran|buat tindak lanjut/i.test(body)) throw new Error('form tulis muncul untuk nakes');
    return { file: await shot(page, 'nakes-detail-readonly'), note: 'Form pencatatan tidak tampil' };
  });
  await step(page, G3, 'Nakes tidak dapat mendaftarkan anak (403)', async () => {
    const res = await page.goto(BASE + '/kader/children/create');
    if (res.status() !== 403) throw new Error('status ' + res.status());
    return { file: await shot(page, 'nakes-403', { full: false }), note: 'HTTP 403' };
  });
  await ctx.close();

  // ------------------------------------------------------------ REGISTRASI ORANG TUA BARU (mobile)
  ctx = await newMobile(browser);
  page = await ctx.newPage();
  const G6 = 'Registrasi Orang Tua';
  await step(page, G6, 'Halaman pendaftaran akun', async () => {
    await page.goto(BASE + '/register');
    await settle(page);
    await expectText(page, 'Buat akun');
    return { file: await shot(page, 'registrasi', { full: false }) };
  });
  await step(page, G6, 'Mendaftar akun baru dan anak langsung tertaut otomatis', async () => {
    await page.fill('#name', 'Ibu Menunggu');
    await page.fill('#phone', '081188887777');
    await page.fill('#password', 'rahasia123');
    await page.fill('#password_confirmation', 'rahasia123');
    await Promise.all([page.waitForNavigation(), page.click('button.btn-submit')]);
    await settle(page);
    if (!page.url().includes('/users')) throw new Error('url ' + page.url());
    await expectText(page, 'Bayi Menunggu');
    return { file: await shot(page, 'beranda-anak-tertaut'), note: 'Anak yang didaftarkan kader langsung muncul' };
  });
  await ctx.close();

  // ------------------------------------------------------------ ADMIN (desktop)
  ctx = await browser.newContext(DESKTOP);
  page = await ctx.newPage();
  const G4 = 'Administrator';
  await step(page, G4, 'Login admin diarahkan ke dashboard', async () => {
    await login(page, '081100000009');
    if (!page.url().includes('/admin/dashboard')) throw new Error('url ' + page.url());
    return { file: await shot(page, 'admin-dashboard') };
  });
  await step(page, G4, 'Laporan stunting', () => visit(page, '/admin/stunting', 'admin-stunting', ['Pendek']));
  await step(page, G4, 'Detail anak pada laporan stunting', async () => {
    await page.locator('a[href*="/admin/stunting/"]').first().click();
    await settle(page);
    return { file: await shot(page, 'admin-stunting-detail') };
  });
  await step(page, G4, 'Pengelolaan pengguna dan peran', async () => {
    await page.goto(BASE + '/admin/users');
    await settle(page);
    await page.waitForTimeout(1500);
    return { file: await shot(page, 'admin-pengguna') };
  });
  await step(page, G4, 'Edit peran pengguna (kader/nakes tersedia)', async () => {
    await page.goto(BASE + '/admin/users/2/edit');
    await settle(page);
    const options = await page.locator('#roles option').allInnerTexts();
    if (!options.some((o) => /Kader/.test(o)) || !options.some((o) => /Kesehatan/.test(o))) throw new Error('opsi: ' + options.join(','));
    return { file: await shot(page, 'admin-edit-peran') };
  });
  await step(page, G4, 'Daftar anak dan petugas', () => visit(page, '/admin/children', 'admin-anak', ['Anak dan penugasan', 'Bayi Menunggu']));
  await step(page, G4, 'Menugaskan nakes pada anak dan menautkan orang tua', async () => {
    await page.goto(BASE + '/admin/children?search=Aisyah');
    await settle(page);
    await page.locator('a.adm-link', { hasText: 'Aisyah' }).first().click();
    await settle(page);
    await page.selectOption('select[name=user_id]', { label: 'Bidan Demo (Tenaga Kesehatan)' });
    await Promise.all([page.waitForNavigation(), page.click('button:has-text("Tugaskan")')]);
    await settle(page);
    await expectText(page, 'Bidan Demo ditugaskan', 'Kader Demo');
    return { file: await shot(page, 'admin-penugasan'), note: 'Nakes tercatat sebagai petugas' };
  });
  await step(page, G4, 'Pengelolaan pertanyaan KPSP', async () => {
    await page.goto(BASE + '/admin/questions');
    await settle(page);
    await page.waitForTimeout(1500);
    return { file: await shot(page, 'admin-kpsp', { full: false }) };
  });
  await step(page, G4, 'Pengelolaan artikel', async () => {
    await page.goto(BASE + '/admin/blogs');
    await settle(page);
    await page.waitForTimeout(1500);
    return { file: await shot(page, 'admin-artikel', { full: false }) };
  });
  await step(page, G4, 'Halaman rekap Excel', () => visit(page, '/admin/dashboard/rekap', 'admin-rekap', [], false));
  await ctx.close();

  await browser.close();
  fs.writeFileSync(path.join(__dirname, 'e2e-results.json'), JSON.stringify(results, null, 2));
  const fails = results.filter((r) => !r.ok).length;
  console.log(`\n${results.length - fails}/${results.length} langkah lulus`);
})();
