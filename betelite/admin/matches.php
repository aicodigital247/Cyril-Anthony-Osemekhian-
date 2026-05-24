<?php
/**
 * BETELITE - Matches and Pitch telemetry dashboard
 */
require_once __DIR__ . "/../includes/header.php";
require_once __DIR__ . "/../includes/navbar.php";

require_admin();

// Handler to Create a new Match
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_match') {
    verify_csrf();
    $home = sanitize($_POST['home_team']);
    $away = sanitize($_POST['away_team']);
    $league = sanitize($_POST['league']);
    $dt = sanitize($_POST['start_datetime']);
    $status = sanitize($_POST['match_status']);
    
    // Give default pitch stats for Live matches
    $poss_h = ($status === 'Live') ? 50 : 0;
    $poss_a = ($status === 'Live') ? 50 : 0;
    
    $sql = "INSERT INTO be_matches (home_team, away_team, league, start_datetime, match_status, possession_home, possession_away) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssssii", $home, $away, $league, $dt, $status, $poss_h, $poss_a);
    
    if (mysqli_stmt_execute($stmt)) {
        $msg_success = "Game created successfully!";
    } else {
        $msg_error = "Could not create dynamic football fixture.";
    }
}

// Handler to update scoreboards / events of live match
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_telemetry') {
    verify_csrf();
    $m_id = (int)$_POST['match_id'];
    $h_score = (int)$_POST['home_score'];
    $a_score = (int)$_POST['away_score'];
    $poss_h = (int)$_POST['possession_home'];
    $poss_a = (int)$_POST['possession_away'];
    $m_time = (int)$_POST['match_time'];
    $status = sanitize($_POST['match_status']);
    $comment = trim($_POST['new_comment'] ?? '');

    // Resolve commentary queues safely
    $sql_get = "SELECT live_commentary FROM be_matches WHERE id = ? LIMIT 1";
    $st_g = mysqli_prepare($conn, $sql_get);
    mysqli_stmt_bind_param($st_g, "i", $m_id);
    mysqli_stmt_execute($st_g);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($st_g));
    $commentary = json_decode($row['live_commentary'] ?? '[]', true) ?: [];

    if (!empty($comment)) {
         array_unshift($commentary, [
              'time' => $m_time ?: 1,
              'text' => $comment
         ]);
    }

    $comm_json = json_encode($commentary);

    $sql = "UPDATE be_matches SET home_score = ?, away_score = ?, possession_home = ?, possession_away = ?, match_time = ?, match_status = ?, live_commentary = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iiiiissi", $h_score, $a_score, $poss_h, $poss_a, $m_time, $status, $comm_json, $m_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $msg_success = "Fixture telemetry calibrated perfectly!";
    } else {
        $msg_error = "Failed to update match statistics.";
    }
}

// Fetch matches list
$res_matches = mysqli_query($conn, "SELECT * FROM be_matches ORDER BY start_datetime DESC");
?>

<main class="max-w-7xl mx-auto px-4 py-8 space-y-8 flex-grow">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
         <div class="space-y-1">
              <span class="text-dangerRed font-mono text-xs font-bold tracking-widest uppercase">Fixture Stadium Registry</span>
              <h1 class="font-display font-bold text-2xl md:text-3xl text-white">Active Football Matches</h1>
              <p class="text-xs text-mutedText">Initiate real-time clashes, update goal increments, add commentary flags and change pitch parameters.</p>
         </div>
         <button type="button" data-bs-toggle="modal" data-bs-target="#createMatchModal" class="px-4 py-2 bg-electricGreen hover:bg-greenHover text-darkBg text-xs font-bold rounded-xl transition-all border-none flex items-center gap-1.5 cursor-pointer shadow">
              <i data-lucide="plus-circle" class="w-4 h-4"></i> Create Fixture Clashes
         </button>
    </div>

    <?php if (isset($msg_success)): ?>
         <div class="p-3 bg-green-950/40 border border-emerald-500/40 text-electricGreen rounded-lg text-xs font-semibold">✓ <?php echo $msg_success; ?></div>
    <?php endif; ?>
    <?php if (isset($msg_error)): ?>
         <div class="p-3 bg-red-900/30 border border-red-500/40 text-dangerRed rounded-lg text-xs font-semibold">⚠️ <?php echo $msg_error; ?></div>
    <?php endif; ?>

    <!-- Matches logs -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
         <div class="lg:col-span-2 space-y-4">
              <h3 class="font-display font-medium text-sm uppercase text-white tracking-wider">Live & Upcoming Matches List</h3>
              
              <div class="space-y-4">
                   <?php if (mysqli_num_rows($res_matches) > 0): ?>
                        <?php while ($m = mysqli_fetch_assoc($res_matches)): ?>
                             <div class="glass-card p-5 space-y-4 border <?php echo ($m['match_status'] === 'Live') ? 'border-emerald-500/30 bg-emerald-500/5' : 'border-slate-800'; ?>">
                                  <div class="flex justify-between items-center bg-slate-900/40 p-2.5 rounded-lg border border-slate-850">
                                       <span class="text-xs font-bold text-slate-300 tracking-wide uppercase"><span class="text-electricGreen">⚽</span> <?php echo $m['league']; ?></span>
                                       <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold <?php echo ($m['match_status'] === 'Live') ? 'bg-emerald-500/10 text-electricGreen border border-emerald-500/20' : (($m['match_status'] === 'Upcoming') ? 'bg-blue-500/10 text-sky-400' : 'bg-slate-800 text-slate-400'); ?>">
                                            <?php echo $m['match_status']; ?> <?php echo ($m['match_status'] === 'Live') ? "({$m['match_time']}')" : ''; ?>
                                       </span>
                                  </div>

                                  <div class="flex justify-between items-center text-center font-display">
                                       <div class="w-5/12 text-sm font-bold text-white"><?php echo $m['home_team']; ?></div>
                                       <div class="w-2/12 font-mono font-black text-xl text-electricGreen">
                                            <?php echo $m['home_score']; ?> - <?php echo $m['away_score']; ?>
                                       </div>
                                       <div class="w-5/12 text-sm font-bold text-white"><?php echo $m['away_team']; ?></div>
                                  </div>

                                  <!-- Expand Calibration tools for Admin quick edits -->
                                  <div class="border-t border-slate-850 pt-3">
                                       <button type="button" data-bs-toggle="collapse" data-bs-target="#edit-panel-<?php echo $m['id']; ?>" class="w-full py-1.5 bg-slate-800 hover:bg-slate-755 text-[10px] uppercase font-bold tracking-wider text-slate-300 rounded border-none cursor-pointer text-center">
                                            ⚙ Calibrate Live Telemetry & Scoreboard
                                       </button>

                                       <!-- Collapse section of telemetry parameters -->
                                       <div class="collapse pt-4" id="edit-panel-<?php echo $m['id']; ?>">
                                            <form action="matches.php" method="POST" class="space-y-3">
                                                 <?php echo csrf_field(); ?>
                                                 <input type="hidden" name="action" value="update_telemetry">
                                                 <input type="hidden" name="match_id" value="<?php echo $m['id']; ?>">
                                                 
                                                 <div class="grid grid-cols-3 gap-2">
                                                      <div>
                                                           <label class="block text-[10px] text-mutedText font-bold uppercase mb-1">H-Score</label>
                                                           <input type="number" name="home_score" class="form-control bg-slate-950 border border-slate-800 font-mono text-center text-xs p-1 rounded text-white" value="<?php echo $m['home_score']; ?>">
                                                      </div>
                                                      <div>
                                                           <label class="block text-[10px] text-mutedText font-bold uppercase mb-1">A-Score</label>
                                                           <input type="number" name="away_score" class="form-control bg-slate-950 border border-slate-800 font-mono text-center text-xs p-1 rounded text-white" value="<?php echo $m['away_score']; ?>">
                                                      </div>
                                                      <div>
                                                           <label class="block text-[10px] text-mutedText font-bold uppercase mb-1">Game Min</label>
                                                           <input type="number" name="match_time" class="form-control bg-slate-950 border border-slate-800 font-mono text-center text-xs p-1 rounded text-white" value="<?php echo $m['match_time']; ?>">
                                                      </div>
                                                 </div>

                                                 <div class="grid grid-cols-3 gap-2">
                                                      <div>
                                                           <label class="block text-[10px] text-mutedText font-bold uppercase mb-1">Possession H%</label>
                                                           <input type="number" name="possession_home" class="form-control bg-slate-950 border border-slate-800 font-mono text-center text-xs p-1 rounded text-white" value="<?php echo $m['possession_home']; ?>">
                                                      </div>
                                                      <div>
                                                           <label class="block text-[10px] text-mutedText font-bold uppercase mb-1">Possession A%</label>
                                                           <input type="number" name="possession_away" class="form-control bg-slate-950 border border-slate-800 font-mono text-center text-xs p-1 rounded text-white" value="<?php echo $m['possession_away']; ?>">
                                                      </div>
                                                      <div>
                                                           <label class="block text-[10px] text-mutedText font-bold uppercase mb-1">Status</label>
                                                           <select name="match_status" class="bg-slate-955 border border-slate-800 text-[10px] py-1 px-1.5 rounded text-white font-bold cursor-pointer w-full">
                                                                <option value="Upcoming" <?php echo ($m['match_status'] === 'Upcoming') ? 'selected' : ''; ?>>Upcoming</option>
                                                                <option value="Live" <?php echo ($m['match_status'] === 'Live') ? 'selected' : ''; ?>>Live Match</option>
                                                                <option value="Completed" <?php echo ($m['match_status'] === 'Completed') ? 'selected' : ''; ?>>Completed</option>
                                                           </select>
                                                      </div>
                                                 </div>

                                                 <div>
                                                      <label class="block text-[10px] text-mutedText font-bold uppercase mb-1">Append Commentary Event</label>
                                                      <input type="text" name="new_comment" class="form-control bg-slate-950 border border-slate-800 text-xs p-2 rounded text-white" placeholder="Manchester United counters down the right side...">
                                                 </div>

                                                 <button type="submit" class="w-full py-1.5 bg-electricGreen hover:bg-greenHover text-darkBg text-xs font-bold rounded cursor-pointer border-none shadow">
                                                      Commit Telemetry Live Updates
                                                 </button>
                                            </form>
                                       </div>
                                  </div>
                             </div>
                        <?php endwhile; ?>
                   <?php else: ?>
                        <div class="glass-card p-12 text-center text-mutedText">Fixture stadium is empty. Create a soccer clash to start.</div>
                   <?php endif; ?>
              </div>
         </div>

         <!-- Info Board -->
         <div class="space-y-4 text-xs text-mutedText">
              <h3 class="font-display font-medium text-sm text-white uppercase tracking-wider">Commentary Simulator</h3>
              <div class="glass-card p-5 space-y-3 leading-relaxed">
                   <p class="font-bold text-white">How Commentary updates sync live:</p>
                   <p>When you append comments onto a Live game, users polling `/live.php` or checking `/api/live.php` will immediately see your commentary updates at the top of the queue.</p>
                   <p>Setting status to <strong>Completed</strong> halts simulation clocks immediately and releases predictions for evaluation.</p>
              </div>
         </div>
    </div>

</main>

<!-- Create Match Modal -->
<div class="modal fade" id="createMatchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content bg-slate-900 border border-slate-800 text-white rounded-2xl animate-fade-in">
              <div class="modal-header border-slate-800">
                   <h5 class="modal-title font-display font-bold text-sm uppercase flex items-center gap-2">
                        <span class="text-electricGreen">⚽</span> Create New Sports Clash
                   </h5>
                   <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <form action="matches.php" method="POST">
                   <?php echo csrf_field(); ?>
                   <input type="hidden" name="action" value="create_match">
                   <div class="modal-body space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                             <div>
                                  <label class="block text-[10px] text-mutedText font-semibold uppercase mb-1">Home Team</label>
                                  <input type="text" name="home_team" class="form-control glass-input" placeholder="Chelsea" required>
                             </div>
                             <div>
                                  <label class="block text-[10px] text-mutedText font-semibold uppercase mb-1">Away Team</label>
                                  <input type="text" name="away_team" class="form-control glass-input" placeholder="Arsenal" required>
                             </div>
                        </div>

                        <div>
                             <label class="block text-[10px] text-mutedText font-semibold uppercase mb-1">League / Tournament</label>
                             <input type="text" name="league" class="form-control glass-input" placeholder="Premier League" required>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                             <div>
                                  <label class="block text-[10px] text-mutedText font-semibold uppercase mb-1">Start Datetime</label>
                                  <input type="datetime-local" name="start_datetime" class="form-control glass-input" required value="<?php echo date('Y-m-d\TH:i'); ?>">
                             </div>
                             <div>
                                  <label class="block text-[10px] text-mutedText font-semibold uppercase mb-1">Match Init Status</label>
                                  <select name="match_status" class="form-control glass-input cursor-pointer font-semibold">
                                       <option value="Upcoming">Upcoming Match</option>
                                       <option value="Live">Start Live Instantly</option>
                                  </select>
                             </div>
                        </div>
                   </div>
                   <div class="modal-footer border-slate-800">
                        <button type="submit" class="w-full py-2.5 bg-electricGreen hover:bg-greenHover text-darkBg text-xs font-bold rounded-xl border-none shadow">
                             Assemble Sports Fixture
                        </button>
                   </div>
              </form>
         </div>
    </div>
</div>

<?php
require_once __DIR__ . "/../includes/footer.php";
?>
