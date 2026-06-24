<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @yield('title')

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
            font-size: clamp(1.7rem, 2.4vw, 2.6rem);
            line-height: 0.98;
            margin-top: 0.85rem;
            margin-bottom: 1rem;
            text-wrap: balance;
            z-index: 1000;
        }

        .hero p,
        .hero-lead {
            color: var(--text);
            font-size: .9rem;
            line-height: 1.78;
            margin: 0 0 1.5rem;
            max-width: 58ch;
        }

        .hero-copy {
            position: relative;
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
            text-align: center;
        }

        .metric-card span {
            display: block;
            color: var(--muted);
            font-size: 0.88rem;
            line-height: 1.45;
            font-weight: 700;
            text-align: center;

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
                linear-gradient(20deg, rgba(10, 132, 255, 0.48), rgba(0, 184, 217, 0.24)),
                url('{{ asset('landing_page/assets/slides/slide1.jpeg') }}');
        }

        .slide:nth-child(2) .slide-bg {
            background-image:
                linear-gradient(30deg, rgba(35, 193, 107, 0.5), rgba(35, 193, 107, 0.2)),
                url('{{ asset('landing_page/assets/slides/slide2.jpeg') }}');

        }

        .slide:nth-child(3) .slide-bg {
            background-image:
                linear-gradient(35deg, rgba(255, 138, 40, 0.52), rgba(255, 111, 58, 0.25)),
                url('{{ asset('landing_page/assets/slides/slide3.jpeg') }}');
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
            /*max-width: 760px; */
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
            /* max-width: 70ch;*/
            line-height: 1.78;
            color: var(--text);
            margin: 0.7rem 0 0;
            font-size: 0.95rem;
        }

        .classes-page-hero {
            padding-top: 146px;
            padding-bottom: 74px;
            background:
                radial-gradient(circle at 12% 12%, rgba(42, 199, 216, 0.14), transparent 30%),
                radial-gradient(circle at 88% 14%, rgba(245, 193, 93, 0.16), transparent 28%),
                linear-gradient(180deg, rgba(246, 250, 253, 0.98), rgba(240, 247, 252, 0.92));
        }

        .classes-page-hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
            gap: 1.2rem;
            align-items: stretch;
        }

        .classes-page-copy h1 {
            font-size: clamp(2rem, 3vw, 3rem);
            line-height: 0.95;
            margin: 0;
        }

        .classes-page-copy p {
            margin: 0.72rem 0 0;
            color: var(--text);
            line-height: 1.72;
        }

        .classes-page-chip-wrap {
            margin: 1rem 0 1.1rem;
            display: grid;
            gap: 0.6rem;
        }

        .classes-page-chip {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            max-width: 100%;
            border-radius: 999px;
            padding: 0.5rem 0.9rem;
            font-size: 0.88rem;
            color: #194567;
            background: rgba(12, 127, 242, 0.1);
            border: 1px solid rgba(12, 127, 242, 0.14);
            font-weight: 700;
        }

        .classes-page-gallery {
            border-radius: 28px;
            border: 1px solid rgba(255, 255, 255, 0.78);
            background: rgba(255, 255, 255, 0.62);
            box-shadow: var(--shadow-soft);
            padding: 0.95rem;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            grid-template-rows: 170px 170px;
            gap: 0.75rem;
        }

        .classes-page-gallery-item {
            border-radius: 18px;
            background-size: cover;
            background-position: center;
            border: 1px solid rgba(9, 23, 34, 0.08);
        }

        .classes-page-gallery-item:first-child {
            grid-column: 1 / -1;
        }

        .classes-page-schedules {
            background: linear-gradient(180deg, rgba(243, 248, 252, 0.88), rgba(241, 247, 252, 0.96));
            padding-top: 72px;
            padding-bottom: 68px;
        }

        .classes-program-block {
            margin-top: 1rem;
            border-radius: 26px;
            padding: 1.3rem;
            border: 1px solid rgba(255, 255, 255, 0.9);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.88), rgba(246, 250, 253, 0.94));
            box-shadow: 0 18px 44px rgba(9, 23, 34, 0.1);
        }

        .classes-program-block+.classes-program-block {
            margin-top: 1.15rem;
        }

        .classes-program-head h3 {
            margin: 0;
            font-size: clamp(1.45rem, 2.2vw, 2rem);
            line-height: 1;
        }

        .classes-program-head p {
            margin: 0.42rem 0 0;
            color: var(--text);
            line-height: 1.65;
            font-weight: 600;
        }

        .classes-branch-grid {
            margin-top: 0.95rem;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.75rem;
        }

        .classes-branch-grid--two {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .classes-branch-card {
            border-radius: 16px;
            padding: 0.95rem;
            background: #ffffff;
            border: 1px solid rgba(9, 23, 34, 0.1);
            box-shadow: 0 10px 22px rgba(9, 23, 34, 0.08);
        }

        .classes-branch-card h4 {
            margin: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #194567;
            font-size: 0.92rem;
            letter-spacing: 0.3px;
        }

        .classes-branch-card ul {
            margin: 0.72rem 0 0;
            padding: 0;
            list-style: none;
            display: grid;
            gap: 0.5rem;
        }

        .classes-branch-card li {
            border-radius: 10px;
            padding: 0.58rem 0.62rem;
            background: rgba(12, 127, 242, 0.06);
            border: 1px solid rgba(12, 127, 242, 0.12);
            color: #294e69;
            line-height: 1.45;
            font-size: 0.86rem;
            font-weight: 600;
        }

        .classes-program-cta {
            margin-top: 0.92rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            border-radius: 12px;
            padding: 0.78rem 1rem;
            color: #fff;
            background: linear-gradient(135deg, #091722, #15436a);
            font-weight: 800;
            box-shadow: 0 12px 28px rgba(9, 23, 34, 0.18);
        }

        .classes-page-cta {
            padding-top: 72px;
            padding-bottom: 92px;
            background:
                radial-gradient(circle at 86% 18%, rgba(12, 127, 242, 0.12), transparent 30%),
                linear-gradient(180deg, rgba(241, 247, 252, 0.95), rgba(236, 244, 250, 0.98));
        }

        .classes-page-cta-wrap {
            border-radius: 28px;
            border: 1px solid rgba(255, 255, 255, 0.92);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.9), rgba(246, 250, 253, 0.95));
            box-shadow: 0 20px 46px rgba(9, 23, 34, 0.1);
            padding: 1.5rem;
        }

        .classes-page-cta-wrap h2 {
            font-size: clamp(1.7rem, 2.6vw, 2.35rem);
            line-height: 0.98;
            margin: 0;
        }

        .classes-page-cta-wrap p {
            margin: 0.78rem 0 0;
            color: var(--text);
            line-height: 1.72;
        }

        .classes-page-cta-actions {
            margin-top: 1rem;
            display: flex;
            gap: 0.72rem;
            flex-wrap: wrap;
        }

        .classes-page-cta-note {
            margin-top: 0.85rem;
            color: #24506f;
            font-weight: 700;
            font-size: 0.92rem;
        }

        /* Classes and venues page visual upgrade */
        .classes-page-hero {
            position: relative;
            overflow: hidden;
            padding-top: 152px;
            padding-bottom: 82px;
            background:
                radial-gradient(circle at 8% 10%, rgba(20, 77, 131, 0.24), transparent 34%),
                radial-gradient(circle at 88% 12%, rgba(255, 141, 63, 0.2), transparent 26%),
                linear-gradient(160deg, #f8fbff 0%, #edf4fb 48%, #f8fbff 100%);
        }

        .classes-page-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(9, 23, 34, 0.028) 1px, transparent 1px),
                linear-gradient(90deg, rgba(9, 23, 34, 0.028) 1px, transparent 1px);
            background-size: 62px 62px;
            mask-image: linear-gradient(180deg, black 0%, rgba(0, 0, 0, 0.35) 70%, transparent 100%);
            pointer-events: none;
        }

        .classes-page-copy {
            position: relative;
            z-index: 1;
        }

        .classes-page-copy h1 {
            font-size: clamp(2.2rem, 3.3vw, 3.4rem);
            line-height: 0.9;
            letter-spacing: 0.2px;
            text-wrap: balance;
        }

        .classes-page-chip {
            background: rgba(255, 255, 255, 0.86);
            border: 1px solid rgba(16, 70, 119, 0.18);
            box-shadow: 0 8px 22px rgba(9, 23, 34, 0.08);
            color: #123c62;
            font-size: 0.84rem;
            font-weight: 800;
            letter-spacing: 0.2px;
        }

        .classes-page-gallery {
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.82);
            background: linear-gradient(160deg, rgba(255, 255, 255, 0.8), rgba(240, 247, 252, 0.86));
            box-shadow: 0 24px 52px rgba(9, 23, 34, 0.14);
            padding: 1rem;
            transform: rotate(-1.2deg);
        }

        .classes-page-gallery-item {
            border-radius: 20px;
            border: 1px solid rgba(9, 23, 34, 0.1);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.22);
            transition: transform 0.28s ease, box-shadow 0.28s ease;
        }

        .classes-page-gallery-item:hover {
            transform: translateY(-3px) scale(1.01);
            box-shadow: 0 16px 28px rgba(9, 23, 34, 0.2);
        }

        .classes-page-schedules {
            background:
                radial-gradient(circle at 12% 0%, rgba(12, 127, 242, 0.1), transparent 28%),
                linear-gradient(180deg, rgba(243, 248, 252, 0.96), rgba(239, 246, 251, 0.98));
        }

        .classes-program-block {
            margin-top: 1.2rem;
            padding: 1.45rem;
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.92);
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.95), rgba(246, 250, 253, 0.98));
            box-shadow: 0 24px 48px rgba(9, 23, 34, 0.1);
        }

        #little-strikers {
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.97), rgba(246, 250, 253, 0.98)),
                radial-gradient(circle at 8% 8%, rgba(249, 115, 22, 0.08), transparent 22%);
        }

        #little-paddlers {
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.97), rgba(246, 250, 253, 0.98)),
                radial-gradient(circle at 10% 10%, rgba(14, 165, 233, 0.1), transparent 24%);
        }

        .classes-program-head {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px dashed rgba(9, 23, 34, 0.12);
        }

        .classes-program-head h3 {
            font-size: clamp(1.5rem, 2.3vw, 2.08rem);
        }

        .classes-program-head p {
            font-size: 0.9rem;
            margin: 0;
            font-weight: 700;
        }

        .classes-branch-grid {
            gap: 0.9rem;
        }

        .classes-branch-card {
            border-radius: 20px;
            padding: 1rem;
            background: linear-gradient(180deg, #ffffff, #f7fbff);
            border: 1px solid rgba(15, 57, 92, 0.13);
            box-shadow: 0 14px 24px rgba(9, 23, 34, 0.08);
            position: relative;
            overflow: hidden;
        }

        .classes-branch-card::after {
            content: '';
            position: absolute;
            inset: auto -36px -52px auto;
            width: 130px;
            height: 130px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(12, 127, 242, 0.14), transparent 68%);
            pointer-events: none;
        }

        .classes-branch-card h4 {
            font-size: 0.84rem;
            text-transform: uppercase;
            letter-spacing: 1.3px;
            color: #1c4e78;
            margin-bottom: 0.1rem;
        }

        .classes-item-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.7rem;
            border-radius: 14px;
            padding: 0.68rem;
            background: rgba(12, 127, 242, 0.055);
            border: 1px solid rgba(12, 127, 242, 0.14);
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .classes-item-row:hover {
            transform: translateY(-1px);
            border-color: rgba(12, 127, 242, 0.26);
            box-shadow: 0 10px 20px rgba(12, 127, 242, 0.12);
        }

        .classes-item-main {
            min-width: 0;
        }

        .classes-item-title {
            margin: 0;
            font-size: 0.85rem;
            line-height: 1.4;
            color: #143a5b;
            font-weight: 800;
            text-wrap: balance;
        }

        .classes-item-schedule {
            margin: 0.2rem 0 0;
            font-size: 0.78rem;
            line-height: 1.45;
            color: #315f83;
            font-weight: 700;
        }

        .classes-item-link {
            flex-shrink: 0;
            text-decoration: none;
            border-radius: 999px;
            padding: 0.48rem 0.82rem;
            font-size: 0.75rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            color: #fff;
            background: linear-gradient(135deg, #0a2f4a, #1f6da7);
            box-shadow: 0 8px 16px rgba(9, 23, 34, 0.16);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .classes-item-link:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 18px rgba(9, 23, 34, 0.2);
        }

        .classes-program-cta {
            margin-top: 1rem;
            border-radius: 14px;
            padding: 0.82rem 1.08rem;
            font-size: 0.9rem;
            letter-spacing: 0.2px;
        }

        .classes-page-cta-wrap {
            border-radius: 32px;
            padding: 1.65rem;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(247, 251, 254, 0.97));
            box-shadow: 0 26px 50px rgba(9, 23, 34, 0.11);
        }

        .classes-page-cta-wrap h2 {
            font-size: clamp(1.75rem, 2.8vw, 2.5rem);
            line-height: 0.94;
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

        .classes-venues {
            position: relative;
            background:
                radial-gradient(circle at 10% 16%, rgba(42, 199, 216, 0.16), transparent 36%),
                radial-gradient(circle at 88% 8%, rgba(245, 193, 93, 0.18), transparent 32%),
                linear-gradient(180deg, #f5f9fd 0%, #edf4fb 56%, #f4f9fd 100%);
            border-top: 0;
            border-bottom: 0;
            margin-top: -30px;
            padding-top: 108px;
        }

        .classes-venues::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(rgba(9, 23, 34, 0.028) 1px, transparent 1px),
                linear-gradient(90deg, rgba(9, 23, 34, 0.028) 1px, transparent 1px);
            background-size: 52px 52px;
            mask-image: radial-gradient(circle at center, black 42%, transparent 96%);
            pointer-events: none;
        }

        .classes-venues::after {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(180deg, rgba(244, 249, 253, 0.2) 0%, rgba(238, 245, 251, 0.45) 28%, rgba(244, 249, 253, 0.86) 100%),
                url('{{ asset('landing_page/assets/images/img4.jpeg') }}') center top / cover no-repeat;
            opacity: 0.14;
            pointer-events: none;
        }

        .classes-venues .container {
            position: relative;
            z-index: 1;
        }

        .classes-venues-head {
            margin-bottom: 1.2rem;
            text-align: center;
        }

        .classes-venues-head p {
            max-width: 74ch;
            margin-left: auto;
            margin-right: auto;
        }

        .classes-venues-locations {
            border-radius: 24px;
            padding: 1.25rem;
            background:
                linear-gradient(155deg, rgba(14, 40, 60, 0.9) 0%, rgba(25, 77, 112, 0.88) 56%, rgba(27, 136, 214, 0.82) 100%),
                url('{{ asset('landing_page/assets/images/img3.jpeg') }}') center / cover no-repeat;
            color: #fff;
            box-shadow: 0 20px 48px rgba(9, 23, 34, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.22);
            position: relative;
            overflow: hidden;
            display: grid;
            grid-template-columns: minmax(220px, 0.42fr) minmax(0, 1fr);
            gap: 1rem;
            align-items: start;
        }

        .classes-venues-locations::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, rgba(255, 255, 255, 0.14), transparent 42%);
            pointer-events: none;
        }

        .classes-venues-list {
            list-style: none;
            padding: 0;
            margin: 0.1rem 0 0;
            display: grid;
            gap: 0.6rem;
            position: relative;
            z-index: 1;
        }

        .classes-venues-list li {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.75rem 0.9rem;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.24);
            font-weight: 700;
        }

        .classes-venues-list li span {
            display: inline-grid;
            place-items: center;
            width: 24px;
            height: 24px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.16);
        }

        .classes-venues-locations p {
            margin: 0;
            line-height: 1.7;
            opacity: 0.96;
            position: relative;
            z-index: 1;
        }

        .classes-venues-locations p+p {
            margin-top: 0.8rem;
        }

        .classes-venues-copy {
            position: relative;
            z-index: 1;
            border-left: 1px solid rgba(255, 255, 255, 0.24);
            padding-left: 1.05rem;
        }

        .classes-venues-panel {
            margin-top: 1rem;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.84), rgba(246, 250, 253, 0.9));
            border: 1px solid rgba(9, 23, 34, 0.1);
            border-radius: 24px;
            box-shadow: 0 18px 42px rgba(9, 23, 34, 0.13);
            backdrop-filter: blur(8px);
            padding: 1.45rem;
            position: relative;
            overflow: hidden;
        }

        .classes-venues-panel::before {
            content: '';
            position: absolute;
            width: 220px;
            height: 220px;
            right: -90px;
            top: -70px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(12, 127, 242, 0.16), transparent 68%);
            pointer-events: none;
        }

        .classes-venues-panel h3 {
            text-align: left;
            font-size: clamp(1.35rem, 2vw, 1.75rem);
            margin: 0 0 0.95rem;
            border-bottom: 1px solid rgba(9, 23, 34, 0.1);
            padding-bottom: 0.7rem;
            position: relative;
            z-index: 1;
        }

        .classes-venues-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
            margin-top: 0.1rem;
            position: relative;
            z-index: 1;
        }

        .classes-venues-card {
            background: #ffffff;
            border: 1px solid rgba(9, 23, 34, 0.1);
            border-radius: 16px;
            padding: 1.1rem;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
            display: grid;
            grid-template-columns: 120px minmax(0, 1fr);
            gap: 0.9rem;
            align-items: stretch;
            min-height: 190px;
        }

        .classes-venues-card::before {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            top: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--venue-color, var(--brand-blue)), color-mix(in srgb, var(--venue-color, var(--brand-blue)) 70%, #fff));
        }

        .classes-venues-card:hover {
            transform: translateY(-3px);
            border-color: color-mix(in srgb, var(--venue-color, var(--brand-blue)) 45%, rgba(9, 23, 34, 0.12));
            box-shadow: 0 12px 28px rgba(9, 23, 34, 0.12);
        }

        .classes-venues-card h4 {
            margin: 0.15rem 0 0;
            font-size: 1.9rem;
            line-height: 1;
            position: relative;
            z-index: 1;
        }

        .classes-venues-card h4 span {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1.8rem;
        }

        .classes-venues-card p {
            margin: 0.45rem 0 1rem;
            line-height: 1.6;
            color: var(--text);
            position: relative;
            z-index: 1;
        }

        .classes-venues-card-media {
            width: 100%;
            height: 100%;
            min-height: 154px;
            border-radius: 12px;
            background-size: cover;
            background-position: center center;
            border: 1px solid rgba(9, 23, 34, 0.08);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.35);
            position: relative;
            z-index: 1;
        }

        .classes-venues-card-body {
            display: flex;
            flex-direction: column;
            min-width: 0;
            position: relative;
            z-index: 1;
        }

        .classes-venues-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            text-decoration: none;
            font-weight: 800;
            padding: 0.72rem 1rem;
            border-radius: 12px;
            color: color-mix(in srgb, var(--venue-color, var(--brand-blue)) 70%, #08243a);
            background: color-mix(in srgb, var(--venue-color, var(--brand-blue)) 13%, #ffffff);
            border: 1px solid color-mix(in srgb, var(--venue-color, var(--brand-blue)) 30%, rgba(9, 23, 34, 0.2));
            position: relative;
            z-index: 1;
            margin-top: auto;
        }

        .classes-venues-bottom {
            margin-top: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.95rem;
            border-top: 1px dashed rgba(9, 23, 34, 0.14);
            padding: 1.1rem 0.8rem 0;
            position: relative;
            z-index: 1;
        }

        #programas.section-muted {
            border-bottom-color: transparent;
            padding-bottom: 104px;
        }

        .classes-venues-bottom p {
            margin: 0;
            color: var(--text);
            line-height: 1.72;
        }

        .classes-venues-cta-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            line-height: 1;
            margin-top: 0;
            position: relative;
            z-index: 2;
            text-decoration: none;
            font-weight: 800;
            color: #fff;
            background: linear-gradient(135deg, #081723, #153d5f 55%, #0d7acc);
            border-radius: 14px;
            padding: 0.9rem 1.2rem;
            box-shadow: 0 12px 28px rgba(9, 23, 34, 0.21);
            transition: transform 0.24s ease, box-shadow 0.24s ease;
            max-width: 200px;
        }

        .classes-venues-cta-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 32px rgba(9, 23, 34, 0.24);
        }

        .birthday-section {
            position: relative;
            padding-top: 88px;
            padding-bottom: 62px;
            background:
                radial-gradient(circle at 10% 14%, rgba(245, 193, 93, 0.2), transparent 34%),
                radial-gradient(circle at 86% 82%, rgba(42, 199, 216, 0.18), transparent 31%),
                linear-gradient(180deg, #f5f9fd 0%, #edf4fa 54%, #f7fbff 100%);
        }

        .birthday-section::before {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                linear-gradient(rgba(9, 23, 34, 0.026) 1px, transparent 1px),
                linear-gradient(90deg, rgba(9, 23, 34, 0.026) 1px, transparent 1px);
            background-size: 56px 56px;
            mask-image: radial-gradient(circle at center, black 40%, transparent 96%);
        }

        .birthday-section::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 120px;
            pointer-events: none;
            background: linear-gradient(180deg, rgba(247, 251, 255, 0) 0%, rgba(242, 248, 252, 0.96) 100%);
        }

        .birthday-wrap {
            position: relative;
            z-index: 1;
            border-radius: 28px;
            padding: 1.6rem;
            border: 1px solid rgba(255, 255, 255, 0.88);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.86), rgba(244, 249, 253, 0.9));
            box-shadow: 0 20px 48px rgba(9, 23, 34, 0.11);
            backdrop-filter: blur(10px);
            display: grid;
            grid-template-columns: minmax(220px, 0.33fr) minmax(0, 1fr);
            gap: 1.2rem;
            align-items: stretch;
        }

        .birthday-media {
            border-radius: 18px;
            min-height: 100%;
            background-size: cover;
            background-position: center;
            border: 1px solid rgba(9, 23, 34, 0.1);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.3);
        }

        .birthday-content {
            min-width: 0;
        }

        .birthday-head {
            max-width: 78ch;
        }

        .birthday-head h2 {
            font-size: clamp(2rem, 3vw, 3rem);
            line-height: 0.95;
        }

        .birthday-hero {
            margin: 0.7rem 0 0.78rem;
            color: #0b3554;
            font-weight: 800;
            line-height: 1.5;
        }

        .birthday-head p {
            margin: 0;
            color: var(--text);
            line-height: 1.72;
        }

        .birthday-grid {
            margin-top: 1.1rem;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.9rem;
        }

        .birthday-point {
            display: flex;
            align-items: flex-start;
            gap: 0.62rem;
            border-radius: 16px;
            padding: 0.92rem 0.95rem;
            background: rgba(255, 255, 255, 0.84);
            border: 1px solid rgba(9, 23, 34, 0.08);
            box-shadow: 0 8px 18px rgba(9, 23, 34, 0.08);
        }

        .birthday-point span {
            font-size: 1.22rem;
            line-height: 1;
            transform: translateY(1px);
        }

        .birthday-point p {
            margin: 0;
            color: #224b67;
            line-height: 1.58;
            font-weight: 700;
            font-size: 0.92rem;
        }

        .birthday-actions {
            margin-top: 1.08rem;
            display: flex;
        }

        .birthday-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-weight: 800;
            border-radius: 14px;
            padding: 0.9rem 1.24rem;
            color: #fff;
            background: linear-gradient(135deg, #091722, #153e61 58%, #0d7acc);
            box-shadow: 0 12px 28px rgba(9, 23, 34, 0.22);
            transition: transform 0.24s ease, box-shadow 0.24s ease;
        }

        .birthday-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 32px rgba(9, 23, 34, 0.25);
        }

        #contacto {
            position: relative;
            padding-top: 64px;
            padding-bottom: 90px;
            background:
                radial-gradient(circle at 14% 14%, rgba(42, 199, 216, 0.14), transparent 34%),
                radial-gradient(circle at 84% 84%, rgba(245, 193, 93, 0.18), transparent 30%),
                linear-gradient(180deg, rgba(243, 248, 252, 0.96) 0%, rgba(241, 247, 252, 0.92) 100%);
        }

        #contacto::before {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: linear-gradient(120deg, rgba(255, 255, 255, 0.28), transparent 34%);
            opacity: 0.08;
        }

        #contacto::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 78px;
            pointer-events: none;
            background: linear-gradient(180deg, rgba(241, 247, 252, 0) 0%, rgba(8, 23, 35, 0.44) 100%);
            opacity: 0.22;
        }

        .contact-layout {
            margin: 0 auto;
            display: grid;
            grid-template-columns: 0.95fr 1.05fr;
            gap: 1.15rem;
            position: relative;
            z-index: 1;
        }

        .contact-info {
            border-radius: 28px;
            padding: 1.55rem;
            color: #fff;
            background:
                radial-gradient(circle at 84% 8%, rgba(255, 255, 255, 0.2), transparent 32%),
                linear-gradient(155deg, #081723, #12304a 48%, #0d86d9 100%);
            box-shadow: 0 28px 70px rgba(9, 23, 34, 0.22);
            position: relative;
            overflow: hidden;
        }

        .contact-info::after {
            content: '';
            position: absolute;
            right: -36px;
            bottom: -36px;
            width: 180px;
            height: 180px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.08);
        }

        .contact-info h3 {
            margin: 0;
            font-size: clamp(1.65rem, 2.4vw, 2.1rem);
            line-height: 0.98;
            max-width: 16ch;
            position: relative;
            z-index: 1;
        }

        .contact-info p {
            margin: 0.82rem 0 0;
            line-height: 1.7;
            opacity: 0.97;
            position: relative;
            z-index: 1;
        }

        .contact-info-points {
            margin-top: 1.15rem;
            display: grid;
            gap: 0.7rem;
            position: relative;
            z-index: 1;
        }

        .contact-info-point {
            padding: 0.95rem 1rem;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.11);
            border: 1px solid rgba(255, 255, 255, 0.16);
        }

        .contact-info-point strong {
            display: block;
            margin-bottom: 0.28rem;
            font-size: 0.94rem;
        }

        .contact-info-point span {
            display: block;
            font-size: 0.86rem;
            line-height: 1.45;
            opacity: 0.96;
        }

        .contact-form {
            background: rgba(255, 255, 255, 0.86);
            border: 1px solid rgba(255, 255, 255, 0.96);
            border-radius: 28px;
            padding: 1.55rem;
            box-shadow: 0 20px 48px rgba(9, 23, 34, 0.12);
            backdrop-filter: blur(16px);
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
        textarea,
        select {
            width: 100%;
            border: 1px solid rgba(72, 97, 115, 0.18);
            background: rgba(246, 248, 251, 0.92);
            border-radius: 10px;
            padding: 0.5rem 0.5rem;
            font: inherit;
            color: var(--ink);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        input:focus,
        textarea:focus,
        select:focus {
            border-color: var(--brand-blue);
            box-shadow: 0 0 0 4px rgba(10, 132, 255, 0.12);
            outline: none;
        }

        textarea {
            min-height: 80px;
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
            transition: transform 0.22s ease, box-shadow 0.22s ease;
        }

        .submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 38px rgba(9, 23, 34, 0.26);
        }

        footer {
            position: relative;
            margin-top: 0;
            padding: 22px 0 30px;
            background:
                radial-gradient(circle at 10% 10%, rgba(18, 72, 112, 0.22), transparent 32%),
                radial-gradient(circle at 88% 82%, rgba(33, 194, 212, 0.22), transparent 30%),
                linear-gradient(165deg, #081723 0%, #0f2a3f 46%, #123a5a 100%);
            overflow: hidden;
        }

        footer::before {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                linear-gradient(rgba(255, 255, 255, 0.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.06) 1px, transparent 1px);
            background-size: 56px 56px;
            mask-image: radial-gradient(circle at center, black 44%, transparent 95%);
            opacity: 0.28;
        }

        .footer-inner {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(0, 0.72fr) minmax(0, 0.94fr) minmax(0, 0.94fr);
            gap: 1.2rem;
            color: #d8e8f3;
            padding: 1.5rem;
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.22);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.09), rgba(255, 255, 255, 0.04));
            box-shadow: 0 20px 56px rgba(4, 12, 22, 0.35);
            backdrop-filter: blur(10px);
            position: relative;
            z-index: 1;
            overflow: hidden;
        }

        .footer-orbit {
            position: absolute;
            width: 330px;
            height: 330px;
            right: -130px;
            bottom: -150px;
            border-radius: 999px;
            border: 24px solid rgba(255, 255, 255, 0.08);
            pointer-events: none;
        }

        .footer-brand {
            display: flex;
            flex-direction: column;
            gap: 0.78rem;
            position: relative;
            z-index: 1;
        }

        .footer-brand img {
            width: 174px;
            height: 62px;
            object-fit: contain;
            display: block;
            filter: brightness(1.25);
        }

        .footer-brand p,
        .footer-note,
        .footer-links a,
        .footer-social a,
        .footer-cta p {
            margin: 0;
            color: rgba(224, 237, 248, 0.9);
            line-height: 1.72;
        }

        .footer-title {
            display: block;
            margin-bottom: 0.65rem;
            color: #ffffff;
            font-size: 0.82rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .footer-links {
            display: grid;
            gap: 0.7rem;
            align-content: start;
            position: relative;
            z-index: 1;
        }

        .footer-social {
            display: grid;
            gap: 0.7rem;
            align-content: start;
            position: relative;
            z-index: 1;
        }

        .footer-links a {
            text-decoration: none;
            font-weight: 700;
            transition: color 0.2s ease, transform 0.2s ease, opacity 0.2s ease;
        }

        .footer-social a {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            text-decoration: none;
            font-weight: 700;
            transition: color 0.2s ease, transform 0.2s ease, opacity 0.2s ease;
        }

        .social-icon {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }

        .footer-social a:hover {
            color: #ffffff;
            opacity: 1;
            transform: translateX(2px);
        }

        .footer-links a:hover {
            color: #ffffff;
            opacity: 1;
            transform: translateX(2px);
        }

        .footer-cta {
            padding: 1rem;
            border-radius: 24px;
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.16), rgba(255, 255, 255, 0.08));
            border: 1px solid rgba(255, 255, 255, 0.22);
            color: #fff;
            box-shadow: 0 14px 34px rgba(4, 12, 22, 0.3);
            position: relative;
            z-index: 1;
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
            padding: 0.84rem 1.02rem;
            border-radius: 14px;
            background: linear-gradient(135deg, #ffffff, #f2f8ff);
            color: #0b2233;
            font-weight: 800;
            text-decoration: none;
            box-shadow: 0 12px 24px rgba(4, 12, 22, 0.2);
        }

        .footer-bottom {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.16);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.8rem;
            flex-wrap: wrap;
            font-size: 0.88rem;
            color: rgba(216, 232, 243, 0.84);
        }

        .whatsapp-float {
            position: fixed;
            right: 18px;
            bottom: 18px;
            width: 56px;
            height: 56px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(145deg, #27d667, #18b452);
            color: #fff;
            box-shadow: 0 14px 30px rgba(18, 132, 59, 0.42);
            z-index: 3000;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: none;
            cursor: pointer;
        }

        .whatsapp-float:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 18px 36px rgba(18, 132, 59, 0.48);
        }

        .whatsapp-float svg {
            width: 30px;
            height: 30px;
        }

        /* ── WhatsApp Modal Styles ── */
        .whatsapp-modal {
            position: fixed;
            bottom: 86px;
            right: 18px;
            width: 290px;
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 22px;
            box-shadow: 0 15px 35px rgba(9, 23, 34, 0.15);
            backdrop-filter: blur(12px);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            z-index: 3001;
            opacity: 0;
            visibility: hidden;
            transform: translateY(12px) scale(0.95);
            transform-origin: bottom right;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .whatsapp-modal.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
        }

        .whatsapp-modal-header {
            background: #091722;
            padding: 0.9rem 1.1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .whatsapp-modal-header h4 {
            margin: 0;
            font-family: 'Baloo 2', cursive;
            color: #ffffff;
            font-size: 0.95rem;
            font-weight: 700;
        }

        .whatsapp-modal-close {
            background: transparent;
            border: none;
            color: rgba(255, 255, 255, 0.6);
            font-size: 1.3rem;
            cursor: pointer;
            line-height: 1;
            padding: 0;
            transition: color 0.2s ease;
        }

        .whatsapp-modal-close:hover {
            color: #ffffff;
        }

        .whatsapp-modal-body {
            padding: 0.8rem;
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
        }

        .whatsapp-option {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.75rem 1rem;
            border-radius: 14px;
            text-decoration: none;
            transition: all 0.25s ease;
            border: 1px solid transparent;
        }

        .whatsapp-option-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .whatsapp-option-info {
            display: flex;
            flex-direction: column;
        }

        .whatsapp-option-name {
            font-weight: 800;
            font-size: 0.92rem;
            color: #091722;
            line-height: 1.2;
        }

        .whatsapp-option-desc {
            font-size: 0.76rem;
            color: #6d8494;
            font-weight: 600;
        }

        .paddlers-option {
            background: rgba(14, 165, 233, 0.05);
        }

        .paddlers-option .whatsapp-option-icon {
            background: #e0f2fe;
            color: #0284c7;
        }

        .paddlers-option:hover {
            background: rgba(14, 165, 233, 0.09);
            border-color: rgba(14, 165, 233, 0.2);
            transform: translateX(3px);
        }

        .strikers-option {
            background: rgba(249, 115, 22, 0.05);
        }

        .strikers-option .whatsapp-option-icon {
            background: #ffedd5;
            color: #ea580c;
        }

        .strikers-option:hover {
            background: rgba(249, 115, 22, 0.09);
            border-color: rgba(249, 115, 22, 0.2);
            transform: translateX(3px);
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

            .birthday-section {
                padding-bottom: 46px;
            }

            .birthday-wrap {
                grid-template-columns: 1fr;
                gap: 0.9rem;
            }

            .birthday-media {
                min-height: 170px;
            }

            .birthday-section::after {
                height: 76px;
            }

            #contacto {
                padding-top: 52px;
                padding-bottom: 28px;
            }

            #contacto::after {
                height: 56px;
            }

            .contact-layout {
                grid-template-columns: 1fr;
            }

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
            .classes-page-hero-grid,
            .birthday-grid,
            .classes-venues-grid,
            .contact-layout,
            .row,
            .hero-metrics,
            .about-points {
                grid-template-columns: 1fr;
            }

            .classes-page-gallery {
                grid-template-columns: 1fr;
                grid-template-rows: none;
            }

            .classes-page-gallery-item {
                min-height: 170px;
            }

            .classes-page-gallery-item:first-child {
                grid-column: auto;
            }

            .classes-branch-grid,
            .classes-branch-grid--two {
                grid-template-columns: 1fr;
            }

            .classes-program-head {
                flex-direction: column;
                align-items: flex-start;
            }

            .classes-item-row {
                flex-direction: column;
                align-items: stretch;
            }

            .classes-item-link {
                text-align: center;
            }

            .classes-page-cta-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .classes-venues-head,
            .classes-venues-locations,
            .classes-venues-panel {
                border-radius: 22px;
            }

            .classes-venues {
                margin-top: -24px;
                padding-top: 96px;
            }

            .classes-venues-locations {
                grid-template-columns: 1fr;
                gap: 0.9rem;
            }

            .classes-venues-copy {
                border-left: 0;
                padding-left: 0;
            }

            .classes-venues-card {
                min-height: 0;
                grid-template-columns: 1fr;
            }

            .classes-venues-card-media {
                min-height: 128px;
            }

            .classes-venues-bottom {
                gap: 0.75rem;
                padding-top: 0.95rem;
            }

            .classes-venues-cta-btn {
                width: min(260px, 100%);
            }

            .classes-venues-panel h3 {
                text-align: center;
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

            .contact-info h3,
            .hero h1 {
                max-width: none;
            }

            .metric-card,
            .panel,
            .brand-card,
            .contact-info,
            .contact-form,
            .footer-inner {
                border-radius: 22px;
            }

            .footer-orbit {
                width: 220px;
                height: 220px;
                right: -92px;
                bottom: -102px;
                border-width: 16px;
            }

            .footer-inner {
                grid-template-columns: 1fr;
            }

            .whatsapp-float {
                right: 12px;
                bottom: 12px;
                width: 52px;
                height: 52px;
            }

            .whatsapp-modal {
                right: 12px;
                bottom: 78px;
                width: 270px;
            }

            .footer-cta {
                border-radius: 22px;
            }
        }

        /* ── Testimonials Section ── */
        .testimonials-section {
            position: relative;
            padding-top: 84px;
            padding-bottom: 84px;
            background:
                radial-gradient(circle at 86% 14%, rgba(245, 193, 93, 0.16), transparent 28%),
                radial-gradient(circle at 12% 82%, rgba(12, 127, 242, 0.12), transparent 30%),
                linear-gradient(180deg, rgba(241, 247, 252, 0.95), rgba(236, 244, 250, 0.98));
        }

        .testimonials-section .section-kicker {
            color: var(--brand-orange);
        }

        .testimonials-section::before {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                linear-gradient(rgba(9, 23, 34, 0.024) 1px, transparent 1px),
                linear-gradient(90deg, rgba(9, 23, 34, 0.024) 1px, transparent 1px);
            background-size: 56px 56px;
            mask-image: radial-gradient(circle at center, black 40%, transparent 96%);
        }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
            gap: 1.5rem;
            margin-top: 2.2rem;
            position: relative;
            z-index: 1;
        }

        .testimonial-card {
            background: rgba(255, 255, 255, 0.72);
            border: 1px solid rgba(255, 255, 255, 0.85);
            border-radius: 28px;
            padding: 2rem;
            box-shadow: 0 16px 36px rgba(9, 23, 34, 0.05);
            backdrop-filter: blur(10px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            min-height: 250px;
        }

        .testimonial-card::after {
            content: '';
            position: absolute;
            width: 140px;
            height: 140px;
            right: -40px;
            bottom: -40px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(245, 193, 93, 0.12), transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        .testimonial-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 24px 50px rgba(9, 23, 34, 0.1);
            background: rgba(255, 255, 255, 0.9);
            border-color: rgba(245, 141, 63, 0.25);
        }

        .testimonial-quote {
            position: relative;
            z-index: 1;
            margin-bottom: 1.5rem;
        }

        .quote-icon {
            font-family: 'Baloo 2', cursive;
            font-size: 4rem;
            line-height: 1;
            color: rgba(245, 141, 63, 0.18);
            position: absolute;
            top: -1.8rem;
            left: -0.5rem;
            pointer-events: none;
        }

        .testimonial-quote p {
            margin: 0;
            font-size: 0.94rem;
            line-height: 1.7;
            color: #314d64;
            font-style: italic;
            position: relative;
            z-index: 1;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
            z-index: 1;
            border-top: 1px solid rgba(9, 23, 34, 0.06);
            padding-top: 1.2rem;
        }

        .testimonial-author img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
            box-shadow: 0 6px 16px rgba(9, 23, 34, 0.1);
        }

        .testimonial-author div {
            display: flex;
            flex-direction: column;
        }

        .testimonial-author strong {
            display: block;
            font-size: 0.96rem;
            color: #091722;
            font-weight: 800;
        }

        .testimonial-author span {
            font-size: 0.78rem;
            color: #6d8494;
            font-weight: 600;
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
                <a href="{{ route('landing.index') }}#inicio">Inicio</a>
                <a href="{{ route('landing.index') }}#programas">Programas</a>
                <a href="{{ route('landing.index') }}#clases-sedes">Clases</a>
                <a href="{{ route('landing.index') }}#cumpleanos">Cumpleaños</a>
                <a href="{{ route('landing.index') }}#contacto">Contacto</a>
                <a href="{{ route('login') }}">Ingresar</a>
            </nav>

            <button class="nav-toggle" type="button" aria-expanded="false" aria-label="Abrir menu de navegacion">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                    <path d="M4 7h16"></path>
                    <path d="M4 12h16"></path>
                    <path d="M4 17h16"></path>
                </svg>
            </button>

            <a class="cta-pill" href="{{ route('enrollment.wizard') }}">Inscribir a mi hijo</a>
        </div>
    </header>

    @yield('content')

    <footer>
        <div class="container footer-inner">
            <div class="footer-orbit" aria-hidden="true"></div>

            <div class="footer-brand">
                <img src="{{ asset('landing_page/logos/lbinc-admin.png') }}" alt="Logo Little Brands Inc">
                <h3>Programas educacionales a través del deporte</h3>
                <p>En Little Brands Inc creamos experiencias deportivas diseñadas especialmente para los más pequeños,
                    ayudando a los niños a desarrollar coordinación, confianza y amor por el deporte en un ambiente
                    seguro, divertido y acompañado por sus familias.</p>
                <p class="footer-note">⚽ Little Strikers  🎾 Little Paddlers</p>
            </div>

            <nav class="footer-links" aria-label="Enlaces del pie de página">
                <span class="footer-title">Navegación</span>
                <a href="{{ route('landing.index') }}#inicio">Inicio</a>
                <a href="{{ route('landing.index') }}#programas">Programas</a>
                <a href="{{ route('landing.index') }}#clases-sedes">Clases</a>
                <a href="{{ route('landing.index') }}#cumpleanos">Cumpleaños</a>
                <a href="{{ route('landing.index') }}#contacto">Contacto</a>
            </nav>

            <div class="footer-social" aria-label="Redes sociales">
                <span class="footer-title">Redes Sociales</span>
                <a href="https://www.instagram.com/littlestrikers/" target="_blank" rel="noopener noreferrer">
                    <svg class="social-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <rect x="3.5" y="3.5" width="17" height="17" rx="5" stroke="currentColor" stroke-width="1.8" />
                        <circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.8" />
                        <circle cx="17.5" cy="6.5" r="1.2" fill="currentColor" />
                    </svg>
                    Little Strikers Instagram
                </a>
                <a href="https://www.instagram.com/littlepaddlersve/" target="_blank" rel="noopener noreferrer">
                    <svg class="social-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <rect x="3.5" y="3.5" width="17" height="17" rx="5" stroke="currentColor" stroke-width="1.8" />
                        <circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.8" />
                        <circle cx="17.5" cy="6.5" r="1.2" fill="currentColor" />
                    </svg>
                    Little Paddlers Instagram
                </a>
                <a href="https://www.tiktok.com/@little.paddlers" target="_blank" rel="noopener noreferrer">
                    <svg class="social-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M14 4v7.2a3.8 3.8 0 1 1-2.8-3.65" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M14 4c1.3 2 2.6 2.9 4.8 3.1" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" />
                    </svg>
                    Little Paddlers TikTok
                </a>
                <a href="https://api.whatsapp.com/send?phone=584141501108" target="_blank" rel="noopener noreferrer">
                    <svg class="social-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12.2 3.6c-4.7 0-8.5 3.8-8.5 8.5 0 1.6.4 3.1 1.2 4.4l-1.2 3.9 4-.9a8.5 8.5 0 0 0 4.5 1.3c4.7 0 8.5-3.8 8.5-8.5s-3.8-8.7-8.5-8.7Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" />
                        <path d="M9.3 8.8c.2-.5.5-.5.8-.5h.7c.2 0 .4.1.5.4l.8 1.8c.1.2.1.4 0 .5l-.4.6c-.1.2-.1.4 0 .6.4.8 1.1 1.5 1.9 2 .2.1.4.1.6 0l.6-.4c.2-.1.4-.1.6 0l1.7.8c.3.1.4.3.4.5v.7c0 .3-.1.6-.5.8-.5.2-1.6.3-3.1-.3a7.8 7.8 0 0 1-4.2-4.2c-.6-1.5-.5-2.6-.3-3.1Z" fill="currentColor" />
                    </svg>
                    WhatsApp Little Brands
                </a>
            </div>

            <div class="footer-cta">
                <span class="footer-title">Siguiente paso</span>
                <p>Si quieres conocer horarios, edades y disponibilidad, escribe al equipo y te orientamos según la
                    etapa de tu hijo.</p>
                <a href="{{ route('landing.index') }}#contacto">Solicitar información</a>
            </div>
        </div>
    </footer>

    <!-- Micro Modal de WhatsApp -->
    <div class="whatsapp-modal" id="whatsappModal" role="dialog" aria-modal="true" aria-labelledby="whatsappModalTitle">
        <div class="whatsapp-modal-header">
            <h4 id="whatsappModalTitle">¿Con quién deseas hablar?</h4>
            <button class="whatsapp-modal-close" id="whatsappCloseBtn" aria-label="Cerrar">&times;</button>
        </div>
        <div class="whatsapp-modal-body">
            <a href="https://api.whatsapp.com/send?phone=584141501108&text=Hola!%20Quisiera%20informaci%C3%B3n%20sobre%20las%20clases%20de%20Little%20Paddlers." target="_blank" rel="noopener noreferrer" class="whatsapp-option paddlers-option">
                <span class="whatsapp-option-icon">🎾</span>
                <div class="whatsapp-option-info">
                    <span class="whatsapp-option-name">Little Paddlers</span>
                    <span class="whatsapp-option-desc">Pádel Infantil</span>
                </div>
            </a>
            <a href="https://api.whatsapp.com/send?phone=584144662043&text=Hola!%20Quisiera%20informaci%C3%B3n%20sobre%20las%20clases%20de%20Little%20Strikers." target="_blank" rel="noopener noreferrer" class="whatsapp-option strikers-option">
                <span class="whatsapp-option-icon">⚽</span>
                <div class="whatsapp-option-info">
                    <span class="whatsapp-option-name">Little Strikers</span>
                    <span class="whatsapp-option-desc">Fútbol Infantil</span>
                </div>
            </a>
        </div>
    </div>

    <button class="whatsapp-float" id="whatsappFloatBtn" aria-label="Escribir por WhatsApp" aria-haspopup="true" aria-expanded="false">
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M12.2 3.4c-4.8 0-8.7 3.9-8.7 8.7 0 1.6.4 3.2 1.2 4.5l-1.2 3.9 4-.9a8.7 8.7 0 0 0 4.6 1.3c4.8 0 8.7-3.9 8.7-8.7s-3.9-8.8-8.6-8.8Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" />
            <path d="M9.3 8.8c.2-.5.5-.5.8-.5h.7c.2 0 .4.1.5.4l.8 1.8c.1.2.1.4 0 .5l-.4.6c-.1.2-.1.4 0 .6.4.8 1.1 1.5 1.9 2 .2.1.4.1.6 0l.6-.4c.2-.1.4-.1.6 0l1.7.8c.3.1.4.3.4.5v.7c0 .3-.1.6-.5.8-.5.2-1.6.3-3.1-.3a7.8 7.8 0 0 1-4.2-4.2c-.6-1.5-.5-2.6-.3-3.1Z" fill="currentColor" />
        </svg>
    </button>

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

            // WhatsApp modal toggling logic
            const whatsappFloatBtn = document.getElementById('whatsappFloatBtn');
            const whatsappModal = document.getElementById('whatsappModal');
            const whatsappCloseBtn = document.getElementById('whatsappCloseBtn');

            if (whatsappFloatBtn && whatsappModal) {
                whatsappFloatBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const isShown = whatsappModal.classList.toggle('show');
                    whatsappFloatBtn.setAttribute('aria-expanded', isShown ? 'true' : 'false');
                });

                if (whatsappCloseBtn) {
                    whatsappCloseBtn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        whatsappModal.classList.remove('show');
                        whatsappFloatBtn.setAttribute('aria-expanded', 'false');
                    });
                }

                document.addEventListener('click', (e) => {
                    if (!whatsappModal.contains(e.target) && e.target !== whatsappFloatBtn && !whatsappFloatBtn.contains(e.target)) {
                        whatsappModal.classList.remove('show');
                        whatsappFloatBtn.setAttribute('aria-expanded', 'false');
                    }
                });

                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') {
                        whatsappModal.classList.remove('show');
                        whatsappFloatBtn.setAttribute('aria-expanded', 'false');
                    }
                });
            }
        })();
    </script>
</body>

</html>
