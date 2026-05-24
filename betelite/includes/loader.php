<?php
/**
 * BETELITE - Page Preloader HUD
 */
?>
<div id="betelite-preloader" class="fixed inset-0 z-50 bg-darkBg flex flex-col items-center justify-center transition-all duration-500 opacity-100 pointer-events-auto">
    <div class="relative flex flex-col items-center space-y-4">
         <!-- Pulsing circular ring -->
         <div class="relative w-16 h-16">
              <div class="absolute inset-0 rounded-full border-4 border-slate-800"></div>
              <div class="absolute inset-0 rounded-full border-4 border-t-electricGreen animate-spin"></div>
         </div>
         
         <!-- Glowing logotype -->
         <div class="text-center">
              <h2 class="font-display font-bold text-lg text-white tracking-widest flex items-center gap-1.5 justify-center">
                   <span class="text-electricGreen text-sm">🏆</span> BET<span class="text-electricGreen">ELITE</span>
              </h2>
              <p class="text-[10px] text-mutedText uppercase font-mono tracking-wider mt-1">Verifying Secure Session</p>
         </div>
    </div>
</div>

<script>
// Hide loading curtain after document triggers fully interactive loading
window.addEventListener('load', function() {
    const preloader = document.getElementById('betelite-preloader');
    if (preloader) {
        preloader.classList.add('opacity-0');
        preloader.classList.add('pointer-events-none');
        setTimeout(() => {
            preloader.className = 'hidden';
        }, 500);
    }
});
</script>
