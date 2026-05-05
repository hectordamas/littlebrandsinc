<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @yield('title')
    <meta name="description"
        content="Holding especializado en formacion deportiva para ninos. Descubre Little Strikers y Little Paddlers.">
    <link rel="icon" type="image/png" href="{{ asset('landing_page/logos/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;700;800&family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --brand-blue: #0c7ff2;
            --brand-cyan: #2ac7d8;
            --brand-green: #1ec97f;
            --brand-orange: #ff8d3f;
            --brand-gold: #f5c15d;
            --ink: #091722;
            --text: #486173;
            --muted: #6d8494;
            --surface: #f6f8fb;
            --surface-strong: #eef3f7;
            --line: rgba(9, 23, 34, 0.1);
            --radius: 26px;
            --shadow-soft: 0 20px 60px rgba(9, 23, 34, 0.08);
            --shadow-strong: 0 30px 80px rgba(9, 23, 34, 0.14);
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at top left, rgba(42, 199, 216, 0.16), transparent 34%),
                radial-gradient(circle at 85% 12%, rgba(245, 193, 93, 0.18), transparent 24%),
                linear-gradient(180deg, #fbfdff 0%, #f3f7fb 42%, #fbfcfe 100%);
            overflow-x: hidden;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(9, 23, 34, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(9, 23, 34, 0.03) 1px, transparent 1px);
            background-size: 64px 64px;
            mask-image: radial-gradient(circle at center, black 38%, transparent 90%);
            pointer-events: none;
            z-index: -2;
        }

        body::after {
            content: '';
            position: fixed;
            inset: 0 auto auto 0;
            width: 420px;
            height: 420px;
            background: radial-gradient(circle, rgba(12, 127, 242, 0.14) 0, transparent 66%);
            filter: blur(14px);
            z-index: -1;
        }

        h1,
        h2,
        h3,
        .logo-text {
            font-family: 'Baloo 2', cursive;
            letter-spacing: 0.2px;
            margin: 0;
        }

        .container {
            width: min(1180px, calc(100% - 2.4rem));
            margin: 0 auto;
        }

        .topbar {
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            z-index: 2200;
            padding-top: 20px;
        }

        .topbar-inner {
            min-height: 78px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.85rem 1rem;
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.72);
            backdrop-filter: blur(18px);
            box-shadow: 0 14px 40px rgba(9, 23, 34, 0.09);
        }

        .brand {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: var(--ink);
            flex-shrink: 0;
        }

        .brand img {
            width: 156px;
            height: 56px;
            object-fit: contain;
            display: block;
        }

        .topnav {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.3rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.74);
            border: 1px solid rgba(9, 23, 34, 0.06);
        }

        .topnav a {
            text-decoration: none;
            color: var(--text);
            font-weight: 700;
            font-size: 0.92rem;
            padding: 0.72rem 0.9rem;
            border-radius: 999px;
            transition: color 0.25s ease, background-color 0.25s ease, transform 0.25s ease;
        }

        .topnav a:hover {
            color: var(--ink);
            background: rgba(12, 127, 242, 0.08);
            transform: translateY(-1px);
        }

        .nav-toggle {
            display: none;
            width: 42px;
            height: 42px;
            border: 1px solid rgba(9, 23, 34, 0.12);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.92);
            color: var(--ink);
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex-shrink: 0;
        }

        .nav-toggle svg {
            width: 20px;
            height: 20px;
        }

        .cta-pill {
            border: 1px solid rgba(9, 23, 34, 0.08);
            border-radius: 999px;
            padding: 0.82rem 1.15rem;
            background: var(--ink);
            color: #fff;
            font-weight: 800;
            text-decoration: none;
            box-shadow: 0 16px 30px rgba(9, 23, 34, 0.18);
        }

        .hero {
            padding-top: 146px;
            padding-bottom: 86px;
            position: relative;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.14fr) minmax(0, 0.86fr);
            align-items: center;
            gap: 2rem;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border-radius: 999px;
            padding: 0.5rem 0.95rem;
            font-size: 0.78rem;
            color: #0f4c78;
            background: rgba(12, 127, 242, 0.1);
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: 1px solid rgba(12, 127, 242, 0.08);
        }

        .hero h1 {
            font-size: clamp(2rem, 3vw, 3.2rem);
            line-height: 0.98;
            margin-top: 0.85rem;
            margin-bottom: 1rem;
            text-wrap: balance;
        }

        .hero p,
        .hero-lead {
            color: var(--text);
            font-size: 1.05rem;
            line-height: 1.78;
            margin: 0 0 1.5rem;
            max-width: 58ch;
        }

        .hero-copy {
            position: relative;
        }

        .hero-copy::after {
            content: '';
            position: absolute;
            right: 8%;
            top: 8%;
            width: 88px;
            height: 88px;
            border-radius: 28px;
            border: 1px solid rgba(12, 127, 242, 0.12);
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.7), rgba(42, 199, 216, 0.16));
            transform: rotate(16deg);
            pointer-events: none;
        }

        .hero-actions {
            display: flex;
            gap: 0.85rem;
            flex-wrap: wrap;
            margin-bottom: 1.6rem;
        }

        .btn-main,
        .btn-soft {
            text-decoration: none;
            border-radius: 16px;
            padding: 0.96rem 1.22rem;
            font-weight: 800;
            font-size: 0.95rem;
        }

        .btn-main {
            color: #fff;
            background: linear-gradient(135deg, var(--ink), #16334c);
            box-shadow: 0 18px 35px rgba(9, 23, 34, 0.18);
        }

        .btn-soft {
            color: var(--ink);
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(9, 23, 34, 0.08);
        }

        .hero-metrics {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.85rem;
            max-width: 620px;
        }

        .metric-card {
            padding: 1rem 1.05rem;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.74);
            border: 1px solid rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            box-shadow: var(--shadow-soft);
        }

        .metric-card strong {
            display: block;
            font-size: 1.55rem;
            line-height: 1;
            margin-bottom: 0.35rem;
        }

        .metric-card span {
            display: block;
            color: var(--muted);
            font-size: 0.88rem;
            line-height: 1.45;
            font-weight: 700;
        }

        .hero-stage {
            position: relative;
        }

        .hero-stage::before {
            content: '';
            position: absolute;
            right: -30px;
            top: -20px;
            width: 220px;
            height: 220px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(245, 193, 93, 0.28), transparent 68%);
            z-index: 0;
        }

        .slider-card {
            background: linear-gradient(160deg, #0b2234, #143653 55%, #0d1e2d 100%);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 34px;
            overflow: hidden;
            box-shadow: var(--shadow-strong);
            position: relative;
            z-index: 1;
        }

        .slider-card::before {
            content: 'Little Brands Inc';
            position: absolute;
            top: 1rem;
            left: 1rem;
            padding: 0.45rem 0.7rem;
            border-radius: 999px;
            font-size: 0.74rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            font-weight: 800;
            color: #fff;
            background: rgba(255, 255, 255, 0.14);
            backdrop-filter: blur(10px);
            z-index: 4;
        }

        .slides {
            position: relative;
            min-height: 500px;
        }

        .slide {
            position: absolute;
            inset: 0;
            opacity: 0;
            transform: translateX(18px) scale(0.99);
            transition: opacity 0.65s ease, transform 0.65s ease;
            padding: 1.2rem;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }

        .slide.active {
            opacity: 1;
            transform: translateX(0) scale(1);
            z-index: 2;
        }

        .slide-bg {
            position: absolute;
            inset: 0;
            border-radius: 0;
            background-size: cover;
            background-position: center;
            z-index: -1;
        }

        .slide:nth-child(1) .slide-bg {
            background-image:
                linear-gradient(20deg, rgba(10, 132, 255, 0.76), rgba(0, 184, 217, 0.42)),
                url('{{ asset('landing_page/assets/slides/slide1.png') }}');
        }

        .slide:nth-child(2) .slide-bg {
            background-image:
                linear-gradient(30deg, rgba(35, 193, 107, 0.8), rgba(35, 193, 107, 0.35)),
                url('{{ asset('landing_page/assets/slides/slide2.png') }}');

        }

        .slide:nth-child(3) .slide-bg {
            background-image:
                linear-gradient(35deg, rgba(255, 138, 40, 0.84), rgba(255, 111, 58, 0.45)),
                url('{{ asset('landing_page/assets/slides/slide3.png') }}');
        }

        .slide-content {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.94), rgba(244, 249, 253, 0.88));
            border-radius: 24px;
            padding: 1.2rem;
            width: min(86%, 360px);
            border: 1px solid rgba(255, 255, 255, 0.66);
            box-shadow: 0 18px 40px rgba(9, 23, 34, 0.14);
        }

        .slide-content strong {
            display: block;
            font-size: 1.32rem;
            margin-bottom: 0.45rem;
            color: #0a2f50;
        }

        .slide-content p {
            margin: 0;
            color: #2c506d;
            font-size: 0.96rem;
            line-height: 1.58;
        }

        .slider-controls {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.7rem;
            padding: 1rem 0.9rem 1.1rem;
            background: rgba(255, 255, 255, 0.05);
        }

        .dot {
            width: 11px;
            height: 11px;
            border-radius: 999px;
            border: 0;
            background: rgba(255, 255, 255, 0.35);
            cursor: pointer;
            transition: transform 0.2s ease, background-color 0.2s ease;
        }

        .dot.active {
            transform: scale(1.28);
            background: #fff;
        }

        section {
            padding: 82px 0;
            position: relative;
        }

        .section-muted {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.72), rgba(246, 248, 251, 0.92));
            border-top: 1px solid rgba(255, 255, 255, 0.76);
            border-bottom: 1px solid rgba(9, 23, 34, 0.04);
        }

        .section-head {
            margin-bottom: 1.3rem;
            max-width: 760px;
        }

        .section-kicker {
            display: inline-block;
            margin-bottom: 0.6rem;
            color: var(--brand-blue);
            font-size: 0.82rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .section-head h2 {
            font-size: clamp(2rem, 3vw, 3rem);
            line-height: 0.96;
        }

        .section-head p {
            max-width: 70ch;
            line-height: 1.78;
            color: var(--text);
            margin: 0.7rem 0 0;
        }

        .about-grid {
            display: grid;
            gap: 1.1rem;
            grid-template-columns: 1.1fr 0.9fr;
        }

        .panel {
            background: rgba(255, 255, 255, 0.76);
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow-soft);
            backdrop-filter: blur(12px);
        }

        .panel h3 {
            font-size: 1.7rem;
            line-height: 1.02;
            margin-bottom: 0.9rem;
        }

        .panel p {
            color: var(--text);
            line-height: 1.78;
        }

        .about-points {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.8rem;
            margin-top: 1.15rem;
        }

        .about-point {
            padding: 1rem;
            border-radius: 20px;
            background: rgba(9, 23, 34, 0.03);
            border: 1px solid rgba(9, 23, 34, 0.05);
        }

        .about-point strong {
            display: block;
            margin-bottom: 0.35rem;
            font-size: 0.98rem;
        }

        .logos {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.9rem;
            margin-top: 1rem;
        }

        .logo-card {
            border-radius: 22px;
            border: 1px solid rgba(9, 23, 34, 0.06);
            background: linear-gradient(180deg, #ffffff, #f4f8fb);
            padding: 1.15rem;
            display: grid;
            place-items: center;
            min-height: 156px;
        }

        .logo-card img {
            width: 100%;
            height: 96px;
            object-fit: contain;
        }

        .mv-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.1rem;
        }

        .mv-card {
            border-radius: var(--radius);
            color: #fff;
            padding: 1.6rem;
            min-height: 260px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            box-shadow: var(--shadow-strong);
            position: relative;
            overflow: hidden;
        }

        .mv-card::before {
            content: '';
            position: absolute;
            width: 180px;
            height: 180px;
            right: -40px;
            top: -40px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
        }

        .mv-card h3 {
            font-size: 2.15rem;
            margin-bottom: 0.65rem;
            position: relative;
            z-index: 1;
        }

        .mv-card p {
            margin: 0;
            line-height: 1.72;
            opacity: 0.96;
            position: relative;
            z-index: 1;
        }

        .mv-card.mision {
            background: linear-gradient(145deg, #0b7be7, #1cc1d4 70%, #83e5e0);
        }

        .mv-card.vision {
            background: linear-gradient(145deg, #16273d, #1d6a75 55%, #28c68d);
        }

        .brands-grid {
            display: grid;
            gap: 1.1rem;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .brand-card {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.92), rgba(248, 251, 253, 0.88));
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 28px;
            padding: 1.4rem;
            box-shadow: var(--shadow-soft);
            position: relative;
            overflow: hidden;
        }

        .brand-card::after {
            content: '';
            position: absolute;
            inset: auto -22px -40px auto;
            width: 160px;
            height: 160px;
            border-radius: 999px;
            background: color-mix(in srgb, var(--card-color, var(--brand-blue)) 25%, transparent);
        }

        .brand-top {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.7rem;
            position: relative;
            z-index: 1;
        }

        .brand-top img {
            width: 82px;
            height: 82px;
            border-radius: 22px;
            object-fit: contain;
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(9, 23, 34, 0.06);
            padding: 10px;
        }

        .brand-card h3 {
            font-size: 1.6rem;
            line-height: 1;
        }

        .brand-card small {
            font-size: 0.82rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--text);
        }

        .brand-card p {
            margin: 0.45rem 0 1rem;
            line-height: 1.65;
            color: var(--text);
            position: relative;
            z-index: 1;
        }

        .brand-card a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
            font-weight: 800;
            padding: 0.8rem 1rem;
            border-radius: 14px;
            color: #fff;
            background: linear-gradient(135deg, var(--card-color, var(--brand-blue)), color-mix(in srgb, var(--card-color, var(--brand-blue)) 70%, #091722));
            position: relative;
            z-index: 1;
        }

        .contact-wrap {
            display: grid;
            grid-template-columns: 0.95fr 1.05fr;
            gap: 1.1rem;
        }

        .contact-panel {
            border-radius: 30px;
            padding: 1.5rem;
            color: #fff;
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.18), transparent 28%),
                linear-gradient(155deg, #081723, #12304a 48%, #0d86d9 100%);
            box-shadow: 0 28px 70px rgba(9, 23, 34, 0.22);
            position: relative;
            overflow: hidden;
        }

        .contact-panel::after {
            content: '';
            position: absolute;
            right: -36px;
            bottom: -36px;
            width: 180px;
            height: 180px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
        }

        .contact-panel p {
            margin: 0.8rem 0 0;
            line-height: 1.75;
            opacity: 0.97;
            position: relative;
            z-index: 1;
        }

        .contact-panel h3 {
            font-size: 2rem;
            line-height: 0.98;
            position: relative;
            z-index: 1;
        }

        .contact-points {
            display: grid;
            gap: 0.75rem;
            margin-top: 1.2rem;
            position: relative;
            z-index: 1;
        }

        .contact-point {
            padding: 0.95rem 1rem;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .contact-point strong {
            display: block;
            margin-bottom: 0.28rem;
            font-size: 0.94rem;
        }

        .contact-form {
            background: rgba(255, 255, 255, 0.86);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 30px;
            padding: 1.5rem;
            box-shadow: var(--shadow-soft);
            backdrop-filter: blur(14px);
        }

        .form-intro {
            margin-bottom: 1rem;
        }

        .form-intro h3 {
            font-size: 1.55rem;
            line-height: 1.02;
            margin-bottom: 0.45rem;
        }

        .form-intro p {
            margin: 0;
            color: var(--text);
            line-height: 1.7;
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.8rem;
        }

        label {
            display: block;
            margin-bottom: 0.35rem;
            font-size: 0.87rem;
            font-weight: 700;
            color: #1d3d57;
        }

        input,
        textarea {
            width: 100%;
            border: 1px solid rgba(72, 97, 115, 0.18);
            background: rgba(246, 248, 251, 0.92);
            border-radius: 16px;
            padding: 0.9rem 0.95rem;
            font: inherit;
            color: var(--ink);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        input:focus,
        textarea:focus {
            border-color: var(--brand-blue);
            box-shadow: 0 0 0 4px rgba(10, 132, 255, 0.12);
            outline: none;
        }

        textarea {
            min-height: 136px;
            resize: vertical;
        }

        .field {
            margin-bottom: 0.8rem;
        }

        .error {
            display: block;
            margin-top: 0.25rem;
            font-size: 0.78rem;
            color: #c52c2c;
        }

        .alert {
            border-radius: 12px;
            padding: 0.8rem 0.9rem;
            margin-bottom: 0.9rem;
            font-size: 0.9rem;
            line-height: 1.45;
        }

        .alert.ok {
            background: #e8fff2;
            border: 1px solid #a2e2bd;
            color: #0f6f3f;
        }

        .alert.bad {
            background: #fff1f1;
            border: 1px solid #ffc2c2;
            color: #b12f2f;
        }

        .submit {
            width: 100%;
            border: 0;
            border-radius: 16px;
            padding: 0.95rem 1rem;
            font: inherit;
            font-weight: 800;
            color: #fff;
            background: linear-gradient(135deg, #091722, #15436a);
            box-shadow: 0 16px 35px rgba(9, 23, 34, 0.2);
            cursor: pointer;
        }

        footer {
            margin-top: 28px;
            padding-bottom: 28px;
        }

        .footer-inner {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(0, 0.75fr) minmax(0, 0.95fr);
            gap: 1.2rem;
            color: #355066;
            padding: 1.35rem;
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.78);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.86), rgba(244, 248, 251, 0.88));
            box-shadow: var(--shadow-soft);
        }

        .footer-brand {
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
        }

        .footer-brand img {
            width: 164px;
            height: 58px;
            object-fit: contain;
            display: block;
        }

        .footer-brand p,
        .footer-note,
        .footer-links a,
        .footer-cta p {
            margin: 0;
            color: var(--text);
            line-height: 1.72;
        }

        .footer-title {
            display: block;
            margin-bottom: 0.65rem;
            color: var(--ink);
            font-size: 0.82rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .footer-links {
            display: grid;
            gap: 0.7rem;
            align-content: start;
        }

        .footer-links a {
            text-decoration: none;
            font-weight: 700;
            transition: color 0.2s ease, transform 0.2s ease;
        }

        .footer-links a:hover {
            color: var(--brand-blue);
            transform: translateX(2px);
        }

        .footer-cta {
            padding: 1rem;
            border-radius: 24px;
            background: linear-gradient(145deg, #0a1d2d, #133e62 58%, #0c7ff2);
            color: #fff;
            box-shadow: 0 20px 44px rgba(9, 23, 34, 0.18);
        }

        .footer-cta .footer-title,
        .footer-cta p {
            color: #fff;
        }

        .footer-cta a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-top: 0.9rem;
            padding: 0.82rem 1rem;
            border-radius: 14px;
            background: #fff;
            color: var(--ink);
            font-weight: 800;
            text-decoration: none;
        }

        .footer-bottom {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(9, 23, 34, 0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.8rem;
            flex-wrap: wrap;
            font-size: 0.88rem;
            color: var(--muted);
        }

        .reveal {
            opacity: 0;
            transform: translateY(24px);
            transition: opacity 0.62s ease, transform 0.62s ease;
        }

        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }

        @media (max-width: 980px) {

            .topbar-inner {
                padding: 0.65rem 0.75rem;
                position: relative;
                overflow: visible;
                flex-wrap: wrap;
                row-gap: 0.5rem;
            }

            .topnav {
                display: none;
                order: 3;
                width: 100%;
                flex-direction: column;
                align-items: stretch;
                gap: 0.25rem;
                border-radius: 14px;
                padding: 0.45rem;
                margin-top: 0.2rem;
                margin-left: 0;
                background: rgba(255, 255, 255, 0.97);
                border: 1px solid rgba(9, 23, 34, 0.08);
                box-shadow: 0 10px 24px rgba(9, 23, 34, 0.12);
            }

            .topbar-inner.menu-open .topnav {
                display: flex;
            }

            .topnav a {
                width: 100%;
                border-radius: 12px;
                padding: 0.78rem 0.9rem;
            }

            .hero-grid,
            .about-grid,
            .mv-grid,
            .brands-grid,
            .contact-wrap,
            .row,
            .hero-metrics,
            .about-points {
                grid-template-columns: 1fr;
            }

            .hero {
                padding-top: 126px;
            }

            .slides {
                min-height: 390px;
            }

            .slide-content {
                width: 100%;
            }

            .cta-pill {
                display: none;
            }

            .nav-toggle {
                display: inline-flex;
                margin-left: auto;
            }

            .brand img {
                width: 142px;
                height: 52px;
            }

            .hero-copy::after,
            .hero-stage::before {
                display: none;
            }

            section {
                padding: 68px 0;
            }

            .contact-panel h3,
            .hero h1 {
                max-width: none;
            }

            .metric-card,
            .panel,
            .brand-card,
            .contact-panel,
            .contact-form,
            .footer-inner {
                border-radius: 22px;
            }

            .footer-inner {
                grid-template-columns: 1fr;
            }

            .footer-cta {
                border-radius: 22px;
            }
        }
    </style>
</head>

<body>
    <header class="topbar">
        <div class="container topbar-inner">
            <a class="brand" href="/#inicio" aria-label="Little Brands Inc">
                <img src="{{ asset('landing_page/logos/lbinc-admin.png') }}" alt="Logo Little Brands Inc">
            </a>

            <nav class="topnav" aria-label="Navegacion principal">
                <a href="/#inicio">Inicio</a>
                <a href="/#acerca">Acerca de</a>
                <a href="/#valores">Valores</a>
                <a href="/#contacto">Contacto</a>
                <a href="{{ route('login') }}">Ingresar</a>
            </nav>

            <button class="nav-toggle" type="button" aria-expanded="false" aria-label="Abrir menu de navegacion">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                    <path d="M4 7h16"></path>
                    <path d="M4 12h16"></path>
                    <path d="M4 17h16"></path>
                </svg>
            </button>

            <a class="cta-pill" href="#contacto">Inscribir a mi hijo</a>
        </div>
    </header>

    @yield('content')
        
    <footer>
        <div class="container footer-inner">
            <div class="footer-brand">
                <img src="{{ asset('landing_page/logos/lbinc-admin.png') }}" alt="Logo Little Brands Inc">
                <p>Formación deportiva infantil con una propuesta clara, moderna y confiable para familias que buscan
                    crecimiento con metodología y cercanía.</p>
                <p class="footer-note">Little Strikers | Little Paddlers</p>
            </div>

            <nav class="footer-links" aria-label="Enlaces del pie de página">
                <span class="footer-title">Navegación</span>
                <a href="#inicio">Inicio</a>
                <a href="#acerca">Acerca de</a>
                <a href="#marcas">Marcas</a>
                <a href="#contacto">Contacto</a>
            </nav>

            <div class="footer-cta">
                <span class="footer-title">Siguiente paso</span>
                <p>Si quieres conocer horarios, edades y disponibilidad, escribe al equipo y te orientamos según la
                    etapa de tu hijo.</p>
                <a href="#contacto">Solicitar información</a>
            </div>
        </div>
    </footer>

    <script>
        (function() {
            const slides = Array.from(document.querySelectorAll('.slide'));
            const dots = Array.from(document.querySelectorAll('.dot'));
            const topbarInner = document.querySelector('.topbar-inner');
            const navToggle = document.querySelector('.nav-toggle');
            const navLinks = Array.from(document.querySelectorAll('.topnav a'));
            let active = 0;
            let timerId = null;

            function closeMobileMenu() {
                if (!topbarInner || !navToggle) {
                    return;
                }
                topbarInner.classList.remove('menu-open');
                navToggle.setAttribute('aria-expanded', 'false');
            }

            if (topbarInner && navToggle) {
                navToggle.addEventListener('click', () => {
                    const nextOpen = !topbarInner.classList.contains('menu-open');
                    topbarInner.classList.toggle('menu-open', nextOpen);
                    navToggle.setAttribute('aria-expanded', nextOpen ? 'true' : 'false');
                });

                navLinks.forEach((link) => {
                    link.addEventListener('click', closeMobileMenu);
                });

                document.addEventListener('click', (event) => {
                    if (!topbarInner.classList.contains('menu-open')) {
                        return;
                    }
                    if (!topbarInner.contains(event.target)) {
                        closeMobileMenu();
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') {
                        closeMobileMenu();
                    }
                });

                window.addEventListener('resize', () => {
                    if (window.innerWidth > 980) {
                        closeMobileMenu();
                    }
                });
            }

            function showSlide(index) {
                active = index;
                slides.forEach((slide, i) => {
                    slide.classList.toggle('active', i === index);
                });
                dots.forEach((dot, i) => {
                    dot.classList.toggle('active', i === index);
                });
            }

            function nextSlide() {
                const next = (active + 1) % slides.length;
                showSlide(next);
            }

            function restartTimer() {
                if (timerId) {
                    clearInterval(timerId);
                }
                timerId = setInterval(nextSlide, 5000);
            }

            dots.forEach((dot) => {
                dot.addEventListener('click', () => {
                    const index = Number(dot.dataset.slide || 0);
                    showSlide(index);
                    restartTimer();
                });
            });

            showSlide(0);
            restartTimer();

            const reveals = document.querySelectorAll('.reveal');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.2
            });

            reveals.forEach((item) => observer.observe(item));
        })();
    </script>
</body>

</html>
