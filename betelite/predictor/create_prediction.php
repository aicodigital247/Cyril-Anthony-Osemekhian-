<?php
/**
 * BETELITE - Predictor Ticket compiler workbook
 */
require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/../includes/navbar.php";

require_predictor();

// Fetch matches currently scheduling
$sql = "SELECT * FROM be_matches WHERE match_status = 'Upcoming' ORDER BY start_datetime ASC";
$res_matches = mysqli_query($conn, $sql);
?>

<main class="max-w-4xl mx-auto px-4 py-8 space-y-8 flex-grow">
    
    <div class="space-y-1">
         <span class="text-vipGold font-mono text-xs font-bold tracking-widest uppercase">Expert Compiling Desk</span>
         <h1 class="font-display font-bold text-2xl md:text-3xl text-white">Assemble Premium Betting Slips</h1>
         <p class="text-xs text-mutedText">Select scheduled fixtures, define analytical market predictions, calibrate odds and establish access prices (set 0.00 for FREE evaluation slips).</p>
    </div>

    <!-- Compile form -->
    <form id="form-compile-slip" class="space-y-6">
         <?php echo csrf_field(); ?>
         
         <div class="glass-card p-6 space-y-4">
              <h3 class="font-display font-semibold text-sm text-white uppercase tracking-wider">1. Ticket Metadata Properties</h3>
              
              <div>
                   <label class="block text-xs font-semibold uppercase text-mutedText mb-2">Slip Title / Tagline</label>
                   <input type="text" id="slip-title" class="form-control glass-input" placeholder="Weekend Premier League 10X Accumulator Slate" required>
              </div>

              <div>
                   <label class="block text-xs font-semibold uppercase text-mutedText mb-2">Expert Tactical Analysis / Description</label>
                   <textarea id="slip-desc" class="form-control glass-input h-24" placeholder="Deep analytics over home physical defensive structures and tactical overlays. Manchester United has key lineups returning..."></textarea>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                   <div>
                        <label class="block text-xs font-semibold uppercase text-mutedText mb-2">Price Access Fee (₦)</label>
                        <input type="number" id="slip-price" class="form-control glass-input font-mono" value="0" min="0" placeholder="0 for Free">
                        <span class="text-[9px] text-mutedText block mt-1">Set as 0 to broadcast for FREE.</span>
                   </div>
                   <div>
                        <label class="block text-xs font-semibold uppercase text-mutedText mb-2">Compiler Confidence Ratio (%)</label>
                        <input type="number" id="slip-confidence" class="form-control glass-input font-mono" value="85" min="40" max="99">
                   </div>
                   <div>
                        <label class="block text-xs font-semibold uppercase text-mutedText mb-2">Sports Discipline Category</label>
                        <input type="text" class="form-control glass-input" value="Football Soccer" disabled>
                   </div>
              </div>
         </div>

         <!-- Selections compiler -->
         <div class="glass-card p-6 space-y-4">
              <h3 class="font-display font-semibold text-sm text-white uppercase tracking-wider">2. Forecast Selections Ledger</h3>
              <p class="text-[10px] text-mutedText">Add selections by selecting scheduled matches from the stadium lists below:</p>

              <!-- Single selection template widget -->
              <div id="selections-box" class="space-y-3">
                   <!-- Rows added dynamically -->
              </div>

              <!-- Match Adding selector -->
              <div class="bg-slate-950 p-4 rounded-xl border border-slate-850 flex flex-col md:flex-row gap-3 items-end">
                   <div class="flex-grow">
                        <label class="block text-xs font-bold uppercase text-mutedText mb-1.5">Select Scheduled Fixture Clash</label>
                        <select id="select-active-match" class="form-control glass-input bg-slate-900 cursor-pointer text-xs font-bold">
                             <?php if (mysqli_num_rows($res_matches) > 0): ?>
                                  <?php while ($m = mysqli_fetch_assoc($res_matches)): ?>
                                       <option value="<?php echo $m['id']; ?>" data-home="<?php echo $m['home_team']; ?>" data-away="<?php echo $m['away_team']; ?>">
                                            <?php echo $m['home_team']; ?> v <?php echo $m['away_team']; ?> (<?php echo $m['league']; ?>)
                                       </option>
                                  <?php endwhile; ?>
                             <?php else: ?>
                                  <option value="">No upcoming fixtures scheduling. Create fixtures in administration panel first.</option>
                             <?php endif; ?>
                        </select>
                   </div>
                   <div class="w-full md:w-44">
                        <label class="block text-xs font-bold uppercase text-mutedText mb-1.5">Betting Market Choice</label>
                        <input type="text" id="choice-market" class="form-control glass-input text-xs" placeholder="Home Win (Away Win/Over 2.5)">
                   </div>
                   <div class="w-full md:w-24">
                        <label class="block text-xs font-bold uppercase text-mutedText mb-1.5">Odds Valuation</label>
                        <input type="number" id="choice-odds" class="form-control glass-input text-xs font-mono" placeholder="2.15" step="0.01">
                   </div>
                   <button type="button" onclick="addSelectionRow()" class="px-4 py-2.5 bg-slate-800 text-white rounded-lg border border-slate-700/60 hover:bg-slate-700 text-xs font-bold whitespace-nowrap inline-flex items-center gap-1.5 border-none cursor-pointer">
                        <i data-lucide="plus" class="w-4 h-4 text-electricGreen"></i> Add Selection
                   </button>
              </div>
         </div>

         <!-- Trigger -->
         <div class="flex justify-end pt-4">
              <button type="button" onclick="submitCompiledSlip()" class="px-6 py-2.5 bg-electricGreen hover:bg-greenHover text-darkBg text-xs font-bold rounded-xl border-none shadow cursor-pointer transition-all flex items-center gap-2">
                   <i data-lucide="sparkles" class="w-4 h-4"></i> Publish Compiled Betting Ticket
              </button>
         </div>
    </form>

</main>

<script>
let selectedMarkets = [];

function addSelectionRow() {
    let matchSelect = $('#select-active-match');
    let matchId = matchSelect.val();
    if (!matchId) {
         alert('Please select matching schedulings first.');
         return;
    }

    let matchOption = matchSelect.find('option:selected');
    let home = matchOption.attr('data-home');
    let away = matchOption.attr('data-away');
    let market = $('#choice-market').val().trim();
    let odds = parseFloat($('#choice-odds').val()) || 1.50;

    if (!market) {
         alert('Please provide forecasting market parameters (e.g. Home Clean Sheet).');
         return;
    }

    // Insert into lists array
    let selectionObj = {
         match_id: matchId,
         home: home,
         away: away,
         market: market,
         odds: odds
    };

    selectedMarkets.push(selectionObj);
    renderRows();

    // Reset fields
    $('#choice-market').val('');
    $('#choice-odds').val('');
}

function removeSelectionRow(idx) {
    selectedMarkets.splice(idx, 1);
    renderRows();
}

function renderRows() {
    let box = $('#selections-box');
    box.empty();

    if (selectedMarkets.length === 0) {
         box.append('<p class="text-center py-6 text-mutedText text-xs italic">No match forecast selections integrated. Construct rows above.</p>');
         return;
    }

    selectedMarkets.forEach((sel, idx) => {
         let rowHtml = `
              <div class="glass-card p-4 flex justify-between items-center gap-4 bg-slate-900/30 border border-slate-850/60">
                   <div class="space-y-1">
                        <span class="text-[9px] text-mutedText uppercase font-bold font-mono">SELECTION #${idx + 1}</span>
                        <h4 class="text-xs font-bold text-white">${sel.home} v ${sel.away}</h4>
                        <p class="text-xs font-semibold text-electricGreen">Market Choice: ${sel.market}</p>
                   </div>
                   <div class="flex items-center gap-4">
                        <span class="font-mono text-sm font-black text-white">Odds: @${sel.odds.toFixed(2)}</span>
                        <button type="button" onclick="removeSelectionRow(${idx})" class="p-1 px-2.5 bg-red-955 text-red-400 rounded hover:bg-red-900 hover:text-white text-[10px] font-bold cursor-pointer transition-all border border-red-900/40">
                             Remove
                        </button>
                   </div>
              </div>
         `;
         box.append(rowHtml);
    });
}

function submitCompiledSlip() {
    let title = $('#slip-title').val().trim();
    let desc = $('#slip-desc').val().trim();
    let price = parseFloat($('#slip-price').val()) || 0.00;
    let confidence = parseInt($('#slip-confidence').val()) || 85;

    if (!title) {
         alert('Headline Title field required.');
         return;
    }

    if (selectedMarkets.length === 0) {
         alert('Betting slip must bundle at least one forecasting selection clash.');
         return;
    }

    $.ajax({
        url: '<?php echo BASE_URL; ?>api/predictions.php',
        type: 'POST',
        data: JSON.stringify({
             title: title,
             description: desc,
             price: price,
              confidence: confidence,
             selections: selectedMarkets,
             csrf_token: '<?php echo $_SESSION['csrf_token'] ?? ''; ?>'
        }),
        contentType: 'application/json',
        dataType: 'json',
        success: function(resp) {
             if (resp.status === 'success') {
                  alert('✓ Expert slip published successfully onto BetElite directorate!');
                  window.location.href = 'dashboard.php';
             } else {
                  alert('⚠️ ' + resp.message);
             }
        },
        error: function(xhr, status, error) {
             console.error("Compile Fail: ", error);
             alert('Network exception compiling betting slips.');
        }
    });
}

// Render empty block initially
renderRows();
</script>

<?php
require_once __DIR__ . "/../includes/footer.php";
?>
