<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Situmbuh - Platform Pemantauan Tumbuh Kembang Anak</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            color: #0F172A;
            background: #fff;
            line-height: 1.6;
            overflow-x: hidden;
        }

        a { text-decoration: none; color: inherit; }

        /* ── Navbar ─────────────────────────────────── */
        .navbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #fff;
            border-bottom: 1px solid #E2E8F0;
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 120px;
        }

        .navbar-logo {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .navbar-logo-text {
            font-size: 20px;
            font-weight: 700;
            color: #0F172A;
        }

        .navbar-links {
            display: flex;
            align-items: center;
            gap: 32px;
            list-style: none;
        }

        .navbar-links a {
            font-size: 15px;
            font-weight: 500;
            color: #475569;
            transition: color .2s;
        }

        .navbar-links a:hover { color: #16A34A; }

        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-ghost {
            padding: 10px 20px;
            border-radius: 8px;
            background: #F1F5F9;
            font-size: 14px;
            font-weight: 600;
            color: #475569;
            transition: background .2s;
        }

        .btn-ghost:hover { background: #E2E8F0; }

        .btn-primary {
            padding: 10px 20px;
            border-radius: 8px;
            background: #16A34A;
            font-size: 14px;
            font-weight: 600;
            color: #fff;
            transition: background .2s;
        }

        .btn-primary:hover { background: #15803D; }

        .hamburger {
            display: none;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
            padding: 4px;
        }

        .hamburger span {
            display: block;
            width: 24px;
            height: 2px;
            background: #475569;
            border-radius: 2px;
            transition: .3s;
        }

        /* ── Mobile nav drawer ──────────────────────── */
        .mobile-menu {
            display: none;
            flex-direction: column;
            background: #fff;
            border-bottom: 1px solid #E2E8F0;
            padding: 16px 24px;
            gap: 16px;
        }

        .mobile-menu.open { display: flex; }

        .mobile-menu a {
            font-size: 15px;
            font-weight: 500;
            color: #475569;
            padding: 8px 0;
            border-bottom: 1px solid #F1F5F9;
        }

        .mobile-menu .mobile-actions {
            display: flex;
            gap: 12px;
            padding-top: 8px;
        }

        .mobile-actions .btn-ghost,
        .mobile-actions .btn-primary {
            flex: 1;
            text-align: center;
        }

        /* ── Hero ───────────────────────────────────── */
        #hero {
            background: linear-gradient(180deg, #F0FDF4 0%, #E0F2FE 100%);
            min-height: 640px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 80px 120px;
            gap: 48px;
        }

        .hero-left {
            display: flex;
            flex-direction: column;
            gap: 24px;
            max-width: 560px;
            flex: 1;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #DCFCE7;
            border-radius: 9999px;
            padding: 6px 12px;
            align-self: flex-start;
        }

        .hero-badge-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #16A34A;
            flex-shrink: 0;
        }

        .hero-badge span {
            font-size: 13px;
            font-weight: 600;
            color: #15803D;
        }

        .hero-title {
            font-size: 48px;
            font-weight: 800;
            color: #0F172A;
            line-height: 1.15;
        }

        .hero-subtitle {
            font-size: 17px;
            color: #475569;
            line-height: 1.6;
        }

        .hero-btns {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-hero-primary {
            display: inline-block;
            padding: 14px 28px;
            border-radius: 10px;
            background: #16A34A;
            font-size: 15px;
            font-weight: 700;
            color: #fff;
            box-shadow: 0 4px 16px #16A34A40;
            transition: background .2s, transform .2s;
        }

        .btn-hero-primary:hover { background: #15803D; transform: translateY(-1px); }

        .btn-hero-outline {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 28px;
            border-radius: 10px;
            background: #fff;
            border: 2px solid #E2E8F0;
            font-size: 15px;
            font-weight: 600;
            color: #475569;
            transition: border-color .2s;
        }

        .btn-hero-outline:hover { border-color: #16A34A; color: #16A34A; }

        .hero-trust {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .hero-trust span {
            font-size: 13px;
            font-weight: 500;
            color: #16A34A;
        }

        .hero-right {
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 1;
            max-width: 480px;
        }

        .hero-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 8px 32px #0000001A, 0 2px 8px #00000008;
            width: 100%;
            max-width: 420px;
            overflow: hidden;
        }

        .hero-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            height: 52px;
            background: #F8FAFC;
            border-bottom: 1px solid #E2E8F0;
        }

        .hero-card-header-title {
            font-size: 14px;
            font-weight: 700;
            color: #0F172A;
        }

        .badge-green {
            background: #DCFCE7;
            border-radius: 9999px;
            padding: 4px 10px;
            font-size: 12px;
            font-weight: 600;
            color: #15803D;
        }

        .hero-card-body {
            padding: 16px 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .hero-chart {
            background: #F8FAFC;
            border-radius: 8px;
            height: 180px;
            display: flex;
            align-items: flex-end;
            padding: 16px;
            gap: 8px;
            overflow: hidden;
        }

        .chart-bar-group {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            flex: 1;
        }

        .chart-bar {
            width: 100%;
            border-radius: 4px 4px 0 0;
            transition: height .3s;
        }

        .chart-label {
            font-size: 10px;
            color: #94A3B8;
            text-align: center;
        }

        .chart-line-svg {
            width: 100%;
            height: 140px;
        }

        .hero-card-stats {
            display: flex;
            gap: 8px;
        }

        .hero-stat {
            flex: 1;
            background: #F8FAFC;
            border-radius: 8px;
            padding: 10px 12px;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .hero-stat-val {
            font-size: 16px;
            font-weight: 700;
            color: #0F172A;
        }

        .hero-stat-lbl {
            font-size: 11px;
            color: #64748B;
        }

        /* ── Sections shared ────────────────────────── */
        .section {
            padding: 80px 120px;
        }

        .section-alt {
            background: #F8FAFC;
        }

        .section-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 12px;
            margin-bottom: 48px;
        }

        .section-label {
            display: inline-block;
            border-radius: 9999px;
            padding: 6px 14px;
            font-size: 13px;
            font-weight: 600;
        }

        .label-green { background: #DCFCE7; color: #15803D; }
        .label-blue  { background: #DBEAFE; color: #1D4ED8; }
        .label-purple{ background: #F5F3FF; color: #7C3AED; }

        .section-title {
            font-size: 36px;
            font-weight: 800;
            color: #0F172A;
            line-height: 1.2;
            max-width: 700px;
        }

        .section-sub {
            font-size: 16px;
            color: #64748B;
            line-height: 1.6;
            max-width: 600px;
        }

        /* ── Features ───────────────────────────────── */
        #features { background: #fff; }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
        }

        .feature-card {
            border-radius: 16px;
            padding: 28px 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .feat-card-green  { background: #F0FDF4; border: 1px solid #BBF7D0; }
        .feat-card-blue   { background: #EFF6FF; border: 1px solid #BFDBFE; }
        .feat-card-orange { background: #FFF7ED; border: 1px solid #FED7AA; }
        .feat-card-purple { background: #F5F3FF; border: 1px solid #DDD6FE; }

        .feat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .feat-icon-green  { background: #16A34A; }
        .feat-icon-blue   { background: #2563EB; }
        .feat-icon-orange { background: #EA580C; }
        .feat-icon-purple { background: #7C3AED; }

        .feat-icon svg { color: #fff; width: 24px; height: 24px; }

        .feat-card-title {
            font-size: 17px;
            font-weight: 700;
            color: #0F172A;
        }

        .feat-card-desc {
            font-size: 14px;
            color: #475569;
            line-height: 1.6;
        }

        /* ── How It Works ───────────────────────────── */
        #how-it-works { background: #F8FAFC; }

        .steps-row {
            display: flex;
            align-items: flex-start;
            gap: 0;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
            flex: 1;
            padding: 0 24px;
            text-align: center;
        }

        .step-num {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 800;
            color: #fff;
            flex-shrink: 0;
        }

        .step-num-green  { background: linear-gradient(135deg, #22C55E, #16A34A); box-shadow: 0 4px 16px #22C55E30; }
        .step-num-blue   { background: linear-gradient(135deg, #38BDF8, #0EA5E9); box-shadow: 0 4px 16px #0EA5E930; }
        .step-num-purple { background: linear-gradient(135deg, #A78BFA, #7C3AED); box-shadow: 0 4px 16px #7C3AED30; }

        .step-title { font-size: 18px; font-weight: 700; color: #0F172A; }

        .step-desc {
            font-size: 14px;
            color: #64748B;
            line-height: 1.6;
        }

        .step-arrow {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 64px;
            flex-shrink: 0;
            color: #CBD5E1;
        }

        .step-arrow svg { width: 28px; height: 28px; }

        /* ── Benefits ───────────────────────────────── */
        #benefits { background: #fff; }

        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        .benefit-card {
            border-radius: 16px;
            padding: 32px 28px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            border: 1px solid #E2E8F0;
        }

        .benefit-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .benf-icon-green  { background: #DCFCE7; color: #16A34A; }
        .benf-icon-blue   { background: #DBEAFE; color: #2563EB; }
        .benf-icon-yellow { background: #FEF3C7; color: #D97706; }

        .benefit-icon svg { width: 24px; height: 24px; }

        .benefit-title-text {
            font-size: 17px;
            font-weight: 700;
            color: #0F172A;
        }

        .benefit-desc-text {
            font-size: 14px;
            color: #64748B;
            line-height: 1.6;
        }

        /* ── Target Users ───────────────────────────── */
        #target-users { background: #F8FAFC; }

        .users-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        .user-card {
            background: #fff;
            border-radius: 20px;
            border: 1px solid #E2E8F0;
            box-shadow: 0 4px 16px #0000000D;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            gap: 20px;
            padding: 32px 28px;
        }

        .user-card-img {
            border-radius: 12px;
            height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-img-green  { background: linear-gradient(135deg, #BBF7D0, #6EE7B7); color: #16A34A; }
        .user-img-blue   { background: linear-gradient(135deg, #BFDBFE, #93C5FD); color: #2563EB; }
        .user-img-purple { background: linear-gradient(135deg, #DDD6FE, #C4B5FD); color: #7C3AED; }

        .user-card-img svg { width: 56px; height: 56px; }

        .user-card-tag {
            display: inline-block;
            border-radius: 9999px;
            padding: 5px 12px;
            font-size: 12px;
            font-weight: 600;
            align-self: flex-start;
        }

        .tag-green  { background: #DCFCE7; color: #15803D; }
        .tag-blue   { background: #DBEAFE; color: #1D4ED8; }
        .tag-purple { background: #F5F3FF; color: #7C3AED; }

        .user-card-title {
            font-size: 18px;
            font-weight: 700;
            color: #0F172A;
        }

        .user-card-desc {
            font-size: 14px;
            color: #64748B;
            line-height: 1.6;
        }

        /* ── CTA ────────────────────────────────────── */
        #cta {
            background: linear-gradient(135deg, #16A34A, #0EA5E9);
            padding: 80px 120px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 28px;
            text-align: center;
        }

        .cta-title {
            font-size: 40px;
            font-weight: 800;
            color: #fff;
            line-height: 1.2;
            max-width: 780px;
        }

        .cta-sub {
            font-size: 17px;
            color: #D1FAE5;
            line-height: 1.6;
            max-width: 640px;
        }

        .cta-btns {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn-cta-white {
            display: inline-block;
            padding: 16px 32px;
            border-radius: 10px;
            background: #fff;
            font-size: 16px;
            font-weight: 700;
            color: #16A34A;
            box-shadow: 0 4px 20px #00000020;
            transition: transform .2s;
        }

        .btn-cta-white:hover { transform: translateY(-2px); }

        .btn-cta-outline {
            display: inline-block;
            padding: 16px 32px;
            border-radius: 10px;
            border: 2px solid rgba(255,255,255,.5);
            background: transparent;
            font-size: 16px;
            font-weight: 600;
            color: #fff;
            transition: background .2s;
        }

        .btn-cta-outline:hover { background: rgba(255,255,255,.1); }

        .cta-stats {
            display: flex;
            align-items: center;
            gap: 48px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .cta-stat { display: flex; flex-direction: column; align-items: center; gap: 4px; }

        .cta-stat-val { font-size: 24px; font-weight: 800; color: #fff; }

        .cta-stat-lbl { font-size: 13px; color: #BBF7D0; }

        .cta-stat-div { width: 1px; height: 48px; background: rgba(255,255,255,.3); }

        /* ── Footer ─────────────────────────────────── */
        footer {
            background: #0F172A;
        }

        .footer-main {
            display: flex;
            gap: 48px;
            padding: 56px 120px 48px;
        }

        .footer-brand {
            display: flex;
            flex-direction: column;
            gap: 16px;
            width: 280px;
            flex-shrink: 0;
        }

        .footer-logo {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .footer-logo-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: linear-gradient(135deg, #22C55E, #0EA5E9);
            flex-shrink: 0;
        }

        .footer-logo-text { font-size: 20px; font-weight: 700; color: #fff; }

        .footer-tagline {
            font-size: 14px;
            color: #94A3B8;
            line-height: 1.7;
        }

        .footer-social {
            display: flex;
            gap: 10px;
        }

        .footer-soc-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: #1E293B;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94A3B8;
            transition: background .2s, color .2s;
        }

        .footer-soc-btn:hover { background: #334155; color: #fff; }
        .footer-soc-btn svg { width: 16px; height: 16px; }

        .footer-nav {
            display: flex;
            flex-direction: column;
            gap: 16px;
            flex: 1;
        }

        .footer-nav-title { font-size: 13px; font-weight: 700; color: #fff; }

        .footer-nav a {
            font-size: 14px;
            color: #94A3B8;
            transition: color .2s;
        }

        .footer-nav a:hover { color: #fff; }

        .footer-contact { display: flex; flex-direction: column; gap: 16px; flex: 1.2; }

        .footer-contact-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #94A3B8;
            font-size: 14px;
        }

        .footer-contact-item svg { width: 15px; height: 15px; color: #22C55E; flex-shrink: 0; }

        .footer-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 56px;
            padding: 0 120px;
            border-top: 1px solid #1E293B;
        }

        .footer-copy { font-size: 13px; color: #64748B; }

        .footer-bottom-links {
            display: flex;
            gap: 20px;
        }

        .footer-bottom-links a {
            font-size: 13px;
            color: #64748B;
            transition: color .2s;
        }

        .footer-bottom-links a:hover { color: #fff; }

        /* ── Urgensi ───────────────────────────────── */
        #urgensi { background: #fff; }
        .urgency-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; max-width: 1100px; margin: 0 auto; }
        .urgency-card { background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 16px; padding: 28px; }
        .urgency-val { display: block; font-size: 40px; font-weight: 800; color: #16A34A; letter-spacing: -0.02em; line-height: 1.1; }
        .urgency-lbl { display: block; margin-top: 8px; font-size: 15px; font-weight: 600; color: #0F172A; }
        .urgency-note { display: block; margin-top: 6px; font-size: 13px; color: #64748B; line-height: 1.5; }
        .urgency-source { max-width: 1100px; margin: 24px auto 0; font-size: 13px; color: #64748B; text-align: center; line-height: 1.6; }
        .urgency-source a { color: #16A34A; }
        .urgency-gap { max-width: 1100px; margin: 32px auto 0; background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 16px; padding: 24px 28px; color: #14532D; font-size: 16px; line-height: 1.6; }

        /* ── Footer ─────────────────────────────────── */
        footer {
            background: #0F172A;
        }

        .footer-main {
            display: flex;
            gap: 48px;
            padding: 56px 120px 48px;
        }

        .footer-brand {
            display: flex;
            flex-direction: column;
            gap: 16px;
            width: 280px;
            flex-shrink: 0;
        }

        .footer-logo {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .footer-logo-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: linear-gradient(135deg, #22C55E, #0EA5E9);
            flex-shrink: 0;
        }

        .footer-logo-text { font-size: 20px; font-weight: 700; color: #fff; }

        .footer-tagline {
            font-size: 14px;
            color: #94A3B8;
            line-height: 1.7;
        }

        .footer-social {
            display: flex;
            gap: 10px;
        }

        .footer-soc-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: #1E293B;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94A3B8;
            transition: background .2s, color .2s;
        }

        .footer-soc-btn:hover { background: #334155; color: #fff; }
        .footer-soc-btn svg { width: 16px; height: 16px; }

        .footer-nav {
            display: flex;
            flex-direction: column;
            gap: 16px;
            flex: 1;
        }

        .footer-nav-title { font-size: 13px; font-weight: 700; color: #fff; }

        .footer-nav a {
            font-size: 14px;
            color: #94A3B8;
            transition: color .2s;
        }

        .footer-nav a:hover { color: #fff; }

        .footer-contact { display: flex; flex-direction: column; gap: 16px; flex: 1.2; }

        .footer-contact-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #94A3B8;
            font-size: 14px;
        }

        .footer-contact-item svg { width: 15px; height: 15px; color: #22C55E; flex-shrink: 0; }

        .footer-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 56px;
            padding: 0 120px;
            border-top: 1px solid #1E293B;
        }

        .footer-copy { font-size: 13px; color: #64748B; }

        .footer-bottom-links {
            display: flex;
            gap: 20px;
        }

        .footer-bottom-links a {
            font-size: 13px;
            color: #64748B;
            transition: color .2s;
        }

        .footer-bottom-links a:hover { color: #fff; }

        /* ── Responsive ─────────────────────────────── */
        @media (max-width: 1200px) {
            .navbar { padding: 0 48px; }
            #hero { padding: 60px 48px; }
            .section { padding: 64px 48px; }
            #cta { padding: 64px 48px; }
            .footer-main { padding: 48px 48px 40px; }
            .footer-bottom { padding: 0 48px; }
            .features-grid { grid-template-columns: repeat(2, 1fr); }
            .benefits-grid { grid-template-columns: repeat(3, 1fr); }
            .urgency-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 900px) {
            .navbar { padding: 0 24px; }
            .navbar-links, .navbar-actions { display: none; }
            .hamburger { display: flex; }

            #hero { flex-direction: column; padding: 48px 24px; min-height: auto; }
            .hero-left { max-width: 100%; }
            .hero-title { font-size: 36px; }
            .hero-right { max-width: 100%; width: 100%; }

            .section { padding: 56px 24px; }

            .features-grid { grid-template-columns: repeat(2, 1fr); }
            .benefits-grid { grid-template-columns: repeat(2, 1fr); }
            .urgency-grid { grid-template-columns: 1fr; }

            .steps-row { flex-direction: column; align-items: center; gap: 32px; }
            .step-arrow { transform: rotate(90deg); height: auto; width: auto; }

            .users-grid { grid-template-columns: 1fr; }

            #cta { padding: 56px 24px; }
            .cta-title { font-size: 28px; }
            .cta-stats { gap: 24px; }
            .cta-stat-div { display: none; }

            .footer-main { flex-direction: column; padding: 40px 24px 32px; gap: 32px; }
            .footer-brand { width: 100%; }
            .footer-bottom { flex-direction: column; gap: 12px; height: auto; padding: 16px 24px; text-align: center; }
            .footer-bottom-links { flex-wrap: wrap; justify-content: center; }
        }

        @media (max-width: 600px) {
            .features-grid { grid-template-columns: 1fr; }
            .benefits-grid { grid-template-columns: 1fr; }
            .urgency-grid { grid-template-columns: 1fr; }
            .hero-title { font-size: 28px; }
            .section-title { font-size: 26px; }
            .cta-title { font-size: 24px; }
        }
    </style>
</head>
<body>

<!-- ╔══════════════════════════════╗ -->
<!-- ║         NAVBAR               ║ -->
<!-- ╚══════════════════════════════╝ -->
<nav class="navbar">
    <a href="{{ url('/') }}" class="navbar-logo">
        <span class="navbar-logo-text">Situmbuh</span>
    </a>

    <ul class="navbar-links">
        <li><a href="#features">Fitur</a></li>
        <li><a href="#how-it-works">Cara Kerja</a></li>
        <li><a href="#benefits">Manfaat</a></li>
        <li><a href="#target-users">Pengguna</a></li>
        <li><a href="#urgensi">Urgensi</a></li>
    </ul>

    <div class="navbar-actions">
        <a href="{{ route('login') }}" class="btn-ghost">Masuk</a>
        <a href="{{ route('register') }}" class="btn-primary">Daftar Gratis</a>
    </div>

    <div class="hamburger" id="hamburger" onclick="toggleMenu()" aria-label="Menu">
        <span></span><span></span><span></span>
    </div>
</nav>

<div class="mobile-menu" id="mobileMenu">
    <a href="#features">Fitur</a>
    <a href="#how-it-works">Cara Kerja</a>
    <a href="#benefits">Manfaat</a>
    <a href="#target-users">Pengguna</a>
    <a href="#urgensi">Urgensi</a>
    <div class="mobile-actions">
        <a href="{{ route('login') }}" class="btn-ghost">Masuk</a>
        <a href="{{ route('register') }}" class="btn-primary">Daftar Gratis</a>
    </div>
</div>

<!-- ╔══════════════════════════════╗ -->
<!-- ║           HERO               ║ -->
<!-- ╚══════════════════════════════╝ -->
<section id="hero">
    <div class="hero-left">
        <div class="hero-badge">
            <div class="hero-badge-dot"></div>
            <span>Berbasis Standar WHO</span>
        </div>

        <h1 class="hero-title">Pantau Tumbuh Kembang Anak Secara Akurat</h1>

        <p class="hero-subtitle">
            Dari deteksi dini, penentuan prioritas, tindak lanjut, sampai evaluasi hasilnya.
            Dipakai bersama oleh orang tua, kader posyandu, dan tenaga kesehatan.
        </p>

        <div class="hero-btns">
            <a href="{{ route('register') }}" class="btn-hero-primary">Mulai Sekarang</a>
            <a href="#how-it-works" class="btn-hero-outline">Pelajari Lebih Lanjut</a>
        </div>

    </div>

    <div class="hero-right">
        <div class="hero-card">
            <div class="hero-card-header">
                <span class="hero-card-header-title">Contoh tampilan grafik</span>
                <span class="badge-green">Normal</span>
            </div>
            <div class="hero-card-body">
                <!-- Mini chart -->
                <div class="hero-chart">
                    <svg class="chart-line-svg" viewBox="0 0 380 140" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                        <!-- WHO reference band -->
                        <path d="M0,100 Q95,85 190,75 Q285,65 380,55 L380,40 Q285,50 190,55 Q95,65 0,80 Z" fill="#DCFCE7" opacity="0.6"/>
                        <!-- Actual growth line -->
                        <polyline points="0,95 63,82 126,70 190,60 253,52 316,44 380,38"
                                  fill="none" stroke="#16A34A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <!-- Dots -->
                        <circle cx="0"   cy="95" r="4" fill="#16A34A"/>
                        <circle cx="63"  cy="82" r="4" fill="#16A34A"/>
                        <circle cx="126" cy="70" r="4" fill="#16A34A"/>
                        <circle cx="190" cy="60" r="4" fill="#16A34A"/>
                        <circle cx="253" cy="52" r="4" fill="#16A34A"/>
                        <circle cx="316" cy="44" r="4" fill="#16A34A"/>
                        <circle cx="380" cy="38" r="5" fill="#16A34A" stroke="#fff" stroke-width="2"/>
                        <!-- X axis labels -->
                        <text x="0"   y="135" font-size="10" fill="#94A3B8" font-family="Inter">Sep</text>
                        <text x="60"  y="135" font-size="10" fill="#94A3B8" font-family="Inter">Okt</text>
                        <text x="123" y="135" font-size="10" fill="#94A3B8" font-family="Inter">Nov</text>
                        <text x="183" y="135" font-size="10" fill="#94A3B8" font-family="Inter">Des</text>
                        <text x="246" y="135" font-size="10" fill="#94A3B8" font-family="Inter">Jan</text>
                        <text x="309" y="135" font-size="10" fill="#94A3B8" font-family="Inter">Feb</text>
                        <text x="367" y="135" font-size="10" fill="#94A3B8" font-family="Inter">Mar</text>
                    </svg>
                </div>
                <!-- Stats row -->
                <div class="hero-card-stats">
                    <div class="hero-stat">
                        <span class="hero-stat-val">12.4 kg</span>
                        <span class="hero-stat-lbl">Berat Badan</span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat-val">86 cm</span>
                        <span class="hero-stat-lbl">Tinggi Badan</span>
                    </div>
                    <div class="hero-stat">
                        <span class="hero-stat-val" style="color:#16A34A">Normal</span>
                        <span class="hero-stat-lbl">Status Gizi</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ╔══════════════════════════════╗ -->
<!-- ║           URGENSI            ║ -->
<!-- ╚══════════════════════════════╝ -->
<section id="urgensi" class="section">
    <div class="section-header">
        <span class="section-label label-green">Mengapa Penting</span>
        <h2 class="section-title">Stunting Masih Menjadi Pekerjaan Rumah Nasional</h2>
        <p class="section-sub">Angka turun, tetapi masih di atas target. Deteksi dini yang ditindaklanjuti adalah kuncinya.</p>
    </div>

    <div class="urgency-grid">
        <div class="urgency-card">
            <span class="urgency-val">21,5%</span>
            <span class="urgency-lbl">Prevalensi stunting balita, 2023</span>
            <span class="urgency-note">Survei Status Gizi Indonesia (SSGI) 2023.</span>
        </div>
        <div class="urgency-card">
            <span class="urgency-val">19,8%</span>
            <span class="urgency-lbl">Prevalensi stunting balita, 2024</span>
            <span class="urgency-note">SSGI 2024, di bawah target 20,1% tahun tersebut.</span>
        </div>
        <div class="urgency-card">
            <span class="urgency-val">14,2%</span>
            <span class="urgency-lbl">Target nasional 2029</span>
            <span class="urgency-note">Sasaran RPJMN yang disampaikan Kementerian Kesehatan.</span>
        </div>
    </div>

    <div class="urgency-gap">
        Mengukur saja tidak cukup. Anak yang tertinggal perlu cepat terlihat, ditindaklanjuti,
        dan hasilnya dinilai. Situmbuh dibuat untuk menutup celah antara pemantauan dan tindakan itu.
    </div>

    <p class="urgency-source">
        Sumber: <a href="https://www.badankebijakan.kemkes.go.id/ssgi-2024" target="_blank" rel="noopener">Badan Kebijakan Pembangunan Kesehatan, Kemenkes RI &ndash; SSGI 2024</a>.
        Situmbuh adalah prototipe lomba; belum diuji di lapangan.
    </p>
</section>

<!-- ╔══════════════════════════════╗ -->
<!-- ║          FEATURES            ║ -->
<!-- ╚══════════════════════════════╝ -->
<section id="features" class="section">
    <div class="section-header">
        <span class="section-label label-green">Fitur Unggulan</span>
        <h2 class="section-title">Dari Pemantauan sampai Hasil yang Terukur</h2>
        <p class="section-sub">
            Bukan sekadar mencatat angka: setiap data diarahkan menjadi prioritas,
            tindakan, dan evaluasi.
        </p>
    </div>

    <div class="features-grid">
        <div class="feature-card feat-card-green">
            <div class="feat-icon feat-icon-green">
                <i data-lucide="scale" style="color:#fff;width:24px;height:24px;"></i>
            </div>
            <div class="feat-card-title">Pemantauan dan Z-Score WHO</div>
            <p class="feat-card-desc">
                Catat berat, tinggi, dan lingkar kepala. Status gizi dihitung dari tabel
                standar pertumbuhan WHO dan ditampilkan pada grafik berpita yang mudah dibaca.
            </p>
        </div>

        <div class="feature-card feat-card-orange">
            <div class="feat-icon feat-icon-orange">
                <i data-lucide="triangle-alert" style="color:#fff;width:24px;height:24px;"></i>
            </div>
            <div class="feat-card-title">Prioritas dengan Alasan</div>
            <p class="feat-card-desc">
                Anak yang paling perlu ditinjau diurutkan otomatis, lengkap dengan faktor
                penyebabnya. Skor ini alat bantu urutan, bukan diagnosis.
            </p>
        </div>

        <div class="feature-card feat-card-blue">
            <div class="feat-icon feat-icon-blue">
                <i data-lucide="clipboard-check" style="color:#fff;width:24px;height:24px;"></i>
            </div>
            <div class="feat-card-title">Tindak Lanjut dan Evaluasi</div>
            <p class="feat-card-desc">
                Kader membuat tindak lanjut, menandainya selesai, lalu sistem membandingkan
                kondisi sebelum dan sesudah untuk melihat dampaknya.
            </p>
        </div>

        <div class="feature-card feat-card-purple">
            <div class="feat-icon feat-icon-purple">
                <i data-lucide="message-circle" style="color:#fff;width:24px;height:24px;"></i>
            </div>
            <div class="feat-card-title">Asisten AI dan Skrining KPSP</div>
            <p class="feat-card-desc">
                Orang tua bisa bertanya kepada asisten yang membaca ringkasan data anak,
                dan mengisi skrining perkembangan KPSP sesuai usia.
            </p>
        </div>
    </div>
</section>

<!-- ╔══════════════════════════════╗ -->
<!-- ║        HOW IT WORKS          ║ -->
<!-- ╚══════════════════════════════╝ -->
<section id="how-it-works" class="section section-alt">
    <div class="section-header">
        <span class="section-label label-blue">Cara Kerja</span>
        <h2 class="section-title">Tiga Langkah dari Data sampai Tindakan</h2>
        <p class="section-sub">
            Alurnya sederhana, tetapi tidak berhenti di pencatatan.
        </p>
    </div>

    <div class="steps-row">
        <div class="step">
            <div class="step-num step-num-green">1</div>
            <div class="step-title">Input Data Anak</div>
            <p class="step-desc">
                Orang tua atau kader memasukkan berat badan, tinggi badan, dan ukuran lain
                setiap kali anak diukur.
            </p>
        </div>

        <div class="step-arrow">
            <i data-lucide="arrow-right" style="color:#CBD5E1;width:28px;height:28px;"></i>
        </div>

        <div class="step">
            <div class="step-num step-num-blue">2</div>
            <div class="step-title">Status dan Prioritas</div>
            <p class="step-desc">
                Sistem menghitung Z-score WHO, lalu mengurutkan anak yang paling perlu
                ditinjau beserta alasannya.
            </p>
        </div>

        <div class="step-arrow">
            <i data-lucide="arrow-right" style="color:#CBD5E1;width:28px;height:28px;"></i>
        </div>

        <div class="step">
            <div class="step-num step-num-purple">3</div>
            <div class="step-title">Tindak Lanjut dan Evaluasi</div>
            <p class="step-desc">
                Kader mencatat tindakan dan hasilnya. Pengukuran berikutnya menunjukkan
                apakah kondisi anak membaik, tetap, atau memburuk.
            </p>
        </div>
    </div>
</section>

<!-- ╔══════════════════════════════╗ -->
<!-- ║          BENEFITS            ║ -->
<!-- ╚══════════════════════════════╝ -->
<section id="benefits" class="section">
    <div class="section-header">
        <span class="section-label label-green">Keunggulan Kami</span>
        <h2 class="section-title">Kenapa Memilih Situmbuh?</h2>
    </div>

    <div class="benefits-grid">
        <div class="benefit-card">
            <div class="benefit-icon benf-icon-green">
                <i data-lucide="shield-check" style="width:24px;height:24px;"></i>
            </div>
            <span class="benefit-title-text">Mengacu pada Standar WHO</span>
            <p class="benefit-desc-text">Perhitungan Z-score memakai tabel standar pertumbuhan anak WHO 2006, sehingga status gizi dibaca dengan acuan yang sama dengan layanan kesehatan.</p>
        </div>

        <div class="benefit-card">
            <div class="benefit-icon benf-icon-blue">
                <i data-lucide="eye" style="width:24px;height:24px;"></i>
            </div>
            <span class="benefit-title-text">Transparan, Bukan Kotak Hitam</span>
            <p class="benefit-desc-text">Setiap skor prioritas disertai faktor dan nilainya. Keputusan klinis tetap berada pada tenaga kesehatan.</p>
        </div>

        <div class="benefit-card">
            <div class="benefit-icon benf-icon-yellow">
                <i data-lucide="lock" style="width:24px;height:24px;"></i>
            </div>
            <span class="benefit-title-text">Akses Dibatasi per Anak</span>
            <p class="benefit-desc-text">Orang tua hanya melihat anaknya sendiri, kader dan tenaga kesehatan hanya anak yang ditugaskan. Asisten AI menerima ringkasan tanpa NIK, nomor telepon, atau nama orang tua.</p>
        </div>
    </div>
</section>

<!-- ╔══════════════════════════════╗ -->
<!-- ║        TARGET USERS          ║ -->
<!-- ╚══════════════════════════════╝ -->
<section id="target-users" class="section section-alt">
    <div class="section-header">
        <span class="section-label label-purple">Pengguna Platform</span>
        <h2 class="section-title">Dirancang untuk Semua Pihak dalam Ekosistem Kesehatan Anak</h2>
    </div>

    <div class="users-grid">
        <div class="user-card">
            <div class="user-card-img user-img-green">
                <i data-lucide="user-round" style="width:56px;height:56px;"></i>
            </div>
            <span class="user-card-tag tag-green">Orang Tua</span>
            <div class="user-card-title">Pantau Anak Anda dari Rumah</div>
            <p class="user-card-desc">
                Catat pengukuran kapan saja, lihat grafik pertumbuhan, dan baca
                ringkasan status anak dengan bahasa sederhana.
            </p>
        </div>

        <div class="user-card">
            <div class="user-card-img user-img-blue">
                <i data-lucide="stethoscope" style="width:56px;height:56px;"></i>
            </div>
            <span class="user-card-tag tag-blue">Kader dan Tenaga Kesehatan</span>
            <div class="user-card-title">Tahu Siapa yang Harus Ditangani Dulu</div>
            <p class="user-card-desc">
                Daftar anak terurut berdasarkan prioritas, lengkap dengan alasan dan
                tindak lanjut yang masih berjalan.
            </p>
        </div>

        <div class="user-card">
            <div class="user-card-img user-img-purple">
                <i data-lucide="building-2" style="width:56px;height:56px;"></i>
            </div>
            <span class="user-card-tag tag-purple">Pengelola Layanan</span>
            <div class="user-card-title">Gambaran Besar dalam Satu Layar</div>
            <p class="user-card-desc">
                Dasbor sebaran prioritas, pemantauan yang terlewat, penyelesaian tindak
                lanjut, dan laporan stunting.
            </p>
        </div>
    </div>
</section>

<!-- ╔══════════════════════════════╗ -->
<!-- ║             CTA              ║ -->
<!-- ╚══════════════════════════════╝ -->
<section id="cta">
    <h2 class="cta-title">Mulai Pantau Tumbuh Kembang Anak Anda Sekarang</h2>
    <p class="cta-sub">
        Daftar, tambahkan data anak, dan lihat bagaimana data pertumbuhan berubah
        menjadi prioritas dan tindakan.
    </p>

    <div class="cta-btns">
        <a href="{{ route('register') }}" class="btn-cta-white">Mulai Gratis Sekarang</a>
        <a href="{{ route('login') }}" class="btn-cta-outline">Sudah punya akun? Masuk</a>
    </div>
</section>

<!-- ╔══════════════════════════════╗ -->
<!-- ║           FOOTER             ║ -->
<!-- ╚══════════════════════════════╝ -->
<footer>
    <div class="footer-main">
        <div class="footer-brand">
            <div class="footer-logo">
                <span class="footer-logo-text">Situmbuh</span>
            </div>
            <p class="footer-tagline">
                Platform digital pemantauan tumbuh kembang anak berbasis standar WHO
                untuk Indonesia yang lebih sehat.
            </p>
        </div>

        <div class="footer-nav">
            <span class="footer-nav-title">Produk</span>
            <a href="#features">Fitur</a>
            <a href="#how-it-works">Cara Kerja</a>
            <a href="#urgensi">Urgensi</a>
        </div>

        <div class="footer-nav">
            <span class="footer-nav-title">Akun</span>
            <a href="{{ route('login') }}">Masuk</a>
            <a href="{{ route('register') }}">Daftar</a>
        </div>

        <div class="footer-contact">
            <span class="footer-nav-title">Hubungi Kami</span>
            <div class="footer-contact-item">
                <i data-lucide="phone" style="width:15px;height:15px;color:#22C55E;"></i>
                <span>+62 812-1186-7462</span>
            </div>
            <div class="footer-contact-item">
                <i data-lucide="map-pin" style="width:15px;height:15px;color:#22C55E;"></i>
                <span>Tasikmalaya, Indonesia</span>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <span class="footer-copy">© 2026 Situmbuh. Prototipe ICONFEST 2026.</span>
    </div>
</footer>

<script>
    // Wait for Lucide library to load
    function initializeApp() {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // Mobile menu toggle
        function toggleMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('open');
        }

        // Close mobile menu on nav link click
        document.querySelectorAll('.mobile-menu a').forEach(function(link) {
            link.addEventListener('click', function() {
                document.getElementById('mobileMenu').classList.remove('open');
            });
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
            anchor.addEventListener('click', function(e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeApp);
    } else {
        initializeApp();
    }
</script>
</body>
</html>
