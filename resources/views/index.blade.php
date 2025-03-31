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
                    <li><a href="#team">Equipo</a></li>
                    <li><a href="#contact">Contacto</a></li>
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>
            <a class="btn-getstarted" href="{{ route('login') }}">Iniciar Sesión</a>

        </div>
    </header>


    <main class="main">
        <!-- Sección Hero -->
        <section id="hero" class="hero section dark-background">

            <div id="hero-carousel" class="carousel slide carousel-fade" data-bs-ride="carousel"
                data-bs-interval="5000">

                <div class="carousel-item active">
                    <img src="bienvenida/assets/img/hero-carousel/hero-carousel-1.jpg" alt="">
                    <div class="carousel-container">
                        <h2>Bienvenido a Max Power Gym<br></h2>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut
                            labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco
                            laboris nisi ut aliquip ex ea commodo consequat.</p>
                        <a href="{{ route('login') }}" class="btn-get-started">Comenzar</a>
                    </div>
                </div><!-- Fin del ítem del carrusel -->

                <div class="carousel-item">
                    <img src="bienvenida/assets/img/hero-carousel/hero-carousel-2.jpg" alt="">
                    <div class="carousel-container">
                        <h2>At vero eos et accusamus</h2>
                        <p>Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id
                            quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus.
                            Temporibus autem quibusdam et aut officiis debitis aut.</p>
                        <a href="{{ route('login') }}" class="btn-get-started">Comenzar</a>
                    </div>
                </div><!-- Fin del ítem del carrusel -->

                <div class="carousel-item">
                    <img src="bienvenida/assets/img/hero-carousel/hero-carousel-3.jpg" alt="">
                    <div class="carousel-container">
                        <h2>Temporibus autem quibusdam</h2>
                        <p>Beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur
                            aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi
                            nesciunt omnis iste natus error sit voluptatem accusantium.</p>
                        <a href="{{ route('login') }}" class="btn-get-started">Comenzar</a>
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
                            Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut
                            labore et dolore
                            magna aliqua.
                        </p>
                        <ul>
                            <li><i class="bi bi-check2-circle"></i> <span>Trabajo sin restricciones y con
                                    beneficios</span></li>
                            <li><i class="bi bi-check2-circle"></i> <span>Exploración de nuevas oportunidades para
                                    crecer</span></li>
                            <li><i class="bi bi-check2-circle"></i> <span>Trabajo sin compromisos ni condiciones
                                    limitantes</span></li>
                        </ul>
                    </div>

                    <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
                        <p>Trabajo sin restricciones y con beneficios. Buscar alternativas que nos ayuden a resolver
                            cualquier reto con eficacia. Hacerlo siempre en un entorno que permita el desarrollo
                            continuo y la satisfacción.</p>
                        <a href="#" class="read-more"><span>Leer Más</span><i class="bi bi-arrow-right"></i></a>
                    </div>

                </div>

            </div>

        </section><!-- /Sección Acerca de -->


        <!-- Sección de Estadísticas -->
        <section id="stats" class="stats section">

            <div class="container" data-aos="fade-up" data-aos-delay="100">

                <div class="row gy-4">

                    <div class="col-lg-3 col-md-6 d-flex flex-column align-items-center">
                        <i class="bi bi-emoji-smile"></i>
                        <div class="stats-item">
                            <span data-purecounter-start="0" data-purecounter-end="298" data-purecounter-duration="1"
                                class="purecounter"></span>
                            <p>Clientes Felices</p>
                        </div>
                    </div><!-- Fin del ítem de Estadísticas -->

                    <div class="col-lg-3 col-md-6 d-flex flex-column align-items-center">
                        <i class="bi bi-journal-richtext"></i>
                        <div class="stats-item">
                            <span data-purecounter-start="0" data-purecounter-end="521" data-purecounter-duration="1"
                                class="purecounter"></span>
                            <p>Proyectos</p>
                        </div>
                    </div><!-- Fin del ítem de Estadísticas -->

                    <div class="col-lg-3 col-md-6 d-flex flex-column align-items-center">
                        <i class="bi bi-headset"></i>
                        <div class="stats-item">
                            <span data-purecounter-start="0" data-purecounter-end="1463" data-purecounter-duration="1"
                                class="purecounter"></span>
                            <p>Horas de Soporte</p>
                        </div>
                    </div><!-- Fin del ítem de Estadísticas -->

                    <div class="col-lg-3 col-md-6 d-flex flex-column align-items-center">
                        <i class="bi bi-people"></i>
                        <div class="stats-item">
                            <span data-purecounter-start="0" data-purecounter-end="15" data-purecounter-duration="1"
                                class="purecounter"></span>
                            <p>Trabajadores Dedicados</p>
                        </div>
                    </div><!-- Fin del ítem de Estadísticas -->

                </div>

            </div>

        </section><!-- /Sección de Estadísticas -->


        <!-- Sección de Características -->
        <section id="features" class="features section">

            <div class="container">

                <div class="row gy-4">

                    <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                        <h3>Quasi eaque omnis</h3>
                        <p>No obstante, su solución es tal que, por trabajo, cualquier corrupción es evitada, y se odia
                            la avaricia que causa molestias grandes.</p>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check2"></i> <span>Trabajo que no excluye ningún elemento</span></li>
                            <li><i class="bi bi-check2"></i> <span>Dolor frecuente que interrumpe el camino</span></li>
                            <li><i class="bi bi-check2"></i> <span>Trabajo dedicado a un fin superior</span></li>
                        </ul>
                    </div><!-- Fin de item de característica-->

                    <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                        <h3>Y nadie tiene dolores en común</h3>
                        <p>El trabajo debe ser preciso, pero tiene consecuencias que no siempre son conocidas. A veces
                            nos enfrentamos a situaciones que parecen imposibles de resolver.</p>

                        <ul class="list-unstyled">
                            <li><i class="bi bi-check2"></i> <span>Alternativas disponibles en todo momento</span></li>
                            <li><i class="bi bi-check2"></i> <span>Es posible obtener las mejores soluciones</span></li>
                            <li><i class="bi bi-check2"></i> <span>Obras complejas que requieren esfuerzo</span></li>
                        </ul>
                    </div><!-- Fin de item de característica-->

                    <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
                        <h3>El trabajo arduo tiene su recompensa</h3>
                        <p>Las dificultades aparecen, pero lo importante es enfrentarlas con valentía, buscando la
                            solución sin ceder al miedo.</p>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check2"></i> <span>El esfuerzo siempre tiene un resultado</span></li>
                            <li><i class="bi bi-check2"></i> <span>Las dificultades son superadas con trabajo</span>
                            </li>
                        </ul>
                    </div><!-- Fin de item de característica-->

                    <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="400">
                        <h3>El compromiso siempre vale la pena</h3>
                        <p>A veces las situaciones nos desafían, pero el trabajo perseverante es lo que nos permite
                            seguir adelante.</p>
                        <ul class="list-unstyled">
                            <li><i class="bi bi-check2"></i> <span>La verdad siempre prevalece</span></li>
                            <li><i class="bi bi-check2"></i> <span>El esfuerzo constante lleva al éxito</span></li>
                            <li><i class="bi bi-check2"></i> <span>Las recompensas llegan con dedicación</span></li>
                        </ul>
                    </div><!-- Fin de item de característica-->

                </div>

            </div>

        </section><!-- /Sección de Características -->


        <!-- Sección de Servicios -->
        <section id="services" class="services section">

            <!-- Título de la Sección -->
            <div class="container section-title" data-aos="fade-up">
                <h2>Servicios</h2>
                <div><span>Consulta Nuestros</span> <span class="description-title">Servicios</span></div>
            </div><!-- Fin del Título de la Sección -->

            <div class="container">

                <div class="row gy-4">

                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                        <div class="service-item  position-relative">
                            <div class="icon">
                                <i class="bi bi-activity"></i>
                            </div>
                            <a href="service-details.html" class="stretched-link">
                                <h3>Nesciunt Mete</h3>
                            </a>
                            <p>Proporciona lo necesario para garantizar la mejor experiencia. Eos accusantium minus
                                dolores iure perferendis tiempo y resultados.</p>
                        </div>
                    </div><!-- Fin del Elemento de Servicio -->

                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                        <div class="service-item position-relative">
                            <div class="icon">
                                <i class="bi bi-broadcast"></i>
                            </div>
                            <a href="service-details.html" class="stretched-link">
                                <h3>Eosle Commodi</h3>
                            </a>
                            <p>Ofrecemos soluciones adaptadas a tus necesidades. Nuestro equipo trabaja para brindarte
                                el mejor servicio posible.</p>
                        </div>
                    </div><!-- Fin del Elemento de Servicio -->

                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                        <div class="service-item position-relative">
                            <div class="icon">
                                <i class="bi bi-easel"></i>
                            </div>
                            <a href="service-details.html" class="stretched-link">
                                <h3>Ledo Markt</h3>
                            </a>
                            <p>Servicios exclusivos diseñados para satisfacer todas tus expectativas. Calidad y
                                confianza en cada entrega.</p>
                        </div>
                    </div><!-- Fin del Elemento de Servicio -->

                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
                        <div class="service-item position-relative">
                            <div class="icon">
                                <i class="bi bi-bounding-box-circles"></i>
                            </div>
                            <a href="service-details.html" class="stretched-link">
                                <h3>Asperiores Commodit</h3>
                            </a>
                            <p>Soluciones personalizadas para cada cliente. Atención excepcional y resultados
                                garantizados.</p>
                        </div>
                    </div><!-- Fin del Elemento de Servicio -->

                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="500">
                        <div class="service-item position-relative">
                            <div class="icon">
                                <i class="bi bi-calendar4-week"></i>
                            </div>
                            <a href="service-details.html" class="stretched-link">
                                <h3>Velit Doloremque</h3>
                            </a>
                            <p>Compromiso y dedicación en cada proyecto. Nuestro equipo está listo para brindarte el
                                mejor soporte.</p>
                        </div>
                    </div><!-- Fin del Elemento de Servicio -->

                    <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="600">
                        <div class="service-item position-relative">
                            <div class="icon">
                                <i class="bi bi-chat-square-text"></i>
                            </div>
                            <a href="service-details.html" class="stretched-link">
                                <h3>Dolori Architecto</h3>
                            </a>
                            <p>Soluciones innovadoras adaptadas a tus necesidades. Confía en nuestra experiencia para
                                obtener los mejores resultados.</p>
                        </div>
                    </div><!-- Fin del Elemento de Servicio -->

                </div>

            </div>

        </section><!-- /Sección de Servicios -->


        <!-- Sección de Testimonios -->
        <section id="testimonials" class="testimonials section light-background">

            <!-- Título de la Sección -->
            <div class="container section-title" data-aos="fade-up">
                <h2>Testimonios</h2>
                <div><span>Consulta nuestros</span> <span class="description-title">Testimonios</span></div>
            </div><!-- Fin del Título de la Sección -->

            <div class="container" data-aos="fade-up" data-aos-delay="100">

                <div class="swiper init-swiper">
                    <script type="application/json" class="swiper-config">
    {
      "loop": true,
      "speed": 600,
      "autoplay": {
        "delay": 5000
      },
      "slidesPerView": "auto",
      "pagination": {
        "el": ".swiper-pagination",
        "type": "bullets",
        "clickable": true
      },
      "breakpoints": {
        "320": {
          "slidesPerView": 1,
          "spaceBetween": 40
        },
        "1200": {
          "slidesPerView": 2,
          "spaceBetween": 20
        }
      }
    }
  </script>
                    <div class="swiper-wrapper">

                        <div class="swiper-slide">
                            <div class="testimonial-wrap">
                                <div class="testimonial-item">
                                    <img src="bienvenida/assets/img/testimonials/testimonials-1.jpg" class="testimonial-img"
                                        alt="">
                                    <h3>Saul Goodman</h3>
                                    <h4>CEO &amp; Fundador</h4>
                                    <div class="stars">
                                        <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i
                                            class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i
                                            class="bi bi-star-fill"></i>
                                    </div>
                                    <p>
                                        <i class="bi bi-quote quote-icon-left"></i>
                                        <span>Proin iaculis purus consequat sem cure dignissim donec porttitor elementum
                                            suscipit rhoncus. Accusantium quam, ultricies eget id, aliquam eget nibh et.
                                            Maecenas aliquam, risus at semper.</span>
                                        <i class="bi bi-quote quote-icon-right"></i>
                                    </p>
                                </div>
                            </div>
                        </div><!-- Fin del Testimonio -->

                        <div class="swiper-slide">
                            <div class="testimonial-wrap">
                                <div class="testimonial-item">
                                    <img src="bienvenida/assets/img/testimonials/testimonials-2.jpg" class="testimonial-img"
                                        alt="">
                                    <h3>Sara Wilsson</h3>
                                    <h4>Diseñadora</h4>
                                    <div class="stars">
                                        <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i
                                            class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i
                                            class="bi bi-star-fill"></i>
                                    </div>
                                    <p>
                                        <i class="bi bi-quote quote-icon-left"></i>
                                        <span>Export tempor illum tamen malis malis eram quae irure esse labore quem
                                            cillum quid cillum eram malis quorum velit fore eram velit sunt aliqua
                                            noster fugiat irure amet legam anim culpa.</span>
                                        <i class="bi bi-quote quote-icon-right"></i>
                                    </p>
                                </div>
                            </div>
                        </div><!-- Fin del Testimonio -->

                        <div class="swiper-slide">
                            <div class="testimonial-wrap">
                                <div class="testimonial-item">
                                    <img src="bienvenida/assets/img/testimonials/testimonials-3.jpg" class="testimonial-img"
                                        alt="">
                                    <h3>Jena Karlis</h3>
                                    <h4>Dueña de Tienda</h4>
                                    <div class="stars">
                                        <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i
                                            class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i
                                            class="bi bi-star-fill"></i>
                                    </div>
                                    <p>
                                        <i class="bi bi-quote quote-icon-left"></i>
                                        <span>Enim nisi quem export duis labore cillum quae magna enim sint quorum nulla
                                            quem veniam duis minim tempor labore quem eram duis noster aute amet eram
                                            fore quis sint minim.</span>
                                        <i class="bi bi-quote quote-icon-right"></i>
                                    </p>
                                </div>
                            </div>
                        </div><!-- Fin del Testimonio -->

                        <div class="swiper-slide">
                            <div class="testimonial-wrap">
                                <div class="testimonial-item">
                                    <img src="bienvenida/assets/img/testimonials/testimonials-4.jpg" class="testimonial-img"
                                        alt="">
                                    <h3>Matt Brandon</h3>
                                    <h4>Freelancer</h4>
                                    <div class="stars">
                                        <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i
                                            class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i
                                            class="bi bi-star-fill"></i>
                                    </div>
                                    <p>
                                        <i class="bi bi-quote quote-icon-left"></i>
                                        <span>Fugiat enim eram quae cillum dolore dolor amet nulla culpa multos export
                                            minim fugiat minim velit minim dolor enim duis veniam ipsum anim magna sunt
                                            elit fore quem dolore labore illum veniam.</span>
                                        <i class="bi bi-quote quote-icon-right"></i>
                                    </p>
                                </div>
                            </div>
                        </div><!-- Fin del Testimonio -->

                        <div class="swiper-slide">
                            <div class="testimonial-wrap">
                                <div class="testimonial-item">
                                    <img src="bienvenida/assets/img/testimonials/testimonials-5.jpg" class="testimonial-img"
                                        alt="">
                                    <h3>John Larson</h3>
                                    <h4>Emprendedor</h4>
                                    <div class="stars">
                                        <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i
                                            class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i
                                            class="bi bi-star-fill"></i>
                                    </div>
                                    <p>
                                        <i class="bi bi-quote quote-icon-left"></i>
                                        <span>Quis quorum aliqua sint quem legam fore sunt eram irure aliqua veniam
                                            tempor noster veniam enim culpa labore duis sunt culpa nulla illum cillum
                                            fugiat legam esse veniam culpa fore nisi cillum quid.</span>
                                        <i class="bi bi-quote quote-icon-right"></i>
                                    </p>
                                </div>
                            </div>
                        </div><!-- Fin del Testimonio -->

                    </div>
                    <div class="swiper-pagination"></div>
                </div>

            </div>

        </section><!-- /Sección de Testimonios -->


        <!-- Sección de Llamado a la Acción -->
        <section id="call-to-action" class="call-to-action section dark-background">

            <img src="bienvenida/assets/img/cta-bg.jpg" alt="">

            <div class="container">
                <div class="row justify-content-center" data-aos="zoom-in" data-aos-delay="100">
                    <div class="col-xl-10">
                        <div class="text-center">
                            <h3>Llamado a la Acción</h3>
                            <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat
                                nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui
                                officia deserunt mollit anim id est laborum.</p>
                            <a class="cta-btn" href="#">Llamado a la Acción</a>
                        </div>
                    </div>
                </div>
            </div>

        </section><!-- /Sección de Llamado a la Acción -->


        <!-- Sección de equipo -->
        <section id="team" class="team section">

            <!-- Título de la sección -->
            <div class="container section-title" data-aos="fade-up">
                <h2>Equipo</h2>
                <div><span>Conoce a nuestro</span> <span class="description-title">Equipo</span></div>
            </div><!-- Fin del título de la sección -->

            <div class="container">

                <div class="row gy-4">

                    <div class="col-xl-3 col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                        <div class="member">
                            <img src="bienvenida/assets/img/team/team-1.jpg" class="img-fluid" alt="">
                            <div class="member-info">
                                <div class="member-info-content">
                                    <h4>Walter White</h4>
                                    <span>Director Ejecutivo</span>
                                </div>
                                <div class="social">
                                    <a href=""><i class="bi bi-twitter-x"></i></a>
                                    <a href=""><i class="bi bi-facebook"></i></a>
                                    <a href=""><i class="bi bi-instagram"></i></a>
                                    <a href=""><i class="bi bi-linkedin"></i></a>
                                </div>
                            </div>
                        </div>
                    </div><!-- Fin del miembro del equipo -->

                    <div class="col-xl-3 col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                        <div class="member">
                            <img src="bienvenida/assets/img/team/team-2.jpg" class="img-fluid" alt="">
                            <div class="member-info">
                                <div class="member-info-content">
                                    <h4>Sarah Jhonson</h4>
                                    <span>Gerente de Producto</span>
                                </div>
                                <div class="social">
                                    <a href=""><i class="bi bi-twitter-x"></i></a>
                                    <a href=""><i class="bi bi-facebook"></i></a>
                                    <a href=""><i class="bi bi-instagram"></i></a>
                                    <a href=""><i class="bi bi-linkedin"></i></a>
                                </div>
                            </div>
                        </div>
                    </div><!-- Fin del miembro del equipo -->

                    <div class="col-xl-3 col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="300">
                        <div class="member">
                            <img src="bienvenida/assets/img/team/team-3.jpg" class="img-fluid" alt="">
                            <div class="member-info">
                                <div class="member-info-content">
                                    <h4>William Anderson</h4>
                                    <span>Director de Tecnología</span>
                                </div>
                                <div class="social">
                                    <a href=""><i class="bi bi-twitter-x"></i></a>
                                    <a href=""><i class="bi bi-facebook"></i></a>
                                    <a href=""><i class="bi bi-instagram"></i></a>
                                    <a href=""><i class="bi bi-linkedin"></i></a>
                                </div>
                            </div>
                        </div>
                    </div><!-- Fin del miembro del equipo -->

                    <div class="col-xl-3 col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="400">
                        <div class="member">
                            <img src="bienvenida/assets/img/team/team-4.jpg" class="img-fluid" alt="">
                            <div class="member-info">
                                <div class="member-info-content">
                                    <h4>Amanda Jepson</h4>
                                    <span>Contadora</span>
                                </div>
                                <div class="social">
                                    <a href=""><i class="bi bi-twitter-x"></i></a>
                                    <a href=""><i class="bi bi-facebook"></i></a>
                                    <a href=""><i class="bi bi-instagram"></i></a>
                                    <a href=""><i class="bi bi-linkedin"></i></a>
                                </div>
                            </div>
                        </div>
                    </div><!-- Fin del miembro del equipo -->

                </div>

            </div>

        </section><!-- /Sección de equipo -->


        <!-- Seccion de contacto -->
        <section id="contact" class="contact section light-background">

            <!-- Título de la sección -->
            <div class="container section-title" data-aos="fade-up">
                <h2>Contacto</h2>
                <div><span>¿Necesitas ayuda?</span> <span class="description-title">Contáctanos</span></div>
            </div><!-- Fin del título de la sección -->

            <div class="container" data-aos="fade-up" data-aos-delay="100">

                <div class="row gy-4">
                    <div class="col-lg-6 ">
                        <div class="row gy-4">

                            <div class="col-lg-12">
                                <div class="info-item d-flex flex-column justify-content-center align-items-center"
                                    data-aos="fade-up" data-aos-delay="200">
                                    <i class="bi bi-geo-alt"></i>
                                    <h3>Dirección</h3>
                                    <p>A108 Calle Adam, Nueva York, NY 535022</p>
                                </div>
                            </div><!-- Fin del elemento de información -->

                            <div class="col-md-6">
                                <div class="info-item d-flex flex-column justify-content-center align-items-center"
                                    data-aos="fade-up" data-aos-delay="300">
                                    <i class="bi bi-telephone"></i>
                                    <h3>Llámanos</h3>
                                    <p>+1 5589 55488 55</p>
                                </div>
                            </div><!-- Fin del elemento de información -->

                            <div class="col-md-6">
                                <div class="info-item d-flex flex-column justify-content-center align-items-center"
                                    data-aos="fade-up" data-aos-delay="400">
                                    <i class="bi bi-envelope"></i>
                                    <h3>Envíanos un correo</h3>
                                    <p>info@example.com</p>
                                </div>
                            </div><!-- Fin del elemento de información -->

                        </div>
                    </div>

                    <div class="col-lg-6">
                        <form action="forms/contact.php" method="post" class="php-email-form" data-aos="fade-up"
                            data-aos-delay="500">
                            <div class="row gy-4">

                                <div class="col-md-6">
                                    <input type="text" name="name" class="form-control" placeholder="Tu Nombre"
                                        required="">
                                </div>

                                <div class="col-md-6 ">
                                    <input type="email" class="form-control" name="email"
                                        placeholder="Tu Correo Electrónico" required="">
                                </div>

                                <div class="col-md-12">
                                    <input type="text" class="form-control" name="subject" placeholder="Asunto"
                                        required="">
                                </div>

                                <div class="col-md-12">
                                    <textarea class="form-control" name="message" rows="4" placeholder="Mensaje"
                                        required=""></textarea>
                                </div>

                                <div class="col-md-12 text-center">
                                    <div class="loading">Cargando</div>
                                    <div class="error-message"></div>
                                    <div class="sent-message">Tu mensaje ha sido enviado. ¡Gracias!</div>

                                    <button type="submit">Enviar Mensaje</button>
                                </div>

                            </div>
                        </form>
                    </div><!-- Fin del formulario de contacto -->

                </div>

            </div>

        </section><!-- /Sección de contacto -->

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
            <p>© <span>Derechos de autor</span> <strong class="px-1 sitename">Max Power Gym</strong> <span>Todos los derechos
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

</html>