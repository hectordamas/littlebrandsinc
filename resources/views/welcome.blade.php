@extends('layouts.landing')

@section('title')
    <title>Little Brands Inc | Formación deportiva infantil</title>
@endsection

@section('content')
    <main>
        <!--Secion Hero -->
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
                        <a class="btn-main" href="#contacto">Quiero Información</a>
                        <a class="btn-soft" href="#programas">Conoce Nuestros Programas</a>
                    </div>

                    <div class="hero-metrics">
                        <article class="metric-card">
                            <strong>⚽🎾</strong>
                            <span>Programas especializadas en fútbol y pádel infantil</span>
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



        <!--Seccion Programas -->
        <section id="programas" class="section-muted">
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


        <!--Seccion Clases y sedes-->
        <section id="clases-sedes" class="classes-venues">
            <div class="container">
                <div class="section-head reveal classes-venues-head">
                    <span class="section-kicker">Clases y sedes</span>
                    <h2>Clases y Sedes</h2>
                    <p>
                        En Little Brands ofrecemos experiencias deportivas diseñadas especialmente para la primera
                        infancia, en espacios seguros, dinámicos y adaptados para el desarrollo de cada niño.
                    </p>
                    <p>
                        Actualmente, nuestras clases están disponibles en Caracas, en tres ubicaciones estratégicas de la
                        ciudad:
                    </p>
                </div>

                <aside class="classes-venues-locations reveal" aria-label="Sedes disponibles en Caracas">
                    <ul class="classes-venues-list">
                        <li><span>📍</span> San Luis</li>
                        <li><span>📍</span> Los Campitos</li>
                        <li><span>📍</span> Los Chorros</li>
                    </ul>
                    <div class="classes-venues-copy">
                        <p>
                            Cada sede ha sido seleccionada para ofrecer un ambiente cómodo, accesible y óptimo para que los
                            niños aprendan, jueguen y se desarrollen con libertad.
                        </p>
                        <p>
                            A través de nuestros programas Little Strikers y Little Paddlers, los más pequeños disfrutan de
                            actividades guiadas por coaches especializados, en grupos reducidos y con atención
                            personalizada,
                            asegurando una experiencia positiva desde el primer día.
                        </p>
                    </div>
                </aside>

                <div class="classes-venues-panel reveal">
                    <h3>⚽🎾 Explora nuestras clases:</h3>

                    <div class="classes-venues-grid">
                        <article class="classes-venues-card" style="--venue-color: #f97316;">
                            <div class="classes-venues-card-media"
                                style="background-image: url('{{ asset('landing_page/assets/images/img6.jpeg') }}');"></div>
                            <div class="classes-venues-card-body">
                                <h4>⚽ Little Strikers</h4>
                                <p>Fútbol para niños de 18 meses a 4 años</p>
                                <a class="classes-venues-btn" href="{{ route('classes.index') }}#little-strikers">👉 Ver clases</a>
                            </div>
                        </article>

                        <article class="classes-venues-card" style="--venue-color: #0ea5e9;">
                            <div class="classes-venues-card-media"
                                style="background-image: url('{{ asset('landing_page/assets/images/img4.jpeg') }}');"></div>
                            <div class="classes-venues-card-body">
                                <h4>🎾 Little Paddlers</h4>
                                <p>Pádel para niños de 2 a 5 años</p>
                                <a class="classes-venues-btn" href="{{ route('classes.index') }}#little-paddlers">👉 Ver clases</a>
                            </div>
                        </article>
                    </div>

                    <div class="classes-venues-bottom">
                        <p>
                            Agenda una clase de prueba y permite que tu hijo/a descubra la diversión, el aprendizaje y la
                            emoción de nuestros programas deportivos en un ambiente seguro y diseñado especialmente para su
                            edad.
                        </p>
                        <a class="classes-venues-cta-btn" href="{{ route('classes.index') }}">👉 Ver clases</a>
                    </div>
                </div>
            </div>
        </section>

        <!--Seccion Cumpleaños -->
        <section id="cumpleanos" class="birthday-section">
            <div class="container">
                <div class="birthday-wrap reveal">
                    <div class="birthday-media"
                        style="background-image: url('{{ asset('landing_page/assets/images/img1.jpeg') }}');"></div>

                    <div class="birthday-content">
                        <div class="birthday-head">
                            <span class="section-kicker">Cumpleaños</span>
                            <h2 style="margin-bottom: 10px;">Cumpleaños</h2>
                            <p class="birthday-hero">⚽🎉 ¡Celebra un cumpleaños inolvidable con Nosotros! 🎂🥳</p>
                            <p>
                                ¿Buscas una forma divertida y única de festejar el cumpleaños de tu bebe? En Little Brands,
                                hacemos de su día especial una experiencia llena de alegría, deporte y momentos inolvidables.
                            </p>
                        </div>

                        <div class="birthday-grid">
                            <article class="birthday-point">
                                <span>🎈</span>
                                <p>Paquetes personalizados para adaptarse a tu celebración.</p>
                            </article>
                            <article class="birthday-point">
                                <span>⚽</span>
                                <p>Juegos y dinámicas diseñadas para la edad de los niños.</p>
                            </article>
                            <article class="birthday-point">
                                <span>😃</span>
                                <p>Entrenadores especializados que garantizan diversión y seguridad.</p>
                            </article>
                        </div>

                        <div class="birthday-actions">
                            <a class="birthday-btn" href="#contacto">Mas información</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!--Seccion Contacto -->
        <section id="contacto">
            <div class="container">
                <div class="section-head reveal">
                    <span class="section-kicker">Contacto</span>
                    <h2>Contacto</h2>
                    <p>
                        Cuéntanos un poco sobre tu hijo/a y nuestro equipo te ayudará a elegir el programa ideal entre
                        Little Strikers y Little Paddlers.
                    
                        Queremos acompañarte desde el primer momento para que vivan una experiencia deportiva divertida,
                        segura y adaptada a cada etapa de crecimiento.
                    </p>
                </div>

                <div class="contact-layout">
                    <aside class="contact-info reveal">
                        <h3>Te acompañamos en el siguiente paso</h3>
                        <p>
                            Nuestro equipo revisará tu información para recomendarte el programa y la sede más adecuada
                            según la etapa de tu hijo/a.
                        </p>
                        <div class="contact-info-points">
                            <div class="contact-info-point">
                                <strong>Recomendación personalizada</strong>
                                <span>Orientación práctica según edad, objetivos y disponibilidad.</span>
                            </div>
                            <div class="contact-info-point">
                                <strong>Proceso rápido</strong>
                                <span>Te respondemos con los siguientes pasos para comenzar.</span>
                            </div>
                            <div class="contact-info-point">
                                <strong>Acompañamiento cercano</strong>
                                <span>Seguimiento claro para que tomes una decisión con confianza.</span>
                            </div>
                        </div>
                    </aside>

                    <form class="contact-form reveal" action="{{ route('landing.contact') }}" method="POST" novalidate>
                        @csrf

                        <div class="form-intro">
                            <h3>Escríbenos</h3>
                            <p>Para ayudarte mejor, completa el siguiente formulario:</p>
                        </div>

                        @if (session('success'))
                            <div class="alert ok">{{ session('success') }}</div>
                        @endif

                        @if ($errors->has('contact'))
                            <div class="alert bad">{{ $errors->first('contact') }}</div>
                        @endif

                        <div class="row">
                            <div class="field">
                                <label for="representative_name">Nombre del representante</label>
                                <input id="representative_name" name="representative_name" type="text"
                                    value="{{ old('representative_name') }}" required minlength="3" maxlength="120">
                                @error('representative_name')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="child_name">Nombre del niño/a</label>
                                <input id="child_name" name="child_name" type="text" value="{{ old('child_name') }}"
                                    required maxlength="160">
                                @error('child_name')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="field">
                                <label for="child_age">Edad del niño/a</label>
                                <input id="child_age" name="child_age" type="number" value="{{ old('child_age') }}"
                                    required min="1" max="18">
                                @error('child_age')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="field">
                                <label for="program_id">Programa de interés</label>
                                <select id="program_id" name="program_id" required>
                                    <option value="">Selecciona una opción</option>
                                    @foreach ($programs ?? [] as $program)
                                        <option value="{{ $program->id }}" @selected((int) old('program_id') === (int) $program->id)>
                                            {{ $program->name }}</option>
                                    @endforeach
                                </select>
                                @error('program_id')
                                    <span class="error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="field">
                                <label for="branch_id">Sede de preferencia</label>
                                <select id="branch_id" name="branch_id" required>
                                    <option value="">Selecciona una sede</option>
                                    @foreach ($branches ?? [] as $branch)
                                        <option value="{{ $branch->id }}" @selected((int) old('branch_id') === (int) $branch->id)>
                                            {{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                @error('branch_id')
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
                            <label for="comment">Objetivo o comentario adicional</label>
                            <textarea id="comment" name="comment" required minlength="12" maxlength="1200">{{ old('comment') }}</textarea>
                            @error('comment')
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
