@extends('layouts.landing')

@section('title')
    <title>Little Brands Inc | Formación deportiva infantil</title>
@endsection

@section('content')
    <main>
        <section id="inicio" class="hero">
            <div class="container hero-grid">
                <div class="reveal hero-copy">
                    <span class="eyebrow">Desarrollando confianza a través del deporte</span>
                    <h1>En Little Brands Inc creamos experiencias deportivas diseñadas especialmente para la primera
                        infancia.</h1>
                    <p class="hero-lead">
                        A través de nuestras marcas Little Strikers y Little Paddlers, ayudamos a niños pequeños a
                        desarrollar coordinación, confianza y amor por el movimiento en un ambiente seguro, divertido y
                        acompañado por sus familias.
                        Más que clases deportivas, construimos experiencias que impactan positivamente el desarrollo físico,
                        social y emocional de cada niño.
                    </p>
                    <div class="hero-actions">
                        <a class="btn-main" href="#contacto">Quiero información</a>
                        <a class="btn-soft" href="#marcas">Conocer nuestras marcas</a>
                    </div>

                    <div class="hero-metrics">
                        <article class="metric-card">
                            <strong>⚽🎾</strong>
                            <span>Marcas especializadas en fútbol y pádel infantil</span>
                        </article>
                        <article class="metric-card">
                            <strong>❤️ </strong>
                            <span>Acompañamiento integral para niños y familias</span>
                        </article>
                        <article class="metric-card">
                            <strong>🌟</strong>
                            <span>Metodología diseñada para aprender jugando</span>
                        </article>
                    </div>
                </div>

                <div class="hero-stage reveal">
                    <div class="slider-card" aria-label="Slider de beneficios">
                        <div class="slides" id="slides">
                            <article class="slide active">
                                <div class="slide-bg"></div>
                                <div class="slide-content">
                                    <strong>Formación diseñada para cada etapa</strong>
                                    <p>Programas adaptados por edad, con objetivos claros y una metodología que permite a
                                        cada niño crecer, disfrutar y desarrollar confianza a su propio ritmo.</p>
                                </div>
                            </article>
                            <article class="slide">
                                <div class="slide-bg"></div>
                                <div class="slide-content">
                                    <strong>Equipo especializado en primera infancia</strong>
                                    <p>Entrenadores con enfoque pedagógico, acompañamiento cercano y una conexión constante
                                        con las familias para apoyar el desarrollo de cada niño dentro y fuera de la cancha.
                                    </p>
                                </div>
                            </article>
                            <article class="slide">
                                <div class="slide-bg"></div>
                                <div class="slide-content">
                                    <strong>Confianza, movimiento y diversión</strong>
                                    <p>Impulsamos el desarrollo deportivo, social y emocional de cada niño para ayudarlos a
                                        crecer seguros, activos y felices.</p>
                                </div>
                            </article>
                        </div>
                        <div class="slider-controls" role="tablist" aria-label="Controles de slider">
                            <button type="button" class="dot active" data-slide="0" aria-label="Slide 1"></button>
                            <button type="button" class="dot" data-slide="1" aria-label="Slide 2"></button>
                            <button type="button" class="dot" data-slide="2" aria-label="Slide 3"></button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!--
            <section id="acerca" class="section-muted">
                <div class="container">
                    <div class="section-head reveal">
                        <span class="section-kicker">Acerca de</span>
                        <h2>Acerca de Little Brands Inc</h2>
                        <p>
                            Somos un holding dedicado a la formación deportiva infantil. Diseñamos ecosistemas de
                            aprendizaje
                            donde cada marca aporta metodología especializada para que los niños construyan hábitos
                            saludables,
                            autoestima y habilidades sociales desde el deporte.
                        </p>
                    </div>

                    <div class="about-grid">
                        <article class="panel reveal">
                            <h3>Un grupo, dos marcas, un mismo propósito</h3>
                            <p>
                                Little Brands Inc integra programas de fútbol y pádel infantil bajo estándares compartidos
                                de calidad,
                                seguridad y acompañamiento familiar. Nuestro objetivo es que cada familia encuentre un
                                espacio confiable
                                para el crecimiento integral de sus hijos.
                            </p>
                            <p>
                                Trabajamos con evaluaciones periódicas, metas por nivel y experiencias de juego que
                                convierten el
                                entrenamiento en un momento esperado por los pequeños.
                            </p>
                            <div class="about-points">
                                <div class="about-point">
                                    <strong>Método claro</strong>
                                    <span>Procesos simples, expectativas transparentes y seguimiento visible para cada
                                        familia.</span>
                                </div>
                                <div class="about-point">
                                    <strong>Experiencia cuidada</strong>
                                    <span>Una marca consistente desde el primer contacto hasta la evolución del
                                        alumno.</span>
                                </div>
                            </div>
                        </article>

                        <aside class="panel reveal" aria-label="Logos de marcas del holding">
                            <h3>Nuestras marcas</h3>
                            <div class="logos">
                                <div class="logo-card">
                                    <img src="{{ $holdingLogo }}" alt="Logo Little Brands Inc">
                                </div>
                                @foreach ($brands as $brand)
    <div class="logo-card">
                                        <img src="{{ $brand['logo'] }}" alt="Logo {{ $brand['name'] }}">
                                    </div>
    @endforeach
                            </div>
                        </aside>
                    </div>
                </div>
            </section>

            <section id="valores">
                <div class="container mv-grid">
                    <article class="mv-card mision reveal">
                        <h3>Misión</h3>
                        <p>
                            Formar niños y niñas a través del deporte con programas seguros, dinámicos y motivadores que
                            fomenten
                            disciplina, autonomía, trabajo en equipo y disfrute del movimiento como parte de su desarrollo
                            integral.
                        </p>
                    </article>

                    <article class="mv-card vision reveal">
                        <h3>Visión</h3>
                        <p>
                            Consolidarnos como el holding líder en formación deportiva infantil en la región, reconocidos
                            por
                            nuestro impacto positivo en miles de familias y por la excelencia metodológica de nuestras
                            marcas.
                        </p>
                    </article>
                </div>
            </section>
        -->

        <section id="marcas" class="section-muted">
            <div class="container">
                <div class="section-head reveal">
                    <span class="section-kicker">Programas</span>
                    <h2>Nuestros programas deportivos</h2>
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
                    <p>
                        Conoce más sobre Little Strikers y Little Paddlers descargando nuestros brochures.
                    </p>
                </div>

                <div class="brands-grid">
                    @foreach ($brands as $brand)
                        <article class="brand-card reveal" style="--card-color: {{ $brand['accent'] }};">
                            <div class="brand-top">
                                <img src="{{ $brand['logo'] }}" alt="Logo {{ $brand['name'] }}">
                                <div>
                                    <h3>{{ $brand['name'] }}</h3>
                                    <small>{{ $brand['sport'] }}</small>
                                </div>
                            </div>
                            <p>{{ $brand['description'] }}</p>
                            <a href="{{ $brand['brochure'] }}" target="_blank" rel="noopener noreferrer">Ver
                                brochure</a>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="contacto">
            <div class="container">
                <div class="section-head reveal">
                    <span class="section-kicker">Contacto</span>
                    <h2>Contacto</h2>
                    <p>
                        Cuéntanos la edad de tu hijo y tus objetivos. Nuestro equipo te ayudará a elegir la mejor opción
                        entre Little Strikers y Little Paddlers.
                    </p>
                </div>

                <div class="contact-wrap">
                    <article class="contact-panel reveal">
                        <h3 style="margin-bottom: 2rem;">Hablemos de su próximo paso deportivo</h3>
                        <p style="margin-bottom: 2rem;">
                            Te responderemos por correo para brindarte horarios, sedes disponibles y recomendaciones
                            según la etapa de aprendizaje de tu hijo.
                        </p>
                        {{-- <p>
                            Destinatario configurable desde entorno: MAIL_TO_ADDRESS
                        </p> --}}
                        <div class="contact-points">
                            <div class="contact-point">
                                <strong>Respuesta clara</strong>
                                <span>Recibirás orientación sobre programa, edad recomendada y siguiente paso.</span>
                            </div>
                            <div class="contact-point">
                                <strong>Proceso simple</strong>
                                <span>Completa el formulario y el equipo continuará la conversación contigo por
                                    correo.</span>
                            </div>
                        </div>
                    </article>

                    <form class="contact-form reveal" action="{{ route('landing.contact') }}" method="POST" novalidate>
                        @csrf

                        <div class="form-intro">
                            <h3>Escríbenos</h3>
                            <p>Comparte tus datos y el contexto de tu hijo para recomendarte la opción más adecuada.</p>
                        </div>

                        @if (session('success'))
                            <div class="alert ok">{{ session('success') }}</div>
                        @endif

                        @if ($errors->has('contact'))
                            <div class="alert bad">{{ $errors->first('contact') }}</div>
                        @endif

                        <div class="row">
                            <div class="field">
                                <label for="name">Nombre</label>
                                <input id="name" name="name" type="text" value="{{ old('name') }}"
                                    required minlength="3" maxlength="120">
                                @error('name')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="email">Email</label>
                                <input id="email" name="email" type="email" value="{{ old('email') }}"
                                    required maxlength="160">
                                @error('email')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="field">
                            <label for="phone">Teléfono</label>
                            <input id="phone" name="phone" type="text" value="{{ old('phone') }}" required
                                minlength="7" maxlength="25">
                            @error('phone')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="message">Mensaje</label>
                            <textarea id="message" name="message" required minlength="12" maxlength="1200">{{ old('message') }}</textarea>
                            @error('message')
                                <span class="error">{{ $message }}</span>
                            @enderror
                        </div>

                        <button class="submit" type="submit">Enviar mensaje</button>
                    </form>
                </div>
            </div>
        </section>
    </main>
@endsection
