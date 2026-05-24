/**
 * BETELITE - Sportsbook Global Javascript Handlers
 */

function addToCart(predictionId) {
    if (!predictionId) return;

    // Send procedural AJAX POST payload to local PHP cart API endpoint
    $.ajax({
        url: 'api/cart.php',
        type: 'POST',
        data: {
            action: 'add',
            prediction_id: predictionId
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                // Flash message
                alert('✓ ' + response.message);
                // Update badge if exists dynamically
                let badge = $('#cart-counter-badge');
                if (badge.length > 0) {
                     let curCount = parseInt(badge.text()) || 0;
                     badge.text(curCount + 1);
                } else {
                     // Auto-reload to refresh headers on success
                     window.location.reload();
                }
            } else {
                alert('⚠️ ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX Exception in BetElite: ", error);
            // Fallback: If not authenticated or API lacks DB, prompt to login or redirect
            alert("Please make sure you are signed in to add prediction slips to your cart.");
            window.location.href = 'login.php';
        }
    });
}
