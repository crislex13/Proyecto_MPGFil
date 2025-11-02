<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio Max Power Gym</title>
    <meta name="description" content="">
    <meta name="keywords" content="">

    <!-- Favicons -->
    <link href="bienvenida/assets/img/logonaranjablanco.png" rel="icon">
    <link href="bienvenida/assets/img/logonaranjablanco.png" rel="apple-touch-icon">

    <!-- Fonts -->
    <!-- <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet"> -->

    <!-- Vendor CSS Files -->
    <link href="bienvenida/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="bienvenida/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="bienvenida/assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="bienvenida/assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
    <link href="bienvenida/assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">

    <!-- Main CSS File -->
    <link href="bienvenida/assets/css/main.css" rel="stylesheet">

    <!-- =======================================================
  * Template Name: Multi
  * Template URL: https://bootstrapmade.com/multi-responsive-bootstrap-template/
  * Updated: Aug 07 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
    ======================================================== -->
</head>

<body>
    <header id="header" class="header d-flex align-items-center sticky-top">
        <div class="container-fluid container-xl position-relative d-flex align-items-center">

            <a href="index.html" class="logo d-flex align-items-center me-auto">
                <!-- Descomenta la línea de abajo si también deseas usar un logo en imagen -->
                <!-- <img src="assets/img/logo.png" alt=""> -->
                <img src="bienvenida/assets/img/logoletrasplnar.png" alt="MaxPowerGym"
                    style="width: 100%; height: auto;">
            </a>

            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="#hero" class="active">Inicio</a></li>
                    <li><a href="#about">Acerca de</a></li>
                    <li><a href="#services">Servicios</a></li>
                    <li><a href="#contact">Contacto</a></li>
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>
            <a class="btn-getstarted" href="<?php echo e(route('login')); ?>">Iniciar Sesión</a>

        </div>
    </header>


    <main class="main">
        <!-- Sección Hero -->
        <section id="hero" class="hero section dark-background">

            <div id="hero-carousel" class="carousel slide carousel-fade" data-bs-ride="carousel"
                data-bs-interval="5000">

                <div class="carousel-item active">
                    <img src="bienvenida/assets/img/hero-carousel/Max-Power-Gym 1.jpg" alt="">
                    <div class="carousel-container">
                        <h2>Bienvenido a Max Power Gym<br></h2>
                        <p>Entrena donde se entrena en serio, Salas amplias, piso deportivo, iluminación LED y un
                            ambiente que te empuja a dar tu mejor versión.</p>
                        <a href="<?php echo e(route('login')); ?>" class="btn-get-started">Comenzar</a>
                    </div>
                </div><!-- Fin del ítem del carrusel -->

                <div class="carousel-item">
                    <img src="bienvenida/assets/img/hero-carousel/Max Power Gym 3.jpg" alt="">
                    <div class="carousel-container">
                        <h2>Fuerza, cardio y clases dirigidas</h2>
                        <p>Programas para todos los niveles. Entrena con método, mide tu progreso y rompe tus marcas.
                        </p>
                        <a href="<?php echo e(route('login')); ?>" class="btn-get-started">Comenzar</a>
                    </div>
                </div><!-- Fin del ítem del carrusel -->

                <div class="carousel-item">
                    <img src="bienvenida/assets/img/hero-carousel/Max Power Gym 6.jpg" alt="">
                    <div class="carousel-container">
                        <h2>Rompe tus límites</h2>
                        <p>Equipo moderno, coaches atentos y disciplina diaria. Aquí las excusas sobran.</p>
                        <a href="<?php echo e(route('login')); ?>" class="btn-get-started">Comenzar</a>
                    </div>
                </div><!-- Fin del ítem del carrusel -->

                <a class="carousel-control-prev" href="#hero-carousel" role="button" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon bi bi-chevron-left" aria-hidden="true"></span>
                </a>

                <a class="carousel-control-next" href="#hero-carousel" role="button" data-bs-slide="next">
                    <span class="carousel-control-next-icon bi bi-chevron-right" aria-hidden="true"></span>
                </a>

                <ol class="carousel-indicators"></ol>

            </div>

        </section><!-- /Sección Hero -->

        <!-- Sección Acerca de -->
        <section id="about" class="about section">

            <!-- Título de la Sección -->
            <div class="container section-title" data-aos="fade-up">
                <h2>Acerca de</h2>
                <div><span>Aprende Más</span> <span class="description-title">Sobre Nosotros</span></div>
            </div><!-- Fin del Título de la Sección -->

            <div class="container">

                <div class="row gy-4">

                    <div class="col-lg-6 content" data-aos="fade-up" data-aos-delay="100">
                        <p>
                            En <strong>Max Power Gym</strong> creemos que la fuerza no solo se construye con pesas, sino
                            con disciplina, constancia y mentalidad.
                            Somos un espacio creado para quienes no buscan excusas, sino resultados reales.
                            Aquí cada entrenamiento cuenta, cada día suma y cada esfuerzo deja huella.
                        </p>
                        <ul>
                            <li><i class="bi bi-check2-circle"></i> <span>Ambientes amplios y equipados con tecnología
                                    de alto rendimiento.</span></li>
                            <li><i class="bi bi-check2-circle"></i> <span>Entrenadores profesionales que te acompañan en
                                    cada objetivo.</span></li>
                            <li><i class="bi bi-check2-circle"></i> <span>Rutinas personalizadas, control de acceso
                                    biométrico y seguimiento de progreso.</span></li>
                        </ul>
                    </div>

                    <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
                        <p>
                            Más que un gimnasio, somos una comunidad enfocada en el crecimiento físico y mental.
                            Nuestro compromiso es brindarte un entorno seguro, moderno y motivador para que entrenes con
                            energía y determinación.
                        </p>
                        <a href="#services" class="read-more"><span>Descubre nuestros servicios</span><i
                                class="bi bi-arrow-right"></i></a>
                    </div>

                </div>

            </div>

        </section><!-- /Sección Acerca de -->


        <!-- Sección de Estadísticas -->
        <!-- Sección de Estadísticas -->
        <section id="stats" class="stats section">
            <div class="container" data-aos="fade-up" data-aos-delay="100">
                <div class="row gy-4">

                    <!-- Socios activos -->
                    <div class="col-lg-3 col-md-6 d-flex flex-column align-items-center">
                        <i class="bi bi-people"></i>
                        <div class="stats-item">
                            <span data-purecounter-start="0" data-purecounter-end="650" data-purecounter-duration="1"
                                class="purecounter"></span>
                            <p>Socios activos</p>
                        </div>
                    </div>

                    <!-- Clases dirigidas por semana -->
                    <div class="col-lg-3 col-md-6 d-flex flex-column align-items-center">
                        <i class="bi bi-calendar-check"></i>
                        <div class="stats-item">
                            <span data-purecounter-start="0" data-purecounter-end="40" data-purecounter-duration="1"
                                class="purecounter"></span>
                            <p>Clases dirigidas / semana</p>
                        </div>
                    </div>

                    <!-- Equipamiento (máquinas y accesorios) -->
                    <div class="col-lg-3 col-md-6 d-flex flex-column align-items-center">
                        <i class="bi bi-lightning-charge"></i>
                        <div class="stats-item">
                            <span data-purecounter-start="0" data-purecounter-end="150" data-purecounter-duration="1"
                                class="purecounter"></span>
                            <p>Equipos y accesorios</p>
                        </div>
                    </div>

                    <!-- Staff (coaches + recepción) -->
                    <div class="col-lg-3 col-md-6 d-flex flex-column align-items-center">
                        <i class="bi bi-person-check"></i>
                        <div class="stats-item">
                            <span data-purecounter-start="0" data-purecounter-end="18" data-purecounter-duration="1"
                                class="purecounter"></span>
                            <p>Coaches y staff</p>
                        </div>
                    </div>

                </div>
            </div>
        </section><!-- /Sección de Estadísticas -->


        <!-- Sección de Características -->
        <!-- Sección de Características -->
        <section id="features" class="features section">
            <div class="container">
                <div class="row gy-4">

                    <!-- 1. Ambientes de alto rendimiento -->
                    <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                        <h3>Ambientes de alto rendimiento</h3>
                        <p>
                            Entrenar en serio exige un espacio a la altura. En Max Power Gym cuentas con salas amplias,
                            pisos deportivos y grilla LED que eleva la energía. Aquí se viene a concentrarse, ejecutar y
                            progresar.
                        </p>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check2"></i> <span>Espacios grandes, ordenados y ventilados.</span></li>
                            <li><i class="bi bi-check2"></i> <span>Espejos de pared completa para cuidar la
                                    técnica.</span></li>
                            <li><i class="bi bi-check2"></i> <span>Ambiente motivador sin distracciones.</span></li>
                        </ul>
                    </div>

                    <!-- 2. Clases que exigen y motivan -->
                    <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                        <h3>Clases que exigen y motivan</h3>
                        <p>
                            Funcional, fuerza, core, baile y HIIT. Programas pensados para todos los niveles con
                            progresión real.
                            Vienes, das lo mejor, sudas la camiseta y te vas con la sensación de haber cumplido.
                        </p>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check2"></i> <span>Metodologías claras y escalables.</span></li>
                            <li><i class="bi bi-check2"></i> <span>Coaches atentos a postura y seguridad.</span></li>
                            <li><i class="bi bi-check2"></i> <span>Horarios amplios para no faltar al
                                    entrenamiento.</span></li>
                        </ul>
                    </div>

                    <!-- 3. Equipamiento y tecnología -->
                    <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                        <h3>Equipamiento y tecnología</h3>
                        <p>
                            Máquinas de fuerza, zona de cardio y accesorios listos para trabajar como se debe.
                            Control de accesos y procesos pensados para que tu tiempo rinda de principio a fin.
                        </p>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check2"></i> <span>Equipos de alto desempeño y mantenimiento
                                    constante.</span></li>
                            <li><i class="bi bi-check2"></i> <span>Accesorios completos para cada grupo muscular.</span>
                            </li>
                            <li><i class="bi bi-check2"></i> <span>Gestión ágil: ingreso rápido y orden en piso.</span>
                            </li>
                        </ul>
                    </div>

                    <!-- 4. Disciplina, seguridad y comunidad -->
                    <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="400">
                        <h3>Disciplina, seguridad y comunidad</h3>
                        <p>
                            No vendemos fórmulas mágicas: vendemos constancia. Cuidamos el respeto, la higiene y la
                            técnica.
                            La meta es simple: que entrenes hoy, mañana y pasado… y veas resultados medibles.
                        </p>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check2"></i> <span>Normas claras para entrenar sin excusas.</span></li>
                            <li><i class="bi bi-check2"></i> <span>Protocolos de seguridad y soporte del staff.</span>
                            </li>
                            <li><i class="bi bi-check2"></i> <span>Comunidad que empuja hacia el siguiente nivel.</span>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
        </section>
        <!-- /Sección de Características -->


        <!-- Sección de Servicios -->
        <section id="services" class="services section">

            <!-- Título de la Sección -->
            <div class="container section-title" data-aos="fade-up">
                <h2>Servicios</h2>
                <div><span>Conoce nuestros</span> <span class="description-title">Servicios</span></div>
            </div>

            <div class="container">
                <div class="row gy-4">

                    <!-- 1 -->
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                        <div class="service-item position-relative">
                            <div class="icon"><i class="bi bi-lightning-charge"></i></div>
                            <h3>Musculación y fuerza</h3>
                            <p>
                                Entrenamiento estructurado para construir fuerza real: progresiones semanales, técnica
                                correcta y
                                acompañamiento del staff. Zona de pesas completa para trabajar cada grupo muscular sin
                                perder tiempo.
                            </p>
                        </div>
                    </div>

                    <!-- 2 -->
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                        <div class="service-item position-relative">
                            <div class="icon"><i class="bi bi-heart-pulse"></i></div>
                            <h3>Cardio y resistencia</h3>
                            <p>
                                Cintas, elípticas y bicicletas para mejorar capacidad aeróbica y control de peso.
                                Protocolos HIIT y trabajo
                                en zona para maximizar resultados sin sesiones eternas.
                            </p>
                        </div>
                    </div>

                    <!-- 3 -->
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                        <div class="service-item position-relative">
                            <div class="icon"><i class="bi bi-people"></i></div>
                            <h3>Clases dirigidas</h3>
                            <p>
                                Funcional, fuerza, core y baile. Ritmo, técnica y motivación en grupo, con opciones para
                                principiantes y
                                avanzados. Saldrás cansado, pero contento.
                            </p>
                        </div>
                    </div>

                    <!-- 4 -->
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
                        <div class="service-item position-relative">
                            <div class="icon"><i class="bi bi-clipboard-check"></i></div>
                            <h3>Evaluación y plan</h3>
                            <p>
                                Diagnóstico inicial, objetivos claros y rutina personalizada. Ajustes periódicos para
                                que el progreso no se
                                estanque y la técnica siempre sea segura.
                            </p>
                        </div>
                    </div>

                    <!-- 5 -->
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="500">
                        <div class="service-item position-relative">
                            <div class="icon"><i class="bi bi-qr-code-scan"></i></div>
                            <h3>Membresías y accesos</h3>
                            <p>
                                Planes flexibles y control de ingreso para una experiencia ordenada. Horarios amplios y
                                normas claras:
                                entrenas sin filas, sin excusas y sin perder el enfoque.
                            </p>
                        </div>
                    </div>

                    <!-- 6 -->
                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="600">
                        <div class="service-item position-relative">
                            <div class="icon"><i class="bi bi-bag-check"></i></div>
                            <h3>Tienda & recovery</h3>
                            <p>
                                Hidratación y accesorios básicos para complementar tu sesión. Enfriamiento y
                                estiramientos guiados para
                                cuidar articulaciones y acelerar la recuperación.
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </section><!-- /Sección de Servicios -->


        <!-- Sección de contacto -->
        <section id="contact" class="contact section light-background">
            <!-- Título -->
            <div class="container section-title" data-aos="fade-up">
                <h2>Contacto</h2>
                <div><span>¿Necesitas ayuda?</span> <span class="description-title">Contáctanos</span></div>
            </div>

            <div class="container" data-aos="fade-up" data-aos-delay="100">
                <div class="row justify-content-center">
                    <div class="col-lg-8">

                        <div class="row gy-4 text-center">

                            <!-- Dirección -->
                            <div class="col-12" data-aos="fade-up" data-aos-delay="200">
                                <div class="info-item d-flex flex-column align-items-center">
                                    <i class="bi bi-geo-alt" style="font-size:2rem; color:#FF6600;"></i>
                                    <h3>Dirección</h3>
                                    <p>C/ Jorge Carrasco entre Z y 3 N° 46 · El Alto</p>
                                </div>
                            </div>

                            <!-- Horarios -->
                            <div class="col-12" data-aos="fade-up" data-aos-delay="240">
                                <div class="info-item d-flex flex-column align-items-center">
                                    <i class="bi bi-clock-history" style="font-size:2rem; color:#FF6600;"></i>
                                    <h3>Horarios</h3>
                                    <p>L–S 06:00–22:00 · Domingo 08:00–14:00</p>
                                </div>
                            </div>

                            <!-- Teléfono / WhatsApp -->
                            <div class="col-12" data-aos="fade-up" data-aos-delay="280">
                                <div class="info-item d-flex flex-column align-items-center">
                                    <i class="bi bi-telephone" style="font-size:2rem; color:#FF6600;"></i>
                                    <h3>Teléfono / WhatsApp</h3>
                                    <p>
                                        <a href="tel:+5917XXXXXXXX">+591 7XX XX XXX</a><br>
                                        <a href="https://wa.me/5917XXXXXXXX" target="_blank" rel="noopener">Escríbenos
                                            por WhatsApp</a>
                                    </p>
                                </div>
                            </div>

                            <!-- Correo -->
                            <div class="col-12" data-aos="fade-up" data-aos-delay="320">
                                <div class="info-item d-flex flex-column align-items-center">
                                    <i class="bi bi-envelope" style="font-size:2rem; color:#FF6600;"></i>
                                    <h3>Correo</h3>
                                    <p><a href="mailto:contacto@maxpowergym.bo">contacto@maxpowergym.bo</a></p>
                                </div>
                            </div>

                            <!-- QR opcional (centrado) -->
                            <div class="col-12" data-aos="fade-up" data-aos-delay="360">
                                <img src="bienvenida/assets/img/qr/whatsapp.png" alt="QR WhatsApp Max Power Gym"
                                    onerror="this.style.display='none'"
                                    style="max-width:240px; width:100%; border-radius:12px; border:1px solid rgba(0,0,0,.06);">
                                <div style="opacity:.75; margin-top:.5rem;">Escanéa el QR para escribirnos directo por
                                    WhatsApp.</div>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </section>




    </main>

    <footer id="footer" class="footer dark-background">

        <div class="container footer-top">
            <div class="row gy-4">
                <div class="col-lg-4 col-md-6 footer-about">
                    <a href="index.html" class="logo d-flex align-items-center">
                        <span class="sitename">Multi</span>
                    </a>
                    <div class="footer-contact pt-3">
                        <p>A108 Calle Adam</p>
                        <p>New York, NY 535022</p>
                        <p class="mt-3"><strong>Teléfono:</strong> <span>+1 5589 55488 55</span></p>
                        <p><strong>Correo electrónico:</strong> <span>info@example.com</span></p>
                    </div>
                    <div class="social-links d-flex mt-4">
                        <a href=""><i class="bi bi-twitter-x"></i></a>
                        <a href=""><i class="bi bi-facebook"></i></a>
                        <a href=""><i class="bi bi-instagram"></i></a>
                        <a href=""><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-3 footer-links">
                    <h4>Enlaces útiles</h4>
                    <ul>
                        <li><a href="#hero">Inicio</a></li>
                        <li><a href="#about">Sobre nosotros</a></li>
                        <li><a href="#services">Servicios</a></li>
                        <li><a href="#">Términos del servicio</a></li>
                        <li><a href="#">Política de privacidad</a></li>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-3 footer-links">
                    <h4>Nuestros servicios</h4>
                    <ul>
                        <li><a href="#">Diseño web</a></li>
                        <li><a href="#">Desarrollo web</a></li>
                        <li><a href="#">Gestión de productos</a></li>
                        <li><a href="#">Marketing</a></li>
                        <li><a href="#">Diseño gráfico</a></li>
                    </ul>
                </div>

                <div class="col-lg-4 col-md-12 footer-newsletter">
                    <h4>Nuestro boletín</h4>
                    <p>¡Suscríbete a nuestro boletín y recibe las últimas noticias sobre nuestros productos y servicios!
                    </p>
                    <form action="forms/newsletter.php" method="post" class="php-email-form">
                        <div class="newsletter-form"><input type="email" name="email"><input type="submit"
                                value="Suscribirse"></div>
                        <div class="loading">Cargando</div>
                        <div class="error-message"></div>
                        <div class="sent-message">Tu solicitud de suscripción ha sido enviada. ¡Gracias!</div>
                    </form>
                </div>

            </div>
        </div>

        <div class="container copyright text-center mt-4">
            <p>© <span>Derechos de autor</span> <strong class="px-1 sitename">Max Power Gym</strong> <span>Todos los
                    derechos
                    reservados</span>
            </p>
            <div class="credits">
                Diseñado por Cristian Alexander Aduviri Colque
            </div>
        </div>

    </footer>


    <!-- Scroll Top -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

    <!-- Preloader -->
    <div id="preloader">
        <div></div>
        <div></div>
        <div></div>
        <div></div>
    </div>

    <!-- Vendor JS Files -->
    <script src="bienvenida/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="bienvenida/assets/vendor/php-email-form/validate.js"></script>
    <script src="bienvenida/assets/vendor/aos/aos.js"></script>
    <script src="bienvenida/assets/vendor/purecounter/purecounter_vanilla.js"></script>
    <script src="bienvenida/assets/vendor/swiper/swiper-bundle.min.js"></script>
    <script src="bienvenida/assets/vendor/glightbox/js/glightbox.min.js"></script>
    <script src="bienvenida/assets/vendor/imagesloaded/imagesloaded.pkgd.min.js"></script>
    <script src="bienvenida/assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>

    <!-- Main JS File -->
    <script src="bienvenida/assets/js/main.js"></script>
</body>

</html><?php /**PATH C:\xampp\htdocs\Laravel\Proyecto_MPGFil\resources\views/index.blade.php ENDPATH**/ ?>