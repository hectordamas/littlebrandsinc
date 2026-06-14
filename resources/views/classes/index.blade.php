@extends('layouts.landing')

@section('title')
    <title>Clases y Sedes | Little Brands Inc</title>
    <meta name="description" content="En Little Brands ofrecemos programas deportivos diseñados especialmente para bebés y niños pequeños, en espacios seguros, dinámicos y llenos de diversión donde pueden aprender, moverse y desarrollar nuevas habilidades mientras disfrutan del deporte.">
    <meta name="keywords" content="Clases deportivas para niños, sedes de Little Brands Inc, horarios de clases infantiles, fútbol para niños, pádel para niños, clases de prueba gratuita, programas deportivos para niños en Caracas">
    <meta property="og:title" content="Clases y Sedes | Little Brands Inc">
    <meta property="og:description" content="En Little Brands ofrecemos programas deportivos diseñados especialmente para bebés y niños pequeños, en espacios seguros, dinámicos y llenos de diversión donde pueden aprender, moverse y desarrollar nuevas habilidades mientras disfrutan del deporte.">
    <meta property="og:image" content="{{ asset('landing_page/assets/images/img6.jpeg') }}">
    <meta property="og:url" content="{{ url()->current() }}">   
    <meta name="twitter:title" content="Clases y Sedes | Little Brands Inc">    
    <meta name="twitter:description" content="En Little Brands ofrecemos programas deportivos diseñados especialmente para bebés y niños pequeños, en espacios seguros, dinámicos y llenos de diversión donde pueden aprender, moverse y desarrollar nuevas habilidades mientras disfrutan del deporte.">
    <meta name="twitter:image" content="{{ asset('landing_page/assets/images/img6.jpeg') }}">
    <meta name="twitter:card" content="summary_large_image">

    <style>
        .classes-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.25rem;
            margin-top: 1.75rem;
            margin-bottom: 2.25rem;
        }

        .class-info-card {
            background: rgba(255, 255, 255, 0.65);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 22px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(9, 23, 34, 0.04);
            backdrop-filter: blur(12px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            gap: 0.9rem;
            position: relative;
            overflow: hidden;
        }

        .class-info-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            transition: width 0.3s ease;
        }

        /* Strikers specific styling */
        #little-strikers .class-info-card::before {
            background: #f97316;
        }
        #little-strikers .class-info-card:hover::before {
            width: 6px;
        }
        #little-strikers .class-icon-wrapper {
            background: linear-gradient(135deg, #fff7ed, #ffedd5);
            color: #ea580c;
        }

        /* Paddlers specific styling */
        #little-paddlers .class-info-card::before {
            background: #0ea5e9;
        }
        #little-paddlers .class-info-card:hover::before {
            width: 6px;
        }
        #little-paddlers .class-icon-wrapper {
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
            color: #0284c7;
        }

        .class-info-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 18px 38px rgba(9, 23, 34, 0.08);
            background: rgba(255, 255, 255, 0.88);
            border-color: rgba(255, 255, 255, 0.95);
        }

        .class-info-header {
            display: flex;
            align-items: center;
            gap: 0.95rem;
        }

        .class-icon-wrapper {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            box-shadow: inset 0 2px 4px rgba(255, 255, 255, 0.8), 0 4px 10px rgba(0, 0, 0, 0.03);
            flex-shrink: 0;
        }

        .class-info-title-area {
            min-width: 0;
        }

        .class-info-title-area h4 {
            margin: 0;
            font-family: 'Baloo 2', cursive;
            font-size: 1.25rem;
            color: #091722;
            line-height: 1.15;
        }

        .class-age {
            display: inline-block;
            font-size: 0.76rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #6d8494;
            margin-top: 0.15rem;
        }

        .class-info-card p {
            margin: 0;
            font-size: 0.88rem;
            line-height: 1.6;
            color: #486173;
        }

        .classes-section-subtitle {
            margin-top: 1.8rem;
            margin-bottom: 0.8rem;
            color: #1c4e78;
            font-weight: 800;
            font-size: 0.92rem;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border-bottom: 1px dashed rgba(9, 23, 34, 0.08);
            padding-bottom: 0.5rem;
        }
    </style>
@endsection

@section('content')
    <main>
        <section class="classes-page-hero" id="inicio-clases">
            <div class="container classes-page-hero-grid">
                <div class="classes-page-copy reveal">
                    <span class="section-kicker">Clases y Sedes</span>
                    <h1>Clases y Sedes</h1>
                    <p>En Little Brands ofrecemos programas deportivos diseñados especialmente para bebés y niños pequeños, en espacios seguros, dinámicos y llenos de diversión donde pueden aprender, moverse y desarrollar nuevas habilidades mientras disfrutan del deporte.</p>
                    {{--
                    <p>
                        Cada una de nuestros programas está diseñada específicamente para acompañar el desarrollo de los
                        niños durante sus primeros años, combinando aprendizaje, deporte y diversión en experiencias
                        adaptadas a cada etapa.
                    </p>
                    <p>
                        Aunque cada programa tiene su propia identidad y metodología, todos comparten la misma visión:
                        ayudar a los niños a crecer con confianza, desarrollar habilidades físicas y sociales, y descubrir
                        el deporte en un ambiente seguro, positivo y lleno de motivación.
                    </p>
                    --}}

                    <div class="classes-page-chip-wrap" style="margin-bottom: 1.5rem;">
                        <span class="classes-page-chip">📍 Sedes en Caracas: San Luis • Los Campitos • Los Chorros</span>
                        <span class="classes-page-chip">🎁 Clases de prueba gratuita disponible</span>
                    </div>

                    <a class="btn-main" href="{{ $freeTrialUrl }}">Agendar Clase de prueba gratuita</a>
                </div>

                <div class="classes-page-gallery reveal" aria-label="Galería de programas deportivos">
                    <div class="classes-page-gallery-item"
                        style="background-image: url('{{ asset('landing_page/assets/images/img6.jpeg') }}');"></div>
                    <div class="classes-page-gallery-item"
                        style="background-image: url('{{ asset('landing_page/assets/images/img4.jpeg') }}');"></div>
                    <div class="classes-page-gallery-item"
                        style="background-image: url('{{ asset('landing_page/assets/images/img3.jpeg') }}');"></div>
                </div>
            </div>
        </section>

        <section class="classes-page-schedules" id="horarios">
            <div class="container">
                <div class="section-head reveal">
                    <span class="section-kicker">Horarios</span>
                    <h2>Horarios y clases disponibles</h2>
                    <p>
                        Aquí encontrarás la disponibilidad por programa y por sede para facilitar tu inscripción.
                    </p>
                </div>

                <article class="classes-program-block reveal" id="little-strikers">
                    <header class="classes-program-head">
                        <h3>⚽ Little Strikers</h3>
                        <p>Fútbol para niños de 18 meses a 4 años</p>
                    </header>

                    <!-- Explicación de cada clase (Categories Info Grid) -->
                    <div class="classes-info-grid">
                        <div class="class-info-card">
                            <div class="class-info-header">
                                <div class="class-icon-wrapper">👶</div>
                                <div class="class-info-title-area">
                                    <h4>Baby Strikers</h4>
                                    <span class="class-age">18 a 24 meses</span>
                                </div>
                            </div>
                            <p>Primeros pasos en el deporte a través del juego, la exploración y el movimiento. Los niños desarrollan habilidades motoras básicas, equilibrio, coordinación y confianza mientras se familiarizan con el balón y las dinámicas grupales.</p>
                        </div>
                        
                        <div class="class-info-card">
                            <div class="class-info-header">
                                <div class="class-icon-wrapper">🏃</div>
                                <div class="class-info-title-area">
                                    <h4>Mini Strikers</h4>
                                    <span class="class-age">24 a 36 meses</span>
                                </div>
                            </div>
                            <p>Etapa enfocada en el desarrollo de la coordinación, el control corporal y las primeras habilidades futbolísticas. A través de juegos y actividades dinámicas, los niños fortalecen su confianza, socialización y capacidad para seguir instrucciones.</p>
                        </div>
                        
                        <div class="class-info-card">
                            <div class="class-info-header">
                                <div class="class-icon-wrapper">⚡</div>
                                <div class="class-info-title-area">
                                    <h4>Super Strikers</h4>
                                    <span class="class-age">3 a 4 años</span>
                                </div>
                            </div>
                            <p>Introducción a conceptos básicos del fútbol mediante ejercicios adaptados a su edad. Los niños desarrollan coordinación, agilidad, trabajo en equipo y mayor control del balón mientras participan en actividades más estructuradas y divertidas.</p>
                        </div>
                    </div>

                    <div class="classes-section-subtitle">
                        <span>📅 Horarios y Sedes Disponibles</span>
                    </div>

                    <div class="classes-branch-grid">
                        @foreach ($strikersSchedules as $branch)
                            <section class="classes-branch-card">
                                <h4>{{ $branch['branch'] }}</h4>
                                <ul>
                                    @foreach ($branch['items'] as $item)
                                        <li class="classes-item-row">
                                            <div class="classes-item-main">
                                                <p class="classes-item-title">{{ $item['title'] }}</p>
                                                <p class="classes-item-schedule">{{ $item['schedule'] }}</p>
                                            </div>
                                            {{--<a class="classes-item-link" href="{{ $item['url'] }}">Inscribir</a>--}}
                                        </li>
                                    @endforeach
                                </ul>
                            </section>
                        @endforeach
                    </div>

                    <a class="classes-program-cta" href="{{ $strikersFreeTrialUrl }}">👉 Reserva tu Cupo</a>
                </article>

                <article class="classes-program-block reveal" id="little-paddlers">
                    <header class="classes-program-head">
                        <h3>🎾 Little Paddlers</h3>
                        <p>Pádel para niños de 2 a 5 años</p>
                    </header>

                    <!-- Explicación de cada clase (Categories Info Grid) -->
                    <div class="classes-info-grid">
                        <div class="class-info-card">
                            <div class="class-info-header">
                                <div class="class-icon-wrapper">👶</div>
                                <div class="class-info-title-area">
                                    <h4>Baby Paddlers</h4>
                                    <span class="class-age">2 a 3 años</span>
                                </div>
                            </div>
                            <p>Primeros pasos en el pádel a través del juego, la exploración y el movimiento. Los niños desarrollan habilidades motoras básicas, equilibrio, coordinación y confianza mientras se familiarizan con la cancha, los implementos deportivos y las actividades grupales.</p>
                        </div>
                        
                        <div class="class-info-card">
                            <div class="class-info-header">
                                <div class="class-icon-wrapper">🏃</div>
                                <div class="class-info-title-area">
                                    <h4>Mini Paddlers</h4>
                                    <span class="class-age">3 a 4 años</span>
                                </div>
                            </div>
                            <p>Etapa enfocada en el desarrollo de la coordinación, el control corporal y las primeras habilidades relacionadas con el pádel. A través de juegos y ejercicios adaptados, los niños fortalecen su confianza, concentración, socialización y capacidad para seguir instrucciones.</p>
                        </div>
                        
                        <div class="class-info-card">
                            <div class="class-info-header">
                                <div class="class-icon-wrapper">⚡</div>
                                <div class="class-info-title-area">
                                    <h4>Super Paddlers</h4>
                                    <span class="class-age">4 a 5 años</span>
                                </div>
                            </div>
                            <p>Introducción a los fundamentos básicos del pádel mediante actividades dinámicas y ejercicios adaptados a su edad. Los niños desarrollan coordinación, agilidad, control de la raqueta y trabajo en equipo mientras participan en experiencias más estructuradas y divertidas dentro de la cancha.</p>
                        </div>
                    </div>

                    <div class="classes-section-subtitle">
                        <span>📅 Horarios y Sedes Disponibles</span>
                    </div>

                    <div class="classes-branch-grid classes-branch-grid--two">
                        @foreach ($paddlersSchedules as $branch)
                            <section class="classes-branch-card">
                                <h4>{{ $branch['branch'] }}</h4>
                                <ul>
                                    @foreach ($branch['items'] as $item)
                                        <li class="classes-item-row">
                                            <div class="classes-item-main">
                                                <p class="classes-item-title">{{ $item['title'] }}</p>
                                                <p class="classes-item-schedule">{{ $item['schedule'] }}</p>
                                            </div>
                                            {{--<a class="classes-item-link" href="{{ $item['url'] }}">Inscribir</a>--}}    
                                        </li>
                                    @endforeach
                                </ul>
                            </section>
                        @endforeach
                    </div>

                    <a class="classes-program-cta" href="{{ $paddlersFreeTrialUrl }}">👉 Reserva tu Cupo</a>
                </article>
            </div>
        </section>

        <section class="classes-page-cta" id="clase-gratuita">
            <div class="container">
                <div class="classes-page-cta-wrap reveal">
                    <h2>🎁 Agenda una Clase de Prueba Gratuita</h2>
                    <p>
                        Descubre por qué nuestras clases ofrecen una experiencia única para los más pequeños. A través de
                        una metodología dinámica y un currículo de ejercicios especialmente diseñado para cada etapa,
                        ayudamos a los niños a aprender, moverse y desarrollar confianza mientras se divierten.
                    </p>
                    <p>
                        Permite que tu hijo/a viva la experiencia de nuestros programas en un ambiente seguro, activo y
                        lleno de aprendizaje.
                    </p>

                    <div class="classes-page-cta-actions">
                        <a class="btn-main" href="{{ $strikersFreeTrialUrl }}">👉 Agendar Clase de Prueba de Little Strikers</a>
                        <a class="btn-soft" href="{{ $paddlersFreeTrialUrl }}">👉 Agendar Clase de Prueba de Little Paddlers</a>
                    </div>

                </div>
            </div>
        </section>
    </main>
@endsection
