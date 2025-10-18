<div style="
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 6px;
    background: <?php echo e(app()->isProduction() ? '#2c2c2c' : 'linear-gradient(90deg, #1a1a1a 0%, #2c2c2c 100%)'); ?>;
    border-left: 4px solid #FF6600;
    border-radius: 10px;
    padding: 12px 16px;
    color: #f5f5f5;
    font-family: 'Poppins', sans-serif;
    font-size: 0.95rem;
    box-shadow: 0 0 6px rgba(255, 102, 0, 0.3);
">
    <div style="display: flex; align-items: center; gap: 8px;">
        <span style="font-size: 1.2rem;">🏋️‍♂️</span>
        <div>
            <strong style="color: #FF6600;">Total pagado:</strong>
            <span style="color: #22c55e; font-weight: bold;">
                Bs. <?php echo e(number_format($total, 2, ',', '.')); ?>

            </span>
        </div>
    </div>

    <div style="display: flex; align-items: center; gap: 8px;">
        <span style="font-size: 1.2rem;">📅</span>
        <div>
            <strong style="color: #FF6600;">Último pago:</strong>
            <span style="color: #60a5fa; font-weight: bold;">
                <?php echo e($ultimo ? \Carbon\Carbon::parse($ultimo)->format('d/m/Y') : 'N/A'); ?>

            </span>
        </div>
    </div>

    <div style="margin-top: 6px; text-align: right; font-size: 0.8rem; opacity: 0.7;">
        💪 ¡Acepta el desafío, rompe los límites!
    </div>
</div><?php /**PATH C:\xampp\htdocs\Laravel\Proyecto_MPGFil\resources\views/components/footer-pagos-inline.blade.php ENDPATH**/ ?>