<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $__env->yieldContent('title', 'Reporte PDF - MaxPowerGym'); ?></title>
    <style>
        @font-face {
            font-family: 'Poppins';
            src: url('<?php echo e(public_path("fonts/Poppins-Regular.ttf")); ?>') format('truetype');
        }
        body {
            font-family: 'Poppins', sans-serif;
            font-size: 12px;
            margin: 40px;
            color: #000;
        }
        header {
            text-align: center;
            margin-bottom: 20px;
        }
        header img {
            width: 150px;
        }
        h1, h2, h3 {
            color: #FF6600;
            margin-bottom: 5px;
        }
        .section-title {
            background-color: #FF6600;
            color: white;
            padding: 5px 10px;
            margin-top: 20px;
            font-weight: bold;
        }
        .content {
            margin-top: 10px;
        }
        .content table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .content th, .content td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .content th {
            background-color: #f2f2f2;
            color: #333;
        }
        footer {
            text-align: center;
            position: fixed;
            bottom: 20px;
            left: 0;
            right: 0;
            font-size: 11px;
            color: #4E5054;
        }
        .frase-final {
            margin-top: 30px;
            font-size: 14px;
            text-align: center;
            font-weight: bold;
            color: #4E5054;
        }
    </style>
</head>
<body>
    <header>
        <img src="<?php echo e(public_path('images/logotipo.png')); ?>" alt="Logo MaxPowerGym">
        <h2>MAXPOWERGYM</h2>
        <p><strong>Reporte Institucional</strong></p>
    </header>

    <main class="content">
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <div class="frase-final">
        ¡Acepta el desafío, rompe los límites!
    </div>

    <footer>
        MaxPowerGym - Sistema desarrollado para gestión interna
    </footer>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Laravel\Proyecto_MPGFil\resources\views/pdf/ejemplo.blade.php ENDPATH**/ ?>