<?php
// app/Views/guias/partials/tabs.php
$currentTab = $currentTab ?? 'recorridos';
?>
<div class="nav-tabs" style="display:flex; justify-content:center; gap:2rem; margin:1.5rem 0;">
    <a href="index.php?r=guias/dashboard" 
       class="<?= $currentTab === 'recorridos' ? 'active' : '' ?>" 
       style="color:white; text-decoration:none; padding:0.8rem 1.5rem; border-radius:8px; background:#977c66; transition:all 0.3s;">
        Mis Recorridos
    </a>
    <a href="index.php?r=guias/horarios" 
       class="<?= $currentTab === 'horarios' ? 'active' : '' ?>" 
       style="color:white; text-decoration:none; padding:0.8rem 1.5rem; border-radius:8px; background:#977c66; transition:all 0.3s;">
        Mis Horarios
    </a>
</div>

<style>
    .nav-tabs a.active {
        background: #bfb641 !important;
        color: #333 !important;
        font-weight: bold;
    }
    .nav-tabs a:hover {
        background: #8c5e22;
    }
</style>