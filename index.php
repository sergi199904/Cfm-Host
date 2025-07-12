<?php
require_once 'includes/db_test.php';

// Filtro por categoría
$categoria_filtro = isset($_GET['categoria']) ? limpiar_input($_GET['categoria']) : '';

// Consulta de productos con filtro
$query = "SELECT * FROM productos WHERE 1=1";
$params = [];
$types = '';

if ($categoria_filtro && $categoria_filtro !== 'todas') {
    $query .= " AND categoria = ?";
    $params[] = $categoria_filtro;
    $types .= 's';
}

$query .= " ORDER BY fecha DESC";

$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Obtener categorías para el filtro
$categories = $conn->query("SELECT nombre, COUNT(*) as total FROM productos GROUP BY categoria ORDER BY nombre");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>CFM Joyas - Venta de Joyas & Accesorios</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="CFM Joyas - Especialistas en joyas, cerámicas y accesorios únicos. Encuentra las mejores piezas con precios accesibles.">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- CSS personalizado - RUTA CORREGIDA -->
  <link href="/css/style.css" rel="stylesheet">
  <style>
    .admin-btn {
      position: fixed;
      bottom: 20px;
      right: 20px;
      z-index: 1000;
      background: linear-gradient(45deg, #007bff, #0056b3);
      border: none;
      border-radius: 50px;
      padding: 15px 20px;
      box-shadow: 0 4px 15px rgba(0,123,255,0.3);
      transition: all 0.3s ease;
    }
    
    .admin-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(0,123,255,0.4);
    }
    
    .category-filter {
      background: linear-gradient(135deg, #000 0%, #2c2c2c 100%) !important;
      color: white;
      border-radius: 20px;
      padding: 25px;
      margin-bottom: 40px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
      border: 2px solid rgba(255, 215, 0, 0.2);
    }
    
    .category-filter h5 {
      font-family: 'Playfair Display', serif;
      font-weight: 600;
      font-size: 1.4rem;
      color: #ffd700 !important;
      margin-bottom: 20px;
    }
    
    .filter-btn {
      background: rgba(255, 215, 0, 0.1) !important;
      border: 2px solid rgba(255, 215, 0, 0.3) !important;
      color: white !important;
      border-radius: 25px;
      padding: 10px 20px;
      margin: 5px;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
      font-weight: 500;
      font-family: 'Inter', sans-serif;
      backdrop-filter: blur(5px);
    }
    
    .filter-btn:hover, .filter-btn.active {
      background: linear-gradient(45deg, #ffd700, #ffb347) !important;
      color: #000 !important;
      transform: translateY(-3px) scale(1.05);
      box-shadow: 0 8px 25px rgba(255, 215, 0, 0.4);
      border-color: #ffd700 !important;
      font-weight: 600;
      text-decoration: none;
    }
    
    .product-card {
      position: relative;
      height: 420px;
      overflow: hidden;
      border-radius: 15px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .product-card .card-img-top {
      height: 250px;
      object-fit: cover;
      transition: transform 0.3s ease;
    }
    
    .product-card:hover .card-img-top {
      transform: scale(1.05);
    }
    
    /* Mejora para navegación de secciones */
    section {
      scroll-margin-top: 90px; /* Espacio para navbar fija */
    }
    
    /* Asegurar que los títulos no se corten */
    #historia, #productos, #contacto, #ubicacion {
      padding-top: 100px !important;
      margin-top: -20px;
    }

    /* ESTILOS PARA FORMULARIO DE CONTACTO MEJORADO */
    .form-control.is-valid {
      border-color: #28a745;
      background-image: none;
    }

    .form-control.is-invalid {
      border-color: #dc3545;
      background-image: none;
    }

    .valid-feedback, .invalid-feedback {
      font-size: 0.875rem;
      margin-top: 0.25rem;
    }

    .valid-feedback {
      color: #28a745;
    }

    .invalid-feedback {
      color: #dc3545;
    }

    /* Animación del botón */
    #submitBtn {
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    #submitBtn:disabled {
      opacity: 0.7;
      transform: none !important;
    }

    #submitBtn.loading {
      pointer-events: none;
    }

    #submitBtn.loading .btn-text {
      opacity: 0;
    }

    #submitBtn.loading::after {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 20px;
      height: 20px;
      margin: -10px 0 0 -10px;
      border: 2px solid #fff;
      border-top: 2px solid transparent;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* Contador de caracteres */
    #charCount {
      font-weight: 600;
    }

    .char-warning {
      color: #ffc107 !important;
    }

    .char-danger {
      color: #dc3545 !important;
    }

    /* Modal personalizado */
    .modal-content {
      border-radius: 15px;
    }

    .modal-header {
      border-radius: 15px 15px 0 0;
    }

    /* Animación de entrada */
    @keyframes slideInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
  </style>

<style>
  footer {
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
  }
</style>
</head>

<body>
  <!-- Botón de administración -->
  <a href="admin/login.php" class="btn admin-btn text-white" title="Panel de Administración">
    <i class="fas fa-cog"></i> Admin
  </a>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg custom-nav">
    <div class="container">
      <a class="navbar-brand" href="/">
        <img src="img/logooficial.jpg" alt="Logo" class="logo"> CFM Joyas
      </a>
      <button class="navbar-toggler custom-toggler" type="button"
              data-bs-toggle="collapse" data-bs-target="#navbarNav"
              aria-controls="navbarNav" aria-expanded="false"
              aria-label="Toggle navigation">
        <i class="fas fa-bars"></i>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="#historia">Historia</a></li>
          <li class="nav-item"><a class="nav-link" href="#productos">Productos</a></li>
          <li class="nav-item"><a class="nav-link" href="#contacto">Contacto</a></li>
          <li class="nav-item"><a class="nav-link" href="#ubicacion">Ubicación</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Header -->
  <header class="text-center py-5">
    <h1 class="header-title">CFM Joyas</h1>
    <p class="lead">"Venta de Joyas & Accesorios"</p>
    <p class="text-muted">
      <i class="fas fa-gem"></i> Joyas únicas 
      <i class="fas fa-palette ms-3"></i> Cerámicas artesanales 
      <i class="fas fa-star ms-3"></i> Accesorios especiales
    </p>
  </header>

  <!-- CARRUSEL OPTIMIZADO - SOLO PRIMERA IMAGEN CON TEXTO -->
  <div id="mainCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">
    <div class="carousel-indicators">
      <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="0" class="active"></button>
      <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="1"></button>
      <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="2"></button>
      <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="3"></button>
      <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="4"></button>
      <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="5"></button>
      <button type="button" data-bs-target="#mainCarousel" data-bs-slide-to="6"></button>
    </div>
    
    <div class="carousel-inner">
      <!-- Slide 1: Imagen 6 - ÚNICA CON TEXTO DE BIENVENIDA -->
      <div class="carousel-item active">
        <div class="carousel-image-container">
          <img src="img/Carrusel/imagen6.jpg" class="d-block carousel-img" alt="Bienvenidos a CFM Joyas">
          <div class="carousel-overlay"></div>
        </div>
        <div class="carousel-caption">
          <div class="carousel-content">
            <h2 class="carousel-title">Bienvenidos a CFM Joyas</h2>
            <p class="carousel-description">Un espacio donde el arte, la creatividad y la calidad se encuentran para ofrecerte la mejor experiencia en joyas y cerámicas.</p>
            <a href="#contacto" class="btn-carousel">
              <i class="fas fa-store"></i> Visítanos
            </a>
          </div>
        </div>
      </div>

      <!-- Slide 3: Imagen 2 - SOLO IMAGEN -->
      <div class="carousel-item">
        <div class="carousel-image-container">
          <img src="img/Carrusel/imagen2.jpg" class="d-block carousel-img" alt="Collares con piedras naturales de colores">
        </div>
      </div>

      <!-- Slide 4: Imagen 7 - SOLO IMAGEN -->
      <div class="carousel-item">
        <div class="carousel-image-container">
          <img src="img/Carrusel/imagen7.jpg" class="d-block carousel-img" alt="Pulseras artesanales con diferentes estilos">
        </div>
      </div>

      <!-- Slide 5: Imagen 5 - SOLO IMAGEN -->
      <div class="carousel-item">
        <div class="carousel-image-container">
          <img src="img/Carrusel/imagen5.jpg" class="d-block carousel-img" alt="Colección premium de joyas variadas">
        </div>
      </div>

      <!-- Slide 6: Imagen 3 - SOLO IMAGEN -->
      <div class="carousel-item">
        <div class="carousel-image-container">
          <img src="img/Carrusel/imagen3.jpg" class="d-block carousel-img" alt="Cerámicas artesanales hechas a mano">
        </div>
      </div>

      <!-- Slide 7: Imagen 4 - SOLO IMAGEN -->
      <div class="carousel-item">
        <div class="carousel-image-container">
          <img src="img/Carrusel/imagen4.jpg" class="d-block carousel-img" alt="Piezas únicas de cerámica funcional">
        </div>
      </div>
    </div>
    
    <!-- Controles -->
    <button class="carousel-control-prev" type="button" data-bs-target="#mainCarousel" data-bs-slide="prev">
      <div class="carousel-control-icon">
        <i class="fas fa-chevron-left"></i>
      </div>
      <span class="visually-hidden">Anterior</span>
    </button>
    
    <button class="carousel-control-next" type="button" data-bs-target="#mainCarousel" data-bs-slide="next">
      <div class="carousel-control-icon">
        <i class="fas fa-chevron-right"></i>
      </div>
      <span class="visually-hidden">Siguiente</span>
    </button>
  </div>

  <!-- Sección Historia -->
  <section id="historia" class="py-5 text-center">
    <div class="container">
      <h2 class="mb-4">Una parte de nosotros</h2>
      <p class="mx-auto" style="max-width:700px;">
       Me cambié a Zapallar en busca de una vida más tranquila, donde el arte se convirtió en mi forma de expresión. Desde la cerámica hasta la joyería, cada pieza refleja mi creatividad . En esta página comparto no solo mis creaciones, sino también mi mundo.
      </p>
    </div>
  </section>

  <!-- Filtro de Categorías ACTUALIZADO -->
  <section class="py-3" id="filtros">
    <div class="container">
      <div class="category-filter text-center">
        <h5 class="mb-3"><i class="fas fa-filter"></i> Filtrar por categoría</h5>
        <div>
          <!-- Filtro "Todas" -->
          <a href="index.php#productos" class="btn filter-btn <?= empty($categoria_filtro) ? 'active' : '' ?>">
            <i class="fas fa-th"></i> Todas
          </a>
          
          <!-- Categorías principales ORIGINALES -->
          <a href="index.php?categoria=joyas#productos" 
             class="btn filter-btn <?= $categoria_filtro === 'joyas' ? 'active' : '' ?>">
            <i class="fas fa-gem"></i> Joyas
          </a>
          
          <a href="index.php?categoria=ceramicas#productos" 
             class="btn filter-btn <?= $categoria_filtro === 'ceramicas' ? 'active' : '' ?>">
            <i class="fas fa-palette"></i> Cerámicas
          </a>
          
          <a href="index.php?categoria=otros#productos" 
             class="btn filter-btn <?= $categoria_filtro === 'otros' ? 'active' : '' ?>">
            <i class="fas fa-star"></i> Otros
          </a>
          
          <!-- NUEVAS categorías específicas -->
          <a href="index.php?categoria=collares#productos" 
             class="btn filter-btn <?= $categoria_filtro === 'collares' ? 'active' : '' ?>">
            <i class="fas fa-circle-notch"></i> Collares
          </a>
          
          <a href="index.php?categoria=pulseras#productos" 
             class="btn filter-btn <?= $categoria_filtro === 'pulseras' ? 'active' : '' ?>">
            <i class="fas fa-link"></i> Pulseras
          </a>
          
          <a href="index.php?categoria=aretes#productos" 
             class="btn filter-btn <?= $categoria_filtro === 'aretes' ? 'active' : '' ?>">
            <i class="fas fa-earring"></i> Aretes
          </a>
          
          <a href="index.php?categoria=anillos#productos" 
             class="btn filter-btn <?= $categoria_filtro === 'anillos' ? 'active' : '' ?>">
            <i class="fas fa-ring"></i> Anillos
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- Sección Productos ACTUALIZADA CON INFO SIEMPRE VISIBLE Y SIN DOBLE $ -->
  <section id="productos" class="py-5 bg-light">
    <div class="container">
      <h2 class="text-center mb-4">
        Nuestros productos
        <?php if ($categoria_filtro): ?>
          <small class="text-muted">- <?= ucfirst($categoria_filtro) ?></small>
        <?php endif; ?>
      </h2>
      
      <?php if ($result->num_rows === 0): ?>
        <div class="text-center py-5">
          <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
          <h4 class="text-muted">No hay productos en esta categoría</h4>
          <a href="index.php#productos" class="btn btn-primary mt-3">Ver todos los productos</a>
        </div>
      <?php else: ?>
        <div class="row g-4">
          <?php $i = 0; while ($row = $result->fetch_assoc()) { $i++; ?>
            <div class="col-sm-6 col-md-4 col-lg-3">
              <a href="<?= htmlspecialchars($row['instagram']) ?>" 
                 target="_blank" rel="noopener noreferrer"
                 class="product-link text-decoration-none">
                <div class="card product-card border-0">
                  
                  <!-- Badge de Categoría -->
                  <div class="category-badge" data-category="<?= htmlspecialchars($row['categoria']) ?>">
                    <?= ucfirst(htmlspecialchars($row['categoria'])) ?>
                  </div>
                  
                  <!-- Imagen del Producto -->
                  <img src="<?= htmlspecialchars($row['imagen']) ?>" 
                       class="card-img-top" 
                       alt="<?= htmlspecialchars($row['nombre']) ?>">
                  
                  <!-- Información del producto SIEMPRE VISIBLE -->
                  <div class="product-info">
                    <h6 class="mb-2"><?= htmlspecialchars($row['nombre']) ?></h6>
                    
                    <!-- Precio prominente - CORREGIDO SIN DOBLE $ -->
                    <div class="price-display">
                      $<?= number_format($row['precio'], 0, ',', '.') ?> CLP
                    </div>
                    
                    <!-- Categoría -->
                    <p class="mb-1">
                      <i class="fas fa-tag"></i> <?= ucfirst(htmlspecialchars($row['categoria'])) ?>
                    </p>
                    
                    <!-- Enlace a Instagram -->
                    <p class="mb-0">
                      <i class="fab fa-instagram"></i> Ver en Instagram
                    </p>
                  </div>
                </div>
              </a>
            </div>
          <?php } ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Sección Contacto MEJORADA -->
  <section id="contacto" class="py-5">
    <div class="container">
      <h2 class="text-center mb-4">Contáctanos</h2>
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="card shadow">
            <div class="card-body">
              <form id="contactForm" action="send_email.php" method="POST" novalidate>
                <!-- Campo Nombre -->
                <div class="mb-3">
                  <label for="name" class="form-label">
                    <i class="fas fa-user"></i> Nombre <span class="text-danger">*</span>
                  </label>
                  <input type="text" name="name" id="name" class="form-control" 
                         placeholder="Tu nombre completo" required minlength="2" maxlength="50">
                  <div class="invalid-feedback">
                    El nombre debe tener entre 2 y 50 caracteres.
                  </div>
                  <div class="valid-feedback">
                    ¡Perfecto!
                  </div>
                </div>

                <!-- Campo Email -->
                <div class="mb-3">
                  <label for="email" class="form-label">
                    <i class="fas fa-envelope"></i> Correo Electrónico <span class="text-danger">*</span>
                  </label>
                  <input type="email" name="email" id="email" class="form-control" 
                         placeholder="tu@email.com" required>
                  <div class="invalid-feedback">
                    Por favor ingresa un email válido.
                  </div>
                  <div class="valid-feedback">
                    ¡Email válido!
                  </div>
                </div>

                <!-- Campo Mensaje -->
                <div class="mb-3">
                  <label for="message" class="form-label">
                    <i class="fas fa-comment"></i> Mensaje <span class="text-danger">*</span>
                  </label>
                  <textarea name="message" id="message" class="form-control" rows="4" 
                            placeholder="Cuéntanos sobre tu consulta, producto de interés, o cualquier pregunta..." 
                            required minlength="10" maxlength="500"></textarea>
                  <div class="invalid-feedback">
                    El mensaje debe tener entre 10 y 500 caracteres.
                  </div>
                  <div class="valid-feedback">
                    ¡Mensaje perfecto!
                  </div>
                  <small class="text-muted">
                    <span id="charCount">0</span>/500 caracteres
                  </small>
                </div>

                <!-- Botón Submit -->
                <div class="text-center">
                  <button type="submit" id="submitBtn" class="btn btn-dark btn-lg">
                    <i class="fas fa-paper-plane"></i> 
                    <span class="btn-text">Enviar Mensaje</span>
                  </button>
                </div>

                <!-- Indicador de carga -->
                <div id="loadingSpinner" class="text-center mt-3" style="display: none;">
                  <div class="spinner-border text-warning" role="status">
                    <span class="visually-hidden">Enviando...</span>
                  </div>
                  <p class="mt-2 text-muted">Enviando tu mensaje...</p>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Sección Ubicación -->
  <section id="ubicacion" class="py-5 bg-light">
    <div class="container text-center">
      <h2 class="mb-4">Ubicación</h2>
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <iframe src="https://maps.google.com/maps?q=-32.5541667,-71.4577222&output=embed"
                  width="100%" height="300" style="border:0; border-radius:10px;" class="shadow"></iframe>
          <p class="mt-3 text-muted">
            <i class="fas fa-map-marker-alt"></i> Zapallar, Región de Valparaíso, Chile
          </p>
          <p class="text-muted small">
            <i class="fas fa-compass"></i> 32°33'15.0"S 71°27'27.8"W
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- Redes Sociales -->
  <div class="social-media text-center py-4">
    <h4>¡Síguenos en nuestras redes sociales!</h4>
    <div class="social-icons d-flex justify-content-center gap-4 mt-3">
      <a href="https://www.instagram.com/cfmjoyas/"
         target="_blank" class="social-icon">
        <img src="https://upload.wikimedia.org/wikipedia/commons/9/95/Instagram_logo_2022.svg"
             alt="Instagram" width="40" height="40">
      </a>
      <a href="https://wa.me/+56998435160"
         target="_blank" class="social-icon">
        <img src="https://cdn.jsdelivr.net/npm/simple-icons@v3/icons/whatsapp.svg"
             alt="WhatsApp" width="40" height="40">
      </a>
    </div>
  </div>

  <!-- Footer -->
  <footer class="py-4 bg-dark text-center text-white">
    <div class="container">
      <div class="row">
        <div class="col-md-4">
          <h5><i class="fas fa-gem"></i> CFM Joyas</h5>
          <p class="small">Especialistas en joyas y accesorios únicos</p>
        </div>
        <div class="col-md-4">
          <h6>Categorías</h6>
          <p class="small">
            <i class="fas fa-gem"></i> Joyas<br>
            <i class="fas fa-palette"></i> Cerámicas<br>
            <i class="fas fa-star"></i> Otros Accesorios
          </p>
        </div>
        <div class="col-md-4">
          <h6>Contacto</h6>
          <p class="small">
            <i class="fab fa-whatsapp"></i> +56 9 9843 5160<br>
            <i class="fas fa-envelope"></i> cfmjoyas@gmail.com
          </p>
        </div>
      </div>
      <hr class="my-3">
      <p class="mb-0">&copy; 2025 CFM Joyas. Todos los derechos reservados.</p>
    </div>
  </footer>

  <!-- MODALES PARA FORMULARIO DE CONTACTO -->

  <!-- Modal de Éxito -->
  <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header bg-success text-white border-0">
          <h5 class="modal-title" id="successModalLabel">
            <i class="fas fa-check-circle"></i> ¡Mensaje Enviado!
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center py-4">
          <div class="mb-3">
            <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
          </div>
          <h4 class="text-success mb-3">¡Gracias por contactarnos!</h4>
          <p class="text-muted mb-3">
            Tu mensaje ha sido enviado exitosamente. Nos pondremos en contacto contigo a la brevedad.
          </p>
          <div class="alert alert-info border-0" style="background: rgba(255, 215, 0, 0.1);">
            <i class="fas fa-info-circle text-warning"></i>
            <strong>Tiempo de respuesta:</strong> 24-48 horas hábiles
          </div>
        </div>
        <div class="modal-footer border-0 justify-content-center">
          <button type="button" class="btn btn-warning px-4" data-bs-dismiss="modal">
            <i class="fas fa-gem"></i> Continuar explorando
          </button>
          <a href="https://wa.me/+56998435160" target="_blank" class="btn btn-success px-4">
            <i class="fab fa-whatsapp"></i> WhatsApp directo
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Error -->
  <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header bg-danger text-white border-0">
          <h5 class="modal-title" id="errorModalLabel">
            <i class="fas fa-exclamation-triangle"></i> Error al Enviar
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center py-4">
          <div class="mb-3">
            <i class="fas fa-times-circle text-danger" style="font-size: 4rem;"></i>
          </div>
          <h4 class="text-danger mb-3">Oops, algo salió mal</h4>
          <p class="text-muted mb-3" id="errorMessage">
            Hubo un problema al enviar tu mensaje. Por favor intenta nuevamente.
          </p>
          <div class="alert alert-warning border-0">
            <i class="fas fa-lightbulb text-warning"></i>
            <strong>Alternativa:</strong> Puedes contactarnos directamente por WhatsApp
          </div>
        </div>
        <div class="modal-footer border-0 justify-content-center">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-redo"></i> Intentar nuevamente
          </button>
          <a href="https://wa.me/+56998435160" target="_blank" class="btn btn-success">
            <i class="fab fa-whatsapp"></i> Contactar por WhatsApp
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap 5 JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Inicializar carrusel con configuraciones personalizadas
      const carousel = new bootstrap.Carousel('#mainCarousel', {
        interval: 5000,
        wrap: true,
        touch: true
      });

      // Pausar en hover para mejor experiencia de usuario
      const carouselElement = document.getElementById('mainCarousel');
      
      carouselElement.addEventListener('mouseenter', () => {
        carousel.pause();
      });
      
      carouselElement.addEventListener('mouseleave', () => {
        carousel.cycle();
      });

      // Animación de entrada para productos
      document.querySelectorAll('.product-link').forEach((el, i) => {
        el.style.animationDelay = (i * 0.1) + 's';
        el.classList.add('visible');
      });
      
      // SCROLL AUTOMÁTICO A PRODUCTOS SI HAY FILTRO APLICADO
      <?php if ($categoria_filtro): ?>
        // Si hay una categoría seleccionada, hacer scroll a productos
        setTimeout(() => {
          document.getElementById('productos').scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        }, 100);
      <?php endif; ?>
      
      // Smooth scroll para navegación con offset para navbar
      document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
          e.preventDefault();
          const targetId = this.getAttribute('href');
          const target = document.querySelector(targetId);
          if (target) {
            // Calcular posición con offset para navbar (90px)
            const targetPosition = target.offsetTop - 90;
            window.scrollTo({
              top: targetPosition,
              behavior: 'smooth'
            });
          }
        });
      });
      
      // Efecto hover para el botón de admin
      const adminBtn = document.querySelector('.admin-btn');
      adminBtn.addEventListener('mouseenter', () => {
        adminBtn.innerHTML = '<i class="fas fa-lock"></i> Acceso Seguro';
      });
      adminBtn.addEventListener('mouseleave', () => {
        adminBtn.innerHTML = '<i class="fas fa-cog"></i> Admin';
      });

      // Efecto adicional para los botones del carrusel
      document.querySelectorAll('.btn-carousel').forEach(btn => {
        btn.addEventListener('click', (e) => {
          e.preventDefault();
          
          // Efecto de pulso
          btn.style.transform = 'scale(0.95)';
          setTimeout(() => {
            btn.style.transform = 'scale(1.05)';
            setTimeout(() => {
              btn.style.transform = '';
            }, 150);
          }, 100);
          
          // Navegación real al href
          const targetId = btn.getAttribute('href');
          const target = document.querySelector(targetId);
          if (target) {
            const targetPosition = target.offsetTop - 90;
            window.scrollTo({
              top: targetPosition,
              behavior: 'smooth'
            });
          }
        });
      });

      // MOBILE MENU IMPROVEMENTS - Fix for mobile navigation
      const navbarToggler = document.querySelector('.navbar-toggler');
      const navbarCollapse = document.querySelector('#navbarNav');
      const navLinks = document.querySelectorAll('.nav-link');

      // Close menu when clicking outside
      document.addEventListener('click', function(event) {
        const isClickInsideNav = navbarCollapse.contains(event.target);
        const isClickOnToggler = navbarToggler.contains(event.target);
        
        if (!isClickInsideNav && !isClickOnToggler && navbarCollapse.classList.contains('show')) {
          const bsCollapse = new bootstrap.Collapse(navbarCollapse, {
            toggle: false
          });
          bsCollapse.hide();
        }
      });

      // Close menu when clicking on nav links
      navLinks.forEach(link => {
        link.addEventListener('click', function() {
          // Only close if menu is currently open
          if (navbarCollapse.classList.contains('show')) {
            const bsCollapse = new bootstrap.Collapse(navbarCollapse, {
              toggle: false
            });
            bsCollapse.hide();
          }
        });
      });

      // SCRIPT PARA VALIDACIONES DEL FORMULARIO DE CONTACTO
      const form = document.getElementById('contactForm');
      const nameInput = document.getElementById('name');
      const emailInput = document.getElementById('email');
      const messageInput = document.getElementById('message');
      const submitBtn = document.getElementById('submitBtn');
      const charCount = document.getElementById('charCount');
      const loadingSpinner = document.getElementById('loadingSpinner');

      // Solo ejecutar si existen los elementos
      if (form && nameInput && emailInput && messageInput) {
        
        // Contador de caracteres
        messageInput.addEventListener('input', function() {
          const count = this.value.length;
          charCount.textContent = count;
          
          if (count > 450) {
            charCount.className = 'char-danger';
          } else if (count > 400) {
            charCount.className = 'char-warning';
          } else {
            charCount.className = '';
          }
        });

        // Validación en tiempo real
        function validateField(field) {
          const value = field.value.trim();
          let isValid = true;

          // Limpiar clases previas
          field.classList.remove('is-valid', 'is-invalid');

          if (field.type === 'email') {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            isValid = emailRegex.test(value);
          } else if (field.hasAttribute('minlength')) {
            isValid = value.length >= parseInt(field.getAttribute('minlength'));
          } else {
            isValid = value.length > 0;
          }

          // Aplicar clase de validación
          if (value.length > 0) {
            field.classList.add(isValid ? 'is-valid' : 'is-invalid');
          }

          return isValid;
        }

        // Eventos de validación
        [nameInput, emailInput, messageInput].forEach(field => {
          field.addEventListener('blur', () => validateField(field));
          field.addEventListener('input', () => {
            if (field.classList.contains('is-invalid')) {
              validateField(field);
            }
          });
        });

        // Envío del formulario
        form.addEventListener('submit', function(e) {
          e.preventDefault();

          // Validar todos los campos
          const nameValid = validateField(nameInput);
          const emailValid = validateField(emailInput);
          const messageValid = validateField(messageInput);

          if (!nameValid || !emailValid || !messageValid) {
            // Enfocar el primer campo inválido
            const firstInvalid = form.querySelector('.is-invalid');
            if (firstInvalid) {
              firstInvalid.focus();
              firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return;
          }

          // Mostrar loading
          submitBtn.classList.add('loading');
          submitBtn.disabled = true;
          if (loadingSpinner) {
            loadingSpinner.style.display = 'block';
          }

          // Envío real
          const formData = new FormData(form);

          fetch('send_email.php', {
            method: 'POST',
            body: formData
          })
          .then(response => response.text())
          .then(data => {
            // Ocultar loading
            submitBtn.classList.remove('loading');
            submitBtn.disabled = false;
            if (loadingSpinner) {
              loadingSpinner.style.display = 'none';
            }

            if (data.includes('éxito') || data.includes('enviado')) {
              // Mostrar modal de éxito
              const successModal = new bootstrap.Modal(document.getElementById('successModal'));
              successModal.show();
              
              // Limpiar formulario
              form.reset();
              [nameInput, emailInput, messageInput].forEach(field => {
                field.classList.remove('is-valid', 'is-invalid');
              });
              charCount.textContent = '0';
            } else {
              // Mostrar modal de error
              document.getElementById('errorMessage').textContent = data;
              const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
              errorModal.show();
            }
          })
          .catch(error => {
            // Ocultar loading y mostrar error
            submitBtn.classList.remove('loading');
            submitBtn.disabled = false;
            if (loadingSpinner) {
              loadingSpinner.style.display = 'none';
            }
            
            document.getElementById('errorMessage').textContent = 'Error de conexión. Verifica tu internet e intenta nuevamente.';
            const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
            errorModal.show();
          });
        });
      }
    });
  </script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>