<?php
/**
 * BETELITE - Standard Bootstrap Modals
 */
?>
<!-- Quick Deposit Modal -->
<div class="modal fade" id="depositModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-slate-900 border border-slate-800 text-white rounded-2xl">
            <div class="modal-header border-slate-800">
                <h5 class="modal-title font-display font-bold flex items-center gap-2">
                    <i data-lucide="plus-circle" class="text-electricGreen"></i> Fund BETELITE Wallet
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="api/payments.php" method="POST" id="form-quick-deposit">
                <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
                <div class="modal-body space-y-4">
                    <p class="text-xs text-mutedText">Instant deposition supported via Paystack or Flutterwave secure NGN gateways.</p>
                    
                    <div>
                        <label class="block text-xs font-semibold uppercase text-mutedText mb-2">Select Amount</label>
                        <div class="grid grid-cols-4 gap-2">
                            <button type="button" onclick="setDepositAmount(1000)" class="btn btn-outline-secondary btn-sm border-slate-700 py-2 font-mono text-white hover:bg-slate-800">₦1K</button>
                            <button type="button" onclick="setDepositAmount(5000)" class="btn btn-outline-secondary btn-sm border-slate-700 py-2 font-mono text-white hover:bg-slate-800">₦5K</button>
                            <button type="button" onclick="setDepositAmount(10000)" class="btn btn-outline-secondary btn-sm border-slate-700 py-2 font-mono text-white hover:bg-slate-800">₦10K</button>
                            <button type="button" onclick="setDepositAmount(25000)" class="btn btn-outline-secondary btn-sm border-slate-700 py-2 font-mono text-white hover:bg-slate-800">₦25K</button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-mutedText mb-1">Custom Amount (₦)</label>
                        <input type="number" name="amount" id="deposit-custom-amount" class="form-control glass-input w-full" placeholder="Min 100 NGN" min="100" required>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase text-mutedText mb-1">Select Gateway</label>
                        <select name="gateway" class="form-control glass-input w-full">
                            <option value="Paystack">Paystack Naira (Instant card/transfer)</option>
                            <option value="Flutterwave">Flutterwave Mobile Money/Core</option>
                            <option value="Crypto">USDT TRC20 Gateway (Auto-converted)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-slate-800">
                    <button type="type" class="w-full py-2.5 bg-electricGreen hover:bg-greenHover text-darkBg font-semibold rounded-xl text-center transition-all">
                        Proceed to Secure Gateway
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function setDepositAmount(val) {
    document.getElementById('deposit-custom-amount').value = val;
}
</script>
