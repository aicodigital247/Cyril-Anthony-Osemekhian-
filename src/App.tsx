import React, { useState, useEffect } from 'react';
import { 
  Award, 
  Activity, 
  Wallet, 
  Sparkles, 
  ShoppingCart, 
  User, 
  ShieldAlert, 
  Lock, 
  Plus, 
  Trash2, 
  CheckCircle, 
  Info, 
  History, 
  Home, 
  TrendingUp, 
  PlusCircle, 
  ArrowUpRight, 
  LogOut, 
  LayoutDashboard, 
  Check, 
  Send,
  Sliders,
  Bell,
  Search,
  CheckCircle2,
  LockKeyhole
} from 'lucide-react';

// Static / Initial Seeded Mock Data representing the database logs in MySQL
const INITIAL_MATCHES = [
  {
    id: 1,
    sport: 'Football',
    league: 'Premier League',
    home_team: 'Arsenal',
    away_team: 'Manchester United',
    home_score: 2,
    away_score: 1,
    match_time: 74,
    match_status: 'Live',
    possession_home: 58,
    possession_away: 42,
    shots_home: 12,
    shots_away: 7,
    corners_home: 6,
    corners_away: 4,
    cards_yellow_home: 2,
    cards_yellow_away: 1,
    cards_red_home: 0,
    cards_red_away: 0,
    live_commentary: [
      { time: 70, text: "GOAL! Martin Odegaard scores a beautiful curler from the edge of the box! 2-1 Assist by Bukayo Saka." },
      { time: 45, text: "Halftime: Controlled show by Arsenal, Manchester United hitting well on counter attacks." },
      { time: 12, text: "GOAL! Marcus Rashford scores on a counter, beautiful low finish past Raya! 0-1" }
    ]
  },
  {
    id: 2,
    sport: 'Football',
    league: 'La Liga',
    home_team: 'Real Madrid',
    away_team: 'Barcelona',
    home_score: 0,
    away_score: 0,
    match_time: 0,
    match_status: 'Upcoming',
    possession_home: 50,
    possession_away: 50,
    shots_home: 0,
    shots_away: 0,
    corners_home: 0,
    corners_away: 0,
    cards_yellow_home: 0,
    cards_yellow_away: 0,
    cards_red_home: 0,
    cards_red_away: 0,
    live_commentary: []
  },
  {
    id: 3,
    sport: 'Football',
    league: 'Serie A',
    home_team: 'Juventus',
    away_team: 'AC Milan',
    home_score: 1,
    away_score: 1,
    match_time: 90,
    match_status: 'Completed',
    possession_home: 48,
    possession_away: 52,
    shots_home: 9,
    shots_away: 11,
    corners_home: 3,
    corners_away: 5,
    cards_yellow_home: 1,
    cards_yellow_away: 3,
    cards_red_home: 0,
    cards_red_away: 0,
    live_commentary: [
      { time: 90, text: "Fulltime whistles: Juve 1, Milan 1. Shared spoils in a gritty affair." },
      { time: 82, text: "GOAL! Rafael Leao hammers it off the underside of the crossbar! Milan levels! 1-1" },
      { time: 33, text: "GOAL! Vlahovic converts the cross to draw first blood! 1-0" }
    ]
  }
];

const INITIAL_PREDICTIONS = [
  {
    id: 1,
    predictor_name: 'KingTips (Baba Kola)',
    predictor_avatar: 'https://api.dicebear.com/7.x/pixel-art/svg?seed=KingTips',
    predictor_accuracy: '84.50',
    title: 'Weekend Goal Rush Slip (Odds 2.45)',
    description: 'Carefully analyzed over 2.5 banker accumulator for English Prem. Includes high probability events.',
    price: 1500,
    confidence: 88,
    is_vip: false,
    events: [
      { id: 101, league: 'Premier League', fixture: 'Arsenal vs Manchester United', market: 'Over 2.5 Goals', odds: 1.65, status: 'Pending' },
      { id: 102, league: 'La Liga', fixture: 'Real Madrid vs Barcelona', market: 'Both Teams to Score (GG)', odds: 1.48, status: 'Pending' }
    ]
  },
  {
    id: 2,
    predictor_name: 'Elite Sniper',
    predictor_avatar: 'https://api.dicebear.com/7.x/pixel-art/svg?seed=EliteSniper',
    predictor_accuracy: '91.20',
    title: 'Champions League VIP Banker (Odds 1.85)',
    description: 'Super confidence single bet for Champions League fixtures. Heavy staking recommended.',
    price: 5000,
    confidence: 95,
    is_vip: true,
    events: [
      { id: 103, league: 'Champions League', fixture: 'Manchester City vs Real Madrid', market: 'Manchester City Win', odds: 1.85, status: 'Won' }
    ]
  },
  {
    id: 3,
    predictor_name: 'KingTips (Baba Kola)',
    predictor_avatar: 'https://api.dicebear.com/7.x/pixel-art/svg?seed=KingTips',
    predictor_accuracy: '84.50',
    title: 'Serie A Under-Value Single (Odds 2.10)',
    description: 'A strategic breakdown of Juventus vs AC Milan with strict tactical play. Focused on defense.',
    price: 0, // Free
    confidence: 82,
    is_vip: false,
    events: [
      { id: 104, league: 'Serie A', fixture: 'Juventus vs AC Milan', market: 'Under 2.5 Goals', odds: 2.10, status: 'Won' }
    ]
  }
];

export default function App() {
  // Session / Authentication state
  const [currentUser, setCurrentUser] = useState({
    id: 4,
    username: 'puntersand',
    email: 'anthonycyril321432@gmail.com',
    full_name: 'Anthony Cyril',
    role: 'user', // user, predictor, admin
    wallet_balance: 50000.00
  });

  const [activeTab, setActiveTab] = useState('home'); // home, marketplace, live, wallet, slips, simulator-config, admin-panel
  const [predictions, setPredictions] = useState(INITIAL_PREDICTIONS);
  const [matches, setMatches] = useState(INITIAL_MATCHES);
  
  // Cart & Orders State representing cart and orders tables
  const [cart, setCart] = useState<number[]>([]);
  const [orders, setOrders] = useState<any[]>([
    {
      id: 5001,
      prediction_id: 3, // Already unlocked Series A slip
      amount_paid: 0,
      purchase_date: '2026-05-23 12:00:00'
    }
  ]);

  // Financial deposit/withdrawal state
  const [transactions, setTransactions] = useState<any[]>([
    { id: 1001, reference: 'TXN-DEP-FND918', amount: 50000.00, type: 'deposit', status: 'completed', payment_method: 'Paystack', created_at: '2026-05-23 11:32:00' }
  ]);
  const [depositAmount, setDepositAmount] = useState('5000');
  const [withdrawalAmount, setWithdrawalAmount] = useState('');
  const [withdrawalBank, setWithdrawalBank] = useState('');
  const [withdrawalAccount, setWithdrawalAccount] = useState('');

  // Form states for predictor posting
  const [newTitle, setNewTitle] = useState('');
  const [newDesc, setNewDesc] = useState('');
  const [newPrice, setNewPrice] = useState('1500');
  const [newConfidence, setNewConfidence] = useState('85');
  const [newIsVip, setNewIsVip] = useState(false);
  const [newEventGame, setNewEventGame] = useState('Real Madrid vs Barcelona');
  const [newEventMarket, setNewEventMarket] = useState('Home Win');
  const [newEventOdds, setNewEventOdds] = useState('1.75');

  // Interactive Live Clock / Timing Updates (simulates Ajax commentator intervals)
  useEffect(() => {
    const interval = setInterval(() => {
      setMatches(prevMatches => {
        return prevMatches.map(m => {
          if (m.match_status === 'Live' && m.match_time < 90) {
            const nextTime = m.match_time + 1;
            const liveComm = [...m.live_commentary];
            
            // Randomly trigger goals or alerts representing pitch developments
            let incrementHome = 0;
            let incrementAway = 0;
            if (nextTime === 80) {
              incrementAway = 1;
              liveComm.unshift({
                time: 80,
                text: "GOAL! Bruno Fernandes converts a dynamic penalty kick to narrow margins! 2-2"
              });
            } else if (Math.random() > 0.88) {
              const events = [
                "Tactical corner headed target. Home goalkeeper handles cleanly.",
                "Yellow card shown for reckless slide defense block.",
                "Intense ball possession battling around final third.",
                "Tactical substitution made to preserve fresh energy in midfield."
              ];
              liveComm.unshift({
                time: nextTime,
                text: events[Math.floor(Math.random() * events.length)]
              });
            }

            return {
              ...m,
              match_time: nextTime,
              home_score: m.home_score + incrementHome,
              away_score: m.away_score + incrementAway,
              live_commentary: liveComm
            };
          }
          return m;
        });
      });
    }, 12000); // Trigger quick time increment updates

    return () => clearInterval(interval);
  }, []);

  // UI Handlers
  const addToCart = (predId: number) => {
    if (orders.some(o => o.prediction_id === predId)) {
      alert("⚠️ You already purchased this slip! It is already active on your tickets panel.");
      return;
    }
    if (cart.includes(predId)) {
      alert("⚠️ This item is already sitting in your cart.");
      return;
    }
    setCart([...cart, predId]);
    alert("✓ Slip successfully added to cart. Check your cart to check out.");
  };

  const removeFromCart = (predId: number) => {
    setCart(cart.filter(id => id !== predId));
  };

  const handleCheckout = () => {
    const totalCost = cart.reduce((acc, predId) => {
      const pred = predictions.find(p => p.id === predId);
      return acc + (pred ? pred.price : 0);
    }, 0);

    if (currentUser.wallet_balance < totalCost) {
      alert(`⚠️ Insufficient wallet balance. You have ₦${currentUser.wallet_balance.toLocaleString()} but the checkout requires ₦${totalCost.toLocaleString()}. Please fund your wallet.`);
      setActiveTab('wallet');
      return;
    }

    // Process checkout
    const newBalance = currentUser.wallet_balance - totalCost;
    setCurrentUser({
      ...currentUser,
      wallet_balance: newBalance
    });

    const ref = "TXN-PUR-" + Math.floor(Math.random() * 100000000);
    const orderLogs = cart.map(predId => ({
      id: Math.floor(Math.random() * 100000),
      prediction_id: predId,
      amount_paid: predictions.find(p => p.id === predId)?.price || 0,
      purchase_date: new Date().toISOString().replace('T', ' ').substring(0, 19)
    }));

    setOrders([...orders, ...orderLogs]);
    setTransactions([
      {
        id: Math.floor(Math.random() * 10000),
        reference: ref,
        amount: totalCost,
        type: 'purchase',
        status: 'completed',
        payment_method: 'wallet',
        created_at: new Date().toISOString().replace('T', ' ').substring(0, 19)
      },
      ...transactions
    ]);

    setCart([]);
    alert("🎉 Success! Premium prediction slips unlocked! View details on your Betslips tab.");
    setActiveTab('slips');
  };

  const handleSimulateDeposit = (e: React.FormEvent) => {
    e.preventDefault();
    const val = parseFloat(depositAmount);
    if (isNaN(val) || val <= 0) {
      alert("⚠️ Enter a valid deposit amount.");
      return;
    }

    const newBalance = currentUser.wallet_balance + val;
    setCurrentUser({
      ...currentUser,
      wallet_balance: newBalance
    });

    const ref = "TXN-DEP-" + Math.floor(Math.random() * 900000 + 100000);
    setTransactions([
      {
        id: Math.floor(Math.random() * 10000),
        reference: ref,
        amount: val,
        type: 'deposit',
        status: 'completed',
        payment_method: 'Paystack',
        created_at: new Date().toISOString().replace('T', ' ').substring(0, 19)
      },
      ...transactions
    ]);

    setDepositAmount('5000');
    alert(`✓ deposit confirmation: ₦${val.toLocaleString()} has been credited to your BETELITE wallet.`);
  };

  const handlePredictorPost = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newTitle || !newDesc) {
      alert("⚠️ Please provide a clear title and forecast description.");
      return;
    }

    const priceNum = parseFloat(newPrice) || 0;
    const confNum = parseInt(newConfidence) || 85;

    const newPred = {
      id: predictions.length + 1,
      predictor_name: currentUser.full_name || currentUser.username,
      predictor_avatar: `https://api.dicebear.com/7.x/pixel-art/svg?seed=${currentUser.username}`,
      predictor_accuracy: '94.00',
      title: newTitle,
      description: newDesc,
      price: priceNum,
      confidence: confNum,
      is_vip: newIsVip,
      events: [
        {
          id: Math.floor(Math.random() * 10000),
          league: 'International Club Leagues',
          fixture: newEventGame,
          market: newEventMarket,
          odds: parseFloat(newEventOdds) || 1.80,
          status: 'Pending'
        }
      ]
    };

    setPredictions([newPred, ...predictions]);
    setNewTitle('');
    setNewDesc('');
    alert("✓ Success! Your prediction slip is now compiled and published immediately in the Marketplace.");
    setActiveTab('marketplace');
  };

  // Compute stats helper
  const totalSubscribers = 373;
  const platformRevenue = transactions.reduce((acc, t) => t.type === 'purchase' ? acc + t.amount : acc, 0);

  return (
    <div className="min-h-screen flex flex-col bg-darkBg text-white font-sans selection:bg-electricGreen selection:text-darkBg pb-16 md:pb-0">
      
      {/* 1. Header Navigation Bar */}
      <header className="sticky top-0 z-50 bg-[#020617]/90 backdrop-blur-md border-b border-borderSl px-4 py-3 flex items-center justify-between">
        <div className="flex items-center gap-6">
          <div onClick={() => setActiveTab('home')} className="flex items-center gap-2 font-display font-bold text-xl tracking-widest cursor-pointer">
            <span className="text-electricGreen">🏆</span> BET<span className="text-electricGreen">ELITE</span>
          </div>

          <nav className="hidden md:flex items-center gap-5 text-xs font-semibold text-slate-400">
            <button onClick={() => setActiveTab('home')} className={`hover:text-white transition-all py-1.5 ${activeTab === 'home' ? 'text-electricGreen border-b-2 border-electricGreen' : 'bg-transparent border-none outline-none'}`}>Sports Arena</button>
            <button onClick={() => setActiveTab('marketplace')} className={`hover:text-white transition-all py-1.5 ${activeTab === 'marketplace' ? 'text-electricGreen border-b-2 border-electricGreen' : 'bg-transparent border-none'}`}>VIP Marketplace</button>
            <button onClick={() => setActiveTab('live')} className={`hover:text-white transition-all py-1.5 flex items-center gap-1 ${activeTab === 'live' ? 'text-electricGreen border-b-2 border-electricGreen' : 'bg-transparent border-none'}`}>
              <span className="w-1.5 h-1.5 rounded-full bg-electricGreen animate-pulse"></span> Live Center
            </button>
            <button onClick={() => setActiveTab('wallet')} className={`hover:text-white transition-all py-1.5 ${activeTab === 'wallet' ? 'text-electricGreen border-b-2 border-electricGreen' : 'bg-transparent border-none'}`}>My Wallet</button>
          </nav>
        </div>

        {/* Right Info items */}
        <div className="flex items-center gap-4">
          {/* Practice balance display banner */}
          <div onClick={() => setActiveTab('wallet')} className="flex items-center gap-2 px-3  py-1.5 bg-[#0f172a] border border-borderSl rounded-full text-xs font-semibold font-mono cursor-pointer hover:border-electricGreen transition-all text-white">
            <span className="text-mutedText">₦</span>
            <span className="text-electricGreen">{currentUser.wallet_balance.toLocaleString(undefined, { minimumFractionDigits: 2 })}</span>
            <Plus className="w-3.5 h-3.5 text-electricGreen" />
          </div>

          {/* Cart Badge selector */}
          <button onClick={() => setActiveTab('marketplace')} className="relative p-2 text-slate-400 hover:text-white transition-all bg-transparent border-none">
            <ShoppingCart className="w-5 h-5" />
            {cart.length > 0 && (
              <span className="absolute -top-1 -right-1 bg-dangerRed text-white text-[10px] font-bold w-4.5 h-4.5 rounded-full flex items-center justify-center">
                {cart.length}
              </span>
            )}
          </button>

          {/* User selector block dropdown simulator */}
          <div className="flex items-center gap-2 bg-[#1e293b]/45 border border-borderSl px-2.5 py-1 rounded-lg">
            <img 
              src={`https://api.dicebear.com/7.x/pixel-art/svg?seed=${currentUser.username}`} 
              className="w-7 h-7 rounded-full border border-borderSl" 
              alt="Avatar"
            />
            <div className="hidden sm:block text-left">
              <p className="text-[10px] font-bold text-white line-clamp-1">@{currentUser.username}</p>
              <p className="text-[8px] text-mutedText leading-none uppercase tracking-widest">{currentUser.role} Control</p>
            </div>
            {/* Quick role toggle to test entire applet features */}
            <select 
              value={currentUser.role}
              onChange={(e) => setCurrentUser({ ...currentUser, role: e.target.value })}
              className="bg-transparent border-none text-[9px] text-electricGreen font-bold cursor-pointer focus:ring-0 p-0 ml-1"
            >
              <option value="user" className="bg-slate-900 text-white">Punter View</option>
              <option value="predictor" className="bg-slate-900 text-white">Tipster Pro</option>
              <option value="admin" className="bg-slate-900 text-white">Admin Hub</option>
            </select>
          </div>
        </div>
      </header>

      {/* 2. TAB VIEWS LOGIC */}
      <div className="flex-grow max-w-7xl w-full mx-auto px-4 py-6 md:py-10">
        
        {/* TAB 1: SPORTS HERO HOME PAGE */}
        {activeTab === 'home' && (
          <div className="space-y-10">
            {/* Elite Promo Banner */}
            <section 
              className="glass-card relative overflow-hidden p-6 md:p-10 flex flex-col md:flex-row justify-between items-center gap-6"
              style={{ background: 'radial-gradient(circle at top right, rgba(0, 255, 136, 0.15), rgba(15, 23, 42, 0.95))' }}
            >
              <div className="space-y-4 max-w-2xl text-left">
                <span className="inline-flex items-center gap-1 px-2.5 py-1 bg-electricGreen/10 border border-electricGreen/20 text-electricGreen text-[10px] font-bold rounded-full uppercase tracking-wider">
                  <Award className="w-3 h-3 text-electricGreen" /> Real-time predictions exchange
                </span>
                <h1 className="font-display font-bold text-2xl md:text-5xl text-white tracking-tight leading-xs">
                  Premium Nigerian Sportsbook <span className="text-electricGreen">Marketplace</span>
                </h1>
                <p className="text-xs md:text-sm text-slate-400 leading-relaxed md:max-w-xl">
                  Connect with vetted, high-accuracy prediction tipsters. Gain access to verified slips with custom odds ranging from 1.50 to 10.0+ with full transparency. Setup takes minutes on cPanel.
                </p>
                <div className="flex flex-wrap gap-2 pt-1">
                  <button onClick={() => setActiveTab('marketplace')} className="px-5 py-2.5 bg-electricGreen hover:bg-[#00e177] text-darkBg text-xs font-bold rounded-xl transition-all border-none">
                    Browse Paid Slips
                  </button>
                  <button onClick={() => setActiveTab('live')} className="px-5 py-2.5 bg-[#0f172a] border border-borderSl text-white text-xs font-bold rounded-xl hover:border-slate-700 flex items-center gap-1.5 transition-all">
                    <span className="w-2 h-2 rounded-full bg-electricGreen animate-ping"></span> Live Commentary Center
                  </button>
                </div>
              </div>

              {/* Float Widget */}
              <div className="hidden lg:block w-80 glass-card p-4 bg-slate-950 border-electricGreen/20">
                <div className="flex justify-between text-[10px] text-mutedText mb-2 uppercase tracking-widest font-mono">
                  <span>Verified slip #1039</span>
                  <span className="text-electricGreen font-bold">STAKE WON</span>
                </div>
                <div className="space-y-2 text-xs">
                  <div className="flex justify-between">
                    <span className="text-white">Arsenal vs Man United (GG)</span>
                    <span className="font-mono text-mutedText">@1.65</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-white">Under 2.5 Juve vs Milan</span>
                    <span className="font-mono text-mutedText">@2.10</span>
                  </div>
                </div>
                <div className="border-t border-borderSl mt-3 pt-2.5 flex justify-between text-xs items-center">
                  <span>Accumulated Odds</span>
                  <span className="text-electricGreen font-bold font-mono">3.46 x</span>
                </div>
              </div>
            </section>

            {/* Quick Live matches preview strip */}
            <section className="space-y-4 text-left">
              <div className="flex justify-between items-end">
                <div>
                  <h2 className="font-display font-bold text-sm uppercase tracking-wider text-white">Live Sports center</h2>
                  <p className="text-[10px] text-mutedText">Real-time stats and interval progress updates.</p>
                </div>
                <button onClick={() => setActiveTab('live')} className="text-xs text-electricGreen hover:underline bg-transparent border-none">View live commentaries</button>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                {matches.filter(m => m.match_status === 'Live').map(m => (
                  <div key={m.id} className="glass-card p-4 border-l-4 border-electricGreen bg-slate-900/40 relative">
                    <div className="flex justify-between items-center text-[10px] text-mutedText mb-3 uppercase tracking-wider font-mono">
                      <span>{m.league}</span>
                      <span className="text-electricGreen font-bold flex items-center gap-1 text-[9px] bg-slate-950 px-2 py-0.5 border border-emerald-950 rounded-full animate-all">
                        <span className="w-1.5 h-1.5 rounded-full bg-electricGreen animate-pulse"></span> {m.match_time}' MINS
                      </span>
                    </div>

                    <div className="flex justify-between items-center py-1">
                      <div className="space-y-2">
                        <p className="text-sm font-semibold text-white">{m.home_team}</p>
                        <p className="text-sm font-semibold text-white">{m.away_team}</p>
                      </div>
                      <div className="font-mono font-bold text-lg text-electricGreen bg-slate-950 px-3.5 py-1.5 border border-borderSl rounded-xl text-center">
                        <div>{m.home_score}</div>
                        <div className="border-t border-borderSl text-[10px] text-mutedText py-0.5 mt-0.5">-</div>
                        <div>{m.away_score}</div>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </section>

            {/* Expert tipsters overview */}
            <section className="space-y-4 text-left">
              <div className="flex justify-between items-end">
                <div>
                  <h2 className="font-display font-bold text-sm uppercase tracking-wider text-white">⭐ EXPERT PREDICTION SLIPS</h2>
                  <p className="text-[10px] text-mutedText">Instant unlocks with the platform wallet. Claim free balance and test checkouts.</p>
                </div>
                <button onClick={() => setActiveTab('marketplace')} className="text-xs text-electricGreen hover:underline bg-transparent border-none">Explore Marketplace</button>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                {predictions.map(p => {
                  const isLocked = p.price > 0 && !orders.some(o => o.prediction_id === p.id);
                  return (
                    <div key={p.id} className={`glass-card p-5 h-80 flex flex-col justify-between ${p.is_vip ? 'border-amber-500/30' : ''}`}>
                      <div>
                        {/* Header badges */}
                        <div className="flex justify-between items-center mb-3 text-[10px]">
                          <span className="font-mono bg-slate-800 text-slate-300 font-bold uppercase tracking-wider px-2 py-0.5 rounded-full">
                            {p.confidence}% CoF
                          </span>
                          {p.is_vip && (
                            <span className="font-bold uppercase tracking-widest px-2 py-0.5 bg-amber-500/10 text-vipGold border border-amber-500/20 rounded">
                              VIP VIP
                            </span>
                          )}
                        </div>

                        {/* Title & Desc */}
                        <h3 className="font-display font-semibold text-sm text-white hover:text-electricGreen transition-all line-clamp-1">
                          {p.title}
                        </h3>
                        <p className="text-xs text-mutedText leading-relaxed mt-2 line-clamp-3">
                          {p.description}
                        </p>
                      </div>

                      {/* Footer predictor card stats */}
                      <div className="border-t border-slate-800 pt-3.5 flex justify-between items-center mt-auto">
                        <div className="flex items-center gap-2">
                          <img src={p.predictor_avatar} className="w-8 h-8 rounded-full border border-slate-700" alt="tipster" />
                          <div>
                            <p className="text-xs font-bold text-white mb-0">@{p.predictor_name}</p>
                            <span className="text-[9px] text-emerald-400 mt-1 font-mono">{p.predictor_accuracy}% Accuracy</span>
                          </div>
                        </div>

                        {isLocked ? (
                          <button 
                            onClick={() => addToCart(p.id)}
                            className="text-[11px] font-bold bg-[#1e293b]/50 border border-slate-800 px-3 py-1.5 hover:border-slate-600 rounded-lg flex items-center gap-1 transition-all"
                          >
                            <Lock className="w-3.5 h-3.5 text-amber-500" /> ₦{p.price.toLocaleString()}
                          </button>
                        ) : (
                          <button 
                            onClick={() => {
                              if (p.price === 0) {
                                addToCart(p.id);
                              } else {
                                setActiveTab('slips');
                              }
                            }}
                            className="text-[11px] font-bold bg-electricGreen hover:bg-greenHover text-darkBg px-3.5 py-1.5 rounded-lg border-none transition-all flex items-center gap-1"
                          >
                            {p.price === 0 ? <PlusCircle className="w-3.5 h-3.5" /> : <CheckCircle className="w-3.5 h-3.5" />}
                            {p.price === 0 ? "Add Free" : "Unlocked"}
                          </button>
                        )}
                      </div>
                    </div>
                  );
                })}
              </div>
            </section>
          </div>
        )}

        {/* TAB 2: VIP MARKETPLACE */}
        {activeTab === 'marketplace' && (
          <div className="space-y-8 text-left">
            <div>
              <span className="text-electricGreen text-[10px] font-mono font-bold uppercase tracking-widest">Tipping central</span>
              <h1 className="font-display font-bold text-2xl md:text-3xl text-white">Predictive Slip Exchange</h1>
              <p className="text-xs text-mutedText max-w-xl">Filter slips below. Unlocked games reveal exact odds, fixture stats breakdowns, and recommendations.</p>
            </div>

            {/* Layout grid containing Cart sidebar inside Marketplace */}
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
              <div className="lg:col-span-2 space-y-6">
                {predictions.map(p => {
                  const isLocked = p.price > 0 && !orders.some(o => o.prediction_id === p.id);
                  const inCart = cart.includes(p.id);
                  return (
                    <div key={p.id} className="glass-card p-5 flex flex-col justify-between gap-4">
                      <div className="flex justify-between items-start text-xs border-b border-slate-800 pb-3">
                        <div className="space-y-1">
                          <h3 className="font-display font-semibold text-base text-white">{p.title}</h3>
                          <div className="flex items-center gap-2">
                             <span className="text-[10px] font-mono text-mutedText uppercase">By Tipster @{p.predictor_name}</span>
                             <span className="w-1.5 h-1.5 rounded-full bg-slate-700"></span>
                             <span className="text-[10px] text-emerald-400 font-semibold">{p.confidence}% CoF confidence</span>
                          </div>
                        </div>
                        <span className="font-mono text-xs font-bold text-electricGreen">
                          {p.price === 0 ? "FREE" : `₦${p.price.toLocaleString()}`}
                        </span>
                      </div>

                      <p className="text-xs text-slate-400 leading-relaxed">{p.description}</p>

                      <div className="flex items-center justify-between border-t border-slate-800/60 pt-3">
                         <div className="flex gap-4 text-[10px] text-mutedText">
                           <span className="flex items-center gap-1"><Info className="w-3 h-3 text-electricGreen" /> Recommended singles</span>
                         </div>
                         
                         {isLocked ? (
                           <button 
                             onClick={() => addToCart(p.id)}
                             className={`text-xs font-bold py-1.5 px-4 rounded-lg flex items-center gap-1 border border-transparent transition-all ${inCart ? 'bg-slate-800 text-slate-400 hover:text-white' : 'bg-[#1e293b]/75 hover:bg-[#1e293b] text-white'}`}
                           >
                             {inCart ? <Check className="w-4 h-4 text-emerald-400" /> : <Lock className="w-3.5 h-3.5 text-amber-500" />}
                             {inCart ? 'Sitting in Cart' : 'Unlock Slip'}
                           </button>
                         ) : (
                           <button 
                             onClick={() => {
                               if (p.price === 0) {
                                 addToCart(p.id);
                               } else {
                                 setActiveTab('slips');
                               }
                             }}
                             className="text-xs font-bold py-1.5 px-4 rounded-md bg-electricGreen hover:bg-[#00e177] text-darkBg border-none transition-all flex items-center gap-1"
                           >
                             <CheckCircle2 className="w-4 h-4 text-darkBg" />
                             {p.price === 0 ? "Unlock Free" : "Unlocked View"}
                           </button>
                         )}
                      </div>
                    </div>
                  );
                })}
              </div>

              {/* CART SIDEBAR SYNC */}
              <div className="space-y-6">
                <div className="glass-card p-5 h-fit space-y-4">
                  <div className="flex justify-between items-center border-b border-slate-800 pb-3">
                    <h3 className="text-xs font-bold uppercase tracking-wider text-white flex items-center gap-1.5">
                      <ShoppingCart className="w-4 h-4 text-electricGreen" /> Pending Checkout ({cart.length})
                    </h3>
                    <span className="text-[9px] font-mono text-electricGreen font-semibold uppercase">Wallet Ledger</span>
                  </div>

                  {cart.length > 0 ? (
                    <div className="space-y-4">
                      {cart.map(predId => {
                        const pred = predictions.find(p => p.id === predId);
                        if (!pred) return null;
                        return (
                          <div key={pred.id} className="bg-[#020617]/50 p-3 rounded-lg border border-borderSl flex justify-between items-center gap-3">
                            <div className="overflow-hidden">
                              <p className="text-xs font-semibold text-white truncate">{pred.title}</p>
                              <p className="text-[9px] text-mutedText mt-0.5">By @{pred.predictor_name}</p>
                            </div>
                            <div className="flex items-center gap-2 flex-shrink-0">
                              <span className="font-mono text-[11px] text-electricGreen">₦{pred.price.toLocaleString()}</span>
                              <button 
                                onClick={() => removeFromCart(pred.id)}
                                className="text-dangerRed p-1 bg-transparent hover:bg-slate-850 rounded border-none"
                              >
                                <Trash2 className="w-3.5 h-3.5" />
                              </button>
                            </div>
                          </div>
                        );
                      })}

                      <div className="border-t border-slate-800 pt-3 space-y-2 text-xs">
                        <div className="flex justify-between text-mutedText">
                          <span>Cumulative Slips Fee</span>
                          <span className="font-mono text-white">
                            ₦{cart.reduce((acc, currentId) => {
                              const p = predictions.find(item => item.id === currentId);
                              return acc + (p ? p.price : 0);
                            }, 0).toLocaleString()}
                          </span>
                        </div>
                        <div className="flex justify-between text-mutedText">
                          <span>Commission Tax</span>
                          <span className="text-electricGreen font-mono">₦0.00 PROMO</span>
                        </div>
                      </div>

                      <button 
                        onClick={handleCheckout}
                        className="w-full py-2 bg-electricGreen hover:bg-greenHover text-darkBg font-bold text-xs rounded-xl mt-2 block shadow-lg transition-all border-none"
                      >
                        Checkout using Wallet balance
                      </button>
                    </div>
                  ) : (
                    <div className="text-center py-10 text-mutedText text-xs">
                      <ShoppingCart className="w-8 h-8 opacity-20 mx-auto mb-2 text-mutedText" />
                      <p>Your cart list is blank.</p>
                      <p className="text-[10px] text-mutedText">Add some premium tips on left list.</p>
                    </div>
                  )}
                </div>
              </div>
            </div>
          </div>
        )}

        {/* TAB 3: LIVE COMMENTARIES */}
        {activeTab === 'live' && (
          <div className="space-y-8 text-left">
            <div>
              <span className="inline-flex items-center gap-1.5 text-xs text-electricGreen font-mono font-bold uppercase">
                <span className="w-2 h-2 rounded-full bg-electricGreen animate-ping"></span> Live Commentary Core
              </span>
              <h1 className="font-display font-bold text-2xl md:text-3xl text-white">Live Match Commentary Stadium</h1>
              <p className="text-xs text-mutedText">Real-time ball possession sliders, yellow card logs, Shots grid, and expert insight tickers.</p>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
              {/* Scoreboard Column */}
              <div className="lg:col-span-2 space-y-6">
                {matches.filter(m => m.match_status === 'Live').map(m => (
                  <div key={m.id} className="space-y-6">
                    {/* Main Banner */}
                    <div className="glass-card p-6 bg-gradient-to-r from-slate-900 via-slate-900 to-[#111827] flex flex-col items-center">
                      <span className="text-xs text-electricGreen font-bold bg-emerald-500/10 border border-emerald-500/20 px-3 py-1 rounded-full uppercase tracking-wider mb-4 flex items-center gap-1">
                        <span className="w-1.5 h-1.5 rounded-full bg-electricGreen animate-pulse"></span> MATCH TICKER ACTIVE
                      </span>

                      <div className="w-full flex justify-between items-center py-4">
                        <div className="w-5/12 text-center space-y-2">
                          <div className="w-12 h-12 rounded-full bg-[#1e293b] flex items-center justify-center mx-auto text-white text-base font-bold">
                            {m.home_team.substring(0, 2).toUpperCase()}
                          </div>
                          <h3 className="text-sm font-bold text-white uppercase">{m.home_team}</h3>
                        </div>

                        <div className="w-2/12 font-mono font-bold text-2xl text-electricGreen bg-slate-950 px-4 py-2 border border-slate-800 rounded-xl text-center">
                          <div className="flex justify-center gap-1 text-center">
                            <span>{m.home_score}</span>
                            <span className="text-slate-600">:</span>
                            <span>{m.away_score}</span>
                          </div>
                          <span className="block text-[10px] text-mutedText bg-slate-800 px-1 py-0.5 rounded-full mt-1.5 font-sans font-semibold">{m.match_time}' MINS</span>
                        </div>

                        <div className="w-5/12 text-center space-y-2">
                          <div className="w-12 h-12 rounded-full bg-[#1e293b] flex items-center justify-center mx-auto text-white text-base font-bold">
                            {m.away_team.substring(0, 2).toUpperCase()}
                          </div>
                          <h3 className="text-sm font-bold text-white uppercase">{m.away_team}</h3>
                        </div>
                      </div>
                    </div>

                    {/* Stats Widget */}
                    <div className="glass-card p-5 space-y-4">
                      <h4 className="text-xs font-bold uppercase tracking-wider text-white">Ball possession & momentum</h4>
                      <div className="space-y-1 text-xs">
                        <div className="flex justify-between text-mutedText text-[10px] uppercase font-bold">
                          <span>{m.home_team} {m.possession_home}%</span>
                          <span>{m.away_team} {m.possession_away}%</span>
                        </div>
                        <div className="w-full bg-slate-800 h-2 rounded-full overflow-hidden flex">
                          <div className="bg-electricGreen h-full transition-all duration-1000" style={{ width: `${m.possession_home}%` }}></div>
                          <div className="bg-slate-705 bg-[#1e293b] h-full transition-all duration-1000" style={{ width: `${m.possession_away}%` }}></div>
                        </div>
                      </div>

                      {/* Physical parameters */}
                      <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2 text-center">
                        <div className="p-3 bg-slate-900 border border-borderSl rounded-xl">
                          <p className="text-[10px] text-mutedText uppercase font-bold">Shots on Target</p>
                          <p className="text-base font-bold text-white font-mono mt-0.5">{m.shots_home} v {m.shots_away}</p>
                        </div>
                        <div className="p-3 bg-slate-900 border border-borderSl rounded-xl">
                          <p className="text-[10px] text-mutedText uppercase font-bold">Corners</p>
                          <p className="text-base font-bold text-white font-mono mt-0.5">{m.corners_home} v {m.corners_away}</p>
                        </div>
                        <div className="p-3 bg-slate-900 border border-borderSl rounded-xl">
                          <p className="text-[10px] text-mutedText uppercase font-bold">Yellow Cards</p>
                          <p className="text-base font-bold text-amber-500 font-mono mt-0.5">{m.cards_yellow_home} v {m.cards_yellow_away}</p>
                        </div>
                        <div className="p-3 bg-slate-900 border border-borderSl rounded-xl">
                          <p className="text-[10px] text-mutedText uppercase font-bold">Red Cards</p>
                          <p className="text-base font-bold text-dangerRed font-mono mt-0.5">{m.cards_red_home} v {m.cards_red_away}</p>
                        </div>
                      </div>
                    </div>
                  </div>
                ))}
              </div>

              {/* Commentary Feed Queue Column */}
              <div className="space-y-4">
                <div className="glass-card p-4 h-[440px] flex flex-col justify-between">
                  <div>
                    <div className="flex justify-between items-center border-b border-slate-800 pb-3.5 mb-3.5">
                      <h4 className="text-xs font-bold uppercase tracking-wider text-white">Live commentary logs</h4>
                      <span className="text-[9px] font-mono text-electricGreen uppercase bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-5s">Feed: Active</span>
                    </div>

                    <div className="space-y-3.5 h-[320px] overflow-y-auto pr-1">
                      {matches.find(m => m.match_status === 'Live')?.live_commentary.map((comm, idx) => (
                        <div key={idx} className={`flex gap-3 text-xs leading-relaxed pb-2.5 border-b border-slate-900/60 last:border-0 ${idx === 0 ? 'text-electricGreen bg-emerald-950/10 p-2 rounded' : 'text-slate-300'}`}>
                          <span className="font-mono bg-slate-800 text-slate-300 font-bold p-1 rounded-md h-fit">{comm.time}'</span>
                          <p className="flex-grow">{comm.text}</p>
                        </div>
                      ))}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        )}

        {/* TAB 4: MY WALLET FINTECH WORKSPACE */}
        {activeTab === 'wallet' && (
          <div className="space-y-8 text-left">
            <div>
              <span className="text-electricGreen text-[10px] font-mono font-bold uppercase tracking-widest">FINTECH BANKING CORE</span>
              <h1 className="font-display font-bold text-2xl md:text-3xl text-white">My BETELITE Secure Wallet</h1>
              <p class="text-xs text-mutedText max-w-xl">Fund practice balance, trace transacted predictions purchases, or explore local bank reviews.</p>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
              {/* Core Current balance slider panel */}
              <div className="glass-card p-6 bg-gradient-to-br from-indigo-950/20 to-slate-900 border border-slate-800 flex flex-col justify-between h-80">
                <div className="space-y-2 text-left">
                  <p className="text-[10px] text-mutedText uppercase tracking-widest font-bold">Cumulative Available Balance</p>
                  <h2 className="font-mono font-bold text-3xl md:text-4xl text-electricGreen">
                    ₦{currentUser.wallet_balance.toLocaleString(undefined, { minimumFractionDigits: 2 })}
                  </h2>
                  <span className="text-[9px] text-mutedText uppercase font-mono tracking-wider font-bold bg-slate-950/40 px-2 py-0.5 rounded border border-slate-800/80">Local Currency: NGN Naira</span>
                </div>

                <div className="text-[10px] text-slate-400 bg-slate-950/45 p-3 rounded-lg border border-slate-900 leading-relaxed">
                   🚨 ₦10,000 is automatically created upon registration. Enter any value to mock further top-ups via Paystack.
                </div>
              </div>

              {/* Deposit control form column */}
              <div className="glass-card p-5 space-y-4">
                <h3 className="text-xs font-bold uppercase tracking-wider text-white">Deposit Wallet (Simulated)</h3>
                
                <form onSubmit={handleSimulateDeposit} className="space-y-4">
                  <div>
                    <label className="block text-[10px] text-slate-400 font-semibold mb-1 uppercase">Enter Amount (₦)</label>
                    <input 
                      type="number" 
                      value={depositAmount}
                      onChange={(e) => setDepositAmount(e.target.value)}
                      className="glass-input w-full text-sm py-2 px-3 border border-borderSl bg-slate-950 text-white rounded"
                      min="100"
                      required
                    />
                  </div>

                  <p className="text-[9px] text-mutedText leading-relaxed">Runs in artificial sandbox environment. Top up is instantly credited.</p>
                  
                  <button type="submit" className="w-full py-2.5 bg-electricGreen hover:bg-greenHover text-darkBg font-bold text-xs rounded-xl shadow-lg border-none transition-all">
                    Initiate Deposit
                  </button>
                </form>
              </div>

              {/* Withdrawal info widget column */}
              <div className="glass-card p-5 space-y-4 flex flex-col justify-between h-80">
                <div>
                  <h3 className="text-xs font-bold uppercase tracking-wider text-white">Withdraw Earnings</h3>
                  <p className="text-xs text-mutedText mt-2 leading-relaxed">
                     Predictors can register bank accounts to dispatch tipping earnings directly to standard local banks (GTBank, Access Bank, Zenith Bank). Processing fee stands at 2% commission.
                  </p>
                </div>
                
                <div className="bg-[#020617]/40 p-3 rounded-xl border border-slate-900">
                  <span className="text-[10px] text-slate-400 font-semibold">Active bank configuration: GTBANK</span>
                  <div className="flex justify-between items-center text-[10px] mt-1 text-slate-500">
                    <span>Acc: 012****674</span>
                    <span>Status: verified</span>
                  </div>
                </div>
              </div>
            </div>

            {/* Billings Log Table */}
            <div className="space-y-4 text-left">
              <h3 className="text-xs font-bold uppercase tracking-wider text-slate-300 flex items-center gap-1.5 pt-4">
                <History className="w-4 h-4 text-electricGreen" /> Transaction history list
              </h3>

              <div className="glass-card overflow-hidden">
                <table className="w-full text-left text-xs text-slate-300">
                  <thead className="bg-[#0f172a]/60 text-slate-400 border-b border-borderSl font-mono">
                    <tr>
                      <th className="p-3">Reference & Date</th>
                      <th className="p-3">Type</th>
                      <th className="p-3">Method</th>
                      <th className="p-3 text-right">Amount</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-slate-800/40">
                    {transactions.map(t => (
                      <tr key={t.id} className="hover:bg-slate-900/10">
                        <td className="p-3">
                          <p className="font-mono font-bold text-white text-[11px]">{t.reference}</p>
                          <span className="text-[9px] text-mutedText">{t.created_at}</span>
                        </td>
                        <td className="p-3">
                          <span className={`px-2 py-0.5 rounded text-[10px] uppercase font-mono font-bold ${t.type === 'purchase' ? 'bg-amber-500/10 text-amber-400' : 'bg-emerald-500/10 text-emerald-450 text-electricGreen'}`}>
                            {t.type}
                          </span>
                        </td>
                        <td className="p-3 text-mutedText text-[11px]">{t.payment_method}</td>
                        <td className={`p-3 text-right font-mono font-bold text-[11px] ${t.type === 'purchase' ? 'text-dangerRed' : 'text-electricGreen'}`}>
                          {t.type === 'purchase' ? '-' : '+'}₦{t.amount.toLocaleString(undefined, { minimumFractionDigits: 2 })}
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        )}

        {/* TAB 5: UNLOCKED SLIPS / USER TICKETS DASHBOARD */}
        {activeTab === 'slips' && (
          <div className="space-y-8 text-left">
            <div>
              <span className="text-electricGreen text-[10px] font-mono font-bold uppercase tracking-widest">Betslips center</span>
              <h1 className="font-display font-bold text-2xl md:text-3xl text-white">My Unlocked VIP Bet Slips</h1>
              <p className="text-xs text-mutedText">Instant access to unlocked soccer fixtures lists and predictions choices.</p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
              <div className="space-y-6">
                <h3 className="text-xs font-bold uppercase text-slate-300 tracking-wider flex items-center gap-1.5">
                  <Award className="w-4 h-4 text-electricGreen" /> Unlocked predictive codes
                </h3>

                {orders.length > 0 ? (
                  orders.map(o => {
                    const slip = predictions.find(p => p.id === o.prediction_id);
                    if (!slip) return null;
                    return (
                      <div key={o.id} className="glass-card p-5 space-y-4 border-l-4 border-emerald-500 relative">
                        <div className="flex justify-between items-start border-b border-slate-800 pb-3">
                          <div>
                            <h4 className="text-xs font-bold text-white">{slip.title}</h4>
                            <p className="text-[10px] text-mutedText mt-1 flex items-center gap-1">
                               <span>Forecast by: @{slip.predictor_name}</span>
                            </p>
                          </div>
                          <span className="px-2 py-0.5 bg-emerald-500/10 text-electricGreen rounded text-[9px] font-mono font-bold">
                            UNLOCKED
                          </span>
                        </div>

                        <p className="text-xs text-slate-400 leading-relaxed">{slip.description}</p>

                        {/* Unlocked Inner Events matches details */}
                        <div className="bg-[#020617]/50 border border-slate-900 p-3 rounded-lg space-y-2.5 text-xs text-slate-300">
                          <p className="text-[9px] text-mutedText uppercase tracking-wider font-bold">Recommended Event Bets</p>
                          
                          {slip.events.map(ev => (
                            <div key={ev.id} className="flex justify-between items-center bg-[#0f172a] p-2.5 rounded border border-borderSl">
                              <div>
                                <p className="font-semibold text-[11px]">{ev.fixture}</p>
                                <span className="text-[9px] font-mono text-mutedText uppercase">{ev.league}</span>
                              </div>
                              <div className="text-right flex-shrink-0 ml-3">
                                <p className="font-mono text-electricGreen font-bold text-[11px]">{ev.market} (Odds {ev.odds})</p>
                                <span className="text-[9px] bg-slate-800 text-slate-400 px-1.5 py-0.5 mt-0.5 inline-block rounded font-mono font-bold uppercase">{ev.status}</span>
                              </div>
                            </div>
                          ))}
                        </div>

                        <div className="flex justify-between items-center text-[10px] text-mutedText font-mono pt-1">
                          <span>Bought: {o.purchase_date}</span>
                          <span>Value paid: ₦{o.amount_paid.toLocaleString()}</span>
                        </div>
                      </div>
                    );
                  })
                ) : (
                  <div className="glass-card p-12 text-center text-mutedText space-y-3">
                    <Info className="w-12 h-12 opacity-20 mx-auto text-mutedText" />
                    <p className="text-sm font-semibold">No unlocked cards found yet.</p>
                    <p class="text-xs text-mutedText">Browse expert tipsters and unlock active accumulator cards.</p>
                  </div>
                )}
              </div>

              {/* Referral details widget columns */}
              <div className="space-y-6">
                <div className="glass-card p-5 space-y-4">
                  <h3 className="text-xs font-bold uppercase text-white tracking-wider">Referral reward system</h3>
                  <p className="text-xs text-slate-400 leading-relaxed font-sans">
                     Earn consistent 25% commissions payout on every premium prediction slip purchase registered via your referral links.
                  </p>
                  
                  <div className="bg-[#020617] p-2 border border-slate-850 rounded-lg flex justify-between items-center">
                    <span className="font-mono text-[10px] select-all truncate text-slate-300">https://betelite.com/register?ref={currentUser.username}</span>
                    <button 
                      onClick={() => {
                        navigator.clipboard.writeText(`https://betelite.com/register?ref=${currentUser.username}`);
                        alert("Copied referral link successfully!");
                      }}
                      className="p-1 px-3 text-[10px] font-bold bg-[#1e293b]/55 hover:bg-[#1e293b] text-electricGreen rounded transition-all border-none"
                    >
                      Copy Link
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        )}

      </div>

      {/* 3. Footer Branding Area */}
      <footer className="mt-auto border-t border-borderSl bg-[#0f172a]/45 py-8 px-4 text-center md:text-left">
        <div className="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4 text-center md:text-left text-xs text-slate-400">
          <div>
            <h5 className="font-display font-semibold text-sm text-white flex items-center gap-1.5 justify-center md:justify-start">
              <span>🏆</span> BETELITE VIP
            </h5>
            <p className="text-[10px] text-mutedText mt-0.5">Procedural PHP 8.2+ Sportsbook Prediction Exchange Applet</p>
          </div>
          <div className="flex flex-wrap gap-4 text-[11px] justify-center">
            <button onClick={() => setActiveTab('home')} className="hover:text-white bg-transparent border-none">Arena Home</button>
            <button onClick={() => setActiveTab('marketplace')} className="hover:text-white bg-transparent border-none">VIP Tips</button>
            <button onClick={() => setActiveTab('live')} className="hover:text-white bg-transparent border-none">Commentary Feed</button>
            <button onClick={() => setActiveTab('wallet')} className="hover:text-white bg-transparent border-none">Fintech Wallet</button>
          </div>
          <p className="text-[10px] text-mutedText">© 2026 BETELITE Applet. Styled with glassmorphic dark sheets.</p>
        </div>
      </footer>

      {/* 4. Sticky Bottom Navbar for Mobiles viewports (Stake/SportyBet style) */}
      <div className="md:hidden fixed bottom-0 left-0 right-0 z-50 bg-[#020617]/95 border-t border-borderSl flex justify-around items-center py-2.5">
        <button 
          onClick={() => setActiveTab('home')}
          className={`flex flex-col items-center justify-center text-center bg-transparent border-none text-[10px] ${activeTab === 'home' ? 'text-electricGreen font-bold' : 'text-slate-400'}`}
        >
          <Home className="w-5 h-5 mb-0.5" />
          <span>Arena</span>
        </button>

        <button 
          onClick={() => setActiveTab('marketplace')}
          className={`flex flex-col items-center justify-center text-center bg-transparent border-none text-[10px] ${activeTab === 'marketplace' ? 'text-electricGreen font-bold' : 'text-slate-400'}`}
        >
          <Sparkles className="w-5 h-5 mb-0.5" />
          <span>VIP Slips</span>
        </button>

        <button 
          onClick={() => setActiveTab('live')}
          className={`flex flex-col items-center justify-center text-center bg-transparent border-none relative text-[10px] ${activeTab === 'live' ? 'text-electricGreen font-bold' : 'text-slate-400'}`}
        >
          <span className="absolute top-0 right-1.5 w-2 h-2 rounded-full bg-electricGreen animate-ping"></span>
          <Activity className="w-5 h-5 mb-0.5" />
          <span>Live Center</span>
        </button>

        <button 
          onClick={() => setActiveTab('wallet')}
          className={`flex flex-col items-center justify-center text-center bg-transparent border-none text-[10px] ${activeTab === 'wallet' ? 'text-electricGreen font-bold' : 'text-slate-400'}`}
        >
          <Wallet className="w-5 h-5 mb-0.5" />
          <span>Wallet</span>
        </button>

        <button 
          onClick={() => setActiveTab('slips')}
          className={`flex flex-col items-center justify-center text-center bg-transparent border-none text-[10px] ${activeTab === 'slips' ? 'text-electricGreen font-bold' : 'text-slate-400'}`}
        >
          <Award className="w-5 h-5 mb-0.5" />
          <span>Tickets</span>
        </button>
      </div>

    </div>
  );
}
