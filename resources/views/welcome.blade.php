@extends('layouts.landing')

@section('title')
    <title>Little Brands Inc | Desarrollando confianza a través del deporte</title>
    <meta name="description" content="A través de Little Strikers y Little Paddlers, acompañamos a niños desde los 18 meses en sus primeros pasos dentro del deporte, ayudándolos a desarrollar coordinación, confianza, habilidades sociales y amor por el deporte en un entorno seguro, divertido y lleno de aprendizaje.">
    <meta name="keywords" content="Little Brands Inc, programas deportivos para niños, fútbol infantil, pádel infantil, desarrollo infantil a través del deporte, clases deportivas para niños pequeños, actividades deportivas para bebés, programas de deporte para niños en Caracas">
    <meta property="og:title" content="Little Brands Inc | Desarrollando confianza a través del deporte">
    <meta property="og:description" content="A través de Little Strikers y Little Paddlers, acompañamos a niños desde los 18 meses en sus primeros pasos dentro del deporte, ayudándolos a desarrollar coordinación, confianza, habilidades sociales y amor por el deporte en un entorno seguro, divertido y lleno de aprendizaje.">
    <meta property="og:image" content="{{ asset('landing_page/assets/images/img6.jpeg') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:title" content="Little Brands Inc | Desarrollando confianza a través del deporte">
    <meta name="twitter:description" content="A través de Little Strikers y Little Paddlers, acompañamos a niños desde los 18 meses en sus primeros pasos dentro del deporte, ayudándolos a desarrollar coordinación, confianza, habilidades sociales y amor por el deporte en un entorno seguro, divertido y lleno de aprendizaje.">
    <meta name="twitter:image" content="{{ asset('landing_page/assets/images/img6.jpeg') }}">
    <meta name="twitter:card" content="summary_large_image">
@endsection

@section('content')
    <main>
        <!--Secion Hero -->
        <section id="inicio" class="hero">
            <div class="container hero-grid">
                <div class="reveal hero-copy">
                    <span class="eyebrow">Desarrollando confianza a través del deporte</span>
                    <h1>En Little Brands Inc creamos programas deportivos diseñados para los más pequeños de la casa.</h1>
                    <p class="hero-lead">
                        A través de Little Strikers y Little Paddlers, acompañamos a niños desde los 18 meses en sus
                        primeros pasos dentro del deporte, ayudándolos a desarrollar coordinación, confianza, habilidades
                        sociales y amor por el deporte en un entorno seguro, divertido y lleno de aprendizaje.
                    </p>
                    <p class="hero-lead">
                        Porque creemos que el deporte puede ser una herramienta extraordinaria para ayudar a los niños a crecer felices, seguros y preparados para afrontar nuevos retos. 
                    </p>
                    <div class="hero-actions">
                        <a class="btn-main" href="{{ route('enrollment.wizard') }}">Inscribir a mi hijo</a>
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
                            <br>
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
                            <button type="button" class="birthday-btn" id="openBirthdayModalBtn" style="border:0; cursor:pointer;">Solicitar Información</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!--Seccion Testimonios -->
        <section id="testimonios" class="testimonials-section">
            <div class="testimonials-section-glow"></div>
            <div class="container">
                <div class="section-head reveal">
                    <span class="section-kicker">Testimonios</span>
                    <h2>Lo que dicen los padres</h2>
                    <p>
                        La confianza de las familias es nuestro pilar fundamental. Conoce la experiencia de otros padres que acompañan a sus hijos en nuestros programas de primera infancia.
                    </p>
                </div>

                <div class="testimonials-grid">
                    <article class="testimonial-card reveal">
                        <div class="testimonial-quote">
                            <span class="quote-icon">“</span>
                            <p>Mi hijo de 2 años lleva 3 meses y cada semana pide ir. Le encanta jugar con la pelota y ver a sus amiguitos. Es el mejor espacio para que libere energía y aprenda.</p>
                        </div>
                        <div class="testimonial-author">
                            <img src="{{ asset('landing_page/assets/images/testimonial1.png') }}" alt="Ana, mamá de Mateo">
                            <div>
                                <strong>Ana</strong>
                                <span>Mamá de Mateo (2 años)</span>
                            </div>
                        </div>
                    </article>

                    <article class="testimonial-card reveal">
                        <div class="testimonial-quote">
                            <span class="quote-icon">“</span>
                            <p>Es increíble cómo ha desarrollado su coordinación y equilibrio en tan poco tiempo. Los profesores son sumamente pacientes, cariñosos y dedicados con cada bebé.</p>
                        </div>
                        <div class="testimonial-author">
                            <img src="{{ asset('landing_page/assets/images/testimonial2.png') }}" alt="Carlos, papá de Sofía">
                            <div>
                                <strong>Carlos</strong>
                                <span>Papá de Sofía (3 años)</span>
                            </div>
                        </div>
                    </article>

                    <article class="testimonial-card reveal">
                        <div class="testimonial-quote">
                            <span class="quote-icon">“</span>
                            <p>Buscábamos una actividad que le ayudara a socializar y soltarse más. Little Paddlers ha superado nuestras expectativas, ella se siente feliz dentro de la cancha.</p>
                        </div>
                        <div class="testimonial-author">
                            <img src="{{ asset('landing_page/assets/images/testimonial3.png') }}" alt="Valeria, mamá de Emma">
                            <div>
                                <strong>Valeria</strong>
                                <span>Mamá de Emma (4 años)</span>
                            </div>
                        </div>
                    </article>
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

        <!-- Birthday Inquiry Modal -->
        <div class="birthday-modal-overlay" id="birthdayModalOverlay" style="display: none;">
            <div class="birthday-modal-container">
                <button type="button" class="birthday-modal-close" id="closeBirthdayModalBtn" aria-label="Cerrar modal">&times;</button>
                <div class="birthday-modal-header">
                    <h3>Solicita información para tu cumpleaños</h3>
                    <p>Completa el siguiente formulario y nuestro equipo se pondrá en contacto contigo para ayudarte a crear una celebración divertida, segura e inolvidable para tu hijo/a.</p>
                </div>
                <form class="birthday-modal-form" id="birthdayInquiryForm" action="{{ route('birthdays.store') }}" method="POST">
                    @csrf
                    
                    <div class="modal-form-section-title">👤 Información de contacto</div>
                    <div class="row">
                        <div class="field">
                            <label for="b_representative_name">Nombre y apellido del representante *</label>
                            <input id="b_representative_name" name="representative_name" type="text" required placeholder="Ej. Mateo González">
                        </div>
                        <div class="field">
                            <label for="b_phone">Número de teléfono *</label>
                            <input id="b_phone" name="phone" type="text" required placeholder="Ej. +58 412 1234567">
                        </div>
                    </div>
                    <div class="field">
                        <label for="b_email">Correo electrónico *</label>
                        <input id="b_email" name="email" type="email" required placeholder="Ej. representante@email.com">
                    </div>

                    <div class="modal-form-section-title">🎂 Información del cumpleaños</div>
                    <div class="row">
                        <div class="field">
                            <label for="b_age_to_celebrate">Edad que cumple *</label>
                            <input id="b_age_to_celebrate" name="age_to_celebrate" type="number" min="1" max="18" required placeholder="Ej. 4">
                        </div>
                        <div class="field">
                            <label for="b_event_date">Fecha del evento *</label>
                            <input id="b_event_date" name="event_date" type="date" required min="{{ date('Y-m-d') }}">
                        </div>
                        <div class="field">
                            <label for="b_start_time">Hora estimada de inicio *</label>
                            <input id="b_start_time" name="start_time" type="text" required placeholder="Ej. 3:00 PM">
                        </div>
                    </div>

                    <div class="modal-form-section-title">📍 Ubicación</div>
                    <div class="row">
                        <div class="field">
                            <label for="b_location_type">Tipo de Ubicación *</label>
                            <select id="b_location_type" name="location_type" required>
                                <option value="">Selecciona una opción</option>
                                <option value="sede_san_luis">Sede San Luis</option>
                                <option value="sede_los_campitos">Sede Los Campitos</option>
                                <option value="sede_los_chorros">Sede Los Chorros</option>
                                <option value="other">Otra ubicación</option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="b_event_location">Lugar del evento (Dirección/Ubicación)</label>
                            <input id="b_event_location" name="event_location" type="text" placeholder="Ej. Salón de fiestas o dirección">
                        </div>
                    </div>

                    <div class="modal-form-section-title">👶 Información de los invitados</div>
                    <div class="row">
                        <div class="field">
                            <label for="b_estimated_children">Número estimado de niños *</label>
                            <input id="b_estimated_children" name="estimated_children" type="number" min="1" required placeholder="Ej. 15">
                        </div>
                        <div class="field">
                            <label for="b_guest_age_range">Rango de edades de los invitados *</label>
                            <input id="b_guest_age_range" name="guest_age_range" type="text" required placeholder="Ej. 2 a 5 años">
                        </div>
                    </div>

                    <div class="modal-form-section-title">⚽🎾 Tipo de celebración</div>
                    <div class="field">
                        <label>Programa de interés *</label>
                        <div class="program-radio-group">
                            <label class="program-radio-label">
                                <input type="radio" name="program_interest" value="strikers" required checked>
                                <span>⚽ Little Strikers (Fútbol)</span>
                            </label>
                            <label class="program-radio-label">
                                <input type="radio" name="program_interest" value="paddlers" required>
                                <span>🎾 Little Paddlers (Pádel)</span>
                            </label>
                        </div>
                    </div>

                    <div class="modal-form-section-title">🎈 Servicios adicionales de interés</div>
                    <div class="checkbox-grid">
                        <label class="checkbox-label">
                            <input type="checkbox" name="additional_services[]" value="Decoración temática">
                            <span>Decoración temática</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="additional_services[]" value="Fotografía y video">
                            <span>Fotografía y video</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="additional_services[]" value="Piñata">
                            <span>Piñata</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="additional_services[]" value="Refrigerios">
                            <span>Refrigerios</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="additional_services[]" value="Torta">
                            <span>Torta</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="additional_services[]" value="Animación adicional">
                            <span>Animación adicional</span>
                        </label>
                    </div>

                    <div class="field" style="margin-top: 1rem;">
                        <label for="b_comments">💬 Comentarios adicionales</label>
                        <textarea id="b_comments" name="comments" placeholder="Cuéntanos más detalles del evento..."></textarea>
                    </div>

                    <button class="submit" type="submit" style="margin-top: 1.5rem;" id="birthdaySubmitBtn">Enviar solicitud de cumpleaños</button>
                </form>
            </div>
        </div>

        <style>
            /* Birthday Modal Styles */
            .birthday-modal-overlay {
                position: fixed;
                inset: 0;
                background: rgba(9, 23, 34, 0.45);
                backdrop-filter: blur(8px);
                z-index: 9999;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1.5rem;
                animation: fadeIn 0.3s ease;
            }

            .birthday-modal-container {
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(245, 250, 254, 0.96));
                border: 1px solid rgba(255, 255, 255, 0.9);
                box-shadow: 0 24px 70px rgba(9, 23, 34, 0.25);
                border-radius: 28px;
                width: min(720px, 100%);
                max-height: 88vh;
                overflow-y: auto;
                padding: 2.25rem;
                position: relative;
                animation: slideUp 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
            }

            .birthday-modal-container::-webkit-scrollbar {
                width: 6px;
            }
            .birthday-modal-container::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 10px;
            }

            .birthday-modal-close {
                position: absolute;
                top: 1.25rem;
                right: 1.25rem;
                border: 0;
                background: rgba(9, 23, 34, 0.05);
                width: 36px;
                height: 36px;
                border-radius: 50%;
                font-size: 1.5rem;
                cursor: pointer;
                display: grid;
                place-items: center;
                color: #486173;
                transition: background-color 0.2s, color 0.2s;
                line-height: 1;
            }

            .birthday-modal-close:hover {
                background: rgba(220, 38, 38, 0.1);
                color: #dc2626;
            }

            .birthday-modal-header {
                margin-bottom: 1.75rem;
                text-align: center;
                padding-right: 1.5rem;
            }

            .birthday-modal-header h3 {
                font-family: 'Baloo 2', cursive;
                font-size: 1.85rem;
                color: #091722;
                margin-bottom: 0.5rem;
            }

            .birthday-modal-header p {
                color: #486173;
                font-size: 0.92rem;
                line-height: 1.55;
                margin: 0;
            }

            .modal-form-section-title {
                font-size: 0.8rem;
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: 0.8px;
                color: #1e3a5f;
                margin: 1.5rem 0 0.85rem;
                border-bottom: 1px dashed rgba(9, 23, 34, 0.1);
                padding-bottom: 0.3rem;
            }

            .modal-form-section-title:first-of-type {
                margin-top: 0;
            }

            .birthday-modal-form .row {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 0.9rem;
            }

            .birthday-modal-form .field {
                margin-bottom: 0.85rem;
            }

            .birthday-modal-form label {
                font-weight: 700;
                color: #1c354e;
                font-size: 0.82rem;
            }

            .birthday-modal-form input,
            .birthday-modal-form select,
            .birthday-modal-form textarea {
                border-radius: 12px;
                border: 1px solid rgba(72, 97, 115, 0.18);
                background: rgba(246, 248, 251, 0.92);
                padding: 0.65rem 0.85rem;
                font-size: 0.88rem;
                width: 100%;
                font-family: inherit;
            }

            .birthday-modal-form select {
                appearance: none;
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23486173' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
                background-repeat: no-repeat;
                background-position: calc(100% - 0.75rem) center;
                padding-right: 2rem;
            }

            .program-radio-group {
                display: flex;
                gap: 1rem;
                margin-top: 0.35rem;
            }

            .program-radio-label {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                cursor: pointer;
                background: rgba(255, 255, 255, 0.7);
                border: 1px solid rgba(72, 97, 115, 0.12);
                padding: 0.65rem 1.15rem;
                border-radius: 14px;
                font-weight: 700 !important;
                transition: all 0.25s ease;
                flex: 1;
            }

            .program-radio-label:hover {
                border-color: #ff8d3f;
                background: #fffbeb;
            }

            .program-radio-label input[type="radio"]:checked + span {
                color: #091722;
            }

            .program-radio-label input[type="radio"] {
                accent-color: #ff8d3f;
                width: 18px;
                height: 18px;
            }

            .checkbox-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
                gap: 0.75rem;
            }

            .checkbox-label {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                cursor: pointer;
                background: rgba(255, 255, 255, 0.7);
                border: 1px solid rgba(72, 97, 115, 0.1);
                padding: 0.55rem 0.85rem;
                border-radius: 12px;
                font-size: 0.82rem;
                font-weight: 600 !important;
                transition: border-color 0.2s, background-color 0.2s;
            }

            .checkbox-label:hover {
                border-color: #ff8d3f;
                background: #fffbeb;
            }

            .checkbox-label input[type="checkbox"] {
                accent-color: #ff8d3f;
                width: 16px;
                height: 16px;
            }

            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }

            @keyframes slideUp {
                from { transform: translateY(30px); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }
        </style>

        <!-- SweetAlert2 for success popups -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const openBtn = document.getElementById('openBirthdayModalBtn');
                const closeBtn = document.getElementById('closeBirthdayModalBtn');
                const overlay = document.getElementById('birthdayModalOverlay');
                const form = document.getElementById('birthdayInquiryForm');

                if (openBtn && closeBtn && overlay) {
                    // Open Modal
                    openBtn.addEventListener('click', function() {
                        overlay.style.display = 'flex';
                        document.body.style.overflow = 'hidden'; // Prevent scrolling
                    });

                    // Close Modal
                    closeBtn.addEventListener('click', function() {
                        overlay.style.display = 'none';
                        document.body.style.overflow = '';
                    });

                    // Close when clicking outside the container
                    overlay.addEventListener('click', function(e) {
                        if (e.target === overlay) {
                            overlay.style.display = 'none';
                            document.body.style.overflow = '';
                        }
                    });
                }

                if (form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();

                        const submitBtn = document.getElementById('birthdaySubmitBtn');
                        const originalText = submitBtn.textContent;
                        submitBtn.textContent = 'Enviando...';
                        submitBtn.disabled = true;

                        const formData = new FormData(form);

                        fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            submitBtn.textContent = originalText;
                            submitBtn.disabled = false;

                            if (data.success) {
                                // Close modal
                                overlay.style.display = 'none';
                                document.body.style.overflow = '';
                                
                                // Reset form
                                form.reset();

                                // Show SweetAlert2 Success Alert
                                Swal.fire({
                                    title: '¡Solicitud Recibida!',
                                    text: data.message,
                                    icon: 'success',
                                    confirmButtonText: 'Genial',
                                    confirmButtonColor: '#ff8d3f'
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error',
                                    text: 'Ocurrió un error al enviar tu solicitud. Por favor intenta nuevamente.',
                                    icon: 'error',
                                    confirmButtonText: 'Entendido',
                                    confirmButtonColor: '#dc3545'
                                });
                            }
                        })
                        .catch(error => {
                            submitBtn.textContent = originalText;
                            submitBtn.disabled = false;
                            console.error('Error:', error);
                            
                            Swal.fire({
                                    title: 'Error de Red',
                                    text: 'No se pudo conectar con el servidor. Verifica tu conexión e intenta nuevamente.',
                                    icon: 'error',
                                    confirmButtonText: 'Entendido',
                                    confirmButtonColor: '#dc3545'
                            });
                        });
                    });
                }
            });
        </script>
    </main>
@endsection
