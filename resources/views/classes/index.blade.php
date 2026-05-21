@extends('layouts.landing')

@section('title')
    <title>Clases y Sedes | Little Brands Inc</title>
@endsection

@section('content')
    <main>
        <section class="classes-page-hero" id="inicio-clases">
            <div class="container classes-page-hero-grid">
                <div class="classes-page-copy reveal">
                    <span class="section-kicker">Clases y Sedes</span>
                    <h1>Clases y Sedes</h1>
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

                    <div class="classes-page-chip-wrap" style="margin-bottom: 1.5rem;">
                        <span class="classes-page-chip">📍 Sedes en Caracas: San Luis • Los Campitos • Los Chorros</span>
                        <span class="classes-page-chip">🎁 Clases de prueba gratuita disponible</span>
                    </div>

                    <a class="btn-main" href="{{ $freeTrialUrl }}">Agendar clase gratuita</a>
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
                        Aquí encontrarás la disponibilidad por programa y por sede, en un formato claro para facilitar
                        tu inscripción.
                    </p>
                </div>

                <article class="classes-program-block reveal" id="little-strikers">
                    <header class="classes-program-head">
                        <h3>⚽ Little Strikers</h3>
                        <p>Fútbol para niños de 18 meses a 4 años</p>
                    </header>

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
                                            <a class="classes-item-link" href="{{ $item['url'] }}">Inscribir</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </section>
                        @endforeach
                    </div>

                    <a class="classes-program-cta" href="{{ $freeTrialUrl }}">👉 Agendar clase de Little Strikers</a>
                </article>

                <article class="classes-program-block reveal" id="little-paddlers">
                    <header class="classes-program-head">
                        <h3>🎾 Little Paddlers</h3>
                        <p>Pádel para niños de 2 a 5 años</p>
                    </header>

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
                                            <a class="classes-item-link" href="{{ $item['url'] }}">Inscribir</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </section>
                        @endforeach
                    </div>

                    <a class="classes-program-cta" href="{{ $freeTrialUrl }}">👉 Agendar clase de Little Paddlers</a>
                </article>
            </div>
        </section>

        <section class="classes-page-cta" id="clase-gratuita">
            <div class="container">
                <div class="classes-page-cta-wrap reveal">
                    <h2>🎁 Agenda una CLASE DE PRUEBA GRATUITA</h2>
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
                        <a class="btn-main" href="{{ $freeTrialUrl }}">👉 Agendar Clase de Little Strikers (Fútbol)</a>
                        <a class="btn-soft" href="{{ $freeTrialUrl }}">👉 Agendar Clase de Little Paddlers (Pádel)</a>
                    </div>

                </div>
            </div>
        </section>
    </main>
@endsection
