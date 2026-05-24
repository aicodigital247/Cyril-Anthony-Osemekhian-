<?php
/**
 * BETELITE - Live Match center & commentaries
 */
require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/navbar.php";

$sql = "SELECT * FROM be_matches WHERE match_status = 'Live' LIMIT 1";
$res = mysqli_query($conn, $sql);
$live_game = mysqli_fetch_assoc($res);
?>

<main class="max-w-7xl mx-auto px-4 py-8 space-y-8 flex-grow">
    
    <div class="space-y-2">
         <span class="inline-flex items-center gap-1.5 text-xs text-electricGreen font-bold font-mono tracking-widest"><span class="live-pulse"></span> ALIVE SCORECENTER</span>
         <h1 class="font-display font-bold text-2xl md:text-3xl text-white">Interactive Live Games Stadium</h1>
         <p class="text-xs text-mutedText">Instant statistical momentum logs, possession bars, shots grids and automated match outcomes updated directly from pitch trackers.</p>
    </div>

    <?php if ($live_game): ?>
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-8">
             <!-- Left side - Possession, Shots, card metrics (Large column) -->
             <div class="lg:col-span-2 space-y-6">
                 <!-- Main Scoreboard Banner -->
                 <div class="glass-card p-6 bg-gradient-to-r from-slate-900 to-slate-950 border border-slate-800 flex flex-col items-center">
                     <span class="text-xs text-electricGreen font-semibold uppercase tracking-widest mb-4 flex items-center gap-1.5 px-3 py-1 bg-emerald-500/10 rounded-full border border-emerald-500/20">
                          <span class="live-pulse"></span> Live Commentary Playing
                     </span>
                     
                     <!-- Teams & score board -->
                     <div class="w-full flex justify-between items-center py-4">
                          <!-- Home Team -->
                          <div class="w-5/12 text-center space-y-2">
                               <div class="h-14 w-14 bg-slate-800 rounded-full border border-slate-700 flex items-center justify-center mx-auto text-white text-xl font-bold font-display">
                                    <?php echo substr($live_game['home_team'], 0, 2); ?>
                                </div>
                               <h3 class="font-display font-bold text-sm md:text-base text-white truncate mt-2"><?php echo $live_game['home_team']; ?></h3>
                          </div>
                          
                          <!-- Scores -->
                          <div class="w-2/12 text-center text-3xl font-mono font-bold text-electricGreen flex flex-col items-center justify-center bg-slate-950 border border-slate-800 rounded-2xl py-3 px-4">
                               <div class="flex items-center gap-2">
                                   <span id="live-home-score"><?php echo $live_game['home_score']; ?></span>
                                   <span class="text-mutedText/45">:</span>
                                   <span id="live-away-score"><?php echo $live_game['away_score']; ?></span>
                               </div>
                               <span class="text-[10px] text-mutedText mt-1.5 block uppercase tracking-wider font-sans bg-slate-800 px-2 py-0.5 rounded-full font-bold">
                                    <span id="live-match-timer"><?php echo $live_game['match_time']; ?></span>' MIN
                               </span>
                          </div>

                          <!-- Away Team -->
                          <div class="w-5/12 text-center space-y-2">
                               <div class="h-14 w-14 bg-slate-800 rounded-full border border-slate-700 flex items-center justify-center mx-auto text-white text-xl font-bold font-display">
                                    <?php echo substr($live_game['away_team'], 0, 2); ?>
                               </div>
                               <h3 class="font-display font-bold text-sm md:text-base text-white truncate mt-2"><?php echo $live_game['away_team']; ?></h3>
                          </div>
                     </div>
                 </div>

                 <!-- Physical Match progress parameters -->
                 <div class="glass-card p-6 space-y-5">
                      <h4 class="font-display font-bold text-sm text-white uppercase tracking-wider flex items-center gap-2">
                           <i data-lucide="bar-chart-2" class="text-electricGreen w-4 h-4"></i> Pitch Analytics Indicators
                      </h4>

                      <!-- Possession -->
                      <div class="space-y-1">
                          <div class="flex justify-between text-xs text-slate-300">
                               <span>Ball Possession</span>
                               <span class="font-mono text-electricGreen"><?php echo $live_game['possession_home']; ?>% - <?php echo $live_game['possession_away']; ?>%</span>
                          </div>
                          <div class="w-full bg-slate-850 h-2 rounded-full overflow-hidden flex border border-slate-800">
                               <div class="bg-electricGreen h-full transition-all duration-1000" style="width: <?php echo $live_game['possession_home']; ?>%;"></div>
                               <div class="bg-slate-700 h-full transition-all duration-1000" style="width: <?php echo $live_game['possession_away']; ?>%;"></div>
                          </div>
                      </div>

                      <!-- Grid for corners, red cards, shots on goal -->
                      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-3 text-center">
                           <div class="bg-darkSec border border-borderSl p-3 rounded-xl">
                                <p class="text-[10px] text-mutedText uppercase tracking-wider font-bold">Shots on Target</p>
                                <p class="text-lg font-mono font-bold text-white mt-1"><?php echo $live_game['shots_home']; ?> v <?php echo $live_game['shots_away']; ?></p>
                           </div>
                           <div class="bg-darkSec border border-borderSl p-3 rounded-xl">
                                <p class="text-[10px] text-mutedText uppercase tracking-wider font-bold">Corners</p>
                                <p class="text-lg font-mono font-bold text-white mt-1"><?php echo $live_game['corners_home']; ?> v <?php echo $live_game['corners_away']; ?></p>
                           </div>
                           <div class="bg-darkSec border border-borderSl p-3 rounded-xl">
                                <p class="text-[10px] text-mutedText uppercase tracking-wider font-bold">Yellow Cards</p>
                                <p class="text-lg font-mono font-bold text-amber-500 mt-1"><?php echo $live_game['cards_yellow_home']; ?> v <?php echo $live_game['cards_yellow_away']; ?></p>
                           </div>
                           <div class="bg-darkSec border border-borderSl p-3 rounded-xl">
                                <p class="text-[10px] text-mutedText uppercase tracking-wider font-bold">Red Cards</p>
                                <p class="text-lg font-mono font-bold text-dangerRed mt-1"><?php echo $live_game['cards_red_home']; ?> v <?php echo $live_game['cards_red_away']; ?></p>
                           </div>
                      </div>
                 </div>
             </div>

             <!-- Right column - Live commentary feed (AJAX updates) -->
             <div class="space-y-4">
                  <div class="glass-card p-4 h-[440px] flex flex-col justify-between">
                       <div>
                            <div class="flex justify-between items-center border-b border-slate-800 pb-3 mb-3">
                                 <h4 class="font-display font-bold text-sm text-white uppercase tracking-wider flex items-center gap-2">
                                      <i data-lucide="message-square-text" class="text-electricGreen w-4 h-4 animate-bounce"></i> Match Commentary Queue
                                 </h4>
                                 <span class="text-[9px] bg-emerald-500/10 border border-emerald-500/20 text-electricGreen px-2 py-0.5 rounded-full font-mono uppercase font-bold">Poll: active</span>
                            </div>
                            <!-- Feed block scrollable -->
                            <div id="commentary-list-box" class="space-y-4 h-[320px] overflow-y-auto pr-2 text-xs">
                                 <?php 
                                    $comments = json_decode($live_game['live_commentary'], true) ?: [];
                                    if (!empty($comments)):
                                        foreach($comments as $idx => $comm):
                                 ?>
                                     <div class="flex gap-2.5 pb-2.5 border-b border-slate-900 last:border-0 <?php echo ($idx === 0) ? 'text-electricGreen pt-1' : 'text-slate-300'; ?>">
                                          <div class="font-mono font-bold bg-slate-800 text-slate-300 w-9 h-6 rounded flex items-center justify-center border border-slate-700/60"><?php echo $comm['time']; ?>'</div>
                                          <div class="leading-relaxed"><?php echo $comm['text']; ?></div>
                                     </div>
                                 <?php 
                                        endforeach;
                                    else:
                                 ?>
                                     <div class="text-center text-mutedText py-12">Match officials are adjusting pitches. Standby for active commentary.</div>
                                 <?php endif; ?>
                            </div>
                       </div>
                  </div>
             </div>
        </section>
    <?php else: ?>
        <!-- Default Live fallback block if DB is empty -->
        <section class="glass-card p-12 text-center max-w-2xl mx-auto text-mutedText space-y-4">
             <i data-lucide="frown" class="w-16 h-16 text-mutedText opacity-30 mx-auto"></i>
             <h3 class="font-display font-semibold text-white">No Matches Playing Right Now</h3>
             <p class="text-xs">There are no soccer games actively monitored at the moment. Check the premium archives or register predictors to formulate VIP weekend predictions.</p>
             <a href="index.php" class="inline-block py-2 px-6 bg-slate-800 hover:bg-slate-7 py-2.5 rounded-xl text-xs text-white no-underline text-center">Back to Football Home</a>
        </section>
    <?php endif; ?>

</main>

<!-- In-page client scripts to simulate polling in active matches -->
<script>
$(document).ready(function() {
    // If a live match is on, simulate progress & scoring increments asynchronously!
    <?php if ($live_game): ?>
    let game_time = <?php echo $live_game['match_time']; ?>;
    
    // Quick polling interval
    setInterval(function() {
        if (game_time < 90) {
            game_time += 1;
            $('#live-match-timer').text(game_time);
            
            // Random events simulator
            if (Math.random() > 0.85) {
                // Flash event on commentator log
                let possibleEvents = [
                    "Near-miss! Yellow card cautioned for holding tactical defense layers.",
                    "Shot flies headers! Manchester United counters pacing strikers down line",
                    "Corner awarded. Goal goalie safely collects high looping cross",
                    "Offside flag halts potential goal break cleanly evaluated by linesman."
                ];
                let evText = possibleEvents[Math.floor(Math.random() * possibleEvents.length)];
                
                let commentHtml = `
                     <div class="flex gap-2.5 pb-2.5 border-b border-slate-900 text-electricGreen animate-pulse">
                          <div class="font-mono font-bold bg-slate-800 text-slate-300 w-9 h-6 rounded flex items-center justify-center border border-slate-700/60">${game_time}'</div>
                          <div class="leading-relaxed">${evText}</div>
                     </div>
                `;
                $('#commentary-list-box').prepend(commentHtml);
            }
        }
    }, 15000); // Trigger comment generation mock
    <?php endif; ?>
});
</script>

<?php
require_once __DIR__ . "/includes/footer.php";
?>
